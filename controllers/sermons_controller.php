<?php
App::import("Sanitize");
App::import("Component", "Cuploadify.Cuploadify");
class SermonsController extends UrgSermonAppController {
    var $AUDIO = "/app/plugins/urg_sermon/webroot/audio";
    var $IMAGES = "/app/plugins/urg_sermon/webroot/img";

    var $components = array(
           "Auth" => array(
                   "loginAction" => array(
                           "plugin" => "urg",
                           "controller" => "users",
                           "action" => "login",
                           "admin" => false
                   )
           ), "Urg", "Cuploadify");
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
        $this->set('sermon', $this->Sermon->read(null, $id));
    }

    function add() {
        if (!empty($this->data)) {
            $logged_user = $this->Auth->user("id");
            if ($this->data["Sermon"]["series_id"] == "") {
                $series_name = $this->data["Sermon"]["series_name"];
                $this->log("Creating new series for: " . $series_name, LOG_DEBUG);
                $this->data["Sermon"]["series_id"] = 
                        $this->requestAction("/urg_sermon/series/create/" .  $series_name);
            }

            $this->Sermon->Post->create();
            $this->log("Saving sermon as post...");
            $this->data["Post"]["user_id"] = $logged_user;
            $this->data["Post"]["group_id"] = $this->data["Sermon"]["series_id"];

            $attachment_count = isset($this->data["Attachment"]) ? 
                    sizeof($this->data["Attachment"]) : 0;

            if ($attachment_count > 0) {
                $this->log("preparing $attachment_count attachments...", LOG_DEBUG);
                foreach ($this->data["Attachment"] as &$attachment) {
                    $attachment["user_id"] = $logged_user;
                }

                $this->Sermon->Post->bindModel(array("hasMany" => array("Attachment")));
                unset($this->Sermon->Post->Attachment->validate["post_id"]);
            }

            $post = $this->Sermon->Post->saveAll($this->data);
            $this->log("Sermon post saved..", LOG_DEBUG);

            $this->Sermon->create();
            $this->data["Sermon"]["post_id"] = $this->Sermon->Post->id; 
            if (!empty($this->data["Sermon"]["pastor_id"])) {
                $this->log("erasing speaker name because it was a Pastor", LOG_DEBUG);
                $this->data["Sermon"]["speaker_name"] = null;
            }
            if ($this->Sermon->save($this->data)) {
                $temp_dir = $this->data["Sermon"]["uuid"];
                $temp_audio = $this->AUDIO . "/$temp_dir";
                $temp_images = $this->IMAGES . "/$temp_dir";
                $doc_root = $this->remove_trailing_slash(env("DOCUMENT_ROOT"));

                if (file_exists($doc_root . $temp_audio)) {
                    $audio_dir = $this->AUDIO . "/" . $this->Sermon->id;
                    $this->rename_dir($doc_root . $temp_audio, $doc_root . $audio_dir);
                    $this->log("moved audio to permanent folder: $doc_root$audio_dir", LOG_DEBUG);
                } else {
                    $this->log("no audio to move, since folder doesn't exist: $doc_root$temp_audio", 
                            LOG_DEBUG);
                }

                if (file_exists($doc_root . $temp_images)) {
                    $images_dir = $this->IMAGES . "/" .  $this->Sermon->id;
                    $this->rename_dir($doc_root . $temp_images, $doc_root . $images_dir);
                    $this->log("moved images to permanent folder: $doc_root$images_dir", LOG_DEBUG);
                } else {
                    $this->log("no images to move, since folder doesn't exist: $doc_root$temp_images", 
                            LOG_DEBUG);
                }

                $this->log("Sermon successfully saved.", LOG_DEBUG);
                $this->Session->setFlash(__('The sermon has been saved', true));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->log("Sermon needs to be corrected, redirecting to form.", LOG_DEBUG);
                $this->Session->setFlash(__('The sermon could not be saved. Please, try again.', true));
            }
        } else {
            $this->data["Sermon"]["uuid"] = String::uuid();
        }

        $this->set("banner_type", 
                $this->requestAction("/urg_post/attachment_types/find_by_name/Banner"));
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
        $this->set("matches",$matches);
        $this->layout = "ajax";
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

    function upload_audio() {
        $this->log("uploading audio...", LOG_DEBUG);
        $this->upload($this->AUDIO);
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
}
?>
