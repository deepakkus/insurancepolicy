<?php
    $htmlTemplatePreviewUrl = Yii::app()->request->baseUrl.'/index.php?r=fsReport/showConditionHTML&condition_num='.$condition->condition_num.'&report_guid='.$fsReport->report_guid;
	$question = FSAssessmentQuestion::model()->findByAttributes(array('question_num' => $condition->condition_num, 'client_id' => $fsReport->client->id));
    
    if(!isset($question)) //question was not found based on criteria from report payload
    {
        echo '<span style="color:red;">ERROR: The passed in report payload contained a condition/question number that didnt exist for the client (question_num: '.$condition->condition_num.', client_id: '.$fsReport->client->id.'</span><br />';   
    }
    else
    {
?>
<tr>
    <td>
        <?php echo CHtml::hiddenField('FSConditions['.$condition->condition_num.'][condition_num]', $condition->condition_num); ?>
        <b><?php echo $question->title; ?></b>
    </td>
    <td>
        <?php 
            echo $form->dropDownList($condition, 'response', 
                    array(
                        '0' => 'Yes', 
                        '1' => 'No', 
                        'Not Sure',
                    ), 
                    array(
                        'name' => 'FSConditions['.$condition->condition_num.'][response]', 
                        'data-class' => 'fsReportConditionDropDownList'
                    )
            );
            
            if(!empty($condition->submitted_photo_path)) {
                $num_photos = range(1,count(explode('|', rtrim($condition->submitted_photo_path,'|'))));
                $num_photos = array_combine($num_photos, $num_photos);
            } ?>    
    </td>
	<td>
		<?php
        $risk_text = $question->risk_text; //default (canned text)
		if(!empty($condition->risk_text))
			$risk_text = $condition->risk_text;
        echo $form->labelEx($condition,'risk_text');
		echo $form->textArea($condition, 'risk_text', array('name' => 'FSConditions['.$condition->condition_num.'][risk_text]', 'value'=>$risk_text, 'rows'=>4, 'maxlength'=>1000,));
        
		$rec_text = $question->rec_text; //default (canned text)
		if(!empty($condition->recommendation_text))
			$rec_text = $condition->recommendation_text;
        echo $form->labelEx($condition,'recommendation_text');
		echo $form->textArea($condition, 'recommendation_text', array('name' => 'FSConditions['.$condition->condition_num.'][recommendation_text]', 'value'=>$rec_text, 'rows'=>4, 'maxlength'=>1000,));
		echo '<br>Assessor Notes: <br>'.$form->textArea($condition, 'notes', array('name' => 'FSConditions['.$condition->condition_num.'][notes]', 'rows'=>4, 'maxlength'=>500,)); 
        $ex_text = $question->example_text; //default (canned text)
		if(!empty($condition->example_text))
			$ex_text = $condition->example_text;
        echo '<br>Example Text: <br>'.$form->textArea($condition, 'example_text', array('name' => 'FSConditions['.$condition->condition_num.'][example_text]', 'value'=>$ex_text, 'rows'=>4, 'maxlength'=>500, )); 
        $ex_photo_file_id = $question->example_image_file_id;
        if(!empty($condition->example_image_file_id))
            $ex_photo_file_id = $condition->example_image_file_id;
        echo '<br>Example Image: <br>';
        echo '<img src="'.Yii::app()->request->baseUrl.'/index.php?r=file/loadFile&id='.$ex_photo_file_id.'" />';
        echo CHtml::fileField('example_image_'.$condition->id, '', array('id'=>'example_image_'.$condition->id));
        echo CHtml::submitButton('Upload Custom Example Image', array('class'=>'submit'));
        ?>
	</td>
	
	<td>
		<?php echo $question->yes_points; ?>
	</td>

    <td>
        <?php
        if(!empty($condition->submitted_photo_path))
        {
            $i = 1;
            foreach($condition->getSubmittedPhotosArray() as $photo_name)
            {
                echo '<div class="conditionPhotoBox" id="condition_'.$condition->id.'_photo_'.$i.'">';
                    $this->renderPartial('_conditionPhoto', array('report_guid'=>$fsReport->report_guid, 'photo_name'=>$photo_name, 'i'=>$i, 'condition_id'=>$condition->id));
                echo '</div>';
                $i++;
            }
        }
        else 
        {
            echo '<i>No photos were submitted</i>';
        }
        ?>
    </td>
    <td>
        <a href="javascript:void(0);" onclick="togglePhotoAddRow(<?php echo $condition->id; ?>);">Add Photo</a>
    </td>
</tr>   
<tr>
    <td colspan="3"></td>
    <td colspan="2">
        <div id="addPhotoRow_<?php echo $condition->id; ?>" style="display:none;">
        <?php
            echo CHtml::fileField('new_photo_'.$condition->id, '', array('id'=>'new_photo_'.$condition->id));
            echo CHtml::submitButton('Upload Photo', array('class'=>'submit'));
        ?>
        </div>
    </td>
</tr>
<tr>
    <td colspan="5"><div class="horizontalLine"></div></td>
</tr>

<?php
    } //end else check to make sure question exists
?>