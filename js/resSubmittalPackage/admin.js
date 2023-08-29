$(document).ready(function() {

    // Disable generate 'Submittal Package button' unless both a notice and RO are selected

    $(document).on('click', '#res-notice-grid input[type=checkbox], #eng-scheduling-grid input[type=checkbox]', function(e) {
        var noticeChecked = $('input[name="selectedNoticeId[]"]:checked');
        var resourceChecked = $('input[name="selectedEngineID[]"]:checked');
        if (noticeChecked.length && resourceChecked.length) {
            $('input[type=submit]').removeClass('disabled');
        }
        else {
            $('input[type=submit]').addClass('disabled');
        }
    });

    // Supply the Notice Number and RO Number for user feedback

    $(document).on('click', '#res-notice-grid input[type=checkbox]', function(e) {
        if ($('input[name="selectedNoticeId[]"]:checked').length) {

            var nodes = e.target.parentNode.parentNode.childNodes;
            var notice = nodes[1].innerHTML;
            var fireID = nodes[2].innerHTML;
            var clientID = nodes[3].innerHTML;
            var state = nodes[4].innerHTML;

            $('#selected-notice-view').html(notice);
            $.fn.yiiGridView.update('eng-scheduling-grid', {
                type: 'get',
                url: 'index.php?r=resSubmittalPackage/admin',
                data: {
                    fireID: fireID,
                    clientID: clientID
                }
            });

            // If state is in CO, add flash message that prompts the user to download a MOU document

            if (state === 'CO') {
                $('<div/>', { class: 'flash-wrapper', css: { position: 'inherit' } }).append(
                    $('<div/>', { class: 'flash' }).append(
                        $('<div/>', {
                            html: 'Your fire is in Colorado.<br />Remember to <b>Download a MOU!</b>',
                            class: 'alert alert-success',
                            css: {
                                color: 'red',
                                fontSize: '1.4em'
                            }
                        })
                    )
                ).appendTo('#flash-wrapper-div')
                 .delay(3000)
                 .fadeOut('slow', function() {
                     this.remove();
                 });
            }
        }
        else {
            $('#selected-notice-view').html('');
            $.fn.yiiGridView.update('eng-scheduling-grid', {
                type: 'get',
                url: 'index.php?r=resSubmittalPackage/admin',
                data: {
                    fireID: null
                }
            });
        }
    });

    $(document).on('click', '#eng-scheduling-grid input[type=checkbox]', function(e) {
        if ($('input[name="selectedEngineID[]"]:checked').length) {
            var RO = e.target.parentNode.parentNode.childNodes[1].innerHTML;
            $('#selected-resource-view').html(RO);
        }
        else {
            $('#selected-resource-view').html('');
        }
    });

    // When form is submitted, populate hidden input variables
    $('#submittal-package-form').submit(function(e) {
        if ($('input[type=submit]').hasClass('disabled')) {
            return false;
        }
        if ($('#map_image').val() === '') {
            alert('There must be a map image!');
            return false;
        }
        var noticeID = $.fn.yiiGridView.getChecked("res-notice-grid", "selectedNoticeId").toString();
        var engineID = $.fn.yiiGridView.getChecked("eng-scheduling-grid", "selectedEngineID").toString();
        document.forms[0].elements['submittal[notice-id]'].value = noticeID;
        document.forms[0].elements['submittal[engine-id]'].value = engineID;
    });
});

// Clear values when user selects browsers 'back' button

window.onload = function() {
    $('input[name="selectedNoticeId[]"]:checked').each(function() { this.checked = false; });
    $('input[name="selectedEngineID[]"]:checked').each(function() { this.checked = false; });
    document.forms[0].elements['submittal[notice-id]'].value = '';
    document.forms[0].elements['submittal[engine-id]'].value = '';
    document.forms[0].elements['submittal[map_image]'].value = '';
};