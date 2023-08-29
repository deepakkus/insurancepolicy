<?php

/**
 * AttachItemForm class.
 * AttachItemForm is the data structure for authorization item form data.
 * It is used for attaching CAuthManager authorization items.
 */
class AttachItemForm extends CFormModel
{
	public $itemname;
    public $children;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('itemname, children', 'required'),
		);
	}

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'children' => 'Children',
			'itemname' => 'Item Name'
		);
	}
}