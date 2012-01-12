<?php
App::import("Lib", "Urg.AbstractWidgetComponent");
class ArticleTitleComponent extends AbstractWidgetComponent {
    function build_widget() {
        $this->controller->loadModel("UrgPost.Post");
        $this->post = $this->controller->Post->findById($this->widget_settings["post_id"]);
        CakeLog::write("debug", "article for article title widget: " . Debugger::exportVar($this->post, 3));
        $this->set("post", $this->post);
        $this->set("title", isset($this->widget_settings["title"]) ? 
                            $this->widget_settings["title"] : $this->post["Post"]["title"]);
        $this->set("pastor", $this->get_pastor());
    }

    function get_pastor() {
        $group = $this->controller->Group->findById($this->post["Post"]["group_id"]);
        return array("Group" => $group["ParentGroup"]);
    }
}

