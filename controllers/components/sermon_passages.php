<?php
class SermonPassagesComponent extends Object {
    var $controller = null;
    var $settings = null;

    function initialize(&$controller, $settings = array()) {
        $this->controller =& $controller;
        $this->settings = $settings;
    }

    function build($widget_id) {
        $settings = $this->settings[$widget_id];
        $this->bindModels();

        $sermon = $this->controller->Sermon->findByPostId($settings["post_id"]);
        $this->controller->set("sermon_$widget_id", $sermon);

        CakeLog::write("debug", "sermon for sermon passages widget: " . Debugger::exportVar($sermon, 3));
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
}

