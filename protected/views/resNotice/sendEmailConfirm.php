<?php

    /* @var $this ResNoticeController */
    /* @var $model ResNotice */
    
    $this->breadcrumbs=array(
        'Response' => array('/resNotice/landing'),
        'Notifications' => array('/resNotice/admin'),
        'Send Email'
    );
    
?>

<h1>Send Email Notification to <?php echo ($model->client) ? $model->client->name : 'Error: no client is attached to this notice!'; ?></h1>

<div class ="row">
    <h3>Please insure that:</h3>
    <ul class ="no-type">
        <li>&#10004; All information is accurate and gramatically correct</li>
        <li>&#10004; Notice has been reviewed by DO</li>
        <li>&#10004; Notice has been published with PC/PM review</li>
    </ul>

    <h3>Notice/Fire Information:</h3>
    <ul>
        <li>Fire:<?php if($model->fire) { echo $model->fire->Name; } ?></li>
        <li>Size:<?php if($model->fireObs) { echo $model->fireObs->Size; } ?> acres</li>
        <li>Containment:<?php if($model->fireObs) { echo ($model->fireObs->Containment >=0) ? $model->fireObs->Containment : 'Unknown'; } ?> &#37;</li>
        <li>Suppression:<?php if($model->fireObs) { echo $model->fireObs->Supression; } ?></li>
        <li>Fire Summary:<?php echo $model->comments; ?></li>
        <li>WDS Actions:<?php echo $model->notes; ?></li>
    </ul>
</div>

<div class ="row">
    <?php 
        $this->widget(
            'bootstrap.widgets.TbButton',
            array(
                'size' => 'large',
                'type' => 'primary',
                'label' => 'Send Email',
                'url' => $this->createUrl('/resNotice/sendEmailNotification', array('id'=>$model->notice_id, 'send'=>true)),
                'htmlOptions'=>array('style'=>'margin-right:15px;'),
            )
        ); 
        
        $this->widget(
          'bootstrap.widgets.TbButton',
          array(
              'size' => 'large',
              'type' => 'default',
              'label' => 'Cancel',
              'url' => $this->createUrl('/resNotice/admin')
          )
        ); 
    ?>

</div>
