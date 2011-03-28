<?php
App::import("Sanitize");
App::import("Component", "ImgLib.ImgLib");
App::import("Helper", "UrgSermon.Pastor");
class PastorsController extends UrgSermonAppController {
    var $AUDIO_WEBROOT = "audio";
    var $IMAGES_WEBROOT = "img";
    var $FILES_WEBROOT = "files";

    var $AUDIO = "/app/plugins/urg_post/webroot/audio";
    var $IMAGES = "/app/plugins/urg_post/webroot/img";
    var $FILES = "/app/plugins/urg_post/webroot/files";

    var $BANNER_SIZE = 700;
    var $PANEL_BANNER_SIZE = 460;

    var $name = 'Pastors';
    var $useTable = false;
    var $uses = array("Group", "Sermon");

    var $components = array("ImgLib");

    var $helpers = array("Time", "Pastor");

    function search($term = "") {
        $term = Sanitize::clean($term);
        $pastors_group = $this->Group->findByName("Pastors");

        $conditions = array();
        $conditions["Group.group_id"] = $pastors_group["Group"]["id"];

        if (strlen($term) >= 2) {
            $conditions["Group.name LIKE"] = "%$term%";
        }

        return $this->Group->find("all", array("conditions" => $conditions));
    }

    function view($slug = null) {
        $this->log("Entering view action", LOG_DEBUG);
        if (!$slug) {
            $this->Session->setFlash(__('Invalid sermon', true));
            $this->redirect(array('action' => 'index'));
        }
        $pastor = $this->Group->findBySlug($slug);

        $this->log("Viewing pastor: " . Debugger::exportVar($pastor, 3), 
                LOG_DEBUG);
        $about_pastor = $this->get_about($pastor["Group"]["name"]);
        $about = $this->get_about("Montreal Chinese Alliance Church");
        $this->set('pastor', $pastor);
        $this->set("about_pastor", $about_pastor);
        $this->set("activity", $this->get_recent_activity($pastor));
        $this->set("upcoming_events", $this->get_upcoming_sermons($pastor));
        $this->set("about", $about);

        $banners = $this->get_banners($about_pastor);
        if (empty($banners)) {
            $banners = $this->get_banners($about);
        }

        $this->set("banners", $banners);
    }

    function get_banners($about) {
        $this->loadModel("Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));

        $banner_type = $this->Attachment->AttachmentType->findByName("Banner");

        $banners = array();

        if (isset($about["Attachment"])) {
            foreach ($about["Attachment"] as $attachment) {
                if ($attachment["attachment_type_id"] == $banner_type["AttachmentType"]["id"])
                    array_push($banners, $this->get_image_path($attachment["filename"],
                                                               $about,
                                                               $this->BANNER_SIZE));
            }
        }

        return $banners;
    }

    function get_about($name) {
        $this->loadModel("Post");
        $this->Post->bindModel(array("belongsTo" => array("Group")));
        $this->Post->bindModel(array("hasMany" => array("Attachment")));

        $about_group = $this->Post->Group->findByName("About");

        $about = $this->Post->find("first", 
                array("conditions" => 
                        array("OR" => array(
                                "Group.name" => "About", 
                                "Group.group_id" => $about_group["Group"]["id"]),
                              "AND" => array("Post.title" => $name)
                        ),
                      "order" => "Post.id DESC"
                )
        );

        $this->log("about for group: $name" .  Debugger::exportVar($about, 3), LOG_DEBUG);

        return $about;
    }

    function get_recent_activity($pastor) {
        $this->Sermon->bindModel(array("belongsTo" => array("Post")));
        $sermons = $this->Sermon->find('all', 
                array("conditions" => array("Sermon.pastor_id" => $pastor["Group"]["id"],
                                            "Post.publish_timestamp < NOW()"),
                      "limit" => 10,
                      "order" => "Post.publish_timestamp DESC"));
        $activity = array();
        foreach ($sermons as $sermon) {
            array_push($activity, $sermon);
        }
        
        $this->log("pastor activity: " . Debugger::exportVar($activity, 3), LOG_DEBUG);

        return $activity;
    }

    function get_upcoming_sermons($pastor) {
        $this->Sermon->bindModel(array("belongsTo" => array("Post")));
        $this->Sermon->bindModel(array("belongsTo" => array(
                "Series" => array(
                    "className" => "Urg.Group",
                    "foreignKey" => "series_id"
                )
            )
        ));
        $sermons = $this->Sermon->find('all', 
                array("conditions" => array("Sermon.pastor_id" => $pastor["Group"]["id"],
                                            "Post.publish_timestamp > NOW()"),
                      "limit" => 10,
                      "order" => "Post.publish_timestamp"));
        
        $this->log("upcoming sermons: " . Debugger::exportVar($sermons, 3), LOG_DEBUG);

        return $sermons;
    }

    function get_image_path($filename, $post, $width, $height = 0) {
        $full_image_path = $this->get_doc_root($this->IMAGES) . "/" .  $post["Post"]["id"];
        $image = $this->ImgLib->get_image("$full_image_path/$filename", $width, $height, 'landscape'); 
        return "/urg_post/img/" . $post["Post"]["id"] . "/" . $image["filename"];
    }

    function get_doc_root($root = null) {
        $doc_root = $this->remove_trailing_slash(env('DOCUMENT_ROOT'));

        if ($root != null) {
            $root = $this->remove_trailing_slash($root);
            $doc_root .=  $root;
        }

        return $doc_root;
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
}
?>
