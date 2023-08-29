var UserClient = (function () {
    return {
        init: function () {
            //pull parent-cleint data from hidden element data attribute
            var parentClientData = $('#User_user_clients').data('parent-child-json');
            var userClientSelector = $('#User_user_clients');
            //when parent client chages
            $('#User_client_id').change(function () {
                //empty out the user clients selector
                userClientSelector.empty();
                //add parentclient to user clients selector
                var parentClientID = $(this).val();
                userClientSelector.append($("<option></option>")
                    .attr("value",parentClientID)
                    .text(parentClientData[parentClientID]['name'])
                );
                //add any children to user clients selector
                var parentChildren = parentClientData[parentClientID]['children'];
                if ($(parentChildren).length > 0) {
                    $.each(parentChildren, function (key, value) {
                        userClientSelector.append($("<option></option>")
                            .attr("value", key).text(value));
                    });
                }
                //select parent client only by default
                userClientSelector.val(parentClientID);
            });
            //if affiliation is changed do some User Client setup
            $('#affiliation').change(function () {
                var radioChecked = $("input:radio[name='affiliation']:checked").val();
                if (radioChecked == 'wds') {
                    $('#User_user_clients').empty();
                    $.each(parentClientData, function (key, value) {
                        userClientSelector.append($("<option></option>")
                            .attr("value", key).text(value.name));
                    });
                } else if (radioChecked == 'alliance') {
                    $('#User_user_clients').empty();
                } else if (radioChecked == 'client') {
                    $('#User_user_clients').empty();
                }
            });
        }
    };
})();

UserClient.init();

