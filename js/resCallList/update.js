/**
 * resCallList/update.js
 *
 * Defines the WDSResCallUpdate namespace with associated methods
 * for the resCallList/update view.
 */
(function (wds, $, undefined) {

    /**
     * Initializes the view, setups up click events and the like.
     */
    wds.init = function () {
        var callAttemptID = getQueryStringVariables()['callAttemptID'];
        
        if (callAttemptID > 0) {
            // Highlight the selected row in the grid.
            $('#gridCallAttempts table tbody tr:has(td:nth-child(1):contains(' + callAttemptID + '))').addClass('selected');
            $('#lnkAddNewCallAttempt').show();
        } else {
            $('#lnkAddNewCallAttempt').hide();
        }

        $('#lnkAddNewCallAttempt').click(function () {
            // Redirect to this page without a callAttemptID (which will allow us to create a new call attempt).
            var resCallListID = getQueryStringVariables()['id'];
            location.href = 'index.php?r=resCallList/update&id=' + resCallListID;
        });
    };

    /**
    * Grid row selection event handler
    */
    wds.onGridRowSelected = function(id) {
        var resCallListID = getQueryStringVariables()['id'];
        var gridID = $.fn.yiiGridView.getSelection(id);
        location.href = 'index.php?r=resCallList/update&id=' + resCallListID + '&callAttemptID=' + gridID;
    }

    /**
    * Gets query string variables from the URL.
    */
    function getQueryStringVariables() {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }

}(window.WDSResCallUpdate = window.WDSResCallUpdate || {}, jQuery));

$(function () {
    WDSResCallUpdate.init();
});
