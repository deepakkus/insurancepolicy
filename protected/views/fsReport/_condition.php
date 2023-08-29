<?php
   $htmlTemplatePreviewUrl = Yii::app()->request->baseUrl.'/index.php?r=fsReport/showConditionHTML&condition_num='.$condition->condition_num.'&report_guid='.$fsReport->report_guid;
?>
<tr>
    <td>
        <?php echo CHtml::hiddenField('FSConditions['.$condition->condition_num.'][condition_num]', $condition->condition_num); ?>
        <b><?php echo $condition->getShortType(); ?></b>
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
                if(count($num_photos) > 1)
                {
                    echo '<span class="paddingLeft10">Use Photo: ';
                    echo $form->dropDownList($condition, 'pic_to_use', $num_photos, 
                            array(
                                'name'=>'FSConditions['.$condition->condition_num.'][pic_to_use]',
                                'data-class' => 'fsReportConditionDropDownList'
                            )
                         );
                    echo '</span>';
                }
            } ?>    
    </td>
    <td>
    <?php if(!empty($condition->submitted_photo_path)) : ?>
        <a class="coloredLink" href="javascript:showHtmlTemplatePreview('<?php echo $htmlTemplatePreviewUrl; ?>');">View Preview</a>        
    <?php endif; ?>
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
            echo CHtml::fileField('new_photo_'.$condition->id, '', array('id'=>'new_photo_'.$condition->id, 'accept'=> 'image/*'));
            echo CHtml::submitButton('Upload Photo', array('class'=>'submit'));
        ?>
        </div>
    </td>
</tr>
<tr>
    <td colspan="5"><div class="horizontalLine"></div></td>
</tr>