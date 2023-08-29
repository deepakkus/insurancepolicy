$(function() {
    
    // Column Form
    
    $('.column-toggle').click(function(){
        $('.column-form').slideToggle();
        if ($('.search-form').is(':visible')) {
            $('.search-form').slideToggle();
        }
        return false;
    });
    
    $('.column-form form').submit(function(){
        $('.column-form').slideToggle();
        $.fn.yiiGridView.update('risk-score-grid', {
            data: $(this).serialize()
        });
        return false;
    });

    $('.clear-checked').click(function(){
        $('input[type=checkbox]').each(function(){
            $(this).removeAttr('checked');
        });
        return false;
    });

    $('#closeColumnsToShow').click(function() {
        $('.column-form').slideUp();
        return false;
    });

    $('.default-quick-view').click(function(){
        $('input[type=checkbox]').each(function(){
            $(this).removeAttr('checked');
        });
        $('input[name="columnsToShow[score_wds]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[address]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[city]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[state]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[scoreType]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[userName]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[clientName]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[geocoded]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[wds_geocode_level]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[date_created]"]').attr('checked', 'checked');
        return false;
    });

    // Search Form

    $('.search-toggle').click(function(){
        $('.search-form').slideToggle();
        if ($('.column-form').is(':visible')) {
            $('.column-form').slideToggle();
        }
        return false;
    });
    
    $('.search-form form').submit(function(){
        $('.search-form').slideToggle();
        $.fn.yiiGridView.update('risk-score-grid', {
            data: $(this).serialize()
        });
        return false;
    });

    $('#risk-score-search').submit(function() {
        $.fn.yiiGridView.update('risk-score-grid', {
            data: $(this).serialize()
        });
        return false;
    });

    $('.reset').click(function() {
        $('#risk-score-search input[type="radio"]').prop('checked', false);
        $('#risk-score-search input[type="text"]').val('');
        $('#risk-score-search select').prop('selectedIndex', 0);
        $('#risk-score-search').submit();
    });
    
    $('.export').click(function() {
        window.location = "/index.php?r=riskScore/riskScores&export=1" + $("#risk-score-search").serialize();
    });
});