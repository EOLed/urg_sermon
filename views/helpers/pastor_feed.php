<?php
class PastorFeedHelper extends AppHelper {
    var $helpers = array("Html", "Time");
    var $widget_options = array("pastor", "pastor_feed");

    function build($options = array()) {
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        $title = $this->Html->tag("h2", 
                __(isset($options["title"]) ? $options["title"] : "Recent Activity", true));

        return $title . $this->activity_feed($options["pastor"], $options["pastor_feed"]);
    }

    function activity_feed($pastor, $activity) {
        $feed = "";
        foreach ($activity as $feed_item) {
            $feed_icon = $this->feed_icon($feed_item);
            $time = $this->Html->div("feed-timestamp",
                    $feed_icon . 
                    $this->Time->timeAgoInWords($feed_item["Post"]["publish_timestamp"], 'j/n/y', false, true));
            $feed .= $this->Html->tag("li", $this->get_feed_message($pastor, $feed_item) . $time);
        }

        return $this->Html->tag("ul", $feed, array("id" => "activity-feed"));
    }

    function get_feed_message($pastor, $feed_item) {
        $feed_message = "";

        if (isset($feed_item["Sermon"])) {
            $feed_message = sprintf(__("%s preached a sermon called %s.", true),
                    $pastor["Group"]["name"],
                    $this->Html->link($feed_item["Post"]["title"], 
                            "/urg_sermon/sermons/view/" . $feed_item["Sermon"]["id"]));
        } else {
            $feed_message = sprintf(__("%s wrote an article called %s.", true),
                    $pastor["Group"]["name"],
                    $this->Html->link($feed_item["Post"]["title"], 
                            "/urg_post/posts/view/" . $feed_item["Post"]["id"] . "/" .
                            $feed_item["Post"]["slug"]));
        }

        return $feed_message;
    }

    function feed_icon($feed_item) {
        $icon = null;
        if (isset($feed_item["Sermon"])) {
           $icon = $this->Html->image("/urg_sermon/img/icons/feed/media-microphone-alt.png",
                                      array("class" => "feed-icon")); 
        } else {
           $icon = $this->Html->image("/urg_sermon/img/icons/feed/cloud-alt.png",
                                      array("class" => "feed-icon")); 
        }
        return $icon; 
    }
}
