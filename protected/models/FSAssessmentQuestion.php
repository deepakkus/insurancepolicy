<?php

/**
 * This is the model class for table "fs_assessment_question".
 *
 * The followings are the available columns in table 'fs_assessment_question':
 * @property integer $id
 * @property integer $active
 * @property integer $client_id
 * @property integer $question_num
 * @property integer $order_by
 * @property integer $label
 * @property integer $section_type
 * @property string $title
 * @property string $question_text
 * @property string $overlay_portrait_image_name
 * @property string $overlay_landscape_image_name
 * @property string $overlay_image_help_text
 * @property boolean $overlay_image_should_stretch
 * @property integer $number_of_required_photos
 * @property boolean $requires_landscape_photo
 * @property string $help_uri
 * @property integer $launch_camera_on_response_action
 * @property integer $enforce_required_photos_on_response_action
 * @property boolean $allow_notes
 * @property integer $yes_points
 * @property string $rec_text
 * @property string $risk_text
 * @property string $description
 * @property string $example_text
 * @property integer $example_image_file_id
 * @property string $type
 * @property string $choices
 * @property string $choices_type
 * @property integer $set_id
 * @property string $photo_text
 *
 * The followings are the available model relations:
 * @property Client $client
 */
class FSAssessmentQuestion extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'fs_assessment_question';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('client_id, question_num, set_id, section_type, order_by, label, type', 'required'),
			array('client_id, active, question_num, order_by, section_type, number_of_required_photos, launch_camera_on_response_action, enforce_required_photos_on_response_action, yes_points, example_image_file_id, set_id', 'numerical', 'integerOnly'=>true),
			array('title, label, overlay_portrait_image_name, overlay_landscape_image_name, help_uri, description', 'length', 'max'=>256),
			array('question_text, rec_text, example_text, risk_text, choices, photo_text', 'length', 'max'=>1024),
			array('overlay_image_help_text', 'length', 'max'=>512),
            array('choices_type', 'length', 'max'=>10),
			array('overlay_image_should_stretch, requires_landscape_photo, allow_notes', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, active, type, client_id, question_num, order_by, label, section_type, title, question_text, example_text, risk_text, photo_text, overlay_portrait_image_name, overlay_landscape_image_name, overlay_image_help_text, overlay_image_should_stretch, number_of_required_photos, requires_landscape_photo, help_uri, launch_camera_on_response_action, enforce_required_photos_on_response_action, allow_notes, yes_points, rec_text, example_image_file_id, choices, choices_type, set_id', 'safe', 'on'=>'search'),
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
			'client' => array(self::BELONGS_TO, 'Client', 'client_id'),
            'example_image' => array(self::BELONGS_TO, 'File', 'example_image_file_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
            'active' => 'Active',
            'type' => 'Type',
			'client_id' => 'Client',
			'question_num' => 'Question Num',
            'order_by' => 'Order By',
            'label' => 'Label',
			'section_type' => 'App Assessment Section',
			'title' => 'Title',
			'question_text' => 'Question Text',
			'overlay_portrait_image_name' => 'Overlay Portrait Image Name',
			'overlay_landscape_image_name' => 'Overlay Landscape Image Name',
			'overlay_image_help_text' => 'Help Text',
			'overlay_image_should_stretch' => 'Overlay Image Should Stretch',
			'number_of_required_photos' => 'Number Of Required Photos',
			'requires_landscape_photo' => 'Requires Landscape Photo',
			'help_uri' => 'Help Uri',
			'launch_camera_on_response_action' => 'Launch Camera On Response Action',
            'enforce_required_photos_on_response_action' => 'Enforce Required Photos On Response Action',
            'allow_notes' => 'Allow Notes',
            'yes_points' => 'Yes Points',
            'rec_text' => 'Action Text',
            'risk_text' => 'Risk Text',
			'description' => 'Description',
            'example_text' => 'Example Text',
            'example_image' => 'Example Image',
            'choices' => 'Choices',
            'choices_type' => 'Choices Type',
            'set_id' => 'Set ID',
            'photo_text' => 'Photo Text',
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
        $criteria->compare('active', $this->active);
        $criteria->compare('type', $this->type);
		$criteria->compare('client_id',$this->client_id);
		$criteria->compare('question_num',$this->question_num);
        $criteria->compare('order_by',$this->order_by);
        $criteria->compare('label',$this->label);
		$criteria->compare('section_type',$this->section_type);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('question_text',$this->question_text,true);
		$criteria->compare('overlay_portrait_image_name',$this->overlay_portrait_image_name,true);
		$criteria->compare('overlay_landscape_image_name',$this->overlay_landscape_image_name,true);
		$criteria->compare('overlay_image_help_text',$this->overlay_image_help_text,true);
		$criteria->compare('overlay_image_should_stretch',$this->overlay_image_should_stretch);
		$criteria->compare('number_of_required_photos',$this->number_of_required_photos);
		$criteria->compare('requires_landscape_photo',$this->requires_landscape_photo);
		$criteria->compare('help_uri',$this->help_uri,true);
		$criteria->compare('launch_camera_on_response_action',$this->launch_camera_on_response_action);
        $criteria->compare('enforce_required_photos_on_response_action',$this->enforce_required_photos_on_response_action);        
        $criteria->compare('allow_notes',$this->allow_notes);
        $criteria->compare('yes_points',$this->yes_points);
        $criteria->compare('rec_text',$this->rec_text, true);
        $criteria->compare('risk_text',$this->risk_text, true);
		$criteria->compare('description', $this->description, true);
        $criteria->compare('example_text', $this->example_text, true);
        $criteria->compare('example_image_file_id', $this->example_image_file_id);
        $criteria->compare('set_id', $this->set_id);
        $criteria->compare('photo_text', $this->photo_text);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
            'pagination'=>array('pageSize'=>100),
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return FSAssessmentQuestion the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function getSectionTitle()
    {
        $sections = array(
            0 => 'Home',
            1 => 'Yard',
        );
        if(isset($this->section_type) && array_key_exists($this->section_type, $sections))
        {
            return $sections[$this->section_type];
        }
        else
            return null;
    }

    public function getChoicesArray()
    {
        $returnArray = json_decode($this->choices, true);
        if(json_last_error() === JSON_ERROR_NONE && is_array($returnArray))
            return $returnArray['choices'];
        else
            return array();
    }

    public function getChoicesTypeOptions()
    {
        return array('single'=>'single','multi'=>'multi');
    }

    public function getTypeOptions()
    {
        return array('normal'=>'normal', 'info only'=>'info only', 'field'=>'field', 'condition'=>'condition', 'foh'=>'foh');
    }
}
