<?php
class SermonsHelper extends AppHelper {
    var $helpers = array("Html", "Time");
    var $widget_options = array("upcoming_sermons", "past_sermons");

    function build($options = array()) {
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        $title = $this->Html->tag("h2", __("Sermon Schedule", true));
        return $this->Html->div("upcoming-events", $title . 
                $this->upcoming_sermons($options["upcoming_sermons"]) .
                $this->upcoming_sermons($options["past_sermons"]));
    }

    function upcoming_sermons($sermons) {
        $upcoming_events = "";
        foreach ($sermons as $sermon) {
            $sermon_info = $this->Html->div("upcoming-info",
                    $sermon["Series"]["name"] . " - " . $sermon["Sermon"]["passages"]);
            $time = $this->Html->div("upcoming-timestamp",
                    $this->Time->format("F d, Y", $sermon["Post"]["publish_timestamp"]));
            $upcoming_events .= $this->Html->tag("li", $time . $sermon["Post"]["title"] . $sermon_info);
        }

        return $this->Html->tag("ul", $upcoming_events, array("id" => "upcoming-events"));
    }
}
