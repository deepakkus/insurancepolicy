$(function() {

    $('.search-toggle').click(function(){
        $('.search-form').slideToggle();
        return false;
    });
    
    $('.search-form form').submit(function(){
        $('.search-form').slideToggle();
        $.fn.yiiGridView.update('fsReport-grid', {
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
        $.fn.yiiGridView.update('fsReport-grid', {
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
    
    $('#closeAdvancedSearch').click(function() {
        $('.search-form').slideUp();
        return false;
    });    
    
    $('.default-advanced').click(function(){
        $('input[type=text]').each(function(){
            $(this).attr('value', '');
        });
        $('select').each(function(){
            $(this).val('');
        });
        $('#adv-search-statuses').val(["New", "FRA", "Edit Ready", "Editor"]);
        return false;
    });

    $('.default-quick-view').click(function(){
        $('input[type=checkbox]').each(function(){
            $(this).removeAttr('checked');
        });
        $('input[name="columnsToShow[id]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[type]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[status]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[assigned_user_name]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[address_line_1]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[city]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[state]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[submit_date]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[due_date]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[fs_user_client_name]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[scheduled_call]"]').attr('checked', 'checked');
        return false;
    });
});