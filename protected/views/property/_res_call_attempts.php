<div class="formContainer">
    <h3>Response Call Attempts for this Property</h3>
    <?php
        $this->widget('zii.widgets.grid.CGridView', array(
            'id'=>'res_call_attempts-grid',
            'dataProvider'=>$call_attempts->search(),
            'columns'=>array(
                array(
                    'class'=>'CLinkColumn',
                    'label'=>'View',
                    'urlExpression'=>'"index.php?r=resCallList/update&id=".$data->call_list_id',
                    'header'=>'Call Attempts',
                    'linkHtmlOptions' => array('target' => '_blank')
                ),
                'attempt_number',
                'date_called',
                'caller_user_name',
                'point_of_contact', 
                'point_of_contact_description', 
                'in_residence:boolean', 
                'evacuated:boolean',
                'publish:boolean'
            )
        ));
    ?>
</div>