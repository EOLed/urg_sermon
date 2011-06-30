<?php
App::import("Helper", "Bible.Bible");
class SermonPassagesHelper extends AppHelper {
    var $helpers = array("Html", "Time", "Bible");
    var $widget_options = array("sermon");

    function build($options = array()) {
        $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
        return $this->passages($options["sermon"]);
    }

    function passages($sermon) {
        $passages = "";
        if (trim($sermon["Sermon"]["passages"]) != "") {
            $passage_placeholder = $this->Html->div("", "", array("id" => "sermon-passages-text",
                                                                  "style" => "display: none"));
            $loading = $this->Html->div("",
                                        $this->Html->image("/urg_sermon/img/loading.gif"), 
                                        array("id" => "sermon-passages-text-loading", 
                                              "style" => "display: none"));
            $translations = " " . $this->Html->tag("span", 
                    $this->Html->link("[ESV]", 
                                      array("plugin" => "urg_sermon",
                                            "controller" => "sermons",
                                            "action" => "passages",
                                            $this->Bible->encode_passage($sermon["Sermon"]["passages"]))));
            $passages = $this->Html->div("sermon-passages", 
                                         $this->Html->tag("h2", __("Passage", true)) .  
                                         $sermon["Sermon"]["passages"] .
                                         $translations . $passage_placeholder . $loading);
        }

        return $passages . $this->js();
    }

    function js() {
        $js = <<< EOT
            $(".sermon-passages a").click(function() {
                $("#sermon-passages-text-loading").show();
                $("#sermon-passages-text").load($(this).attr("href"),
                    function () { 
                        $("#sermon-passages-text-loading").hide();
                        $(this).show("slide");
                    }
                );

                return false;
            });
EOT;
        return $this->Html->scriptBlock($js);
    }
}
