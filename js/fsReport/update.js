$(function () {
    $('#lnkViewStatusHistory').click(showReportStatusHistory);
    $('#lnkReportStatusHistoryClose').click(hideReportStatusHistory);
});

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