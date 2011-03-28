<div class="pastors view">
    <?php foreach ($banners as $banner) { ?>
    <div id="banner" class="grid_9 right-border">
        <?php //echo $this->Html->image($banner, array("class"=>"shadow")); ?>
    </div>
    <?php } ?>
    <div id="about-panel" class="grid_3">
        <h3><?php echo strtoupper(__("About us", true)); ?></h3>
    </div>

    <div id='pastor-name' class='grid_12 page-title'>
        <div><?php echo $pastor["Group"]["name"]?></div>
    </div>

    <div id="about-pastor" class="grid_4 right-border">
        <h2><?php echo __("Bio", true) ?></h2>
        <?php echo $pastor["Group"]["description"]; ?>
    </div>
    <div id="pastor-feed" class="grid_4 right-border">
        <h2><?php echo __("Recent activity", true); ?></h2>
        <?php echo $this->Pastor->activity_feed($pastor, $activity); ?>
    </div>
    <div id="pastor-upcoming" class="grid_4">
        <h2><?php echo __("Upcoming events", true); ?></h2>
    </div>
</div>
<script type="text/javascript">
<?php echo $this->element("js_equal_height"); ?>
$("#about-pastor, #pastor-feed, #pastor-upcoming").equalHeight();
</script>
<?php $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline" => false)); ?>
