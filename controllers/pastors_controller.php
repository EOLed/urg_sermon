<?php
App::import("Sanitize");
class PastorsController extends UrgSermonAppController {
    var $name = 'Pastors';
    var $useTable = false;
    var $uses = array("Group");

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
}
    
?>
