<?php

/* @var $this ResDedicatedAgencyController */

$this->breadcrumbs=array(
	'Response',
	'Manage Agency Visits' => array('admin'),
    'Search'
);

?>

<div class="container-fluid marginTop20">
    <div class="row-fluid">
        <div class="span6">

            <?php $this->beginWidget('CActiveForm', array(
		        'action' => Yii::app()->createUrl($this->route),
		        'method' => 'post',
	        )); ?>

                <h3>Select a noticed fire or enter a pair of coordinates manually.</h3>
                <br />

                <p><b>Noticed Fires</b> (takes priority)</p>
                <?php echo CHtml::dropDownList('AgencyVisitSearch[fire]', $searchForm->fire_id, CHtml::listData($noticedFires, 'Fire_ID', 'Name'), array('prompt' => ''));  ?>

                <h3 class="marginTop20 marginBottom20">OR</h3>

                <?php echo CHtml::textField('AgencyVisitSearch[lat]', $searchForm->lat, array('placeholder' => 'Lat')); ?>
                <?php echo CHtml::textField('AgencyVisitSearch[lon]', $searchForm->lon, array('placeholder' => 'Lon')); ?>

                <div class="marginTop20">
        	        <?php echo CHtml::submitButton('Search', array('class'=>'submitButton')); ?>
                </div>

            <?php $this->endWidget(); ?>

        </div>
        <div class="span6">

            <?php if ($agencyVisits): ?>

            <div class="table-wrapper container">
                <h2 style="background-color:#222; color:white;"><i>&nbsp;Agency Visits within 100 miles</i></h2>
                <table class="table table-condensed">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Contact Name</th>
                            <th>WDS Contact</th>
                            <th>Last Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agencyVisits as $visit): ?>
                        <tr>
                            <th><?php echo $visit->name; ?></th>
                            <th><?php echo $visit->full_address; ?>n</th>
                            <th><?php echo $visit->contact_name; ?></th>
                            <th><?php echo $visit->wds_contact; ?></th>
                            <th><?php echo date('Y-m-d', strtotime($visit->last_contact_date)); ?></th>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php $ids = implode(',', array_map(function($data) { return $data->id; }, $agencyVisits)); ?>

            <a href="<?php echo $this->createUrl('/resDedicatedAgency/downloadAgencyPdfs', array('ids' => $ids)); ?>" class="btn btn-large">
                <i class=" icon-download-alt"></i> Download PDF
            </a>

            <?php endif; ?>

        </div>
    </div>
</div>