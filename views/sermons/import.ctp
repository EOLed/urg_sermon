<script type="text/javascript">
    function on_complete(event, ID, fileObj, response, data) {
    }

    function upload_in_progress(event, ID, fileObj, data) {
    }

    function uploads_completed(event, data) {
    }
</script>
<div class="sermons form">
<?php echo $this->Form->create('Sermon'); ?>
    <div class="grid_6 right-border">
    <?php 
    echo $this->Html->div("input", 
            $this->element("uploadify", 
            array("plugin" => "cuploadify", 
                    "dom_id" => "import-sermon", 
                    "session_id" => $this->Session->id(),
                    "include_scripts" => array("uploadify_css", "uploadify", "swfobject"),
                    "options" => array("auto" => true, 
                            "folder" => "/import",
                            "script" => $this->Html->url("/urg_sermon/sermons/upload_import_file"),
                            "buttonText" => strtoupper(__("Import", true)), 
                            "multi" => true,
                            //"queueID" => "upload_queue",
                            "removeCompleted" => true,
                            "fileExt" => "*.xml;*.asc",
                            "fileDataName" => "importFile",
                            "fileDesc" => "Churchie Files",
                            "onComplete" => "on_complete",
                            "onProgress" => "upload_in_progress",
                            "onAllComplete" => "uploads_completed"
                            )))); 
    echo $this->Html->div("", $this->Html->tag("ul", "", array("id"=>"attachment-queue")));
    ?>
    </div>
</div>
<?php $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));

