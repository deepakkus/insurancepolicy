L.Control.LegendToggle = L.Control.extend({
    options: {
        position: 'bottomright',
    },
    initialize: function(legendControl, options) {
        L.Util.setOptions(this, options);
        this._legendControl = legendControl;
    },
    onAdd: function(map) {
        this._map = map;
        var controlDiv = L.DomUtil.create('div', 'leaflet-legend-toolbar leaflet-bar');
        L.DomEvent.disableClickPropagation(controlDiv);
        L.DomEvent.on(controlDiv, 'click', this._click, this);
        var controlUI = L.DomUtil.create('a', 'leaflet-legend', controlDiv);
        controlUI.title = 'Toggle Legend';
        controlUI.href = '#';
        controlUI.style.border = '1px solid #404040;';
        controlUI.style.background = "url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAZCAYAAADe1WXtAAAABGdBTUEAALGPC/xhBQAAAAlwSFlzAAAOwgAADsIBFShKgAAAAt1JREFUSEu9ld1LU3Ecxg0Kil7ALnqDWhcFXRQkdlFWKEkRWFTYG1R3Rl1F0FVXFaUrajp1nu24TRsoZFALz9nL2dmmpoY2X7a5c87+ge692kWop+/TfkxtP49e9cADP77f5/NsY7/tVPxXvftm7PSEtftiTAuIqj7bkzDme5LGPM5emmGHDIuvL6F/tFKMGVIgmf/tCs+ZT3vGzQftib/GGTPsxLghOQZ+7GbY2nKF0+e8qvZLiOaoYMy8+EriGjtkkO2QMucZXq4PwZ8H/XE9JURy5h2Hyi1baWS6KOtTtSnn56lDrGa1PIr+xhfXF++1xbklPCMLxq3ozaxmWfZg6gR9GYUn/lEubGUwYNHB6opqk7MuT0wzbztiXNDKYMA6qYPVFSWq2liblDGvtIS5oJXBtBLbHdPGWV1Rvcm8Zv86w4U2YrB0j3VWV5QYy313DGbMhuYQF7IyGMdg2uxWtFFWV1R7KONzRzXz1nuFC1oZjJvubHso62N1RdmDE9W9SWOxSRjmglZuEoZMsPbgdDWrW5ZHyWXwZfFAK4PxKFqG1ayWGM1ep9904bFnhAvz/IiyYNzh7A1Ws1p2OVvpjevxTvrDuNoS4ZasNDLI0s80AZbVlOvtl6nTH5P5pWeBcW7RSiODbMvA5BmGr62u8JxHiMwt3bS4CdghI1CWYdZyyZP7/HFj1q3kzGv2aFkhZtj5VD3dGkntZ9j6ag3lLtA1KbwcSJmXX8ulQpxffErhChWcg5l6Ft+QNtlstq1OKX3Xn9AXn/dNlEpxxgw7ZJAtImtrM3k7eRcZH+tApzzTFxjKLzx0j5hwYDi/0CFN92PHMsiCAVumLeQd5D1kG/ko+Vh1bUNdhzyjeFWdHh262SmnFcywYxlkwYBFR0l4lW3kveTD5CPk4+RT5LPkWkGezcI4sxl2yCALBiw6Su+YV3qSDBgPtbqqmvrGqppLjTizGXbI/FNasfkPlvo5BSfTbOcAAAAASUVORK5CYII=') no-repeat 2px 0px";
        return controlDiv;
    },
    _click: function(e) {
        L.DomEvent.preventDefault(e);
        if (document.contains(this._legendControl.getContainer())) {
            this._map.removeControl(this._legendControl);
        }
        else {
            this._map.addControl(this._legendControl);
        }
    },
});

L.control.legendToggle = function(legendControl, options) {
    return new L.Control.LegendToggle(legendControl, options);
};