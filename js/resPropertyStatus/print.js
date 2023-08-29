/**
 * resPropertyStatus/print.js
 *
 * Defines the WDSResPropertyStatusPrint namespace with associated methods
 * for the print view.
 */
(function (wds, $, undefined) {
    // Private members
    var PROPERTY_STATUS_DATA_KEY = 'property_status';

    /**
     * Initializes the view, setups up click events, etc.
     */
    wds.init = function () {
        // Retrieve the data from local storage.
        var data = $.parseJSON(localStorage.getItem(PROPERTY_STATUS_DATA_KEY));

        if (!data) {
            alert('Could not load data!');
            return false;
        }
        
        $('#lblFireName').text(data[0]['fire_name']);

        var tableContents = '';

        // Populate the activity log table.
        for (var i = 0; i < data.length; i++) {
            var address = data[i].property_address_line_1;

            if (data[i].property_address_line_2)
                address += data[i].property_address_line_2;

            address += ', ' + data[i].property_city;
            address += ', ' + data[i].property_state;
            address += ' ' + data[i].property_zip;

            var threat = data[i] === 1 ? 'Yes' : 'No';
            var division = data[i].division ? data[i].division : '';
            var priority = data[i].priority ? data[i].priority : '';
            var distance = data[i].distance ? parseFloat(data[i].distance).toFixed(2) : '';

            tableContents += '<tr>';
            tableContents += '<td>' + address + '</td>';
            tableContents += '<td>' + division + '</td>';
            tableContents += '<td>' + threat + '</td>';
            tableContents += '<td>' + priority + '</td>';
            tableContents += '<td>' + distance + '</td>';
            tableContents += '</tr>';
        }

        $('#activityLogTable > tbody').append(tableContents);
    }

}(window.WDSResPropertyStatusPrint = window.WDSResPropertyStatusPrint || {}, jQuery));

$(function () {
    WDSResPropertyStatusPrint.init();
});
