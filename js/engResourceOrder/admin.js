$(function() {

    $('.search-toggle').click(function() {
        console.log('dicks');
        $('.search-form').slideToggle();
        return false;
    });

    $('.search-form form').submit(function() {
        $('.search-form').slideToggle();
        var data = $(this).serialize();

        // If Adv Search is empty, submit empty advSearch[]
        if (data === 'r=engResourceOrder%2Fadmin') {
            $.fn.yiiGridView.update('eng-resource-order-grid', {
                data: 'r=engResourceOrder%2Fadmin' + encodeURI('&advSearch[]=')
            });
           return false;
        }

        $.fn.yiiGridView.update('eng-resource-order-grid', {
            data: $(this).serialize()
        });
        return false;
    });

    $('.clear-form').click(function() {
        $('#adv-search-eng-clients option:selected').each(function() { $(this).removeAttr('selected'); });
        $('#adv-search-eng-assignments option:selected').each(function() { $(this).removeAttr('selected'); });
        return false;
    });

    $('#closeAdvancedSearch').click(function() {
        $('.search-form').slideUp();
        return false;
    });

});