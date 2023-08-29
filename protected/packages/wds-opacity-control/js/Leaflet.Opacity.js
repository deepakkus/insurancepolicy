L.Control.OpacityToggle = L.Control.extend({
    options: {
        position: 'topleft'
    },
    initialize: function(baselayer, options) {
        L.Util.setOptions(this, options);
        this._baselayer = baselayer;
    },
    onAdd: function(map) {
        this._map = map;
        var container = L.DomUtil.create('div', 'leaflet-addresses-toolbar leaflet-bar');
        var handleContainer = L.DomUtil.create('div', 'ui-opacity', container);
        var handle = L.DomUtil.create('a', 'handle', handleContainer);

        var plus = L.DomUtil.create('span', 'ui-plus', handleContainer);
        var minus = L.DomUtil.create('span', 'ui-minus', handleContainer);

        handleContainer.style.background = '#FFFFFF';
        handleContainer.style.height = '200px'
        handleContainer.style.width = '28px';
        handleContainer.style.border = '1px solid rgba(0,0,0,0.4)';
        handleContainer.style.borderRadius = '3px';
        handleContainer.style.zIndex = '1000';
        handleContainer.style.textAlign = 'center';

        handle.style.position = 'absolute';
        handle.style.background = '#404040';
        handle.style.width = '26px';
        handle.style.height = '10px';
        handle.style.borderRadius = '1px';
        handle.style.cursor = 'ns-resize';
        handle.style.zIndex = '10';

        plus.style.position = 'relative';
        plus.innerHTML = '+';
        plus.style.cursor = 'default';
        plus.style.fontSize = 'x-large';
        plus.style.top = '8px';
        plus.style.display = 'block';
        plus.style.zIndex = '5';

        minus.style.position = 'relative';
        minus.innerHTML = '&#8722;';
        minus.style.cursor = 'default';
        minus.style.fontSize = 'x-large';
        minus.style.top = '150px';
        plus.style.display = 'block';
        minus.style.zIndex = '5';

        L.DomEvent.disableClickPropagation(container);

        var layer = this._baselayer;
        var start = false;
        var startTop;

        document.onmousemove = function(e) {
            if (!start) { return; }
            handle.style.top = Math.max(-5, Math.min(195, startTop + parseInt(e.clientY, 10) - start)) + 'px';
            layer.setOpacity(1 - (handle.offsetTop / 200));
        };

        handle.onmousedown = function(e) {
            // Record initial positions.
            start = parseInt(e.clientY, 10);
            startTop = handle.offsetTop - 5;
            return false;
        };

        document.onmouseup = function(e) {
            start = null;
        };

        return container;
    }
});

L.control.opacityToggle = function(baselayer, options) {
    return new L.Control.OpacityToggle(baselayer, options);
};