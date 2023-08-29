<?php

/**
 * This is the model class for table "eng_resource_order".
 *
 * The followings are the available columns in table 'eng_resource_order':
 * @property integer $id
 * @property integer $user_id
 * @property string $date_ordered
 * @property string $date_created
 */
class EngResourceOrder extends CActiveRecord
{
    public $user_name;

    public $form_ordered_date;
    public $form_ordered_time;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'eng_resource_order';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, form_ordered_date, form_ordered_time', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('date_created, date_ordered', 'safe'),
			// The following rule is used by search().
			array('id, user_id, date_created, date_ordered, user_name', 'safe', 'on'=>'search'),
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
            'engineScheduling' => array(self::HAS_MANY, 'EngScheduling', 'resource_order_id'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'RO #',
			'user_id' => 'User',
			'date_created' => 'Date Created',
            'date_ordered' => 'Date Ordered',
            'user_name' => 'User Name',

            // Virtual Attributes
            'form_ordered_date' => 'Ordered Date',
            'form_ordered_time' => 'Ordered Time'
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
	public function search($advSearch = null)
	{
		$criteria=new CDbCriteria;

        $criteria->with = array('user');

		$criteria->compare('t.id',$this->id);
        $criteria->compare('[user].name',$this->user_name,true);

        if ($this->date_ordered)
            $criteria->addCondition("t.date_ordered >= '$this->date_ordered' and t.date_ordered < '" . date('Y-m-d', strtotime($this->date_ordered . ' + 1 day')) . "'");

        if (!empty($advSearch['eng-clients']) && empty($advSearch['eng-assignments']))
        {
            $criteria->join = 'inner join eng_scheduling as s on t.id = s.resource_order_id
                               inner join eng_scheduling_client as c on c.engine_scheduling_id = s.id';
            $criteria->addInCondition('c.client_id', $advSearch['eng-clients']);
        }
        else if (empty($advSearch['eng-clients']) && !empty($advSearch['eng-assignments']))
        {
            $criteria->join = 'inner join eng_scheduling as s on t.id = s.resource_order_id';
            $criteria->addInCondition('s.assignment', $advSearch['eng-assignments']);
        }
        else if (!empty($advSearch['eng-clients']) && !empty($advSearch['eng-assignments']))
        {
            $criteria->join = 'inner join eng_scheduling as s on t.id = s.resource_order_id
                               inner join eng_scheduling_client as c on c.engine_scheduling_id = s.id';
            $criteria->addInCondition('c.client_id', $advSearch['eng-clients']);
            $criteria->addInCondition('s.assignment', $advSearch['eng-assignments']);
        }

		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder'=>array('id'=>CSort::SORT_DESC),
				'attributes' => array(
                    'user_name' => array(
                        'asc' => 'user.name',
                        'desc' => 'user.name DESC'
                    ),
                    '*',
				),
			),
			'criteria'=>$criteria,
            'pagination' => array('PageSize'=>20)
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return EngResourceOrder the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function afterFind()
    {
        if ($this->user)
            $this->user_name = $this->user->name;

        return parent::beforeFind();
    }

    protected function beforeSave()
    {
        Yii::app()->format->datetimeFormat = 'Y-m-d H:i';

        if (isset($_POST['EngResourceOrder']['form_ordered_date']) && isset($_POST['EngResourceOrder']['form_ordered_time']))
        {
            $this->date_ordered = Yii::app()->format->datetime($_POST['EngResourceOrder']['form_ordered_date'] . ' ' . $_POST['EngResourceOrder']['form_ordered_time']);
        }

        return parent::beforeSave();
    }

    //----------------------------------------------------- General Functions -------------------------------------------------------------

    public function availibleFireClients()
    {
        return Client::model()->findAll(array('order'=>'name ASC','condition'=>'wds_fire = 1'));
    }

    public function downloadResourceOrderPDF($model, $forceDownload = true)
    {
        Yii::import('application.vendors.tcpdf.*');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Wildfire Defense Systems');
        $pdf->SetTitle('Resource Order ' . $model->resource_order_num);
        $pdf->SetSubject('Resource Order');
        $pdf->SetKeywords('PDF, Resource Order');
        $pdf->SetFont('times', '', 12);

		//set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 18, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // list styles
        $pdf->setListIndentWidth(10);
        $pdf->setLIsymbol('circle');

        $pdf->AddPage('L');

        $pdf->SetFont('times', '', 10);

        $html = '
        <table style="text-align: center;" cellpadding="10px">
            <tr>
                <td style="text-align: left;">Incident: ' . $model->assignment . ($model->fire_id ? ' (<i>' . $model->fire_name . '</i>)' : '') . '</td>
                <td style="text-align: right;">Resource Order: ' . $model->resource_order_num . '</td>
            </tr>
            <tr>
                <td colspan="2">' .
                    implode('<br />', array_map(function($engineClient) { return $engineClient->client->name; }, $model->engineClient)) .
                '</td>
            </tr>
        </table>';

        $html .=
        '<br />

        <table style="text-align: center; vertical-align: middle;" cellpadding="4px">
            <tr>
                <td style="width: 25%; border: 1px solid #000000;">Assignment Name and Location</td>
                <td style="width: 20%; border: 1px solid #000000;">Order Time</td>
                <td style="width: 20%; border: 1px solid #000000;">Estimated Incident Arrival Time</td>
                <td style="width: 35%; border: 1px solid #000000;">Ordered By</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000000;"><b>' . $model->resourceOrderGetAssignment() . '</b></td>
                <td style="border: 1px solid #000000;"><b>' . $model->resourceOrderNearestQuarterHour(date('d-m-Y', strtotime($model->resourceOrder->date_ordered))) . '</b></td>
                <td style="border: 1px solid #000000;"><b>' . date('m/d/Y \a\t H:i \M\D\T', strtotime($model->arrival_date)) . '</b></td>
                <td style="border: 1px solid #000000;"><b>WDS Staff:<br />' . $model->resourceOrder->user_name . '</b></td>
            </tr>
        </table>

        <br />

        <table style="text-align: center; vertical-align: middle;" cellpadding="4px">
            <tr>
                <td style="width: 25%; border: 1px solid #000000;">Company Name and Phone #</td>
                <td style="width: 20%; border: 1px solid #000000;">Preseason Agreement #</td>
                <td style="width: 20%; border: 1px solid #000000;">Resource Requested</td>
                <td style="width: 35%; border: 1px solid #000000;">Engine Boss and Contact Info</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000000;" valign="middle"><b>' . $model->resourceOrderGetCompanyInfo() . '</b></td>
                <td style="border: 1px solid #000000;" valign="middle"><b>' . ($model->engine->alliancepartner ? $model->engine->alliancepartner->preseason_agreement : '') . '</b></td>
                <td style="border: 1px solid #000000;" valign="middle"><b>' . $model->engine->engine_name . '</b></td>
                <td style="border: 1px solid #000000;" valign="middle"><b>' . $model->resourceOrderGetEngineBoss() . '</b></td>
            </tr>
        </table>

        <br />

        <table style="border: 1px solid #000000;" cellpadding="6px">
            <tr>
                <td>
                    <span>Instructions</span>
                    <ul>
                        <li>Specific Instructions: ' . $model->specific_instructions . '</li>
                        <li>Fire Officer: ' . $model->resourceOrderGetFireOfficer() . '</li>
                        <li>Morning WDS briefing - 1000 hrs MDT and the number is (515) 604-9094 (contact supervisor for the code)</li>
                        <li>Insure engine is fully equipped with sprinkler kits, gel, and all electronic equipment.</li>
                        <li>Send all shift tickets daily to eest@wildfire-defense.com.</li>
                        <li>Wildfire Defense Systems, Inc. &ndash; (406)586-5400 ext. 1 or (877)-323-4730 ext. 1</li>
                    </ul>
                </td>
            </tr>
        </table>

        <br />
        <br />

        <table style="font-weight: bold;">
            <tr>
                <th width="12%" style="text-align: right;">Crew Manifest: &nbsp;&nbsp;</th>
                <td width="88%" style="text-align: left;">' . implode(' / ', array_map(function($employee) { return "$employee->crew_first_name $employee->crew_last_name"; }, $model->employees)) . '</td>
            </tr>
            <tr>
                <th width="12%" style="text-align: right;">Make: &nbsp;&nbsp;</th>
                <td width="88%" style="border: none; text-align: left;">' . $model->engine->make . '</td>
            </tr>
            <tr>
                <th width="12%" style="text-align: right;">Model: &nbsp;&nbsp;</th>
                <td width="88%" style="text-align: left;">' . $model->engine->model . '</td>
            </tr>
            <tr>
                <th width="12%" style="text-align: right;">VIN: &nbsp;&nbsp;</th>
                <td width="88%" style="text-align: left;">' . $model->engine->vin . '</td>
            </tr>
            <tr>
                <th width="12%" style="text-align: right;">Plate: &nbsp;&nbsp;</th>
                <td width="88%" style="text-align: left;">' . $model->engine->plates . '</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        // ----------------- OUTPUT PDF -----------------------------

        if ($forceDownload)
        {
            //Inline
            $pdf->Output('Resource Order.pdf', 'I');
            return;
        }

        //Allow user to choose where to save
        $fileName = Yii::getPathOfAlias('webroot.protected.downloads') . DIRECTORY_SEPARATOR . 'Resource Order.pdf';
        $pdf->Output($fileName, 'F');
        return $fileName;
    }

    //----------------------------------------------------- Virtual Attributes -------------------------------------------------------------

    public function getEngineName()
    {
        return implode('<br />', array_map(function($scheduling) { return $scheduling->engine_name; }, $this->engineScheduling));
    }

    public function getEngineAssignment()
    {
        return implode('<br />', array_map(function($scheduling) { return $scheduling->assignment; }, $this->engineScheduling));
    }

    public function getEngineCity()
    {
        return implode('<br />', array_map(function($scheduling) { return $scheduling->city; }, $this->engineScheduling));
    }

    public function getEngineState()
    {
        return implode('<br />', array_map(function($scheduling) { return $scheduling->state; }, $this->engineScheduling));
    }

    public function getDateStart()
    {
        return implode('<br />', array_map(function($scheduling) { return $scheduling->start_date; }, $this->engineScheduling));
    }

    public function getClients()
    {
        return implode('<br />', array_map(function($scheduling) { return implode(', ', $scheduling->client_names); }, $this->engineScheduling));
    }
}
