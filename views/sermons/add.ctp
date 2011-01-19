<?php
/**
 * Sermon upload form.
 * This form is responsible for uplaoding sermons onto an Urg system.
 *
 * @author Amos Chan <amos.chan@chapps.org>
 * @since v 1.0
 */
 ?>
<?php echo $this->Html->scriptStart(); ?>
    var attachmentCounter = 0;
    var in_progress = false;
    var submit_form = false;
    function on_complete_images(event, ID, fileObj, response, data) {
        $('<input>').attr({ 
                type: 'hidden', 
                id: 'Attachment' + attachmentCounter + 'Filename', 
                name: 'data[Attachment][' + attachmentCounter + '][filename]' ,
                value: fileObj.name,
        }).appendTo('form');
        $('<input>').attr({ 
                type: 'hidden', 
                id: 'Attachment' + attachmentCounter + 'AttachmentTypeId', 
                name: 'data[Attachment][' + attachmentCounter + '][attachment_type_id]' ,
                value: <?php echo $banner_type["AttachmentType"]["id"]; ?>,
        }).appendTo('form');
        attachmentCounter++;
    }

    function upload_in_progress(event, ID, fileObj, data) {
        in_progress = true;
    }
<?php echo $this->Html->scriptEnd(); ?>

<div class="sermons form">
<?php echo $this->Form->create('Sermon'); ?>
    <fieldset>
        <legend><?php __('Add Sermon'); ?></legend>
        <?php
        echo $this->Form->hidden("uuid");
        echo $this->Form->hidden('series_id');
        echo $this->Form->hidden("pastor_id");
        echo $this->Form->hidden("confirm_speaker_name");
        echo $this->Form->hidden("confirm_series_name");
        echo $this->Form->input("series_name", array("label"=>__("sermons.label.series", true)));
        echo $this->Form->input('Post.title');
        echo $this->Form->hidden('speaker_name');
        echo $this->Form->input("display_speaker_name", 
                array("label"=>__("sermons.label.speaker.name", true)));
        echo $this->Form->input("passages");
        echo $this->Form->input('Post.content', array("label"=>__("sermons.label.description", true)));
        echo $this->element("uploadify", 
                array("plugin" => "cuploadify", 
                        "dom_id" => "image_upload", 
                        "session_id" => $this->Session->id(),
                        "include_scripts" => array("uploadify_css", "uploadify", "swfobject"),
                        "options" => array("auto" => true, 
                                "folder" => "/" . $this->data["Sermon"]["uuid"],
                                "script" => $this->Html->url("/urg_sermon/sermons/upload_images"),
                                "buttonText" => "ADD IMAGES", 
                                "multi" => true,
                                "queueID" => "upload_queue",
                                "removeCompleted" => true,
                                "fileExt" => "*.jpg;*.jpeg;*.png;*.gif;*.bmp",
                                "fileDataName" => "imageFile",
                                "fileDesc" => "Image Files",
                                "onComplete" => "on_complete_images",
                                "onProgress" => "upload_in_progress",
                                "onAllComplete" => "uploads_completed"
                                )));
       echo $this->element("uploadify",
                array("plugin" => "cuploadify", 
                        "dom_id" => "audio_upload", 
                        "session_id" => $this->Session->id(),
                        "options" => array("auto" => true, 
                                "folder" => "/" . $this->data["Sermon"]["uuid"],
                                "script" => $this->Html->url("/urg_sermon/sermons/upload_audio"),
                                "buttonText" => "ADD AUDIO", 
                                "removeCompleted" => false,
                                "queueID" => "upload_queue",
                                "fileExt" => "*.mp3",
                                "fileDataName" => "audioFile",
                                "fileDesc" => "Audio Files")));
       echo $this->Html->div("upload_queue", "", array("id" => "upload_queue"));
       ?>
    </fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
    <div id="in-progress" title="<?php echo __("sermons.form.upload.pending", true); ?>">
        <p><?php echo __("sermons.form.upload.pending.body", true); ?></p>
    </div>
</div>
<div class="actions">
    <h3><?php __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('List Sermons', true), array('action' => 'index'));?></li>
    </ul>
</div>

<?php echo $this->Html->scriptStart(); ?>
    var search_series = true;
    var search_speaker = true;

    $(function() {
        $("#SermonSeriesName").autocomplete({
            source: "<?php echo $this->Html->url(
                    array("plugin" => "urg_sermon", 
                          "controller" => "series", 
                          "action" => "autocomplete")
            ); ?>",
            minLength: 0,
            select: function(event, ui) {
                $("#SermonSeriesId").val(ui.item.id);
                $("#SermonConfirmSeriesName").val(ui.item.value);
            },
            open: function(event, ui) {
                $("#SermonSeriesId").val("");
            },
            change: function(event, ui) {
                if (ui.item != null) {
                    $("#SermonConfirmSeriesName").val(ui.item.value);
                } else {
                    $("#SermonConfirmSeriesName").val("");
                }
            },
            search: function(event, ui) {
                search_series = false;
                if ($(this).val().length == 1) {
                    $(this).autocomplete("close");
                    return false;
                }
            },
            close: function(event, ui) {
                search_series = true;
            }
        }).focus(function() {
            if (search_series && this.value == "") {
                 $(this).autocomplete("search", '');
            }
        });
    });

    $("#SermonSeriesName").blur(function() {
        if ($("#SermonSeriesName").val() != $("#SermonConfirmSeriesName").val()) {
            $("#SermonSeriesId").val("");
        }
    });

    $(function() {
        $("#SermonDisplaySpeakerName").autocomplete({
            source: "<?php echo $this->Html->url(
                    array("plugin" => "urg_sermon", 
                          "controller" => "sermons", 
                          "action" => "autocomplete_speaker")); ?>",
            minLength: 0,
            select: function(event, ui) {
                $("#SermonDisplaySpeakerName").val(ui.item.value);
                $("#SermonConfirmSpeakerName").val(ui.item.value);

                if (ui.item.group_id) {
                    $("#SermonPastorId").val(ui.item.group_id);
                } else {
                    $("#SermonPastorId").val("");
                    $("#SermonSpeakerName").val(ui.item.value); 
                }
            },
            change: function(event, ui) {
                if (ui.item != null) {
                    $("#SermonConfirmSpeakerName").val(ui.item.value);
                } else {
                    $("#SermonConfirmSpeakerName").val("");
                }
            },
            search: function(event, ui) {
                search_speaker = false;
                if ($("#SermonDisplaySpeakerName").val().length == 1) {
                    $("#SermonDisplaySpeakerName").autocomplete("close");
                    return false;
                }
            },
            close: function(event, ui) {
                search_speaker = true;
            }
        }).focus(function() {
            if (search_speaker && this.value == "") {
                $(this).autocomplete("search");
            }
        })
        .data("autocomplete")._renderItem = function(ul, item) {
            var pastor_class = item.belongsToChurch ? " class='pastor_item' " : "";
            return $("<li" + pastor_class + "></li>").data("item.autocomplete", item)
                    .append("<a>" + item.label + "</a>" )
                    .appendTo(ul);
        };
    });
    
    $("#SermonDisplaySpeakerName").blur(function() {
        if ($("#SermonDisplaySpeakerName").val() != $("#SermonConfirmSpeakerName").val()) {
            $("#SermonPastorId").val("");
            $("#SermonSpeakerName").val($("#SermonDisplaySpeakerName").val());
        }
    });
<?php echo $this->Html->scriptEnd(); ?>

<?php echo $this->Html->script("tinymce/jquery.tinymce.js"); ?>

<?php echo $this->Html->scriptStart(); ?>
    $(function() {
        $('#PostContent').tinymce({
            script_url: "<?php echo $this->Html->url("/js/tinymce/tiny_mce.js"); ?>",
            theme: "advanced",
            theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,fontsizeselect",
            theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,cleanup,code,|,forecolor,backcolor",
            theme_advanced_buttons3 : "",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_resizing : true
        });
    });

    $(function() {
        $("#in-progress").dialog({
            modal: true,
            autoOpen: false
        })
    });

    $("#SermonAddForm").submit(function() {
        if (in_progress) {
            submit_form = true;
            $("#in-progress").dialog("open");
        }

        return !in_progress;
    });

    function uploads_completed(event, data) {
        in_progress = false;

        $("#in-progress").dialog("close");

        if (submit_form) {
            $("#SermonAddForm").submit();
        }
    }
<?php echo $this->Html->scriptEnd(); ?>
<?php $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
