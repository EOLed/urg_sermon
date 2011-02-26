<?php
App::import("Sanitize");
App::import("Component", "Cuploadify.Cuploadify");
App::import("Component", "ImgLib.ImgLib");
class SermonsController extends UrgSermonAppController {
    var $AUDIO_WEBROOT = "audio";
    var $IMAGES_WEBROOT = "img";
    var $FILES_WEBROOT = "files";

    var $AUDIO = "/app/plugins/urg_sermon/webroot/audio";
    var $IMAGES = "/app/plugins/urg_sermon/webroot/img";
    var $FILES = "/app/plugins/urg_sermon/webroot/files";

    var $BANNER_SIZE = 700;
    
    var $components = array(
           "Auth" => array(
                   "loginAction" => array(
                           "plugin" => "urg",
                           "controller" => "users",
                           "action" => "login",
                           "admin" => false
                   )
           ), "Urg", "Cuploadify", "ImgLib");

    var $helpers = array(
        "Js" => array("Jquery"), "Time"
    );
    var $name = 'Sermons';

    function index() {
        $this->Sermon->recursive = 0;
        $this->set('sermons', $this->paginate());
    }

    function view($id = null) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid sermon', true));
            $this->redirect(array('action' => 'index'));
        }
        $sermon = $this->Sermon->find("first", 
                array(  "conditions" => array("Sermon.id"=>$id),
                        "recursive" => 3
                )
        );

        $series = $this->Sermon->find("all",
                array(  "conditions" => array("Sermon.series_id" => $sermon["Series"]["id"]),
                        "order" => array("Post.publish_timestamp")
                )
        );
                        

        $this->loadModel("Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));
        $attachments = $this->Attachment->find("list", 
                array(  "conditions" => array("AND" => array(
                                "AttachmentType.name" => array("Banner", "Audio", "Documents"),
                                "Attachment.post_id" => $sermon["Post"]["id"])
                        ),
                        "fields" => array(  "Attachment.filename", 
                                            "Attachment.id",
                                            "AttachmentType.name"
                                    ),
                        "joins" => array(   
                                array(  "table" => "attachment_types",
                                        "alias" => "AttachmentType",
                                        "type" => "LEFT",
                                        "conditions" => array(
                                            "AttachmentType.id = Attachment.attachment_type_id"
                                        )
                                )
                        )
               )
        );
        $this->log("Viewing sermon: " . Debugger::exportVar($sermon, 3), 
                LOG_DEBUG);
        $this->log("Available attachments: " . Debugger::exportVar($attachments, 1), 
                LOG_DEBUG);
        $this->log("Related sermons: " . Debugger::exportVar($series, 3), LOG_DEBUG);
        $this->set('sermon', $sermon);
        $this->set("attachments", $attachments);
        $this->set("series_sermons", $series);
        $banners = array();
        
        foreach ($attachments["Banner"] as $key=>$attachment_id) {
            $full_image_path = $this->get_doc_root($this->IMAGES) . "/" .  $sermon["Sermon"]["id"];
            $image = $this->ImgLib->get_image("$full_image_path/$key", 
                    $this->BANNER_SIZE, 0, 'landscape'); 
            array_push($banners, "/urg_sermon/img/" . $sermon["Sermon"]["id"] . "/" . 
                    $image["filename"]);
        }

        $this->set("banners", $banners);
    }

    function add() {
        if (!empty($this->data)) {
            $sermon_ds = $this->Sermon->getDataSource();
            $post_ds = $this->Sermon->Post->getDataSource();

            $logged_user = $this->Auth->user();
            if ($this->data["Sermon"]["series_name"] != "") {
                $series_name = $this->data["Sermon"]["series_name"];
                $series_group = $this->Sermon->Series->findByName("Series");
                $existing_series = $this->Sermon->Series->find("first", 
                        array("conditions" => 
                                array(
                                        "Series.group_id" => $series_group["Series"]["id"], 
                                        "Series.name" => $series_name
                                )
                        )
                );

                if ($existing_series === false) {
                    $this->Sermon->Series->create();
                    $this->data["Series"]["group_id"] = $series_group["Series"]["id"];
                    $this->data["Series"]["name"] = $this->data["Sermon"]["series_name"];
                    $this->log("New Series for: " . $series_name, LOG_DEBUG);
                } else {
                    $this->data["Series"] = $existing_series["Series"];
                    $this->log("Series exists: $series_name", LOG_DEBUG);
                }
            }

            $this->Sermon->Post->create();
            $post_ds->begin($this->Sermon->Post);
            $sermon_ds->begin($this->Sermon);
            $this->data["User"] = $logged_user["User"];

            $attachment_count = isset($this->data["Attachment"]) ? 
                    sizeof($this->data["Attachment"]) : 0;

            if ($attachment_count > 0) {
                $this->log("preparing $attachment_count attachments...", LOG_DEBUG);
                foreach ($this->data["Attachment"] as &$attachment) {
                    $attachment["user_id"] = $logged_user["User"]["id"];
                }

                $this->Sermon->Post->bindModel(array("hasMany" => array("Attachment")));
                unset($this->Sermon->Post->Attachment->validate["post_id"]);
            }

            $this->Sermon->Post->bindModel(array("belongsTo" => array(
                    "Series" => array(
                        "className" => "Urg.Group",
                        "foreignKey" => "group_id"
                    )
                )
            ));
            
            unset($this->Sermon->Post->validate["group_id"]);

            $this->Sermon->Post->unbindModel(array("belongsTo" => array("Group")));

            $status = $this->Sermon->Post->saveAll($this->data, array("atomic"=>false));
  
            $this->log("Post saved: " . Debugger::exportVar($status, 3), LOG_DEBUG);
            if (!is_bool($status) || $status) {
                $this->data["Series"]["id"] = $this->Sermon->Post->Series->id;
                $this->data["Post"]["id"] = $this->Sermon->Post->id;
                $this->log("Post successfully saved. Now saving sermon with series id as: " . 
                        $this->data["Series"]["id"] . " and post id as: " . 
                        $this->data["Post"]["id"], LOG_DEBUG);
                $this->Sermon->create();
                if ($this->data["Sermon"]["speaker_name"] != "") {
                    $speaker_name = $this->data["Sermon"]["speaker_name"];
                    $pastors_group = $this->Sermon->Pastor->findByName("Pastors");
                    $existing_pastor = $this->Sermon->Pastor->find("first", 
                            array("conditions" => 
                                    array(
                                            "Pastor.group_id" => $pastors_group["Pastor"]["id"], 
                                            "Pastor.name" => $speaker_name
                                    )
                            )
                    );

                    if ($existing_pastor === false) {
                        $this->log("New speaker: " . $speaker_name, LOG_DEBUG);
                    } else {
                        $this->data["Pastor"] = $existing_pastor["Pastor"];
                        $this->data["Sermon"]["speaker_name"] = null;
                        unset($this->Sermon->validate["speaker_name"]);
                        $this->log("Speaker is a pastor: $speaker_name", LOG_DEBUG);
                    }
                }

                $this->log("Attempting to save: " . Debugger::exportVar($this->data, 3), LOG_DEBUG);
                if ($this->Sermon->saveAll($this->data, array("atomic"=>false))) {
                    $temp_dir = $this->data["Sermon"]["uuid"];
                    $temp_audio = $this->AUDIO . "/$temp_dir";
                    $temp_images = $this->IMAGES . "/$temp_dir";
                    $temp_files = $this->FILES . "/$temp_dir";
                    $doc_root = $this->remove_trailing_slash(env("DOCUMENT_ROOT"));

                    if (file_exists($doc_root . $temp_audio)) {
                        $audio_dir = $this->AUDIO . "/" . $this->Sermon->id;
                        $this->rename_dir($doc_root . $temp_audio, $doc_root . $audio_dir);
                        $this->log("moved audio to permanent folder: $doc_root$audio_dir", LOG_DEBUG);
                    } else {
                        $this->log("no audio to move, since folder doesn't exist: $doc_root$temp_audio",
                                LOG_DEBUG);
                    }

                    if (file_exists($doc_root . $temp_files)) {
                        $files_dir = $this->FILES . "/" . $this->Sermon->id;
                        $this->rename_dir($doc_root . $temp_files, $doc_root . $files_dir);
                        $this->log("moved files to permanent folder: $doc_root$files_dir", LOG_DEBUG);
                    } else {
                        $this->log("no files to move, since folder doesn't exist: $doc_root$temp_files",
                                LOG_DEBUG);
                    }

                    if (file_exists($doc_root . $temp_images)) {
                        $images_dir = $this->IMAGES . "/" .  $this->Sermon->id;
                        $this->rename_dir($doc_root . $temp_images, $doc_root . $images_dir);
                        $this->log("moved images to permanent folder: $doc_root$images_dir", LOG_DEBUG);

                        $this->loadModel("Attachment");
                        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));

                        $banner_type = $this->Attachment->AttachmentType->findByName("Banner");
                        $post_banner = $this->Attachment->find("first", 
                                array("conditions" => array("AND" => array(
                                "Attachment.attachment_type_id" => $banner_type["AttachmentType"]["id"],
                                "Attachment.post_id" => $this->data["Post"]["id"]
                        ))));
                        $this->log("post banner: " . Debugger::exportVar($post_banner, 3), LOG_DEBUG);

                        $this->log("resizing banners...", LOG_DEBUG);
                        $full_image_path = $this->get_doc_root($this->IMAGES) . "/" .
                                $this->Sermon->id;
                        $this->log("full sermon image path: $full_image_path", LOG_DEBUG);
                        $saved_image = $this->ImgLib->get_image($full_image_path . "/" . 
                                $post_banner["Attachment"]["filename"], $this->BANNER_SIZE, 0, 
                                'landscape');
                        $this->log("saved $saved_image[filename]", LOG_DEBUG);
                    } else {
                        $this->log("no images to move, since folder doesn't exist: " .
                                "$doc_root$temp_images", LOG_DEBUG);
                    }

                    $post_ds->commit($this->Sermon->Post);
                    $sermon_ds->commit($this->Sermon);

                    $this->log("Sermon successfully saved.", LOG_DEBUG);
                    $this->Session->setFlash(__('The sermon has been saved', true));
                    $this->redirect(array('action' => 'index'));
                } else {
                    $sermon_ds->rollback($this->Sermon);
                    $post_ds->rollback($this->Sermon->Post);
                    $this->log("Sermon needs to be corrected, redirecting to form.", LOG_DEBUG);
                    $this->Session->setFlash(
                            __('The sermon could not be saved. Please, try again.', true));
                } 
            } else {
                $this->Sermon->saveAll($this->data, array("validate"=>"only"));
                $this->log("Sermon needs to be corrected, redirecting to form.", LOG_DEBUG);
                $this->Session->setFlash(__('The sermon could not be saved. Please, try again.', true));
            }
        } else {
            $this->data["Sermon"]["uuid"] = String::uuid();
        }

        $this->loadModel("Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));

        $this->set("banner_type", 
                $this->Attachment->AttachmentType->findByName("Banner"));
        $this->set("audio_type", 
                $this->Attachment->AttachmentType->findByName("Audio"));
        $posts = $this->Sermon->Post->find('list');
        $this->set(compact('posts'));
    }

    function edit($id = null) {
        if (!$id && empty($this->data)) {
            $this->Session->setFlash(__('Invalid sermon', true));
            $this->redirect(array('action' => 'index'));
        }
        if (!empty($this->data)) {
            if ($this->Sermon->save($this->data)) {
                $this->Session->setFlash(__('The sermon has been saved', true));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The sermon could not be saved. Please, try again.', true));
            }
        }
        if (empty($this->data)) {
            $this->data = $this->Sermon->read(null, $id);
        }
        $posts = $this->Sermon->Post->find('list');
        $this->set(compact('posts'));
    }

    function delete($id = null) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for sermon', true));
            $this->redirect(array('action'=>'index'));
        }
        if ($this->Sermon->delete($id)) {
            $this->Session->setFlash(__('Sermon deleted', true));
            $this->redirect(array('action'=>'index'));
        }
        $this->Session->setFlash(__('Sermon was not deleted', true));
        $this->redirect(array('action' => 'index'));
    }

    function autocomplete_speaker() {
        $term = Sanitize::clean($this->params["url"]["term"]);
        $matches = strlen($term) == 0 ? $this->suggest_speaker() : $this->search_speaker($term);
        $this->set("data",$matches);
        $this->render("json", "ajax");
    }
    
    function search_speaker($term) {
        $prepared_matches = array();

        $pastors = $this->requestAction("/urg_sermon/pastors/search/" . $this->params["url"]["term"]);
        foreach ($pastors as $pastor) {
            array_push($prepared_matches,
                    array("label"=> $pastor["Group"]["name"], "belongsToChurch"=>true,
                            "value"=>$pastor["Group"]["name"], "group_id"=>$pastor["Group"]["id"]));
        }

        $matches = $this->Sermon->query("SELECT DISTINCT speaker_name speaker_name " .
                "FROM sermons Sermon WHERE speaker_name LIKE '%$term%' ORDER BY speaker_name LIMIT 3");
        foreach ($matches as $match) {
            array_push($prepared_matches, 
                    array("label"=>$match["Sermon"]["speaker_name"], 
                            "value"=>$match["Sermon"]["speaker_name"]));
        }

        return $prepared_matches;
    }

    function suggest_speaker() {
        $prepared_matches = array();

        $pastors = $this->requestAction("/urg_sermon/pastors/search/" . $this->params["url"]["term"]);
        foreach ($pastors as $pastor) {
            array_push($prepared_matches,
                    array("label"=> $pastor["Group"]["name"], "belongsToChurch"=>true,
                            "value"=>$pastor["Group"]["name"], "group_id"=>$pastor["Group"]["id"]));
        }
        
        return $prepared_matches;
    }

    function upload_attachments() {
        $this->log("uploading attachments...", LOG_DEBUG);

        $this->log("determining what type of attachment...", LOG_DEBUG);

        $this->loadModel("Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));
        $attachment_type = null;
        $root = null;
        if ($this->is_filetype($this->Cuploadify->get_filename(),
                array(".jpg", ".jpeg", ".png", ".gif", ".bmp"))) {
            $root = $this->IMAGES;
            $attachment_type = $this->Attachment->AttachmentType->findByName("Images");
            $webroot_folder = $this->IMAGES_WEBROOT;
        } else if ($this->is_filetype($this->Cuploadify->get_filename(), array(".mp3"))) {
            $root = $this->AUDIO;
            $attachment_type = $this->Attachment->AttachmentType->findByName("Audio");
            $webroot_folder = $this->AUDIO_WEBROOT;
        } else if ($this->is_filetype($this->Cuploadify->get_filename(), 
                array(".ppt", ".pptx", ".doc", ".docx"))) {
            $root = $this->FILES;
            $attachment_type = $this->Attachment->AttachmentType->findByName("Documents");
            $webroot_folder = $this->FILES_WEBROOT;
        }
        $this->log("attachment type detected as: " . Debugger::exportVar($attachment_type, 3), 
                LOG_DEBUG);
        $this->upload($root);

        //TODO cache id
        $this->set("data", array(
                "attachment_type_id"=>$attachment_type["AttachmentType"]["id"],
                "webroot_folder"=>$webroot_folder
        ));
        $this->render("json", "ajax");
    }
    
    function upload($root) {
        $options = array("root" => $root);
        $this->log("uploading options: " . Debugger::exportVar($options), LOG_DEBUG);
        $this->Cuploadify->upload($options);
        $this->log("done uploading.", LOG_DEBUG);
    }

    function upload_images() {
        $this->log("uploading images...", LOG_DEBUG);
        $this->upload($this->IMAGES);
    }

    function upload_image() {
        $this->upload($this->IMAGES);
        $options = array("root" => $this->IMAGES);
        $target_folder = $this->Cuploadify->get_target_folder($options);
        $filename = $target_folder . $this->Cuploadify->get_filename();
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $banner = $target_folder . "banner.$ext";

        rename($filename, $banner); 
        $this->log("uploading $filename as $banner", LOG_DEBUG);
    }

    /**
     * Validates the field specified by the parameters.
     * Returns the error message key.
     */
    function validate_field($model_name="Sermon", $field) {
        $this->layout = "ajax";
        $errors = array();

        $this->data[$model_name][$field] = $this->params["url"]["value"];

        $model = $model_name == "Sermon" ? $this->Sermon : $this->Sermon->{$model_name};
        $model->set($this->data);

        if ($model->validates(array("fieldList"=>array($field)))) {
        } else {
            $errors = $model->invalidFields();
        }

        $this->log("Errors on $model_name.$field: " . Debugger::exportVar($errors, 2), LOG_DEBUG);
        $this->set("error", isset($errors[$field]) ? $errors[$field] : null);
        $this->set("model", $model_name);
        $this->set("field", $field);
    }

    /**
     * Removes the trailing slash from the string specified.
     * @param $string the string to remove the trailing slash from.
     */
    function remove_trailing_slash($string) {
        $string_length = strlen($string);
        if (strrpos($string, "/") === $string_length - 1) {
            $string = substr($string, 0, $string_length - 1);
        }

        return $string;
    }

    /**
     * Renames the directory, even if there are contents inside of it.
     * @param $string old_dir The old directory name.
     * @param $string new_dir The new directory name.
     */
    function rename_dir($old_name, $new_name) {
        $this->log("Moving $old_name to $new_name", LOG_DEBUG);
        if (file_exists($old_name)) {
            $this->log("creating dir: $new_name", LOG_DEBUG);
            $old = umask(0);
            mkdir($new_name, 0777, true); 
            umask($old);
            if ($handle = opendir($old_name)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                       rename("$old_name/$file", "$new_name/$file"); 
                    }
                }
                closedir($handle);
                rmdir($old_name);
            }
        }
    }

    function is_filetype($filename, $filetypes) {
        $filename = strtolower($filename);
        $is = false;
        if (is_array($filetypes)) {
            foreach ($filetypes as $filetype) {
                if ($this->ends_with($filename, $filetype)) {
                    $is = true;
                    break;
                }
            }
        } else {
            $is = $this->ends_with($filename, $filetypes);
        }

        $this->log("is $filename part of " . implode(",",$filetypes) . "? " . ($is ? "true" : "false"), 
                LOG_DEBUG);
        return $is;
    }

    function ends_with($haystack, $needle) {
        return strrpos($haystack, $needle) === strlen($haystack)-strlen($needle);
    }
    
    function get_doc_root($root = null) {
        $doc_root = $this->remove_trailing_slash(env('DOCUMENT_ROOT'));

        if ($root != null) {
            $root = $this->remove_trailing_slash($root);
            $doc_root .=  $root;
        }

        return $doc_root;
    }
}
?>
