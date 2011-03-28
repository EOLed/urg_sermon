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
        $this->set("activity", $this->get_recent_activity($pastor));
        $this->set("banners", array());
    }

    function get_recent_activity($pastor) {
        $this->Sermon->bindModel(array("belongsTo" => array("Post")));
        $sermons = $this->Sermon->find('all', 
                array("conditions" => array("Sermon.pastor_id" => $pastor["Group"]["id"],
                                            "Post.publish_timestamp !=" => null),
                      "limit" => 10,
                      "order" => "Post.publish_timestamp DESC"));
        $activity = array();
        foreach ($sermons as $sermon) {
            array_push($activity, $sermon);
        }
        
        $this->log("pastor activity: " . Debugger::exportVar($activity, 3));

        return $activity;
    }
}
?>
