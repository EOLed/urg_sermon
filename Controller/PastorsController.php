<?php
App::uses("Sanitize", "Utility");
App::uses("ImgLibComponent", "ImgLib.Controller/Component");
App::uses("GroupController", "Urg.Controller");
App::uses("PastorHelper", "UrgSermon.View/Helper");
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
    var $uses = array("Urg.Group", "UrgSermon.Sermon");

    var $components = array("ImgLib.ImgLib");

    var $helpers = array("Time", "UrgSermon.Pastor");

    function search($term = "") {
        $term = Sanitize::clean($term);
        $pastors_group = $this->Group->find("first", array("conditions" => array("Group.name" => "Pastors")));

        $conditions = array();
        $conditions["Group.parent_id"] = $pastors_group["Group"]["id"];

        if (strlen($term) >= 2) {
            $conditions["Group.name LIKE"] = "%$term%";
        }

        return $this->Group->find("all", array("conditions" => $conditions));
    }

    function view($slug = null) {
        $this->log("Entering view action", LOG_DEBUG);
        if (!$slug) {
            $this->Session->setFlash(__('Invalid sermon'));
            $this->redirect(array('action' => 'index'));
        }
        $pastor = $this->Group->findBySlug($slug);

        $this->log("Viewing pastor: " . Debugger::exportVar($pastor, 3), 
                LOG_DEBUG);
        $this->set('pastor', $pastor);
        $this->set("activity", $this->get_recent_activity($pastor));
        $this->set("upcoming_events", $this->get_upcoming_sermons($pastor));

        $this->set("title_for_layout", __("Pastors") . " &raquo; " . $pastor["Group"]["name"]);
    }

    function get_about($name) {
        $this->loadModel("Post");
        $this->Post->bindModel(array("belongsTo" => array("Group")));
        $this->Post->bindModel(array("hasMany" => array("Attachment")));

        $about_group = $this->Post->Group->findByName("About");

        $about = $this->Post->find("first", 
                array("conditions" => 
                        array("OR" => array(
                                "Post.title" => "About", 
                                "Group.parent_id" => $about_group["Group"]["id"]),
                              "AND" => array("Post.title" => $name)
                        ),
                      "order" => "Post.id DESC"
                )
        );

        if ($about === false) {
            $this->Post->bindModel(array("belongsTo" => array("Group")));
            $this->Post->bindModel(array("hasMany" => array("Attachment")));

            $about = $this->Post->find("first", 
                array("conditions" => 
                        array(
                            "AND" => array("Post.title" => "About", "Group.name" => $name)
                        ),
                      "order" => "Post.publish_timestamp DESC"
                )
            );
        }

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

        $unsorted_activity = array();
        foreach ($sermons as $sermon) {
            $unsorted_activity[$sermon["Post"]["publish_timestamp"]] = $sermon;
        }

        $article_group = $this->get_article_group($pastor);
        $this->log("article group for pastor: " . Debugger::exportVar($article_group, 3), LOG_DEBUG);
        $this->loadModel("Post");
        $this->Post->bindModel(array("belongsTo" => array("Group")));
        $this->Post->bindModel(array("hasMany" => array("Attachment")));
        
        $articles = $this->Post->find("all",
                array("conditions" => array("Post.group_id" => $article_group["Group"]["id"],
                                            "Post.publish_timestamp < NOW()"),
                      "limit" => 10,
                      "order" => "Post.publish_timestamp DESC"));

        foreach ($articles as $article) {
            $unsorted_activity[$article["Post"]["publish_timestamp"]] = $article;
        }

        krsort($unsorted_activity);

        $activity = array_values($unsorted_activity);

        $this->log("pastor activity: " . Debugger::exportVar($activity, 3), LOG_DEBUG);

        return $activity;
    }

    function get_article_group($pastor) {
        return $this->Group->find("first", array("conditions" => 
                array("Group.name" => "Articles",
                      "Group.parent_id" => $pastor["Group"]["id"])));
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
