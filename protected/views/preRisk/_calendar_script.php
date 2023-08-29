<script type="text/javascript">

    $(document).ready(function() {

        function onetimeEventListener(node, type, callback) {
            node.addEventListener(type, function (e) {
                e.target.removeEventListener(e.type, arguments.callee);
                return callback(e);
            });
        }

        function initCalendar() {

            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,basicWeek,basicDay'
                },
                editable: false,
                eventLimit: true,
                lazyFetching: true,
                weekMode: 'liquid',
                eventSources: [
                    <?php foreach($engines as $engine): ?>
                        <?php echo "'" . CController::createUrl('/preRisk/calendarEventFeed', array('engine_id'=>$engine)) . "',\n"; ?>
                    <?php endforeach; ?>
                ],
                dayClick: function(date, jsEvent, view) {
                    console.log(date.toDate());
                },
                eventRender: function(event, element) {

                    // Setting up Tooltips
                    var day = new Date(event.date).getDay();
                    var tooltipSide = (leftTooltipDates.indexOf(day) === -1) ? 'right' : 'left';

                    element[0].setAttribute('data-toggle', 'tooltip');
                    element[0].setAttribute('data-placement', tooltipSide);
                    element[0].setAttribute('data-original-title', event.title);
                    element[0].title = '';

                    // Setting up modals
                    element.attr('href', 'javascript:void(0);');
                    element.click(function () {
                        var content = '<table class="table">';
                        content += '<col width="30%"><col width="70%">'
                        content += '<tr class="warning"><th align="right">Name</th><td>' + event.fname + ', ' + event.lname + '</td></tr>';
                        content += '<tr><th align="right">Address</th><td>' + event.address + '</td></tr>';
                        content += '<tr><th align="right"></th><td>' + event.city + ', ' + event.state + ', ' + event.zip + '</td></tr>';
                        content += '<tr><th align="right">Start</th><td>' + event.start.toDate().toLocaleString() + '</td></tr>';
                        content += '<tr><th align="right">Contact Date</th><td>' + event.contactDate + '</td></tr>';
                        content += '<tr><th align="right">Assigned By</th><td>' + event.assignedBy + '</td></tr>';
                        content += '<tr><th align="right">Homeowner Present</th><td>' + event.homeownerPresent + '</td></tr>';
                        content += '<tr><th align="right">Engine</th><td>' + event.engine + '</td></tr>';
                        content += '<tr><th>&nbsp;</th><td>&nbsp;</td></tr>';
                        content += '<tr><th align=\"right\">Details</th><td>' + event.appointmentInformation + '</td></tr>';
                        content += '</table>';
                        content += '<div class="row-fluid center">';
                        content += '<div class="span4"><a class="btn btn-small" href="/index.php?r=preRisk/update&id=' + event.id + '&type=resource" target="_blank"><i class="icon-home"></i> Scheduling</a></div>';
                        content += '<div class="span4"><a class="btn btn-small" href="/index.php?r=preRisk/update&id=' + event.id + '&type=preRiskfollowUp" target="_blank"><i class="icon-comment"></i> Follow Up</a></div>';
                        content += '<div class="span4"><a class="btn btn-small" href="/index.php?r=preRisk/update&id=' + event.id + '&type=review" target="_blank"><i class="icon-folder-open"></i> Production</a></div>';
                        content += '</div>';

                        // Show modal
                        $('#calendarmodal .modal-title').html('<i>' + event.title + '</i>');
                        $('#calendarmodal .modal-body p').html(content);
                        $('#calendarmodal').modal();

                        // Dismiss on background click
                        var engineCheckboxes = document.getElementsByClassName('modal-backdrop')[0];
                        onetimeEventListener(engineCheckboxes, 'click', function(event) {
                            $('#calendarmodal').modal('hide');
                        });
                    });
                },
                eventClick: function(calEvent, jsEvent, view) {
                    null;
                },
                eventMouseover: function(event, jsEvent, view) {
                    this.style.opacity = '0.8';
                },
                eventMouseout: function(event, jsEvent, view) {
                    this.style.opacity = '1.0';
                }
            });
        }

        // Adds and removes from calendar based on engine
        function addRemoveEventSources() {
            var engineCheckboxes = document.getElementsByClassName('engine-checkbox');
            for (var i = 0; i < engineCheckboxes.length; i++) {
                if (engineCheckboxes[i].type === 'checkbox') {
                    engineCheckboxes[i].addEventListener('click', function(event) {
                        if (this.checked) {
                            console.log('adding');
                            $('#calendar').fullCalendar('addEventSource', '<?php echo CController::createUrl('/preRisk/calendarEventFeed') . '&engine_id='; ?>' + this.name);
                        }
                        else {
                            console.log('removing');
                            $('#calendar').fullCalendar('removeEventSource', '<?php echo CController::createUrl('/preRisk/calendarEventFeed') . '&engine_id='; ?>' + this.name);
                        }
                    });
                }
            }
        }

        // Keeping body from scrolling when modal is open
        function modalBackgroundScroll() {
            $("#calendarmodal").on("show", function () {
                document.body.style.overflow = 'hidden';
            }).on("hidden", function () {
                document.body.style.overflow = 'auto';
            });
        }

        document.createAttribute('data-toggle');
        document.createAttribute('data-placement');
        document.createAttribute('data-original-title');

        var leftTooltipDates = [3, 4, 5];

        initCalendar();
        addRemoveEventSources();
        modalBackgroundScroll();
    });

</script>