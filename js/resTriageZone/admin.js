$(function() {

    // Populate Fires for a client
    $("select[name='ResTriageZone[clientID]']").change(function() {
        var actionUrl = this.getAttribute('data-url');
        var clientID = this.options[this.selectedIndex].value;
        if (!isNaN(parseInt(clientID))) {
            $.get(actionUrl, { clientID: clientID }, function(data) {
                var el = $("select[name='ResTriageZone[fireID]']");
                el.html(data);
                flashSelectMenu(el);
                $("select[name='ResTriageZone[notice_id]']").html('');
            }, 'html').error(function(jqXHR) {
                console.log(jqXHR);
            });
        }
    });

    // Populate Notices from a fire
    $("select[name='ResTriageZone[fireID]']").change(function() {
        var clientSelect = $("select[name='ResTriageZone[clientID]']")[0];
        var actionUrl = this.getAttribute('data-url');
        var clientID = clientSelect.options[clientSelect.selectedIndex].value;
        var fireID = this.options[this.selectedIndex].value;
        if (!isNaN(parseInt(fireID))) {
            $.get(actionUrl, { clientID: clientID, fireID: fireID }, function(data) {
                var el = $("select[name='ResTriageZone[notice_id]']");
                el.html(data);
                flashSelectMenu(el);
            }, 'html').error(function(jqXHR) {
                console.log(jqXHR);
            });
        }
    });

    // Take the notice and add it's perimeter and threat to the map
    $("select[name='ResTriageZone[notice_id]']").change(function() {
        var noticeID = this.options[this.selectedIndex].value;
        var actionUrl = this.getAttribute('data-url');
        if (!isNaN(parseInt(noticeID))) {
            $.get(actionUrl, { noticeID: noticeID }, function(data) {
                $.event.trigger({ type: 'map:perimeter', perimeterID: data.perimeterID });
                $.event.trigger({ type: 'map:buffer', perimeterID: data.perimeterID });
                $.event.trigger({ type: 'map:threat', perimeterID: data.perimeterID });
                $.event.trigger({ type: 'map:policyholders', perimeterID: data.perimeterID, clientID: data.clientID });
                setTimeout(function() { orderLayers(); }, 5000);
            }, 'json').error(function(jqXHR) {
                console.log(jqXHR);
            });
        }
    });

    function flashSelectMenu(el) {
        var n = 0;
        var selected = false;
        var interval = setInterval(function() {
            if (n < 4) {
                if (selected === false) {
                    el.css({ backgroundColor: 'rgba(0,0,2550,0.2)' });
                    selected = true;
                } else {
                    el.css({ backgroundColor: '' });
                    selected = false;
                }
                n++;
            } else {
                clearInterval(interval);
            }
        }, 250);
    }

});