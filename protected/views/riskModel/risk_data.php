<?php

/* @var $this RiskModelController */
/* @var $form CActiveForm */
/* @var $model RiskDataForm */

$this->breadcrumbs=array(
	'Risk Data',
);

$isData = (!is_null($tabular_data) && !is_null($tabular_data_clusters)) ? true : false;

Yii::app()->clientScript->registerScript(1,'

    var form = document.getElementsByTagName("form")[0];
    var downloadbtn = document.getElementById("download");
    if(downloadbtn)
    {
            downloadbtn.addEventListener("click", function() 
            {
                form.action = "' . $this->createUrl($this->route) . '&export=1";
                form.submit();
                form.action = "' . $this->createUrl($this->route) .'";
                return false;
            });
    }
');

?>

<h1>Get Risk Data</h1>

<div class="form" style="float: inherit;">

    <?php $form=$this->beginWidget('CActiveForm', array(
	    'id' => 'risk-query-data-form',
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true
         )
    )); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="clearfix paddingTop20 paddingBottom20">

	    <div class="fluidField">
		    <?php echo $form->labelEx($model, 'lat'); ?>
		    <?php echo $form->textField($model, 'lat', array('placeholder' => 'Ex: 40.4215', 'pattern' => '\d{2,3}\.{1}\d+')); ?>
		    <?php echo $form->error($model, 'lat'); ?>
	    </div>

        <div class="fluidField">
		    <?php echo $form->labelEx($model, 'lon'); ?>
		    <?php echo $form->textField($model, 'lon', array('placeholder' => 'Ex: -120.45124', 'pattern' => '-\d{2,3}\.{1}\d+')); ?>
		    <?php echo $form->error($model, 'lon'); ?>
	    </div>

    </div>

    <div class="buttons">
		<?php echo CHtml::submitButton('Submit', array('class'=>'submit')); ?>
        <?php if ($isData): ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Download CSV', '#', array('id' => 'download')); ?>
        </span>
        <?php endif; ?>
	</div>

    <?php $this->endWidget(); ?>

</div>

<?php if ($isData): ?>

<?php

// Generate anchor links to hook together risk data and any clusters that may exist
$anchorLinks = '<ul>';
$anchorLinks .= '<li>' . CHtml::link('<b>Risk Data</b>', '#risk') . '</li>';
foreach ($tabular_data_clusters as $key => $cluster)
    $anchorLinks .= '<li>' . CHtml::link('<b>Cluster ' . ($key + 1) . '</b> - ' . count($cluster) . ' Points', '#cluster' . ($key + 1)) . '</li>';
$anchorLinks .= '</ul>';

?>

<div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #333333;">

    <div id="summary">
        <h3>There are <?php echo count($tabular_data); ?> risk points and <?php echo count($tabular_data_clusters); ?> clusters.</h3>
    </div>

    <!-- Risk Data Table -->

    <a name="<?php echo 'risk'; ?>"></a>
    <?php echo $anchorLinks; ?>
    <table class="table table-striped table-hover table-condensed" style="width: 70%;">
        <caption><h3>Risk Data (<?php echo count($tabular_data) . ' Points'; ?>)</h3></caption>
        <thead>
            <tr>
                <th>Distance</th>
                <th>Inverse Distance</th>
                <th>Risk</th>
                <th>Flame Length</th>
                <th>Crown</th>
                <th>Slope</th>
                <th>VCC</th>
                <th>VDEP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($tabular_data as $data): ?>
            <tr>
                <td><?php echo $data->distance; ?></td>
                <td><?php echo $data->inverse_distance; ?></td>
                <td><?php echo $data->risk; ?></td>
                <td><?php echo $data->flame_length; ?></td>
                <td><?php echo $data->crown; ?></td>
                <td><?php echo $data->slope; ?></td>
                <td><?php echo $data->vcc; ?></td>
                <td><?php echo $data->vdep; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Risk Data Clusters Tables -->

    <?php if (count($tabular_data_clusters)): ?>

    <?php foreach($tabular_data_clusters as $key => $cluster): ?>

    <a name="<?php echo 'cluster' . ($key + 1); ?>"></a>
    <?php echo $anchorLinks; ?>
    <table class="table table-striped table-hover table-condensed" style="width: 70%;">
        <caption><h3>Cluster <?php echo $key + 1 . ' (' . count($cluster) . ' Points)'; ?></h3></caption>
        <thead>
            <tr>
                <th>Distance</th>
                <th>Inverse Distance</th>
                <th>Risk</th>
                <th>Flame Length</th>
                <th>Crown</th>
                <th>Slope</th>
                <th>VCC</th>
                <th>VDEP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cluster as $data): ?>
            <tr>
                <td><?php echo $data->distance; ?></td>
                <td><?php echo $data->inverse_distance; ?></td>
                <td><?php echo $data->risk; ?></td>
                <td><?php echo $data->flame_length; ?></td>
                <td><?php echo $data->crown; ?></td>
                <td><?php echo $data->slope; ?></td>
                <td><?php echo $data->vcc; ?></td>
                <td><?php echo $data->vdep; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php endif; ?>