<?php

/**
 * This is the model class for table "eng_crew_management".
 *
 * The followings are the available columns in table 'eng_crew_management':
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $work_phone
 * @property string $cell_phone
 * @property string $email
 * @property string $address
 * @property string $crew_type
 * @property integer $fire_officer
 * @property integer $photo_id
 * @property integer $alliance
 * @property integer $alliance_id
 * @property integer $user_id
 * @property integer $wdsfleet_active
 * @property integer $wdsfleet_download_kmz
 * @property integer $wdsfleet_download_policy
 */
class EngCrewManagement extends CActiveRecord
{
    public $alliance_partner;
    public $fullname;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'eng_crew_management';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('first_name, last_name, crew_type', 'required'),
			array('fire_officer, photo_id, alliance, alliance_id, user_id', 'numerical', 'integerOnly'=>true),
			array('first_name, last_name, work_phone, cell_phone, crew_type', 'length', 'max'=>20),
			array('email', 'length', 'max'=>50),
            array('email', 'email'),
            array('address', 'length', 'max'=>80),
            array('wdsfleet_active, wdsfleet_download_kmz, wdsfleet_download_policy','safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, first_name, last_name, work_phone, cell_phone, email, address, crew_type, fire_officer, photo_id, alliance, alliance_id, alliance_partner, wdsfleet_active, wdsfleet_download_kmz, wdsfleet_download_policy', 'safe', 'on'=>'search'),
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
			'file' => array(self::BELONGS_TO, 'File', 'photo_id'),
            'alliancepartner' => array(self::BELONGS_TO, 'Alliance', 'alliance_id'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'work_phone' => 'Work Phone',
			'cell_phone' => 'Cell Phone',
			'email' => 'Email',
			'address' => 'Address',
			'crew_type' => 'Crew Qualification',
            'fire_officer' => 'Fire Officer',
			'photo_id' => 'Photo',
			'alliance' => 'Alliance',
            'alliance_id' => 'Alliance Partners',
            'alliance_partner' => 'Alliance Partner',
            'user_id' => 'User',
            'fullname' => 'Full Name',
            'wdsfleet_active' => 'Fleet User',
            'wdsfleet_download_kmz' => 'Fleet Download KMZ',
            'wdsfleet_download_policy' => 'Fleet Download Policy'
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

        $criteria->with = array('alliancepartner');

		$criteria->compare('id',$this->id);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('work_phone',$this->work_phone,true);
		$criteria->compare('cell_phone',$this->cell_phone,true);
		$criteria->compare('t.email',$this->email,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('crew_type',$this->crew_type,true);
        $criteria->compare('fire_officer',$this->fire_officer);
		$criteria->compare('photo_id',$this->photo_id);
		$criteria->compare('alliance',$this->alliance);
        $criteria->compare('alliance_id',$this->alliance_id);
        $criteria->compare('alliancepartner.name',$this->alliance_partner,true);
        $criteria->compare('wdsfleet_active',$this->wdsfleet_active);
        $criteria->compare('wdsfleet_download_kmz',$this->wdsfleet_download_kmz);
        $criteria->compare('wdsfleet_download_policy',$this->wdsfleet_download_policy);
		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder'=>array('id' => CSort::SORT_DESC),
				'attributes' => array(
                    'alliance_partner' => array(
                        'asc' => 'alliancepartner.name ASC',
                        'desc' => 'alliancepartner.name DESC'
                    ),
                    '*',
				),
			),
			'criteria'=>$criteria,
            'pagination' => array('PageSize' => 10)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return EngCrewManagement the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeSave()
    {
        $this->saveAttachment('create_photo_id','photo_id');

        if (!$this->alliance_id)
        {
            $this->alliance_id = null;
            $this->alliance = false;
        }

        if (!$this->alliance)
            $this->alliance_id = null;

        return parent::beforeSave();
    }

    protected function afterFind()
    {
        $this->fullname = $this->first_name . ' ' . $this->last_name;

        if ($this->alliance)
            $this->alliance_partner = $this->alliancepartner->name;

        return parent::afterFind();
    }

    //-----------------------------------------------------Virtual Attributes -------------------------------------------------------------
    #region Virtual Attributes

    /**
     * Virtual attribute for Crew Member Photo - retreives the file name
     */
    public function getPhotoName()
    {
        return $this->photo_id && $this->file ? $this->file->name : 'No File';
    }

    #endregion

    //-----------------------------------------------------General Functions -------------------------------------------------------------
    #region General Functions

    public function getAlliancePartners()
    {
        return CHtml::listData(Alliance::model()->findAll(),'id','name');
    }

    /**
     * Desc: Gets an array of engine schedules a crew member was assigned to on a given date
     * @param int $crewID
     * @param string $date
     * @return EngScheduling[] $schedules
     */
    public static function getAssignments($crewID, $date)
    {
        // Find engine id that crew member was on for the given day
        $sql = '
            DECLARE @date DATE = :date;

            SELECT TOP 1 s.engine_id FROM eng_scheduling s
            INNER JOIN eng_scheduling_employee e ON e.engine_scheduling_id = s.id
            WHERE e.crew_id = :crew_id AND CONVERT(DATE, e.start_date) <= @date and CONVERT(DATE, e.end_date) >= @date
        ';

        $engineID = Yii::app()->db->createCommand($sql)
            ->bindParam(':crew_id', $crewID, PDO::PARAM_INT)
            ->bindParam(':date', $date, PDO::PARAM_STR)
            ->queryScalar();

        if ($engineID)
        {
            // Use that engine ID to select all assignments
            $sql = '
                DECLARE @date DATE = :date;

                SELECT * from eng_scheduling
                WHERE engine_id = :engine_id AND CONVERT(DATE, start_date) <= @date AND CONVERT(DATE, end_date) >= @date
                ORDER BY id DESC;
            ';

            $schedules = EngScheduling::model()->findAllBySql($sql, array(':engine_id'=>$engineID, 'date'=>$date));
            return $schedules;
        }
        else
            return null;
    }

    /**
     * Summary of getUsers
     * @return array
     */
    public function getUsers()
    {
        $users = User::model()->findAll(array(
		'select' => 'id, name, type',
		'condition' => 'client_id IS NULL AND active = 1 AND type LIKE \'%Engine User%\'',
		'order' => 'name ASC'
	    ));
	    $users = array_filter($users, function($user) { return in_array('Engine User', $user->getTypes()); });
        return CHtml::listData($users, 'id','name');
    }


    public function getCrewTypes()
    {
        return array(
            'FFT1' => 'FFT1',
            'FFT2' => 'FFT2',
            'ENGBt' => 'ENGBt',
            'ENGB' => 'ENGB',
            'STLD' => 'STLD'
        );
    }

    public function getCrewMembers()
    {
        return CHtml::listData(EngCrewManagement::model()->findAll(array(
            'order' => 'alliance_id, first_name ASC'
        )), 'id', function($data) { return $data->fullname . (isset($data->alliance_partner) ? " - $data->alliance_partner" : ''); });
    }

    /**
     * Used by the before save to store attachments
     * @param string $propertyNameFile - variable from the form (read as $_FILES)
     * @param string $propertyName - model variable to assign as file_id (read as $_FILES)
     */
    private function saveAttachment($propertyNameFile, $propertyName)
    {
        //geoJson File Upload
        $uploaded_file = CUploadedFile::getInstanceByName($propertyNameFile);

        if ($uploaded_file)
        {
            $image = new ImageResize($uploaded_file->getTempName());

            if ($image->getSourceWidth() > 100)
            {
                // Save Thumbnail Image
                $image->resizeToWidth(100);
                $image->crop(100, 100);
                $image_thumb_temp = dirname($uploaded_file->getTempName()) . DIRECTORY_SEPARATOR . 'thumb_' . $uploaded_file->getName();
                $image->save($image_thumb_temp, IMAGETYPE_JPEG);

                $image_thumb = new stdClass();
                $image_thumb->tempName = $image_thumb_temp;
                $image_thumb->name = 'thumb_' . $uploaded_file->getName();
                $image_thumb->type = $uploaded_file->getType();
            }
            else
            {
                $image_thumb = new stdClass();
                $image_thumb->tempName = $uploaded_file->getTempName();
                $image_thumb->name = 'thumb_' . $uploaded_file->getName();
                $image_thumb->type = $uploaded_file->getType();
            }

            if(isset($this->$propertyName)) { //if there already exists a file, replace it
                File::model()->saveFile($image_thumb, $this->$propertyName);
            }

            else { //new file
                $this->$propertyName = File::model()->saveFile($image_thumb);
            }

            // Clean up temp files
            if (isset($image_thumb_temp)) { unlink($image_thumb_temp); }
        }

    }
    
    #endregion
}
