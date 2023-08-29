/**
 * resCallList/admin.js
 *
 * Defines the WDSResCallList namespace with associated methods
 * for the resCallList/admin view.
 */
(function (wds, $, undefined) {
    // Private members
    var gridResponseCallList = 'gridResponseCallList';

    /**
     * Initializes the view, setups up click events and the like.
     */
    wds.init = function () {
        $('.search-toggle').click(function () {
            $('.search-form').slideToggle();
            return false;
        });

        $('.search-form form').submit(function () {
            $('.search-form').slideToggle();
            $.fn.yiiGridView.update(gridResponseCallList, {
                data: $(this).serialize()
            });
            return false;
        });

        $('.column-toggle').click(function () {
            $('.column-form').slideToggle();
            return false;
        });

        $('.column-form form').submit(function () {

            $('.column-form').slideToggle();
            $.fn.yiiGridView.update(gridResponseCallList, {
                data: $(this).serialize(), type: 'POST'
            });
            return false;
        });

        $('.clear-checked').click(function () {
            $('input[type=checkbox]').each(function () {
                if ($(this).val() != 'client_name') {
                    $(this).removeAttr('checked');
                }
            });
            return false;
        });

        $('#closeColumnsToShow').click(function () {
            $('.column-form').slideUp();
            return false;
        });

        $('#closeAdvancedSearch').click(function () {
            $('.search-form').slideUp();
            return false;
        });

        $('.default-advanced').click(function () {
            $('input[type=text], select').each(function () {
                $(this).val('');
            });
            return false;
        });

        $('#defaultQuickView').click(function () {
            $('input[type=checkbox]').each(function () {
                $(this).removeAttr('checked');
            });

            var columnsToCheck = [
                'do_not_call',
                'assigned_caller_user_name',
                'client_name',
                'fire_name',
                'notice_name',
                'res_triggered_priority',
                'res_triggered_threat',
                'res_triggered_distance',
                'member_first_name',
                'member_last_name',
                'property_address_line_1',
                'property_city',
                'property_state'
            ];

            for (var i in columnsToCheck) {
                $('input[name="wds_response_call_list_columnsToShow[' + columnsToCheck[i] + ']"]').prop('checked', true);
            }
            return false;
        });

        $('#btnAssignCaller').click(function () {
            var selectedCallListIDs = $('#hiddenSelectedCallerIDs').val();

            var userID = $('#ddlCaller').val();
            var data = { "data": '{"data": {"assignedCallerUserID": ' + userID + ', "callListIDs": [' + selectedCallListIDs + ']}}' };

            // Set wait cursor.
            $("body").css("cursor", "progress");

            // Save the caller IDs.
            $.ajax({
                type: 'POST',
                url: 'index.php?r=resCallList/assignCallerToCalls',
                data: data
            }).done(function (result) {
                $('#selectCallerModal').modal('hide');
                if (result && result.error == 0) {
                    // Success. Refresh the grid.
                    $.fn.yiiGridView.update(gridResponseCallList);

                    // Restore the default cursor.
                    $("body").css("cursor", "default");
                } else {
                    // An error occurred.
                    alert(result.errorMessage);
                }
            }).fail(function (jqXHR, text) {

                console.log(jqXHR);
                console.log(text);

                $('#selectCallerModal').modal('hide');

                alert('Failed to assign the caller! Please try again.');

                // Restore cursor.
                $("body").css("cursor", "default");
            });

            return false;
        });

        $('#btnPublishCall').click(function () {

            var selectedCallListIDs = $('#hiddenSelectedCallerPublishIDs').val();
            var publishedType = $('#ddlPublish').val();

            var data = { "data": '{"data": {"publishedCallType": ' + publishedType + ', "callListIDs": [' + selectedCallListIDs + ']}}' };

            $.ajax({
                url: 'index.php?r=resCallList/publishCalls',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function (data) {
                    $('#publishCallsModal').modal('hide');
                    if (data && data.error == 0) {
                        $.fn.yiiGridView.update(gridResponseCallList);
                    }
                    else {
                        alert(data.errorMessage);
                    }
                },
                error: function (XMLHttpRequest) {
                    console.log(XMLHttpRequest);
                    $('#publishCallsModal').modal('hide');
                    alert('Failed to set published status! Please try again.');
                }
            });

            return false;
        });

    };

    /**
     * Click handler for the assign caller button.
     * @param object[] callListIDs
     */
    wds.assignItemsToCaller = function (callListIDs) {
        var ids = [];

        for (var i = 0; i < callListIDs.length; i++) {
            ids.push($(callListIDs[i]).val());
        }

        var idsString = ids.join(',');

        $('#hiddenSelectedCallerIDs').val(idsString);

        // Launch the lightbox to select the caller.
        $('#selectCallerModal').modal();
    };

    /**
     * Click handler for the publish calls button.
     * @param object[] callListIDs
     */
    wds.publishCalls = function (callListIDs) {
        var ids = [];

        for (var i = 0; i < callListIDs.length; i++) {
            ids.push($(callListIDs[i]).val());
        }

        var idsString = ids.join(',');

        $('#hiddenSelectedCallerPublishIDs').val(idsString);

        $('#publishCallsModal').modal();
    };


}(window.WDSResCallList = window.WDSResCallList || {}, jQuery));

$(function () {
    WDSResCallList.init();
});
