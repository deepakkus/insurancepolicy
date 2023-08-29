<?php
    $imagesPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$report_guid.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
    $imgURL = Yii::app()->request->baseUrl . '/index.php?r=site/getImage&filepath='.urlencode($imagesPath.$photo_name);
?>
<div class="floatLeft">
    <div><b><?php echo $i; ?></b></div>
    <?php echo CHtml::ajaxButton ("",
            CController::createUrl('fsReport/RemoveConditionPhoto', array('condition_id'=>$condition_id, 'photo_name_to_delete'=>$photo_name)), 
            array('update' => '#condition_'.$condition_id.'_photo_'.$i), 
            array('class' => 'deleteImageButton', 'onclick' => 'if (!confirm("Are you sure you want to delete this photo?")) { return; }'));
    ?>
</div>
<div class="floatLeft paddingLeft5">
    <a href="<?php echo $imgURL; ?>" target="_blank">
        <img src="<?php echo $imgURL; ?>" style="height: 90px">
    </a>
</div>