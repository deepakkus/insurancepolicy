$(function() {
    
    $('.search-button').click(function(){
        $('.search-form').toggle();
        return false;
    });

    $('.search-toggle').click(function(){
        $('.search-form').slideToggle();
        return false;
    });

    $('.search-form form').submit(function(){
    	$('.search-form').slideToggle();
        $.fn.yiiGridView.update('importFile-grid', {
            data: $(this).serialize()
        });
        return false;
    });
 
    $('.column-toggle').click(function(){
        $('.column-form').slideToggle();
        return false;
    });

    $('.column-form form').submit(function(){
        $('.column-form').slideToggle();
        $.fn.yiiGridView.update('importFile-grid', {
            data: $(this).serialize()
        });
        return false;
    });

    $('#closeColumnsToShow').click(function() {
        $('.column-form').slideUp();
        return false;
    });
        
    $('.clear-checked').click(function(){
        $('input[type=checkbox]').each(function(){
            $(this).removeAttr('checked');
        });
        return false;
    });

    $('.default-quick-view').click(function(){
        $('input[type=checkbox]').each(function(){
            $(this).removeAttr('checked');
        });
        
        $('input[name="columnsToShow[id]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[type]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[status]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[date_time]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[file_path]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[details]"]').attr('checked', 'checked');
        
        return false;
    });

});