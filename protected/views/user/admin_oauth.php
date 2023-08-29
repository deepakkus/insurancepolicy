<script type="application/javascript">
    $(document).ready(function () 
    {
        var i =0; 
        var usertype;
        var userOptions;
        var opts = $("#User_type > option").map(function() { return this.value; }).get();
        $("#User_type")
        .find("option")
        .remove()
        .end();
        for(i=0;i<opts.length;i++)
        {
            userOptions = (opts[i]).substr(0, 12)+"..";
            if(opts[i].length>12)
            {
                $("#User_type").append("<option value="+userOptions+">"+userOptions+"</option>");
            }
           else
           {
                $("#User_type").append("<option value="+opts[i]+">"+opts[i]+"</option>");
           }
        }
        //Tooltip for all dropdowns
        $("#User_type").each( function ()
        { 
            var sel = this;
            for(i=0;i<sel.length;i++)
            {   
                sel.options[i].title = opts[i];    
            }   
        });
    });  
</script>
<?php

$this->breadcrumbs=array(
	'Oauth2 Users' => array('adminOauth'),
	'Manage'
);

echo '<h1>Manage Oauth2 Users</h1>';

echo CHtml::link('Add Oauth2 User', array('user/createOauth'), array('class' => 'btn btn-success')) . " ";
echo CHtml::link('View Tokens', array('oa2Tokens/admin'), array('class' => 'btn btn-info'));

echo '<div class ="table-responsive">';

$this->widget('zii.widgets.grid.CGridView', array(
	'id' => 'user-grid-oauth',
	'dataProvider' => $model->searchOauth(),
	'filter' => $model,
	'columns' => array(
        array(
            'class' => 'CButtonColumn',
            'template' => '{update}',
            'header' => 'Actions',
            'headerHtmlOptions' => array('class' => 'grid-column-width-50'),
            'htmlOptions' => array('style' => 'text-align: center'),
            'updateButtonUrl' => '$this->grid->controller->createUrl("user/updateOauth", array("id" => $data->id))'
        ),
		'id',
        array(
            'name' => 'client',
            'value' => '(isset($data->client->name)) ? $data->client->name : "";',
            'filter' => CHtml::activeDropDownList($model, 'client_id', CHtml::listData(Client::model()->findAll(), 'id', 'name'), array('empty'=>''))
        ),
		'username',
        'client_secret',
        'redirect_uri',
        array(
            'name' => 'scope',
            'filter' => array(
                WDSAPI::SCOPE_DASH => WDSAPI::SCOPE_DASH,
                WDSAPI::SCOPE_FIRESHIELD => WDSAPI::SCOPE_FIRESHIELD,
                WDSAPI::SCOPE_RISK => WDSAPI::SCOPE_RISK,
                WDSAPI::SCOPE_USAAENROLLMENT => WDSAPI::SCOPE_USAAENROLLMENT,
                WDSAPI::SCOPE_ENGINE => WDSAPI::SCOPE_ENGINE,
                WDSAPI::WDS_PRO => WDSAPI::WDS_PRO
            )
        ),
        array(
            'name' => 'type',
            'filter' => CHtml::activeDropDownList($model, 'type', $model->getTypes(), array('empty'=>'','style'=> 'width:140px'))
        ),
	    array (
		    'name' => 'active',
            'type' => 'raw',
            'value' => '$data->activeMask;',
            'filter' => CHtml::activeDropDownList($model, 'active', $model->getActiveFilter(), array('encode'=>false, 'empty'=>''))
        )
    )
));

echo '</div>';
?>
