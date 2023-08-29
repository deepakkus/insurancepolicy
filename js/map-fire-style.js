Array.prototype.contains = function(element) {
    return this.indexOf(element) > -1;
};

String.prototype.toProperCase = function() {
    return this.toLowerCase().replace(/^(.)|\s(.)/g, function($1) {
        return $1.toUpperCase();
    });
};

/**
 * Rotates a point the provided number of degrees about another point.
 */
L.Point.prototype.rotate = function(angle, point) {
    var radius = this.distanceTo(point);
    var theta = (angle * Math.PI / 180) + Math.atan2(this.y - point.y, this.x - point.x);
    this.x = point.x + (radius * Math.cos(theta));
    this.y = point.y + (radius * Math.sin(theta));
};

/**
 * Draws a regular polygon marker on the map given a radius (or x and y radii) in pixels
 */
L.TriangleMarker = L.Path.extend({

    includes: L.Mixin.Events,

    options: {
        fill: true,
        radiusX: 10,
        radiusY: 10,
        rotation: 30.0,
        position: {
            x: 0,
            y: 0
        },
        gradient: false,
        dropShadow: false,
        clickable: true
    },

    initialize: function(centerLatLng, options) {

        L.setOptions(this, options);

        this._latlng = centerLatLng;
        this._numberOfSides = 3;
    },

    setLatLng: function(latlng) {
        this._latlng = latlng;
        return this.redraw();
    },

    projectLatlngs: function() {
        this._point = this._map.latLngToLayerPoint(this._latlng);
        this._points = this._getPoints();
    },

    getBounds: function() {
        var map = this._map,
			radiusX = this.options.radius || this.options.radiusX,
			radiusY = this.options.radius || this.options.radiusY,
			deltaX = radiusX * Math.cos(Math.PI / 4),
			deltaY = radiusY * Math.sin(Math.PI / 4),
			point = map.project(this._latlng),
			swPoint = new L.Point(point.x - deltaX, point.y + deltaY),
			nePoint = new L.Point(point.x + deltaX, point.y - deltaY),
			sw = map.unproject(swPoint),
			ne = map.unproject(nePoint);

        return new L.LatLngBounds(sw, ne);
    },

    setRadius: function(radius) {
        this.options.radius = radius;
        return this.redraw();
    },

    setRotation: function(rotation) {
        this.options.rotation = rotation;
        return this.redraw();
    },

    getLatLng: function() {
        return this._latlng;
    },

    getTextAnchor: function() {
        if (this._point) {
            return this._point;
        }
    },

    getPathString: function() {
        var anchorPoint = this.getTextAnchor();

        if (this._shape) {
            var width = this._shape.getAttribute('width');
            var height = this._shape.getAttribute('height');
            this._shape.setAttribute('x', anchorPoint.x - Number(width) / 2);
            this._shape.setAttribute('y', anchorPoint.y - Number(height) / 2);
        }

        this._path.setAttribute('shape-rendering', 'geometricPrecision');
        return new L.SVGPathBuilder(this._points).build(6);
    },

    _getPoints: function() {

        var angleSize = 360 / this._numberOfSides;
        var angle = 0;
        var points = [];
        var newPoint;
        var angleRadians;
        var radiusX = this.options.radius || this.options.radiusX;
        var radiusY = this.options.radius || this.options.radiusY;

        while (angle < 360) {
            angleRadians = angle * Math.PI / 180;
            // Calculate the point the radius pixels away from the center point at the
            // given angle;
            newPoint = this._getPoint(angleRadians, radiusX, radiusY);
            // Add the point to the latlngs array
            points.push(newPoint);
            // Increment the angle
            angle += angleSize;
        }

        return points;
    },

    _getPoint: function(angle, radiusX, radiusY) {
        var startPoint = this.options.position ? this._point.add(new L.Point(this.options.position.x, this.options.position.y)) : this._point;
        var point = new L.Point(startPoint.x + radiusX * Math.cos(angle), startPoint.y + radiusY * Math.sin(angle));
        point.rotate(this.options.rotation, startPoint);
        return point;
    }
});

L.triangleMarker = function(centerLatLng, options) {
    return new L.TriangleMarker(centerLatLng, options);
};

L.SVGPathBuilder = L.Class.extend({
    initialize: function(points) {
        this._points = points || [];
    },

    options: {
        closePath: true
    },

    _getPathString: function(points, digits) {
        var pathString = '';

        if (points.length > 0) {

            var point = points[0];
            var digits = digits !== null ? digits : 2;
            var startChar = 'M';
            var lineToChar = 'L';
            var closePath = 'Z';

            if (L.Browser.vml) {
                digits = 0;
                startChar = 'm';
                lineToChar = 'l';
                closePath = 'xe';
            }

            pathString = startChar + point.x.toFixed(digits) + ',' + point.y.toFixed(digits);

            for (var index = 1; index < points.length; index++) {
                point = points[index];
                pathString += lineToChar + point.x.toFixed(digits) + ',' + point.y.toFixed(digits);
            }

            if (this.options.closePath) {
                pathString += closePath;
            }

        }

        return pathString;
    },

    addPoint: function(point, inner) {
        this._points.push(point);
    },

    build: function(digits) {
        digits = digits || this.options.digits;
        var pathString = this._getPathString(this._points, digits);
        return pathString;
    }
});

// Extension of leaflet Control class to allow status toggling for mutliple layers in a 
// pre-existing leaflet layer control
// Code was altered from the following to customize: https://github.com/Leaflet/Leaflet/blob/0.7/src/control/Control.Layers.js
L.Control.PolicyStatusToggle = L.Control.extend({

    options: {
        collapsed: false,
        position: 'topright'
    },

    initialize: function(controlOptions, options) {
        L.setOptions(this, options);
        this._featureLayers = controlOptions.featureLayers;
        this._featureLayersGeoJson = controlOptions.featureLayersGeoJson;
        this._statusPropertyName = controlOptions.statusPropertyName;
        this._propertyStatusNames = [];
    },

    onAdd: function(map) {
        this._map = map;
        if (!this._propertyStatusNames.length) {
            this._processStatusNames();
        }
        this._initLayout();
        this._addStatusCheckboxes();
        return this._container;
    },

    // Setup DOM layout
    _initLayout: function () {

        var className = 'leaflet-control-layers';
		var container = this._container = L.DomUtil.create('div', className);

		//Makes this work on IE10 Touch devices by stopping it from firing a mouseout event when the touch is released
		container.setAttribute('aria-haspopup', true);

		if (!L.Browser.touch) {
			L.DomEvent
                .disableClickPropagation(container)
				.disableScrollPropagation(container);
		} else {
			L.DomEvent.on(container, 'click', L.DomEvent.stopPropagation);
		}

		var form = this._form = L.DomUtil.create('form', className + '-list');

		if (this.options.collapsed) {
			if (!L.Browser.android) {
				L.DomEvent
				    .on(container, 'mouseover', this._expand, this)
				    .on(container, 'mouseout', this._collapse, this);
			}
			var link = this._layersLink = L.DomUtil.create('a', className + '-toggle', container);
			link.href = '#';
			link.title = 'Layers';

			if (L.Browser.touch) {
				L.DomEvent
				    .on(link, 'click', L.DomEvent.stop)
				    .on(link, 'click', this._expand, this);
			} else {
				L.DomEvent.on(link, 'focus', this._expand, this);
			}

			this._map.on('click', this._collapse, this);
			// TODO keyboard accessibility
		} else {
			this._expand();
		}

		this._toggleList = L.DomUtil.create('div', className + '-status-toggles', form);

		container.appendChild(form);
	},

    // Add checkboxes for availible status to widget
    _addStatusCheckboxes: function() {
        this._toggleInputs = [];
        this._toggleList.innerHTML = '<b>Displayed</b>';
        this._propertyStatusNames.forEach(function(status, index) {

			var input = document.createElement('input');
			input.type = 'checkbox';
			input.className = 'leaflet-control-layers-selector propertyStatus';
			input.defaultChecked = true;
            input.setAttribute('data-status', status);

		    L.DomEvent.on(input, 'click', this._onInputClick, this);

		    var name = document.createElement('span');
		    name.innerHTML = ' ' + status.toProperCase();

            var label = document.createElement('label');
		    label.appendChild(input);
		    label.appendChild(name);

		    this._toggleList.appendChild(label);
            this._toggleInputs.push(input);

        }.bind(this));
    },

    // Find all possible status
    _processStatusNames: function() {
        this._featureLayers.forEach(function(featureLayer, index) {
            featureLayer.eachLayer(function(layer) {
                var responseStatus = layer.feature.properties[this._statusPropertyName];
                if (this._propertyStatusNames.indexOf(responseStatus) === -1) {
                    this._propertyStatusNames.push(responseStatus);
                }
            }.bind(this));
        }.bind(this));
    },

    // Applying filters to each feature layer in map
    // Sadly, leaflet doesn't have a "setFilter()" function, this must be done manually
	_onInputClick: function () {
	$( ".marker" ).remove();
        var filterFunction = null;
        var filterChecked = [];
        var statusPropertyName = this._statusPropertyName; // pass by reference, no binding needed then

        this._toggleInputs.forEach(function(input) {
            if (input.checked) { filterChecked.push(input.getAttribute('data-status')); }    
        });

        if (filterChecked.length) {
            filterFunction = function(feature, layer) { return filterChecked.indexOf(feature.properties[statusPropertyName]) !== -1; };
        } else {
            filterFunction = function(feature, layer) { return false; };
        }
		var pid =$("#client_list option:selected").index();
        // Now that the filter function has been declared, apply it to all featureLayers
        this._featureLayers.forEach(function(featureLayer, index) {
            featureLayer.options.filter = filterFunction;
            featureLayer.clearLayers();
            featureLayer.addData(this._featureLayersGeoJson[pid]);
        }.bind(this));
	},
	_onZipcodeInputClick: function () {
	},
	_expand: function () {
		L.DomUtil.addClass(this._container, 'leaflet-control-layers-expanded');
	},

	_collapse: function () {
		this._container.className = this._container.className.replace(' leaflet-control-layers-expanded', '');
	}
});

L.control.policyStatusToggle = function(options) {
    return new L.Control.PolicyStatusToggle(options);
};

// --------------------------- Styles -------------------------------------

var Perimeter = (function () {

    function perimeterPolyStyle() {
        return {
            fillColor: '#FF0000',
            weight: 2,
            opacity: 0.7,
            color: '#000000',
            fillOpacity: 0.5
        };
    }

    function perimeterPointStyle(feature) {



        var latlng = new L.LatLng(layer.feature.geometry.coordinates[1], layer.feature.geometry.coordinates[0]);
        var pointLayer = new L.CircleMarker(latlng, {
            radius: 8,
            fillColor: '#FF0000',
            color: '#000000',
            weight: 1,
            opacity: 0.7,
            fillOpacity: 0.5
        });
        fireLayer.removeLayer(layer);
        fireLayer.addLayer(pointLayer);
    }

    var bufferColors = {
        'outer':'white',
        'three': 'yellow',
        'one': 'orange',
        'half': 'red'
    };

    function getBufferColor(d) {
        return bufferColors[d];
    }

    function bufferStyle(feature) {
        return {
            fillColor: 'transparent',
            weight: 3,
            opacity: 1,
            color: getBufferColor(feature.properties.distance)
        };
    }

    return {
        perimeterPolyStyle: perimeterPolyStyle,
        perimeterPointStyle: perimeterPointStyle,
        bufferStyle: bufferStyle
    };

})();


var Threat = {
    threatStyle: function(feature) {
        return {
            fillColor: 'yellow',
            weight: 0,
            opacity: 0.4,
            weight: 0.7,
            color: 'yellow',
            fillOpacity: 0.4
        };
    }
};

var Policyholders = (function () {

    // return public functions
    return {
        pointToLayer: function (feature, latlng) {
            if (feature.properties.client == 'Mutual of Enumclaw')
            {
                feature.properties.not_enrolled_color = '#ff0000'
            }
            if (feature.properties.client == 'Pharmacists') {
                feature.properties.not_enrolled_color = '#f57ab6'
            }
            if (feature.properties.client == 'Pemco') {
                feature.properties.not_enrolled_color = '#00e6a9'
            }
            if (feature.properties.client == 'Cincinnati Insurance') {
                feature.properties.not_enrolled_color = '#c500ff'
            }
            if (feature.properties.client == 'Mutual of Enumclaw') {
                feature.properties.enrolled_color = '#ff0000'
            }
            if (feature.properties.client == 'Pharmacists') {
                feature.properties.enrolled_color = '#f57ab6'
            }
            if (feature.properties.client == 'Pemco') {
                feature.properties.enrolled_color = '#00e6a9'
            }
            if (feature.properties.client == 'Cincinnati Insurance') {
                feature.properties.enrolled_color = '#c500ff'
            }
            if (feature.hasOwnProperty('properties')) {
                if (feature.properties.response_status == 'not enrolled') {
                    return L.circleMarker(latlng, {
                        radius: 6,
                        fillColor: feature.properties.not_enrolled_color,
                        color: 'black',
                        weight: 2,
                        fillOpacity: 1.0,
                        opacity: 0.7,
						className:'marker'
                    });
                } else if (feature.properties.response_status == 'enrolled') {
                    return new L.TriangleMarker(latlng, {
                        fillColor: feature.properties.enrolled_color,
                        fillOpacity: 1.0,
                        color: 'black',
                        opacity: 1.0,
                        weight: 2,
						className:'marker'
                    });
                } else {
                    return L.circleMarker(latlng, {
                        radius: 6,
                        fillColor: '#939393',
                        color: 'black',
                        weight: 2,
                        fillOpacity: 1.0,
                        opacity: 0.7,
						className:'marker'
                    });
                }
            }
        },
        onEachFeature: function(feature, layer) {
            var props = feature.properties;
            var popup = '<p class="center" style="margin-bottom: -10px;"><b>' + props.last_name + '</b></p><br>';
            popup += '<div><b>Address:</b> ' + props.address + '</div>';
            popup += '<div><b>Status:</b> ' + props.response_status + '</div>';
            popup += '<div><b>Distance:</b> ' + String(props.distance) + ' miles</div>';
            popup += '<div><b>Property Info:</b> <a href = "/index.php?r=property/view&pid=' + props.pid + '" target="_blank">View Property</a> </div>';
            if ('wds_geocode_confidence' in props) {
                popup += '<div><b>Geocode Confidence:</b> ' + props.wds_geocode_confidence + '</div>';
            }
                
            layer.bindPopup(popup, { offset: new L.Point(0, -3) });
            if (props.response_status == 'Enrolled') { layer.bindLabel(props.last_name); }
            $('.chosen-select').append('<option value="' + props.last_name + '" data-pid="' + props.pid + '">' + props.last_name + ' - ' + props.address + '</option>');
        }
    };

})();

var Zipcodes = (function () {

    /**
     * This method performs tasks for each feature processed
     * @param {L.FeatureGroup} layer 
     */
    function onEachFeature(layer) {
        layer.on({
            mousemove: zipcodesMousemove,
            mouseout: zipcodesMouseout
        });
    }

    /**
     * This method dynamicall toggles styles based on mouse location
     * @param {object} event 
     */
    function zipcodesMousemove(event) {
        var layer = event.target;

        layer.setStyle({
            opacity: 1.0,
            weight: 3.0,
            fillOpacity: 0.9,
            color: 'blue'
        });

        if (!L.Browser.ie && !L.Browser.opera) {
            layer.bringToFront();
        }
    }

    /**
     * This method dynamicall toggles styles based on mouse location
     * @param {object} event 
     */
    function zipcodesMouseout(event) {
        zipcodesLayer.setStyle(zipcodesStyle(event.target));
    }

    /**
     * This method return a style object for map zipcodes
     * @param {object} feature 
     */
    function zipcodesStyle(feature) {
        return {
            fillColor: 'transparent',
            weight: 2.0,
            color: '#E1E1E1',
            opacity: 0.7
        };
    }

    // return public functions
    return {
        onEachFeature: onEachFeature,
        zipcodesStyle: zipcodesStyle
    };

})();