<?php
class SermonsComponent extends Object {
    var $controller = null;
    var $settings = null;

    function initialize(&$controller, $settings = array()) {
        $this->controller =& $controller;
        $this->settings = $settings;
    }

    function build($widget_id) {
        $settings = $this->settings[$widget_id];
        $upcoming = $this->get_upcoming_sermons(
                isset($settings["pastor_id"]) ? $settings["pastor_id"] : null);
        $past = $this->get_past_sermons(
                isset($settings["pastor_id"]) ? $settings["pastor_id"] : null);
        $this->controller->set("upcoming_sermons_$widget_id", $upcoming);
        $this->controller->set("past_sermons_$widget_id", $past);
    }

    function bindModels() {
        $this->controller->loadModel("Sermon");
        $this->controller->Sermon->bindModel(array(
                "belongsTo" => array('Post' => array('className' => 'UrgPost.Post',
                                                     'foreignKey' => 'post_id',
                                                     'conditions' => '',
                                                     'fields' => '',
                                                     'order' => ''),
                                     'Series' => array('className' => 'Urg.Group',
                                                       'foreignKey' => 'series_id'),
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
                         "limit" => 5);
        return $this->get_sermons($options, $pastor_id);
    }
    
    function get_upcoming_sermons($pastor_id = null) {
        $options = array("order" => "Post.publish_timestamp ASC",
                         "conditions" => array("Post.publish_timestamp > NOW()"),
                         "limit" => 5);
        return array_reverse($this->get_sermons($options, $pastor_id));
    }
}
