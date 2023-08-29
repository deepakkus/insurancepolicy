// Keep weather widget fixed to top of screen when scrolling down.
function moveScroller() {
    var move = function() {
        var st = $(window).scrollTop();
        var ot = $("#weather-details-anchor").offset().top;
        var s = $("#weather-details");
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

$(function() {
    if (document.getElementById("weather-details-anchor")) {
        moveScroller();
    }
});

$(document).ready(function() {
    // Event listener to rotate wind arrow when clicked
    $('table.compass td input').click(function(event) {
        var degrees = Number(event.target.getAttribute('data-angle'));
        var label = event.target.value;
        rotateByDegrees(degrees,label);
    });
});


function rotateByDegrees(angle,label) {
    $('.needle').animate({ borderSpacing: angle }, {
        step: function(now, fx) {
            $(this).css('-webkit-transform', 'rotate(' + now + 'deg)');
            $(this).css('   -moz-transform', 'rotate(' + now + 'deg)');
            $(this).css('        transform', 'rotate(' + now + 'deg)');
        },
        duration: 600,
        complete: function() {
            $('#ResFireObs_Fx_Wind_Dir').val(label);
        }
    });
}


function resNoticePopulateThreats(fire_id, action_url) {
    $.ajax({
        url: action_url,
        type: 'POST',
        data: {
            fire_id: fire_id
        },
        success: function (data) {
            $("#ResNotice_threat_id").html(data);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log(XMLHttpRequest);
        }
    });
    return false;
}

var Weather = (function() {

    var azimuthals = [0, 22, 45, 67, 90, 112, 135, 157, 180, 202, 225, 247, 270, 292, 315, 337, 360];
    var directionDict = { 0: 'N', 22: 'NNE', 45: 'NE', 67: 'ENE', 90: 'E', 112: 'ESE', 135: 'SE', 157: 'SSE', 180: 'S', 202: 'SSW', 225: 'SW', 247: 'WSW', 270: 'W', 292: 'WNW', 315: 'NW', 337: 'NNW', 360: 'N' };

    function getNoaaWeather(lat, lon, updateFields) {
        $.ajax({
            dataType: 'json',
            url: '/index.php?r=resFireObs/getNoaaWeather',
            type: 'post',
            data: {
                lat: lat,
                lon: lon
            },
            success: function (parsed_json) {
                // Remove progress bar
                $('.weather-progress').remove();

                // Create weather data array and pass to function that will populate table
                var weatherData = {
                    'lat': parsed_json.currentobservation.latitude,
                    'lon': parsed_json.currentobservation.longitude,
                    'startPeriodName': parsed_json.time.startPeriodName,
                    'shortWeather': parsed_json.data.text,
                    'weatherIconUrl': parsed_json.data.iconLink,
                    'hazardUrl': parsed_json.data.hazardUrl,
                    'hazard': parsed_json.data.hazard,
                    'currentobs': parsed_json.currentobservation
                };

                // Render Weather Widget
                forecastWidget(weatherData);
                hazardsWidget(weatherData);
                currentObsWidget(weatherData);
                if (updateFields) {
                    populateFields(weatherData);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                console.log(errorThrown);

                // Remove progress bar
                $('.weather-progress').remove();

                // Render No Weather View
                $('#weather-details').hide();
                $('.cell-right').append($.parseHTML(document.querySelector("#weather-error").innerHTML)[1]);
            }
        });
    }

    function forecastWidget(weatherData) {
        var html = '';
        html += '<h2>5 day Forecast</h2>';
        html += '<table class="table table-condensed table-hover"  style="width:75%">';
        if (($.isArray(weatherData.startPeriodName) && weatherData.startPeriodName.length >= 4) &&
            ($.isArray(weatherData.weatherIconUrl) && weatherData.weatherIconUrl.length >= 4) &&
            ($.isArray(weatherData.shortWeather) && weatherData.shortWeather.length >= 4)) {
            for (var i = 0; i < 4; i++) {
                html += '<tr>' +
                        '<th colspan="100%" align="left">' + weatherData.startPeriodName[i] + '</th>' +
                    '</tr>' +
                    '<tr style="padding:10px 0;">' +
                        '<td><img style ="margin: 10px 0;" src="' + weatherData.weatherIconUrl[i] + '" alt = ""/></td>' +
                        '<td><p style="margin: 10px 0;">' + weatherData.shortWeather[i] + '</p></td>' +
                    '</tr>';
            }
        }
        html += '</table>';
        html += '<br />';
        html += '<div class ="row">';
        html += '<p>Read the full forecast<a href = "http://forecast.weather.gov/MapClick.php?lat=' + weatherData.lat + '&lon=' + weatherData.lon + '" target ="_blank"> here</a>';
        html += '</div>';

        $('#yw0_tab_1').append(html);
    }

    function hazardsWidget(weatherData) {
        var html = '';
        html += '<h2>Hazard Outlooks</h2>';
        if (($.isArray(weatherData.hazard) && weatherData.hazard.length > 0) &&
            ($.isArray(weatherData.hazardUrl) && weatherData.hazardUrl.length > 0)) {
            for (var i = 0; i < weatherData.hazard.length; i++) {
                html += weatherData.hazard[i] + ' - <a href="' + weatherData.hazardUrl[i] + '" target="_blank"> Read discussion here.</a></p>';
            }
        }
        else {
            html += '<p>No hazards</p>';
        }
        $('#yw0_tab_2').append(html);
    }

    function currentObsWidget(weatherData) {
        var html = '<table class="table table-condensed table-hover" style="width:75%">' +
            '<thead>' +
                '<tr>' +
                    '<th style="width:25%">Type</th>' +
                    '<th style="width:75%">Value</th>' +
                '</tr>' +
            '</thead>' +
        '<tbody>';

        var keys = ['name', 'elev', 'latitude', 'longitude', 'Temp', 'Dewp', 'Relh', 'Winds', 'Windd', 'Gust', 'Weather', 'Visibility', 'WindChill'];
        var attrs = {};

        Object.keys(weatherData.currentobs).map(function(value, index) {
            if (keys.indexOf(value)) {
                attrs[value] = weatherData.currentobs[value];
            }
        });

        for (attr in attrs) {
            html += '<tr><td><strong>' + attr + '</strong></td>';
            html += '<td>' + attrs[attr] + '</td>';
            html += '</tr>';
        }

        html += '</tbody></table>';
        $('#yw0_tab_3').append(html);
    }

    function closest(array, num) {
        var num = Number(num);
        var i = 0;
        var minDiff = 1000;
        var retval;
        for (i in array) {
            var m = Math.abs(num - array[i]);
            if (m < minDiff) {
                minDiff = m;
                retval = array[i];
            }
        }
        return retval;
    }

    function populateFields(weatherData) {
        $('#ResFireObs_Temp').val(weatherData.currentobs.Temp);
        $('#ResFireObs_Wind_Speed').val(weatherData.currentobs.Winds);
        $('#ResFireObs_Gust').val(weatherData.currentobs.Gust);
        $('#ResFireObs_Wind_Dir').val(directionDict[closest(azimuthals, weatherData.currentobs.Windd)]);
        $('#ResFireObs_Humidity').val(weatherData.currentobs.Relh);

        if (weatherData.hazard.indexOf('Red Flag Warning') !== -1) {
            $('#ResFireObs_Red_Flags').prop('checked', true)
        }
    }

    return {
        getNoaaWeather: getNoaaWeather
    };

})();