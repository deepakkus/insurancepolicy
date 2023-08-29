<?php

/**
 * This is the model class for table "res_perimeter_buffers".
 *
 * The followings are the available columns in table 'res_perimeter_buffers':
 * @property integer $id
 * @property string $date_created
 * @property string $date_updated
 * @property integer $perimeter_id
 * @property integer $location_id
 * @property string $buffer_distance
 */
class ResPerimeterBuffers extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'res_perimeter_buffers';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('perimeter_id, location_id', 'required'),
			array('perimeter_id, location_id', 'numerical', 'integerOnly'=>true),
			array('date_created, date_updated, buffer_distance', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, date_created, date_updated, perimeter_id, location_id, buffer_distance', 'safe', 'on'=>'search')
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
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'perimeter_id' => 'Perimeters',
			'location_id' => 'Location',
            'buffer_distance' => 'Buffer Distance'
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
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_updated',$this->date_updated,true);
		$criteria->compare('perimeter_id',$this->perimeter_id);
		$criteria->compare('location_id',$this->location_id);
        $criteria->compare('buffer_distance',$this->buffer_distance);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ResPerimeterBuffers the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Creates new buffer geographies and saves them for the given perimeter
     * @param int $perimeterID
     * @param int $outerRingDistance - optional, if the outer ring needs to be greater than 5 miles (default alert distance)
     * @return boolean - true/false if the save worked
     */
    public static function createBuffers($perimeterID, $outerRingDistance = 5)
    {
        //Returned at end
        $result = true;

        //Generate buffers
        $buffers = GIS::generateBuffers($perimeterID, $outerRingDistance);
        foreach($buffers as $key=>$value)
        {
            //set the type based on if it's the standard 1/2 - 3 miles, or if it's the variable outer
            $type = ($key == 'outer') ? "$key ring buffer" : "$key mile buffer";
            $distance = '';
            if($key == 'half')
            {
                $distance = '.5';
            }
            elseif($key == 'one')
            {
                $distance = '1';
            }
            elseif($key == 'three')
            {
                $distance = '3';
            }
            else
            {
                $distance = $outerRingDistance;
            }

            //Create geographies
            $location = new Location();
            $location->geog = $value;
            $location->type = $type;
            if($location->save())
            {
                //Save buffer reference
                $buffer = new ResPerimeterBuffers();
                $buffer->date_created = date('Y-m-d H:i');
                $buffer->date_updated = date('Y-m-d H:i');
                $buffer->perimeter_id = $perimeterID;
                $buffer->location_id = $location->getPrimaryKey();
                $buffer->buffer_distance = $distance;

                if(!$buffer->save())
                {
                    $result = false;
                }
            }
            else
            {
                $result = false;
            }
        }

        return $result;

    }

    /**
     * Summary of updateOuterRingBuffer
     * @param int $perimeterID
     * @param float $outerRingDistance - distance in miles
     */
    public static function updateOuterRingBuffer($perimeterID, $outerRingDistance)
    {
        //Feedback on if it saved or not
        $result = true;

        //Find the buffer in the locations table
        $outerRing = Location::model()->findBySql("select * from location
            where type = 'outer ring buffer'
            and id in (select location_id from res_perimeter_buffers where perimeter_id = :perimeter_id)", array(':perimeter_id'=>$perimeterID)
        );

        //Older perimeters won't have any rings
        if($outerRing)
        {
            //Generate new outer ring
            $bufferGeog = GIS::generateBuffer($perimeterID, $outerRingDistance);
            $buffer = ResPerimeterBuffers::model()->findBySql(
                "select b.* 
                from res_perimeter_buffers b
                inner join location l on l.id = b.location_id
                where 
                    b.perimeter_id = :perimeter_id 
                    and l.type = 'outer ring buffer'", 
                array(':perimeter_id'=>$perimeterID)
            );

            //Update outer ring in db
            $outerRing->geog = $bufferGeog['outer'];
            if(!$outerRing->save())
            {
                $result = false;
            }
            else
            {
                $buffer->buffer_distance = $outerRingDistance;
                $result = $buffer->save();
            }
        }

        return $result;
    }

    /**
     * Generates new buffers because a perimeter has been updated
     * @param int $perimeterID
     * @return boolean if buffers were created
     */
    public static function updateBuffers($perimeterID)
    {
        //Return true/false
        $return = null;
        //Get buffers to update
        $buffers = ResPerimeterBuffers::model()->findAllByAttributes(array('perimeter_id'=>$perimeterID));
        //Older perimeters won't have any buffers
        if($buffers)
        {
            //Go through and delete locations and buffers
            foreach($buffers as $buffer)
            {
                $locationID = $buffer->location_id;
                $buffer->delete();
                Location::model()->deleteByPk($locationID);
            }
        }

        //See if there is a monitor log related to the perimeter, if so use the alert distance as the outer ring
        $monitorLog = ResMonitorLog::model()->findBySql("select top 1 * from res_monitor_log where perimeter_id = :perimeter_id", array(":perimeter_id"=>$perimeterID));

        //Entry exists, so find the alert distance
        if($monitorLog)
        {
            $return = ResPerimeterBuffers::createBuffers($perimeterID, $monitorLog->Alert_Distance);
        }
        else
        {
            $return = ResPerimeterBuffers::createBuffers($perimeterID);
        }

        //Call create buffers function
        return $return;

    }
}
