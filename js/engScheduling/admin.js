/* Modal Script */

var AdminEngineModal = {
    init: function() {
        // Delete button in the Modal
        $('#btnDeleteScheduledEngine').click(function() {
            if (confirm('All associated shift tickets will be deleted.  Is this ok?')) {
                $.post(this.href, function(data) {
                    window.location.reload();
                });
            }
            return false;
        });
    }
};

/* Admin Search Form */

var engineSchedulingGrid = 'eng-scheduling-grid';

$('.search-button').click(function() {
    $('#grid-search-form').slideToggle();
    return false;
});

$('#EngScheduling_resource_order_num').keypress(function (e) {
    var keycode = e.charCode || e.keyCode;
    
    if (keycode == 46) {
        return false;
    }
    
});
$('.search-client-links').click(function() {
    $('#grid-search-form').slideToggle();
    $.fn.yiiGridView.update(engineSchedulingGrid, {
        type: 'POST',
        data: {
            searchClientID: this.getAttribute('data-id')
        }
    });
    return false;
});

$('#closeGridSearch').click(function() {
    $('#grid-search-form').slideUp();
    return false;
});

/* Calendar Search Form */

$('.calendar-search-button').click(function() {
    $('#calendar-search-form').slideToggle();
    return false;
});

$('#closeCalendarSearch').click(function() {
    $('#calendar-search-form').slideUp();
    return false;
});

$('.clear-checked').click(function() {
    $('input[type=checkbox]').each(function() {
        $(this).removeAttr('checked');
    });
    return false;
});

$('input[name=calendarSearchSubmit]').click(function() {
    var searchData = { assignments: [], clients: [] };
    var dateString = moment($('.arrow-left').data('date')).add({ days: 7 }).format();
    var url = $('.arrow-left').attr('href');

    $('.engine-assignments-checkbox:checked').each(function() { searchData.assignments.push(this.value); });
    $('.engine-clients-checkbox:checked').each(function() { searchData.clients.push(this.value); });
    $('#calendar-search-form').slideToggle();

    return loadCalendar(url, dateString, JSON.stringify(searchData));
});

/* Calendar */

$(document).on('click', '.engine-calendar', function() {
    $.get(this.href, function(data) {
        $('#schedule-engine-modal').modal('show').find('#modal-content').html(data);
    }, 'html').error(function(jqXHR) {
        console.log(jqXHR);
    });
    return false;
});

$(document).on('click', '.engine-calendar-new > a', function() {
    if (!confirm('Do you want to create a new calendar entry?')) {
        return false;
    }
    window.location.href = this.href;
    return false;
});

$(document).on('click', '.arrow-left', function() {
    var date = this.getAttribute('data-date');
    loadCalendar(this.href, date);
    return false;
});

$(document).on('click', '.arrow-right', function() {
    var date = this.getAttribute('data-date');
    loadCalendar(this.href, date);
    return false;
});

function loadCalendar(url, dateString, searchData) {
    var searchData = searchData || null;
    $('.calendar-loading').addClass('grid-view-loading');
    $.get(url, { datestring: dateString, searchdata: searchData }, function(data) {
        $('#calendar-container').html(data);
        $('.calendar-loading').removeClass('grid-view-loading');
        var $table = $('#calendar-container table');
        $table.floatThead({
            responsiveContainer: function($table){
                return $table.closest('.table-responsive');
            }
        });
    }, 'html').error(function(jqXHR) {
        console.log(jqXHR);
    });
}

$(function() {
    var $table = $('#calendar-container table');
    $table.floatThead({
        responsiveContainer: function($table) {
            return $table.closest('.table-responsive');
        }
    });
});