$(document).ready(function () {
    /**
     * resPhVisit/updateVisit.js
     *
     * Defines the WDSResPolicyActionUpdate namespace with associated methods
     * for the resPhVisit/update view.
     */
    (function (wds, $, undefined) {

        /**
         * Initializes the view, setups up click events and the like.
         */
        wds.init = function () {
            var photoID = getQueryStringVariables()['photoID'];

            if (photoID > 0) {
                // Highlight the selected row in the grid.
                $('#gridPhotos table tbody tr td:first-child').filter(function() {
                    return $(this).text() === photoID;
                }).parent().addClass('selected');
                $('#lnkAddNewPhoto').show();
            } else {
                $('#lnkAddNewPhoto').hide();
            }

            /**
            * Listener for the sortable photo table
            */
            $("tbody.sort").sortable({
                stop: function (event, ui) {
                    $('.order').each(function (e) {
                        var id = $(this).data("id");
                        $('input[name="ExistingResPhPhotos[' + id + '][order]"]').val($(this).index() + 1);
                    });
                }
            });

            /**
            * Delete a photo option
            */
            $('.table').on('click', 'a.deleteButton', function (e) {
                if (!confirm('Are you sure you want to delete this image?')) {
                    e.preventDefault();
                    return false;
                }
            });
        };

        /**
         * Grid row selection event handler
         */
        wds.onGridRowSelected = function (id) {
            var gridID = $.fn.yiiGridView.getSelection(id);
            var queryVars = getQueryStringVariables();
            var id = queryVars['id'],
                fid = queryVars['fid'],
                cid = queryVars['cid'],
                fn = queryVars['fn'],
                cn = queryVars['cn'];
            location.href = 'index.php?r=resPhVisit/update&id=' + id + '&fid=' + fid + '&cid=' + cid + '&fn=' + fn + '&cn=' + cn + '&photoID=' + gridID;
        }

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
         * Manager user approval logic
         */
        $(function() {
        
            var userApprovalElement = document.getElementById('ResPhVisit_approval_user_id');
            var statusElement = document.getElementById('ResPhVisit_status');
            var approvalNeeded = ['saved','lost'];

            // enable/disable user approval drop down depending which status is selected
            statusElement.addEventListener('change', function() {
                if (approvalNeeded.indexOf(this.value) !== -1) {
                    userApprovalElement.disabled = false;
                    return false;
                }
                userApprovalElement.disabled = true;
                userApprovalElement.selectedIndex = 0;
                return false;
            });

            // On form submit, make sure appropriate user approval info in filled out
            document.getElementById('visit_photos_form').addEventListener('submit', function(e) {
                e.preventDefault();
                // If user box needs filling, but is empty, then tell the user
                if (userApprovalElement.disabled === false && userApprovalElement.value === '') {
                    alert('Manager approval must be filled out!');
                    return false;
                }
                if (userApprovalElement.disabled === true) {
                    userApprovalElement.disabled = false
                }
                this.submit();
            });
        });

    }(window.WDSResPhotoUpdate = window.WDSResPhotoUpdate || {}, jQuery));

    $(function () {
        WDSResPhotoUpdate.init();
    });


});