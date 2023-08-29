<?php

/**
 * Subclass of CDbAuthManager
 */
class DbAuthManager extends CDbAuthManager
{
	public $itemTable = 'auth_item';
	public $itemChildTable = 'auth_item_child';
	public $assignmentTable = 'auth_assignment';
    public $connectionID = 'db';

	public function init()
	{
		return parent::init();
	}

    /**
     * Return string representation of authItem type.
     * @param integer $type CAuthItem constant
     * @return string
     */
    public function getAuthItemTypeName($type)
    {
        switch ($type) {
            case CAuthItem::TYPE_ROLE: return 'role';
            case CAuthItem::TYPE_TASK: return 'task';
            case CAuthItem::TYPE_OPERATION: return 'operation';
        }
    }
}
