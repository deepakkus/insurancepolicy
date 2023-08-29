<?php

/**
 * This is the model class for table "file".
 *
 * The followings are the available columns in table 'file':
 * @property integer $id
 * @property string $name
 * @property file $data
 * @property string $type
 * @property file $data_thumb
 */
class File extends CActiveRecord
{
	/**
     * @return string the associated database table name
     */
	public function tableName()
	{
		return 'file';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('name, type', 'length', 'max'=>200),
            array('data, data_thumb', 'file',
                'types' => 'jpg, gif, png, jpeg, bmp, pdf',
                'maxSize'=>1024 * 1024 * 50,
                'tooLarge'=>'The file was larger than 50MB. Please upload a smaller file',
                'allowEmpty'=>true,
            ),
			// The following rule is used by search().
			array('id, name, type', 'safe', 'on'=>'search'),
		);
	}

	/**
     * @return array relational rules.
     */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(

		);
	}

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
            'type' => 'Type',
		);
	}

	/**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('type',$this->type,true);
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Client the static model class
     */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Special Function to create/update a File Model.
     * Can't use the normal save() AR method because it messes up
     * the varbinary(max) field (it quotes it), so have to use query builder instead.
     * @param $uploaded_file - This should be a file from this CUploadedFile function, for example:
     * $uploaded_file = CUploadedFile::getInstanceByName('example_image');
     * @param $id - leave out or null for new model. Pass in the id to update an existing one.
     * @return id of saved file on success, false on error.
     */
    public function saveFile($uploaded_file, $id = null)
    {
        if(isset($id)) //existing file
        {
            try
            {
                $fp = fopen($uploaded_file->tempName, 'rb');
                $content = fread($fp, filesize($uploaded_file->tempName));
                $content = '0x'.unpack('H*hex', $content)['hex'];
                fclose($fp);
                $command = Yii::app()->db->createCommand("UPDATE [file] SET [name] = '".addslashes($uploaded_file->name)."', [type] = '".addslashes($uploaded_file->type)."', [data] = ".$content." WHERE id = $id");
                $command->execute();
                return $id;
            }
            catch(Exception $e)
            {
                return false;
            }
        }
        else //new file
        {
            try
            {
                $fp = fopen($uploaded_file->tempName, 'rb');
                $content = fread($fp, filesize($uploaded_file->tempName));
                $content = '0x'.unpack('H*hex', $content)['hex'];
                fclose($fp);
                $command = Yii::app()->db->createCommand("INSERT INTO [file] ([name], [type], [data]) VALUES ('".addslashes($uploaded_file->name)."', '".addslashes($uploaded_file->type)."', $content)");
                $command->execute();
                return Yii::app()->db->getLastInsertID();
            }
            catch(Exception $e)
            {
                return false;
            }
        }
    }

    public function saveImageWithThumbnail($uploaded_file, $uploaded_file_thumb, $id = null)
    {
        if(isset($id)) //existing file
        {
            try
            {
                $fp = fopen($uploaded_file->tempName, 'rb');
                $content = fread($fp, filesize($uploaded_file->tempName));
                $content_full = '0x'.unpack('H*hex', $content)['hex'];
                fclose($fp);

                $fp = fopen($uploaded_file_thumb->tempName, 'rb');
                $content = fread($fp, filesize($uploaded_file_thumb->tempName));
                $content_thumb = '0x'.unpack('H*hex', $content)['hex'];
                fclose($fp);

                $command = Yii::app()->db->createCommand("UPDATE [file] SET [name] = '" . addslashes($uploaded_file->name) .
                                                                        "', [type] = '" . addslashes($uploaded_file->type) .
                                                                        "', [data] = $content_full, [data_thumb] = $content_thumb WHERE id = $id");
                $command->execute();
                return $id;
            }
            catch(Exception $e)
            {
                return false;
            }
        }
        else //new file
        {
            try
            {
                $fp = fopen($uploaded_file->tempName, 'rb');
                $content = fread($fp, filesize($uploaded_file->tempName));
                $content_full = '0x'.unpack('H*hex', $content)['hex'];
                fclose($fp);

                $fp = fopen($uploaded_file_thumb->tempName, 'rb');
                $content = fread($fp, filesize($uploaded_file_thumb->tempName));
                $content_thumb = '0x'.unpack('H*hex', $content)['hex'];
                fclose($fp);

                $command = Yii::app()->db->createCommand("INSERT INTO [file] ([name], [type], [data], [data_thumb]) VALUES ('".addslashes($uploaded_file->name)."', '".addslashes($uploaded_file->type)."', $content_full, $content_thumb)");
                $command->execute();

                return Yii::app()->db->getLastInsertID();
            }
            catch(Exception $e)
            {
                return false;
            }
        }
    }
}