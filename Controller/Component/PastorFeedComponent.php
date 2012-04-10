<?php
App::uses("AbstractWidgetComponent", "Urg.Controller/Component");
class PastorFeedComponent extends AbstractWidgetComponent {
    function build_widget() {
        $settings = $this->settings[$this->widget_id];
        $feed = $this->get_pastor_feed(isset($settings["group_id"]) ? $settings["group_id"] : null);
        $pastor = $this->controller->Group->findById($settings["group_id"]);
        $this->set("pastor_feed", $feed);
        $this->set("pastor", $pastor);
        $this->set("add_sermon", $this->can_add_sermon());
        $this->set("add_article", $this->can_add_article());
    }

    function can_add_sermon() {
        $series_group = $this->controller->Group->findByName("Series");
        return $this->controller->Urg->has_access(array("plugin"=>"urg_sermon", 
                                                        "controller"=>"sermons", 
                                                        "action"=>"add"), 
                                                  $series_group["Group"]["id"]);
    }

    function can_add_article() {
        $pastor = $this->controller->Group->findById($this->widget_settings["group_id"]);
        $article_group = $this->get_article_group($pastor);
        $this->set("article_group_slug", $article_group["Group"]["slug"]);
        return $this->controller->Urg->has_access(array("plugin"=>"urg_post", 
                                                        "controller"=>"posts", 
                                                        "action"=>"add"), 
                                                  $article_group["Group"]["id"]);
    }

    function bindModels() {
        $this->controller->loadModel("UrgSermon.Sermon");
        $this->controller->Sermon->bindModel(array(
                "belongsTo" => array('Post' => array('className' => 'UrgPost.Post',
                                                     'foreignKey' => 'post_id',
                                                     'conditions' => '',
                                                     'fields' => '',
                                                     'order' => ''),
                                     'Pastor' => array('className' => 'Urg.Group',
                                                       'foreignKey' => 'pastor_id')
        )));
    }

    function get_article_group($pastor) {
        return $this->controller->Group->find("first", array("conditions" => 
                array("Group.name" => "Articles",
                      "Group.parent_id" => $pastor["Group"]["id"])));
    }

    function get_pastor_feed($pastor_id) {
        $pastor = $this->controller->Group->findById($pastor_id);
        $this->bindModels();
        $this->controller->Sermon->bindModel(array("belongsTo" => array("Post")));

        Configure::load("config");
        $days_of_relevance = Configure::read("ActivityFeed.daysOfRelevance");
        $limit = isset($this->widget_settings["limit"]) ? $this->widget_settings["limit"] : Configure::read("ActivityFeed.limit");

        $sermons = $this->controller->Sermon->find('all', 
                array("conditions" => array("Sermon.pastor_id" => $pastor["Group"]["id"],
                                            "Post.publish_timestamp BETWEEN SYSDATE() - INTERVAL $days_of_relevance DAY AND SYSDATE()"),
                      "limit" => $limit,
                      "order" => "Post.publish_timestamp DESC"));
        $activity = array();

        $unsorted_activity = array();
        foreach ($sermons as $sermon) {
            $unsorted_activity[$sermon["Post"]["created"]] = $sermon;
        }

        $article_group = $this->get_article_group($pastor);
        CakeLog::write("debug", "article group for pastor: " . Debugger::exportVar($article_group, 3));
        $this->controller->loadModel("UrgPost.Post");
        $this->controller->Post->bindModel(array("belongsTo" => array("Group")));
        $this->controller->Post->bindModel(array("hasMany" => array("Attachment")));
        
        $articles = $this->controller->Post->find("all",
                array("conditions" => array("Post.group_id" => $article_group["Group"]["id"],
                                            "Post.publish_timestamp BETWEEN SYSDATE() - INTERVAL $days_of_relevance DAY AND SYSDATE()"),
                      "limit" => $limit,
                      "order" => "Post.publish_timestamp DESC"));

        foreach ($articles as $article) {
            $unsorted_activity[$article["Post"]["publish_timestamp"]] = $article;
        }

        krsort($unsorted_activity);

        $activity = array_values($unsorted_activity);

        CakeLog::write("debug", "pastor activity: " . Debugger::exportVar($activity, 3));

        return $activity;
    }
}
