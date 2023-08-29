<?php $this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'Call List' => array('/resCallList/admin'),
    'Create'
); ?>

<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/resCallList/create.js'); ?>

<div class="padding20">
    <h1>Add To Call List</h1>
    <?php $this->renderPartial('_formAddToCallList', array(
        'model'=>$model,
        'fireslist' => $fireslist,
        'clientslist' => $clientslist,
        'dataProvider' => $dataProvider,
        'columnsArray' => $columnsArray
    )); ?>
</div>