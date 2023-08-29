$(function() {

    $('.search-toggle').click(function(){
        $('.search-form').slideToggle();
        return false;
    });
    
    $('.search-form form').submit(function(){
        $('.search-form').slideToggle();
        var advFormData = $(this).serialize();
        $.fn.yiiGridView.update('properties-grid', {
            data: advFormData
        });
        $('.search-toggle').css("color", "red");
        return false;
    });
    
    $('.column-toggle').click(function(){
        $('.column-form').slideToggle();
        return false;
    });
    
    $('.column-form form').submit(function () {
        var totalColumn = $("input:checked").length;
        if (totalColumn > 40)
        {
           alert("Please select maximum 40 columns");
           $('.column-form').slideToggle();
           return false;
        }
        $('.column-form').slideToggle();
        $.fn.yiiGridView.update('properties-grid', {
            data: $(this).serialize()
        });
        return false;
    });

       $('.monitor-buttons form').submit(function() {
        $.fn.yiiGridView.update('res-monitor-log-grid', {
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
        $('#adv-search-fs-statuses').val(["not enrolled", "ineligible", "offered", "enrolled", "declined"]);
        return false;
    });

    $('.default-quick-view').click(function(){
        $('input[type=checkbox]').each(function(){
            $(this).removeAttr('checked');
        });
        $('input[name="columnsToShow[pid]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[member_mid]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[member_first_name]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[member_last_name]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[address_line_1]"]').attr('checked', 'checked');
        $('input[name="columnsToShow[city]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[state]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[geo_risk]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[policy]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[policy_status]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[fireshield_status]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[response_status]"]').attr('checked', 'checked');
		$('input[name="columnsToShow[pre_risk_status]"]').attr('checked', 'checked');
        return false;
    });

});