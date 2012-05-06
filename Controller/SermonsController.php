<?php
App::uses("Sanitize", "Utility");
App::uses("CuploadifyComponent", "Cuploadify.Controller/Component");
App::uses("ImgLibComponent", "ImgLib.Controller/Component");
App::uses("BibleComponent", "Bible.Controller/Component");
App::uses("TempFolderComponent", "Controller/Component");
App::uses("BibleHelper", "Bible.View/Helper");
App::uses("SoundManager2Helper", "Sm2.View/Helper");
App::uses("MarkdownHelper", "Markdown.View/Helper");
App::uses("UrgSermonAppController", "UrgSermon.Controller");
App::uses("WidgetUtilComponent", "Urg.Controller/Component");
class SermonsController extends UrgSermonAppController {
    var $AUDIO_WEBROOT = "audio";
    var $IMAGES_WEBROOT = "img";
    var $FILES_WEBROOT = "files";

    var $AUDIO = "/app/Plugin/UrgPost/webroot/audio";
    var $IMAGES = "/app/Plugin/UrgPost/webroot/img";
    var $FILES = "/app/Plugin/UrgPost/webroot/files";
    var $WEBROOT = "/app/Plugin/UrgPost/webroot";

    var $BANNER_SIZE = 700;
    var $PANEL_BANNER_SIZE = 460;
    
    var $components = array(
           "Auth" => array(
                   "loginAction" => array(
                           "plugin" => "urg",
                           "controller" => "users",
                           "action" => "login",
                           "admin" => false
                   )
           ), 
           "Urg.Urg", 
           "Cuploadify.Cuploadify", 
           "ImgLib.ImgLib", 
           "TempFolder",
           "Bible.Bible" => array("Esv"=>array("key" => "bef9e04393f0f17f"))
    );

    var $helpers = array(
        "Js" => array("Jquery"), 
        "Time", 
        "Bible.Bible", 
        "Sm2.SoundManager2", 
        "Markdown.Markdown", 
        "TwitterBootstrap.TwitterBootstrap",
        "Html", 
        "Form",
        "Session"
    );
    var $name = 'Sermons';

    function beforeFilter() {
        $this->Auth->allow("view", "passages");
    }

    function index() {
        $this->Sermon->recursive = 0;
        $this->set('sermons', $this->paginate());
    }

    function view($id = null) {
        $this->log("Entering view action", LOG_DEBUG);
        if (!$id) {
            $this->Session->setFlash(__('Invalid sermon'));
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
                        

        $this->loadModel("UrgPost.Attachment");
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

        $banner = null; 
        foreach ($attachments["Banner"] as $key=>$attachment_id) {
            $banner = $key;
        }

        $this->set("title_for_layout", __("Sermons") . " &raquo; " . $sermon["Series"]["name"] . " &raquo; " . $sermon["Post"]["title"]);

        $this->set("banners", array($this->__get_image_path($banner, $sermon, $this->BANNER_SIZE)));
    }

    function import() {
        
    }

    function __get_image_path($filename, $sermon, $width, $height = 0) {
        $full_image_path = $this->__get_doc_root($this->IMAGES) . "/" .  $sermon["Post"]["id"];
        $image = $this->ImgLib->get_image("$full_image_path/$filename", $width, $height, 'landscape'); 
        return "/urg_post/img/" . $sermon["Post"]["id"] . "/" . $image["filename"];
    }

    function passages($passage) {
        $this->layout = "ajax";
        $passages = $this->Bible->get_passage($passage);

        if ($passages == null) {
            $this->log("No passages found for $passage", LOG_DEBUG);
            $this->Session->setFlash(
                    __("We're having problems retrieving Bible verses at the moment. Please try again later."), 
                    "flash_error");
        }

        $this->set("passages", $passages);
    }

    function __populate_series() {
        if ($this->request->data["Sermon"]["series_name"] != "") {
            $series_name = $this->request->data["Sermon"]["series_name"];
            $series_group = $this->Sermon->Post->Group->find("first", array("conditions"=>array("Group.name"=>"Series")));
            $existing_series = $this->Sermon->Post->Group->find("first", 
                    array("conditions" => 
                            array(
                                    "Group.parent_id" => $series_group["Group"]["id"], 
                                    "Group.name" => $series_name
                            )
                    )
            );

            if ($existing_series === false) {
                $this->Sermon->Post->Group->create();
                $this->request->data["Group"]["parent_id"] = $series_group["Group"]["id"];
                $this->request->data["Group"]["name"] = $this->data["Sermon"]["series_name"];
                $this->request->data["Group"]["slug"] = $series_group["Group"]["slug"] . "-" . strtolower(Inflector::slug(str_replace("'", "", $this->data["Sermon"]["series_name"]), "-"));
                $this->Sermon->Post->Group->save($this->request->data);
                $this->request->data["Group"]["id"] = $this->Sermon->Post->Group->id;
                $this->request->data["Group"]["home"] = 1;
                $this->log("New Series for: " . $series_name, LOG_DEBUG);
            } else {
                $this->request->data["Group"] = $existing_series["Group"];
                $this->log("Series exists: " . Debugger::exportVar($this->request->data["Group"], 3), 
                        LOG_DEBUG);
            }
        } else {
            $this->log("No series to populate...", LOG_DEBUG);
        }
    }

    function __prepare_attachments() {
        $logged_user = $this->Session->read("User");
        $attachment_count = isset($this->request->data["Attachment"]) ? 
                sizeof($this->request->data["Attachment"]) : 0;
        if ($attachment_count > 0) {
            $this->log("preparing $attachment_count attachments...", LOG_DEBUG);
            foreach ($this->request->data["Attachment"] as &$attachment) {
                $attachment["user_id"] = $logged_user["User"]["id"];
            }

            $this->Sermon->Post->bindModel(array("hasMany" => array("Attachment")));
            unset($this->Sermon->Post->Attachment->validate["post_id"]);
        }
    }

    function __save_post($id = null) {
        $logged_user = $this->Session->read("User");
        $this->request->data["User"] = $logged_user["User"];

        $this->__populate_series();
        $this->__prepare_attachments();

        $this->log("post belongsto: " . 
                Debugger::exportVar($this->Sermon->Post->belongsTo, 3), LOG_DEBUG);

        $this->request->data["Post"]["id"] = $this->data["Sermon"]["id"];

        $post_timestamp = date_parse_from_format("Y-m-d h:i A", 
                                                 $this->request->data["Post"]["formatted_date"] . " " . 
                                                 $this->request->data["Post"]["displayTime"]);
        $this->request->data["Post"]["publish_timestamp"] = 
                "$post_timestamp[year]-$post_timestamp[month]-$post_timestamp[day]" . 
                " $post_timestamp[hour]:$post_timestamp[minute]";
        //unset($this->Sermon->Post->validate["group_id"]);
/*
        if ($id != null) {
            $this->request->data["Group"] = $this->data["Series"];
        } else {
            //$this->loadModel("Urg.SequenceId");
            $this->request->data["Post"]["id"] = $this->data["Sermon"]["id"];//$this->SequenceId->next($this->Sermon->Post->useTable);
        }
*/
        $this->log("Saving post: " . Debugger::exportVar($this->request->data, 3), LOG_DEBUG);

        $this->log("updated post belongsto: " . 
                Debugger::exportVar($this->Sermon->Post->belongsTo, 3), LOG_DEBUG);

        if ($this->request->data["Post"]["publish_timestamp"] == 0) {
            $this->request->data["Post"]["publish_timestamp"] = null;
        }
/*
        $this->Sermon->Post->bindModel(array("belongsTo" => array(
                "Series" => array(
                    "className" => "Urg.Group",
                    "foreignKey" => "group_id"
                )
            )
        ));

        $this->log("binded model", LOG_DEBUG);
        $status = $this->Sermon->Post->saveAll($this->request->data, array("atomic"=>false));

        if ($id != null && isset($this->Sermon->Post->Group->id)) {
            unset($this->request->data["Group"]);
            $this->request->data["Series"]["id"] = $this->Sermon->Post->Group->id;
            $this->log("Saving post with group id: " . $this->request->data["Series"]["id"], LOG_DEBUG);
        }

        $this->log("Post saved: " . Debugger::exportVar($status, 3), LOG_DEBUG);
*/
        $status = $this->Sermon->Post->saveAll($this->request->data);
        return $status;
    }

    function delete_attachment($id) {
        $dom_id = $this->params["url"]["domId"];
        $success = $this->Sermon->Post->Attachment->delete($id);
        $this->set("data", array("success"=>$success === true, "domId"=>$dom_id));
        $this->render("json", "ajax");
    }

    function __populate_speaker() {
        if ($this->request->data["Sermon"]["speaker_name"] != "") {
            $speaker_name = $this->request->data["Sermon"]["speaker_name"];
            $pastors_group = $this->Sermon->Post->Group->find("first", array("conditions"=>array("Group.name"=>"Pastors")));
            CakeLog::write(LOG_DEBUG, "pastors group for populate: " . Debugger::exportVar($pastors_group, 3));
            $existing_pastor = $this->Sermon->Pastor->find("first", 
                    array("conditions" => 
                            array(
                                    "Pastor.parent_id" => $pastors_group["Group"]["id"], 
                                    "Pastor.name" => $speaker_name
                            )
                    )
            );

            if ($existing_pastor === false) {
                $this->log("New speaker: " . $speaker_name, LOG_DEBUG);
            } else {
                $this->request->data["Pastor"] = $existing_pastor["Pastor"];
                $this->request->data["Sermon"]["speaker_name"] = null;
                unset($this->Sermon->validate["speaker_name"]);
                $this->log("Speaker is a pastor: $speaker_name", LOG_DEBUG);
            }
        }
    }

    function __resize_banner($sermon_id) {
        $full_image_path = $this->__get_doc_root($this->IMAGES) . "/" .  $sermon_id;

        if (file_exists($full_image_path)) {
            $this->loadModel("UrgPost.Attachment");
            $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));

            $banner_type = $this->Attachment->AttachmentType->findByName("Banner");
            $post_banner = $this->Attachment->find("first", 
                    array("conditions" => array("AND" => array(
                    "Attachment.attachment_type_id" => $banner_type["AttachmentType"]["id"],
                    "Attachment.post_id" => $this->request->data["Post"]["id"]
            ))));

            if (isset($post_banner["Attachment"])) {
                $this->log("post banner: " . Debugger::exportVar($post_banner, 3), LOG_DEBUG);
                $this->log("resizing banners...", LOG_DEBUG);
                $this->log("full sermon image path: $full_image_path", LOG_DEBUG);
                $saved_image = $this->ImgLib->get_image($full_image_path . "/" . 
                        $post_banner["Attachment"]["filename"], $this->BANNER_SIZE, 0, 'landscape');
                $this->log("saved $saved_image[filename]", LOG_DEBUG);
            } else {
                $this->log("no banners found for post: " . $this->request->data["Post"]["id"], LOG_DEBUG);
            }
        }
    }

    function add($render = true) {
        if (!empty($this->request->data)) {
            $logged_user = $this->Session->read("User");

            $sermon_ds = $this->Sermon->getDataSource();
            $post_ds = $this->Sermon->Post->getDataSource();

            $post_ds->begin($this->Sermon->Post);
            $sermon_ds->begin($this->Sermon);

            $this->Sermon->Post->create();
            $save_post_status = $this->__save_post();

            // if post saved successfully
            //if (!is_bool($save_post_status) || $save_post_status) {
                //$this->request->data["Series"]["id"] = $this->Sermon->Series->id;
                //$this->request->data["Post"]["id"] = $this->Sermon->Post->id;
                /*$this->log("Post successfully saved. Now saving sermon with series id as: " . 
                        $this->request->data["Series"]["id"] . " group id as: " .
                        $this->request->data["Post"]["id"], LOG_DEBUG);*/
                $this->Sermon->create();

                $this->__populate_speaker();

                $this->log("Attempting to save: " . Debugger::exportVar($this->request->data, 3), LOG_DEBUG);
                if ($this->Sermon->saveAll($this->request->data)) { //, array("atomic"=>false))) {
                    /*$temp_dir = $this->request->data["Sermon"]["id"];

                    $this->consolidate_attachments(
                            array($this->AUDIO, $this->FILES, $this->IMAGES), 
                            $temp_dir
                    );*/

                    $this->__resize_banner($this->Sermon->id);

                    $post_ds->commit($this->Sermon->Post);
                    $sermon_ds->commit($this->Sermon);

                    $this->log("Sermon successfully saved.", LOG_DEBUG);

                    if ($render) {
                        $this->Session->setFlash(__('The sermon has been saved'));
                        $this->redirect(array("plugin"=>"urg_post", "controller"=>"posts", "action"=>"view", $this->request->data["Post"]["id"]));
                    }
                } else {
                    $sermon_ds->rollback($this->Sermon);
                    $post_ds->rollback($this->Sermon->Post);
                    $this->log("Sermon needs to be corrected, redirecting to form.", LOG_DEBUG);

                    if ($render) {
                        $this->Session->setFlash(
                                __('The sermon could not be saved. Please, try again.'));
                    }
                } 
          /*  } else {
                $this->Sermon->saveAll($this->request->data, array("validate"=>"only"));
                $this->log("Sermon needs to be corrected, redirecting to form.", LOG_DEBUG);

                if ($render) {
                    $this->Session->setFlash(__('The sermon could not be saved. Please, try again.'));
                }
            }*/
        } else {
            $this->loadModel("Urg.SequenceId");
            $this->request->data["Sermon"]["id"] = $this->request->data["Post"]["id"] = $this->SequenceId->next($this->Sermon->Post->useTable);
            $this->request->data["Post"]["formatted_date"] = date("Y-m-d");
            $this->request->data["Post"]["displayDate"] = date("F d, Y");
            $this->request->data["Post"]["displayTime"] = date("h:i A");
        }

        $this->loadModel("UrgPost.Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));

        $this->set("banner_type", 
                $this->Attachment->AttachmentType->findByName("Banner"));
        $this->set("audio_type", 
                $this->Attachment->AttachmentType->findByName("Audio"));

        /*$posts = $this->Sermon->Post->find('list');
        $this->set(compact('posts'));*/
    }

    function edit($id = null) {
        $this->Sermon->id = $id;

        if (!$id && empty($this->request->data)) {
            $this->Session->setFlash(__('Invalid sermon'));
            $this->redirect(array('action' => 'index'));
        }
        if (!empty($this->request->data)) {
            $logged_user = $this->Session->read("User");

            $sermon_ds = $this->Sermon->getDataSource();
            $post_ds = $this->Sermon->Post->getDataSource();

            $post_ds->begin($this->Sermon->Post);
            $sermon_ds->begin($this->Sermon);

            $save_post_status = $this->__save_post($this->request->data["Post"]["id"]);

            // if post saved successfully
            if (!is_bool($save_post_status) || $save_post_status) {
                $this->log("Post successfully saved. Now saving sermon with series id as: " . 
                        $this->request->data["Group"]["id"] . " and post id as: " . 
                        $this->request->data["Post"]["id"], LOG_DEBUG);

                $this->__populate_speaker();

                $this->log("Attempting to save: " . Debugger::exportVar($this->request->data, 3), LOG_DEBUG);
                if ($this->Sermon->saveAll($this->request->data, array("atomic"=>false))) {
                    $this->__resize_banner($this->Sermon->id);

                    $post_ds->commit($this->Sermon->Post);
                    $sermon_ds->commit($this->Sermon);

                    $this->log("Sermon successfully saved.", LOG_DEBUG);
                    $this->Session->setFlash(__('The sermon has been saved'));
                    $referer = $this->Session->read("Referer");
                    $this->Session->delete("Referer");
                    $this->redirect($referer);
                } else {
                    $sermon_ds->rollback($this->Sermon);
                    $post_ds->rollback($this->Sermon->Post);
                    $this->log("Sermon needs to be corrected, redirecting to form.", LOG_DEBUG);
                    $this->Session->setFlash(
                            __('The sermon could not be saved. Please, try again.'));
                } 
            } else {
                $this->Sermon->saveAll($this->request->data, array("validate"=>"only"));
                $this->log("Sermon needs to be corrected, redirecting to form.", LOG_DEBUG);
                $this->Session->setFlash(__('The sermon could not be saved. Please, try again.'));
            }
        }

        if (empty($this->request->data)) {
            $this->request->data = $this->Sermon->find("first", array("conditions" => array("Sermon.id" => $id),
                                                             "recursive" => 2));
            $this->request->data["Post"]["formatted_date"] = date("Y-m-d", strtotime($this->data["Post"]["publish_timestamp"]));
            $this->request->data["Post"]["displayDate"] = date("F j, Y", strtotime($this->data["Post"]["publish_timestamp"]));
            $this->request->data["Post"]["displayTime"] = date("h:i A", strtotime($this->data["Post"]["publish_timestamp"]));
            $this->request->data["Post"]["id"] = $this->request->data["Sermon"]["id"] = $id;
            $this->log("form data: " . Debugger::exportVar($this->request->data, 2), LOG_DEBUG);
            $this->__load_speaker();
            $this->Session->write("Referer", $this->referer());
        }

        $this->loadModel("UrgPost.Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));

        $banner_type = $this->Attachment->AttachmentType->findByName("Banner");

        $this->set("banner_type", $banner_type);
        $this->set("audio_type", $this->Attachment->AttachmentType->findByName("Audio"));
        
        $posts = $this->Sermon->Post->find('list');
        $this->request->data["Sermon"]["series_name"] = $this->data["Post"]["Group"]["name"];
        $banner = $this->Attachment->find("first", array(
                "conditions"=>
                        array("Attachment.post_id"=>$this->request->data["Post"]["id"],
                              "Attachment.attachment_type_id"=>$banner_type["AttachmentType"]["id"]
                        ),
                "order" => "Attachment.created DESC"
            )
        );

        $banner_path = $banner === false ? false : $this->__get_image_path($banner["Attachment"]["filename"], 
                                                                           $this->request->data, 
                                                                           $this->PANEL_BANNER_SIZE);
        $this->set("banner", $banner_path);

        $this->set("attachments", $this->Attachment->find("all", array("conditions"=>
                array("Attachment.post_id"=>$this->request->data["Post"]["id"],
                      "Attachment.attachment_type_id !="=>$banner_type["AttachmentType"]["id"]
                )
            )
        ));

        $this->log("sermon banner: " . Debugger::exportVar($banner, 2), LOG_DEBUG);
        $this->set(compact('posts'));
    }

    function delete($id = null) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for sermon'));
            $this->redirect(array('action'=>'index'));
        }

        $sermonToDelete = $this->Sermon->read(null, $id);
        $this->log("Deleting sermon: " . Debugger::exportVar($sermonToDelete, 3), LOG_DEBUG);
        if ($this->Sermon->delete($id) && 
                $this->Sermon->Post->deleteAll(array("Post.id" => $sermonToDelete["Post"]["id"]))) {
            $this->__rrmdir($this->__remove_trailing_slash(env("DOCUMENT_ROOT")) . 
                    $this->AUDIO . "/" . $sermonToDelete["Sermon"]["id"]);
            $this->__rrmdir($this->__remove_trailing_slash(env("DOCUMENT_ROOT")) . 
                    $this->IMAGES . "/" . $sermonToDelete["Sermon"]["id"]);
            $this->__rrmdir($this->__remove_trailing_slash(env("DOCUMENT_ROOT")) . 
                    $this->FILES . "/" . $sermonToDelete["Sermon"]["id"]);
            $this->Session->setFlash(__('Sermon deleted'));
            $this->redirect("/");
        }
        $this->Session->setFlash(__('Sermon was not deleted'));
        $this->redirect("/");
    }


    function autocomplete_speaker() {
        $term = Sanitize::clean($this->params["url"]["term"]);
        $matches = strlen($term) == 0 ? $this->__suggest_speaker() : $this->__search_speaker($term);
        $this->set("data",$matches);
        $this->render("json", "ajax");
    }
    
    function __search_speaker($term) {
        $prepared_matches = array();

        $pastors = $this->requestAction("/urg_sermon/pastors/search/" . $this->params["url"]["term"]);
        foreach ($pastors as $pastor) {
            array_push($prepared_matches,
                    array("label"=> $pastor["Group"]["name"], "belongsToChurch"=>true,
                            "value"=>$pastor["Group"]["name"], "parent_id"=>$pastor["Group"]["id"]));
        }

        $matches = $this->Sermon->find("all", array("conditions"=>array("Sermon.speaker_name LIKE"=>"%$term%"),
                                                    "limit" => 3,
                                                    "fields" => array("DISTINCT speaker_name")));
        foreach ($matches as $match) {
            array_push($prepared_matches, 
                    array("label"=>$match["Sermon"]["speaker_name"], 
                            "value"=>$match["Sermon"]["speaker_name"]));
        }

        return $prepared_matches;
    }

    function __suggest_speaker() {
        $prepared_matches = array();

        $pastors = $this->requestAction("/urg_sermon/pastors/search/" . $this->params["url"]["term"]);
        foreach ($pastors as $pastor) {
            array_push($prepared_matches,
                    array("label"=> $pastor["Group"]["name"], "belongsToChurch"=>true,
                            "value"=>$pastor["Group"]["name"], "parent_id"=>$pastor["Group"]["id"]));
        }
        
        return $prepared_matches;
    }

    function upload_import_file() {
        $filename = $this->__get_filename($this->upload(
                array(
                    "root"=>$this->TempFolder->mkdir(), 
                    "filename_prefix"=>time() . "-",
                    "doc_root_relative" => false
                )
        ));
        $this->set("data", array("filename"=>$filename));
        $this->render("json", "ajax");
    }

    function upload_attachments() {
        $this->log("uploading attachments...", LOG_DEBUG);

        $this->log("determining what type of attachment...", LOG_DEBUG);

        $this->loadModel("UrgPost.Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));
        $attachment_type = null;
        $root = null;
        if ($this->__is_filetype($this->Cuploadify->get_filename(),
                array(".jpg", ".jpeg", ".png", ".gif", ".bmp"))) {
            $root = $this->IMAGES;
            $attachment_type = $this->Attachment->AttachmentType->findByName("Images");
            $webroot_folder = $this->IMAGES_WEBROOT;
        } else if ($this->__is_filetype($this->Cuploadify->get_filename(), array(".mp3"))) {
            $root = $this->AUDIO;
            $attachment_type = $this->Attachment->AttachmentType->findByName("Audio");
            $webroot_folder = $this->AUDIO_WEBROOT;
        } else if ($this->__is_filetype($this->Cuploadify->get_filename(), 
                array(".ppt", ".pptx", ".doc", ".docx",".pdf"))) {
            $root = $this->FILES;
            $attachment_type = $this->Attachment->AttachmentType->findByName("Documents");
            $webroot_folder = $this->FILES_WEBROOT;
        }

        $webroot_folder = $this->get_webroot_folder($this->Cuploadify->get_filename());
        $this->log("attachment type detected as: " . Debugger::exportVar($attachment_type, 3), 
                LOG_DEBUG);
        $this->upload(array("root" => $root));

        //TODO cache id
        $this->set("data", array(
                "attachment_type_id"=>$attachment_type["AttachmentType"]["id"],
                "webroot_folder"=>$webroot_folder
        ));
        $this->render("json", "ajax");
    }

    function __get_attachment_type($filename) {
        $attachment_type = null;
        if ($this->__is_filetype($filename,  array(".jpg", ".jpeg", ".png", ".gif", ".bmp"))) {
            $attachment_type = $this->Attachment->AttachmentType->findByName("Images");
        } else if ($this->__is_filetype($filename, array(".mp3"))) {
            $attachment_type = $this->Attachment->AttachmentType->findByName("Audio");
        } else if ($this->__is_filetype($filename, array(".ppt", ".pptx", ".doc", ".docx"))) {
            $attachment_type = $this->Attachment->AttachmentType->findByName("Documents");
        }

        return $attachment_type;
    }

    function __load_speaker() {
        if (isset($this->request->data["Pastor"]["name"])) {
            $this->request->data["Sermon"]["speaker_name"] = $this->data["Pastor"]["name"];
        }
    }

    function get_webroot_folder($filename) {
        $webroot_folder = null;

        if ($this->__is_filetype($filename, array(".jpg", ".jpeg", ".png", ".gif", ".bmp"))) {
            $webroot_folder = $this->IMAGES_WEBROOT;
        } else if ($this->__is_filetype($filename, array(".mp3"))) {
            $webroot_folder = $this->AUDIO_WEBROOT;
        } else if ($this->__is_filetype($filename, array(".ppt", ".pptx", ".doc", ".docx"))) {
            $webroot_folder = $this->FILES_WEBROOT;
        }

        return $webroot_folder;
    }

    function upload($options) {
        $this->log("uploading options: " . Debugger::exportVar($options), LOG_DEBUG);
        $filename = $this->Cuploadify->upload($options);
        $this->log("done uploading $filename.", LOG_DEBUG);

        return $filename;
    }

    function upload_images() {
        $this->log("uploading images...", LOG_DEBUG);
        $this->upload(array("root"=>$this->IMAGES));
    }

    function upload_image() {
        $options = array("root" => $this->IMAGES);
        $this->upload($options);
        $target_folder = $this->Cuploadify->get_target_folder($options);
        $filename = $target_folder . $this->Cuploadify->get_filename();
    }

    function test() {
        $this->autoRender = false;
        $i = 0;
        while ($i < 100) {
            $i = $i + rand(0, 25); 
            $this->__ajax_log("loading" . $this->params["url"]["filename"], $i);
            $this->__ajax_log("additional information");
            $this->__ajax_log("more additional info");
            sleep(1);
        }

        $this->log("done!", LOG_DEBUG);
    }

    /**
     * Validates the field specified by the parameters.
     * Returns the error message key.
     */
    function validate_field($model_name="Sermon", $field) {
        $this->layout = "ajax";
        $errors = array();

        $this->request->data[$model_name][$field] = $this->params["url"]["value"];

        $model = $model_name == "Sermon" ? $this->Sermon : $this->Sermon->{$model_name};
        $model->set($this->request->data);
        if ($model->validates(array("fieldList"=>array($field)))) {
            if (!isset($this->request->data["Sermon"]["display_speaker_name"]) || strlen(trim($this->request->data["Sermon"]["display_speaker_name"])) == 0) {
                $errors["display_speaker_name"] = array("Please enter a speaker name.");
            }
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
    function __remove_trailing_slash($string) {
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
    function __rename_dir($old_name, $new_name) {
        $this->log("Moving $old_name to $new_name", LOG_DEBUG);
        if (file_exists($old_name)) {
            $this->log("creating dir: $new_name", LOG_DEBUG);
            $old = umask(0);

            if (file_exists($new_name)) {
                mkdir($new_name, 0777, true); 
            }

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

    function __is_filetype($filename, $filetypes) {
        $filename = strtolower($filename);
        $is = false;
        if (is_array($filetypes)) {
            foreach ($filetypes as $filetype) {
                if ($this->__ends_with($filename, $filetype)) {
                    $is = true;
                    break;
                }
            }
        } else {
            $is = $this->__ends_with($filename, $filetypes);
        }

        $this->log("is $filename part of " . implode(",",$filetypes) . "? " . ($is ? "true" : "false"), 
                LOG_DEBUG);
        return $is;
    }

    function __ends_with($haystack, $needle) {
        return strrpos($haystack, $needle) === strlen($haystack)-strlen($needle);
    }
    
    function __get_doc_root($root = null) {
        $doc_root = $this->__remove_trailing_slash(env('DOCUMENT_ROOT'));

        if ($root != null) {
            $root = $this->__remove_trailing_slash($root);
            $doc_root .=  $root;
        }

        return $doc_root;
    }
    
	/**
	 * Function used to delete a folder.
	 * @param $path full-path to folder
	 * @return bool result of deletion
	 */
    function __rrmdir($path) {
        if (!file_exists($path))
            return;

        if (is_dir($path)) {
            if (version_compare(PHP_VERSION, '5.0.0') < 0) {
                $entries = array();
                if ($handle = opendir($path)) {
                    while (false !== ($file = readdir($handle))) $entries[] = $file;
                        closedir($handle);
                    }
                } else {
                    $entries = scandir($path);
                    if ($entries === false) $entries = array();
                }

                foreach ($entries as $entry) {
                    if ($entry != '.' && $entry != '..') {
                        $this->__rrmdir($path.'/'.$entry);
                    }
                }
            return rmdir($path);
        } else {
            return unlink($path);
        }
    }

    function __get_value($node, $tag_name) {
        return $node->child($tag_name)->children[0]->value; 
    }
    
    function __get_percentage($stage, $total_stages) {
        return $stage / $total_stages * 100;
    }

    function process_import_file($filename = null) {
        if ($filename == null) {
            $filename = $this->params["url"]["filename"];
        }

        $this->loadModel("UrgPost.Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));
        $banner_type = $this->Attachment->AttachmentType->findByName("Banner");

        App::uses("Xml");

        $temp_import_dir = $this->TempFolder->mkdir() . "/import";
        if (!file_exists($temp_import_dir)) {
            mkdir($temp_import_dir, 0777, true); 
        }

        $full_path = "$temp_import_dir/$filename";
        $this->log("Processing import file: '$full_path'", LOG_DEBUG);
        $import_file = new Xml(file_get_contents($full_path));

        $stages = $this->__get_num_stages($import_file);
        $this->log("number of stages in $filename: $stages", LOG_DEBUG);
        $current_stage = 0;

        foreach ($import_file->children[0]->children as $sermon) {
            $data = array();
            $this->loadModel("Urg.SequenceId");
            $this->request->data["Sermon"]["id"] = $this->SequenceId->next($this->Sermon->useTable);
            $data["Post"]["title"] = $this->__get_value($sermon, "title");
            $this->__ajax_log(sprintf(__("Importing sermon: %s"), $data["Post"]["title"]), 
                    $this->__get_percentage(++$current_stage, $stages));
            $data["Sermon"]["series_name"] = $this->__get_value($sermon, "series");
            $data["Sermon"]["speaker_name"] = $this->__get_value($sermon, "speaker");
            $data["Sermon"]["passages"] = $this->__get_value($sermon, "passages");
            $data["Post"]["content"] = $this->__get_value($sermon, "notes");
            $data["Post"]["publish_timestamp"] = $this->__get_value($sermon, "timestamp");
            $data["Sermon"]["description"] = $this->__get_value($sermon, "description");
            $data["Attachment"] = array();

            $attachment_counter = 0;

            foreach ($sermon->child("banners")->children as $banner) {
                $temp_folder = $this->__get_doc_root() . $this->IMAGES . "/" . $data["Sermon"]["id"];
                if (!file_exists($temp_folder))
                    mkdir($temp_folder);

                $attachment = array();
                $attachment["attachment_type_id"] = $banner_type["AttachmentType"]["id"];
                $file_path = $banner->attributes["src"];
                $this->__ajax_log(sprintf(__("Copying banner from %s..."), $file_path));
                $filename = $this->__get_filename($this->__copy_file($file_path, $temp_folder));
                $attachment["filename"] = $filename;
                $this->__ajax_log(sprintf(__("Banner %s copied"), $filename),
                        $this->__get_percentage(++$current_stage, $stages));

                $data["Attachment"][$attachment_counter++] = $attachment;
            }

            foreach ($sermon->child("attachments")->children as $current_attachment) {
                $file_path = $current_attachment->attributes["src"];
                $temp_folder = $this->__get_doc_root() . $this->WEBROOT . "/" .
                        $this->get_webroot_folder($file_path) . "/" . $data["Sermon"]["id"];
                if (!file_exists($temp_folder))
                    mkdir($temp_folder);

                $file_path = $current_attachment->attributes["src"];
                $attachment = array();
                $attachment_type = $this->__get_attachment_type($file_path);
                $attachment["attachment_type_id"] = $attachment_type["AttachmentType"]["id"];
                $this->__ajax_log(sprintf(__("Copying attachment from %s..."), $file_path));
                $filename = $this->__get_filename($this->__copy_file($file_path, $temp_folder));
                $attachment["filename"] = $filename;
                $this->__ajax_log(sprintf(__("Attachment %s copied"), $filename),
                        $this->__get_percentage(++$current_stage, $stages));

                $data["Attachment"][$attachment_counter++] = $attachment;
            }

            $this->request->data = &$data;

            $this->autoRender = false;
            $this->__ajax_log(sprintf(__("Saving sermon %s..."), $data["Post"]["title"]));
            $this->add(false);
            $this->__ajax_log(sprintf(__("Sermon %s saved."), $data["Post"]["title"]),
                        $this->__get_percentage(++$current_stage, $stages));
        }

        $this->__ajax_log(__("Sermon import completed."), 
                $this->__get_percentage(++$current_stage, $stages));
    }

    function __get_num_stages($import_file) {
        $stages = 0;
        foreach ($import_file->children[0]->children as $sermon) {
            $stages += sizeof($sermon->child("banners")->children);
            $stages += sizeof($sermon->child("attachments")->children);
            $stages++;
        }

        return ++$stages;
    }

    function __copy_file($uri, $folder) {
        $filename = $this->__get_filename($uri); 
        $remote_file = fopen($uri, "r");
        $destination_file_path = "$folder/$filename";
        $local_file = fopen($destination_file_path, "w");

        stream_copy_to_stream($remote_file, $local_file);

        fclose($local_file);
        fclose($remote_file);

        return $destination_file_path;
    }

    function __ajax_log($message, $pct = false) {
        $this->log("[" . $this->Session->id() . "] " . $message, LOG_DEBUG);
        $log = $this->__get_log();

        if ($pct !== false) {
            $log[0] = $pct;
        }

        array_push($log, $message);
        $this->__write_log($log);
    }

    function __get_log() {
        return explode("|", file_get_contents($this->TempFolder->mkdir() . "/sermon.import.log"));
    }

    function __write_log($log) {
        $log_handle = fopen($this->TempFolder->mkdir() . "/sermon.import.log", "w");
        fwrite($log_handle, implode("|", $log));
        fclose($log_handle);
    }

    function get_status() {
        $log = $this->__get_log();
        $this->__write_log(array($log[0]));

        $this->set("data", array("pct" => array_shift($log), "log" => $log));
        $this->render("json", "ajax");
    }

    function __get_filename($full_path) {
        $filename = $full_path;
        $index = 0;
        if (($index = strrpos($full_path, "/")) !== false) {
            $filename = substr($full_path, $index + 1);
        }

        return $filename;
    }
}

if (!function_exists('date_parse_from_format')) {
  function date_parse_from_format($format, $date) {
    $i=0;
    $pos=0;
    $output=array();
    while ($i< strlen($format)) {
      $pat = substr($format, $i, 1);
      $i++;
      switch ($pat) {
        case 'd': //    Day of the month, 2 digits with leading zeros    01 to 31
          $output['day'] = substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'D': // A textual representation of a day: three letters    Mon through Sun
          //TODO
        break;
        case 'j': //    Day of the month without leading zeros    1 to 31
          $output['day'] = substr($date, $pos, 2);
          if (!is_numeric($output['day']) || ($output['day']>31)) {
            $output['day'] = substr($date, $pos, 1);
            $pos--;
          }
          $pos+=2;
        break;
        case 'm': //    Numeric representation of a month: with leading zeros    01 through 12
          $output['month'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'n': //    Numeric representation of a month: without leading zeros    1 through 12
          $output['month'] = substr($date, $pos, 2);
          if (!is_numeric($output['month']) || ($output['month']>12)) {
            $output['month'] = substr($date, $pos, 1);
            $pos--;
          }
          $pos+=2;
        break;
        case 'Y': //    A full numeric representation of a year: 4 digits    Examples: 1999 or 2003
          $output['year'] = (int)substr($date, $pos, 4);
          $pos+=4;
        break;
        case 'y': //    A two digit representation of a year    Examples: 99 or 03
          $output['year'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'g': //    12-hour format of an hour without leading zeros    1 through 12
          $output['hour'] = substr($date, $pos, 2);
          if (!is_numeric($output['day']) || ($output['hour']>12)) {
            $output['hour'] = substr($date, $pos, 1);
            $pos--;
          }
          $pos+=2;
        break;
        case 'G': //    24-hour format of an hour without leading zeros    0 through 23
          $output['hour'] = substr($date, $pos, 2);
          if (!is_numeric($output['day']) || ($output['hour']>23)) {
            $output['hour'] = substr($date, $pos, 1);
            $pos--;
          }
          $pos+=2;
        break;
        case 'h': //    12-hour format of an hour with leading zeros    01 through 12
          $output['hour'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'H': //    24-hour format of an hour with leading zeros    00 through 23
          $output['hour'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'i': //    Minutes with leading zeros    00 to 59
          $output['minute'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 's': //    Seconds: with leading zeros    00 through 59
          $output['second'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'l': // (lowercase 'L')    A full textual representation of the day of the week    Sunday through Saturday
        case 'N': //    ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)    1 (for Monday) through 7 (for Sunday)
        case 'S': //    English ordinal suffix for the day of the month: 2 characters    st: nd: rd or th. Works well with j
        case 'w': //    Numeric representation of the day of the week    0 (for Sunday) through 6 (for Saturday)
        case 'z': //    The day of the year (starting from 0)    0 through 365
        case 'W': //    ISO-8601 week number of year: weeks starting on Monday (added in PHP 4.1.0)    Example: 42 (the 42nd week in the year)
        case 'F': //    A full textual representation of a month: such as January or March    January through December
        case 'u': //    Microseconds (added in PHP 5.2.2)    Example: 654321
        case 't': //    Number of days in the given month    28 through 31
        case 'L': //    Whether it's a leap year    1 if it is a leap year: 0 otherwise.
        case 'o': //    ISO-8601 year number. This has the same value as Y: except that if the ISO week number (W) belongs to the previous or next year: that year is used instead. (added in PHP 5.1.0)    Examples: 1999 or 2003
        case 'e': //    Timezone identifier (added in PHP 5.1.0)    Examples: UTC: GMT: Atlantic/Azores
        case 'I': // (capital i)    Whether or not the date is in daylight saving time    1 if Daylight Saving Time: 0 otherwise.
        case 'O': //    Difference to Greenwich time (GMT) in hours    Example: +0200
        case 'P': //    Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)    Example: +02:00
        case 'T': //    Timezone abbreviation    Examples: EST: MDT ...
        case 'Z': //    Timezone offset in seconds. The offset for timezones west of UTC is always negative: and for those east of UTC is always positive.    -43200 through 50400
        case 'a': //    Lowercase Ante meridiem and Post meridiem    am or pm
        case 'A': //    Uppercase Ante meridiem and Post meridiem    AM or PM
        case 'B': //    Swatch Internet time    000 through 999
        case 'M': //    A short textual representation of a month: three letters    Jan through Dec
        default:
          $pos++;
      }
    }
return  $output;
  }
}
?>
