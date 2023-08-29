<?php

Yii::app()->clientScript->registerCss(1, "

    .coordinate-coversion {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .coordinate-coversion ul{
        list-style-type: none;
        margin: 0;
        padding: 0;
    }
    .form input[type='text'] {
        width: 100px;
    }

");

Yii::app()->clientScript->registerScript(1, "

    $('.coordinate-conversion').click(function(e) {
        e.preventDefault();
        $('#modal-content').css('display', 'block');
        $('#modal-container').dialog('open');
    });

");

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/convert-coordinates.js');

$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'modal-container',
    'options' => array(
        'title' => 'Coordinate Conversion',
        'autoOpen' => false,
        'closeText' => false,
        'modal' => true,
        'buttons' => array(
            'OK' => 'js:function() { $(this).dialog("close"); }'
        ),
        'show' => array(
            'effect' => 'drop',
            'duration' => 300,
            'direction' => 'up'
        ),
        'hide' => array(
            'effect' => 'fadeOut',
            'duration' => 300
        ),
        'width' => 800,
        'resizable' => false,
        'draggable' => true
    )
));

?>

<div id="modal-content" style="display: none;">
    <div class ="form">
        <h3>Conversions</h3>
        <div class="coordinate-coversion">
            <ul>
			    <li style="margin-bottom:10px;"><b>Degrees Minutes Seconds</b></li>
			    <li>
                    <table>
                        <tr>
                            <td>Lat - D M S:</td>
                            <td><input type="text" name="dDmsLat" maxlength ="2" size ="2" id="dDmsLat"/></td>
                            <td><input type="text" name="dDmsLat" maxlength="2" size="2" id ="mDmsLat"/></td>
                            <td><input type="text" name="sDmsLat" maxlength="2" size="2" id ="sDmsLat"/><br /></td>
                        </tr>
                        <tr>
                            <td>Long - D M S:</td>
                            <td><input type="text" name="dDmsLong" maxlength="4" size="4" id="dDmsLong"/></td>
                            <td><input type="text" name="dDmsLong" maxlength="2" size="2" id="mDmsLong"/></td>
                            <td><input type="text" name="sDmsLong" maxlength="2" size="2" id="sDmsLong"/></td>
                        </tr>
                    </table>
			    </li>
			    <li style="margin-bottom:10px;">
                    <a href="#" style="color: blue;" onclick="convertDMS(event)">Convert DMS</a>
			    </li>
			    <li style="margin-bottom:10px;"><b>Degrees Decimal Minutes</b></li>
			    <li>
                    <table>
                        <tr>
                            <td>Lat -D M:</td>
                            <td><input type="text" id="d_ddm" size="2"/></td>
                            <td><input type="text" id="m_ddm" size="5"/></td>
                        </tr>
                        <tr>
                            <td>Long -D M:</td>
                            <td><input type="text" id="dl_ddm" size="4"/></td>
                            <td><input type="text" id="ml_ddm" size="5"/></td>
                        </tr>
                    </table>
			    </li>
			    <li>
                    <a href="#" style="color: blue;" onclick="convertDDM(event)">Convert DDM</a>
			    </li>
            </ul>
        </div>
    </div>
</div>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>