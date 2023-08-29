<?php

  $data = Yii::app()->db->createCommand('
  
  select 
    ActionCategory.Category [category], 
    ActionType.*, 
    (select count(*) from res_ph_action ActionTaken where ActionTaken.action_type_id = ActionType.id and ActionTaken.visit_id = ' . $model->id . ') ActionTaken,
    (select top 1 qty from res_ph_action ActionTaken where ActionTaken.action_type_id = ActionType.id and ActionTaken.visit_id = ' . $model->id . ') ActionQuanity,
    (select top 1 alliance_qty from res_ph_action ActionTaken where ActionTaken.action_type_id = ActionType.id and ActionTaken.visit_id = ' . $model->id . ') ActionAlQuanity
      from res_ph_action_type ActionType
        join res_ph_action_category ActionCategory on ActionType.category_id = ActionCategory.id
        order by ActionCategory.Category desc, ActionType.name 

  ')->queryAll();

  $Category = '';
  $CategoryGroupChange = false;

?>

<div>
  <span style='color:lightgray;'>Visit ID = '<?php echo $model->id ?>'</span>
</div>
<div style="width:100%;">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
    	<th width="60%">&nbsp;</th>
        <th width="20%">WDS</th>
        <th width="20%">Alliance</th>
    </tr>
    <tr><td colspan='3'>&nbsp;</td></tr>
  <?php foreach ($data as $dataEntry): ?>

    <?php
      if ($Category != $dataEntry['category']) { $CategoryGroupChange = true; } else { $CategoryGroupChange = false; }
      if ($CategoryGroupChange == true)
      {
        $Category = $dataEntry['category'];

        if($Category == 'Pre-Suppression')
        {
            echo "<h3>" . 'Loss Prevention' . "</h3>";
        }
        else if($Category == 'Post Fire Services')
        {
            echo "<h3>" . '' . "</h3>";
        }
        else
        {
            echo "<tr><td colspan='3'><h3>" . $Category . "</h3></td></tr>";
        }
      }
      if($Category!='Non Property')
      {
    ?>
    <tr>
    	<td align="left" valign="top">
    <!--<div>
    <div class="checkbox checkbox-padding">-->
      <input value="<?php echo $dataEntry['id'] ?>" id="<?php echo $dataEntry['id'] ?>" <?php if ($dataEntry['ActionTaken'] == 1) { echo "checked='checked'"; } ?> type="checkbox" name="ResPhActions[]">
      <?php echo $dataEntry['name'] ?>
		  <?php if(!empty($dataEntry['definition'])): ?>
        <a data-toggle="tooltip" title="<?php echo $dataEntry['definition'] ?>"><i class="icon icon-info-sign" id="defination-ico"></i></a>
      <?php endif; ?>
      </td>
      <?php if($dataEntry['units'] != ''): ?>
        &nbsp;<td align="left" valign="top">
        <input style="width:45px" max="999" step="0.1" type="number" value="<?php echo $dataEntry['ActionQuanity'] ?>" name="ResPhActionTypeQty[<?php echo $dataEntry['id'] ?>]" id="ResPhActionTypeQty_<?php echo $dataEntry['id'] ?>">
        <b><?php echo $dataEntry['units'] ?></b>
        </td>
        <td align="left" valign="top">
        <input style="width:45px" max="999" step="0.1" type="number" value="<?php echo $dataEntry['ActionAlQuanity'] ?>" name="ResPhActionTypeAllianceQty[<?php echo $dataEntry['id'] ?>]" id="ResPhActionTypeAllianceQty_<?php echo $dataEntry['id'] ?>">
        <b><?php echo $dataEntry['units'] ?></b>
        </td>
      <?php endif; ?>
    <!--</div>
    </div>-->
    </tr>
    <?php 
    }
      $CategoryGroupChange = false;
    ?>

  <?php endforeach; ?> 
  
</table>
</div>