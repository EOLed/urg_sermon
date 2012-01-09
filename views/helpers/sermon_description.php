<?php
App::import("Lib", "Urg.AbstractWidgetHelper");
App::import("Helper", "Markdown.Markdown");
class SermonDescriptionHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time", "Markdown");

    function build_widget() {
        CakeLog::write("debug", "building sermon description widget");
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        return $this->description();
    }

    function description() {
        $sermon = $this->options["sermon"];
        $description = "";
        if (trim($sermon["Sermon"]["description"]) != "") {
            $description = $this->Html->div("sermon-description", 
                                            $this->Html->tag("h2", __("Description", true)) . 
                                            $this->Markdown->html($sermon["Sermon"]["description"]));
        }

        return $description;
    }
}
