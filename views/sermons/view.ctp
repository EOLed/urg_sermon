<div class="sermons view">
    <?php foreach ($banners as $banner) { ?>
    <div id="banner" class="grid_9 right-border">
        <?php echo $this->Html->image($banner, array("class"=>"shadow")); ?>
    </div>
    <?php } ?>
    <div id="about-panel" class="grid_3">
        <?php if ($sermon["Series"]["description"] != "") { ?>
            <h3><?php __("About the series") ?></h3>
            <?php echo $sermon["Series"]["description"]; ?>
        <?php } else if ($sermon["Pastor"]["description"] != "") { ?>
            <h3><?php __("About the speaker") ?></h3>
            <?php echo $sermon["Pastor"]["description"]; ?>
        <?php } else { ?>
            <h3><?php echo strtoupper(__("About us", true)); ?></h3>
        <?php } ?>
    </div>

    <?php 
        if (isset($attachments["Audio"])) {
            echo "<div class='grid_12 sermon-audio'>";
            $playlist = array();
            foreach ($attachments["Audio"] as $filename => $attachment_id) {
                array_push($playlist, array(
                        "title" => $sermon["Post"]["title"],
                        "link" => "/urg_sermon/audio/" . $sermon["Sermon"]["id"] . "/" . $filename,
                        "id" => "sermon-audio-link-" . $sermon["Sermon"]["id"] . "-player"
                ));
            }
            echo $this->SoundManager2->build_page_player($playlist);
            echo "</div>";
        } else {
            echo "<div id='sermon-title' class='grid_12'>";
            echo "<div>" . $sermon["Post"]["title"] . "</div>";
            echo "</div>";
        }
    ?>

    <div id="sermon-info" class="grid_12">
        <div id="sermon-series" 
                class="alpha grid_3 top-border bottom-border right-border sermon-details" 
                style="border-right-width: 0px">
            <h3 class="sermon-details"><?php __("From the series"); ?></h3>
            <?php echo $sermon["Series"]["name"]; ?>
        </div>
        <div id="sermon-date" 
                class="grid_3 top-border bottom-border right-border left-border sermon-details"
                style="border-right-width: 0px">
            <h3 class="sermon-details"><?php __("Taken place on"); ?></h3>
            <?php echo $this->Time->format("F d, Y", $sermon["Post"]["publish_timestamp"]) ?>
        </div>
        <div id="sermon-speaker" 
                class="grid_3 top-border bottom-border right-border left-border sermon-details"
                style="border-right-width: 0px">
            <h3 class="sermon-details"><?php __("Spoken by"); ?></h3>
            <?php echo $sermon["Pastor"]["name"] != "" ? $sermon["Pastor"]["name"] : 
                    $sermon["Sermon"]["speaker_name"] ?>
        </div>
        <div id="sermon-resources" 
                class="omega grid_3 top-border bottom-border left-border sermon-details">
            <h3 class="sermon-details"><?php __("Resources"); ?></h3>
            <?php if (isset($attachments["Documents"])) { ?>
                <ul id="sermon-resource-list">
                <?php foreach ($attachments["Documents"] as $filename=>$attachment_id) { ?> 
                    <li>
                        <?php echo $this->Html->link(
                                $this->Html->image("/urg_sermon/img/icons/" . 
                                        strtolower(substr($filename, strrpos($filename, ".") + 1, 
                                        strlen($filename))) . ".png", array("style"=>"height: 32px")), 
                                $this->Html->url("/urg_sermon/files/" . 
                                        $sermon["Sermon"]["id"] . "/" . $filename), 
                                        array("escape" => false, "class" => "gdoc") ); ?>
                    </li>
                <?php } ?>
                <?php if (isset($attachments["Audio"])) { ?>
                    <li>
                        <?php foreach ($attachments["Audio"] as $filename => $attachment_id) { ?>
                        <?php echo $this->Html->link(
                                $this->Html->image("/urg_sermon/img/icons/" . 
                                        strtolower(substr($filename, strrpos($filename, ".") + 1, 
                                        strlen($filename))) . ".png", array("style"=>"height: 32px")), 
                                $this->Html->url("/urg_sermon/audio/" . 
                                        $sermon["Sermon"]["id"] . "/" . $filename), 
                                        array("escape" => false, "class" => "exclude sermon-audio",
                                        "id" => "sermon-audio-link-" . $sermon["Sermon"]["id"]) ); ?>
                        <?php } ?>
                    </li>
                <?php } ?>
                </ul>
            <? } ?>
        </div>
    </div>

    <div class="grid_5">
        <?php if ($sermon["Sermon"]["description"] != "") { ?>
        <div class="sermon-description">
            <h2><?php __("Description") ?></h2>
            <?php echo $sermon["Sermon"]["description"] ?>
        </div>
        <?php } ?>
        <?php if ($sermon["Sermon"]["passages"] != "") { ?>
        <div class="sermon-passage">
            <h2><?php __("Passage") ?></h2>
            <?php echo $sermon["Sermon"]["passages"] . " "; ?>
            <span class="sermon-passage-translation">
                <?php echo $this->Html->link("[ESV]", "/urg_sermon/sermons/passages/" . $this->Bible->encode_passage($sermon["Sermon"]["passages"])); ?>
            </span>
            <div id="sermon-passage-text" style="display: none"></div>
            <div id="sermon-passage-text-loading" style="display: none">
                <?php echo $this->Html->image("/urg_sermon/img/loading.gif"); ?>
            </div>
        </div>
        <?php } ?>
        
        <?php if (isset($sermon["Series"]) && $sermon["Series"]["name"] != "No Series") { ?>
        <div class="series">
        <h2><?php echo $sermon["Series"]["name"] ?></h2>
        <ol class="series-sermon-list">
        <?php foreach ($series_sermons as $series_sermon) { ?>
            <li class="series-sermon-list-item">
                <a href="<?php echo $this->Html->url("/urg_sermon/sermons/view/") . 
                        $series_sermon["Sermon"]["id"] ?>"><?php echo $series_sermon["Post"]["title"]?></a>
                <div class="series-sermon-details">
                    <?php echo sprintf(__("by %s on %s", true),
                            $this->element("speaker_name", array("plugin"=>"urg_sermon", 
                                    "sermon"=> $series_sermon)),
                            $this->Time->format("n/j/y", $sermon['Post']['publish_timestamp'])) ?>
                </div>
            </li>
        <?php } ?>
        </ol>
        </div>
        <?php } ?>
    </div>

    <div class="grid_7 left-border">
    <?php if (isset($attachments["Documents"])) { ?>
        <div id="sermon-docs" style="display: none">
            <iframe class="shadow sermon-attachment-viewer" id="sermon-doc-viewer"></iframe>
            <a href="#" id="close-sermon-doc"><?php echo $this->Html->image("/urg_sermon/img/icons/x.png", array("style"=>"height: 32px")); ?></a>
        </div>
    <? } ?>
        <div id="sermon-notes">
            <h2><?php echo __("Sermon notes", true); ?></h2>
            <?php echo $sermon["Post"]["content"]; ?>
        </div>
    </div>

</div>
<script type="text/javascript">
<?php echo $this->element("js_equal_height"); ?>
$("div.sermon-details").equalHeight();

$(".gdoc").click(function() {
    $("#sermon-doc-viewer").attr("src", "http://docs.google.com/gview?embedded=true&url=http://<?php echo $_SERVER['SERVER_NAME'] ?>" + $(this).attr("href"));
    $("#sermon-notes").hide();
    $("#sermon-docs").show("fade");
    return false;
});

$("#close-sermon-doc").click(function() {
    $("#sermon-docs").hide();
    $("#sermon-notes").show("slide");
    return false;
});

$(".sermon-passage a").click(function() {
    $("#sermon-passage-text-loading").show();
    $("#sermon-passage-text").load($(this).attr("href"),
        function () { 
            $("#sermon-passage-text-loading").hide();
            $(this).show("slide");
        }
    );

    return false;
});

$("#sermon-resource-list li a").click(function() {
    pagePlayer.handleClick({
        target:document.getElementById($(this).attr("id") + "-player")
    });
    return false;
});
</script>

<?php $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline" => false)); ?>
