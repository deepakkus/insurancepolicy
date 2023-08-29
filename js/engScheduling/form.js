var EngineSchedule = (function() {

    return {
        init: function() {

            // Populate Fires when 'Response' is selected from the 'Assignment' dropdown
            $('#EngScheduling_assignment').change(function() {
                var selected = $("#EngScheduling_assignment option:selected").val();
                if (selected === 'Response') {
                    // Populate Fires List and Unlock the Dropdown
                    var schedule_id = $('#schedule_id').val();
                    $.post('/index.php?r=engScheduling/formGetAvailibleFires&schedule_id=' + schedule_id, function(data) {
                        $("#EngScheduling_fire_id").html(data);
                    });
                    $('#EngScheduling_fire_id').prop('disabled', false);
                    var fireOfficer = $("#EngScheduling_fire_officer_id option:selected").val();
                    if (fireOfficer == '' || fireOfficer == undefined) {
                        alert("A fire officer is required for Response Assignments");
                    }
                } else {
                    //$("#EngScheduling_fire_officer_id").val('').change();
                    $('#EngScheduling_fire_id').find('option').remove().end().append('<option value=""></option>').val('').change();
                }
                return false;
            });
            // Fill out fire information when a fire is selected from the 'Fire' dropdown
            $('#EngScheduling_fire_id').change(function() {
                var fireID = $("#EngScheduling_fire_id option:selected").val();
                // Populate Fire Information based off Fire selected
                $.post('/index.php?r=engScheduling/formPopulateFireInformation', { fireid: fireID }, function(data) {
                    if (!data.error) {
                        $('#EngScheduling_city').val(data.data.city);
                        $('#EngScheduling_state').val(data.data.state);
                        $('#EngScheduling_lat').val(data.data.lat);
                        $('#EngScheduling_lon').val(data.data.lon);
                    }
                }, 'json');
            });

            // Geocode city/state
            $('#geocode-address-link').click(function(e) {
                e.preventDefault();
                var city = $('#EngScheduling_city').val();
                var state = $('#EngScheduling_state').val();
                var address = city + ', ' + state;
                $.get('/index.php?r=engScheduling/geocode', { address: address }, function(data) {
                    console.log(data);
                    if (!data.error) {
                        $('#EngScheduling_lat').val(data.geometry.lat.toFixed(6));
                        $('#EngScheduling_lon').val(data.geometry.lon.toFixed(6));
                    } else {
                        alert('Geocode was unsuccessful with the following error: ' + data.error_message);
                    }
                }, 'json');
            });

            $('#render-ro-modal').click(function(e) {
                e.preventDefault();
                $('#ro-modal').modal('show');
            });
        }
    };

})();

var EngineScheduleRoModal = (function() {
    return {
        init: function() {
            // Populate model with past ROs to choose from
            $('select[name=clients]').change(function(e) {
                var clientID = e.target.options[e.target.selectedIndex].value;
                if (clientID) {
                    $.post('/index.php?r=engResourceOrder/resourceOrderModel&clientid=' + clientID, function(data) {
                        $('#ro-modal-container').html(data);
                        $('.ro-modal-link').click(function(e) {
                            var ro = e.target.parentNode.parentNode.firstChild.innerText;
                            $('#ro-modal').modal('hide');
                            $('#EngScheduling_resource_order_id').val(ro);
                        });
                    });
                }
            });

            // Populate model with create new RO form
            $('#create-ro').click(function(e) {
                $.post(this.href, function(data) {
                    $('#ro-modal-container').html(data);
                    $('#EngResourceOrder_form_ordered_date')
                        .datepicker({'autoclose':true,'todayHighlight':true,'language':'en'})
                        .prop('required',true);
                    $('#EngResourceOrder_form_ordered_time')
                        .timepicker({'showMeridian':false,'defaultTime':false})
                        .prop('required',true);
                    $('a[href$="/index.php?r=engResourceOrder/admin"]').hide();
                    // Catch form submit action
                    $('#eng-resource-order-form').submit(function(e) {
                        // Asynch submit form
                        $.post(this.action, $(this).serialize(), function(result) {
                            try {
                                // If valid response, load the new RO into the form
                                var result = JSON.parse(result);
                                if (result.hasOwnProperty('error') && result.error == 0) {
                                    var message = '<div class="flash-wrapper">' + 
                                        '<div class="flash">' + 
                                            '<ul class="flashes">' + 
                                                '<li><div class="flash-success">' + result.message + '</div></li>' +
                                            '</ul>' +
                                        '</div>' +
                                    '</div>';
                                    $('#header').append($.parseHTML(message));
                                    $('.flash').delay(5000).fadeOut('slow');
                                    $('#ro-modal').modal('hide');
                                    $('#ro-modal-container').html('');
                                    // Populate new RO into form
                                    $.post('/index.php?r=engScheduling/formGetResourceOrders', function(data) {
                                        $('#EngScheduling_resource_order_id').html(data).prop('selectedIndex', 1);
                                    });
                                }
                            } catch(e) {
                                console.log('Something went wrong!');
                                console.warn(e);
                            }
                        }).error(function(error) {
                            console.log(error);
                        });
                        return false;
                    });
                });
                return false;
            });
        }
    };
})();

var EngineClient = (function() {

   function init() {
        var engineclientmodelID = getQueryStringVariables()['engineclientmodelID'];
        if (engineclientmodelID > 0) {
            // Highlight the selected row in the grid.
            $('#gridClients table tbody tr td:first-child').filter(function() {
                return $(this).text() === engineclientmodelID;
            }).parent().addClass('selected');
            $('#lnkAddNewClient').show();
        } else {
            $('#lnkAddNewClient').hide();
        }

        $('#lnkAddNewClient').click(function() {
            // Redirect to this page without a callAttemptID (which will allow us to create a new call attempt).
            var engSchedulingID = getQueryStringVariables()['id'];
            location.href = 'index.php?r=engScheduling/update&id=' + engSchedulingID + '&engineclientmodelID=';
        });
    }

    /**
     * Grid row selection event handler
     */
    function onGridRowSelected(id) {
        var engSchedulingID = getQueryStringVariables()['id'];
        var gridID = $.fn.yiiGridView.getSelection(id);
        location.href = 'index.php?r=engScheduling/update&id=' + engSchedulingID + '&engineclientmodelID=' + gridID;
    }

    return {
        init: init,
        onGridRowSelected: onGridRowSelected
    };

})();

var EngineEmployee = (function() {

    function init() {
        var employeeID = getQueryStringVariables()['employeeID'];
        if (employeeID > 0) {
            // Highlight the selected row in the grid.
            $('#gridEmployees table tbody tr td:first-child').filter(function() {
                return $(this).text() === employeeID;
            }).parent().addClass('selected');
            $('#lnkAddNewEmployee').show();
        } else {
            $('#lnkAddNewEmployee').hide();
        }

        $('#lnkAddNewEmployee').click(function() {
            // Redirect to this page without a callAttemptID (which will allow us to create a new call attempt).
            var engSchedulingID = getQueryStringVariables()['id'];
            location.href = 'index.php?r=engScheduling/update&id=' + engSchedulingID + '&employeeID=';
        });
    }

    /**
     * Grid row selection event handler
     */
    function onGridRowSelected(id) {
        var engSchedulingID = getQueryStringVariables()['id'];
        var gridID = $.fn.yiiGridView.getSelection(id);
        location.href = 'index.php?r=engScheduling/update&id=' + engSchedulingID + '&employeeID=' + gridID;
    }

    return {
        init: init,
        onGridRowSelected: onGridRowSelected
    };

})();

/**
 * Gets query string variables from the URL.
 */
function getQueryStringVariables() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

/**
 * Keep engine location info fixed to top of screen when scrolling down..
 */
function moveScroller() {
    var move = function() {
        var st = $(window).scrollTop();
        var ot = $(".location-info-anchor").offset().top;
        var s = $(".location-info");
        var w = s.width();
        if (st > ot) {
            s.css({
                position: "fixed",
                top: "0px",
                width: w
            });
        } else {
            if (st <= ot) {
                s.css({
                    position: "relative",
                    top: "",
                    width: "auto"
                });
            }
        }
    };
    $(window).scroll(move);
    move();
}

(function($) {
    $.fn.scrollTo = function(callback) {
        $('body, html').animate({
            scrollTop: $(this).offset().top + 'px'
        }, {
            duration: 200,
            complete: function() {
                setTimeout(function() { typeof callback === 'function' && callback(); }, 200);
            }
        });
        return this;
    }
})(jQuery);

EngineSchedule.init();
EngineScheduleRoModal.init();
EngineClient.init();
EngineEmployee.init();

var supportsMatchMedia = typeof window.matchMedia !== 'undefined' ? true : false;
var mobile = supportsMatchMedia ? window.matchMedia( '(min-width: 768px)' ) : null;

// Don't use "sticky" location info when elements are stacked
if (mobile && mobile.matches) {
    moveScroller();
}
