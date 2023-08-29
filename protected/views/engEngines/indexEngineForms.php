<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
    'Engines'=>array('/engEngines/index'),
	'Engine Forms'
);

?>

<h1>Engine Forms</h1>

<div class="container-fluid">
    <div class="row-fluid">

        <!-- Dedicated Engine Forms -->

        <div class="span6">
            <?php $this->beginWidget('bootstrap.widgets.TbBox', array(
                'title' => 'Dedicated Engine Documents',
                'headerIcon' => 'icon-download-alt',
                'htmlOptions' => array('class' => 'bootstrap-widget-table')
            )); ?>
            <div style="padding: 10px;">
                <?php
                foreach ($dedicatedEngineForms as $form) {
                    echo CHtml::link($form, $this->createUrl('/engEngines/indexEngineForms',  array('fileName' => $form)), array('class' => 'show marginBottom10'));
                }
                echo CHtml::link('Agency Visit Form', $this->createUrl('/resDedicatedAgency/downloadNewAgencyPdf'), array('target' => 'blank'));
                ?>
            </div>
            <?php $this->endWidget(); ?>
        </div>

        <!-- Response Engine Forms -->

        <div class="span6">
            <?php $this->beginWidget('bootstrap.widgets.TbBox', array(
                'title' => 'ResponseService Engine Documents',
                'headerIcon' => 'icon-download-alt',
                'htmlOptions' => array('class' => 'bootstrap-widget-table')
            )); ?>
            <div style="padding: 10px;">
                <?php
                foreach ($responseEngineForms as $form)
                    echo CHtml::link($form, $this->createUrl('/engEngines/indexEngineForms',  array('fileName' => $form)), array('class' => 'show marginBottom10'));
                ?>
            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>

    <div class="row-fluid">

        <!-- CO MOU DOCS -->

        <div class="span6">
            <?php $this->beginWidget('bootstrap.widgets.TbBox', array(
                'title' => 'Colorado MOU Documents',
                'headerIcon' => 'icon-download-alt',
                'htmlOptions' => array('class' => 'bootstrap-widget-table')
            )); ?>
            <div style="padding: 10px;">
                <?php
                foreach ($mouNames as $fileName)
                    echo CHtml::link($fileName, $this->createUrl('/engEngines/indexEngineForms',  array('fileName' => $fileName)), array('class' => 'show marginBottom10'));
                ?>
            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>

