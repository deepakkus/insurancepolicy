function checkForDeactivate() {
        
    var radioChecked = $("input:radio[name='affiliation']:checked").val();

    if (radioChecked == 'wds') {
        //Set value to nothing
        $('#User_client_id').val(0);
        $('#User_alliance_id').val(0);
        //Disable
        $('#User_client_id').prop("disabled", true);
        $('#User_alliance_id').prop("disabled", true);
    } else if (radioChecked == 'alliance') {
        //Clear out value
        $('#User_client_id').val(0);
        //Disable
        $('#User_client_id').prop("disabled", true);
        $('#User_alliance_id').prop("disabled", false);
    } else if (radioChecked == 'client') {
        //Set value to nothing
        $('#User_alliance_id').val(0);
        //Disable
        $('#User_client_id').prop("disabled", false);
        $('#User_alliance_id').prop("disabled", true);
    }
}

function selectAffiliate() {

    var allianceIndex = $("#User_alliance_id option:selected").index();
    var clientIndex = $("#User_client_id option:selected").index();

    if (clientIndex > 0) {
        $("input[name=affiliation][value=client]").prop('checked', true);
    } else if (allianceIndex > 0) {
        $("input[name=affiliation][value=alliance]").prop('checked', true);
    } else {
        $("input[name=affiliation][value=wds]").prop('checked', true);
    }
}

$('#affiliation').click(function () {
    checkForDeactivate();
});