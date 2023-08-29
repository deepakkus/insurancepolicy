$(function() {

    var gridMonitoringLog = 'res-monitor-log-grid';

    $('.column-toggle').click(function () {
        $('.column-form').slideToggle();
        return false;
    });

    $('.column-form form').submit(function () {
        $('.column-form').slideToggle();
        var data = $(this).serialize();
        var adminComponent = 'r=' + encodeURIComponent('resMonitorLog/admin');
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
        $('input[name="columnsToShow[Fire_City]"]').prop('checked', 'checked');
        return false;
    });
    $('.submitButton').click(function () {
        if (!($('input[type=checkbox]').is(':checked'))) {
            alert('Please check at least one column');
            return false;
        }

        return true;
    });
    
        $('.default-quick-view').click(function(){
            $('input[type=checkbox]').each(function(){
                $(this).removeAttr('checked');
            });
        
            $('input[name="columnsToShow[Fire_Name]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[fire_alternate_name]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[Fire_City]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[Fire_State]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[Fire_Size]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[Fire_Containment]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[Dispatcher]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[monitored_time_stamp]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[monitored_date_stamp]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[closest]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[client_triggered]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[client_noteworthy]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[Comments]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[resFireObs]"]').prop('checked', 'checked');
            $('input[name="columnsToShow[Smoke_Check]"]').prop('checked', 'checked');
        
            return false;
        });
});