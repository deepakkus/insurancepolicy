/**
 * resPropertyStatus/update.js
 *
 * Defines the WDSResPropertyStatusUpdate namespace with associated methods
 * for the resPropertyStatus/update view.
 */
(function (wds, $, undefined) {
    /**
     * Private members
     */
    var PROPERTY_STATUS_DATA_KEY = 'property_status';
    var PROPERTY_STATUS_ID_KEY = 'selected_property_status_id';
    var _propertyStatus = null;

    /**
     * Initializes the view, setups up click events, etc.
     */
    wds.init = function () {
        bindHandlers();
        toggleControlsByConnection();

        var isiPad = navigator.userAgent.match(/iPad/i) != null;

        if (isiPad) {
            // Fixes visual weirdness with the date visited textbox.
            $('#dateVisited').attr('readonly', true);
        }

        var id = localStorage.getItem(PROPERTY_STATUS_ID_KEY);
        _propertyStatus = getPropertyStatusFromLocalStorage(id);

        if (_propertyStatus) {
            loadControls();
        } else {
            alert("There was a problem loading the selected property status record from local storage!");
        }

        var memberLabelText = $('#lblClient').text() == 'USAA' ? 'Member' : 'Policyholder';
        $('#lblMember').text(memberLabelText + " Name:");
    };

    /** 
     * Loads the HTML controls using the selected property status data. 
     * Only called when the page is in offline mode.
     */
    function loadControls() {
        $('.breadcrumbs span').first().text(_propertyStatus['id']);

        var address = _propertyStatus['property_address_line_1'] + ' ';
        if (_propertyStatus['property_address_line_2'])
            address += _propertyStatus['property_address_line_2'];
        address += '<br/>' + _propertyStatus['property_city'] + ', ' + _propertyStatus['property_state'] + ' ' + _propertyStatus['property_zip'];

        $('#lblMemberName').text(_propertyStatus['member_first_name'] + ' ' + _propertyStatus['member_last_name']);
        $('#lblAddress').html(address);
        $('#lblThreat').text(_propertyStatus['threat']);
        $('#lblPriority').text(_propertyStatus['priority']);
        $('#lblDistance').text(parseFloat(_propertyStatus['distance']).toFixed(2));
        $('#lblClient').text(_propertyStatus['client_name']);
        $('#lblResponseStatus').text(_propertyStatus['property_response_status']);

        $('#ResTriggeredWithPropertyStatus_status').val(_propertyStatus['status']);
        $('#dateVisited').val(_propertyStatus['date_visited']);
        $('#ResTriggeredWithPropertyStatus_division').val(_propertyStatus['division']);
        $('#ResTriggeredWithPropertyStatus_engine_id').val(_propertyStatus['engine_id']);
        
        var hasPhoto = _propertyStatus['has_photo'] === '1' || _propertyStatus['has_photo'] === 1 ? true : false;
        $('#ResTriggeredWithPropertyStatus_has_photo').prop('checked', hasPhoto);

        if (_propertyStatus['actions'])
            $('#ResTriggeredWithPropertyStatus_actions').text(_propertyStatus['actions']);

        if (_propertyStatus['other_issues'])
            $('#ResTriggeredWithPropertyStatus_other_issues').text(_propertyStatus['other_issues']);
    }

    /**
     * Binds event handlers.
     */
    function bindHandlers() {
        $('#btnSubmit').click(function () {
            saveChangesOffline();
            return false;
        });

        $('#btnCancel').click(function () {
            location.href = 'index.php?r=resPropertyStatus/admin';
        });

        window.addEventListener("offline", function (e) {
            toggleControlsByConnection();
        });

        window.addEventListener("online", function (e) {
            toggleControlsByConnection();
        });
    }

    /**
     * Toggles the header "offline mode" label based on internet connectivity.
     */
    function toggleControlsByConnection() {
        if (navigator.onLine) {
            $('#offlineText').text('').hide();
            $('#mainMbMenuCover').hide();

            // Enable "home" breadcrumb link
            if ($(".breadcrumbs span:contains('Home')").length)
                $(".breadcrumbs span:contains('Home')")[0].outerHTML = '<a href="/index.php">Home</a>';
        } else {
            $('#offlineText').text('(Offline Mode)').show();
            $('#mainMbMenuCover').show();

            // Disable "home" breadcrumb link
            $(".breadcrumbs a:contains('Home')")[0].outerHTML = '<span>Home</span>';
        }
    }

    /**
     * Saves changes to the current property status record to local storage.
     */
    function saveChangesOffline() {
        // Load the data from HTML into the JSON object.
        _propertyStatus.status = $('#ResTriggeredWithPropertyStatus_status').val();
        _propertyStatus.date_visited = $('#dateVisited').val();
        _propertyStatus.division = $('#ResTriggeredWithPropertyStatus_division').val();
        _propertyStatus.engine_id = $('#ResTriggeredWithPropertyStatus_engine_id').val();
        _propertyStatus.engine_name = $('#ResTriggeredWithPropertyStatus_engine_id option:selected').text();
        _propertyStatus.has_photo = $('#ResTriggeredWithPropertyStatus_has_photo').prop('checked') ? 1 : 0;
        _propertyStatus.actions = $('#ResTriggeredWithPropertyStatus_actions').val();
        _propertyStatus.other_issues = $('#ResTriggeredWithPropertyStatus_other_issues').val();

        // Save the data into local storage.
        if (!updatePropertyStatusInLocalStorage(_propertyStatus)) {
            alert('Failed to update local storage! Please try again.');
        } else {
            // Success. Return to the grid.
            location.href = 'index.php?r=resPropertyStatus/admin';
        }
    }

    /**
     * Gets a property status JSON object for the given ID.
     * Returns the object, otherwise null if not found.
     */
    function getPropertyStatusFromLocalStorage(id) {
        var data = $.parseJSON(localStorage.getItem(PROPERTY_STATUS_DATA_KEY));

        var selectedPropertyStatusItem = null;

        for (var i = 0; i < data.length; i++) {
            if (data[i]['id'] == id) {
                selectedPropertyStatusItem = data[i];
                break;
            }
        }

        return selectedPropertyStatusItem;
    }

    /**
     * Updates a property status record in the local storage.
     */
    function updatePropertyStatusInLocalStorage(propertyStatus) {
        var data = $.parseJSON(localStorage.getItem(PROPERTY_STATUS_DATA_KEY));

        var success = false;

        for (var i = 0; i < data.length; i++) {
            if (data[i]['id'] == propertyStatus.id) {
                data[i] = propertyStatus;
                data[i].unsaved = true;
                localStorage.setItem(PROPERTY_STATUS_DATA_KEY, JSON.stringify(data));
                success = true;
                break;
            }
        }

        return success;
    }

}(window.WDSResPropertyStatusUpdate = window.WDSResPropertyStatusUpdate || {}, jQuery));

$(function () {
    WDSResPropertyStatusUpdate.init();
});
