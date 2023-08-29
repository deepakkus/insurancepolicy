<?php $this->beginContent($this->module->appLayout); ?>
<div class="container" style="width:95%;">
    <div style="display: table; width: 100%;">
        <div style="display: table-cell; width:85%">
            <div id="content">
                <?php echo $content; ?>
            </div>
        </div>
        <div style="display: table-cell; width:15%">
            <div id="sidebar">
                <?php $this->renderPartial('/layouts/_menu'); ?>
            </div>
        </div>
    </div>
</div>
<?php $this->endContent(); ?>
