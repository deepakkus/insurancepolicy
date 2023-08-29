<?php

/* @var $this RiskVersionController */
/* @var $data RiskVersion */

?>

<div class="row-fluid">
    <div class="span12">
        <div class="version-section">
            <p class="lead">
                <a class="btn btn-primary" title="Update Category" href="<?php echo $this->createUrl('riskVersion/update', array('id' => $data->id)); ?>">
                    <i class="icon-pencil icon-white"></i>
                </a> Version: <?php echo $data->version; ?>
                <a class="pull-right" style="font-size: smaller;" title="Make Live" href="<?php echo $this->createUrl('riskVersion/makeLive', array('id' => $data->id)); ?>">
                    Make Live
                </a>
            </p>
            <hr style="border-color: powderblue;" />
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo $data->getAttributeLabel('year_dataset'); ?></th>
                        <th><?php echo $data->getAttributeLabel('comment'); ?></th>
                        <th><?php echo $data->getAttributeLabel('is_live'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $data->year_dataset; ?></td>
                        <td><?php echo $data->comment; ?></td>
                        <td><?php echo Yii::app()->format->formatBoolean($data->is_live); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>