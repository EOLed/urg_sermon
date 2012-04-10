<?php
App::uses("AbstractWidgetComponent", "Urg.Controller/Component");
class BaseSermonComponent extends AbstractWidgetComponent {
    var $sermon = null;

    function build_widget() {
        $this->bindModels();

        $this->sermon = $this->controller->Sermon->find("first", 
                array("conditions" => array("Post.id" => $this->widget_settings["post_id"]),
                      "recursive" => 2));
        $this->set("sermon", $this->sermon);
        $this->set("can_edit", $this->can_edit());
        $this->set("can_delete", $this->can_delete());
        $this->set("can_add", $this->can_add());

        CakeLog::write("debug", "sermon for sermon widget: " . Debugger::exportVar($this->sermon, 3));
    }

    function can_edit() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_sermon", 
                                                        "controller"=>"sermons", 
                                                        "action"=>"edit"), 
                                                  $this->sermon["Post"]["group_id"]);
    }

    function can_add() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_sermon", 
                                                        "controller"=>"sermons", 
                                                        "action"=>"add"));
    }

    function can_delete() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_sermon", 
                                                        "controller"=>"sermons", 
                                                        "action"=>"delete"), 
                                                  $this->sermon["Post"]["group_id"]);
    }

    function bindModels() {
        $this->controller->loadModel("UrgSermon.Sermon");
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

        $this->set("attachments", $attachments);

        CakeLog::write("debug", "attachments for sermon widget: " . Debugger::exportVar($attachments, 3));
    }

    function set_sermon_series($sermon) {
        $this->bindModels();

        $series = $this->controller->Sermon->find("all",
                array(  "conditions" => array("Post.group_id" => $sermon["Post"]["Group"]["id"],
                                              "Post.publish_timestamp < SYSDATE()"),
                        "order" => array("Post.publish_timestamp"),
                        "recursive" => 2
                )
        );

        CakeLog::write(LOG_DEBUG, "Related sermons: " . Debugger::exportVar($series, 3));

        $this->set("series_sermons", $series);
    }

    function bind_attachments() {
        $this->controller->loadModel("UrgPost.Attachment");
    }
}


