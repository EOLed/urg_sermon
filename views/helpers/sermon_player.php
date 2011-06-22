<?php
App::import("Helper", "Sm2.SoundManager2");
class SermonPlayerHelper extends AppHelper {
    var $helpers = array("Html", "Time", "Sm2.SoundManager2");
    var $widget_options = array("sermon", "attachments");

    function build($options = array()) {
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        return $this->player($options["sermon"], $options["attachments"]);
    }

    function player($sermon, $attachments) {
        $player = "";
        if (isset($attachments["Audio"])) {
            $player .= "<div class='grid_12 sermon-audio'>";
            $playlist = array();
            foreach ($attachments["Audio"] as $filename => $attachment_id) {
                array_push($playlist, array(
                        "title" => $sermon["Post"]["title"],
                        "link" => "/urg_sermon/audio/" . $sermon["Sermon"]["id"] . "/" . $filename,
                        "id" => "sermon-audio-link-" . $sermon["Sermon"]["id"] . "-player"
                ));
            }
            $player .=  $this->SoundManager2->build_page_player($playlist);
            $player .= "</div>";
        } else {
            $player .= "<div id='sermon-title' class='grid_12'>";
            $player .= "<div>" . $sermon["Post"]["title"] . "</div>";
            $player .= "</div>";
        }

        return $player;
    }
}
