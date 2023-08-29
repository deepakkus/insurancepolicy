/**
 * resCallList/create.js
 *
 * Defines the WDSResCallListAdd namespace with associated methods
 * for the resCallList/create view.
 */
(function(wds, $, undefined) {

    var gridResponseCallListAdd = 'gridNewCallListGrid';

    wds.init = function() {
        $('#btnFindProperty').click(function () {
            var pid = $('#ResCallList_property_id').val();
            if (!$.isNumeric((pid)))
            {
                alert("Please Enter a Numeric Value.");
                return false;
            }
            $.fn.yiiGridView.update(gridResponseCallListAdd, {
                type: 'get',
                url: $(this).attr('href'),
                data: {
                    pid: pid
                },
            });

            return false;
        });
    };

}(window.WDSResCallListAdd = window.WDSResCallListAdd || {}, jQuery));

$(function() {
    WDSResCallListAdd.init();
});
