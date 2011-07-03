<?php
class BaseSermonComponent extends Object {
    var $controller = null;
    var $settings = null;
    var $sermon = null;
    var $widget_id = null;

    function initialize(&$controller, $settings = array()) {
        $this->controller =& $controller;
        $this->settings = $settings;
    }

    function build($widget_id) {
        $this->widget_id = $widget_id;
        $settings = $this->settings[$widget_id];
        $this->bindModels();

        $this->sermon = $this->controller->Sermon->findByPostId($settings["post_id"]);
        $this->controller->set("sermon_$widget_id", $this->sermon);

        CakeLog::write("debug", "sermon for sermon widget: " . Debugger::exportVar($this->sermon, 3));
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

    function set_attachments($sermon) {
        $attachments = $this->controller->Attachment->find("list", 
                array(  "conditions" => array("AND" => array(
                                "AttachmentType.name" => array("Banner", "Audio", "Documents"),
                                "Attachment.post_id" => $sermon["Post"]["id"])
                        ),
                        "fields" => array(  "Attachment.filename", 
                                            "Attachment.id",
                                            "AttachmentType.name"
                                    ),
                        "joins" => array(   
                                array(  "table" => "attachment_types",
                                        "alias" => "AttachmentType",
                                        "type" => "LEFT",
                                        "conditions" => array(
                                            "AttachmentType.id = Attachment.attachment_type_id"
                                        )
                                )
                        )
               )
        );

        $this->controller->set("attachments_" . $this->widget_id, $attachments);

        CakeLog::write("debug", "attachments for sermon meta widget: " . 
                                Debugger::exportVar($attachments, 3));
    }

    function set_sermon_series($sermon) {
        $this->bindModels();

        $series = $this->controller->Sermon->find("all",
                array(  "conditions" => array("Sermon.series_id" => $sermon["Series"]["id"]),
                        "order" => array("Post.publish_timestamp")
                )
        );

        CakeLog::write(LOG_DEBUG, "Related sermons: " . Debugger::exportVar($series, 3));

        $this->controller->set("series_sermons_" . $this->widget_id, $series);
    }

    function bind_attachments() {
        $this->controller->loadModel("Attachment");
    }
}


