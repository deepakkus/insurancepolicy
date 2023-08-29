<?php

class RiskReportForm extends CFormModel
{
    public $risk_v;
    public $risk_whp;
    public $risk_wds;
    public $geojson;
    public $address;
    public $lat;
    public $lon;
    public $state;
    
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
            array('risk_v, risk_whp, risk_wds, geojson, address, lat, lon', 'required'),
            array('risk_v, risk_whp, risk_wds, geojson, address, lat, lon, state', 'safe')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'risk_v' => 'Risk V',
			'risk_whp' => 'Risk WHP',
			'risk_wds' => 'Risk WDS',
            'geojson' => 'GeoJSON',
            'address' => 'Address',
            'lat' => 'Lat',
            'lon' => 'Lon',
            'state' => 'State'
		);
	}
}
