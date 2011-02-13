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
    var image_in_progress = false;
    var audio_in_progress = false;
    var submit_form = false;
    function on_complete_images(event, ID, fileObj, response, data) {
        if ($("#SermonBannerAttachmentIndex").val() == "") {
            $("#SermonBannerAttachmentIndex").val($("input.attachment").size());
        }

        bannerIndex = $("#SermonBannerAttachmentIndex").val();

        banner_filename = "banner" + fileObj.name.substr(fileObj.name.lastIndexOf('.'));

        if ($("#Attachment" + bannerIndex + "Filename").length == 0) {
            $('<input>').attr({ 
                    type: 'hidden', 
                    id: 'Attachment' + bannerIndex + 'Filename', 
                    name: 'data[Attachment][' + bannerIndex + '][filename]' ,
                    value: banner_filename,
                    class: "attachment"
            }).appendTo('form');
            $('<input>').attr({ 
                    type: 'hidden', 
                    id: 'Attachment' + bannerIndex + 'AttachmentTypeId', 
                    name: 'data[Attachment][' + bannerIndex + '][attachment_type_id]' ,
                    value: <?php echo $banner_type["AttachmentType"]["id"]; ?>,
            }).appendTo('form');
        }

        banner_width = $("#sermon-banner").width();

        $("#sermon-banner").html(
                "<img id='#sermon-banner-img' src='" +
                "<?php echo $this->Html->url("/urg_sermon/img/" . $this->data["Sermon"]["uuid"]); ?>" 
                + "/" + banner_filename + "#" + Math.random() + "' style='width: " + banner_width +  "px;' />");
    }

    function on_complete_audio(event, ID, fileObj, response, data) {
        attachmentCounter = $("input.attachment").size();
        $('<input>').attr({ type: 'hidden', 
                id: 'Attachment' + attachmentCounter + 'Filename', 
                name: 'data[Attachment][' + attachmentCounter + '][filename]' ,
                value: fileObj.name,
                class: "attachment"
        }).appendTo('form');
        $('<input>').attr({ 
                type: 'hidden', 
                id: 'Attachment' + attachmentCounter + 'AttachmentTypeId', 
                name: 'data[Attachment][' + attachmentCounter + '][attachment_type_id]' ,
                value: <?php echo $audio_type["AttachmentType"]["id"]; ?>,
        }).appendTo('form');

        $("<li>").attr({ id: "AttachmentQueueListItem" + attachmentCounter }).appendTo("#audio-queue");

        $("<a>").attr({
                href: "<?php echo $this->Html->url("/urg_sermon/audio/" . $this->data["Sermon"]["uuid"]); ?>"
                        + "/" + fileObj.name,
                id: "AttachmentQueueAudioLink" + attachmentCounter 
        }).appendTo("#AttachmentQueueListItem" + attachmentCounter);

        $("#AttachmentQueueAudioLink" + attachmentCounter).text(fileObj.name);
    }

    function image_upload_in_progress(event, ID, fileObj, data) {
        image_in_progress = true;
    }

    function audio_upload_in_progress(event, ID, fileObj, data) {
        audio_in_progress = true;
    }
<?php echo $this->Html->scriptEnd(); ?>

<div class="sermons form">
<?php echo $this->Form->create('Sermon'); ?>
    <fieldset>
        <legend><?php __('Add Sermon'); ?></legend>
        <?php
        echo $this->Form->hidden("uuid");
        echo $this->Form->hidden("bannerAttachmentIndex");
        echo $this->Html->div("placeholder", __("INSERT SERMON BANNER", true), 
                array("id" => "sermon-banner"));
        echo $this->element("uploadify", 
                array("plugin" => "cuploadify", 
                        "dom_id" => "image_upload", 
                        "session_id" => $this->Session->id(),
                        "include_scripts" => array("uploadify_css", "uploadify", "swfobject"),
                        "options" => array("auto" => true, 
                                "folder" => "/" . $this->data["Sermon"]["uuid"],
                                "script" => $this->Html->url("/urg_sermon/sermons/upload_image"),
                                "buttonText" => "ADD IMAGES", 
                                //"multi" => true,
                                //"queueID" => "upload_queue",
                                "removeCompleted" => true,
                                "fileExt" => "*.jpg;*.jpeg;*.png;*.gif;*.bmp",
                                "fileDataName" => "imageFile",
                                "fileDesc" => "Image Files",
                                "onComplete" => "on_complete_images",
                                "onProgress" => "image_upload_in_progress",
                                "onAllComplete" => "image_uploads_completed"
                                )));
        echo $this->Form->input("series_name", array("label"=>__("Series", true)));
        echo $this->Html->div("error-message", "", 
                array("id"=>"SermonSeriesNameError", "style"=>"display: none"));
        echo $this->Html->div("validated", "✓", 
                array("id"=>"SermonSeriesNameValid", "style"=>"display: none"));
        echo $this->Form->input('Post.title');
        echo $this->Html->div("error-message", "", 
                array("id"=>"PostTitleError", "style"=>"display: none"));
        echo $this->Html->div("validated", "✓", 
                array("id"=>"PostTitleValid", "style"=>"display: none"));
        echo $this->Form->input("speaker_name", 
                array("label"=>__("Speaker", true)));
        echo $this->Html->div("error-message", "", 
                array("id"=>"SermonSpeakerNameError", "style"=>"display: none"));
        echo $this->Html->div("validated", "✓", 
                array("id"=>"SermonSpeakerNameValid", "style"=>"display: none"));
        echo $this->Form->input("passages");
        echo $this->Form->hidden("Post.publish_timestamp");
        echo $this->Form->input("Post.displayDate", 
                array("type"=>"text", "label"=>__("Date", true)));
        echo $this->Form->input('Post.content', array("label"=>__("Description", true)));
        echo $this->element("uploadify",
                array("plugin" => "cuploadify", 
                        "dom_id" => "audio_upload", 
                        "session_id" => $this->Session->id(),
                        "options" => array("auto" => true, 
                                "folder" => "/" . $this->data["Sermon"]["uuid"],
                                "script" => $this->Html->url("/urg_sermon/sermons/upload_audio"),
                                "buttonText" => "ADD AUDIO", 
                                "removeCompleted" => true,
                                "fileExt" => "*.mp3",
                                "fileDataName" => "audioFile",
                                "fileDesc" => "Audio Files",
                                "onComplete" => "on_complete_audio",
                                "onProgress" => "audio_upload_in_progress",
                                "onAllComplete" => "audio_uploads_completed"
                                )));
       echo $this->Html->div("", $this->Html->tag("ul", "", array("id"=>"audio-queue")));
       ?>
    </fieldset>
    <?php 
        echo $this->Html->div("", $this->Html->image("/urg_sermon/img/loading.gif"), 
                array("id" => "loading-validate", "style" => "display: none")); 
    ?>
    <?php echo $this->Form->end(__('Submit', true));?>
    <div style="display: none;" id="in-progress" 
            title="<?php echo __("Uploads pending...", true); ?>">
        <p><?php echo __("The sermon form will be submitted after all attachments have been uploaded.", true); ?></p>
    </div>
</div>
<div class="actions">
    <h3><?php __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('List Sermons', true), array('action' => 'index'));?></li>
    </ul>
</div>
<?php echo $this->Html->scriptStart(); ?>
    function on_validate(dom_id, XMLHttpRequest, textStatus) {
        $("#loading-validate").hide();
        
        if ($(dom_id + "Error").text() == "") {
            $(dom_id + "Error").hide();
            $(dom_id).after($(dom_id + "Valid"));
            $(dom_id + "Valid").show();
            $(dom_id).removeClass("invalid");
        } else {
            $(dom_id + "Valid").hide();
            $(dom_id).after($(dom_id + "Error"));
            $(dom_id + "Error").show();
            $(dom_id).addClass("invalid");
        }
    }

    function loading_validate(dom_id) {
        $(dom_id).after($("#loading-validate"));
        $(dom_id + "Error").hide();
        $("#loading-validate").show();
    }

    $("#PostTitle").blur(function() {
        if ($(this).hasClass("dirty")) {
        <?php
        $this->Js->get("#PostTitle");
        echo $this->Js->request("/urg_sermon/sermons/validate_field/Post/title", array(
                "update" => "#PostTitleError",
                "async" => true,
                "data" => '{ value: $("#PostTitle").val() }',
                "dataExpression" => true,
                "complete" => "on_validate('#PostTitle', XMLHttpRequest, textStatus)",
                "before" => "loading_validate('#PostTitle')"
        ));
        ?>
        }

        $(this).removeClass("dirty");
    });

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
                $("#SermonSeriesName").val(ui.item.id);
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
                <?php
                $this->Js->get("#SermonSeriesName");
                echo $this->Js->request("/urg_sermon/sermons/validate_field/Sermon/series_name", 
                    array(
                        "update" => "#SermonSeriesNameError",
                        "async" => true,
                        "data" => '{ value: $("#SermonSeriesName").val() }',
                        "dataExpression" => true,
                        "complete" => "on_validate('#SermonSeriesName', XMLHttpRequest, textStatus)",
                        "before" => "loading_validate('#SermonSeriesName')"
                    )
                );
                ?>
            }
        }).focus(function() {
            if (search_series && this.value == "") {
                 $(this).autocomplete("search", '');
            }
        });
    });

    $(function() {
        $("#SermonSpeakerName").autocomplete({
            source: "<?php echo $this->Html->url(
                    array("plugin" => "urg_sermon", 
                          "controller" => "sermons", 
                          "action" => "autocomplete_speaker")); ?>",
            minLength: 0,
            select: function(event, ui) {
                $("#SermonSpeakerName").val(ui.item.value);
            },
            search: function(event, ui) {
                search_speaker = false;
                if ($("#SermonSpeakerName").val().length == 1) {
                    $("#SermonSpeakerName").autocomplete("close");
                    return false;
                }
            },
            close: function(event, ui) {
                search_speaker = true;
                <?php
                $this->Js->get("#SermonSpeakerName");
                echo $this->Js->request(
                        "/urg_sermon/sermons/validate_field/Sermon/display_speaker_name", 
                    array(
                        "update" => "#SermonSpeakerNameError",
                        "async" => true,
                        "data" => '{ value: $("#SermonSpeakerName").val() }',
                        "dataExpression" => true,
                        "complete" => "on_validate('#SermonSpeakerName', XMLHttpRequest, textStatus)",
                        "before" => "loading_validate('#SermonSpeakerName')"
                    )
                );
                ?>
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
<?php echo $this->Html->scriptEnd(); ?>

<?php echo $this->Html->script("tinymce/jquery.tinymce.js"); ?>

<?php echo $this->Html->scriptStart(); ?>
    $(function() {
        $('#PostContent').tinymce({
            script_url: "<?php echo $this->Html->url("/js/tinymce/tiny_mce.js"); ?>",
            theme: "advanced",
            theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|," +
                                      "justifyleft,justifycenter,justifyright,fontsizeselect",
            theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|," +
                                      "link,unlink,anchor,cleanup,code,|,forecolor,backcolor",
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
        error = false;
        scrolled = false;
        $(":input.invalid").each(function(index) {
            if (!scrolled) {
                $('html,body').animate(
                        { scrollTop: $(this).offset().top - 30 }, 
                        { duration: 'fast', easing: 'swing'}
                );
                scrolled = true;
            }
            $(this).effect("highlight", { color: "#FFD4D4" });
            error = true;
        });

        if (error) return false;

        if (image_in_progress || audio_in_progress) {
            submit_form = true;
            $("#in-progress").dialog("open");
        }

        return !image_in_progress && !audio_in_progress;
    });

    function image_uploads_completed(event, data) {
        image_in_progress = false;

        $("#in-progress").dialog("close");

        if (submit_form) {
            $("#SermonAddForm").submit();
        }
    }

    function audio_uploads_completed(event, data) {
        audio_in_progress = false;

        $("#in-progress").dialog("close");

        if (submit_form) {
            $("#SermonAddForm").submit();
        }
    }
    
    $($(":input").addClass("dirty"));

    $($(":input").change(function(event) {
        $(this).addClass("dirty");
    }));

    function invalidate(dom_id) {
        has_errors = $("#flashMessage").length;
        if (!has_errors || $(dom_id).hasClass("form-error")) {
            $(dom_id).addClass("invalid"); 
        }
    }

    $(function() {
        invalidate("#PostTitle");
        invalidate("#SermonSeriesName");
        invalidate("#SermonSpeakerName");
    });

    $(function() {
        $("#PostDisplayDate").datepicker({
            altField: "#PostPublishTimestamp",
            altFormat: "yy-mm-dd",
            dateFormat: "MM d, yy"
        });
    });
<?php echo $this->Html->scriptEnd(); ?>
<?php $this->Html->css("/urg_sermon/css/urg_sermon.css", null, array("inline"=>false));
