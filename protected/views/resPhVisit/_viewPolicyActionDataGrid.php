<?php

Yii::app()->format->dateFormat = 'Y-m-d';
Yii::app()->format->numberFormat = array('decimals' => 2, 'decimalSeparator' => '.', 'thousandSeparator' => ',');

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'resPolicyAction-grid',
    'dataProvider' => $dataProvider,
    'ajaxUpdate' => true,
    'filter' => null,
    'nullDisplay' => '<span class="not-set">(not set)</span>',
    'columns' => array(
        array(
            'header' => 'First Name',
            'name' => 'first_name'
        ),
        array(
            'header' => 'Last Name',
            'name' => 'last_name'
        ),
        array(
            'header' => 'Mem. Num',
            'name' => 'member_num'
        ),
        array(
            'header' => 'Policy',
            'name' => 'policy'
        ),
        array(
            'header' => 'Address',
            'name' => 'address_line_1'
        ),
        array(
            'header' => 'City',
            'name' => 'city'
        ),
        array(
            'header' => 'State',
            'name' => 'state'
        ),
        array(
            'header' => 'Response Status',
            'name' => 'response_status'
        ),
        array(
            'header' => 'Threat',
            'name' => 'threat',
            'type' => 'raw',
            'value' => function($data)
            {
                if ($data['threat'] === '1')
                {
                    return '<span style = "color:red;">yes</span>';
                }
                elseif ($data['threat'] === '0')
                {
                    return 'no';
                }

                return null;
            }
        ),
        array(
            'header' => 'Distance',
            'name' => 'distance',
            'type' => 'number'
        ),
        array(
            'header' => 'Action Date',
            'name' => 'date_action',
            'type' => 'date'
        )

    )
));
