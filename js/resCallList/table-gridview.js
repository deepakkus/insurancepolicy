/**
 * resCallList/admin.js
 *
 * Defines the WDSResCallList namespace with associated methods
 * for the resCallList/admin view.
 */
(function (wds, $, undefined) {
    // Private members
    var gridResponseCallList = 'gridResponseCallList';

    /**
     * Initializes the view, setups up click events and the like.
     */
    wds.init = function () {
      $('#btnAssignCaller').click(function () {
            var selectedCallListIDs = $('#hiddenSelectedCallerIDs').val();

            var userID = $('#ddlCaller').val();
            var data = { "data": '{"data": {"assignedCallerUserID": ' + userID + ', "callListIDs": [' + selectedCallListIDs + ']}}' };

            // Set wait cursor.
            $("body").css("cursor", "progress");

            // Save the caller IDs.
            $.ajax({
                type: 'POST',
                url: 'index.php?r=resCallList/assignCallerToCalls',
                data: data
            }).done(function (result) {
                $('#selectCallerModal').modal('hide');
                if (result && result.error == 0) {
                    // Success. Refresh the grid.
                    $.fn.yiiGridView.update(gridResponseCallList);

                    // Restore the default cursor.
                    $("body").css("cursor", "default");
                } else {
                    // An error occurred.
                    alert(result.errorMessage);
                }
            }).fail(function (jqXHR, text) {

                console.log(jqXHR);
                console.log(text);

                $('#selectCallerModal').modal('hide');

                alert('Failed to assign the caller! Please try again.');

                // Restore cursor.
                $("body").css("cursor", "default");
            });

            return false;
        });
      $('.column-toggle').click(function () {
          
          $('.column-form').slideToggle();
          return false;
      });
        $('#btnAssignCaller1').click(function () {

            var selectedCallListIDs = $('#hiddenSelectedCallerIDs').val();
            var listid = selectedCallListIDs.split(',');

            var userID = $('#ddlCaller').val();
            var data = { "data": '{"data": {"assignedCallerUserID": ' + userID + ', "callListIDs": [' + selectedCallListIDs + ']}}' };

            // Set wait cursor.
            $("body").css("cursor", "progress");
            // Save the caller IDs.
            $.ajax({
                type: 'POST',
                url: 'index.php?r=resCallList/assignCallerToCalls',
                data: data
            }).done(function (result) {
                $('#selectCallerModal').modal('hide');
                if (result && result.error == 0) {
                    // Success. Refresh the grid.
                    //$.fn.yiiGridView.update(gridResponseCallList);

                    $("input[name='gridResponseCallList_c0[]']:checkbox").prop('checked', false);
                    for (var i = 0; i < listid.length; i++) {
                        $('td.assign_caller_' + listid[i]).html(result.callername);
                    }

                    // Restore the default cursor.
                    $("body").css("cursor", "default");
                } else {
                    // An error occurred.
                    alert(result.errorMessage);
                }
            }).fail(function (jqXHR, text) {

                console.log(jqXHR);
                console.log(text);

                $('#selectCallerModal').modal('hide');

                alert('Failed to assign the caller! Please try again.');

                // Restore cursor.
                $("body").css("cursor", "default");
            });

            return false;
        });
        //sorting
        $('body').on('click', '#s_client_name, #s_fire_name, #distance, #s_do_not_call, #assign_caller, #nocite_type, #priority, #threat, #response_status, #p_id, #address_line_1, #address_line_2, #p_city,#p_state, #p_zip, #s_member_num, #first_name, #last_name, #triggered', function () {
            var sortdata = $(this).text();
            //alert($(this).attr("data-sort"));
            var sorttype = $(this).attr("class");
            if (sorttype) {
                if (sorttype.includes("desc")) { sorttype = 'asc'; } else { sorttype = 'desc'; }
            }

            var data = {
                sortdata: sortdata,
                sorttype: sorttype,
                filterdata: $('#filterdata').val(),
                filteritem: $('#filteritem').val(),
                mclient: $('#mclient').val(),
                mfire: $('#mfire').val(),
                mcaller: $('#mcaller').val(),
                mdonotcall: $('#mdonotcall').val(),
                mnoticetype: $('#mnoticetype').val(),
                mthreat: $('#mthreat').val(),
                mtriggered: $('#mtriggered').val(),
                mresponsestatus: $('#mresponsestatus').val(),
                mpublish: $('#mpublish').val(),
                mcallstatus: $('#mcallstatus').val(),
                mfirstname: $('#mfirstname').val(),
                mlastname: $('#mlastname').val(),
                mpropertyid: $('#mpropertyid').val(),
                maddress1: $('#maddress1').val(),
                maddress2: $('#maddress2').val(),
                mpcity: $('#mpcity').val(),
                mpstate: $('#mpstate').val(),
                mpzip: $('#mpzip').val(),
                mnumber: $('#mnumber').val(),
                mdcomments: $('#mdcomments').val(),
                mgcomments: $('#mgcomments').val(),
                mrevacuated: $('#mrevacuated').val(),
                mrtpriority: $('#mrtpriority').val(),
                mrtdistance: $('#mrtdistance').val(),
            };
            $.ajax({
                url: 'index.php?r=resCallList/searchCalls',
                type: 'post',
                dataType: 'json',
                data: data,
                cache: false,
                async: true,
                success: function (data) {
                    //$('#publishCallsModal').modal('hide');
                    if (data && data.error == 0) {
                        //alert();
                        //alert(data.sql);
                        $('#res_call_list').html(data.data);

                    }
                    else {
                        alert(data.errorMessage);
                    }
                },
                error: function (XMLHttpRequest) {
                    console.log(XMLHttpRequest);
                    alert('Failed to set published status! Please try again.');
                }
            });
            // this is my ajaxcall for searching something!
            return false; // donot want to submit our form!
        });
        //previous page
        $('body').on('click', '#call_prev', function () {
            var page = $(this).attr('data-prev');
            var curr = $(".active");
            curr = curr.next();
            var li = $("ul li.active").prev()
            // $('#yw1 li').removeClass("active");
            if (page > 0) {
                $('#yw1 li').removeClass("active");
            }
            if (page == 0)
            {
                $('.previous').html('<a href="javascript:void(0)">←</a>');
            }
            li.addClass("active");
            
            var data = {
                sortdata: $('#sortdata').val(),
                sorttype: $('#sorttype').val(),
                filterdata: $('#filterdata').val(),
                filteritem: $('#filteritem').val(),
                mclient: $('#mclient').val(),
                mfire: $('#mfire').val(),
                mcaller: $('#mcaller').val(),
                mdonotcall: $('#mdonotcall').val(),
                mnoticetype: $('#mnoticetype').val(),
                mthreat: $('#mthreat').val(),
                mtriggered: $('#mtriggered').val(),
                mresponsestatus: $('#mresponsestatus').val(),
                mpublish: $('#mpublish').val(),
                mcallstatus: $('#mcallstatus').val(),
                mfirstname: $('#mfirstname').val(),
                mlastname: $('#mlastname').val(),
                mpropertyid: $('#mpropertyid').val(),
                maddress1: $('#maddress1').val(),
                maddress2: $('#maddress2').val(),
                mpcity: $('#mpcity').val(),
                mpstate: $('#mpstate').val(),
                mpzip: $('#mpzip').val(),
                mnumber: $('#mnumber').val(),
                mdcomments: $('#mdcomments').val(),
                mgcomments: $('#mgcomments').val(),
                mrevacuated: $('#mrevacuated').val(),
                mrtpriority: $('#mrtpriority').val(),
                mrtdistance: $('#mrtdistance').val(),
                page: page
            };
            $.ajax({
                url: 'index.php?r=resCallList/searchCalls',
                type: 'post',
                dataType: 'json',
                data: data,
                cache: false,
                async: true,
                success: function (data) {
                    //$('#publishCallsModal').modal('hide');
                    if (data && data.error == 0) {
                        //alert(data.sql);
                        //alert(data.toPage);
                        $('#res_call_list').html(data.data);
                        if (data.stratPage == 1 || page==0) {
                            $('.previous').addClass('previous disabled');
                            $('.previous').html('<a href="javascript:void(0)">←</a>');
                        }
                        if (data.toPage > 0) {
                            $('.summary').html('Displaying ' + data.stratPage + '-' + data.toPage + ' of ' + data.totalpages + ' results.');
                        }
                        if (page > 0) {
                            $('#call_prev').attr('data-prev',  parseInt(page) - 1 );
                        }
                        else {
                            $('.previous').html('<a href="javascript:void(0)">←</a>');
                        }
                    }
                    else {
                        alert(data.errorMessage);
                    }
                },
                error: function (XMLHttpRequest) {
                    console.log(XMLHttpRequest);
                    alert('Failed to set published status! Please try again.');
                }
            });
            // this is my ajaxcall for searching something!
            return false;
        });
        //next page
        $('body').on('click', '#call_next', function () {

            var page = $(this).attr('data-next');
            //var curr = $(".active");
            //curr = curr.next();
            
            var li = $("ul li.active").next()
            //alert(li.text())
            $('#yw1 li').removeClass("active");
            li.addClass("active");
            var data = {
                sortdata: $('#sortdata').val(),
                sorttype: $('#sorttype').val(),
                filterdata: $('#filterdata').val(),
                filteritem: $('#filteritem').val(),
                mclient: $('#mclient').val(),
                mfire: $('#mfire').val(),
                mcaller: $('#mcaller').val(),
                mdonotcall: $('#mdonotcall').val(),
                mnoticetype: $('#mnoticetype').val(),
                mthreat: $('#mthreat').val(),
                mtriggered: $('#mtriggered').val(),
                mresponsestatus: $('#mresponsestatus').val(),
                mpublish: $('#mpublish').val(),
                mcallstatus: $('#mcallstatus').val(),
                mfirstname: $('#mfirstname').val(),
                mlastname: $('#mlastname').val(),
                mpropertyid: $('#mpropertyid').val(),
                maddress1: $('#maddress1').val(),
                maddress2: $('#maddress2').val(),
                mpcity: $('#mpcity').val(),
                mpstate: $('#mpstate').val(),
                mpzip: $('#mpzip').val(),
                mnumber: $('#mnumber').val(),
                mdcomments: $('#mdcomments').val(),
                mgcomments: $('#mgcomments').val(),
                mrevacuated: $('#mrevacuated').val(),
                mrtpriority: $('#mrtpriority').val(),
                mrtdistance: $('#mrtdistance').val(),
                page: page
            };
            $.ajax({
                url: 'index.php?r=resCallList/searchCalls',
                type: 'post',
                dataType: 'json',
                data: data,
                cache: false,
                async: true,
                success: function (data) {
                    //$('#publishCallsModal').modal('hide');
                    if (data && data.error == 0) {
                        //alert(data.sql);
                        //alert(data.nextpage)
                        var tpages = 25;
                        $('#res_call_list').html(data.data);
                        
                        $('.summary').html('Displaying ' + data.stratPage + '-' + data.toPage + ' of ' + data.totalpages + ' results.');
                        var i = 1;
                        var jclass = '';
                        var disableclass = '';
                        var html = '<ul id="yw1" class="yiiPager yp1">';
                        var nextclass = '';
                        var j = 1;
                        var nextpagelink = '';
                        if (data.pagecounter > 10 && page >= 7) {
                            j = page - 5;
                        }
                        if (page == 1) {
                            disableclass = 'disabled';
                        }
                        var c = 1;
                        html += '<li class="previous ' + disableclass + '"><a href="javascript:void(0)" id="call_prev" data-prev="' + (parseInt(page) - 1) + '">←</a></li>';
                        for (i = j; i <= data.pagecounter; i++) {

                            if (page == i) {
                                jclass = 'active';
                            }
                            else {
                                jclass = '';
                            }
                            if (c <= 10) {
                                html += '<li class="' + jclass + '" id="r_call_list_pages"><a href="javascript:void(0)" id="call_list_pages_">' + i + '</a></li>';
                            }
                            c++;
                        }
                        nextpagelink = (parseInt(page) + 1);
                        if (page == data.pagecounter) {
                            disableclass = ' disabled';
                        }
                        
                        html += '<li class="next' + disableclass + '"><a href="javascript:void(0)" id="call_next" data-next="' + page + '">→</a></li>';
                        html += '</ul>';
                        $('.pagination').html(html);
                        
                        if (page == data.pagecounter) {
                            $('.next').html('<a href="javascript:void(0)">→</a>');
                        }
                        else {
                            $('#call_next').attr('data-next', data.nextpage);
                        }
                    }
                    else {
                        alert(data.errorMessage);
                    }
                },
                error: function (XMLHttpRequest) {
                    console.log(XMLHttpRequest);
                    alert('Failed to set published status! Please try again.');
                }
            });
            // this is my ajaxcall for searching something!
            return false;
        });

        //pagination
        //$('body').on('click', '*[id^=call_list_pages_]', function () {
        $('body').on('click', '.yp1 li', function () {
            var page = $(this).text(); 
            var prev = 0;
            var curr = $(this); 
            if (page == '←') {
                exit();
            }
            if (page == '→') {
                exit();
            }
            $('#yw1 li').removeClass("active");
            curr.addClass("active");

            $('#call_next').attr({ 'data-next': (parseInt(page) + 1) });
            $('.next').removeClass('disabled');

            $('.next').html('<a href="javascript:void(0)" id="call_next" data-next="' + (parseInt(page) + 1) + '">→</a>');
            if (parseInt(page) > 1) {
                $('.previous').removeClass('disabled');
                $('.previous').html('<a href="javascript:void(0)" id="call_prev" data-prev="' + (parseInt(page) - 1) + '">←</a>');

            }
            else {
                $('.previous').html('<a href="javascript:void(0)">←</a>');
            }
            var data = {
                sortdata: $('#sortdata').val(),
                sorttype: $('#sorttype').val(),
                filterdata: $('#filterdata').val(),
                filteritem: $('#filteritem').val(),
                mclient: $('#mclient').val(),
                mfire: $('#mfire').val(),
                mcaller: $('#mcaller').val(),
                mdonotcall: $('#mdonotcall').val(),
                mnoticetype: $('#mnoticetype').val(),
                mthreat: $('#mthreat').val(),
                mtriggered: $('#mtriggered').val(),
                mresponsestatus: $('#mresponsestatus').val(),
                mpublish: $('#mpublish').val(),
                mcallstatus: $('#mcallstatus').val(),
                mfirstname: $('#mfirstname').val(),
                mlastname: $('#mlastname').val(),
                mpropertyid: $('#mpropertyid').val(),
                maddress1: $('#maddress1').val(),
                maddress2: $('#maddress2').val(),
                mpcity: $('#mpcity').val(),
                mpstate: $('#mpstate').val(),
                mpzip: $('#mpzip').val(),
                mnumber: $('#mnumber').val(),
                mdcomments: $('#mdcomments').val(),
                mgcomments: $('#mgcomments').val(),
                mrevacuated: $('#mrevacuated').val(),
                mrtpriority: $('#mrtpriority').val(),
                mrtdistance: $('#mrtdistance').val(),
                page: page
            };
            $.ajax({
                url: 'index.php?r=resCallList/searchCalls',
                type: 'post',
                dataType: 'json',
                data: data,
                cache: false,
                async: true,
                success: function (data) {
                    //$('#publishCallsModal').modal('hide');
                    if (data && data.error == 0) {
                        //alert(data.sql);
                        var tpages = 25; //alert(data.toPage)
                        $('#res_call_list').html(data.data);

                        if (data.toPage == data.totalpages) {
                            $('.next').addClass('next disabled');
                            $('.next').html('<a href="javascript:void(0)">→</a>');
                        }
                        
                        $('.summary').html('Displaying ' + data.stratPage + '-' + data.toPage + ' of ' + data.totalpages + ' results.');

                        var i = 1;
                        var jclass = '';
                        var disableclass = '';
                        var html = '<ul id="yw1" class="yiiPager yp1">';
                        var nextclass = '';
                        var j = 1;
                        var nextpagelink = '';
                        if (data.pagecounter>10 && page >= 7){
                            j = page - 5;
                        }
                        if (page == 1) {
                            disableclass = 'disabled';
                        }
                        var c = 1;
                        html += '<li class="previous ' + disableclass + '"><a href="javascript:void(0)" id="call_prev" data-prev="' + (parseInt(page) - 1) + '">←</a></li>';
                        for (i = j; i <= data.pagecounter; i++) {

                            if (page == i) {
                                jclass = 'active';
                            }
                            else {
                                jclass = '';
                            }
                            if (c <= 10) {
                                html += '<li class="' + jclass + '" id="r_call_list_pages"><a href="javascript:void(0)" id="call_list_pages_">' + i + '</a></li>';
                            }
                            c++;
                            
                        }
                        if (page == data.pagecounter) {
                            disableclass = ' disabled';
                        }
                        nextpagelink = (parseInt(page) + 1);
                        html += '<li class="next' + disableclass + '"><a href="javascript:void(0)" id="call_next" data-next="' + (parseInt(page) + 1) + '">→</a></li>';
                        html += '</ul>';
                        $('.pagination').html(html);
                        if (page == data.pagecounter) {
                            $('.next').html('<a href="javascript:void(0)">→</a>');
                        }
                    }
                    else {
                        alert(data.errorMessage);
                    }
                },
                error: function (XMLHttpRequest) {
                    console.log(XMLHttpRequest);
                    alert('Failed to set published status! Please try again.');
                }
            });
            // this is my ajaxcall for searching something!
            return false;
        });

        //searching dropdown
        $('body').on('change', '#assigned_caller_user_name, #client_name, #fire_name, #do_not_call, #res_notice_wds_status, #res_triggered_threat, #res_triggered_response_status, #resCallList_triggered, #ResCallAttempt_publish, #ResCallAttempt_prop_res_status', function (e) {

            var assigned_caller_user_name = $('#assigned_caller_user_name option:selected').val();
            var client_name = $('#client_name option:selected').val();
            var fire_name = $('#fire_name option:selected').val();
            var do_not_call = $('#do_not_call option:selected').val();
            var res_notice_wds_status = $('#res_notice_wds_status option:selected').val();
            var res_triggered_threat = $('#res_triggered_threat option:selected').val();
            var res_triggered_response_status = $('#res_triggered_response_status option:selected').val();
            var resCallList_triggered = $('#resCallList_triggered option:selected').val();

            var ResCallAttempt_publish = $('#ResCallAttempt_publish option:selected').val();
            var ResCallAttempt_prop_res_status = $('#ResCallAttempt_prop_res_status option:selected').val();
            var filterdata = $(this).find('option:selected').val();
            var filteritem = $(this).attr('data-filter'); 
            var data = {
                filterdata: filterdata,
                filteritem: filteritem,
                mclient: $('#mclient').val(),
                mfire: $('#mfire').val(),
                mcaller: $('#mcaller').val(),
                mdonotcall: $('#mdonotcall').val(),
                mnoticetype: $('#mnoticetype').val(),
                mthreat: $('#mthreat').val(),
                mtriggered: $('#mtriggered').val(),
                mresponsestatus: $('#mresponsestatus').val(),
                mpublish: $('#mpublish').val(),
                mcallstatus: $('#mcallstatus').val(),
                mfirstname: $('#mfirstname').val(),
                mlastname: $('#mlastname').val(),
                mpropertyid: $('#mpropertyid').val(),
                maddress1: $('#maddress1').val(),
                maddress2: $('#maddress2').val(),
                mpcity: $('#mpcity').val(),
                mpstate: $('#mpstate').val(),
                mpzip: $('#mpzip').val(),
                mnumber: $('#mnumber').val(),
                mdcomments: $('#mdcomments').val(),
                mgcomments: $('#mgcomments').val(),
                mrevacuated: $('#mrevacuated').val(),
                mrtpriority: $('#mrtpriority').val(),
                mrtdistance: $('#mrtdistance').val(),
                searchcriteria: true,
            }
            $.ajax({
                url: 'index.php?r=resCallList/searchCalls',
                type: 'post',
                dataType: 'json',
                data: data,
                cache: false,
                async: true,
                success: function (data) {
                    //$('#publishCallsModal').modal('hide');
                    if (data && data.error == 0) {
                        //alert(data.sql);
                        //alert(data.f);
                        var disableclass = 'disabled';
                        $('#res_call_list').html(data.data);
                        if (data.toPage == data.totalpages) {
                            $('.next').addClass('next disabled');
                        }
                        if (data.totalpages > 0) {
                            $('.summary').html('Displaying ' + data.stratPage + '-' + data.toPage + ' of ' + data.totalpages + ' results.');
                        }
                        else {
                            $('.summary').html('');
                        }
                        if (data.stratPage == 1) {
                            disableclass = 'disabled';
                        }
                        if (data.pagination == false) {
                            $('.pagination').hide();
                        }
                        else {
                            var i = 1;
                            var jclass = '';
                            var html = '<ul id="yw1" class="yiiPager yp1">';
                            var nextclass = '';
                            if (data.stratPage > 1) {
                                //html += '<li class="previous disabled"><a href="/index.php?r=resCallList/admin&amp;ajax=gridResponseCallList">←</a></li>';
                                html += '<li class="previous ' + disableclass + '"><a href="javascript:void(0)" id="call_prev" data-prev="' + (parseInt(page) - 1) + '">←</a></li>';
                            }
                            else {
                                html += '<li class="previous ' + disableclass + '"><a href="javascript:void(0)" >←</a></li>';
                            }
                            for (i = 1; i <= data.pagecounter; i++) {
                                if (i == 1) {
                                    jclass = 'active';
                                }
                                else {
                                    jclass = '';
                                }
                                if (i <= 10) {
                                    html += '<li class="' + jclass + '" id="r_call_list_pages"><a href="javascript:void(0)" id="call_list_pages_">' + i + '</a></li>';
                                }

                            }
                            html += '<li class="next"><a href="javascript:void(0)" id="call_next" data-next="' + (data.pages + 1) + '">→</a></li>';
                            html += '</ul>';
                            $('.pagination').html(html);
                            $('.pagination').show();
                        }
                    }
                    else {
                        alert(data.errorMessage);
                    }
                },
                error: function (XMLHttpRequest) {
                    console.log(XMLHttpRequest);
                    alert('Failed to set published status!Please try again.');
                }
            });
            // this is my ajaxcall for searching something!
            return false;
        });
        //searching textfields
        $('body').on('keypress', '#firstname, #lastname, #propertyid, #property_address_line_1, #property_address_line_2, #res_triggered_distance, #property_city, #property_state, #property_zip, #member_num, #res_call_attempt_dashboard_comments, #res_call_attempt_general_comments, #res_call_attempt_evacuated, #res_triggered_priority', function (e) {
            if (e.which == 13) { // 13 is the keycode for enter
                var firstname = $('#firstname').val(); 
                var filter = [];
                filter['firstname'] = $('#firstname').val(); //alert(filter['firstname']);
                var lastname = $('#lastname').val();
                var propertyid = $('#propertyid').val();
                var property_address_line_1 = $('#property_address_line_1').val();
                var property_address_line_2 = $('#property_address_line_2').val();
                var res_triggered_distance = $('#res_triggered_distance').val();
                var property_city = $('#property_city').val();
                var property_state = $('#property_state').val();
                var property_zip = $('#property_zip').val();
                var member_num = $('#member_num').val();
                var res_call_attempt_dashboard_comments = $('#res_call_attempt_dashboard_comments').val();
                var res_call_attempt_general_comments = $('#res_call_attempt_general_comments').val();
                var res_call_attempt_evacuated = $('#res_call_attempt_evacuated').val();
                var res_triggered_priority = $('#res_triggered_priority').val();

                var data = {
                    firstname: firstname, lastname: lastname, propertyid: propertyid,
                    property_address_line_1: property_address_line_1,
                    property_address_line_2: property_address_line_2,
                    res_triggered_distance: res_triggered_distance,
                    property_city: property_city,
                    property_state: property_state,
                    property_zip: property_zip,
                    member_num: member_num,
                    res_call_attempt_dashboard_comments: res_call_attempt_dashboard_comments,
                    res_call_attempt_general_comments: res_call_attempt_general_comments,
                    res_call_attempt_evacuated: res_call_attempt_evacuated,
                    res_triggered_priority: res_triggered_priority,
                    mclient: $('#mclient').val(),
                    mfire: $('#mfire').val(),
                    mcaller: $('#mcaller').val(),
                    mdonotcall: $('#mdonotcall').val(),
                    mnoticetype: $('#mnoticetype').val(),
                    mthreat: $('#mthreat').val(),
                    mtriggered: $('#mtriggered').val(),
                    mresponsestatus: $('#mresponsestatus').val(),
                    mpublish: $('#mpublish').val(),
                    mcallstatus: $('#mcallstatus').val(),
                    filterdata: $('#filterdata').val(),
                    filteritem: $('#filteritem').val(),
                    searchcriteria: true,
                    filter: filter
                }
                $.ajax({
                    url: 'index.php?r=resCallList/searchCalls',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    cache: false,
                    async: true,
                    success: function (data) {
                        //$('#publishCallsModal').modal('hide');
                        if (data && data.error == 0) {
                            //alert(data.sql);
                           //alert(data.f);
                            $('#res_call_list').html(data.data);
                            // $('.summary').html('Displaying 1-' + data.pages + ' of ' + data.totalpages + ' results.');
                            if (data.totalpages > 0) {
                                $('.summary').html('Displaying 1-' + data.toPage + ' of ' + data.totalpages + ' results.');
                            }
                            else {
                                $('.summary').html('');
                            }
                            if (data.pagination == false) {
                                $('.pagination').hide();
                            }
                            else {
                                var i = 1;
                                var jclass = '';
                                var html = '<ul id="yw1" class="yiiPager">';

                                html += '<li class="previous disabled"><a href="javascript:void(0)">←</a></li>';
                                for (i = 1; i <= data.pagecounter; i++) {
                                    if (i == 1) {
                                        jclass = 'active';
                                    }
                                    else {
                                        jclass = '';
                                    }
                                    if (i <= 10) {
                                        html += '<li class="' + jclass + '" id="r_call_list_pages"><a href="javascript:void(0)" id="call_list_pages_">' + i + '</a></li>';
                                    }
                                }
                                html += '<li class="next"><a href="javascript:void(0)" id="call_next" data-next="' + (data.pages + 1) + '">→</a></li>';
                                html += '</ul>';
                                $('.pagination').html(html);
                                $('.pagination').show();
                            }
                        }
                        else {
                            alert(data.errorMessage);
                        }
                    },
                    error: function (XMLHttpRequest) {
                        console.log(XMLHttpRequest);
                        alert('Failed to set published status! Please try again.');
                    }
                });
                // this is my ajaxcall for searching something!
                return false; // donot want to submit our form!
            }
        });
        $('body').on('click', 'input[type="checkbox"]', function () {
            if ($(this).prop("checked") == true) {
                $(".bulk-actions-blocker").removeAttr("style");
                $("#btnLaunchAssignCallerDialog1").removeClass("disabled");
                $("#btnPublishCaller").removeClass("disabled");
            }
            else if ($(this).prop("checked") == false) {
                $(".bulk-actions-blocker").attr("style", "position:absolute;top:0;left:0;height:100%;width:100%;display:block;");
                $("#btnLaunchAssignCallerDialog1").addClass("disabled");
                $("#btnPublishCaller").addClass("disabled");
            }
        });
        $('body').on('click', '#gridResponseCallList_c0_all', function () {

            $(this).closest('table').find('td input:checkbox').prop('checked', this.checked);
            if ($(this).is(':checked')) {
                $(".bulk-actions-blocker").removeAttr("style");
                $("#btnLaunchAssignCallerDialog1").removeClass("disabled");
                $("#btnPublishCaller").removeClass("disabled");
            }
            else {
                $(".bulk-actions-blocker").attr("style", "position:absolute;top:0;left:0;height:100%;width:100%;display:block;");
                $("#btnLaunchAssignCallerDialog1").addClass("disabled");
                $("#btnPublishCaller").addClass("disabled");
            }
        });
        $('#btnPublishCall').click(function () {

            var selectedCallListIDs = $('#hiddenSelectedCallerPublishIDs').val();
            var publishedType = $('#ddlPublish').val();

            var data = { "data": '{"data": {"publishedCallType": ' + publishedType + ', "callListIDs": [' + selectedCallListIDs + ']}}' };

            $.ajax({
                url: 'index.php?r=resCallList/publishCalls',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function (data) {
                    $('#publishCallsModal').modal('hide');
                    if (data && data.error == 0) {
                        $.fn.yiiGridView.update(gridResponseCallList);
                    }
                    else {
                        alert(data.errorMessage);
                    }
                },
                error: function (XMLHttpRequest) {
                    console.log(XMLHttpRequest);
                    $('#publishCallsModal').modal('hide');
                    alert('Failed to set published status! Please try again.');
                }
            });

            return false;
        });

        $('body').on('click', '#btnPublishCaller', function () {
            var grid = $("#gridResponseCallList");
            if (!$("input[name='gridResponseCallList_c0\[\]']:checked", grid).length) {
                alert('No items are checked');
                return false;
            }
            var checked = $('input[name="gridResponseCallList_c0\[\]"]:checked');

            var fn = function (values) { WDSResCallList.publishCalls(values); }; if ($.isFunction(fn)) { fn(checked); }
            return false;

        });
        $('body').on('click', '#btnPublishCall1', function () {
            var selectedCallListIDs = $('#hiddenSelectedCallerPublishIDs').val();
            var publishedType = $('#ddlPublish').val();

            var data = { "data": '{"data": {"publishedCallType": ' + publishedType + ', "callListIDs": [' + selectedCallListIDs + ']}}' };

            $.ajax({
                url: 'index.php?r=resCallList/publishCalls',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function (data) {
                    $('#publishCallsModal').modal('hide');
                    if (data && data.error == 0) {
                        $("input[name='gridResponseCallList_c0[]']:checkbox").prop('checked', false);
                        // $.fn.yiiGridView.update(gridResponseCallList);
                    }
                    else {
                        alert(data.errorMessage);
                    }
                },
                error: function (XMLHttpRequest) {
                    console.log(XMLHttpRequest);
                    //$('#publishCallsModal').modal('hide');
                    alert('Failed to set published status! Please try again.');
                }
            });

            return false;
        });
        $('body').on('click', '#btnLaunchAssignCallerDialog1', function () {

            var grid = $("#gridResponseCallList");
            if (!$("input[name='gridResponseCallList_c0\[\]']:checked", grid).length) {
                alert('No items are checked');
                return false;
            }
            var checked = $('input[name="gridResponseCallList_c0\[\]"]:checked');
            var fn = function (values) { WDSResCallList.assignItemsToCaller(values); }; if ($.isFunction(fn)) { fn(checked); }
            return false;
        });
    };

    /**
     * Click handler for the assign caller button.
     * @param object[] callListIDs
     */
    wds.assignItemsToCaller = function (callListIDs) {
        var ids = [];

        for (var i = 0; i < callListIDs.length; i++) {
            ids.push($(callListIDs[i]).val());
        }

        var idsString = ids.join(',');

        $('#hiddenSelectedCallerIDs').val(idsString);

        // Launch the lightbox to select the caller.
        $('#selectCallerModal').modal();
    };

    /**
     * Click handler for the publish calls button.
     * @param object[] callListIDs
     */
    wds.publishCalls = function (callListIDs) {
        var ids = [];

        for (var i = 0; i < callListIDs.length; i++) {
            ids.push($(callListIDs[i]).val());
        }

        var idsString = ids.join(',');

        $('#hiddenSelectedCallerPublishIDs').val(idsString);

        $('#publishCallsModal').modal();
    };


}(window.WDSResCallList = window.WDSResCallList || {}, jQuery));

