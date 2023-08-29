$(function() {

    $('.search-toggle').click(function(){
        $('.search-form').slideToggle();
        return false;
    });
    
    $('.search-form form').submit(function () {
        $('.search-form').slideToggle();
        $.fn.yiiGridView.update('pre-risk-grid', {
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
        $.fn.yiiGridView.update('pre-risk-grid', {
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
        $('#adv-search-statuses').val(["CANCELLED - SNOW", "COMPLETED - Delivered to Member", "Contacted 4 Times", "DECEASED", "Declined (Do Not Contact)", "Declined (USAA - Approval Denied)", "Declined (USAA - Approval Pending)", "Declined (USAA - Approved)", "Declined - Fireshield Enrolled", "FINAL CONTACT", "More Info Required", "NEED MORE INFO - Letter Mailed", "NO LONGER INSURED - PER USAA", "Postponed", "Postponed - Fire", "Postponed - Previously (Contacted 4 Times)", "Postponed - Previously (Declined)", "Postponed - Previously (More Info Required)", "Postponed - Previously (Previously Canceled - Snow)", "Postponed - Previously (Scheduled)", "Scheduled", "Scheduled - Previously (Contacted 4 Times)", "Scheduled - Previously (Declined USAA Approval Pending)", "Scheduled - Previously (More Info Required)", "Scheduled - Previously (Postponed - Fire)", "Scheduled - Previously (Postponed)", "Scheduled - Previously (Previously Canceled - Snow)", "T.B.S. - Letter Mailed", "TBS - Info Received", "TO BE SCHEDULED"]);
        return false;
    });
    
    $('.reset-filters').click(function(){
        $('input[type=text]').each(function(){
            $(this).attr('value', '');
        });
        $('select').each(function(){
            $(this).val('');
        });
        $.fn.yiiGridView.update('pre-risk-grid'
        );
        return false;
    });

    function checkColumns(fields) {
        if ($.isArray(fields)) {
            fields.forEach(function(field) {
                $('input[name="columnsToShow[' + field + ']"]').attr('checked', 'checked');
            });
        }
    }

    $('.default-quick-view').click(function(){
        $('input[type=checkbox]').each(function(){
            $(this).removeAttr('checked');
        });

        var fields = [
            'id',
            'member_member_num',
            'member_last_name',
            'property_address_line_1',
            'property_city',
            'property_state',
            'status',
            'engine',
            'ha_time',
            'ha_date',
            'call_list_month',
            'call_list_year'
        ];

        checkColumns(fields);
        return false;
    });

    $('.bi-monthly-view').click(function() {
        $('input[type=checkbox]').each(function(){
            $(this).removeAttr('checked');
        });

        var fields = [
            'member_member_num',
            'member_last_name',
            'property_address_line_1',
            'status',
            'ha_date',
            'completion_date'
        ];

        checkColumns(fields);
        return false;
    });

    $('.follow-up-view').click(function() {
        $('input[type=checkbox]').each(function() {
            $(this).removeAttr('checked');
        });

        var fields = [
            'member_member_num',
            'property_address_line_1',
            'property_city',
            'property_state',
            'property_zip',
            'ha_date',
            'recommended_actions',
            'follow_up_2_answer_1',
            'follow_up_2_answer_2',
            'follow_up_2_answer_3',
            'follow_up_2_answer_4',
            'follow_up_2_answer_5',
            'followUpAnswer6Combined',
            'follow_up_2_answer_7',
            'follow_up_2_answer_8'
        ];

        checkColumns(fields);
        return false;
    });

    $('.eom-reports-view').click(function() {
        $('input[type=checkbox]').each(function() {
            $(this).removeAttr('checked');
        });

        var fields = [
            'member_member_num',
            'contact_date',
            'ha_date',
            'ha_time',
            'completion_date',
            'recommended_actions',
            'property_state'
        ];

        checkColumns(fields);
        return false;
    });

    $('.eom-calls-view').click(function() {
        $('input[type=checkbox]').each(function() {
            $(this).removeAttr('checked');
        });

        var fields = [
            'member_last_name',
            'status',
            'property_pid',
            'property_geo_risk',
            'property_policy',
            'property_state',
            'ha_date',
            'ha_time',
            'call_list_month',
            'call_list_year',
            'completion_date',
            'recommended_actions'
        ];

        checkColumns(fields);
        return false;
    });

    $('.eom-data-view').click(function() {
        $('input[type=checkbox]').each(function() {
            $(this).removeAttr('checked');
        });

        var fields = [
            'member_member_num',
            'property_address_line_1',
            'property_state',
            'ha_date',
            'completion_date'
        ];

        checkColumns(fields);
        return false;
    });

    $('.delivery-view').click(function() {
        $('input[type=checkbox]').each(function() {
            $(this).removeAttr('checked');
        });

        var fields = [
            'member_member_num',
            'completion_date',
            'delivery_date',
            'delivery_method',
        ];

        checkColumns(fields);
        return false;
    });

    $('.mailing-view').click(function() {
        $('input[type=checkbox]').each(function() {
            $(this).removeAttr('checked');
        });

        var fields = [
            'member_member_num',
            'member_salutation',
            'member_first_name',
            'member_middle_name',
            'member_last_name',
            'status',
            'property_address_line_1',
            'property_address_line_2',
            'property_city',
            'property_state',
            'property_zip',
            'member_email_1',
            'ha_date',
            'appointmentWithSalutation',
            'delivery_date',
            'delivery_method',
            'member_mail_address_line_1',
            'member_mail_address_line_2',
            'member_mail_city',
            'member_mail_state',
            'member_mail_zip',
            'call_center_comments'
        ];

        checkColumns(fields);
        return false;
    });

});