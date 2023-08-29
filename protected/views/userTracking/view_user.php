<?php

/* @var $this UserTrackingController */
/* @var $model UserTracking */

$this->breadcrumbs = array(
    'User Tracking' => array('admin'),
    'View User',
);

?>

<h1>User Tracking Stats</h1>

<?php

// UserTrackingForm 

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'user-tracking-form',
    'type' => 'horizontal',
    'htmlOptions' => array('class' => 'well')
));

echo $form->errorSummary($userTrackingForm);

echo $form->dropDownListRow($userTrackingForm, 'userID', $userTrackingForm->getTrackedUsers(), array(
    'prompt' => 'Select a user ...'
));

echo $form->datepickerRow($userTrackingForm, 'startDate', array(
    'prepend' => '<i class="icon-calendar"></i>',
    'placeholder' => 'Start Date ...',
    'options' => array(
        'format' => 'yyyy-mm-dd',
        'autoclose' => true,
        'todayHighlight' => true
    )
));

echo $form->datepickerRow($userTrackingForm, 'endDate', array(
    'prepend' => '<i class="icon-calendar"></i>',
    'placeholder' => 'End Date ...',
    'options' => array(
        'format' => 'yyyy-mm-dd',
        'autoclose' => true,
        'todayHighlight' => true
    )
));

echo $form->hiddenField($userTrackingForm, 'route');
echo $form->hiddenField($userTrackingForm, 'platformID');

echo CHtml::submitButton('Search', array('class' => 'submit'));
echo CHtml::link('Cancel', array('admin'), array('class' => 'paddingLeft10'));

$this->endWidget();
unset($form);

?>

<?php if (!is_null($stats)): ?>

<div class="row-fluid">
    <div class="span12">
        <table class="table table-striped table-hover table-condensed">
            <caption><h3>User Stats</h3></caption>
            <thead>
                <tr>
                    <th></th>
                    <th>Visits</th>
                    <th>Route</th>
                    <th>Platform</th>
                    <th>Most Recent View</th>
                    <th>Views</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($stats as $index => $data): ?>
                <tr>
                    <td><?php echo strval($index + 1); ?></td>
                    <td><?php echo $data['count']; ?></td>
                    <td><?php echo $data['route']; ?></td>
                    <td><?php echo $data['platform']; ?></td>
                    <td><?php echo $data['date']; ?></td>
                    <td><?php echo CHtml::link('Views', '#', array('class' => 'views', 'data-platform_id' => $data['platform_id'], 'data-route' => $data['route'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php

Yii::app()->clientScript->registerScript(1, '

    var views = document.getElementsByClassName("views");

    for (var i = 0; i < views.length; i++) {
        views[i].addEventListener("click", function(e) {
            e.preventDefault();
            document.getElementById("' . CHtml::activeId($userTrackingForm, 'route') . '").value = this.getAttribute("data-route");
            document.getElementById("' . CHtml::activeId($userTrackingForm, 'platformID') . '").value = this.getAttribute("data-platform_id");
            var form = document.getElementsByTagName("form")[0];
            form.action = "' . $this->createUrl('/userTracking/viewUserDetails') . '";
            form.submit();
        });
    }

');

?>