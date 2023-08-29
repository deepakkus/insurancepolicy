$(function () {
    $('#lnkViewStatusHistory').click(showReportStatusHistory);
    $('#lnkReportStatusHistoryClose').click(hideReportStatusHistory);
    $('#lnkRiskInfo').click(toggleRiskInfo);
    $('#lnkAdditionalInfo').click(toggleAdditionalInfo);
    $('#lnkGeneralInfo').click(toggleGeneralInfo);
    $('#lnkSummaryAndNotes').click(toggleSummaryAndNotes);
    $('.lnkConditionData').click(function () {
        if ($(this).text().indexOf('+') > 0)
            $(this).text($(this).text().replace(/\+$/, '-'));
        else
            $(this).text($(this).text().replace(/\-$/, '+'));

        var $collapsableSection = $(this).parent().next();
        $collapsableSection.slideToggle();
    });

    //floating button:
    var offset = $("#saveButton").offset();
    var topPadding = 180;
    $(window).scroll(function () {
        if ($(window).scrollTop() > offset.top) {
            $("#saveButton").stop().animate({
                marginTop: $(window).scrollTop() - offset.top + topPadding
            }, 0);
        } else {
            $("#saveButton").stop().animate({
                marginTop: 0
            }, 0);
        };
    });

    //photo drag and drop
    var $fieldPhotos = $(".fieldPhotos");
    $("li", $fieldPhotos).draggable({
        cancel: "a.ui-icon", // clicking an icon won't initiate dragging
        revert: "invalid", // when not dropped, the item will revert back to its initial position
        containment: "document",
        helper: "clone",
        cursor: "move"
    });

    var $conditionPhotosCopyDrop = $(".conditionPhotosCopyDrop");
    // let the conditionPhotosCopyDrop divs be droppable, accepting the fieldphotos list items
    $conditionPhotosCopyDrop.droppable({
        accept: ".fieldPhotos > li",
        activeClass: "ui-state-highlight",
        drop: function (event, ui) {
            copyPhoto(ui.draggable, $(this));
        }
    });

    //image delete function
    $('.deleteImageButton').click(deleteImage);

    //sortable condition photos
    var $conditionPhotos = $(".conditionPhotos");
    $conditionPhotos.sortable({
        tolerance: 'pointer',
        revert: 'invalid',
        placeholder: 'placeholder tile well conditionPhoto span2',
        forceHelperSize: true,
        update: function (event, ui) {
            sortPhotos($(this));
        }
    });

    //fancybox
    $(".fancybox-thumb").fancybox({
        prevEffect: 'none',
        nextEffect: 'none',
        helpers: {
            thumbs: {
                width: 90,
                height: 90
            }
        }
    });

    //Yes Response Auto-fill pts
    $(".fsReportConditionResponseDropDown").change(function () {
        var conditionID = $(this).data('condition-id');
        var conditionDefaultYesScore = $(this).data('condition-default-yes-score');
        if ($(this).val() == '0') { //yes
            $("#FSConditions_" + conditionID + "_score").val(conditionDefaultYesScore);
            $("#TabLabel_" + conditionID).attr('style', 'color:#b30000;font-weight:bold');
        } else if ($(this).val() == '1') { //no
            $("#FSConditions_" + conditionID + "_score").val('');
            $("#TabLabel_" + conditionID).removeAttr('style');
        }
    });

    //Change to Completed status auto check Send Notification box
    $("#FSReport_status").change(function () {
        if ($('#FSReport_status').val() == 'Completed') {
            $('#send_notification').prop('checked', true);
        }
        else {
            $('#send_notification').prop('checked', false);
        }
    });

    //Update photo count label on change of conditionphotopath
    $(".conditionPhotoPath").change(function () {
        var conditionID = $(this).data('condition-id');
        if ($(this).val() == '')
            $photoCount = 0;
        else
            $photoCount = $(this).val().split("|").length;
        $("#photo_count_" + conditionID).text($photoCount);
    });

    // jQuery UI tooltip on edit photo links
    // Need the jQuery bridge because of bootstrap 2.x namespace collisions
    $.widget.bridge('uitooltip', $.ui.tooltip);
    $('.photo-edit').uitooltip();

    // Check to see if there's any saved work before navigating away
    $('.photo-edit').click(function() {
        if (!confirm('This will navigate away from the current report and you will lose any unsaved work.  Are you sure you want to do this?')) {
            return false;
        }
    });
});

// image copy function
function copyPhoto($fieldPhoto, $conditionCopyDrop) {
    $conditionPhotoPath = $conditionCopyDrop.parent().siblings('.conditionPhotoPath');
    $conditionPhotoPath.val(function (i, val) {
        return val + (!val ? '' : '|') + $fieldPhoto.data('photo-name');
    });
    $conditionPhotoPath.trigger('change');
    $conditionPhotos = $conditionCopyDrop.parent().siblings('.conditionPhotos');
    var newPhoto = '<div class="conditionPhoto tile well span2" data-photo-name="' + $fieldPhoto.data('photo-name') + '" id="condition_' + $conditionPhotos.data('condition-id') + '_photo_' + $fieldPhoto.data('photo-name') + '">';
    var deleteButtonID = 'delete_image_button_condition_' + $conditionPhotos.data('condition-id') + '_photo_' + $fieldPhoto.data('photo-name');
    newPhoto += '<input class="deleteImageButton" type="button" data-condition-id="' + $conditionPhotos.data('condition-id') + '" data-photo-name="' + $fieldPhoto.data('photo-name') + '" id="' + deleteButtonID + '">';
    newPhoto += '<a class="fancybox-thumb" rel="condition' + $conditionPhotos.data('condition-id') + 'photos" href="' + $fieldPhoto.data('photo-url') + '">';
    newPhoto += '<img src="' + $fieldPhoto.data('photo-url') + '" style="height: 90px" />';
    newPhoto += '</a></div>';
    $conditionPhotos.append(newPhoto);
    var deleteButtonSelector = '#' + deleteButtonID.replace(".", "\\.");
    $(deleteButtonSelector).on('click', deleteImage);
}

function sortPhotos($condPhotos) {
    var submited_photos_str = '';
    $condPhotos.children('.conditionPhoto').each(function () {
        submited_photos_str += $(this).data('photo-name') + "|";
    });
    $conditionPhotoPath = $condPhotos.siblings('.conditionPhotoPath');
    $conditionPhotoPath.val(submited_photos_str.slice(0, -1)).trigger('change');
}

function deleteImage() {
    var conditionPhotoID = '#condition_';
    conditionPhotoID += $(this).data('condition-id');
    conditionPhotoID += '_photo_';
    conditionPhotoID += $(this).data('photo-name');
    conditionPhotoID = conditionPhotoID.replace(".", "\\.");
    var $conditionPhoto = $(conditionPhotoID);
    $conditionPhoto.remove();
    sortPhotos($('#condition_photos_' + $(this).data('condition-id')));
}

function toggleAdditionalInfo() {
    $('#collapsableAdditionalInfo').slideToggle(400, function () {
        if ($('a#lnkAdditionalInfo').text() == 'Additional Info -')
            $('a#lnkAdditionalInfo').text('Additional Info +');
        else
            $('a#lnkAdditionalInfo').text('Additional Info -');
    });
}

function toggleRiskInfo() {
    $('#collapsableRiskInfo').slideToggle(400, function () {
        if ($('a#lnkRiskInfo').text() == 'Risk Info -')
            $('a#lnkRiskInfo').text('Risk Info +');
        else
            $('a#lnkRiskInfo').text('Risk Info -');
    });
}

function toggleGeneralInfo() {
    $('#collapsableGeneralInfo').slideToggle(400, function () {
        if ($('a#lnkGeneralInfo').text() == 'General Info -')
            $('a#lnkGeneralInfo').text('General Info +');
        else
            $('a#lnkGeneralInfo').text('General Info -');
    });
}

function toggleSummaryAndNotes() {
    $('#collapsableSummaryAndNotes').slideToggle(400, function () {
        if ($('a#lnkSummaryAndNotes').text() == 'Summary and Notes -')
            $('a#lnkSummaryAndNotes').text('Summary and Notes +');
        else
            $('a#lnkSummaryAndNotes').text('Summary and Notes -');
    });
}

function showHtmlTemplatePreview(url) {
    $('#iframeHtmlTemplatePreview').attr('src', url);
    var hiddenHtmlTemplatePreview = $('#hiddenHtmlTemplatePreview');

    hiddenHtmlTemplatePreview.show();

    var top = (($(window).height() - hiddenHtmlTemplatePreview.outerHeight()) / 2) + document.body.scrollTop;
    var left = (($(window).width() - hiddenHtmlTemplatePreview.outerWidth()) / 2) + document.body.scrollLeft;

    hiddenHtmlTemplatePreview.css('top', top + 'px');
    hiddenHtmlTemplatePreview.css('left', left + 'px');

    $('body').click(function () {
        $('body').unbind('click');
        hideHtmlTemplatePreview();
    });
}

function hideHtmlTemplatePreview() {
    $('#iframeHtmlTemplatePreview').attr('src', 'about:blank');
    $('#hiddenHtmlTemplatePreview').hide();
}

function togglePhotoAddRow(id) {
    var addPhotoRow = $('#addPhotoRow_' + id);
    if (addPhotoRow.is(':visible'))
        addPhotoRow.slideUp();
    else
        addPhotoRow.slideDown('slow');
}

function showReportStatusHistory() {
    var hiddenReportStatusHistory = $('#hiddenReportStatusHistory');

    hiddenReportStatusHistory.show();

    var top = (($(window).height() - hiddenReportStatusHistory.outerHeight()) / 2) + document.body.scrollTop;
    var left = (($(window).width() - hiddenReportStatusHistory.outerWidth()) / 2) + document.body.scrollLeft;

    hiddenReportStatusHistory.css('top', top + 'px');
    hiddenReportStatusHistory.css('left', left + 'px');
}

function hideReportStatusHistory() {
    $('#hiddenReportStatusHistory').hide();
}
