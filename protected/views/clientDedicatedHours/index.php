<?php

/* @var $this ClientDedicatedHoursController */
/* @var $dedicatedForm EngineReportDayForm */

/* @var $resultsTotals array */
/* @var $resultsTotalsWithClients array */
/* @var $dedicatedPools array */
/* @var $dedicatedHoursForClient array */

$this->breadcrumbs=array(
	'Dedicated Anlytics' => array('/resDedicated/index'),
    'Shift Ticket Dedicated Anlytics'
);

$script = <<<JAVASCRIPT

    $(function() {

        var hash = window.location.hash;
        if (hash) {
            $('#dedicated-service-hours-tabs a[href="' + hash + '"]').tab('show');
        }

        $('#dedicated-service-hours-tabs a').click(function(e) {
            history.pushState(null, null, this.href);
        });

    });

JAVASCRIPT;

$clientScript = Yii::app()->clientScript->registerScript('dedicated-service-hours-script', $script);

$this->renderPartial('//site/indexAnalyticsNav');

?>

<h2>Dedicated Hours</h2>

<div class="row-fluid">
    <div class="span6">

<?php

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'dedicated-service-hours-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well',
        'style' => 'overflow: hidden;'
    )
));

echo $form->datepickerRow($dedicatedForm, 'startDate', array(
    'prepend' => '<i class="icon-calendar"></i>',
    'placeholder' => $dedicatedForm->getAttributeLabel('startDate') . ' ...',
    'options' => array(
        'format' => 'yyyy-mm-dd',
        'autoclose' => true,
        'todayHighlight' => true
    )
));

echo $form->datepickerRow($dedicatedForm, 'endDate', array(
    'prepend' => '<i class="icon-calendar"></i>',
    'placeholder' => $dedicatedForm->getAttributeLabel('endDate') . ' ...',
    'options' => array(
        'format' => 'yyyy-mm-dd',
        'autoclose' => true,
        'todayHighlight' => true
    )
));

echo $form->dropDownListRow($dedicatedForm, 'clientID', $dedicatedServiceClients);

echo CHtml::submitButton('Search', array('class' => 'submit'));

$this->endWidget();

$dedicatedHours = $dedicatedForm->GetMostRecentDedicatedHourPools();

?>

    </div>
    <div class="span6">

        <p class="lead">Most recent allocated dedicated service hours by client</p>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td><strong>Hours Allowed</strong></td>
                        <td><strong>Dedicated Year</strong></td>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    foreach ($dedicatedHours as $hours)
                    {
                        echo '<tr>
                            <td>' . $hours->name . '</td>
                            <td>' . $hours->dedicated_hours . '</td>
                            <td>' . date('Y', strtotime($hours->dedicated_start_date)) . '</td>
                        </tr>';
                    }

                    ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php

$this->widget('bootstrap.widgets.TbTabs', array(
    'type' => 'tabs',
    'id' => 'dedicated-service-hours-tabs',
    'tabs' => array(
        array(
            'id' => 'totals',
            'label' => 'Totals',
            'content' => $this->renderPartial('_index_totals', array(
                'resultsTotals' => $resultsTotals,
                'resultsTotalsWithClients' => $resultsTotalsWithClients,
                'dedicatedServiceClients' => $dedicatedServiceClients
            ), true),
            'active' => true
        ),
        array(
            'id' => 'client',
            'label' => 'By Client',
            'content' => $this->renderPartial('_index_client', array(
                'dedicatedPools' => $dedicatedPools,
                'dedicatedHoursForClient' => $dedicatedHoursForClient,
                'dedicatedForm' => $dedicatedForm
            ), true)
        )
    )
));