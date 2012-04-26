<?php
App::uses("SoundManager2Helper", "Sm2.View/Helper");
App::uses("AbstractWidgetHelper", "Urg.Lib");
class SermonPlayerHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time", "Sm2.SoundManager2");

    function build_widget() {
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        return $this->player();
    }

    function player() {
        $sermon = $this->options["sermon"];
        $attachments = $this->options["attachments"];

        $player = "";
        if (isset($attachments["Audio"])) {
            $player .= "<div class='span12 sermon-audio'>";
            $playlist = array();
            CakeLog::write("debug", "Sermon attachments " . Debugger::exportVar($this->options["attachments"], 3));
            foreach ($attachments["Audio"] as $filename => $attachment_id) {
                array_push($playlist, array(
                        "title" => $sermon["Post"]["title"],
                        "link" => "/urg_post/audio/" . $sermon["Post"]["id"] . "/" . $filename,
                        "id" => "sermon-audio-link-" . $sermon["Post"]["id"] . "-player"
                ));
            }
            $player .=  $this->SoundManager2->build_page_player($playlist);
            $player .= "</div>";
        } else {
            $player .= "<div id='sermon-title' class='span12'>";
            $player .= "<div>" . $sermon["Post"]["title"] . "</div>";
            $player .= "</div>";
        }

        return $player;
    }
}
