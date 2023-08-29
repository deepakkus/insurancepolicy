<?php

    /* @var $this ResNoticeController */
    /* @var $model ResNotice */

    $this->breadcrumbs=array(
        'Response' => array('/resNotice/landing'),
        'Notifications' => array('/resNotice/admin'),
        'Send Email'
    );
    
?>

<?php if(isset($sendEmail['error']) && $sendEmail['error'] == 0): ?>
    <div class="alert alert-success" role="alert"><h3>Success! Email has been sent to <?php echo ($model->client) ? $model->client->name : 'Error: no client is attached to this notice!'; ?></h3></div>
<?php else: ?>
    <div class="alert alert-error" role="alert"><h3>Error! <?php echo (isset($sendEmail['error'])) ? $sendEmail['errorMessage'] : 'Email was not sent.'; ?></h3></div>
<?php endif; ?>

<div class ="row">

    <?php 
        $this->widget(
            'bootstrap.widgets.TbButton',
            array(
                'size' => 'large',
                'type' => 'success',
                'label' => 'Return to Notice Management',
                'url' => $this->createUrl('/resNotice/admin', array('send'=>1, 'id'=>$model->notice_id)),
                'htmlOptions'=>array('style'=>'margin-right:15px;'),
            )
        ); 

    ?>

</div>