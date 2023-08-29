<div class="formContainer">
    <h3>Fire Shield Reports for this Property</h3>
    <?php
        $this->widget('zii.widgets.grid.CGridView', array(
            'id'=>'fs_reports-grid',
            'dataProvider'=>$fs_reports->search(),
            'columns'=>array(
                array(
                    'class'=>'CLinkColumn',
                    'label'=>'Edit',
                    'urlExpression'=>'"index.php?r=fsReport/update&id=".$data->id',
                    'header'=>'Edit',
                ),
                'status',
                'start_date',
                'end_date',
                'risk_level',
                'geo_risk',
				array('name'=>'property_geo_risk', 'value'=>'$data->property->geo_risk',),
                'condition_risk',
            ),
        ));
    ?>
</div>