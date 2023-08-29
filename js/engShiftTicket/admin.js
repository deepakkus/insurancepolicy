$(function() {

    // ------------ calendar view ----------------------   

    /* Shift Ticket Search Form */

    $('#shift-ticket-filter-button').click(function() {
        $('#shift-ticket-filter-form').slideToggle();
        return false;
    });

    $('#close-shift-ticket-filter').click(function() {
        $('#shift-ticket-filter-form').slideUp();
        return false;
    });

    $('.clear-checked').click(function() {
        $('input[type=checkbox]').each(function() {
            $(this).removeAttr('checked');
        });
        return false;
    });

    /* Shift Ticket Table */

    $('#shift-ticket-todays').click(function() {
        updateGetNewShiftTicketTable(this.href, this.dataset.date);
        return false;
    });

    // Listener for the back button - loads the shift ticket table with previous day's contents
    $('#shift-tickets').on('click', '#back', function() {
        updateGetNewShiftTicketTable(this.href, this.dataset.date);
        return false;
    });

    // Listener for the forward button - loads the shift ticket table with next day's contents
    $('#shift-tickets').on('click', '#forward', function() {
        updateGetNewShiftTicketTable(this.href, this.dataset.date);
        return false;
    });

    // Listener for filter options
    $('input[name=filter-submit]').click(function() {

        var url = $("#back").attr('href');
        var dateString = $("#current-date").data("date");
        var filterData = { clients: [], fires: [], submitted: [], completed: [] };

        $('.shift-tickets-clients-checkbox:checked').each(function() { filterData.clients.push(this.value); });
        $('.shift-tickets-fires-checkbox:checked').each(function() { filterData.fires.push(this.value); });
        $('.shift-tickets-submitted-checkbox:checked').each(function() { filterData.submitted.push(this.value); });
        $('.shift-tickets-completed-checkbox:checked').each(function() { filterData.completed.push(this.value); });
        
        $('#shift-ticket-filter-form').slideToggle();

        return updateGetNewShiftTicketTable(url, dateString, JSON.stringify(filterData));
    });

    // Update the html
    function updateGetNewShiftTicketTable(url, dateString, filterData) {
        var filterData = filterData || null;
        $('.shift-ticket-table-loading').addClass('grid-view-loading');
        $.get(url, { date: dateString, filterData: filterData }, function (response) {
            $('#shift-tickets').html(response);
        });
        return false;
    }

    // ------------ grid view ----------------------       

    $('#shiftTicketGridAdvSearchToggle').click(function() {
        $('#shiftTicketGridAdvSearchForm').slideToggle();
        return false;
    });

    $('#shiftTicketGridAdvSearchForm form').submit(function() {
        $('#shiftTicketGridAdvSearchForm').slideToggle();
        $.fn.yiiGridView.update('shiftTicketGrid', {
            data: $(this).serialize()
        });
        return false;
    });

    $('#shiftTicketGridCloseAdvancedSearch').click(function() {
        $('#shiftTicketGridAdvSearchForm').slideUp();
        return false;
    });

    $('#shiftTicketGridColumnsToggle').click(function() {
        $('#shiftTicketGridColumnForm').slideToggle();
        return false;
    });

    $('#shiftTicketGridColumnForm form').submit(function() {
        $('#shiftTicketGridColumnForm').slideToggle();
        $.fn.yiiGridView.update('shiftTicketGrid', {
            data: $(this).serialize()
        });
        return false;
    });

    $('#shiftTicketGridCloseColumnsToShow').click(function() {
        $('#shiftTicketGridColumnForm').slideUp();
        return false;
    });

});

$(document).on("click", ".activities-popup", function() {
    $("#modal-container").find("#modal-content").html(this.dataset.activities);
    $("#modal-container").dialog("option", "title", this.dataset.activitiesTitle);
    $("#modal-container").dialog("open");
    return false;
});
