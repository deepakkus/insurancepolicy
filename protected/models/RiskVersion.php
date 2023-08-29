<?php

/**
 * This is the model class for table "risk_version".
 *
 * The followings are the available columns in table 'risk_version':
 * @property integer $id
 * @property string $version
 * @property string $year_dataset
 * @property string $comment
 * @property integer $is_live
 *
 * The followings are the available model relations:
 * @property RiskStateMeans[] $riskStateMeans
 * @property RiskBatchFile[] $riskBatchFiles
 * @property RiskScore[] $riskScores
 */
class RiskVersion extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'risk_version';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('version, year_dataset, is_live', 'required'),
            array('is_live', 'numerical', 'integerOnly'=>true),
            array('version', 'length', 'max'=>10),
            array('year_dataset', 'length', 'max'=>4),
            array('comment', 'length', 'max'=>300),
            // The following rule is used by search().
            array('id, version, year_dataset, comment, is_live', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'riskStateMeans' => array(self::HAS_MANY, 'RiskStateMeans', 'version_id'),
            'riskBatchFiles' => array(self::HAS_MANY, 'RiskBatchFile', 'version_id'),
            'riskScores' => array(self::HAS_MANY, 'RiskScore', 'version_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'version' => 'Version',
            'year_dataset' => 'Year of Dataset',
            'comment' => 'Comment',
            'is_live' => 'Is Live'
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
        $criteria = new CDbCriteria;

        $criteria->compare('version', $this->version, true);
        $criteria->compare('year_dataset', $this->year_dataset, true);
        $criteria->compare('comment', $this->comment, true);
        $criteria->compare('is_live', $this->is_live);

        return new CActiveDataProvider($this, array(
            'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes'=>array(
                    '*'
                )
            ),
			'criteria' => $criteria,
            'pagination' => array('pageSize' => 10)
		));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return RiskVersion the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Return id of live risk version
     * @return int
     */
    public static function getLiveVersionID()
    {
        return (int)Yii::app()->db->createCommand('SELECT id FROM risk_version WHERE is_live = 1')->queryScalar();
    }

    /**
     * Return name of live risk version
     * @return string
     */
    public static function getLiveVersionName()
    {
        return Yii::app()->db->createCommand('SELECT version FROM risk_version WHERE is_live = 1')->queryScalar();
    }
}
