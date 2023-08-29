<?php
/* @var $this EngSchedulingController */
/* @var $model EngScheduling */
?>

<div style="margin: 10px;">
    <h4 class="marginBottom20"><b>Will overwrite current sorting</b></h4>
    <?php
    foreach ($model->getAvailibleFireClients() as $client)
        echo CHtml::link($client->name, '#', array('class' => 'show marginBottom10 search-client-links', 'data-id' => $client->id));
    ?>
    <div class="clearfix paddingTop10">
        <?php echo CHtml::button('Close', array('id' => 'closeGridSearch', 'class' => 'submitButton')); ?>
    </div>
</div>