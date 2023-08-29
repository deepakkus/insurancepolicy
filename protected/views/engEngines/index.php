<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
	'Engines'
);

Yii::app()->clientScript->registerCssFile('/css/engEngines/index.css');
Yii::app()->bootstrap->init();
?>

<div class="container-fluid" style="margin-bottom: 200px;">

    <div class="jumbotron">
        <h1>Engine Management System</h1>
        <p class="lead">Click the links below to <i>manage engines.</i></p>
    </div>

    <div class="row-fluid">
        <div class="span6 center">
            <h2>Scheduling</h2>
            <p class="text-info text-large">Schedule engines, set statuses, and schedule crew members.</p>
            <p>
                <a href="<?php echo $this->createUrl('/engScheduling/admin') ?>" class="btn btn-primary btn-large">Scheduling &raquo;</a>
            </p>
        </div>
        <div class="span6 center">
            <h2>Crew Management</h2>
            <p class="text-info text-large">Manage WDS Engine Bosses and Crew</p>
            <p><a href="<?php echo $this->createUrl('/engCrewManagement/admin') ?>" class="btn btn-primary btn-large">Crew Management &raquo;</a></p>
        </div>
    </div>

    <!--
    <div class="row-fluid">
        <div class="span12 center">
            <h2>Shift Tickets</h2>
            <p class="text-info text-large">Review Engine Crew Shift Tickets.</p>
            <p>
                <a href="<?php //echo $this->createUrl('/engShiftTicket/admin') ?>" class="btn btn-primary btn-large btn-engine">Review &raquo;</a>
            </p>
        </div>
    </div>
    -->

    <hr class="engine-separator" />

    <div class="row-fluid">
        <div class="span6 center">
            <h2>Manage Engines</h2>
            <p class="text-info text-large">Add and edit engines to the WDS and Alliance engine fleet.</p>
            <p><a href="<?php echo $this->createUrl('/engEngines/admin') ?>" class="btn btn-primary btn-large">Manage Engines &raquo;</a></p>
        </div>
        <div class="span6 center">
            <h2>Manage Alliance Partners</h2>
            <p class="text-info text-large">Manage Alliance Partner Information</p>
            <p><a href="<?php echo $this->createUrl('/alliance/admin') ?>" class="btn btn-primary btn-large">Alliance &raquo;</a></p>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12 center">
            <h2>Engine Analytics</h2>
            <p class="text-info text-large">Engine Analytics and a map view of where todays engines are.</p>
            <p>
                <a href="<?php echo $this->createUrl('/engEngines/indexAnalytics') ?>" class="btn btn-primary btn-large btn-engine">Analytics &raquo;</a>
                <a href="<?php echo $this->createUrl('/engEngines/indexAnalyticsMap') ?>" class="btn btn-primary btn-large btn-engine">Engines Map &raquo;</a>
            </p>
        </div>
    </div>

    <hr class="engine-separator" />

    <div class="row-fluid">
        <div class="span6 center">
            <h2>Engine Documents</h2>
            <p class="text-info text-large">Various Documents for WDS Field Crews</p>
            <p><a href="<?php echo $this->createUrl('/engEngines/indexEngineForms'); ?>" class="btn btn-primary btn-large">Engine Documents &raquo;</a></p>
        </div>
        <div class="span6 center">
            <h2>Response Engine Document<br />Generators</h2>
            <p class="text-info text-large">Resource Orders, Agency Visits.</p>
            <?php $this->widget('bootstrap.widgets.TbButtonGroup', array(
                'size' => 'large',
                'type' => 'primary',
                'htmlOptions' => array('style' => 'margin: 0 auto; display: inline-block; text-align: left;'),
                'buttons' => array(
                    array(
                        'label' => 'Response Engine Forms',
                        'items' => array(
                            array('label' => 'Agency Visits', 'url' => array('/resDedicatedAgency/admin'))
                        )
                    )
                )
            )); ?>
        </div>
    </div>

</div>