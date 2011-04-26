<?php
class PastorHelper extends AppHelper {
    var $helpers = array("Html", "Time");

    function activity_feed($pastor, $activity) {
        $feed = "";
        foreach ($activity as $feed_item) {
            $feed_icon = $this->feed_icon($pastor, $feed_item);
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

    function upcoming_events($pastor, $sermons) {
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
