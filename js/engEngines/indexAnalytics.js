Date.prototype.monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
];

Date.prototype.getMonthName = function() {
    return this.monthNames[this.getMonth()];
};

/**
 * Charts using the LineLabels object will only show labels
 * every 10 units along the x-axis.
 */
Chart.types.Line.extend({
    name: 'LineLabels',
    initialize: function(data) {
        Chart.types.Line.prototype.initialize.apply(this, arguments);
        var xLabels = this.scale.xLabels
        xLabels.forEach(function(label, i) {
            if (i % 10 !== 0) {
                xLabels[i] = '';
            }
        })
    }
});

var AnalyticsEnginesChart = (function() {
    return {
        init: function(responseEngines, dedicatedEngines, onHoldEngines, totalEngines, onHold) {
            var dataLength = Object.keys(totalEngines).length;
            if (dataLength) {
                var scalableWidth = dataLength * 11.236;
                var responseEnginesData = [];
                var dedicatedEnginesData = [];
                var onHoldEnginesData = [];
                var totalEnginesData = [];
                var dayLabels = [];

                // Scale graph if data is too dense on screen
                if (scalableWidth > $('#content').width()) {
                    document.getElementById('engines-chart-parent').style.width = scalableWidth.toFixed() + 'px';
                }

                for (var timestamp in responseEngines) {
                    var date = new Date(timestamp * 1000);
                    dayLabels.push(date.getMonthName() + ' ' + date.getDate() + ', ' + date.getFullYear());
                    responseEnginesData.push(responseEngines[timestamp].count);
                    dedicatedEnginesData.push(dedicatedEngines[timestamp].count);
                    if (onHold === true) {
                        onHoldEnginesData.push(onHoldEngines[timestamp].count)
                    }
                    totalEnginesData.push(totalEngines[timestamp].count);
                }

                var data = {
                    labels: dayLabels,
                    datasets: [{
                        label: 'Total Engines',
                        fillColor: 'rgba(255,255,191,0.7)',
                        strokeColor: '#666666',
                        pointColor: 'rgba(255,255,191,0.7)',
                        pointStrokeColor: '#ccccccc',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: 'rgba(151,187,205,1)',
                        data: totalEnginesData
                    }, {
                        label: 'Dedicated Engines',
                        fillColor: 'rgba(50,136,189,0.7)',
                        strokeColor: '#444444',
                        pointColor: 'rgba(145,191,219,0.7)',
                        pointStrokeColor: '#fff',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: 'rgba(151,187,205,1)',
                        data: dedicatedEnginesData
                    }, {
                        label: 'Response Engines',
                        fillColor: 'rgba(215,25,28,0.7)',
                        strokeColor: '#222222',
                        pointColor: 'rgba(215,25,28,0.7)',
                        pointStrokeColor: '#fff',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: 'rgba(151,187,205,1)',
                        data: responseEnginesData
                    }]
                };

                if (onHold === true) {
                    data.datasets.splice(2, 0, {
                        label: 'On Hold Engines',
                        fillColor: 'rgba(153,213,148,0.7)',
                        strokeColor: '#444444',
                        pointColor: 'rgba(153,213,148,0.7)',
                        pointStrokeColor: '#fff',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: 'rgba(151,187,205,1)',
                        data: onHoldEnginesData
                    });
                }

                var ctx = document.getElementById('engines-chart').getContext('2d');
                ctx.canvas.width = ctx.canvas.parentNode.offsetWidth - 20;

                var chart = new Chart(ctx).LineLabels(data, {
                    legendTemplate: function(legend) {
                        var html = '<table>';
                        for (var i=0; i< legend.datasets.length; i++) {
                            html += '<tr><td style="height: 18px; width:30px; background-color:' + legend.datasets[i].fillColor + '; border:1px solid #CCCCCC;"></td>';
                            html += '<td>' + legend.datasets[i].label + '</td></tr>';
                        }
                        html += '</table>';
                        return html;
                    },
                    multiTooltipTemplate: function(tooltip) {
                        return tooltip.value + ' ' + tooltip.datasetLabel.substr(0, tooltip.datasetLabel.indexOf(' Engines'));;
                    },
                    bezierCurve: true,
                    pointDot: false,
                    pointHitDetectionRadius: 1
                });

                // Set date selector on engine day stats for on click
                ctx.canvas.addEventListener('click', function(e) {
                    var activePoints = chart.getPointsAtEvent(e);
                    var dateSelect = $("select[name='EngineReportDayForm[date]']");
                    var engineReportDayForm = $('#engine-report-day-form');
                    if (activePoints.length) {
                        var point = activePoints[0];
                        var date = new Date(point.label);
                        var formattedDate = date.toISOString().split('T')[0];
                        dateSelect.val(formattedDate);
                        $('html, body').animate({ 
                            scrollTop: engineReportDayForm.offset().top 
                        }, {
                            duration: 500
                        }).promise().done(function() {
                            dateSelect.trigger('change');
                        });
                    }
                });

                document.getElementById('engine-chart-legend').innerHTML = chart.generateLegend();
            }
        }
    };
})();

var AnalyticPoliciesChart = (function() {
    return {
        init: function(policiesTriggered) {
            var dataLength = Object.keys(policiesTriggered).length;
            if (dataLength) {

                var scalableWidth = dataLength * 11.236;
                var policiesTriggeredData = [];
                var dayLabels = [];

                // Scale graph if data is too dense on screen
                if (scalableWidth > $('#content').width()) {
                    document.getElementById('policies-chart-parent').style.width = scalableWidth.toFixed() + 'px';
                }

                for (var timestamp in policiesTriggered) {
                    var date = new Date(timestamp * 1000);
                    dayLabels.push(date.getMonthName() + ' ' + date.getDate() + ', ' + date.getFullYear());
                    policiesTriggeredData.push(policiesTriggered[timestamp].count);
                }

                var data = {
                    labels: dayLabels,
                    datasets: [{
                        label: 'Total Engines',
                        fillColor: 'rgba(179,227,250,0.8)',
                        strokeColor: '#666666',
                        pointColor: 'rgba(179,227,250,0.8)',
                        pointStrokeColor: '#ccccccc',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: 'rgba(151,187,205,1)',
                        data: policiesTriggeredData
                    }]
                };

                var ctx = document.getElementById('policies-chart').getContext('2d');
                ctx.canvas.width = ctx.canvas.parentNode.offsetWidth - 20;

                new Chart(ctx).LineLabels(data, {
                    bezierCurve: true,
                    pointDot: false,
                    pointHitDetectionRadius: 1
                });
            }
        }
    }
})();

var AnalyticDispatchedFiresChart = (function () {
    return {
        init: function(dispatchedFires) {
            var dataLength = Object.keys(dispatchedFires).length;
            if (dataLength) {

                var scalableWidth = dataLength * 11.236;
                var dispatchedFiresData = [];
                var dayLabels = [];

                // Scale graph if data is too dense on screen
                if (scalableWidth > $('#content').width()) {
                    document.getElementById('fires-chart-parent').style.width = scalableWidth.toFixed() + 'px';
                }

                for (var timestamp in dispatchedFires) {
                    var date = new Date(timestamp * 1000);
                    dayLabels.push(date.getMonthName() + ' ' + date.getDate() + ', ' + date.getFullYear());
                    dispatchedFiresData.push(dispatchedFires[timestamp].count);
                }

                var data = {
                    labels: dayLabels,
                    datasets: [{
                        label: 'Total Engines',
                        fillColor: 'rgba(179,227,250,0.8)',
                        strokeColor: '#666666',
                        pointColor: 'rgba(179,227,250,0.8)',
                        pointStrokeColor: '#ccccccc',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: 'rgba(151,187,205,1)',
                        data: dispatchedFiresData
                    }]
                };

                var ctx = document.getElementById('fires-chart').getContext('2d');
                ctx.canvas.width = ctx.canvas.parentNode.offsetWidth - 20;

                new Chart(ctx).LineLabels(data, {
                    bezierCurve: true,
                    pointDot: false,
                    pointHitDetectionRadius: 1
                });
            }
        }
    }
})();



$(function () {

    var engineReportDayForm = $('#engine-report-day-form');
    var dateSelect = $("select[name='EngineReportDayForm[date]']");
    var firesSelect = $("select[name='EngineReportDayForm[fires][]']");
    var clientidsInput = $("input[name='EngineReportDayForm[clientids]']");

    // Fill out fires multi-select
    $("select[name='EngineReportDayForm[date]']").change(function(e) {
        var selectedDate = e.target.options[e.target.selectedIndex].value;
        var clientIDs = clientidsInput.val();
        $.get('/index.php?r=engEngines/indexAnalyticsDayGetFires', { date: selectedDate, clientids: clientIDs }, function(data) {
            firesSelect.html(data);
            if (data === '') {
                $('#no-fires-found').fadeIn('fast').delay(1000).fadeOut('fast');
            }
        }, 'html').error(function(jqXHR) {
            console.log(jqXHR);
        });
    });
    
    // Remove selected from multiselects on detailed stats by day form
    $('#clear-selects').click(function(e) {
        $("select[name='EngineReportDayForm[fires][]'] option:selected").removeAttr('selected');
        $("select[name='EngineReportDayForm[alliance][]'] option:selected").removeAttr('selected');
        $("select[name='EngineReportDayForm[assignment][]'] option:selected").removeAttr('selected');
    });

    // Whenever a day link is selected, animate the user to the detailed stats by day form
    // and select the proper date.
    $('.date-select').click(function(e) {
        e.preventDefault();
        var timestamp = $(this).data('date');
        var date = new Date(parseInt(timestamp) * 1000);
        var formattedDate = date.toISOString().split('T')[0];
        dateSelect.val(formattedDate);
        $('html, body').animate({ 
            scrollTop: engineReportDayForm.offset().top 
        }, {
            duration: 500
        }).promise().done(function() {
            dateSelect.trigger('change');
        });
    });
    $('#search_report').click(function ()
    {
        //Validation for start and end date
        var beginDate = $("#EngineReportForm_startdate").val();
        var endDate = $("#EngineReportForm_enddate").val();
        if (beginDate > endDate)
        {
            alert("Please Enter Valid Date!!");
            return false;
        }
    });
});

