<?php

class ViewAuthItemForm extends CFormModel
{
    public $role;
    public $task;
    public $operation;

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
        $rules = array(
            array('role, task, operation', 'safe')
		);

        if (isset($_POST['ajax']))
        {
            if ($_POST['ajax'] === 'view-role-form')
                $rules[] = array('role', 'required');
            else if ($_POST['ajax'] === 'view-task-form')
                $rules[] = array('task', 'required');
            else if ($_POST['ajax'] === 'view-operation-form')
                $rules[] = array('operation', 'required');
        }

		return $rules;
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
            'role' => 'Role',
            'task' => 'Task',
            'operation' => 'Operation'
		);
	}
}
