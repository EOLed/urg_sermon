<script type="text/javascript">
    function on_complete(event, ID, fileObj, response, data) {
        response = jQuery.parseJSON(response);
        <?php
        echo $this->Js->request("/urg_sermon/sermons/process_import_file", 
            array(
                "async" => true,
                "data" => '{ filename: response.filename }',
                "dataExpression" => true,
                "complete" => "on_import_complete('#import-progress', XMLHttpRequest, textStatus)",
                "before" => "loading('#import-progress')"
            )
        );
        ?>
    }

    var keep_loading = true;

    function loading(dom_id) {
        $(dom_id).show(); 
        timer = setInterval(function() {
            if (!keep_loading)
                clearInterval(timer);
                <?php echo $this->Js->request("/urg_sermon/sermons/get_status", 
                    array(
                        "async" => true,
                        "success" => "processed(data, textStatus);",
                    )
                ); ?>
        }, 2000);
    }

    function processed(data, textStatus) {
        data = jQuery.parseJSON(data);
        $("#import-progress-bar").progressbar("value", parseInt(data.pct));
        for (var i=0; i<data.log.length; i++) {
            $("#import-log-list").prepend("<li>" + data.log[i] + "</li>");
        }
    }

    function on_import_complete(dom_id, XMLHttpRequest, textStatus) {
        keep_loading = false;
    }

    function upload_in_progress(event, ID, fileObj, data) {
    }

    function uploads_completed(event, data) {
    }

    $(function() {
        $("#import-progress-bar").progressbar({value: 1});
    });
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
        <div id="import-progress" style="display: none">
            <div id="import-progress-bar"></div>
            <div id="import-log">
                <ul id="import-log-list"> </ul>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $("#import-log").hover(function() { $(this).addClass("hover"); },
                           function() { $(this).removeClass("hover"); });
</script>
<?php $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
