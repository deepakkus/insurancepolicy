<?php
/* @var $this ResDedicatedController */
/* @var $model ResDedicated */

$this->breadcrumbs=array(
	'Dedicated Analytics' => array('/resDedicated/index'),
    'Dedicated Analytics All Clients'
);

Assets::registerChartsJSPackage();

Yii::app()->clientScript->registerCss(1, '

    .table-fires {
        margin-bottom: 25px;
    }

    .table-fires th { 
        border-top: none !important;
    }
'
);

$this->renderPartial('//site/indexAnalyticsNav'); 

?>

<h1 class="center">Dedicated Service Analytics</h1>

<div class ="row-fluid">
    <div class ="span12">
        <div class="form" style="background-color:#FFFFFF; border: 0;">
            <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	            'id'=>'res-dedicated-anlytics-form'
            )); ?>
            <div>
                <?php echo CHtml::label('Start Date', ''); ?>
                <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'ResDedicatedAnalytics[client_start_date]', 'value' => date('m/d/Y',strtotime($clientstartdate)) )); ?>
            </div>
            <div>
                <?php echo CHtml::label('End Date', ''); ?>
                <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'ResDedicatedAnalytics[client_end_date]', 'value' => date('m/d/Y',strtotime($clientenddate)) )); ?>
            </div>
            <div class="buttons">
                <?php echo CHtml::submitButton('Submit', array('class'=>'submit')); ?>
            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>

<br />
<br />
<br />
<br />

<h2 class="center">Month Breakdown</h2>
<p class="center"><b><i>(One Dedicated Service Day is 8 Hours.)</i></b></p>

<?php foreach(array_chunk($dedicatedHoursMonthBreakdown, 2, true) as $dedicatedStatsArray): ?>
    <div class="row-fluid">
        <?php foreach($dedicatedStatsArray as $dedicated): ?>
        <div class="span6">
            <h2><small> Month: <?php echo date('F Y', strtotime($dedicated['dedicated_date'])) ?></small></h2>
            <div class="table-responsive">
                <table class="table table-fires">
                    <tr>
                        <th style="padding: 5px;">State</th>
                        <th style="padding: 5px;">Days Used</th>
                        <th style="padding: 5px;">State</th>
                        <th style="padding: 5px;">Days Used</th>
                    </tr>

                    <?php foreach(array_chunk($dedicated['dedicated_states'], 2, true) as $values): ?>
                    <tr>
                        <?php foreach($values as $key => $value): ?>
                        <td class="td-padding"><?php echo $key ?></td>
                        <td class="td-padding"><?php echo round($value / 8, 2) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>

                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>