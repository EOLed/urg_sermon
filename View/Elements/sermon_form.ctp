<!-- Load Feather code -->
<script type="text/javascript" src="http://feather.aviary.com/js/feather.js"></script>

<!-- Instantiate Feather -->
<script type="text/javascript">
    var featherEditor = new Aviary.Feather({ apiKey: '68348725d', 
                                             apiVersion: 2, 
                                             tools: 'all', 
                                             appendTo: '', 
                                             cropPresets: [["Banner","16:9"]],
                                             onSave: function(imageID, newURL) { 
                                                 $.get("<?php echo $this->Html->url(array("plugin" => "urg_post",
                                                                                          "controller" => "posts",
                                                                                          "action" => "sideload_banner")); ?>", { url: newURL, post_id: $("#PostId").val() }, function(data) {
                                                         data = jQuery.parseJSON(data);
                                                         var img = document.getElementById(imageID); 
                                                         img.src = data.src; 
                                                         if ($("#Attachment0Filename").length) {
                                                             document.getElementById("Attachment0Filename").value = data.filename;
                                                         } else {
                                                             on_complete_images(null, null, { name:data.filename }, null);
                                                         }
                                                     });
                                                 } 
                                           }); 
    
    function launchEditor(id, src) { 
        featherEditor.launch({ image: id, url: src }); 
        return false; 
    } 
</script>
<div id="injection_site"></div>
<div class="sermons form">
    <div class="row">
        <div class="span12">
            <?php echo $this->Form->create('Sermon', array("class" => "form-horizontal")); ?>
                <div class="row">
                    <div class="span6">
                        <fieldset>
                            <legend> <div> <h2><?php echo __('Add Sermon'); ?></h2> </div> </legend>
                            <?php
                            echo $this->Form->hidden("Sermon.id");
                            echo $this->Form->hidden("Post.id");
                            echo $this->Form->hidden("bannerAttachmentIndex");
                            echo $this->TwitterBootstrap->input("series_name", array("label" => __("Series"),
                                                                                     "class" => "span4"));
                            echo $this->Html->div("error-message", "", 
                                    array("id"=>"SermonSeriesNameError", "style"=>"display: none"));
                            echo $this->Html->div("validated", "✓", 
                                    array("id"=>"SermonSeriesNameValid", "style"=>"display: none"));
                            echo $this->TwitterBootstrap->input('Post.title', array("class" => "span4"));
                            echo $this->Html->div("error-message", "", 
                                    array("id"=>"PostTitleError", "style"=>"display: none"));
                            echo $this->Html->div("validated", "✓", 
                                    array("id"=>"PostTitleValid", "style"=>"display: none"));
                            echo $this->TwitterBootstrap->input("speaker_name", 
                                    array("label"=>__("Speaker"), "class" => "span4"));
                            echo $this->Html->div("error-message", "", 
                                    array("id"=>"SermonSpeakerNameError", "style"=>"display: none"));
                            echo $this->Html->div("validated", "✓", 
                                    array("id"=>"SermonSpeakerNameValid", "style"=>"display: none"));
                            echo $this->TwitterBootstrap->input("passages", array("class" => "span4"));
                            echo $this->Form->hidden("Post.formatted_date");
                            $time = $this->Form->text("Post.displayTime", array("div"=> false, 
                                                                                "label"=> false,
                                                                                "class" => "span2"));
                            $options = array("type" => "text", 
                                             "class" => "span2", 
                                             "label"=> __("Date"), 
                                             "after" => $time);
                            echo $this->TwitterBootstrap->input("Post.displayDate", $options);
                            echo $this->TwitterBootstrap->input('Sermon.description', array("label"=>__("Description"), 
                                                                                      "rows"=>"8",
                                                                                      "class" => "span4"));
                            echo $this->TwitterBootstrap->input('Post.content', array("label"=>__("Notes"), 
                                                                                      "rows"=>"20",
                                                                                      "class" => "span4"));
                            ?>
                        </fieldset>
                    </div>
                    <div class="span6">
                        <fieldset>
                            <legend> <div> <h2><?php echo __('Add Resources'); ?></h2> </div> </legend>
                            <?php 
                            echo $this->Html->div("input", 
                                    $this->Html->div("placeholder", "", array("id" => "sermon-banner")) . 
                                    $this->element("Cuploadify.uploadify", 
                                    array("plugin" => "Cuploadify", 
                                            "dom_id" => "image_upload", 
                                            "session_id" => CakeSession::id(),
                                            "include_scripts" => array("uploadify_css", "uploadify", "swfobject"),
                                            "options" => array("auto" => true, 
                                                    "folder" => "/" . $this->data["Sermon"]["id"],
                                                    "script" => $this->Html->url("/urg_sermon/sermons/upload_image"),
                                                    "buttonText" => strtoupper(__("Add Banner")), 
                                                    //"multi" => true,
                                                    //"queueID" => "upload_queue",
                                                    "removeCompleted" => true,
                                                    "fileExt" => "*.jpg;*.jpeg;*.png;*.gif;*.bmp",
                                                    "fileDataName" => "imageFile",
                                                    "fileDesc" => "Image Files",
                                                    "onComplete" => "on_complete_images",
                                                    "onProgress" => "image_upload_in_progress",
                                                    "onAllComplete" => "image_uploads_completed"
                                                    )))); 
                            echo $this->Html->div("input", $this->element("Cuploadify.uploadify",
                                    array("plugin" => "Cuploadify", 
                                            "dom_id" => "attachment_upload", 
                                            "session_id" => CakeSession::id(),
                                            "options" => array("auto" => true, 
                                                    "folder" => "/" . $this->data["Sermon"]["id"],
                                                    "script" => $this->Html->url("/urg_sermon/sermons/upload_attachments"),
                                                    "buttonText" => strtoupper(__("Attachments")), 
                                                    "removeCompleted" => true,
                                                    "fileExt" => "*.mp3;*.jpg;*.jpeg;*.png;*.gif;*.bmp;" .
                                                                 "*.ppt;*.pptx;*.doc;*.docx;*.pdf",
                                                    "fileDataName" => "attachmentFile",
                                                    "fileDesc" => "Sermon Attachments",
                                                    "multi" => true,
                                                    "onComplete" => "on_complete_attachments",
                                                    "onProgress" => "attachment_upload_in_progress",
                                                    "onAllComplete" => "attachment_uploads_completed"
                                                    ))));
                            echo $this->Html->div("", $this->Html->tag("ul", "", array("id"=>"attachment-queue")));
                            if ($this->fetch("attachment_queue") == "") {
                                echo $this->start("attachment_queue");
                                echo $this->Html->div("", 
                                                      $this->Html->tag("ul", 
                                                                       "", 
                                                                       array("id"=>"attachment-queue")));
                                echo $this->end();
                            }
                            echo $this->fetch("attachment_queue");
                            ?>
                        </fieldset>
                    </div>
                </div>
                <div class="row form-actions">
                    <div class="span12">
                        <?php
                            echo $this->Form->button(__("Publish", true), array("class" => "btn btn-primary")) . " ";
                            echo $this->Form->button(__("Reset", true), array("type" => "reset", "class" => "btn"));
                        ?>
                    </div>
                    <?php 
                        echo $this->Html->div("", $this->Html->image("/urg_sermon/img/loading.gif"), 
                                array("id" => "loading-validate", "style" => "display: none")); 
                    ?>
                    <div style="display: none;" id="in-progress" title="<?php echo __("Uploads pending..."); ?>">
                        <p>
                            <?php echo __("The sermon form will be submitted after all attachments have been uploaded."); ?>
                        </p>
                    </div>
                </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>    
</div>

<script type="text/javascript">
    $("#sermon-banner").click(function() {
        launchEditor("editable-banner-img", "<?php echo substr(Router::url('/', true), 0, strlen(Router::url('/', true)) - 1); ?>" + $("#editable-banner-img").attr("src"));
        return false;
    });
<?php if (isset($banner) && $banner !== false) { ?>
$($("#sermon-banner").prepend('<?php echo $this->Html->image($banner, array("id" => "editable-banner-img")); ?>'));
<?php } ?>
</script>
