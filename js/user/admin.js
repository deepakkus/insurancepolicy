/**
 * user/admin.js
 *
 * Defines the WDSUser namespace with associated methods
 * for the user/manageClientUsers view.
 */
(function (wds, $, undefined) {
    // Private members
    var gridclientUser = 'gridclientUser';

    /**
     * Initializes the view, setups up click events and the like.
     */
    wds.init = function () {

        $('#btnClientUser').click(function () {

            var selectedclientUserIDs = $('#hiddenclientUserIDs').val();
            var statusType = $('#ddlChangestatus').val();

            var data = { "data": '{"data": {"statusType": ' + statusType + ', "clientUserIDs": [' + selectedclientUserIDs + ']}}' };

            $.ajax({
                url: 'index.php?r=user/changeStatus',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function(data) {
                    $('#changeStatusModal').modal('hide');
                    if (data && data.error == 0) {
                        $.fn.yiiGridView.update(gridclientUser);
                    }
                    else {
                        alert(data.errorMessage);
                    }
                },
                error: function(XMLHttpRequest) {
                    console.log(XMLHttpRequest);
                    $('#changeStatusModal').modal('hide');
                    alert('Failed to set change status! Please try again.');
                }
            });

            return false;
        });

    };
    
    
    /**
     * Click handler for the change status button.
     * @param object[] clientUsers
     */
    wds.changeStatus = function (clientUsers) {
        var ids = [];

        for (var i = 0; i < clientUsers.length; i++) {
            ids.push($(clientUsers[i]).val());
        }

        var idsString = ids.join(',');

        $('#hiddenclientUserIDs').val(idsString);

        $('#changeStatusModal').modal();
    };

    
}(window.WDSUser = window.WDSUser || {}, jQuery));

$(function() {
    WDSUser.init();

    // Page Size Dropdown
    $('#optnpageSize').change(function () {       
        $.fn.yiiGridView.update('gridclientUser', {
            data: $(this).serialize()
        });
        return false;
    });
});


