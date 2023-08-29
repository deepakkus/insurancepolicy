<?php
    // Set the manifest url so that this page may be cached for offline use.
    $this->htmlManifest = 'manifest="index.php?r=site/page&view=offlineCache"';

    echo CHtml::cssFile(Yii::app()->baseUrl.'/css/resPropertyStatus/print.css');
    echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/resPropertyStatus/print.js');    
?>

<h3>Wildfire Defense Systems - Property Status Checklist</h3>

<div id="checklistContainer">
    <table id="checklistHeaderTable">
        <tr>
            <td class="bold">
                1. Incident Name
            </td>
            <td class="bold" colspan="2">
                2. Operational Period (Date/Time)
            </td>
        </tr>
        <tr>
            <td>
                <span id="lblFireName"></span>
            </td>
            <td>
                From:
            </td>
            <td>
                To:
            </td>
        </tr>
        <tr>
            <td class="bold">
                3. Unit Name/Designators
            </td>
            <td class="bold" colspan="2">
                4. Unit Leader (Name and Position)
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
            <td colspan="2">
                &nbsp;
            </td>
        </tr>
    </table>
    <table id="personnelAssignedTable">
        <tr>
            <td class="bold" colspan="3">
                5. Personnel Assigned
            </td>
        </tr>
        <tr>
            <td class="center">
                NAME
            </td>
            <td class="center">
                POSITION
            </td>
            <td class="center">
                HOME BASE
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
            <td>
                &nbsp;
            </td>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
            <td>
                &nbsp;
            </td>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
            <td>
                &nbsp;
            </td>
            <td>
                &nbsp;
            </td>
        </tr>
    </table>
    <table id="activityLogTable">
        <tr>
            <td class="bold" colspan="6">
                6. Activity Log
            </td>
        </tr>
        <tr>
            <td class="center height20">
                ADDRESS
            </td>
            <td class="center">
                DIV
            </td>
            <td class="center">
                THREAT
            </td>
            <td class="center">
                PRIORITY
            </td>
            <td class="center">
                DISTANCE
            </td>
        </tr>
        <?php 
        //foreach ($data->getData() as $prop)
        //{
        //    $address = $prop->property_address_line_1;
            
        //    if (!empty($prop->property_address_line_2))
        //        $address .= $prop->property_address_line_2;
            
        //    $address .= ', ' . $prop->property_city;
        //    $address .= ', ' . $prop->property_state;
        //    $address .= ' ' . $prop->property_zip;
            
        //    $threat = $prop->threat == 1 ? 'Yes' : 'No';
            
        //    echo '<tr>';
        //    echo '<td>' . $address . '</td>';
        //    echo '<td>' . $prop->division . '</td>';
        //    echo '<td>' . $threat . '</td>';
        //    echo '<td>' . $prop->priority . '</td>';
        //    echo '<td>' . round($prop->distance, 2) . '</td>';
        //    echo '</tr>';
        //}
        ?>
    </table>
</div>