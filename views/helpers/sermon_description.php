<?php
class SermonDescriptionHelper extends AppHelper {
    var $helpers = array("Html", "Time");
    var $widget_options = array("sermon");

    function build($options = array()) {
        CakeLog::write("debug", "building sermon description widget");
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        return $this->description($options["sermon"]);
    }

    function description($sermon) {
        $description = "";
        if (trim($sermon["Sermon"]["description"]) != "") {
            $description = $this->Html->div("sermon-description", 
                                            $this->Html->tag("h2", __("Description", true)) . 
                                            $sermon["Sermon"]["description"]);
        }

        return $description;
    }
}
