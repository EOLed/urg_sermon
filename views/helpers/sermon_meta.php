<?php
class SermonMetaHelper extends AppHelper {
    var $helpers = array("Html", "Time", "EqualHeight");
    var $widget_options = array("sermon", "attachments");

    function build($options = array()) {
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        return $this->meta($options["sermon"], $options["attachments"]);
    }

    function meta($sermon, $attachments) {
        $series = $this->Html->div("alpha grid_3 top-border bottom-border right-border " .
                "sermon-details", $this->item(__("From the series", true), $sermon["Series"]["name"]),
                array("id" => "sermon-series", "style" => "border-right-width: 0px"));

        $time = $this->Html->div("grid_3 top-border bottom-border left-border right-border " .
                "sermon-details", $this->item(__("Taken place on", true), 
                $this->Time->format("F d, Y", $sermon["Post"]["publish_timestamp"])),
                array("id" => "sermon-date", "style" => "border-right-width: 0px"));

        $speaker = $this->Html->div("grid_3 top-border bottom-border left-border right-border " .
                "sermon-details", $this->item(__("Spoken by", true), 
                $sermon["Pastor"]["name"] != "" ? $sermon["Pastor"]["name"] : 
                $sermon["Sermon"]["speaker_name"]),
                array("id" => "sermon-speaker", "style" => "border-right-width: 0px"));

        $resources = $this->Html->div("omega grid_3 top-border bottom-border left-border " .
                "sermon-details", $this->item(__("Resources", true), 
                $this->resource_list($sermon, $attachments)),
                array("id" => "sermon-resources", "style" => "border-right-width: 0px"));
        
        return $this->Html->div("grid_12", 
                                $series . $time . $speaker . $resources, 
                                array("id" => "sermon-info")) . $this->js();
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
                $url = $this->Html->url("/urg_sermon/files/" .  
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
                $url = $this->Html->url("/urg_sermon/audio/" . 
                        $sermon["Sermon"]["id"] . "/" . $filename);
                $image_options = array("style"=>"height: 32px",
                                       "alt"=>$filename,
                                       "title"=>$filename);
                $list .= $this->Html->tag("li", $this->Html->link(
                    $this->Html->image("/urg_sermon/img/icons/" . 
                            strtolower(substr($filename, strrpos($filename, ".") + 1, 
                            strlen($filename))) . ".png", $image_options), $url,
                            array("escape" => false, "class" => "exclude sermon-audio",
                            "id" => "sermon-audio-link-" . $sermon["Sermon"]["id"])));
            }
        }

        return $this->Html->tag("ul", $list, array("id" => "sermon-resource-list"));
    }

    function item($heading, $value) {
        return $this->Html->tag("h3", $heading, array("class" => "sermon-details")) . $value;
    }
}
