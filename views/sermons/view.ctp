<script type="text/javascript" src="<?php echo $this->Html->url("/sm2/script/soundmanager2-nodebug-jsmin.js") ?>"></script>
<script type="text/javascript" src="<?php echo $this->Html->url("/sm2/script/page-player.js") ?>"></script>
<script type="text/javascript">
    soundManager.url = "<?php echo $this->Html->url("/sm2/swf/") ?>";
    soundManager.flashVersion = 9;
</script>
<?php echo $this->Html->css("/sm2/css/page-player.css", null, array("inline" => false)) ?>
<div id="sm2-container" style="width: 1px; height: 1px;"></div>
<div class="sermons view">
    <h2><?php  __('Sermon');?></h2>
    <?php 
        if (isset($attachments["Audio"])) {
            echo "<ul class='playlist'>";
            foreach ($attachments["Audio"] as $filename => $attachment_id) {
                echo $this->Html->tag("li", $this->Html->link($filename,
                        "/urg_sermon/audio/" . $sermon["Sermon"]["id"] . "/" . $filename,
                        array("class" => "inline-playable"))
                );
            }
            echo "</ul>";
        }
    ?>
    <div class="sermon-banner">
    <?php    
        if (isset($attachments["Banner"])) {
            foreach ($attachments["Banner"] as $filename => $attachment_id) {
                echo $this->Html->image(
                        "/urg_sermon/img/" . $sermon["Sermon"]["id"] . "/" . $filename
                );
            }
        }
    ?>
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
<div class="actions">
    <h3><?php __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('Edit Sermon', true), array('action' => 'edit', $sermon['Sermon']['id'])); ?> </li>
        <li><?php echo $this->Html->link(__('Delete Sermon', true), array('action' => 'delete', $sermon['Sermon']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $sermon['Sermon']['id'])); ?> </li>
        <li><?php echo $this->Html->link(__('List Sermons', true), array('action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Sermon', true), array('action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Posts', true), array('controller' => 'posts', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Post', true), array('controller' => 'posts', 'action' => 'add')); ?> </li>
    </ul>
</div>

<?php $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline" => false)); ?>
