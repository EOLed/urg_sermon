<script type="text/javascript" src="<?php echo $this->Html->url("/sm2/script/soundmanager2-nodebug-jsmin.js") ?>"></script>
<script type="text/javascript" src="<?php echo $this->Html->url("/sm2/script/page-player.js") ?>"></script>
<script type="text/javascript">
    soundManager.url = "<?php echo $this->Html->url("/sm2/swf/") ?>";
    soundManager.flashVersion = 9;
</script>
<?php echo $this->Html->css("/sm2/css/page-player.css", null, array("inline" => false)) ?>
<div id="sm2-container" style="width: 1px; height: 1px;"></div>
<div class="sermons view">
    <?php foreach ($banners as $banner) { ?>
    <div id="banner-<?php echo $sermon["Sermon"]["id"]; ?>" class="grid_9 right-border">
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
            echo "<ul class='playlist'>";
            foreach ($attachments["Audio"] as $filename => $attachment_id) {
                echo $this->Html->tag("li", $this->Html->link($sermon["Post"]["title"],
                        "/urg_sermon/audio/" . $sermon["Sermon"]["id"] . "/" . $filename,
                        array("class" => "inline-playable"))
                );
            }
            echo "</ul>";
            echo "</div>";
        }
    ?>

    <div id="sermon-info" class="grid_12">
        <div id="sermon-series" 
                class="alpha grid_3 top-border bottom-border right-border sermon-details">
            <h3 class="sermon-details"><?php echo strtoupper(__("From the series", true)); ?></h3>
            <?php echo $sermon["Series"]["name"]; ?>
        </div>
        <div id="sermon-date" 
                class="grid_3 top-border bottom-border right-border left-border sermon-details">
            <h3 class="sermon-details"><?php echo strtoupper(__("Taken place on", true)); ?></h3>
            <?php echo $this->Time->format("F d, Y", $sermon["Post"]["publish_timestamp"]) ?>
        </div>
        <div id="sermon-speaker" 
                class="grid_3 top-border bottom-border right-border left-border sermon-details">
            <h3 class="sermon-details"><?php echo strtoupper(__("Spoken by", true)); ?></h3>
            <?php echo $sermon["Pastor"]["name"] != "" ? $sermon["Pastor"]["name"] : 
                    $sermon["Sermon"]["speaker_name"] ?>
        </div>
        <div id="sermon-resources" 
                class="omega grid_3 top-border bottom-border left-border sermon-details">
            <h3 class="sermon-details"><?php echo strtoupper(__("Resources", true)); ?></h3>
            <?php if (isset($attachments["Documents"])) { ?>
                <ul id="sermon-resource-list">
                <?php foreach ($attachments["Documents"] as $filename=>$attachment_id) { ?> 
                    <li>
                        <?php echo $this->Html->link(
                                $this->Html->image("/urg_sermon/img/icons/" . 
                                        strtolower(substr($filename, strrpos($filename, ".") + 1, 
                                        strlen($filename))) . ".png", array("style"=>"height: 48px")), 
                                $this->Html->url("/urg_sermon/files/" . 
                                        $sermon["Sermon"]["id"] . "/" . $filename), 
                                        array("escape" => false, "class" => "gdoc") ); ?>
                    </li>
                <?php } ?>
                </ul>
            <? } ?>
        </div>
    </div>

    <div class="grid_4">
        <div class="sermon-description">
            <h2><?php __("Description") ?></h2>
            <?php echo $sermon["Sermon"]["description"] ?>
        </div>
        <div class="sermon-passage">
            <h2><?php __("Passage") ?></h2>
            <?php echo $sermon["Sermon"]["passages"] ?>
        </div>
        
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

    <div class="grid_8">
    <?php if (isset($attachments["Documents"])) { ?>
        <div id="sermon-docs" style="display: none">
            <iframe class="shadow sermon-attachment-viewer" id="sermon-doc-viewer"></iframe>
        </div>
    <? } ?>
    </div>

</div>
<script type="text/javascript">
<?php echo $this->element("js_equal_height"); ?>
$("div.sermon-details").equalHeight();

$(".gdoc").click(function() {
    $("#sermon-doc-viewer").attr("src", "http://docs.google.com/gview?embedded=true&url=http://<?php echo $_SERVER['SERVER_NAME'] ?>" + $(this).attr("href"));
    $("#sermon-docs").show();
    return false;
});
</script>

<?php $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline" => false)); ?>
