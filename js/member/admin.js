$(function() {
    
    $('.search-button').click(function(){
        $('.search-form').toggle();
        return false;
    });

    $('.search-toggle').click(function () {
        $('.search-form').slideToggle();
        $('.column-form').hide();
        return false;
    });

    $('.search-form form').submit(function(){
    	$('.search-form').slideToggle();
        $.fn.yiiGridView.update('member-grid', {
            data: $(this).serialize()
        });
        return false;
    });
 
    $('.column-toggle').click(function () {
        $('.column-form').slideToggle();
        $('.search-form').hide();
        return false;
    });

    $('.column-form form').submit(function () {
        if ($("[type='checkbox']:checked").length <= 0)
        {
            alert("Please select some columns to show.");
            return false;
        }
        $('.column-form').slideToggle();
        $.fn.yiiGridView.update('member-grid', {
            data: $(this).serialize()
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
    $('.clear-checked').click(function(){
        $('input[type=checkbox]').each(function(){
            $(this).removeAttr('checked');
        });
        return false;
    });

    $('.default-advanced').click(function(){
        $('input[type=text]').each(function(){
            $(this).attr('value', '');
        });
        $('select').each(function(){
            $(this).val('');
        });
        $('#adv-search-fs-statuses').val(["not enrolled", "ineligible", "offered", "enrolled", "declined"]);
        return false;
    });

    $('.default-quick-view').click(function(){
        $('input[type=checkbox]').each(function(){
            $(this).removeAttr('checked');
        });
        
        $('input[name="columnsToShow[mid]"]').prop('checked', 'checked');
        $('input[name="columnsToShow[member_num]"]').prop('checked', 'checked');
        $('input[name="columnsToShow[first_name]"]').prop('checked', 'checked');
        $('input[name="columnsToShow[last_name]"]').prop('checked', 'checked');
        $('input[name="columnsToShow[home_phone]"]').prop('checked', 'checked');
        $('input[name="columnsToShow[work_phone]"]').prop('checked', 'checked');
        $('input[name="columnsToShow[cell_phone]"]').prop('checked', 'checked');
        $('input[name="columnsToShow[email]"]').prop('checked', 'checked');
        $('input[name="columnsToShow[mail_address_line_1]"]').prop('checked', 'checked');
		$('input[name="columnsToShow[mail_city]"]').prop('checked', 'checked');
		$('input[name="columnsToShow[mail_state]"]').prop('checked', 'checked');
		$('input[name="columnsToShow[mail_zip]"]').prop('checked', 'checked');
		$('input[name="columnsToShow[client]"]').prop('checked', 'checked');
        
        return false;
    });

});