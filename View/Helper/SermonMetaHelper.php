<?php
App::uses("AbstractWidgetHelper", "Urg.Lib");
class SermonMetaHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time", "EqualHeight");

    function build_widget() {
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        return $this->meta();
    }

    function meta() {
        $sermon = $this->options["sermon"];
        $attachments = $this->options["attachments"];
        $series = $this->Html->div("alpha grid_3 top-border bottom-border right-border " .
                "sermon-details", $this->item(__("From the series"), $sermon["Post"]["Group"]["name"]),
                array("id" => "sermon-series", "style" => "border-right-width: 0px"));

        $time = $this->Html->div("grid_3 top-border bottom-border left-border right-border " .
                "sermon-details", $this->item(__("Taken place on"), 
                $this->Time->format("F d, Y", $sermon["Post"]["publish_timestamp"])),
                array("id" => "sermon-date", "style" => "border-right-width: 0px"));

        $speaker = $this->Html->div("grid_3 top-border bottom-border left-border right-border sermon-details", 
                                    $this->item(__("Spoken by"), 
                                            $sermon["Pastor"]["name"] != "" ? 
                                                    $this->Html->link(__($sermon["Pastor"]["name"]), 
                                                                      array("plugin" => "urg", 
                                                                            "controller" => "groups", 
                                                                            "action" => "view", 
                                                                            $sermon["Pastor"]["slug"])) : 
                                                    __($sermon["Sermon"]["speaker_name"])),
                                    array("id" => "sermon-speaker", 
                                          "style" => "border-right-width: 0px"));

        $resources = $this->Html->div("omega grid_3 top-border bottom-border left-border " .
                "sermon-details", $this->item(__("Resources"), 
                $this->resource_list($sermon, $attachments)),
                array("id" => "sermon-resources", "style" => "border-right-width: 0px"));

        $admin_links = "";

        if ($this->options["can_edit"]) {
            $admin_links .= $this->Html->link(__("Edit"), array("plugin" => "urg_sermon",
                                                                              "controller" => "sermons",
                                                                              "action" => "edit",
                                                                              $this->options["sermon"]["Sermon"]["id"]));
        }

        if ($this->options["can_delete"]) {
            $admin_links .= $this->Html->link(__("Delete"),
                                              array("plugin" => "urg_sermon",
                                                    "controller" => "sermons",
                                                    "action" => "delete",
                                                    $this->options["sermon"]["Sermon"]["id"]),
                                              null,
                                              __("Are you sure you want to delete this?"));
        }
        
        return $this->Html->div("grid_12", 
                                $series . $time . $speaker . $resources, 
                                array("id" => "sermon-info")) . $this->Html->div("grid_12", $admin_links) . $this->js();
    }

    function js() {
        $js = $this->EqualHeight->equal_height(true);
        $js .= <<< EOT
        $("div.sermon-details").equalHeight();

        $("#sermon-resource-list li a").click(function() {
            pagePlayer.handleClick({
                target:document.getElementById($(this).attr("id") + "-player")
            });
            return false;
        });
EOT;

        return $this->Html->scriptBlock($js);
    }

    function resource_list($sermon, $attachments) {
        $list = "";

        if (isset($attachments["Documents"])) {
            foreach ($attachments["Documents"] as $filename=>$attachment_id) {
                $url = $this->Html->url("/urg_post/files/" .  
                        $sermon["Sermon"]["id"] . "/" . $filename); 
                $image_options = array("style"=>"height: 32px", 
                                       "alt"=>$filename, 
                                       "title"=>$filename); 
                $list .= $this->Html->tag("li", $this->Html->link(
                    $this->Html->image("/urg_sermon/img/icons/" . 
                            strtolower(substr($filename, strrpos($filename, ".") + 1, 
                            strlen($filename))) . ".png", $image_options), 
                            $url, array("escape" => false, "class" => "gdoc"))); 
            }
        }

        if (isset($attachments["Audio"])) {
            foreach ($attachments["Audio"] as $filename => $attachment_id) {
                $url = $this->Html->url("/urg_post/audio/" . 
                        $sermon["Sermon"]["id"] . "/" . $filename);
                $image_options = array("style"=>"height: 32px",
                                       "alt"=>$filename,
                                       "title"=>$filename);
                $list .= $this->Html->tag("li", $this->Html->link(
                    $this->Html->image("/urg_sermon/img/icons/" . 
                            strtolower(substr($filename, strrpos($filename, ".") + 1, 
                            strlen($filename))) . ".png", $image_options), $url,
                            array("escape" => false, "class" => "exclude sermon-audio",
                            "id" => "sermon-audio-link-" . $sermon["Post"]["id"])));
            }
        }

        return $this->Html->tag("ul", $list, array("id" => "sermon-resource-list"));
    }

    function item($heading, $value) {
        return $this->Html->tag("h3", $heading, array("class" => "sermon-details")) . $value;
    }
}
