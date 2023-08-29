<?php

/**
 * This is the model class for table "members".
 *
 * The followings are the available columns in table 'members':
 * @property integer $mid
 * @property string $member_num
 * @property string $salutation
 * @property string $rank
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $home_phone
 * @property string $work_phone
 * @property string $cell_phone
 * @property string $email_1
 * @property string $email_2
 * @property string $mail_address_line_1
 * @property string $mail_address_line_2
 * @property string $mail_city
 * @property string $mail_county
 * @property string $mail_state
 * @property string $mail_zip
 * @property string $mail_zip_supp
 * @property string $spec_handling_code
 * @property string $signed_ola
 * @property string $spouse_member_num
 * @property string $spouse_first_name
 * @property string $spouse_middle_name
 * @property string $spouse_last_name
 * @property string $spouse_salutation
 * @property string $spouse_rank
 * @property string $client
 * @property int $client_id
 * @property string $fs_carrier_key
 * @property string $last_update
 * @property string $mem_fireshield_status
 * @property string $mem_fs_status_date
 * @property integer $is_tester
 * @property string $trial_expire_date
 * @property integer $status_override
 * @property int $type_id  //relates to properties_type table to differentiate between 'pif' and 'agent' types
 */

class Member extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Property the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'members';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('member_num, client_id', 'required'),
            array('member_num', 'validateUniquePolicyholderNumber', 'on' => 'insert'),
            array('member_num', 'validateUniquePolicyholderNumber', 'on' => 'adminCreateForm'),
			array('client', 'in', 'range'=>$this->getClients()),
			array('signed_ola', 'length', 'max'=>10),
			array('middle_name, spouse_middle_name, home_phone, work_phone, cell_phone, mail_state, mail_zip, mail_zip_supp, fs_carrier_key', 'length', 'max'=>25),
			array('last_update, mem_fs_status_date', 'length', 'max'=>30),
			array('member_num, spouse_member_num, first_name, salutation, rank, spouse_rank, spouse_salutation, spouse_first_name, last_name, spouse_last_name, email_1, email_2, mail_address_line_2, mail_city, mail_county, spec_handling_code', 'length', 'max'=>50),
            array('email_1, email_2', 'email', 'on' => 'adminCreateForm'),
			array('mail_address_line_1', 'length', 'max'=>100),
            array('fs_carrier_key', 'checkUniqueRegCode'),
			array('mem_fireshield_status', 'in', 'range'=>Property::model()->getProgramStatuses()),
            array('is_tester, status_override, type_id', 'numerical', 'integerOnly'=>true),
			array('mid, member_num, spouse_member_num, salutation, spouse_salutation, rank, spouse_rank, first_name, spouse_first_name, middle_name, spouse_middle_name, last_name, spouse_last_name, home_phone, work_phone, cell_phone, email_1, email_2, mail_address_line_1, mail_address_line_2, mail_city, mail_county, mail_state, mail_zip, mail_zip_supp, signed_ola, spec_handling_code, client, fs_carrier_key, is_tester, trial_expire_date, status_override, client_id, type_id', 'safe', 'on'=>'search'),
		);
	}

    /**
     * Validate unique combinations of "member_num" and client_id"
     * @param string $attribute
     */
    public function validateUniquePolicyholderNumber($attribute)
    {
        $sql = 'SELECT (
	        SELECT TOP 1 1 FROM [members] WHERE [member_num] = :member_num AND [client_id] = :client_id
        ) [member_num_exists]';

        $exists = Yii::app()->db->createCommand($sql)->queryScalar(array(
            ':member_num' => $this->member_num,
            ':client_id' => $this->client_id
        ));

        if ($exists)
        {
            $this->addError($attribute, 'Duplicate policyholder number and client combination!');
        }
    }
    /*
    * parameter string
    * check unique member number
    * return string
    */
    public function checkMember($memnumber)
    {
        $member_number = $memnumber;
        $sql = "SELECT (SELECT TOP 1 1 FROM [members] WHERE [member_num] = :member_num) [member_num_exists]";
        $exists = Yii::app()->db->createCommand($sql)->queryScalar(
        array(':member_num' => $member_number)
        );
        if($exists == 1)
        {
            $member_num = preg_replace("/[^0-9]/", '', $member_number);
            if($member_num)
            {
                $member_num++;
            }
            $member_number = $this->checkMember('MEM'.$member_num);
        }
        return $member_number;
    }
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'properties' => array(self::HAS_MANY, 'Property', 'member_mid'),
            'status_history' => array(self::HAS_MANY, 'StatusHistory', 'table_id'),
            'fs_report' => array(self::HAS_MANY, 'FSReport', 'id'),
            'client_model' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'type' => array(self::BELONGS_TO, 'PropertiesType', 'type_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'mid' => 'ID',
			'member_num' => 'PolicyHolder #',
			'spouse_member_num' => 'Spouse Member #',
			'salutation' => 'Salutation',
			'spouse_salutations' => 'Spouse Salutation',
			'rank' => 'Rank',
			'spouse_rank' => 'Spouse Rank',
			'first_name' => 'First Name',
			'spouse_first_name' => 'Spouse First Name',
			'middle_name' => 'Middle Name',
			'spouse_middle_name' => 'Spouse Middle Name',
			'last_name' => 'Last Name',
			'spouse_last_name' => 'Spouse Last Name',
			'home_phone' => 'Home Phone',
			'work_phone' => 'Work Phone',
			'cell_phone' => 'Cell Phone',
			'email_1' => 'Email',
			'email_2' => 'Secondary Email',
			'mail_address_line_1' => 'Mailing Address Line 1',
			'mail_address_line_2' => 'Mailing Address Line 2',
			'mail_city' => 'Mailing City',
			'mail_state' => 'Mailing State',
			'mail_county' => 'Mailing County',
			'mail_zip' => 'Mailing Zip',
			'mail_zip_supp' => 'Mailing Zip Supplement',
			'signed_ola' => 'Signed OLA',
			'spec_handling_code' => 'Special Handling Code',
			'client' => 'Client',
			'fs_carrier_key' => 'App Registration Code',
			'last_update' => 'Last Update',
			'mem_fireshield_status' => 'Fireshield Status',
			'mem_fs_status_date' => 'Fireshield Status Date',
			'is_tester' => 'Test Member',
            'trial_expire_date' => 'Trial Expire Date',
            'status_override' => 'Status Override',
            'client_id' => 'Client',
            'type_id' => 'Type',
		);
	}

    /**
     * validation rule for making sure reg code is unique across both mem and agent
     * @param string $attribute the reg code (fs_carrier_key)
     */
    public function checkUniqueRegCode($attribute)
    {
        // Only run those code for USAA
        if ($this->client_id == 1)
        {
            $member = Member::model()->findByAttributes(array('fs_carrier_key' => $this->$attribute));
            $agent = Agent::model()->findByAttributes(array('fs_carrier_key' => $this->$attribute));
            if (isset($agent) || (isset($member) && $member->mid != $this->mid))
            {
                $this->addError($attribute, 'Your reg code must be unique.');
            }
        }
    }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($advSearch = NULL, $pageSize = 25, $sort = NULL)
	{
        // Support yes/no searches by transforming the text into its numerical value.
        if (strcasecmp($this->is_tester, 'yes') == 0)
            $this->is_tester = 1;

        if (strcasecmp($this->is_tester, 'no') == 0)
            $this->is_tester = 0;

        if (strcasecmp($this->status_override, 'yes') == 0)
            $this->status_override = 1;

        if (strcasecmp($this->status_override, 'no') == 0)
            $this->status_override = 0;

		// Warning: Please modify the following code to remove attributes that
		// should not be searched.
		$criteria=new CDbCriteria;
        $criteria->with = array('type');
		$criteria->compare('mid', $this->mid, true);
		$criteria->compare('member_num', $this->member_num);
		$criteria->compare('salutation', $this->salutation);
		$criteria->compare('rank', $this->rank);
		$criteria->compare('first_name', $this->first_name);
		$criteria->compare('middle_name', $this->middle_name);
		$criteria->compare('last_name', $this->last_name);
		$criteria->compare('spouse_salutation', $this->spouse_salutation);
		$criteria->compare('spouse_rank', $this->spouse_rank);
		$criteria->compare('spouse_first_name', $this->spouse_first_name);
		$criteria->compare('spouse_middle_name', $this->spouse_middle_name);
		$criteria->compare('spouse_last_name', $this->spouse_last_name);
		$criteria->compare('spouse_member_num', $this->spouse_member_num);
		$criteria->compare('home_phone', $this->home_phone);
		$criteria->compare('work_phone', $this->work_phone);
		$criteria->compare('cell_phone', $this->cell_phone);
		$criteria->compare('email_1', $this->email_1);
		$criteria->compare('email_2', $this->email_2);
		$criteria->compare('mail_address_line_1', $this->mail_address_line_1);
		$criteria->compare('mail_address_line_2', $this->mail_address_line_2);
		$criteria->compare('mail_city', $this->mail_city);
		$criteria->compare('mail_state', $this->mail_state);
		$criteria->compare('mail_county', $this->mail_county);
		$criteria->compare('mail_zip', $this->mail_zip);
		$criteria->compare('mail_zip_supp', $this->mail_zip_supp);
		$criteria->compare('signed_ola', $this->signed_ola);
		$criteria->compare('spec_handling_code', $this->spec_handling_code);
		$criteria->compare('client', $this->client);
		$criteria->compare('fs_carrier_key', $this->fs_carrier_key);
		$criteria->compare('t.last_update', $this->last_update);
		$criteria->compare('mem_fireshield_status', $this->mem_fireshield_status);
		$criteria->compare('mem_fs_status_date', $this->mem_fs_status_date);
		$criteria->compare('is_tester', $this->is_tester);
        $criteria->compare('trial_expire_date', $this->trial_expire_date);
        $criteria->compare('status_override', $this->status_override);
        $criteria->compare('t.client_id', $this->client_id);
        $criteria->compare('t.type_id', $this->type_id);

        //From the advanced search dropdown
        if(isset($advSearch['fs_statuses']) && count($advSearch['fs_statuses']) < 5)
		{
            $criteria->addInCondition('mem_fireshield_status',$advSearch['fs_statuses'], 'AND');
		}

        $sortWay = false; //false = DESC, true = ASC
		if(stripos($sort,'.')) //if the sort order is desc it will be specified like 'status.desc'. if its asc then it will just be 'status'
			$sortWay = true;
		$sort = str_replace('.desc', '', $sort); //if the sort order is descending specified it will be like status.desc so need to drop the .desc

        $dataProvider = new CActiveDataProvider($this, array(
			'sort'=>array(
				'defaultOrder'=>array($sort=>$sortWay),
				'attributes'=>array(
					'*',
				),
			),
			'criteria'=>$criteria,
            //'pagination'=>array('pageSize'=>$pageSize)
		));

		if($pageSize == NULL)
		{
			$dataProvider->pagination = false;
		}
		else
		{
			$dataProvider->pagination->pageSize = $pageSize;
			$dataProvider->pagination->validateCurrentPage = false;
		}

		return $dataProvider;
	}

	//creates a report of the current gridview (all pages)
    public function makeDownloadableReport($columnsToShow, $advSearch, $sort)
    {
        $myFile = Yii::getPathOfAlias('webroot').'/protected/downloads/'.Yii::app()->user->name.'_MemReport.csv';
		$fh = fopen($myFile, 'w') or die("can't open file");

        //headerrow
        $tempLine = '';
        foreach($columnsToShow as $column)
        {
            $tempLine .= $column.',';
        }
		fwrite($fh, rtrim($tempLine, ',')."\n");

		//loop through all pages in dataprovider so report contains all data rows
		$pageSize = 100;
		$dataProvider = $this->search($advSearch, $pageSize, NULL);
		$dataRows = $dataProvider->getData(true);
		$pagination = $dataProvider->pagination;
        while ($pagination->currentPage < $pagination->pageCount)
		{
			if($pagination->currentPage+1 == $pagination->pageCount)
			{
				$numberOnLastPage = $pagination->itemCount - $pagination->offset;
				$lastPageIndex = $pageSize;
			}

			$dataRows = $dataProvider->getData(true);
			foreach($dataRows as $data)
			{
				//this top if is part of the dirty hack needed to not repeat data with pagination that only occurs on the second to last and last pages of a MSSQL search. if there is only one page the hack isnt needed.
                //if($pagination->currentPage+1 == $pagination->pageCount && $pagination->pageCount != 1)
                //{
                //    if($lastPageIndex <= $numberOnLastPage)
                //    {
                //        $tempLine = '';
                //        foreach($columnsToShow as $columnToShow)
                //        {
                //            $tempLine .= '"'.str_replace('"', '""', (isset($data[$columnToShow]) ? $data[$columnToShow] : "")).'",';
                //        }
                //        fwrite($fh, $tempLine."\n");
                //    }
                //    $lastPageIndex--;
                //}
                //else
                //{
					$tempLine = '';
					foreach($columnsToShow as $columnToShow)
					{
						$tempLine .= '"'.str_replace('"', '""', (isset($data[$columnToShow]) ? $data[$columnToShow] : "")).'",';
					}
					fwrite($fh, $tempLine."\n");
				//}
			}

			$pagination->currentPage++;
		}

		fclose($fh);
    }

	public function getClients()
	{
        $criteria = new CDbCriteria();
        $criteria->select = 't.name';
        $clients = Client::model()->findAll($criteria);
        $clients_array = array();

        foreach ($clients as $client)
        {
            array_push($clients_array, $client->name);
        }

        return $clients_array;
	}

    public function getProgramStatuses()
	{
		return array('not enrolled', 'ineligible', 'offered', 'enrolled', 'declined');
	}

	public function getFSOfferedDate()
	{
		if($this->mem_fireshield_status == 'offered')
			return date_format(new DateTime($this->mem_fs_status_date), 'm/d/Y h:i A');
		else
		{
			$sh = StatusHistory::model()->findByAttributes(array('table_name'=>'members', 'table_id'=>$this->mid, 'table_field'=>'mem_fireshield_status', 'status'=>'offered'));
			if(isset($sh))
				return date_format(new DateTime($sh->date_changed), 'm/d/Y h:i A');
			else
				return '';
		}
	}

	public function getFSEnrolledDate()
	{
		if($this->mem_fireshield_status == 'enrolled')
			return date_format(new DateTime($this->mem_fs_status_date), 'm/d/Y h:i A');
		else
		{
			$sh = StatusHistory::model()->findByAttributes(array('table_name'=>'members', 'table_id'=>$this->mid, 'table_field'=>'mem_fireshield_status', 'status'=>'enrolled'));
			if(isset($sh))
				return date_format(new DateTime($sh->date_changed), 'm/d/Y h:i A');
			else
				return '';
		}
	}

	protected function afterFind() {
        // Convert the date/time fields to display format.
        $format = 'm/d/Y h:i A';
        $this->mem_fs_status_date = date_format(new DateTime($this->mem_fs_status_date), $format);

        parent::afterFind();
    }

	protected function beforeSave()
	{
        if (empty($this->fs_carrier_key))
			$this->fs_carrier_key = FSUser::model()->createUniqueRegCode();


        // The status override causes all automatic status logic to be bypassed.
        if (!$this->status_override)
        {
            if($this->isNewRecord && !isset($this->mem_fireshield_status))
            {
                $this->mem_fireshield_status = 'not enrolled';
                $this->mem_fs_status_date = date('Y-m-d H:i:s');
            }
        }

		if (!$this->isNewRecord)
		{
			$currentMem = Member::model()->findByPk($this->mid);

            // If any of the FS status field has changed, push their values onto the status_history table.
            if ($currentMem->mem_fireshield_status != $this->mem_fireshield_status)
                StatusHistory::model()->insertStatus($currentMem, 'mem_fireshield_status', $currentMem->mem_fs_status_date);
        }

        if(!isset($this->client_id) && isset($this->client))
        {
            $client = Client::model()->findByAttributes(array('name'=>$this->client));
            $this->client_id = $client->id;
        }

        //if type_id isn't set, assume it's a PIF type
        if (empty($this->type_id))
            $this->type_id = 1; //PIF

		$this->last_update = date('Y-m-d H:i:s');
		return parent::beforeSave();
	}

    /**
     * Checks to see if the given member email address exists.
     * @return integer ID of the Member if exists, 0 otherwise.
     */
    public function checkEmailExists($emailField, $email)
    {
        $id = 0;

        $member = Member::model()->findByAttributes(array($emailField => $email));

        if (isset($member))
            $id = $member->mid;

        return $id;
    }

	/**
     * Retrieves the status history for the current property for a specific table_field.
     * @param string $tableField table field name
     * @return CActiveDataProvider status history data
     */
    public function getStatusHistory($tableField)
    {
        return new CActiveDataProvider('StatusHistory', array(
			'sort' => array('defaultOrder' => array('date_changed' => true)),
			'criteria' => array(
                'condition' => 'table_name=\'members\' AND table_id='.$this->mid.' AND table_field=\''. $tableField .'\'',
            ),
		));
    }
    /*
    *   Check fscarrier key for USAA client Only 
    *   Status = offered/enrolled
    *   Validate Email
    */
    public function validateFSUserEmail($registrationCode,$emailAddress)
    {
        $fsmember = Member::model()->find("fs_carrier_key = '".$registrationCode."'");
        if(isset($fsmember))
        {
            if(isset($fsmember->client_id) && ($fsmember->client_id==1))
            {
                if(in_array($fsmember->mem_fireshield_status,array('offered','enrolled')))
                {
                    if(!in_array($emailAddress,array($fsmember->email_1,$fsmember->email_2)))
                    {
                        return false;
                    }
                }
                else
                {
                    return false;
                }
            }
        }
        return true;
    }
}
?>
