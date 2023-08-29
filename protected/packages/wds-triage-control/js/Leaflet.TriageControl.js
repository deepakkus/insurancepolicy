// Triage Zone Object

var TriageZoneArea = function () {
    this.init();
};

TriageZoneArea.prototype.init = function () {
    this.id = null;
    this.triageZoneId = null;
    this.geog = null;
    this.notes = null;
};

// Leaflet Triage Zone plugin

L.Control.TriageControl = L.Control.extend({

    initialize: function (triageZoneAreas, editableLayers, options) {
        L.Util.setOptions(this, options);
        this.triageZoneAreas = triageZoneAreas;
        this.editableLayers = editableLayers;
    },

    options: {
        position: 'topright'
    },

    onAdd: function (map) {

        var container = L.DomUtil.create('div', 'leaflet-triagetoolbar');

        this.map = map;
        this.intersectionLayers = new L.LayerGroup().addTo(map);

        this.setupLeafletDraw();
        this.setupLeafletDrawListeners();

        var layerContainer = this.addTriageLayerContainer();
        var submitButtonContainer = this.addSubmitButtonContainer();

        container.appendChild(layerContainer);
        if (this.isAdvancedUpload) {
            container.appendChild(this.addUploadContainer());
        }
        container.appendChild(submitButtonContainer);

        L.DomEvent.disableClickPropagation(container);

        if (this.triageZoneAreas.length) {
            this.addUpdateLayers();
        }

        return container;
    },

    // On update - Adding Polygons to map and layer widget
    addUpdateLayers: function () {
        for (var i = 0; i < this.triageZoneAreas.length; i++) {
            var area = this.triageZoneAreas[i];
            var geojson = JSON.parse(area.geog);
            var latlngs = geojson.coordinates[0].map(function (latlng) { return new L.LatLng(latlng[1], latlng[0]); });
            var polygon = new L.Polygon(latlngs);
            polygon.setStyle(this.drawControl.options.draw.polygon.shapeOptions);
            this.editableLayers.addLayer(polygon);

            // Adding to widget
            polygon.notes = area.notes;
            polygon.bindLabel('Work Zone: ' + area.notes);
            this.addTriageLayerToTable(polygon);
        }
    },

    // Setting up leaflet draw plugin
    setupLeafletDraw: function () {

        var drawOptions = {
            position: 'topleft',
            draw: {
                polyline: false,
                circle: false,
                rectangle: false,
                marker: false,
                polygon: {
                    allowIntersection: false,
                    drawError: {
                        color: 'blue',
                        message: '<b>NOPE!</b> Now allowed to cross the streams!'
                    },
                    shapeOptions: {
                        fillColor: '#66FFFF',
                        color: '#333333',
                        fillOpacity: 0.6,
                        opacity: 0.8,
                        weight: 3.0
                    }
                }
            },
            edit: {
                featureGroup: this.editableLayers,
                edit: {
                    selectedPathOptions: {
                        maintainColor: true,
                        fillOpacity: 0.3
                    }
                }
            }
        };

        L.drawLocal.draw.toolbar.buttons.polygon = 'Draw a new work zone!';
        L.drawLocal.draw.handlers.polygon.tooltip.start = 'Click to start drawing a work zone.';
        L.drawLocal.draw.handlers.polygon.tooltip.edit = 'Click to continue drawing this work zone.';
        L.drawLocal.draw.handlers.polygon.tooltip.end = 'Click first point to close this work zone.';
        L.drawLocal.draw.handlers.polygon.tooltip.end = 'Click first point to close this work zone.';
        L.drawLocal.edit.toolbar.buttons.edit = 'Edit Zones.';
        L.drawLocal.edit.toolbar.buttons.remove = 'Delete Zones.';

        this.drawControl = new L.Control.Draw(drawOptions).addTo(this.map);
    },

    // Setting up leaflet draw listeners
    setupLeafletDrawListeners: function () {

        this.map.on(L.Draw.Event.CREATED, function (e) {
            this.editableLayers.addLayer(e.layer);
            this.addTriageLayerToTable(e.layer);
        }.bind(this));

        this.map.on(L.Draw.Event.DELETED, function (e) {
            e.layers.getLayers().forEach(function (layer) {
                this.removeTriageLayerFromTable(e.layers.getLayerId(layer));
            }.bind(this));
        }.bind(this));

        this.map.on(L.Draw.Event.DRAWSTART, function (e) {
            this.intersectionLayers.clearLayers();
        }.bind(this));

        this.map.on(L.Draw.Event.DRAWSTOP, function (e) {
            this.intersectionLayers.clearLayers();
        }.bind(this));

        this.map.on(L.Draw.Event.EDITSTART, function (e) {
            this.intersectionLayers.clearLayers();
        }.bind(this));

        this.map.on(L.Draw.Event.EDITSTOP, function (e) {
            this.intersectionLayers.clearLayers();
        }.bind(this));

        this.map.on(L.Draw.Event.DELETESTART, function (e) {
            this.intersectionLayers.clearLayers();
        }.bind(this));

        this.map.on(L.Draw.Event.DELETESTOP, function (e) {
            this.intersectionLayers.clearLayers();
        }.bind(this));
    },

    // Checking all layers for a geomtric intersection
    isIntersectingPolygons: function () {
        var layers = this.editableLayers.getLayers();
        var intersecting = false;
        if (layers.length > 1) {
            var layerIndexes = Object.keys(layers).map(Number.call, Number)
            var uniqueCombinations = k_combinations(layerIndexes, 2);
            for (var i = 0; i < uniqueCombinations.length; i++) {
                var index1 = uniqueCombinations[i][0];
                var index2 = uniqueCombinations[i][1];
                var intersection = turf.intersect(layers[index1].toGeoJSON(), layers[index2].toGeoJSON());
                if (intersection !== undefined) {
                    this.intersectionLayers.addLayer(
                        new L.GeoJSON(intersection, {
                            style: function (feature) {
                                return {
                                    fillOpacity: 0.7,
                                    fillColor: 'red',
                                    color: 'black',
                                    opacity: 1.0,
                                    weight: 0.5
                                };
                            }
                        })
                    );
                    intersecting = true;
                }
            }
        }
        return intersecting;
    },

    // Creating DOM to hold the triage zones
    addTriageLayerContainer: function () {
        var container = L.DomUtil.create('div', 'leaflet-triagetoolbar-section');
        var triageContainer = L.DomUtil.create('div', 'leaflet-triagetoolbar-toolbar leaflet-bar', container);
        var triageDiv = L.DomUtil.create('div', 'leaflet-wdstoolbar-triagelayers', triageContainer);

        this.triageLayersTable = document.createElement('table');
        this.triageLayersTable.id = 'triage-layers-table';
        this.triageLayersTable.innerHTML = '<tr>' +
            '<th>Layer</th><th>Notes</th>' +
            '</tr>';

        triageDiv.appendChild(this.triageLayersTable);

        return container;
    },

    // Creating DOM to submit the triage zones
    addSubmitButtonContainer: function () {
        var container = L.DomUtil.create('div', 'leaflet-triagetoolbar-section');
        var sumbitContainer = L.DomUtil.create('div', 'leaflet-triagetoolbar-toolbar leaflet-bar', container);
        var submitButton = L.DomUtil.create('button', 'leaflet-wdstoolbar-submit', sumbitContainer);

        L.DomUtil.addClass(submitButton, 'btn btn-small btn-primary');
        submitButton.setAttribute('type', 'button');
        submitButton.innerText = 'Submit';

        L.DomEvent.on(submitButton, 'click', this.submitTriageZones, this);

        return container;
    },

    // Add KML upload drag and drop HTML 5 functionality
    addUploadContainer: function () {
        var container = L.DomUtil.create('div', 'leaflet-triagetoolbar-section');
        var uploadContainer = L.DomUtil.create('div', 'leaflet-triagetoolbar-toolbar leaflet-bar', container);
        var uploadDiv = L.DomUtil.create('div', 'leaflet-wdstoolbar-uploadfile', uploadContainer);

        var uploadForm = document.createElement('form');

        uploadForm.innerHTML = '<div class="upload-labels">' +
            '<label for="fileinput">Upload a KML here</label>' +
            '<input type="file" id="fileinput" style="display:none;" />' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="33" viewBox="0 0 50 43" fill="#92b0b3">' +
            '<path d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"></path>' +
            '</svg>' +
            '</div>';

        uploadDiv.appendChild(uploadForm);

        ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(function (event) {
            uploadForm.addEventListener(event, function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragover', 'dragenter'].forEach(function (event) {
            uploadForm.addEventListener(event, function () {
                if (!L.DomUtil.hasClass(uploadForm, 'active')) {
                    L.DomUtil.addClass(uploadForm, 'active');
                }
            });
        });

        ['dragleave', 'dragend', 'drop'].forEach(function (event) {
            uploadForm.addEventListener(event, function () {
                if (L.DomUtil.hasClass(uploadForm, 'active')) {
                    L.DomUtil.removeClass(uploadForm, 'active');
                }
            });
        });

        uploadForm.addEventListener('drop', function (e) {
            var files = e.target.files || e.dataTransfer.files;
            this.intersectionLayers.clearLayers();
            if (files.length) {
                var file = files[0];
                // Read file
                var reader = new FileReader();
                reader.onload = (function (file, _this) {
                    return function () {
                        if (file.type === 'application/vnd.google-earth.kml+xml') {
                            var contents = this.result;
                            var latlngbounds = null;
                            // Run contents of file through leaflet omnivore
                            omnivore.kml.parse(contents).eachLayer(function (layer) {
                                var polygon = new L.Polygon(layer.getLatLngs());
                                var bounds = layer.getBounds();
                                // Adding kml layer to layer control and map
                                polygon.setStyle(_this.drawControl.options.draw.polygon.shapeOptions);
                                _this.editableLayers.addLayer(polygon);
                                polygon.bindLabel('Need to add notes!');
                                _this.addTriageLayerToTable(polygon);
                                // Creating bounds
                                if (latlngbounds instanceof L.LatLngBounds) {
                                    latlngbounds.extend(bounds);
                                } else {
                                    latlngbounds = new L.LatLngBounds(bounds.getSouthWest(), bounds.getNorthEast());
                                }
                            });

                            if (latlngbounds !== null) {
                                _this.map.fitBounds(latlngbounds);
                            }
                        } else {
                            alert('The uploaded file must be a KML!');
                        }
                    };
                })(file, this);
                reader.readAsText(file);
            }
        }.bind(this));

        return container;
    },

    // Adding triage layer to table by layer ID
    addTriageLayerToTable: function (layer) {
        var layerID = this.editableLayers.getLayerId(layer);
        var trElement = document.createElement('tr'),
            tdElement1 = document.createElement('td'),
            tdElement2 = document.createElement('td'),
            notesElement = document.createElement('input');

        trElement.setAttribute('data-layer-id', layerID);
        tdElement1.setAttribute('class', 'triage-results');
        tdElement1.innerText = 'Zone:';

        notesElement.type = 'text';
        notesElement.maxLength = 200;
        notesElement.value = '';

        tdElement2.appendChild(notesElement);
        trElement.appendChild(tdElement1);
        trElement.appendChild(tdElement2);
        this.triageLayersTable.appendChild(trElement);
        this.bindEventListeners.apply(this, [trElement, notesElement, layer]);

        // If layer has notes property, set the input value
        if (layer.hasOwnProperty('notes')) {
            notesElement.value = layer.notes;
        } else {
            layer.bindLabel('Need to add notes!');
        }
    },

    // Removing triage layer from table by layer ID
    removeTriageLayerFromTable: function (layerID) {
        var trElement = this.triageLayersTable.querySelector('tr[data-layer-id="' + layerID + '"]');
        this.triageLayersTable.removeChild(trElement);
    },

    // Binding even listeners to table tr element
    bindEventListeners: function (trElement, notesElement, layer) {
        // Darken triage zone and trElement on trElement mouseover
        trElement.addEventListener('mouseover', function (e) {
            this.style.backgroundColor = 'rgba(0,51,255,0.7)';
            layer.setStyle({
                fillOpacity: 0.7,
                fillColor: '#0033ff',
                color: '#000000',
                opacity: 1.0
            });
        });
        // Light triage zone and trElement on trElement mouseover
        trElement.addEventListener('mouseout', function (e) {
            this.style.backgroundColor = '';
            layer.setStyle({
                fillOpacity: layer.editing._enabled ? 0.3 : 0.6,
                fillColor: '#66FFFF',
                color: '#333333',
                opacity: 0.8
            });
        });
        // Change map layer label on input change
        notesElement.addEventListener('input', function (e) {
            layer.bindLabel('Work Zone: ' + e.target.value);
        });
    },

    // Add/Remove loading gif
    actionLoading: function (action) {
        var loading = document.querySelector('.leaflet-wdstoolbar-submit');
        if (action === 'add') {
            if (!L.DomUtil.hasClass(loading, 'active')) {
                L.DomUtil.addClass(loading, 'active');
            }
        }
        if (action === 'remove') {
            if (L.DomUtil.hasClass(loading, 'active')) {
                L.DomUtil.removeClass(loading, 'active');
            }
        }
    },

    // Sumbitting triage zones
    submitTriageZones: function (e) {

        var triageZones = [];
        var triagePriorities = [];
        var trElements = this.triageLayersTable.querySelectorAll('tr');
        var noticeSelect = document.querySelector('select[name="ResTriageZone[notice_id]"]');

        // submit validation
        if (!noticeSelect.options.length) {
            alert('Make sure a notice is selected first!');
            return false;
        }

        var noticeID = noticeSelect.options[noticeSelect.selectedIndex].value;

        if (noticeID === '') {
            alert('Make sure a notice is selected first!');
            return false;
        }

        if (this.isIntersectingPolygons() === true) {
            alert('There are intersecting polygons, please fix this!');
            return false;
        }

        // Creating javascript triage zone areas to pass to Admin
        for (var i = 0; i < trElements.length; i++) {
            var layerID = trElements[i].getAttribute('data-layer-id');
            if (layerID) {
                var layer = this.editableLayers.getLayer(layerID);
                var notes = trElements[i].lastChild.firstChild.value;

                // Notes must be set
                if (notes === '') {
                    triageZones.length = 0;
                    triagePriorities.length = 0;
                    alert('Notes must be assigned to all zones!');
                    return false;
                }

                // This priority is already selected
                if (triagePriorities.indexOf(notes) !== -1) {
                    triageZones.length = 0;
                    triagePriorities.length = 0;
                    alert('All notes must be unique!');
                    return false;
                }

                var triageZoneArea = new TriageZoneArea();
                triageZoneArea.geog = layer.toGeoJSON();
                triageZoneArea.notes = notes;

                triageZones.push(triageZoneArea);
                triagePriorities.push(notes);
            }
        }

        // Disabled submit button
        e.target.disabled = true;
        this.actionLoading('add');

        // Submit data
        $.post(window.location.href, {
            noticeID: noticeID,
            triageZones: JSON.stringify(triageZones)
        }, function (data) {
            if (data === true) {
                this.actionLoading('remove');
                window.location.href = '/index.php?r=resTriageZone/admin';
            } else {
                e.target.disabled = false;
                this.actionLoading('remove');
                var messageText = 'Something went wrong!';
                if (data.hasOwnProperty('notice_id')) {
                    messageText += ('\n' + data.notice_id[0]);
                }
                alert(messageText);
                console.log(data);
            }
        }.bind(this), 'json');
    },

    // Determine if browser supports HTML 5 drag and drop uploads
    isAdvancedUpload: function () {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FileReader' in window;
    }
});

L.control.triageControl = function (options) {
    return new L.Control.TriageControl(options);
};

/**
 * K-combinations
 * 
 * Get k-sized combinations of elements in a set.
 * 
 * Usage:
 *   k_combinations(set, k)
 * 
 * Parameters:
 *   set: Array of objects of any type. They are treated as unique.
 *   k: size of combinations to search for.
 * 
 * Return:
 *   Array of found combinations, size of a combination is k.
 * 
 * Examples:
 * 
 *   k_combinations([1, 2, 3], 1)
 *   -> [[1], [2], [3]]
 * 
 *   k_combinations([1, 2, 3], 2)
 *   -> [[1,2], [1,3], [2, 3]
 * 
 *   k_combinations([1, 2, 3], 3)
 *   -> [[1, 2, 3]]
 * 
 *   k_combinations([1, 2, 3], 4)
 *   -> []
 * 
 *   k_combinations([1, 2, 3], 0)
 *   -> []
 * 
 *   k_combinations([1, 2, 3], -1)
 *   -> []
 * 
 *   k_combinations([], 0)
 *   -> []
 */
function k_combinations(set, k) {
    var i, j, combs, head, tailcombs;

    // There is no way to take e.g. sets of 5 elements from
    // a set of 4.
    if (k > set.length || k <= 0) {
        return [];
    }

    // K-sized set has only one K-sized subset.
    if (k == set.length) {
        return [set];
    }

    // There is N 1-sized subsets in a N-sized set.
    if (k == 1) {
        combs = [];
        for (i = 0; i < set.length; i++) {
            combs.push([set[i]]);
        }
        return combs;
    }

    combs = [];
    for (i = 0; i < set.length - k + 1; i++) {
        // head is a list that includes only our current element.
        head = set.slice(i, i + 1);
        // We take smaller combinations from the subsequent elements
        tailcombs = k_combinations(set.slice(i + 1), k - 1);
        // For each (k-1)-combination we join it with the current
        // and store it to the set of k-combinations.
        for (j = 0; j < tailcombs.length; j++) {
            combs.push(head.concat(tailcombs[j]));
        }
    }
    return combs;
}