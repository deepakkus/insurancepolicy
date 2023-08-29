<?php

class Assets
{
    static function registerMapboxPackage()
    {
        $mapbox = array(
            'baseUrl' => '/protected/packages/mapbox',
            'css' => array(
                'css/mapbox.css',
                'css/leaflet.fullscreen.css',
                'css/leaflet.label.css'
            ),
            'js' => array(
                'js/mapbox.js',
                'js/Leaflet.fullscreen.min.js',
                'js/leaflet.label.js',
                'js/esri-leaflet.js',
            ),
            'depends' => array('jquery')
        );

        Yii::app()->clientScript->addPackage('mapbox', $mapbox)->registerPackage('mapbox');
    }

    static function registerMapboxMarkerCluster()
    {
        $mapboxmarkercluster = array(
            'baseUrl' => '/protected/packages/mapbox-marker-cluster',
            'css' => array(
                'css/MarkerCluster.css'
            ),
            'js' => array(
                'js/leaflet.markercluster.js',
            ),
            'depends' => array('mapbox')
        );

        Yii::app()->clientScript->addPackage('mapboxmarkercluster', $mapboxmarkercluster)->registerPackage('mapboxmarkercluster');
    }

    static function registerMapboxLeafletOmnivore()
    {
        $mapboxleafletomnivore = array(
            'baseUrl' => '/protected/packages/mapbox-leaflet-omnivore',
            'css' => array(),
            'js' => array(
                'js/leaflet-omnivore.min.js'
            ),
            'depends' => array('mapbox')
        );

        Yii::app()->clientScript->addPackage('mapboxleafletomnivore', $mapboxleafletomnivore)->registerPackage('mapboxleafletomnivore');
    }

    static function registerMapboxLeafletDraw()
    {
        $mapboxleafletdraw = array(
            'baseUrl' => '/protected/packages/mapbox-leaflet-draw',
            'css' => array(
                'css/leaflet.draw.css'
            ),
            'js' => array(
                'js/leaflet.draw.js'
            ),
            'depends' => array('mapbox')
        );

        Yii::app()->clientScript->addPackage('mapboxleafletdraw', $mapboxleafletdraw)->registerPackage('mapboxleafletdraw');
    }

    static function registerTurfJs()
    {
        $turfjs = array(
            'baseUrl' => '/protected/packages/turf-js',
            'css' => array(),
            'js' => array(
                'js/turf.min.js'
            ),
            'depends' => array('mapbox')
        );

        Yii::app()->clientScript->addPackage('turfjs', $turfjs)->registerPackage('turfjs');
    }

    static function registerRiskControl()
    {
        $wdsriskcontrol = array(
            'baseUrl' => '/protected/packages/wds-risk-control',
            'css' => array(
                'css/Leaflet.RiskControl.css'
            ),
            'js' => array(
                'js/Leaflet.RiskControl.js',
            ),
            'depends' => array('mapbox')
        );

        Yii::app()->clientScript->addPackage('wdsriskcontrol', $wdsriskcontrol)->registerPackage('wdsriskcontrol');
    }

    static function registerOpacityControl()
    {
        $wdsopacitycontrol = array(
            'baseUrl' => '/protected/packages/wds-opacity-control',
            'css' => array(),
            'js' => array(
                'js/Leaflet.Opacity.js',
            ),
            'depends' => array('mapbox')
        );

        Yii::app()->clientScript->addPackage('wdsopacitycontrol', $wdsopacitycontrol)->registerPackage('wdsopacitycontrol');
    }

    static function registerLegendToggleControl()
    {
        $wdslegendtogglecontrol = array(
            'baseUrl' => '/protected/packages/wds-legend-control',
            'css' => array(),
            'js' => array(
                'js/Leaflet.LegendToggle.js',
            ),
            'depends' => array('mapbox')
        );

        Yii::app()->clientScript->addPackage('wdslegendtogglecontrol', $wdslegendtogglecontrol)->registerPackage('wdslegendtogglecontrol');
    }

    static function registerGeocoderFormControl()
    {
        $wdsgeocoderformcontrol = array(
            'baseUrl' => '/protected/packages/wds-geocoder-form-control',
            'css' => array(),
            'js' => array(
                'js/Leaflet.GeocoderForm.js',
            ),
            'depends' => array('mapbox')
        );

        Yii::app()->clientScript->addPackage('wdsgeocoderformcontrol', $wdsgeocoderformcontrol)->registerPackage('wdsgeocoderformcontrol');
    }

    static function registerTriageControl()
    {
        $wdstriagecontrol = array(
            'baseUrl' => '/protected/packages/wds-triage-control',
            'css' => array(
                'css/Leaflet.TriageControl.css'
            ),
            'js' => array(
                'js/Leaflet.TriageControl.js',
            ),
            'depends' => array('mapbox', 'mapboxleafletdraw')
        );

        self::registerMapboxLeafletDraw();
        Yii::app()->clientScript->addPackage('wdstriagecontrol', $wdstriagecontrol)->registerPackage('wdstriagecontrol');
    }

    static function registerEvacControl()
    {
        $wdsEvacControl = array(
            'baseUrl' => '/protected/packages/wds-evac-control',
            'css' => array(
                'css/Leaflet.EvacControl.css'
            ),
            'js' => array(
                'js/Leaflet.EvacControl.js',
            ),
            'depends' => array('mapbox', 'mapboxleafletdraw')
        );

        self::registerMapboxLeafletDraw();
        Yii::app()->clientScript->addPackage('wdsevaccontrol', $wdsEvacControl)->registerPackage('wdsevaccontrol');
    }

    static function registerFullCalendarPackage()
    {
        $fullcalendar = array(
            'baseUrl'=> '/protected/packages/fullcalendar',
            'css'=>array(
                'css/fullcalendar.min.css',
                'css/jquery-ui.min.css'
            ),
            'js'=>array(
                'js/moment.min.js',
                'js/fullcalendar.min.js',
                'js/jquery-ui.custom.min.js'
            ),
            'depends' => array('jquery')
        );

        Yii::app()->clientScript->addPackage('fullcalendar', $fullcalendar)->registerPackage('fullcalendar');
    }


    static function registerChartsJSPackage()
    {
        $mapbox = array(
           'baseUrl'=>'/protected/packages/chartsjs',
           'js'=>array('js/Chart.min.js')
        );

        Yii::app()->clientScript->addPackage('chartsjs', $mapbox)->registerPackage('chartsjs');
    }

    static function registerD3PackageV3()
    {
        $d3 = array(
            'baseUrl' => '/protected/packages/d3',
            'css' => array(),
            'js' => array(
                'js/d3.v3.min.js',
                'js/tooltip.js'
            )
        );

        Yii::app()->clientScript->addPackage('d3v3', $d3)->registerPackage('d3v3');
    }

    static function registerD3Package()
    {
        $d3 = array(
            'baseUrl' => '/protected/packages/d3',
            'css' => array(),
            'js' => array(
                'js/d3.v4.min.js',
                'js/tooltip.js'
            )
        );

        Yii::app()->clientScript->addPackage('d3', $d3)->registerPackage('d3');
    }

    static function registerProj4Php()
    {
        include_once(Yii::app()->getBasePath() . '/packages/proj4php/proj4php.php');
    }

    static function registerGeoPHP()
    {
        include_once(Yii::app()->getBasePath() . '/packages/geoPHP/geoPHP.php');
    }

    /**
     * Jquery Plugin from the following link
     *  https://github.com/evoluteur/colorpicker
     */
    static function registerColorPicker()
    {
        $colorpicker = array(
            'baseUrl' => '/protected/packages/colorpicker',
            'css' => array(
                'css/jquery.colorpicker.min.css'
            ),
            'js' => array(
                'js/jquery.colorpicker.min.js',
            ),
            'depends' => array('jquery','jquery.ui')
        );

        Yii::app()->clientScript->registerScriptFile(Yii::app()->clientScript->coreScriptUrl . '/jui/js/jquery-ui.min.js');
        Yii::app()->clientScript->registerCssFile(Yii::app()->clientScript->coreScriptUrl . '/jui/css/base/jquery-ui.css');
        Yii::app()->clientScript->addPackage('colorpicker', $colorpicker)->registerPackage('colorpicker');
    }

    static function registerMapboxGLPackage()
    {
        $mapboxGl = array(
            'baseUrl' => '/protected/packages/mapbox-gl',
            'css' => array(
                'css/mapbox-gl.css',
            ),
            'js' => array(
                'js/mapbox-gl.js',
            ),
            'depends' => array('jquery')
        );

        Yii::app()->clientScript->addPackage('mapbox-gl', $mapboxGl)->registerPackage('mapbox-gl');
    }

    static function registerMapboxGLGeocoderPackage()
    {
        $mapboxGlGeocoder = array(
            'baseUrl' => '/protected/packages/mapbox-gl-geocoder',
            'css' => array(
                'css/mapbox-gl-geocoder.css'
            ),
            'js' => array(
                'js/mapbox-gl-geocoder.js',
            ),
            'depends' => array('mapbox-gl')
        );

        Yii::app()->clientScript->addPackage('mapbox-gl-geocoder', $mapboxGlGeocoder)->registerPackage('mapbox-gl-geocoder');
    }
}

?>
