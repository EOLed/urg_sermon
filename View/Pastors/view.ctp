<div class="pastors view">
    <?php foreach ($banners as $banner) { ?>
    <div id="banner" class="span9 right-border">
        <?php echo $this->Html->image($banner, array("class"=>"shadow")); ?>
    </div>
    <?php } ?>
    <div id="about-panel" class="span3">
        <h3><?php echo strtoupper(__("About us")); ?></h3>
        <?php echo $about["Post"]["content"] ?>
    </div>

    <div id='pastor-name' class='span12 page-title'>
        <div><?php echo $pastor["Group"]["name"]?></div>
    </div>

    <div id="about-pastor" class="span4 right-border">
        <h2><?php echo __("Bio") ?></h2>
        <?php echo $about_pastor["Post"]["content"]; ?>
    </div>
    <div id="pastor-feed" class="span4 right-border">
        <h2><?php echo __("Recent activity"); ?></h2>
        <?php echo $this->Pastor->activity_feed($pastor, $activity); ?>
    </div>
    <div id="pastor-upcoming" class="span4">
        <h2><?php echo __("Upcoming events"); ?></h2>
        <?php echo $this->Pastor->upcoming_events($pastor, $upcoming_events); ?>
    </div>
</div>
<script type="text/javascript">
<?php echo $this->element("js_equal_height"); ?>
$("#about-pastor, #pastor-feed, #pastor-upcoming").equalHeight();
</script>
<?php $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline" => false)); ?>
