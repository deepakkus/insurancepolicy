<?php

class RefreshRiskForm extends CFormModel
{
    public $id;
    
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
            array('id', 'required')
		);
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
            'id' => 'Risk Type'
		);
	}

    public function getRiskTypesDropdown()
    {
        return CHtml::listData(RiskScoreType::model()->findAll(), 'id', 'type');
    }
}
