Array.prototype.sum = function() {
    var total = 0;
    for (var i = 0, n = this.length; i < n; ++i) {
        total += this[i];
    }
    return total;
}

Array.prototype.max = function() {
    return Math.max.apply(null, this);
};

Array.prototype.min = function() {
    return Math.min.apply(null, this);
};

Date.prototype.monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
];

Date.prototype.getMonthName = function() {
    return this.monthNames[this.getMonth()];
};

var AnalyticsMap = (function() {

    function getStyle(feature) {
        return {
            weight: 2,
            opacity: .9,
            color: '#555555',
            fillOpacity: 0.7,
            fillColor: getColor(feature.properties.daycount)
        };
    }

    // get color depending on population density value
    function getColor(d) {
        for (var i = maxdaycountscale.length; i--;) {
            if (!d) return '#ffffe5';
            if (d >= maxdaycountscale[i]) {
                return colors[i];
            }
        }
    }

    function onEachFeature(feature, layer) {
        layer.on({
            mousemove: mousemove,
            mouseout: mouseout
        });
    }

    function mousemove(e) {
        var layer = e.target;
        var popupContent = '<div class="marker-title">' + layer.feature.properties.name + '</div>' +
            'Day count: ' + (layer.feature.properties.daycount ? layer.feature.properties.daycount : 0) + '<br />' +
            'Engine Count: ' + (layer.feature.properties.enginecount ? layer.feature.properties.enginecount : 0);
        popupContent += '<div class="marginTop10"><b>Breakdown</b></div>';
        popupContent += '<table class="table table-condensed"><tr><th>Assignment</th><th>Daycount</th><th>EngineCount</th></tr>';
        if (layer.feature.properties.breakdown) {
            for (var i = 0; i < layer.feature.properties.breakdown.length; i++) {
                popupContent += '<tr><td>' + layer.feature.properties.breakdown[i].assignment + '</td><td>' + layer.feature.properties.breakdown[i].daycount + '</td><td>' + layer.feature.properties.breakdown[i].enginecount + '</td></tr>';
            }
        }
        popupContent += '</table>';
        popup.setLatLng(e.latlng);
        popup.setContent(popupContent);

        if (!popup._map) popup.openOn(map);
        window.clearTimeout(closeTooltip);

        // highlight feature
        layer.setStyle({
            weight: 4,
            opacity: 0.3,
            fillOpacity: 0.9
        });

        if (!L.Browser.ie && !L.Browser.opera) {
            layer.bringToFront();
        }
    }

    function mouseout(e) {
        statesLayer.resetStyle(e.target);
        closeTooltip = window.setTimeout(function() {
            map.closePopup();
        }, 100);
    }

    function getLegendHTML() {
        var labels = [],
        from, to;
        for (var i = 0; i < maxdaycountscale.length; i++) {
            from = maxdaycountscale[i];
            to = maxdaycountscale[i + 1];
            labels.push('<li><span class="swatch" style="background:' + getColor(from) + '"></span> ' + Math.ceil(from) + (Math.ceil(to) ? '&ndash;' + Math.ceil(to) : '+')) + '</li>';
        }
        return '<span><b>Engine Days</b> in each state</span><ul style="list-style: none;">' + labels.join('') + '</ul>';
    }

    // return public functions
    return {
        onEachFeature: onEachFeature,
        getStyle: getStyle,
        getLegendHTML: getLegendHTML
    };

})();