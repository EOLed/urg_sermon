<?php
App::import("Lib", "Urg.AbstractWidgetHelper");
class SermonSeriesHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time");

    function build_widget() {
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        return $this->series();
    }

    function series() {
        $sermon = $this->options["sermon"];
        $series_sermons = $this->options["series_sermons"];
        $sermon_list = "";
        $counter = 0;
        if (isset($sermon["Post"]["Group"]) && $sermon["Post"]["Group"]["name"] != "No Series") {
            $counter++;
            foreach ($series_sermons as $series_sermon) {
                $item = "";
                $item .= $this->Html->link($series_sermon["Post"]["title"], 
                                           array("plugin" => "urg_post",
                                                 "controller" => "posts",
                                                 "action" => "view", 
                                                 $series_sermon["Post"]["id"]));
                $item .= $this->Html->div("series-sermon-details",
                        sprintf(__("by %s on %s", true),
                                $this->speaker_name($series_sermon),
                                $this->Time->format("n/j/y", $series_sermon['Post']['publish_timestamp'])));
                $item = $this->Html->tag("li", $item, array("class" => "series-sermon-list-item " . 
                                                                       ($counter++ % 2 ? "even" : "")));;
                $sermon_list .= $item;
            }

            $sermon_list = $this->Html->tag("ol", $sermon_list, array("class" => "series-sermon-list"));
            $title = $this->Html->tag("h2", $sermon["Post"]["Group"]["name"]);
            $sermon_list = $this->Html->div("series", $title . $sermon_list);
        }

        return $sermon_list;
    }

    function speaker_name($sermon) {
        $speaker = $sermon["Pastor"]["name"] != null ? 
                $sermon["Pastor"]["name"] : $sermon["Sermon"]["speaker_name"];
        return $speaker;
    }
}

