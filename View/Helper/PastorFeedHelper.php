<?php
App::uses("AbstractWidgetHelper", "Urg.Lib");
class PastorFeedHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time");
    var $widget_options = array("pastor", "pastor_feed");

    function build_widget() {
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        $title = $this->Html->tag("h2", 
                __(isset($this->options["title"]) ? $this->options["title"] : "Recent Activity"));

        return $title . $this->activity_feed($this->options["pastor"], $this->options["pastor_feed"]);
    }

    function activity_feed($pastor, $activity) {
        $feed = "";

        $actions = array();

        if ($this->options["add_sermon"]) {
            array_push($actions, $this->Html->link(__("Add a sermon"),
                                                   array("plugin" => "urg_sermon",
                                                         "controller" => "sermons",
                                                         "action" => "add")));
        }

        if ($this->options["add_article"]) {
            array_push($actions, $this->Html->link(__("Add an article"), 
                                                   array("plugin" => "urg_post",
                                                         "controller" => "posts",
                                                         "action" => "add",
                                                         $this->options["article_group_slug"])));
        }

        if (!empty($actions)) {
            $feed = $this->Html->div("", 
                                     $this->_View->element("bootstrap_dropdown", 
                                                           array("label" => __("Action", true),
                                                                 "items" => $actions,
                                                                 "class" => "btn-mini btn-inverse")),
                                     array("class" => "action-dropdown", "escape" => false));
        }

        foreach ($activity as $feed_item) {
            $feed_icon = $this->feed_icon($feed_item);
            $time = $this->Html->div("feed-timestamp",
                    $feed_icon . 
                    $this->Time->timeAgoInWords($feed_item["Post"]["publish_timestamp"], 'j/n/y', false, true));
            $feed .= $this->Html->tag("li", 
                                      $this->get_feed_message($pastor, $feed_item) . $time,
                                      array("class" => "activity-feed-item"));
        }

        return $this->Html->tag("ul", $feed, array("id" => "activity-feed"));
    }

    function get_feed_message($pastor, $feed_item) {
        $feed_message = "";

        $post_url = array("plugin" => "urg_post",
                          "controller" => "posts",
                          "action" => "view",
                          $feed_item["Post"]["id"],
                          $feed_item["Post"]["slug"]);

        if (isset($feed_item["Sermon"])) {
            $feed_message = sprintf(__("%s preached a sermon called %s."),
                    __($pastor["Group"]["name"]),
                    $this->Html->link($feed_item["Post"]["title"], $post_url));
        } else {
            $feed_message = sprintf(__("%s wrote an article called %s."),
                    __($pastor["Group"]["name"]),
                    $this->Html->link($feed_item["Post"]["title"], $post_url));
        }

        return $feed_message;
    }

    function feed_icon($feed_item) {
        $icon = null;
        if (isset($feed_item["Sermon"])) {
           $icon = $this->Html->image("/urg_sermon/img/icons/feed/media-microphone-alt.png",
                                      array("class" => "feed-icon")); 
        } else {
           $icon = $this->Html->image("/urg_sermon/img/icons/feed/cloud.png",
                                      array("class" => "feed-icon")); 
        }
        return $icon; 
    }
}
