// Add progress bar when submitted
$('#res-perimeters-form').submit(function () {
    $('#button-row').empty();
    $('#button-row').html(
        '<div style="width: 300px; margin: 0px; float:left; visibility: visible;" class="monitor-progress progress progress-striped active">' +
            '<div class="bar" style="width: 100%;margin:0;"></div>' +
        '</div>');
});