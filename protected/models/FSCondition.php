<?php

/**
 * This is the model class for table "fs_condition", which stores all the different conditions (i.e., question responses) tied to a FSReport.
 * It pulls default text from FSReportText based on condition_num, but the text could be updated and it would not affect past entries.
 *
 * The followings are the available columns in table 'fs_condition':
 * @property integer $id
 * @property integer $fs_report_id
 * @property integer $condition_num  //used as FSAssessmentQuestion->question_num relation too
 * @property integer $response  //0=yes, 1=no, 2=not sure
 * @property string $after_review_text
 * @property string $did_you_know_text
 * @property string $risk_text
 * @property string $recommendation_text
 * @property string $example_text
 * @property string $example_photo_path
 * @property string $submitted_photo_path
 * @property integer $pic_to_use
 * @property string $notes
 * @property integer $example_image_file_id
 * @property string $question_text  //what the actual question_text was at the time of the asking in the app. used in case it changes over time on the client level
 * @property integer $selected_choices
 * @property integer $set_id
 * @property integer $score
 * @property integer $question_id //FSAssessmentQuestion direct relation.
 */
class FSCondition extends CActiveRecord
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
		return 'fs_condition';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fs_report_id, condition_num, response, after_review_text, did_you_know_text, recommendation_text, risk_text, example_text, example_photo_path, submitted_photo_path, pic_to_use, notes, example_image_file_id, selected_choices, question_text, set_id, score, question_id', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, fs_report_id, condition_num, response, after_review_text, did_you_know_text, recommendation_text, risk_text, example_text, example_photo_path, submitted_photo_path, pic_to_use, notes, example_image_file_id, selected_choices, question_text, set_id, score, question_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'report' => array(self::BELONGS_TO, 'FSReport', 'fs_report_id'),
            'example_image' => array(self::BELONGS_TO, 'File', 'example_image_file_id'),
            'question' => array(self::BELONGS_TO, 'FSAssessmentQuestion', 'question_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'fs_report_id' => 'Report ID',
			'condition_num' => 'Condition #',
			'response' => 'Response',
			'after_review_text' => 'After Review Text',
			'did_you_know_text' => 'Did You Know Text',
            'risk_text' => 'Risk Text',
			'recommendation_text' => 'Action Text',
			'example_text' => 'Example Text',
			'example_photo_path' => 'Example Photo',
			'submitted_photo_path' => 'Submitted Photo: ',
			'pic_to_use' => 'Picture to use in Report: ',
            'notes' => 'Notes',
            'example_image' => 'Example Photo',
            'selected_choices' => 'Selected Choices',
            'question_text' => 'Question Text',
            'set_id' => 'Set ID',
            'score' => 'Score',
            'question_id' => 'Question ID',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('fs_report_id',$this->fs_report_id);
		$criteria->compare('condition_num',$this->condition_num);
		$criteria->compare('response',$this->response);
		$criteria->compare('after_review_text',$this->after_review_text);
		$criteria->compare('did_you_know_text',$this->did_you_know_text);
		$criteria->compare('recommendation_text',$this->recommendation_text);
        $criteria->compare('risk_text', $this->risk_text);
		$criteria->compare('exampe_text',$this->example_text);
		$criteria->compare('example_photo_path',$this->example_photo_path);
		$criteria->compare('submitted_photo_path',$this->submitted_photo_path);
		$criteria->compare('pic_to_use',$this->pic_to_use);
		$criteria->compare('notes',$this->notes);
        $criteria->compare('example_image_file_id', $this->example_image_file_id);
        $criteria->compare('selected_choices', $this->selected_choices);
        $criteria->compare('question_text', $this->question_text);
        $criteria->compare('set_id', $this->set_id);
        $criteria->compare('score', $this->score);
        $criteria->compare('question_id', $this->question_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public function getResponses()
    {
        return json_decode($this->selected_choices);
    }

	public function getType($condition_num = NULL)
	{
        if (!isset($condition_num))
            $condition_num = $this->condition_num;

		if($condition_num == 1)
			return 'Windows';
		if($condition_num == 2)
			return 'Siding';
		if($condition_num == 3)
			return 'Elevated Home';
		if($condition_num == 4)
			return 'Openings';
		if($condition_num == 5)
			return 'Rooftop Covering';
		if($condition_num == 6)
			return 'Rooftop Debris';
		if($condition_num == 7)
			return 'Vents/Openings';
		if($condition_num == 8)
			return 'Gutters';
		if($condition_num == 9)
			return 'Eaves';
		if($condition_num == 10)
			return 'Detached Structure';
		if($condition_num == 11)
			return 'Attached Structure';
		if($condition_num == 12)
			return 'Vegetation';
		if($condition_num == 13)
			return 'Flammable Materials';
		if($condition_num == 14)
			return 'Unmanaged Vegetation';
		if($condition_num == 15)
			return 'Surrounding Terrain';
	}

    /**
     * Returns an abbreviated type used for condition tabs (or other space-constrained areas).
     */
    public function getShortType()
	{
		if($this->condition_num == 1)
			return 'Windows';
		if($this->condition_num == 2)
			return 'Siding';
		if($this->condition_num == 3)
			return 'Elevated';
		if($this->condition_num == 4)
			return 'Openings';
		if($this->condition_num == 5)
			return 'Roof Cover';
		if($this->condition_num == 6)
			return 'Roof Debris';
		if($this->condition_num == 7)
			return 'Openings';
		if($this->condition_num == 8)
			return 'Gutters';
		if($this->condition_num == 9)
			return 'Eaves';
		if($this->condition_num == 10)
			return 'Detached';
		if($this->condition_num == 11)
			return 'Attached';
		if($this->condition_num == 12)
			return 'Veg';
		if($this->condition_num == 13)
			return 'Materials';
		if($this->condition_num == 14)
			return 'Unmanaged';
		if($this->condition_num == 15)
			return 'Terrain';
	}

	public function getYouTubeID()
	{
		if($this->condition_num == 1)
			return 'DcG7cZN2sXI'; // windows
		if($this->condition_num == 2)
			return 'E_7DxfVchXI'; // siding
		if($this->condition_num == 3)
			return 'cTg10vkc7ho'; // elevated home
		if($this->condition_num == 4)
			return 'y7N4n9Y0PaQ';
		if($this->condition_num == 5)
			return 'aqsg67naLbQ'; // rooftop cover
		if($this->condition_num == 6)
			return 'H_I6kpeKQgg'; // roof debris
		if($this->condition_num == 7)
			return 'y7N4n9Y0PaQ'; // openings
		if($this->condition_num == 8)
			return 'sOFg4ZvATLo';
		if($this->condition_num == 9)
			return 'axzOY2ZH43A';
		if($this->condition_num == 10)
			return 'moYuXCES5yA';
		if($this->condition_num == 11)
			return 'ThT-zdCI2R8';
		if($this->condition_num == 12)
			return 'UOzukRkmD54';
		if($this->condition_num == 13)
			return '1t9T8i_G1h8';
		if($this->condition_num == 14)
			return '0GdAQ5zr-l8';
		if($this->condition_num == 15)
			return 'VuhFiaE4gAw';
	}

	public function getSubmittedPhotosArray()
	{
		$returnArray = array();
		if(!empty($this->submitted_photo_path))
		{
			$photos = explode('|', $this->submitted_photo_path);
			foreach($photos as $photo)
			{
				if(!empty($photo))
					$returnArray[] = $photo;
			}
		}
		return $returnArray;
	}

	public function createHTMLTemplate()
	{
		$response = 'No'; //if no
		if($this->response == 0 ) //if Yes
			$response = 'Yes';
		$fsReport = FSReport::model()->findByPk($this->fs_report_id);
		if($fsReport->type == 'fs') //need to round risk levels so text lookups work
			$risk_level = round($fsReport->risk_level);
		else
			$risk_level = $fsReport->risk_level;

		$headerReportText = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Header' AND response LIKE '%".$response."%'");
		if(!isset($headerReportText))
			return false; //error, have to have a report text entry
		$header = $headerReportText->text;

		if(strlen($header) > 45)
			$header = '<div style="width:280px;line-height:1.1em;font-size:15px;padding-left:10px;padding-top:5px;">'.$header.'</div>';
		else
			$header = '<div style="width:280px;line-height:1.1em;font-size:18px;padding-left:10px;padding-top:5px;">'.$header.'</div>';

		$exImg = '
			<div class="image-container right">
				<img style="height:122px;width:122px" class="image" src="img/'.$this->condition_num.'_ex.jpg" />
			</div>';

		$goodNews = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Good News' AND response LIKE '%".$response."%'");
		if(isset($goodNews))
			$goodNews = '<p class="slim">'.str_replace('Good News -', '<span class="strong">Good News -</span>', $goodNews->text).'</p>';
		else
			$goodNews = '';

		$remember = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Remember' AND response LIKE '%".$response."%'");
		if(isset($remember))
			$remember = '<p class="slim">'.str_replace('Remember -', '<span class="strong">Remember -</span>', $remember->text).'</p>';
		else
			$remember = '';

		$afterReview = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'After Review' AND response LIKE '%".$response."%'");
		//"After expert review" text can **only** appear for conditions where a
		//user submitted a photo (ie cases where a user initially answered YES)
		//EXCEPT for conditions 14 & 15... for these two, the text should appear
		//for level 2/3 without a user submitted photo... this is because for
		//conditions 1-13, no "expert review" can occur if the user initially answers
		//NO as no photo will be included to be reviewed  (14/15 can be reviewed using google earth, hence no photo needed)
		if(isset($afterReview) && (in_array($this->condition_num, array(14, 15)) ||  !empty($this->submitted_photo_path)))
			$afterReview = '<p class="slim">'.$afterReview->text.'</p>';
		else
			$afterReview = '';

		$didYouKnow = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Did You Know?' AND response LIKE '%".$response."%'");
		if(isset($didYouKnow))
		{
			$didYouKnow = '<p class="slim">'.str_replace('Did you know?', '<span class="strong">Did you know?</span>', $didYouKnow->text).'</p>';
			$didYouKnow = str_replace('Risk -', '<span class="strong">Risk -</span>', $didYouKnow);
			$didYouKnow = str_replace('Action -', '<span class="strong">Action -</span>', $didYouKnow);
		}
		else
			$didYouKnow = '';

		$recommendation = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Recommendation' AND response LIKE '%".$response."%'");
		if(isset($recommendation))
		{
			$recommendation = '<p class="slim">'.str_replace('Recommendation -', '<span class="strong">Recommendation -</span>', $recommendation->text).'</p>';
			$recommendation = str_replace('Risk -', '<span class="strong">Risk -</span>', $recommendation);
			$recommendation = str_replace('Action -', '<span class="strong">Action -</span>', $recommendation);
		}
		else
			$recommendation = '';

		$html = '
			<!DOCTYPE html>
			<html>
				<head>
					<meta name="viewport" content=" initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
					<title></title>
					<link href="css/style.css" rel="stylesheet" media="screen" type="text/css">
					<script src="fontAdjust.js" type="text/javascript"></script>
				</head>
				<body>
					<div class="background">&nbsp;</div>
					<div class="container">
						<div class="header">
							'.$header.'
						</div>
						<div class="content">
							'.$exImg.$goodNews.$remember.$afterReview.$didYouKnow.$recommendation.'

							<div class="media-container"></div>
						</div>
					</div>
					<script type="text/javascript" language="javascript">
					window.onload = function(){
						window.setTimeout(function(){
							document.getElementsByClassName(\'media-container\')[0].innerHTML = \'<iframe async="true" class="media" src="https://www.youtube.com/embed/'.$this->getYouTubeID().'?autoplay=0" frameborder="0" allowfullscreen></iframe>\';},0);}
					</script>
				</body>
			</html>';

        $file_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'outgoing'.DIRECTORY_SEPARATOR.$this->report->report_guid.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.$this->condition_num.'.html';
        if(file_exists($file_path))
        {
		    if(file_put_contents($file_path, $html) === FALSE)
            {
			    return false;
		    }
		    else
            {
			    return true;
		    }
        }
	}

	public function getPDFTemplateHTML($usaa_ver=false)
	{
		$response = 'No'; //if no
		if($this->response == 0 )  //if Yes
			$response = 'Yes';

		$fsReport = FSReport::model()->findByPk($this->fs_report_id);
		if($fsReport->type == 'fs') //need to round risk levels so text lookups work
			$risk_level = round($fsReport->risk_level);
		else
			$risk_level = $fsReport->risk_level;

		if($response == 'Yes')
		{
			$header = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Header' AND response LIKE '%".$response."%'");
            if (isset($header->text))
            {
                $header = $header->text;
            }

			if($usaa_ver)
			{
				if($this->getRiskLevel() < 9)
				{
					if($this->getRiskLevel() == 1)
						$header = '<h1 align="center">'.$header.' <span style="color:red;font-size:12pt;">('.$this->getRiskLevel().' point)</span></h1>';
					else
						$header = '<h1 align="center">'.$header.' <span style="color:red;font-size:12pt;">('.$this->getRiskLevel().' points)</span></h1>';
				}
				else
					$header = '<h1 align="center" style="background-color:yellow">'.$header.' <span style="color:red;font-size:12pt;">('.$this->getRiskLevel().' points)</span></h1>';
			}
			else
				$header = '<h1 align="center">'.$header.'</h1>';

			//RECOMMENDATION Section
			$submittedPhoto = '';
			if(empty($this->submitted_photo_path)) //if no photo was submitted, then need to show the place holder images
			{
				if($this->condition_num == 1 || $this->condition_num == 8) //these conditions do not require a photo
					$submittedPhoto = '<br /><span style="text-align:center"><img width="250px" height="175px" src="'.Yii::app()->createAbsoluteUrl('site/getImageToken',array('token'=>'123test', 'filepath'=>Yii::app()->basePath.'/fs_reports/pdf_images/no_photo_required.jpg')).'" /></span>';
				else
					$submittedPhoto = '<br /><span style="text-align:center"><img width="250px" height="175px" src="'.Yii::app()->createAbsoluteUrl('site/getImageToken',array('token'=>'123test', 'filepath'=>Yii::app()->basePath.'/fs_reports/pdf_images/no_photo_submitted.jpg')).'" /></span>';
			}
			else //photo was submitted
			{
				$photos = explode('|', $this->submitted_photo_path);
				$photo_index = 0;
				if(isset($this->pic_to_use))
                {
                    if ($this->pic_to_use <= count($photos))
                        $photo_index = $this->pic_to_use - 1;
                }
                $image_path = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$this->report->report_guid.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$photos[$photo_index];
				$submittedPhoto = '<br /><span style="text-align:center">Your submitted photo<br /><img height="175px" src="'.Yii::app()->createAbsoluteUrl('site/getImageToken',array('token'=>'123test', 'filepath'=>$image_path)).'" /></span>';
			}


			$afterReview = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'After Review' AND response LIKE '%".$response."%'");
			//"After expert review" text can **only** appear for conditions where a
			//user submitted a photo (ie cases where a user initially answered YES)
			//EXCEPT for conditions 14 & 15... for these two, the text should appear
			//for level 2/3 without a user submitted photo... this is because for
			//conditions 1-13, no "expert review" can occur if the user initially answers
			//NO as no photo will be included to be reviewed  (14/15 can be reviewed using google earth, hence no photo needed)
			if(isset($afterReview, $afterReview->text) && (in_array($this->condition_num, array(14, 15)) ||  !empty($this->submitted_photo_path)))
				$afterReview = '<p>'.$afterReview->text.'</p>';
			else
				$afterReview = '';

			$didYouKnow = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Did You Know?' AND response LIKE '%".$response."%'");
			if(isset($didYouKnow, $didYouKnow->text))
			{
				$didYouKnow = '<p>'.str_replace('Did you know?', '<b>Did you know?</b>', $didYouKnow->text).'</p>';
				$didYouKnow = str_replace('Risk -', '<b>Risk -</b>', $didYouKnow);
				$didYouKnow = str_replace('Action -', '<b>Action -</b>', $didYouKnow);
			}
			else
				$didYouKnow = '';

			$recommendation = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Recommendation' AND response LIKE '%".$response."%'");
			if(isset($recommendation, $recommendation->text))
			{
				$recommendation = '<p>'.str_replace('Recommendation -', '<b>Recommendation -</b>', $recommendation->text).'</p><br />';
				$recommendation = str_replace('Risk -', '<b>Risk -</b>', $recommendation);
				$recommendation = str_replace('Action -', '<b>Action -</b>', $recommendation);
			}
			else
				$recommendation = '';


			//EXAMPLE Section
			$exampleText = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Example' AND response LIKE '%".$response."%'");
            if (isset($exampleText->text))
            {
                $exampleText = $exampleText->text;
            }

			$example = '
				<span style="text-align:center">
					Example photo<br />';
            if (file_exists(Yii::app()->basePath.'/fs_reports/pdf_images/'.$this->condition_num.'_ex.jpg'))
            {
                $example .= '<img width="250px" height="175px" src="'.Yii::app()->createAbsoluteUrl('site/getImageToken',array('token'=>'123test', 'filepath'=>Yii::app()->basePath.'/fs_reports/pdf_images/'.$this->condition_num.'_ex.jpg')).'" />';
            }
			$example .=	'</span>
				<p>'.str_replace('Example -', '<b>Example -</b>', $exampleText).'</p>
			';

			$html = $header.$afterReview.$submittedPhoto.$didYouKnow.$recommendation.$example;
		}
		elseif($response == 'No') //no response
		{

			$header = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Header' AND response LIKE '%".$response."%'");
            if (isset($header->text))
            {
                $header = $header->text;
            }
			$html = '<table>';
			$html .= '<tr><th colspan="2" style="background-color:#82b753;"><h1 align="center">'.$header.'</h1></th></tr>';

			$goodNews = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Good News' AND response LIKE '%".$response."%'");
			if(isset($goodNews, $goodNews->text))
			    $goodNews = '<tr><td colspan="2" style="background-color:#82b753;">'.str_replace('Good News -', '<b>Good News -</b>', $goodNews->text).'</td></tr>';
			else
				$goodNews = '';
			$html .= $goodNews;

			$exampleText = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Example' AND response LIKE '%".$response."%'");
            if (isset($exampleText->text))
            {
                $exampleText = $exampleText->text;
            }

			$example = '
				<tr><td>';
            if (file_exists(Yii::app()->basePath.'/fs_reports/pdf_images/'.$this->condition_num.'_ex.jpg'))
            {
                $example .= '<img width="250px" height="175px" src="'.Yii::app()->createAbsoluteUrl('site/getImageToken',array('token'=>'123test', 'filepath'=>Yii::app()->basePath.'/fs_reports/pdf_images/'.$this->condition_num.'_ex.jpg')).'" />';
            }
			$example .=	'</td>
				<td><br /><br /><br /><br /><br />'.str_replace('Example -', '<b>Example -</b>', $exampleText).'</td></tr>
			';
			$html .= $example;

			$remember = FSReportText::model()->find("condition_num = '".$this->condition_num."' AND risk_level LIKE '%".$risk_level."%' AND type = 'Remember' AND response LIKE '%".$response."%'");
			if(isset($remember, $remember->text))
				$remember = '<tr><td colspan="2">'.str_replace('Remember -', '<b>Remember -</b>', $remember->text).'</td></tr>';
			else
				$remember = '';
			$html .= $remember;

			$html .= '</table>';
		}

		return $html;
	}

	public function getRiskLevel()
	{
        $fsReport = FSReport::model()->with('agent_property', 'agent', 'client')->findByPk($this->fs_report_id);
		if(isset($fsReport->type) && ($fsReport->type == 'uw' || $fsReport->type == 'edu')) //agent reports (uw or edu)
		{
            if($this->response == 1) //no
                return 0;
            else //yes or not sure
            {
			    $question = FSAssessmentQuestion::model()->findByAttributes(array('question_num' => $this->condition_num, 'client_id' => (isset($fsReport->client))?$fsReport->client->id:$fsReport->user->client_id, 'set_id'=>$this->set_id));
                if(!isset($question))
                    return 0;
                else
			        return $question->yes_points;
            }
		}
        elseif(isset($fsReport->type) && ($fsReport->type == '2.0' || $fsReport->type == 'sl'))
        {
            $return_val = 0;
            $question = FSAssessmentQuestion::model()->findByAttributes(array('question_num' => $this->condition_num, 'client_id' => (isset($fsReport->client))?$fsReport->client->id:$fsReport->user->client_id, 'set_id'=>$this->set_id));
            if(isset($question) && $question->type == 'field')
            {
                $selected_choices = json_decode($this->selected_choices, true);
                if(isset($selected_choices) && is_array($selected_choices))
                {
                    foreach($selected_choices as $choice)
                    {
                        if(isset($choice['value']) && is_numeric($choice['value']))
                            $return_val += $choice['value'];
                    }
                }
            }
            elseif(isset($question) && $question->type == 'condition')
            {
                $return_val = $this->score;
            }
            return $return_val;
        }
        else //fs reports
        {
		    if(in_array($this->condition_num, array(1,3,4,6,7,9,10,15)) && $this->response != 1) //response 0=yes, 1=no, 2=not sure
		    {
			    return 1;
		    }
		    elseif(in_array($this->condition_num, array(8,12,13,14)) && $this->response != 1)
		    {
			    return 3;
		    }
		    elseif(in_array($this->condition_num, array(2,5,11)) && $this->response != 1)
		    {
			    return 9;
		    }
		    else
		    {
			    return 0;
		    }
        }
	}

}