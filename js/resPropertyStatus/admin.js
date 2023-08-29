/**
 * resPropertyStatus/admin.js
 *
 * Defines the WDSResPropertyStatus namespace with associated methods
 * for the resPropertyStatus/admin view.
 */
(function (wds, $, undefined) {
    // Private members
    var PROPERTY_STATUS_DATA_KEY = 'property_status';
    var PROPERTY_STATUS_HTML_SNAPSHOT_KEY = 'property_status_html';
    var SELECTED_PROPERTY_STATUS_ID_KEY = 'selected_property_status_id';

    /**
     * Initializes the view, setups up click events and the like.
     */
    wds.init = function () {
        bindHandlers();
        toggleControlsByConnection();

        if (navigator.onLine && checkForUnsavedChanges()) {
            $('#btnSavePendingChanges').show();
        }
    };

    /**
     * Called just before the property status grid will be updated via ajax.
     */
    wds.onBeforeGridUpdate = function () {
        log('Updating the grid.');

        // Save unsaved changes or else they will get lost!
        return savePendingChanges();
    };

    /**
     * Called after the property status grid is updated via ajax.
     * We'll use this to refresh the local storage.
     */
    wds.onAfterGridUpdate = function () {        
        saveGridDataToCache();
    };

    /**
     * Called when the manifest resources have been newly re-downloaded.
     */
    wds.onCacheUpdateReady = function () {
        if (applicationCache.status == applicationCache.UPDATEREADY) {
            log('Application cache updateready, swapping cache.');
            saveGridDataToCache();
            location.reload();
        }
    };

    /**
     * Called on page loads after the first download of the manifest.
     */
    wds.onNoUpdate = function () {
        log('No update event fired.');
        if (navigator.onLine) {
            $.fn.yiiGridView.update('gridResponsePropertyStatus');
        } else {
            restoreGridHtmlFromCache();
        }
    };

    /**
     * Called when the browser can't download the manifest (normal in the offline case).
     */
    wds.onCacheError = function (e) {
        log('Cache error event fired.');
        // We'll use this opportunity to make sure the grid is restored from its last refresh 
        // point so the cached html (and potentially out of date) version is not displayed.
        restoreGridHtmlFromCache();
    };

    /**
     * Restores the grid's HTML from local storage when reloading page from a cached version.
     * Ensures the data is the same as when last refreshed before we went offline.
     */ 
    function restoreGridHtmlFromCache() {
        var updatedID = localStorage.getItem(SELECTED_PROPERTY_STATUS_ID_KEY);

        if (updatedID !== null) {
            log('Restoring grid HTML from snapshot.');

            var html = localStorage.getItem(PROPERTY_STATUS_HTML_SNAPSHOT_KEY);
            $('#gridResponsePropertyStatus').html(html);

            if (!navigator.onLine) {
                $('#gridResponsePropertyStatus > table > thead > tr.filters').hide();
            }
        }

        log('Updating grid HTML snapshot.');

        var data = $.parseJSON(localStorage.getItem(PROPERTY_STATUS_DATA_KEY));

        if (!data)
            return false;

        // Get an array of the grid's current columns and their bound field names.
        var columns = [];
        $('#gridResponsePropertyStatus .sort-link').each(function (index, obj) {
            var columnName = getParameterByName($(obj).attr('href'), 'ResTriggeredWithPropertyStatus_sort');
            columnName = columnName.replace('.desc', '');
            columns.push(columnName);
        });
        
        // Find the updated row's data.
        var updatedRowData = null;
        for (var i = 0; i < data.length; i++) {
            if (data[i].id === updatedID) {
                updatedRowData = data[i];
                break;
            }
        }

        if (!updatedRowData)
            return false;

        // Find the updated tr row in the grid.
        var fields = $('a[href$="' + updatedRowData.id + '"]').parents('tr').children('td');

        // Update that row in the HTML snapshot with the new data.
        for (var i = 0; i < fields.length; i++) {
            if (columns[i] === 'has_photo' || columns[i] === 'threat')
                var text = updatedRowData[columns[i]] === '1' || updatedRowData[columns[i]] === 1 ? 'Yes' : 'No';
            else if (columns[i] === 'distance')
                var text = updatedRowData[columns[i]] ? parseFloat(updatedRowData[columns[i]]).toFixed(2) : '';
            else
                var text = updatedRowData[columns[i]];

            $(fields[i + 1]).empty().append(text);
        }

        // Store a new HTML snapshot so the update can be recalled on the next offline page load.
        var html = $('#gridResponsePropertyStatus').html();
        localStorage.setItem(PROPERTY_STATUS_HTML_SNAPSHOT_KEY, html);

        // Finally, we don't need the selected property ID anymore.
        localStorage.removeItem(SELECTED_PROPERTY_STATUS_ID_KEY);
    }

    function saveGridDataToCache() {
        log('Saving grid data to cache.');

        // Get the grid's data from the HTML, which is refreshed on every AJAX call.
        var data = $('#offlineExtendedGridViewData').html();

        // Save to local storage.
        localStorage.setItem(PROPERTY_STATUS_DATA_KEY, data);

        // Get the grid's html at this moment so it can be restored for offline use.
        var html = $('#gridResponsePropertyStatus').html();

        localStorage.setItem(PROPERTY_STATUS_HTML_SNAPSHOT_KEY, html);

        // Refresh the checked columns, since the cached page will not be updated to reflect the user's latest selections.
        updateCheckedColumns();
    }

    /**
     * Refreshes the checked columns in the "Columns to Show" box.
     */
    function updateCheckedColumns() {
        // Clear all checked boxes.
        $('input[data-type="propertyStatusColumn"]').prop('checked', false);

        // Reset checked boxes based on the current columns in the grid.
        $('#gridResponsePropertyStatus .sort-link').each(function (index, object) {
            var columnName = getParameterByName($(object).attr('href'), 'ResTriggeredWithPropertyStatus_sort');
            columnName = columnName.replace('.desc', '');
            
            $('input[name="wds_response_prop_status_columnsToShow[' + columnName + ']"]').prop('checked', true);
        });
    }

    /**
     * Toggles various HTML controls for offline mode based on internet connectivity.
     */
    function toggleControlsByConnection() {
        if (navigator.onLine) {
            $('#offlineText').text('').hide();
            $('#mainMbMenuCover').hide();
            $('#btnColumns').attr('disabled', false);
            $('#btnResetFilters').attr('disabled', false);
            $('#gridResponsePropertyStatus > table > thead > tr.filters').show();
            $('.pagination').show();

            // Enable "home" breadcrumb link
            if ($(".breadcrumbs span:contains('Home')").length)
                $(".breadcrumbs span:contains('Home')")[0].outerHTML = '<a href="/index.php">Home</a>';
        } else {
            $('#offlineText').text('(Offline Mode)').show();
            $('#mainMbMenuCover').show();
            $('#btnColumns').attr('disabled', true);
            $('#btnResetFilters').attr('disabled', true);
            $('#gridResponsePropertyStatus > table > thead > tr.filters').hide();
            $('.pagination').hide();
            
            // Disable "home" breadcrumb link
            $(".breadcrumbs a:contains('Home')")[0].outerHTML = '<span>Home</span>';
        }
    }

    /**
     * Binds event handlers.
     */
    function bindHandlers() {
        $('.search-toggle').click(function () {
            $('.search-form').slideToggle();
            return false;
        });

        $('.search-form form').submit(function () {
            $('.search-form').slideToggle();
            $.fn.yiiGridView.update('gridResponsePropertyStatus', {
                data: $(this).serialize()
            });
            return false;
        });

        $('#btnColumns').click(function () {
            $('.column-form').slideToggle();
            return false;
        });

        $('.column-form form').submit(function () {
            $('.column-form').slideToggle();
            $.fn.yiiGridView.update('gridResponsePropertyStatus', {
                data: $(this).serialize()
            });
            return false;
        });

        $('.clear-checked').click(function () {
            $('input[type=checkbox]').each(function () {
                $(this).removeAttr('checked');
            });
            return false;
        });

        $('#closeColumnsToShow').click(function () {
            $('.column-form').slideUp();
            return false;
        });

        $('#closeAdvancedSearch').click(function () {
            $('.search-form').slideUp();
            return false;
        });

        $('.default-advanced').click(function () {
            $('input[type=text], select').each(function () {
                $(this).val('');
            });
            return false;
        });

        $('#defaultQuickView').click(function () {
            $('input[type=checkbox]').each(function () {
                $(this).removeAttr('checked');
            });

            var columnsToCheck = [
                'client_name',
                'fire_name',
                'notice_name',
                'member_last_name',
                'property_address_line_1',
                'property_response_status',
                'priority',
                'threat',
                'distance',
                'engine_name',
                'status',
                'date_visited'
            ];

            for (var i in columnsToCheck) {
                $('input[name="wds_response_prop_status_columnsToShow[' + columnsToCheck[i] + ']"]').prop('checked', true);
            }
            return false;
        });

        $('#btnResetFilters').click(function () {
            // Clear the cached grid HTML snapshot.
            if (PROPERTY_STATUS_HTML_SNAPSHOT_KEY in localStorage)
                localStorage.removeItem(PROPERTY_STATUS_HTML_SNAPSHOT_KEY);

            location.href = 'index.php?r=respropertystatus/admin&resetFilters=1';
        });

        $('#btnPrintChecklist').click(function () {
            location.href = 'index.php?r=resPropertyStatus/admin&print=1';
        });

        $(document).on('click', '.update', function () {
            var id = getParameterByName($(this).attr('href'), 'id');

            // Remember the selected property status ID so the update form knows what to load.
            localStorage.setItem(SELECTED_PROPERTY_STATUS_ID_KEY, id);

            // Go to the update form (note: only the page without any query string parameters is cached).
            location.href = 'index.php?r=resPropertyStatus/update';

            return false;
        });

        $('#btnSavePendingChanges').click(function () {
            savePendingChanges();
        });

        applicationCache.addEventListener('updateready', WDSResPropertyStatus.onCacheUpdateReady, false);
        applicationCache.addEventListener('error', WDSResPropertyStatus.onCacheError, false);
        applicationCache.addEventListener('noupdate', WDSResPropertyStatus.onNoUpdate, false);

        window.addEventListener("offline", function (e) {
            toggleControlsByConnection();
        });

        window.addEventListener("online", function (e) {
            toggleControlsByConnection();
            savePendingChanges();
        });
    }

    /**
     * Saves pending unsaved property status changes.
     */
    function savePendingChanges() {
        var data = $.parseJSON(localStorage.getItem(PROPERTY_STATUS_DATA_KEY));

        if (!data)
            return;

        var propertyStatuses = [];
        var pendingChangesExist = false;

        for (var i = 0; i < data.length; i++) {
            if (data[i].unsaved) {
                pendingChangesExist = true;

                propertyStatuses.push({
                    "id": data[i].id,
                    "engine_id": data[i].engine_id,
                    "division": data[i].division,
                    "status": data[i].status,
                    "actions": data[i].actions,
                    "has_photo": data[i].has_photo,
                    "other_issues": data[i].other_issues,
                    "date_visited": data[i].date_visited
                });
            }
        }

        if (!pendingChangesExist)
            return;

        var inputData = {
            "data": {
                "property_statuses": propertyStatuses
            }
        };

        $.ajax({
            type: 'POST',
            url: 'index.php?r=resPropertyStatus/savePropertyStatus',
            data: 'data=' + JSON.stringify(inputData)
        }).done(function (result) {
            if (result && result.error == 0) {
                flashMessage('All pending changes saved successfully.', 'flash-success');

                markAllChangesAsSaved();

                $.fn.yiiGridView.update('gridResponsePropertyStatus');
                $('#btnSavePendingChanges').hide();
            } else {
                // An error occurred.
                flashMessage('Failed to save pending changes. Please try again later.', 'flash-error');
                log(result.errorMessage);
                $('#btnSavePendingChanges').show();
            }
        }).fail(function (jqXHR, text) {
            flashMessage('Failed to save pending changes. Please try again later.', 'flash-error');
            log('Failed to save property status! ' + text);
            $('#btnSavePendingChanges').show();
        });

        return false;
    }

    function checkForUnsavedChanges() {
        var data = $.parseJSON(localStorage.getItem(PROPERTY_STATUS_DATA_KEY));

        if (!data)
            return false;

        var unsavedChangesExist = false;

        for (var i = 0; i < data.length; i++) {
            if (data[i].unsaved) {
                unsavedChangesExist = true;
                break;
            }
        }

        return unsavedChangesExist;
    }

    /**
     * Marks all changes as saved in local storage.
     */
    function markAllChangesAsSaved() {
        var data = $.parseJSON(localStorage.getItem(PROPERTY_STATUS_DATA_KEY));

        for (var i = 0; i < data.length; i++) {
            if (data[i].unsaved) {
                data[i].unsaved = false;
            }
        }

        localStorage.setItem(PROPERTY_STATUS_DATA_KEY, JSON.stringify(data));
    }

    /**
     * Flashes the Yii success or error message box in the header.
     * @param message - message text to display
     * @param flashType - 'flash-success' or 'flash-error'
     */
    function flashMessage (message, flashType) {
        // Make sure the flash div exists with the appropriate flash type.
        $('#header .flash-wrapper').remove();
        $('#header').append('<div class="flash-wrapper"><div class="flash hidden"><ul class="flashes"><li><div class="' + flashType + '"></div></li></ul></div></div>');

        // Display it.
        $('.' + flashType).text(message);
        $('.flash-wrapper .flash').fadeIn('slow').delay(5000).fadeOut('slow');
    }

    /**
     * Gets query string parameter by name for a given URL.
     */
    function getParameterByName(url, name) {
        var match = RegExp('[?&]' + name + '=([^&]*)').exec(url);
        return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
    }

}(window.WDSResPropertyStatus = window.WDSResPropertyStatus || {}, jQuery));

$(function () {
    WDSResPropertyStatus.init();
});