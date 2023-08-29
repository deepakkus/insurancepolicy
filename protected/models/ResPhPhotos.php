<?php

/**
 * This is the model class for table "res_ph_photos".
 *
 * The followings are the available columns in table 'res_ph_photos':
 * @property integer $id
 * @property integer $visit_id
 * @property integer $file_id
 * @property string $notes
 * @property integer $order
 * @property integer $publish
 *
 * The followings are the available model relations:
 * @property File $file
 * @property ResPhVisit $phVisit
 */
class ResPhPhotos extends CActiveRecord
{
    public $photoName;
    public $imageNo;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_ph_photos';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('visit_id', 'required'),
			array('visit_id, file_id, order, publish', 'numerical', 'integerOnly' => true),
            array('notes', 'length', 'max'=>300),
			array('id, visit_id, file_id, notes, order, publish', 'safe', 'on' => 'search')
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'file' => array(self::BELONGS_TO, 'File', 'file_id'),
			'phVisit' => array(self::BELONGS_TO, 'ResPhVisit', 'visit_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'visit_id' => 'Visit',
			'file_id' => 'Photo',
            'photoName' => 'Photo Name',
            'notes' => 'Notes',
			'order' => 'Order',
			'publish' => 'Publish'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search($visitID)
	{
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('visit_id', $this->visit_id);
		$criteria->compare('file_id', $this->file_id);
        $criteria->compare('notes', $this->notes);
		$criteria->compare('order', $this->order);
		$criteria->compare('publish', $this->publish);

        $criteria->addCondition('visit_id = :visit_id');
        $criteria->params[':visit_id'] = $visitID;

		return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes'=>array('*')
            ),
			'criteria' => $criteria,
            'pagination' => false
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResPhPhotos the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeSave()
	{
        if(isset($this->imageNo))
           $this->saveAttachment("create_file_id[".$this->imageNo."]",'file_id');
        else
           $this->saveAttachment('create_file_id','file_id');

		if($this->isNewRecord)
		{
			$order = Yii::app()->db->createCommand()
			  ->select('max([order]) as max')
			  ->from('res_ph_photos')
			  ->where('visit_id=:visit_id', array(':visit_id'=>$this->visit_id))
			  ->queryScalar();
			$this->order = $order + 1;
		}

        return parent::beforeSave();
    }

    /**
     * Used by the before save to store attachments
     * @param string $propertyNameFile - variable from the form (read as $_FILES)
     * @param string $propertyName - model variable to assign as file_id (read as $_FILES)
     */
    private function saveAttachment($propertyNameFile, $propertyName)
    {
        $uploaded_file = CUploadedFile::getInstanceByName($propertyNameFile);

        if ($uploaded_file)
        {
            $image = new ImageResize($uploaded_file->getTempName());

            $image_full_temp = dirname($uploaded_file->getTempName()) . DIRECTORY_SEPARATOR . $uploaded_file->getName();
            $image_thumb_temp = dirname($uploaded_file->getTempName()) . DIRECTORY_SEPARATOR . 'thumb_' . $uploaded_file->getName();

            if ($image->getSourceWidth() > 900)
                $image->resizeToWidth(900);

            $image->save($image_full_temp, IMAGETYPE_JPEG);

            $image->resizeToWidth(200);
            $image->crop(200, 200);
            $image->save($image_thumb_temp, IMAGETYPE_JPEG);

            // Assign images to new objects for save
            $image_full = new stdClass();
            $image_full->tempName = $image_full_temp;
            $image_full->name = $uploaded_file->getName();
            $image_full->type = $uploaded_file->getType();

            $image_thumb = new stdClass();
            $image_thumb->tempName = $image_thumb_temp;
            $image_thumb->name = 'thumb_' . $uploaded_file->getName();
            $image_thumb->type = $uploaded_file->getType();

            if(isset($this->$propertyName)) //if there already exists a file, replace it
            {
                File::model()->saveImageWithThumbnail($image_full, $image_thumb, $this->$propertyName);
            }
            else //new file
            {
                $this->$propertyName = File::model()->saveImageWithThumbnail($image_full, $image_thumb);
            }

            // Clean up temp files
            if (isset($image_full_temp)) { unlink($image_full_temp); }
            if (isset($image_thumb_temp)) { unlink($image_thumb_temp); }
        }

    }
}
