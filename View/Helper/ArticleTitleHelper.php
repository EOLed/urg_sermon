<?php
App::uses("AbstractWidgetHelper", "Urg.Lib");
class ArticleTitleHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time");

    function build_widget() {
        CakeLog::write(LOG_DEBUG, "building article title  widget with options: " .
                                  Debugger::exportVar($this->options, 3));
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        return $this->title_widget($this->options["title"], $this->options["post"]);
    }

    function title_widget() {
        $post = $this->options["post"];
        $title = $this->Html->div("", $post["Post"]["title"]);
        $post_info = $this->Html->div("", 
                                       __(sprintf("by %s on %s", 
                                                  $this->Html->link($this->options["pastor"]["Group"]["name"],
                                                              array("plugin" => "urg",
                                                                    "controller" => "groups",
                                                                    "action" => "view",
                                                                    $this->options["pastor"]["Group"]["slug"])),
                                                  date("F j, Y h:i A", strtotime($post["Post"]["publish_timestamp"]))),
                                          true), array("id" => "post-info"));

        return $this->Html->div("span12", $this->Html->div("page-title", $title . $post_info, array("id" => "article-title")));
    }
}

