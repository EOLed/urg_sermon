<?php
App::import("Sanitize");
App::import("Component", "Cuploadify.Cuploadify");
class SermonsController extends UrgSermonAppController {
    var $components = array(
           "Auth" => array(
                   "loginAction" => array(
                           "plugin" => "urg",
                           "controller" => "users",
                           "action" => "login",
                           "admin" => false
                   )
           ), "Urg", "Cuploadify");
    var $name = 'Sermons';

    function index() {
        $this->Sermon->recursive = 0;
        $this->set('sermons', $this->paginate());
    }

    function view($id = null) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid sermon', true));
            $this->redirect(array('action' => 'index'));
        }
        $this->set('sermon', $this->Sermon->read(null, $id));
    }

    function add() {
        if (!empty($this->data)) {
            if ($this->data["Sermon"]["series_id"] == "") {
                $this->data["Sermon"]["series_id"] = $this->requestAction("/urg_sermon/series/create/" .
                        $this->data["Sermon"]["series_name"]);
            }

            $this->Sermon->Post->create();
            $this->data["Post"]["user_id"] = $this->Auth->user("id");
            $this->data["Post"]["group_id"] = $this->data["Sermon"]["series_id"];
            $post = $this->Sermon->Post->save($this->data);

            $this->Sermon->create();
            $this->data["Sermon"]["post_id"] = $this->Sermon->Post->id; 
            if (!empty($this->data["Sermon"]["pastor_id"])) {
                $this->data["Sermon"]["speaker_name"] = null;
            }
            if ($this->Sermon->save($this->data)) {
                $this->Session->setFlash(__('The sermon has been saved', true));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The sermon could not be saved. Please, try again.', true));
            }
        } else {
            $this->data["Sermon"]["uuid"] = String::uuid();
        }
        
        $posts = $this->Sermon->Post->find('list');
        $this->set(compact('posts'));
    }

    function edit($id = null) {
        if (!$id && empty($this->data)) {
            $this->Session->setFlash(__('Invalid sermon', true));
            $this->redirect(array('action' => 'index'));
        }
        if (!empty($this->data)) {
            if ($this->Sermon->save($this->data)) {
                $this->Session->setFlash(__('The sermon has been saved', true));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The sermon could not be saved. Please, try again.', true));
            }
        }
        if (empty($this->data)) {
            $this->data = $this->Sermon->read(null, $id);
        }
        $posts = $this->Sermon->Post->find('list');
        $this->set(compact('posts'));
    }

    function delete($id = null) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for sermon', true));
            $this->redirect(array('action'=>'index'));
        }
        if ($this->Sermon->delete($id)) {
            $this->Session->setFlash(__('Sermon deleted', true));
            $this->redirect(array('action'=>'index'));
        }
        $this->Session->setFlash(__('Sermon was not deleted', true));
        $this->redirect(array('action' => 'index'));
    }

    function autocomplete_speaker() {
        $term = Sanitize::clean($this->params["url"]["term"]);
        $matches = strlen($term) == 0 ? $this->suggest_speaker() : $this->search_speaker($term);
        $this->set("matches",$matches);
        $this->layout = "ajax";
    }
    
    function search_speaker($term) {
        $prepared_matches = array();

        $pastors = $this->requestAction("/urg_sermon/pastors/search/" . $this->params["url"]["term"]);
        foreach ($pastors as $pastor) {
            array_push($prepared_matches,
                    array("label"=> $pastor["Group"]["name"], "belongsToChurch"=>true,
                            "value"=>$pastor["Group"]["name"], "group_id"=>$pastor["Group"]["id"]));
        }

        $matches = $this->Sermon->query("SELECT DISTINCT speaker_name speaker_name " .
                "FROM sermons Sermon WHERE speaker_name LIKE '%$term%' ORDER BY speaker_name LIMIT 3");
        foreach ($matches as $match) {
            array_push($prepared_matches, 
                    array("label"=>$match["Sermon"]["speaker_name"], 
                            "value"=>$match["Sermon"]["speaker_name"]));
        }

        return $prepared_matches;
    }

    function suggest_speaker() {
        $prepared_matches = array();

        $pastors = $this->requestAction("/urg_sermon/pastors/search/" . $this->params["url"]["term"]);
        foreach ($pastors as $pastor) {
            array_push($prepared_matches,
                    array("label"=> $pastor["Group"]["name"], "belongsToChurch"=>true,
                            "value"=>$pastor["Group"]["name"], "group_id"=>$pastor["Group"]["id"]));
        }
        
        return $prepared_matches;
    }

    function upload_images() {
        $this->log("uploading files...", LOG_DEBUG);
        $options = array("root" => "/app/plugins/urg_sermon/webroot/img" );
        $this->log("uploading options: " . Debugger::exportVar($options), LOG_DEBUG);
        $this->Cuploadify->upload($options);
        $this->log("done uploading.", LOG_DEBUG);
    }
}
?>
