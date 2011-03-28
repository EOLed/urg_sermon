<?php
App::import("Sanitize");
App::import("Helper", "UrgSermon.Pastor");
class PastorsController extends UrgSermonAppController {
    var $name = 'Pastors';
    var $useTable = false;
    var $uses = array("Group", "Sermon");

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
        $this->set('pastor', $pastor);
        $this->set("about", $this->get_about($pastor));
        $this->set("activity", $this->get_recent_activity($pastor));
        $this->set("upcoming_events", $this->get_upcoming_sermons($pastor));
        $this->set("banners", array());
    }

    function get_about($pastor) {
        $this->loadModel("Post");
        $this->Post->bindModel(array("belongsTo" => array("Group")));

        $about_group = $this->Post->Group->findByName("About");

        $about = $this->Post->find("first", 
                array("conditions" => array("OR" => 
                        array("Group.name" => "About", "Group.group_id" => $about_group["Group"]["id"]),
                    "AND" => array("Post.title" => $pastor["Group"]["name"])))
        );

        $this->log("about for pastor: " . $pastor["Group"]["name"] . " " . 
                Debugger::exportVar($about, 3), LOG_DEBUG);

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
}
?>
