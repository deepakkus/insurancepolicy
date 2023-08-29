/**
 * resPolicyAction/policyAdd.js
 *
 * Defines the WDSResCallListAdd namespace with associated methods
 * for the resPolicyAction/policyAdd view.
 */
(function(wds, $, undefined) {

    wds.init = function() {

        /**
         * Refresh new PID form
         */
        $('#formAddToPolicyholderActions').submit(function () {
            
            var pid = $('#add-new-action-pid').val();
            var queryVars = getQueryStringVariables(),
                fid = queryVars['fid'],
                cid = queryVars['cid'],
                fn = queryVars['fn'],
                cn = queryVars['cn'];
            $.fn.yiiGridView.update('gridAddToPolicyholderActions', {
                type: 'get',
                url: $(this).attr('href'),
                data: {
                    pid: pid,
                    fid: fid,
                    cid: cid,
                    fn: fn,
                    cn: cn
                }
            });

            return false;
        });
    };
    $(document).ready(function(){
    $('#btnFindProperty').click(function () {
        var pid = $('#add-new-action-pid').val();
        if (!$.isNumeric((pid))) {
            alert("Please Enter a Numeric Value.");
            return false;
        }
    });
    });
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

}(window.WDSResNewPIDActionAdd = window.WDSResNewPIDActionAdd || {}, jQuery));

$(function() {
    WDSResNewPIDActionAdd.init();
});
