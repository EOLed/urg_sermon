<?php
App::import("Lib", "Urg.AbstractWidgetComponent");
class SermonsComponent extends AbstractWidgetComponent {
    function build_widget() {
        $settings = $this->widget_settings;
        $upcoming = $this->get_upcoming_sermons(
                isset($settings["pastor_id"]) ? $settings["pastor_id"] : null);
        $past = $this->get_past_sermons(
                isset($settings["pastor_id"]) ? $settings["pastor_id"] : null);
        $this->set("upcoming_sermons", $upcoming);
        $this->set("past_sermons", $past);
        $this->set("can_add", $this->can_add());
    }

    function can_add() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_sermon", 
                                                        "controller"=>"sermons", 
                                                        "action"=>"add"));
    }

    function bindModels() {
        $this->controller->loadModel("Sermon");
        $this->controller->Sermon->bindModel(array(
                "belongsTo" => array('Post' => array('className' => 'UrgPost.Post',
                                                     'foreignKey' => 'post_id',
                                                     'conditions' => '',
                                                     'fields' => '',
                                                     'order' => ''),
                                     'Pastor' => array('className' => 'Urg.Group',
                                                       'foreignKey' => 'pastor_id')
        )));
    }

    function get_sermons($options, $pastor_id = null) {
        $this->bindModels();
        if ($pastor_id != null) {
            $options["conditions"]["Sermon.pastor_id"] = $pastor_id;
        }

        $sermons = $this->controller->Sermon->find("all", $options);
        
        $this->log("upcoming sermons: " . Debugger::exportVar($sermons, 3), LOG_DEBUG);

        return $sermons;
    }

    function get_past_sermons($pastor_id = null) {
        $options = array("order" => "Post.publish_timestamp DESC",
                         "conditions" => array("Post.publish_timestamp < NOW()"),
                         "recursive" => 2,
                         "limit" => 5);
        return $this->get_sermons($options, $pastor_id);
    }
    
    function get_upcoming_sermons($pastor_id = null) {
        $options = array("order" => "Post.publish_timestamp ASC",
                         "conditions" => array("Post.publish_timestamp > NOW()"),
                         "recursive" => 2,
                         "limit" => 5);
        return array_reverse($this->get_sermons($options, $pastor_id));
    }
}
