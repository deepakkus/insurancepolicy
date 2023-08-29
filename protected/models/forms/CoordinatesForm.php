<?php

class CoordinatesForm extends CFormModel
{
    public $lat;
    public $lon;

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		return array(
            array('lat, lon', 'required'),
            array('lat', 'match', 'pattern' => '/\d{2,3}\.{1}\d+/', 'message' => '{attribute} must entered in the correct format.<br />Ex: 40.12421'),
            array('lon', 'match', 'pattern' => '/-\d{2,3}\.{1}\d+/', 'message' => '{attribute} must entered in the correct format.<br />Ex: -122.4512'),
            array('lat', 'isValidLat'),
            array('lon', 'isValidLon')
		);
	}

    /**
     * Checking if latitude is a valid value.
     * @param string $attribute
     */
    public function isValidLat($attribute)
    {
        if (is_numeric($this->lat))
        {
            $latitude = (double)$this->lat;

            if ($latitude < -90.0 || $latitude > 90.0)
            {
                $this->addError($attribute, 'Your "' . $this->getAttributeLabel($attribute) . '" is out of range.  Must be between -90 and 90!');
            }
        }
    }

    /**
     * Checking if longitude is a valid value.
     * @param string $attribute
     */
    public function isValidLon($attribute)
    {
        if (is_numeric($this->lon))
        {
            $longitude = (double)$this->lon;

            if ($longitude < -180.0 || $longitude > 180.0)
            {
                $this->addError($attribute, 'Your "' . $this->getAttributeLabel($attribute) . '" is out of range.  Must be between -180 and 180!');
            }
        }
    }

	/**
     * @return array
     */
	public function attributeLabels()
	{
		return array(
            'lat' => 'Lat',
            'lon' => 'Long'
		);
	}
}
