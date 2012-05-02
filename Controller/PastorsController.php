<?php
App::uses("Sanitize", "Utility");
App::uses("ImgLibComponent", "ImgLib.Controller/Component");
App::uses("GroupController", "Urg.Controller");
App::uses("PastorHelper", "UrgSermon.View/Helper");
App::uses("UrgSermonAppController", "UrgSermon.Controller");
class PastorsController extends UrgSermonAppController {
    var $AUDIO_WEBROOT = "audio";
    var $IMAGES_WEBROOT = "img";
    var $FILES_WEBROOT = "files";

    var $AUDIO = "/app/Plugin/UrgPost/webroot/audio";
    var $IMAGES = "/app/Plugin/UrgPost/webroot/img";
    var $FILES = "/app/Plugin/UrgPost/webroot/files";

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
}
?>
