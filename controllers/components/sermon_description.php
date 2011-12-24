<?php
App::import("Component", "UrgSermon.BaseSermon");
class SermonDescriptionComponent extends BaseSermonComponent {
    function build_widget() {
        parent::build_widget();
        $this->post = $this->controller->Post->findById($this->widget_settings["post_id"]);
        $this->set("can_edit", $this->can_edit());
        $this->set("can_delete", $this->can_delete());
        $this->set("group_slug", $this->get_group_slug());
    }

    function get_group_slug() {
        $group = $this->controller->Group->findById($this->post["Post"]["group_id"]);
        return $group["Group"]["slug"];
    }

    function can_edit() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_sermon", 
                                                        "controller"=>"sermons", 
                                                        "action"=>"edit"), 
                                                  $this->post["Post"]["group_id"]);
    }

    function can_delete() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_sermon", 
                                                        "controller"=>"sermons", 
                                                        "action"=>"delete"), 
                                                  $this->post["Post"]["group_id"]);
    }
}

