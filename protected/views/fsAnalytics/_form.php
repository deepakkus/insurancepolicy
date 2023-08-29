<?php echo CHtml::beginForm(); ?>

<div id="analyticsParamsForm" class="form clear marginTop10">
    <div class="">
        <div class="col">
            <label for="analyticsParamStartDate">Start Date</label>
            <?php
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                    'name' => 'analyticsParamStartDate',
                    'mode' => 'date',
                    'value' => $startDate,
                    'options' => array(
                        'showAnim' => 'slideDown',
                    ),
                ));
            ?>            
        </div>
        <div class="col">
            <label for="analyticsParamEndDate">End Date</label>
            <?php
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                    'name' => 'analyticsParamEndDate',
                    'mode' => 'date',
                    'value' => $endDate,
                    'options' => array(
                        'showAnim' => 'slideDown',
                    ),
                ));
            ?>            
        </div>
        <div class="col">
            <label>&nbsp;</label>
            <?php echo CHtml::submitButton('Submit', array('onclick' => 'return validateDates();')); ?>
        </div>
        <?php
            echo '<div class="paddingTop20" style="float: right">';
            echo CHtml::link('Print FireShield Metrics', array(
                'fsAnalytics/admin', 'print' => 'true',
                'startDate' => $startDate,
                'endDate' => $endDate,
            ));
            echo '</div>';
        ?>
    </div>
</div>

<?php echo CHtml::endForm(); ?>

<script type="text/javascript">
    function validateDates() 
    {
        if ($('#analyticsParamStartDate').val().length === 0) 
        {
            alert('Please provide a valid Start Date.');
            return false;
        }

        if ($('#analyticsParamEndDate').val().length === 0) 
        {
            alert('Please provide a valid End Date.');
            return false;
        }

        return true;
    }
</script>