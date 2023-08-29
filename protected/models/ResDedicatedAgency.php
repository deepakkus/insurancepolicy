<?php

/**
 * This is the model class for table "res_dedicated_agency".
 *
 * The followings are the available columns in table 'res_dedicated_agency':
 * @property integer $id
 * @property string $name
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $contact_name
 * @property string $contact_phone_1
 * @property string $contact_phone_2
 * @property string $email
 * @property string $comment
 * @property string $wds_contact
 * @property string $last_contact_date
 * @property string $date_created
 * @property string $geog
 * @property integer $client_id
 */
class ResDedicatedAgency extends CActiveRecord
{
    public $lat;
    public $lon;
    
    public $full_address;
    
	/**
     * @return string the associated database table name
     */
	public function tableName()
	{
		return 'res_dedicated_agency';
	}

	/**
     * @return array validation rules for model attributes.
     */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('name, address, contact_name, wds_contact, last_contact_date', 'required'),
            array('state', 'length', 'max'=>2),
			array('city, contact_phone_1, contact_phone_2, email, wds_contact', 'length', 'max'=>50),
            array('name, address, contact_name', 'length', 'max'=>100),
            array('comment', 'length', 'max'=>2000),
			array('last_contact_date, date_created, geog', 'safe'),
            array('client_id', 'numerical', 'integerOnly' => true),
            
            array('lat, lon', 'required'),
            array('lat, lon', 'numerical', 'allowEmpty' => false),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('address, contact_name, contact_phone_1, contact_phone_2, email, comment, wds_contact, last_contact_date, client_id', 'safe', 'on'=>'search'),
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
		);
	}

	/**
     * @return array customized attribute labels (name=>label)
     */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
            'name' => 'Dept Name',
			'address' => 'Address',
            'city' => 'City',
            'state' => 'State',
			'contact_name' => 'Contact Name',
			'contact_phone_1' => 'Contact Phone 1',
			'contact_phone_2' => 'Contact Phone 2',
			'email' => 'Email',
			'comment' => 'Comment',
			'wds_contact' => 'WDS Contact',
			'last_contact_date' => 'Latest Contact Date',
            'date_created' => 'Date Created',
			'geog' => 'Geography',
            'client_id' => 'Client',
            
            // Virtual attributes
            'full_address' => 'Address',
            'lat' => 'Lat',
            'lon' => 'Lon'
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
		$criteria=new CDbCriteria;

        $criteria->compare('name',$this->name,true);
		$criteria->compare('address',$this->address,true);
        $criteria->compare('city',$this->city,true);
        $criteria->compare('state',$this->state,true);
		$criteria->compare('contact_name',$this->contact_name,true);
		$criteria->compare('contact_phone_1',$this->contact_phone_1,true);
		$criteria->compare('contact_phone_2',$this->contact_phone_2,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('comment',$this->comment,true);
		$criteria->compare('wds_contact',$this->wds_contact,true);
        $criteria->compare('client_id',$this->client_id);
        
		return new CActiveDataProvider($this, array(
			'sort' => array(
				'defaultOrder'=>array('last_contact_date'=>CSort::SORT_DESC),
				'attributes' => array('*'),
			),
			'criteria'=>$criteria,
            'pagination' => array('PageSize'=>20)
		));
	}

	/**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResDedicatedAgency the static model class
     */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function beforeFind()
    {
        // Need to convert geometry type to wkt so it doesn't come through as binary (problems with reading and writing)
        if(isset($this->dbCriteria) && isset($this->dbCriteria->select) && $this->dbCriteria->select == '*')
        {
            $alias = $this->getTableAlias();
            $columnReplace = ($alias != 't') ?
                "$alias.geog.ToString() as $alias.geog, $alias.geog.Lat as lat, $alias.geog.Long as lon" : 
                't.geog.ToString() as geog, geog.Lat as lat, geog.Long as lon';

            $columns = implode(',', array_keys($this->attributes));
            $select = str_replace('geog', $columnReplace, $columns);
            $this->dbCriteria->select = $select;
        }

        parent::beforeFind();
    }
    
    protected function afterFind()
    {
        $this->full_address = "$this->address, $this->city, $this->state";
        
        return parent::afterFind();
    }
    
    protected function beforeSave()
    {
        if ($this->isNewRecord)
            $this->date_created = date('Y-m-d');
        
        $post = Yii::app()->request->getPost('ResDedicatedAgency');
        $lat = $post['lat'];
        $lon = $post['lon'];
        
        if (!empty($lat) && !empty($lon))
            $this->geog = "POINT ($lon $lat)";
        
        return parent::beforeSave();
    }
    
    //------------------------------------------------------------------- General Calls----------------------------------------------------------------
    #region General Functions
    
	/**
     * Virtual Attribute
     * Returns comment field truncated to 300 chars if needed
     * @return string
     */
    public function getAgencyComment()
    {
        if ($this->comment)
            return (strlen($this->comment) > 300) ? substr($this->comment, 0, 300) . ' ...' : $this->comment;
    }
    
	/**
     * Find all location in the ResDedicatedAgency model by locations
     * Default search distance is 100 miles
     * @param string $lat latitude.
     * @param string $lon longitude.
     * @param integer $miles distance radius for the buffer search
     * @return array ResDedicatedAgency model classes
     */
    public function findModelsByLocation($lat, $lon, $miles = 100)
    {
        $metersSearch = $miles * 1609.344;
        
        return self::model()->findAll(array(
            'condition' => "geog.STIntersects(geography::Point($lat, $lon, 4326).STBuffer($metersSearch)) = 1"
        ));
    }
    
    
    public function agencyVisitPDFTemplate($models = null)
    {
        Yii::import('application.vendors.tcpdf.*'); 
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Wildfire Defense Systems');
        $pdf->SetTitle('Agency Visits');
        $pdf->SetSubject('Agency Visits');
        $pdf->SetKeywords('PDF, Agency Visits');
        $pdf->SetFont('times', '', 12);
        
		//set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 16, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        if ($models)
        {
            foreach ($models as $model)
                self::agencyPdfForm($pdf, $model);
        }
        else
        {
            self::agencyPdfForm($pdf);
        }
        
        $pdf->Output('Agency Visits.pdf','I');
    }
    
    private function agencyPdfForm($pdf, $model = null)
    {
        $pdf->AddPage();
        
        $html = '
        <table style="text-align: left; vertical-align: middle; " cellpadding="4px">
            <tr>
                <td colspan="2" style="font-size: 22pt;">WILDFIRE DEFENSE SYSTEMS</td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <table>
                        <tr><td>Bozeman Office:</td></tr>
                        <tr><td>201 Evergreen Drive, Suite 1</td></tr>
                        <tr><td>Bozeman, Montana 59715</td></tr>
                        <tr><td>ph: 406.586.5400 fx: 406.406.050 0</td></tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <table>
                        <tr><td>Corporate Office:</td></tr>
                        <tr><td>PO Box 2269</td></tr>
                        <tr><td>Red Lodge, Montana 59068</td></tr>
                        <tr><td>ph: 406.446.3646 fx: 406.794.0806</td></tr>
                    </table>
                </td>
            </tr>
        </table>
        ';
        
        $pdf->Cell(20, 0);
        $pdf->SetFont('times', '', 10);
        $pdf->writeHTMLCell(135, 50, $pdf->GetX(), $pdf->GetY(), $html, 0, 0, false, true, '', false);
        $pdf->Image('images/logo.jpg','','',25);
        $pdf->Ln();
        
        $pageWidth = $pdf->getPageWidth() - ($pdf->getMargins()['left'] + $pdf->getMargins()['right']);
        
        $pdf->SetFont('helvetica', '', 11);
        
        $pdf->Cell(35, 5, 'NAME:', 0, 0, 'R');
        if ($model)
            $pdf->Cell(55, 5, $model ? $model->name : '');
        else
            $pdf->TextField('name', 55, 5, array('readonly' => false,'value' => ''));
        $pdf->Ln(6);

        $pdf->Cell(35, 5, 'ADDRESS:', 0, 0, 'R');
        if ($model)
            $pdf->Cell(55, 5, $model ? $model->address : '');
        else
            $pdf->TextField('address', 55, 5, array('readonly' => false,'value' => ''));
        $pdf->Ln(6);
        
        $pdf->Cell(35, 5, 'CITY:', 0, 0, 'R');
        if ($model)
            $pdf->Cell(55, 5, $model ? $model->city : '');
        else
            $pdf->TextField('city', 55, 5, array('readonly' => false,'value' => ''));
        $pdf->Ln(6);
        
        $pdf->Cell(35, 5, 'STATE:', 0, 0, 'R');
        if ($model)
            $pdf->Cell(55, 5, $model ? $model->state : '');
        else
            $pdf->TextField('state', 55, 5, array('readonly' => false,'value' => ''));
        $pdf->Ln(6);
        
        $pdf->Cell(35, 5, 'CONTACT NAME:', 0, 0, 'R');
        if ($model)
            $pdf->Cell(55, 5, $model ? $model->contact_name : '');
        else
            $pdf->TextField('contact_name', 55, 5, array('readonly' => false,'value' => ''));
        $pdf->Ln(6);
        
        $pdf->Cell(35, 5, 'PHONE 1:', 0, 0, 'R');
        if ($model)
            $pdf->Cell(55, 5, $model ? $model->contact_phone_1 : '');
        else
            $pdf->TextField('contact_phone_1', 55, 5, array('readonly' => false,'value' => ''));
        $pdf->Ln(6);
        
        $pdf->Cell(35, 5, 'PHONE 2:', 0, 0, 'R');
        if ($model)
            $pdf->Cell(55, 5, $model ? $model->contact_phone_2 : '');
        else
            $pdf->TextField('contact_phone_2', 55, 5, array('readonly' => false,'value' => ''));
        $pdf->Ln(6);
        
        $pdf->Cell(35, 5, 'EMAIL:', 0, 0, 'R');
        if ($model)
            $pdf->Cell(55, 5, $model ? $model->email : '');
        else
            $pdf->TextField('email', 55, 5, array('readonly' => false,'value' => ''));
        $pdf->Ln(6);
        
        $pdf->Cell(35, 5, 'WDS CONTACT:', 0, 0, 'R');
        if ($model)
            $pdf->Cell(55, 5, $model ? $model->wds_contact : '');
        else
            $pdf->TextField('wds_contact', 55, 5, array('readonly' => false,'value' => ''));
        $pdf->Ln(6);
        
        $pdf->Cell(35, 5, 'DATE:', 0, 0, 'R');
        if ($model)
            $pdf->Cell(55, 5, $model ? date('Y-m-d', strtotime($model->last_contact_date)) : '');
        else
            $pdf->TextField('last_contact_date', 55, 5, array('readonly' => false,'value' => ''));
        $pdf->Ln(12);
        
        $style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + $pageWidth, $pdf->GetY(), $style);
        $pdf->Ln(6);
        
        $pdf->Cell(35, 5, 'SUMMARY REPORT:');
        $pdf->Ln(6);
        if ($model)
            $pdf->Write(0, $model->comment, '', 0, 'L', true, 0, false, true, 0);
        else
            $pdf->TextField('comment', $pageWidth, 100, array('readonly' => false, 'multiline' => true), array('v' => ''));
        $pdf->lastPage();
    }
    
    #endregion
}
