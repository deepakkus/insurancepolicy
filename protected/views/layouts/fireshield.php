<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" width="device-width" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/fireshield.css" media="screen, projection" />
	
    <?php
	    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/jquery.mobile.css');
	    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/jqm-datebox.min.css');
	    Yii::app()->clientScript->registerCoreScript('jquery');     
	    Yii::app()->clientScript->registerCoreScript('jquery.ui');
	    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jquery.mobile.js');
	    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jqm-datebox.core.min.js');
	    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jqm-datebox.mode.calbox.min.js');
	    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jqm-datebox.mode.datebox.min.js');
	    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jqm-datebox.mode.flipbox.min.js');
	    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jquery.mobile.datebox.i18n.en_US.utf8.js');
    ?>
		
		
		<script type="text/javascript">
			$(document).ready(function(){
				$(".box-question").click(function(){
                    $(this).next('.box-answer').slideToggle(200);
					
                    var expandButton = $('.expandButton', this);
                    
					if (expandButton.html() === "+") {
						expandButton.html("-");
					} else {
						expandButton.html("+");
                    }
				});
			});
		</script>
	</head>
	<body>
		<?php echo $content; ?>
    </body>
</html>