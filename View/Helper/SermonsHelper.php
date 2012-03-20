<?php
App::import("Lib", "Urg.AbstractWidgetHelper");
class SermonsHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time");

    function build_widget() {
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        $title = $this->Html->tag("h2", __("Sermon Schedule"));
        $admin_links = "";

        if ($this->options["can_add"]) {
            $admin_links = $this->Html->div("", $this->Html->link(__("Add Sermon..."),
                                                                  array("plugin" => "urg_sermon",
                                                                        "controller" => "sermons",
                                                                        "action" => "add")));
        }
      
        return $this->Html->div("upcoming-events", $title . $admin_links .
                $this->upcoming_sermons("upcoming-sermons", $this->options["upcoming_sermons"], false) .
                $this->upcoming_sermons("past-sermons", $this->options["past_sermons"]));
    }

    function upcoming_sermons($id, $sermons, $enabled = true) {
        $upcoming_events = "";
        foreach ($sermons as $sermon) {
            $speaker = __(isset($sermon["Pastor"]["name"]) ? $sermon["Pastor"]["name"] : $sermon["Sermon"]["speaker_name"]);
            $series = $sermon["Post"]["Group"]["name"];
            $sermon_info = $this->Html->div("upcoming-info", $speaker);
            $post_title = $sermon["Post"]["title"];
            if ($enabled) {
                $post_title = $this->Html->link($post_title, array("plugin" => "urg_post",
                                                                   "controller" => "posts",
                                                                   "action" => "view",
                                                                   $sermon["Post"]["id"],
                                                                   $sermon["Post"]["slug"]));
            }
            $time = $this->Html->div("upcoming-timestamp", 
                                     $this->Time->format("F d, Y @ g:i A", $sermon["Post"]["publish_timestamp"]));
            $upcoming_events .= $this->Html->tag("li", $time . $post_title . $sermon_info);
        }

        $js = "";
        if ($enabled) {
            $js = $this->Html->scriptBlock($this->js($id));
        }

        return $this->Html->tag("ul", $upcoming_events, array("id" => $id)) . $js;
    }

    function js($id) {
        return '
            $("#' . $id . '-events li").click(function() {
                window.location = $(this).find("a").attr("href");
            });
        ';
    }
}
