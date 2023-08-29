<?php
/**
 * This is the model class for table "fs_contact_us", which stores info coming in from the app
 * for people who don't have a carrier key and get redirected to a Contact Us form.
 *
 * The followings are the available columns in table 'fs_contact_us':
 * @property integer $id
 * @property string $email
 * @property string $from
 * @property string $timestamp
 * @property string $status
 * @property string $provider
 */
class FSContactUs extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return fs the static model class
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
		return 'fs_contact_us';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
                    array('email, from, status, provider', 'required'),
                    array('email', 'email'),
                    array('email', 'length', 'max' => 100),
                    array('from, provider', 'length', 'max' => 50),
                    array('status', 'in', 'range' => $this->getStatuses()),
                    array('timestamp', 'default',
                        'value' => new CDbExpression('GETDATE()'),
                        'setOnEmpty' => false, 'on' => 'insert'),
                    // The following rule is used by search().
                    // Please remove those attributes that should not be searched.
                    array('id, email, from, timestamp, status, provider', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
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
			'email' => 'Email',
			'from' => 'From',
			'timestamp' => 'Date/Time',
			'status' => 'Status',
			'provider' => 'Provider',
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('email',$this->email);
		$criteria->compare('[from]',$this->from);
		$criteria->compare('timestamp',$this->timestamp);
		$criteria->compare('status',$this->status);
		$criteria->compare('provider', $this->provider);

		return new CActiveDataProvider($this, array(
                    'criteria'=>$criteria,
                    'pagination'=>array('pageSize'=>50),
		));
	}

    public function sendConfirmationEmail()
    {
        $result = true;
        if($this->provider == 'USAA')
        {
            $usaa_mem = Member::model()->with('properties')->find('(email_1=:email_1 OR email_2=:email_2) AND client=:client', array(':email_1'=>$this->email, ':email_2'=>$this->email, ':client'=>'USAA'));
            if(isset($usaa_mem)) //found the usaa member tied to this email. Set all proerties to eligible and send them usaa specific email with reg code
            {
                foreach($usaa_mem->properties as $prop)
                {
                    if($prop->policy_status == 'active' && $prop->fireshield_status != 'ineligible')
                    {
                        $property = Property::model()->findByPk($prop->pid);
                        $property->fireshield_status = 'offered';
                        $property->fs_status_date = date('Y-m-d H:i:s');
                        $property->save();
                    }
                }
                //setup email to member with reg code with $usaa_mem->fs_carrier_key
                $body = 'Dear '.$usaa_mem->first_name.' '.$usaa_mem->last_name.",\r\n\r\n";
                $body .= "Thank you for your interest in the WDSpro app*. Here is your personal registration code to access and complete the assessment: ".$usaa_mem->fs_carrier_key."\r\n\r\n";
                $body .= "Within a week after you take the assessment, your personalized protection plan will be ready on your mobile device. Many of the plan’s recommendations are low- or no-cost preventive measures, and some are as easy as cutting back vegetation and clearing debris from gutters.\r\n\r\nThank you for the opportunity to provide solutions to help protect you from wildfires**.\r\n\r\nWildfire Defense Systems\r\n\r\n\r\n* The WDSpro app is owned and provided by WDS; by using the app, you will be subject to its terms and conditions, including the privacy policy. The program is provided on an “as is” and “as available” basis. To the fullest extent permissible by applicable law, USAA disclaims all warranties, express or implied, including, but not limited to, implied warranties of merchantability and fitness for a particular purpose. \r\n\r\n** There are no guarantees that mitigation steps taken as a result of the wildfire-protection plan provided by WDS will prevent damage.  USAA is not liable to you or anyone else for damages arising out of reliance on or use of the app, including, but not limited to, direct, indirect, incidental, punitive, and consequential damages.";
            }
            else //didnt find email
            {
                //setup non-match usaa specific confirmation email
                $body = "Thank you for your interest in the WDSpro app*. \r\n\r\nWe were unable to match your email address. To participate in this program, your eligible property** must be located in one of the below states. \r\n\r\nEligible States: AZ, CA, CO, ID, MT, NV, NM, OR, TX, UT, WA and WY.\r\n\r\nIf you have any questions about participation in this program, please call USAA at 800-531-USAA (8722).\r\n\r\nThank you,\r\n\r\n Wildfire Defense Systems\r\n\r\n\r\n* The WDSpro app is owned and provided by WDS; by using the app, you will be subject to its terms and conditions, including the privacy policy. The program is provided on an “as is” and “as available” basis. To the fullest extent permissible by applicable law, USAA disclaims all warranties, express or implied, including, but not limited to, implied warranties of merchantability and fitness for a particular purpose. \r\n\r\n** Condos, townhomes, cooperatives, apartments and mobile homes are not eligible for this program.";
            }
        }
        else //setup generic confirmation email
        {
            $body = "Thank you for your interest in the WDSpro mobile application. Unfortunately, your email is not associated with a pre-approved use of the application. Please contact your insurance carrier for more information or email jamidon@wildfire-defense.com.";
            $body .= "\r\n\r\nThank you,\r\n\r\nWildfire Defense Systems";
        }

        //send the confirmation email
        Yii::import('application.extensions.phpmailer.JPhpMailer');
        $mail = new JPhpMailer;
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = Yii::app()->params['emailHost'];
        $mail->Port = Yii::app()->params['emailPort'];
        $mail->Username = Yii::app()->params['emailUser'];
        $mail->Password = Yii::app()->params['emailPass'];
        $mail->SetFrom(Yii::app()->params['emailUser'], 'Wildfire Defense Systems');
        $mail->Subject = 'WDSpro Registration Code Request';
        $mail->Body = $body;
        $mail->AddAddress($this->email);
        if($mail->Send())
            $result = true; // success
        else
            $result = false; //error

        
        return $result;
    }
        
        /**
         * Returns FSContactUs status types.
         */
        public function getStatuses()
        {
            return array('Contacted', 'Duplicate', 'New');
        }
}
