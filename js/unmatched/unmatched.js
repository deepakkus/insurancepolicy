    
/**
*   Description: Show the position on the map and center the map
*/
function showPositionAndCenter(position) {

    marker.setLatLng(position).addTo(map);

    //Center map on location
    map.panTo(marker.getLatLng());
}

/**
*   Description: If user drags the marker, update the form field values
*/
function updateForm() {
    var latLng = marker.getLatLng();
    $("#CoordinatesForm_lon").val(latLng.lng);
    $("#CoordinatesForm_lat").val(latLng.lat);
}


$(document).ready(function () {

    //Disable the submit button so that the user has to veryify map location
    $("#submitButton").prop("disabled", true);

    //Unlock submit button on map if user indicates they've verified
    $("#confirm").click(function () {
        if ($(this).is(':checked')) {
            $("#submitButton").prop("disabled", false);
        }
        else {
            $("#submitButton").prop("disabled", true);
        }
    });

    //Center map on coordinate if supplied
    if ($("#CoordinatesForm_lon").val() != '' && $("#CoordinatesForm_lat").val() != '') {
        showPositionAndCenter({ "lat": $("#CoordinatesForm_lat").val(), "lng": $("#CoordinatesForm_lon").val() })
    }

    //Update map marker and location
    $("#CoordinatesForm_lat").on('keyup paste', function () {
        if ($("#CoordinatesForm_lon").val() != '') {
            showPositionAndCenter({ "lat": $(this).val(), "lng": $("#CoordinatesForm_lon").val() })
        }
    });

    $("#CoordinatesForm_lon").on('keyup paste', function () {
        if ($("#CoordinatesForm_lat").val() != '') {
            showPositionAndCenter({ "lat": $("#CoordinatesForm_lat").val(), "lng": $(this).val() })
        }
    });

    //Validation latitude
    $("#CoordinatesForm_lat").on('keydown', function (e) {
        if (e.keyCode == 69) {
            e.preventDefault();
        }
    });

    //Validation longitude
    $("#CoordinatesForm_lon").on('keydown', function (e) {
        if (e.keyCode == 69) {
            e.preventDefault();
        }
    });

});
