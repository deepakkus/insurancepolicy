L.Control.RiskControl = L.Control.extend({

    includes: L.Mixin.Events,

    options: {
        position: 'topleft'
    },

    initialize: function(geocoderControl, options) {
        L.Util.setOptions(this, options);
        this._geocoderControl = geocoderControl;
        this._geocoderFormInput = document.getElementsByClassName('leaflet-control-mapbox-geocoder-form')[0].firstChild;
    },

    onAdd: function(map) {

        var container = L.DomUtil.create('div', 'leaflet-risktoolbar');

        this._map = map;

        var toolbarContainer = this.addAddressToolbar();
        var riskResult = this.addRiskResult();
        var coordinatesContainer = this.addCoordinates();

        container.appendChild(toolbarContainer);
        container.appendChild(riskResult);
        container.appendChild(coordinatesContainer);

        this.setupMarker();
        this.setupMapboxGeocoder();

        L.DomEvent.disableClickPropagation(container);

        return container;
    },

    addAddressToolbar: function() {

        var container = L.DomUtil.create('div', 'leaflet-risktoolbar-section');
        var toolbarContainer = L.DomUtil.create('div', 'leaflet-risktoolbar-toolbar leaflet-bar', container);

        var dropMarker = L.DomUtil.create('a', 'leaflet-coordinates', toolbarContainer);

        dropMarker.title = 'Drop Marker On Map';

        L.DomEvent.on(dropMarker, 'click', this._clickDropMarker, this);

        return container;
    },

    addRiskResult: function() {

        var container = L.DomUtil.create('div', 'leaflet-risktoolbar-section');
        var riskContainer = L.DomUtil.create('div', 'leaflet-risktoolbar-toolbar leaflet-bar', container);
        var riskDiv = L.DomUtil.create('div', 'leaflet-wdstoolbar-riskresult', riskContainer);

        var riskResults = '<table>' +
            '<tr><td class="risk-results">VI: </td><td id="v-risk-result"></td></tr>' +
            '<tr><td class="risk-results">WHP: </td><td id="whp-risk-result"></td></tr>' +
            '<tr><td class="risk-results">WDS Risk: </td><td id="wds-risk-result"></td></tr>' +
        '</table>';

        riskDiv.innerHTML = riskResults;

        return container;
    },

    addCoordinates: function() {

        var container = L.DomUtil.create('div', 'leaflet-risktoolbar-section');
        var coordinatesContainer = L.DomUtil.create('div', 'leaflet-risktoolbar-toolbar leaflet-bar', container);

        var wrap = L.DomUtil.create('div', 'leaflet-risktoolbar-toolbar-coord-wrap', coordinatesContainer),
            form = L.DomUtil.create('form', 'leaflet-risktoolbar-toolbar-coord-form', wrap),
            inputLat = L.DomUtil.create('input', '', form),
            inputLon = L.DomUtil.create('input', '', form),
            inputSubmit = L.DomUtil.create('input', '', form);

        inputLat.type = 'text';
        inputLat.setAttribute('placeholder', 'Latitude');
        this._inputLat = inputLat;

        inputLon.type = 'text';
        inputLon.setAttribute('placeholder', 'Longitude');
        this._inputLon = inputLon;

        inputSubmit.type = 'submit';
        inputSubmit.style.display = 'none';

        L.DomEvent.addListener(form, 'submit', this._formMarkerByCoordiantes, this);

        return container;
    },

    setupMarker: function() {

        var latlng = this._map.getCenter();

        this.marker = new L.Marker(latlng, {
            draggable: true,
            icon: L.mapbox.marker.icon({
                'marker-size': 'medium',
                'marker-symbol': 'fire-station',
                'marker-color': '#FF0000'
            })
        });

        var popupContainer = document.createElement('div');

        var table = '<table>' +
            '<tr><td><b>Coordinates: </b></td><td id="risk-coords"></td></tr>' +
            '<tr><td><b>Address: </b></td><td id="risk-address"></td></tr>' +
            '<tr><td><b>Location Type: </b></td><td id="risk-location-type"></td></tr>' +
            '<tr><td><b>Location Score: </b></td><td id="risk-location-score"></td></tr>' +
        '</table>';

        var getRisk = document.createElement('a');
        var removeMarker = document.createElement('a');
        var loadingIcon = document.createElement('div');

        getRisk.href = '#'
        getRisk.innerText = 'Get Risk';
        removeMarker.href = '#'
        removeMarker.innerText = 'Remove Marker'
        popupContainer.className = 'leaflet-wdstoolbar-riskmarker';
        loadingIcon.className = 'leaflet-wdstoolbar-loading';

        popupContainer.innerHTML = table;
        popupContainer.appendChild(getRisk);
        popupContainer.appendChild(removeMarker);
        popupContainer.appendChild(loadingIcon);

        var rowCoordsContent = popupContainer.querySelector('#risk-coords')
        var rowAddressContent = popupContainer.querySelector('#risk-address');
        var rowLocationTypeContent = popupContainer.querySelector('#risk-location-type');
        var rowLocationScoreContent = popupContainer.querySelector('#risk-location-score');

        rowCoordsContent.innerText = latlng.lat.toFixed(6) + ', ' + latlng.lng.toFixed(6);

        this._popupCoord = rowCoordsContent;
        this._popupAddress = rowAddressContent;
        this._popupLocationType = rowLocationTypeContent;
        this._popupLocationScore = rowLocationScoreContent;

        this.marker.bindPopup(popupContainer, {
            maxWidth: 450,
            minWidth: 300
        });

        this._map.on('popupopen', function(e) {
            // Make sure this popup config is targeted towards dom objects and not string bindPopups
            if (typeof e.popup.getContent() === 'object') {
                e.popup.options.maxWidth = 450;
                e.popup.options.minWidth = 300;
            }
        });

        L.DomEvent.on(getRisk, 'click', this._markerGetRisk, this);
        L.DomEvent.on(removeMarker, 'click', this._markerRemove, this);

        this.marker.on('dragend', function(e) {
            this._geocoderFormInput.value = '';
            var pos = e.target.getLatLng();
            rowCoordsContent.innerText = pos.lat.toFixed(6) + ', ' + pos.lng.toFixed(6);
            rowAddressContent.innerText = '';
            rowLocationTypeContent.innerHTML = '';
            rowLocationScoreContent.innerText = '';
            e.target.openPopup();
        }.bind(this));
    },

    setupMapboxGeocoder: function() {

        var map = this._map;
        var marker = this.marker;
        var geocoderControl = this._geocoderControl;
        var popupCoord = this._popupCoord;
        var popupAddress = this._popupAddress;
        var popupLocationType = this._popupLocationType;
        var popupLocationScore = this._popupLocationScore;

        geocoderControl.options.autocomplete = true;
        geocoderControl.options.pointZoom = 15;
        geocoderControl.getContainer().style.zIndex = '1000'

        geocoderControl.on('select', function(layer) {
            this.getContainer().getElementsByClassName('leaflet-control-mapbox-geocoder-form')[0].firstChild.value = layer.feature.place_name;
            geocoderControl._closeIfOpen();
            var lat = layer.feature.geometry.coordinates[1];
            var lon = layer.feature.geometry.coordinates[0];
            var latlng = new L.LatLng(lat, lon);
            marker.setLatLng(latlng);
            if (!map.hasLayer(marker)) map.addLayer(marker);
            map.setView(latlng, 15);
            popupCoord.innerText = latlng.lat.toFixed(6) + ', ' + latlng.lng.toFixed(6);
            popupAddress.innerText = layer.feature.place_name;
            popupLocationType.innerHTML = layer.feature.id.split('.')[0];
            popupLocationScore.innerText = layer.feature.relevance;
            marker.openPopup();
        });

        geocoderControl.on('error', function(error) {
            console.log(error);
        });
    },

    _clickDropMarker: function() {
        var pos = this._map.getCenter();
        this.marker.setLatLng(pos);
        this._popupCoord.innerText = pos.lat.toFixed(6) + ', ' + pos.lng.toFixed(6);
        this._popupAddress.innerText = '';
        this._popupLocationType.innerHTML = '';
        if (!this._map.hasLayer(this.marker)) {
            this._map.addLayer(this.marker);
        }
        this.marker.openPopup();
    },

    _formMarkerByCoordiantes: function(e) {
        L.DomEvent.preventDefault(e);
        var lat = this._inputLat.value.trim();
        var lon = this._inputLon.value.trim();
        var latlng = new L.LatLng(lat, lon);
        this.marker.setLatLng(latlng);
        if (!this._map.hasLayer(this.marker)) {
            this._map.addLayer(this.marker);
        }
        this._map.setView(latlng, 15);
        this._popupCoord.innerText = latlng.lat.toFixed(6) + ', ' + latlng.lng.toFixed(6);
        this._popupAddress.innerText = '';
        this._popupLocationType.innerHTML = '';
        this.marker.openPopup();
    },

    _markerGetRisk: function(e) {
        L.DomEvent.preventDefault(e);
        var latlng = this.marker.getLatLng();

        this._actionLoading('add');

        Ajax.getrequest('/index.php?r=riskModel/riskQuery', {
            data: {
                lat: latlng.lat,
                lon: latlng.lng,
                type: 3
            },
            success: function(xhr) {
                var data = JSON.parse(xhr.responseText);
                // If "-1" if returned, there was no risk score
                if (data.error == 1) {
                    alert(data.error_message);
                    this._actionLoading('remove');
                    return;
                }
                this.fire('risk', {
                    score_v: data.score_v,
                    score_whp: data.score_whp,
                    score_wds: data.score_wds,
                    geojson: data.map,
                    latlng: latlng
                });
                this._actionLoading('remove');
                this._markerRemove();
                document.getElementById('v-risk-result').innerText = data.score_v.toFixed(6);
                document.getElementById('whp-risk-result').innerText = data.score_whp.toFixed(6);
                document.getElementById('wds-risk-result').innerText = data.score_wds.toFixed(6);
            }.bind(this),
            error: function(xhr) {
                console.log(xhr);
                alert('There is no data for this region.');
                this._actionLoading('remove');
            }.bind(this)
        });
    },

    _actionLoading: function(action) {
        var loading = document.querySelector('.leaflet-wdstoolbar-loading');
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

    _markerRemove: function(e) {
        if (e) L.DomEvent.preventDefault(e);
        if (this._map.hasLayer(this.marker)) {
            this._map.removeLayer(this.marker);
        }
    },
});

L.control.riskControl = function(geocoderControl, options) {
    return new L.Control.RiskControl(geocoderControl, options);
};

Ajax = {
    getrequest: function(url, settings) {
        var xhr = new XMLHttpRequest();
        var settings = settings || {};
        if (settings.data) url += '&' + this.param(settings.data);
        xhr.open(settings.method || 'GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function(state) {
            if (xhr.readyState == 4) {
                if (xhr.status == 200 && settings.success) {
                    settings.success(xhr);
                } else if (xhr.status != 200 && settings.error) {
                    settings.error(xhr);
                }
            }
        };
        xhr.send(settings.data || '');
    },
    param: function(object) {
        var encodedString = '';
        for (var prop in object) {
            if (object.hasOwnProperty(prop)) {
                if (encodedString.length > 0) {
                    encodedString += '&';
                }
                encodedString += encodeURI(prop + '=' + object[prop]);
            }
        }
        return encodedString;
    }
};