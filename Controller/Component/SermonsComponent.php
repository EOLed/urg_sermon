<?php
App::uses("AbstractWidgetComponent", "Urg.Lib");
class SermonsComponent extends AbstractWidgetComponent {
    function build_widget() {
        $settings = $this->widget_settings;
        Configure::load("config");

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
        $days_of_relevance = Configure::read("ActivityFeed.daysOfRelevance");
        $limit = isset($this->widget_settings["limit"]) ? $this->widget_settings["limit"] : Configure::read("ActivityFeed.limit");

        $options = array("order" => "Post.publish_timestamp DESC",
                         "conditions" => array("Post.publish_timestamp BETWEEN SYSDATE() - INTERVAL $days_of_relevance DAY AND SYSDATE()"),
                         "recursive" => 2,
                         "limit" => $limit);
        return $this->get_sermons($options, $pastor_id);
    }
    
    function get_upcoming_sermons($pastor_id = null) {
        $days_of_relevance = Configure::read("ActivityFeed.daysOfRelevance");
        $limit = isset($this->widget_settings["limit"]) ? $this->widget_settings["limit"] : Configure::read("ActivityFeed.limit");

        $options = array("order" => "Post.publish_timestamp ASC",
                         "conditions" => array("Post.publish_timestamp > NOW()"),
                         "recursive" => 2,
                         "limit" => $limit);
        return array_reverse($this->get_sermons($options, $pastor_id));
    }
}
