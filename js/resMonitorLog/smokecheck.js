$(function() {

    var gridMonitoringLog = 'res-smokecheck-grid';

    $('.column-toggle').click(function () {
        $('.column-form').slideToggle();
        return false;
    });

    $('.column-form form').submit(function () {
        $('.column-form').slideToggle();
        var data = $(this).serialize();
        var adminComponent = 'r=' + encodeURIComponent('resMonitorLog/smokeCheck');
        if (data === adminComponent) {
            $.fn.yiiGridView.update(gridMonitoringLog, {
                data: adminComponent + encodeURI('&columnsToShow[]=')
            });
            return false;
        }
        $.fn.yiiGridView.update(gridMonitoringLog, {
            data: $(this).serialize()
        });
        return false;
    });

    $('#closeColumnsToShow').click(function () {
        $('.column-form').slideUp();
        return false;
    });

    $('.clear-checked').click(function () {
        $('input[type=checkbox]').each(function () {
            $(this).removeAttr('checked');
        });
        return false;
    });

    $('.default-quick-view').click(function () {
        $('input[type=checkbox]').each(function () {
            $(this).removeAttr('checked');
        });
        $('input[name="columnsToShow[Fire_City]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[Fire_State]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[Fire_Size]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[Fire_Containment]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[Fire_Fuels]"]').attr('checked', 'checked');
        return false;
    });

});