<?php

class AuthItemForm extends CFormModel
{
    public $platform;
    public $oldPlatform;
    public $name;
    public $oldName;
    public $type;
    public $description;
    public $bizRule;
	public $data;

    public $isNewRecord;

    const PLATFORM_WDS = 'wds';
    const PLATFORM_DASH = 'dash';
    const PLATFORM_ENGINE = 'engine';
    const PLATFORM_OA2 = 'oa2';

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('platform, name, type', 'required'),
            array('name', 'isUniqueName'),
			array('type', 'numerical', 'integerOnly' => true),
            array('type', 'in', 'range' => array(
                CAuthItem::TYPE_ROLE,
                CAuthItem::TYPE_TASK,
                CAuthItem::TYPE_OPERATION), 'allowEmpty' => false),
			array('bizRule, data', 'default', 'value' => NULL),
			array('oldName, oldPlatform, description', 'safe')
		);
	}

    public function isUniqueName($attribute)
    {
        // Don't bother validating if updating and name hasn't changed
        if ($this->scenario === 'update' && $this->$attribute === $this->oldName)
            return;

        $itemTableName = Yii::app()->getModule('wdsauth')->authManager->itemTable;

        $existingName = Yii::app()->db->createCommand()
            ->select('name')
            ->from($itemTableName)
            ->where('name = :name', array(':name' => sprintf('%s.%s', $this->platform, $this->$attribute)))
            ->queryColumn();

        if ($existingName)
            $this->addError($attribute, sprintf('%s "%s" has already been taken!', $this->getAttributeLabel($attribute), $this->$attribute));
    }

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
            'platform' => 'Platform',
            'oldPlatform' => 'Old Platform',
            'name' => 'Name',
            'oldName' => 'Old Name',
            'type' => 'Type',
            'description' => 'Description',
            'bizRule' => 'Bisiness Rule',
            'data' => 'Data'
		);
	}

    /**
     * Summary of createCAuthItem
     * @param IAuthManager $auth authorization manager
     * @return CAuthItem
     */
    public function createCAuthItem($auth)
    {
        return new CAuthItem($auth, $this->platform . '.' . $this->name, $this->type, $this->description, $this->bizRule, $this->data);
    }

    /**
     * Loading model attributes from authItem
     * @param CAuthItem $authItem 
     */
    public function loadModelFromAuthItem($authItem)
    {
        $authItemName = explode('.', $authItem->getName());

        $this->platform = $authItemName[0];
        $this->oldPlatform = $authItemName[0];
        $this->name = $authItemName[1];
        $this->oldName = $authItemName[1];
        $this->type = $authItem->getType();
        $this->description = $authItem->getDescription();
        $this->bizRule = $authItem->getBizRule();
        $this->data = $authItem->getData();
    }

    public function getPlatforms()
    {
        return array(
            self::PLATFORM_WDS => self::PLATFORM_WDS,
            self::PLATFORM_DASH => self::PLATFORM_DASH,
            self::PLATFORM_ENGINE => self::PLATFORM_ENGINE,
            self::PLATFORM_OA2 => self::PLATFORM_OA2
        );
    }
}
