<?php
/**
 * This is the model class for table "status_history".
 *
 * The followings are the available columns:
 * @property integer $id
 * @property string $table_name
 * $property string $table_field
 * @property integer $table_id
 * @property string $status
 * @property string $date_changed
 * @property integer $user_id
 */

class StatusHistory extends CActiveRecord
{    
	/**
	 * Returns the static model of the specified AR class.
	 * @return StatusHistory the static model class
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
		return 'status_history';
	}
	
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('table_name, table_field, table_id, status, date_changed', 'required'),
			array('user_id, table_id', 'numerical', 'integerOnly'=>true),
			array('table_name, table_field, status', 'length', 'max'=>50),
			array('date_changed', 'length', 'max'=>30),
			array('id, table_name, table_field, table_id, status, date_changed, user_id', 'safe', 'on'=>'search'),
		);		
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'property' => array(self::BELONGS_TO, 'Property', 'table_id'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'table_name' => 'Table Name',
			'table_field' => 'Table Field',
			'table_id' => 'Table ID',
			'status' => 'Status',
			'date_changed' => 'DateTime',
			'user_id' => 'User',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('table_name', $this->table_name);
		$criteria->compare('table_field', $this->table_field);
		$criteria->compare('table_id', $this->table_id);
		$criteria->compare('status', $this->status);
		$criteria->compare('date_changed', $this->date_changed);
		$criteria->compare('user_id', $this->user_id);
			        
		$data_provider = new CActiveDataProvider($this, array('criteria'=>$criteria, 'pagination'=>array('pageSize'=>50)));
		
		return $data_provider;
	}
	
	public function getProgramStatuses()
	{
		return array('ineligible', 'not enrolled', 'offered', 'enrolled', 'declined');
	}
	
	public function getPolicyStatuses()
	{
		return array('active', 'pending', 'canceled', 'expired');
	}
    
    /**
     * Given a model, this function inserts the current value of the given status field(s) into the status_history table.
     * @param CActiveRecord $model
     * @param string $statusField
     */
    public function insertStatus($model, $statusField, $dateChanged)
    {
        $statusHistory = new StatusHistory();
        $statusHistory->table_name = $model->tableName();
        $statusHistory->table_field = $statusField; 
        $statusHistory->table_id = $model->attributes[$model->tableSchema->primaryKey];
        $statusHistory->status = $model->attributes["$statusField"];
        $statusHistory->date_changed = $dateChanged;

		if(isset(Yii::app()->user) && isset(Yii::app()->user->id))
			$statusHistory->user_id = Yii::app()->user->id;
		else
			$statusHistory->user_id = NULL;

        $statusHistory->insert();
    }
}
?>
