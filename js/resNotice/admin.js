function resNoticePopulatePerimeters(fire_id, action_url) {
    $.post(action_url, { fireID: fire_id }, function(data) {
        $("#ResNotice_perimeter_id").html(data);
        $.event.trigger('html:perimeter');
    }, 'html').error(function(jqXHR) {
        console.log(jqXHR);
    });
}

function resNoticePopulateWdsStatusRadio(fire_id, client_id, action_url) {
    $.post(action_url, { fireID: fire_id, clientID: client_id }, function (data) {
        $("#wds-status-radio-list").html(data);
    }, 'html').error(function (jqXHR) {
        console.log(jqXHR);
    });
}

$(document).on('html:perimeter', function(event) {
    mapEventHandler();
});

$('#ResNotice_perimeter_id').change(function() {
    mapEventHandler();
});

function mapEventHandler(type) {
    var perimeterID = $('#ResNotice_perimeter_id').val();
    $.event.trigger({ type: 'map:perimeter', perimeterID: perimeterID });
    $.event.trigger({ type: 'map:buffer', perimeterID: perimeterID });
    $.event.trigger({ type: 'map:threat', perimeterID: perimeterID });
}

function animateMapDiv(opacity, zIndex) {
    $('.map-fixed').animate({ opacity: opacity }, {
        step: function(now, fx) {
            $(this).css('-webkit-transition', 'opacity 500ms ease 150ms');
            $(this).css('   -moz-transition', 'opacity 500ms ease 150ms');
            $(this).css('        transition', 'opacity 500ms ease 150ms');
        },
        duration: 0,
        complete: function() {
            setTimeout(function() {
                if (zIndex === -10) {
                    $('.map-fixed').css({ zIndex: zIndex });
                }
            }, 500);
        }
    });
}

$('.map-show').click(function(e) {
    if ($('.map-fixed').css('opacity') == '1') {
        animateMapDiv(0,-10);
    }
    else {
        $('.map-fixed').css({ zIndex: 10 });
        animateMapDiv(1, 10);
    }
});

$('.close-icon').click(function(e) {
    e.preventDefault();
    animateMapDiv(0, -10);
})


// Recommended action JS validation

$(function () {

    var dispatchTypes = {
        1: 'Dispatched',
        2: 'Non-Dispatched',
        3: 'Demobilized'
    };

    function getKeyByValue(object, value) {
        for (var key in object) {
            if (object[key] === value) {
                return key;
            }
        }
    }

    function getAllowedStatuses(object, value) {
        var retArray = [];
        for (var key in object) {
            if (object[key].indexOf(value) > -1) {
                retArray.push(key);
            }
        }
        return retArray;
    }

    var attributes = {
        'Non-Dispatched': [
            'No Immediate Threat',
            'Enrollment/Response Recommended',
            'Potential Threat'
        ],
        'Dispatched': [
            'Program Responding',
            'Program Resources on Incident'
        ],
        'Demobilized': [
            'No Immediate Threat',
            'Enrollment/Response Recommended',
            'Potential Threat',
            'Program Resources Demobilized',
        ]
    };
    
    $(document).ready(function ()
    {
        $("input[name='ResNotice[recommended_action]']").prop('disabled', true);
        var status = $("input[name='ResNotice[wds_status]']:checked").val();
        // Edit Disable / Enable Radio button recommended_action according to wds_status
        if (status)
        {
            if (status == 1)
            {
                var actions = attributes['Dispatched'];
            }
            else if (status == 2)
            {
                var actions = attributes['Non-Dispatched'];
            }
            else if (status == 3)
            {
                var actions = attributes['Demobilized'];
            }
            $("input[name='ResNotice[recommended_action]']").prop('disabled', true);
            for (var j = 0; j < actions.length; j++)
            {
                $("input[name='ResNotice[recommended_action]'][value='" + actions[j] + "']").prop('disabled', false);
            }
        }

        $(document).on("change", "input[name='ResNotice[wds_status]']", function () {
            var currentStatus = $(this).val();
            var currentStatusString = dispatchTypes[currentStatus];
            var currentActionString = $("input[name='ResNotice[recommended_action]']:checked").val();
            var allowedActions = attributes[currentStatusString];
            if (allowedActions.indexOf(currentActionString) === -1)
            {
                $("input[name='ResNotice[recommended_action]'][value='" + allowedActions[0] + "']").prop('checked', true);
            }
            // Disable / Enable Radio button recommended_action according to wds_status
            if (allowedActions.indexOf(currentStatusString) == -1)
            {
                $("input[name='ResNotice[recommended_action]']").prop('disabled', true);
                for (var j = 0; j < allowedActions.length; j++)
                {
                    $("input[name='ResNotice[recommended_action]'][value='" + allowedActions[j] + "']").prop('disabled', false);
                }
            }
        });

        // On new action, if status is not allowed, change
        $(document).on("change", "input[name='ResNotice[recommended_action]']", function () {
            var currentStatus = $("input[name='ResNotice[wds_status]']:checked").val();
            var currentStatusString = dispatchTypes[currentStatus];
            var currentActionString = $(this).val();
            var allowedStatuses = getAllowedStatuses(attributes, currentActionString);
            if (allowedStatuses.indexOf(currentStatusString) === -1) {

                // If wds_status exists, then check, otherwise, remove radio check alltogether
                var radioChecked = false;
                for (var i = 0; i < allowedStatuses.length; i++) {
                    if ($("input[name='ResNotice[wds_status]'][value='" + getKeyByValue(dispatchTypes, allowedStatuses[i]) + "']").length) {
                        $("input[name='ResNotice[wds_status]'][value='" + getKeyByValue(dispatchTypes, allowedStatuses[i]) + "']").prop('checked', true);
                        radioChecked = true;
                    }
                }

                if (radioChecked === false) {
                    $("input[name='ResNotice[wds_status]']").removeAttr("checked");
                }
            }
        });
    });
});
function checkThreatPerimeter(url, perimeter_id, wdsStatus)
{
    var result;
     $.ajax({
        type: "POST",
        url: url,
        async: false,
        data: { perimeterID: perimeter_id },
    }).done(function (data) {
        if (data == "Success" && wdsStatus == 1) {
            result = true;
        }
        else
        {
            result = false;
        }
    });
     return result;
}