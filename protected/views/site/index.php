<?php

$this->pageTitle = Yii::app()->name;

Yii::app()->clientScript->registerCss(1, '
    .message-board {
        border: 1px solid black;
        padding: 20px;
        background-color: lightyellow;
        font-size: 1.1em;
        border-radius: 4px;
        box-shadow: 3px 3px 5px 1px #cccccc;
        box-sizing: border-box;
        margin-top: 20px;
    }
');

?>

<?php if(Yii::app()->user->isGuest): ?>
<h1>Welcome to <i><?php echo CHtml::encode(Yii::app()->name); ?></i></h1>
<p>WDS Admin - Internal Software for WDS Employees</p>

<?php else: ?>

<h1>Welcome, <?php echo Yii::app()->user->getState('fullname'); ?>!</h1>
<p>WDS Admin - Internal Software for WDS Employees</p>

<div class="row-fluid" style="margin-bottom: 40px;">
    <div class="span6">
        <div class="message-board">
            <p class="lead">Announcements Board</p>
            <hr style="border-color: powderblue;" />
            <?php echo Yii::app()->systemSettings->announcements; ?>
        </div>
    </div>
    <div class="span6">
        <div class="message-board">
            <p class="lead">Support Board</p>
            <hr style="border-color: powderblue;" />
            <?php echo Yii::app()->systemSettings->support; ?>
        </div>
    </div>
</div>

<?php endif; ?>


