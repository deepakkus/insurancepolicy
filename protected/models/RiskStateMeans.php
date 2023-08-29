<?php

/**
 * This is the model class for table "risk_state_means".
 *
 * The followings are the available columns in table 'risk_state_means':
 * @property integer $id
 * @property string $mean
 * @property string $std_dev
 * @property integer $state_id
 * @property string $date_created
 * @property string $date_updated
 * @property string $version_id
 *
 * The followings are the available model relations:
 * @property GeogStates $state
 * @property RiskVersion $riskVersion
 */
class RiskStateMeans extends CActiveRecord
{
    public $stateAbbr;
    public $version;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'risk_state_means';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('mean, std_dev, state_id, version_id', 'required'),
			array('state_id', 'numerical', 'integerOnly'=>true),
			array('mean, std_dev', 'numerical'),
            // The following validation rule checks for unique version_id / state_id combinations
            array('state_id', 'checkStateVersion'),
			// The following rule is used by search().
			array('mean, std_dev, stateAbbr, version', 'safe', 'on'=>'search'),
		);
	}

    /**
     * Checking to see if unique combinations of state_id / version_id have already been entered.
     */
    public function checkStateVersion()
    {
        if ($this->state_id && $this->version_id)
        {
            $exists = RiskStateMeans::model()->exists('state_id = :state_id AND version_id = :version_id', array(
                ':state_id' => $this->state_id,
                ':version_id' => $this->version_id
            ));

            if ($exists)
            {
                $this->addError('state_id', 'This combination of state and version has already been entered.');
                $this->addError('version_id', 'This combination of state and version has already been entered.');
            }
        }
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'state' => array(self::BELONGS_TO, 'GeogStates', 'state_id'),
            'riskVersion' => array(self::BELONGS_TO, 'RiskVersion', 'version_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'mean' => 'Mean',
			'std_dev' => 'Std Dev',
			'state_id' => 'State',
			'date_created' => 'Date Created',
            'date_updated' => 'Date Updated',
            'version_id' => 'Version',

            // Virtual Abbributes
            'stateAbbr' => 'State',
            'version' => 'Version'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

        $criteria->with = array(
            'state' => array(
                'select' => array('id','abbr')
            ),
            'riskVersion' => array(
                'select' => array('id','version')
            )
        );

		$criteria->compare('mean', $this->mean);
		$criteria->compare('std_dev', $this->std_dev);
        $criteria->compare('state.abbr', $this->stateAbbr, true);
        $criteria->compare('riskVersion.version', $this->version, true);

        return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array(
                    'stateAbbr'=>array(
                        'asc' => 'state.abbr ASC',
                        'desc' => 'state.abbr DESC'
                    ),
                    'version' => array(
                        'asc' => 'riskVersion.version ASC',
                        'desc' => 'riskVersion.version DESC'
                    ),
                    '*'
                )
            ),
			'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return RiskStateMeans the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeSave()
    {
        if ($this->isNewRecord)
        {
            $this->date_created = date('Y-m-d H:i');
        }

        $this->date_updated = date('Y-m-d H:i');

        return parent::beforeSave();
    }

    protected function afterFind()
    {
        if ($this->state)
        {
            $this->stateAbbr = $this->state->abbr;
        }

        if ($this->riskVersion)
        {
            $this->version = $this->riskVersion->version;
        }

        $this->mean = number_format((double)$this->mean, 14);
        $this->std_dev = number_format((double)$this->std_dev, 14);

        return parent::afterFind();
    }

    /**
     * Returns the RiskStateMeans model from a pair of coordinates.
     * @param string $lat latitude.
     * @param string $long longitude.
     * @return RiskStateMeans the static model class
     */
    public static function loadModelByLatLong($lat, $long)
    {
        $versionID = RiskVersion::getLiveVersionID();

        // Try find by coordiantes first

        $sql = 'state_id = (
            SELECT id FROM geog_states WHERE geography::Point(:lat, :lon, 4326).STIntersects(geog) = 1
        ) AND version_id = :version_id';

        $model = self::model()->find($sql, array(
            ':lat' => $lat,
            ':lon' => $long,
            ':version_id' => $versionID
        ));

        if (!$model)
        {
            // Reverse geocode to get the state ( will work for all situations )

            $place = Geocode::reverseGeocode($lat, $long);

            if (!isset($place['error']) && isset($place['state_abbr']))
            {
                if (is_string($place['state_abbr']) && strlen($place['state_abbr']) === 2)
                {
                    $model = self::model()->find('state_id = (SELECT id FROM geog_states WHERE abbr = :abbr) AND version_id = :version_id', array(
                        ':abbr' => $place['state_abbr'],
                        ':version_id' => $versionID
                    ));
                }
            }
        }

        return $model;
    }
}
