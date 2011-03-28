<?php
class PastorHelper extends AppHelper {
    var $helpers = array("Html", "Time");

    function activity_feed($pastor, $activity) {
        $feed = "";
        foreach ($activity as $feed_item) {
            $feed_icon = $this->feed_icon($feed_item);
            $time = $this->Html->div("feed-timestamp",
                    $feed_icon . 
                    $this->Time->timeAgoInWords($feed_item["Post"]["publish_timestamp"]));
            $feed .= $this->Html->tag("li", 
                    sprintf(__("%s preached a sermon called %s.", true),
                            $pastor["Group"]["name"],
                            $this->Html->link($feed_item["Post"]["title"], 
                                    "/urg_sermon/sermons/view/" . $feed_item["Sermon"]["id"])) . $time);
        }

        return $this->Html->tag("ul", $feed, array("id" => "activity-feed"));
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
