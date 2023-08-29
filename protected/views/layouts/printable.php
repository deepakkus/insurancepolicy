<!DOCTYPE html>
<html lang="en" <?php echo $this->htmlManifest; ?>>
<head>
	<meta charset="UTF-8" />
	<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/printable.css" type="text/css" />
    <?php 
        // Tell Yii (and its extensions) to not load jquery. We'll use Google's minified and up-to-date CDN version instead.
        Yii::app()->clientScript->scriptMap = array('jquery.min.js' => false);
    ?>
	<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script> -->
	<title><?php echo $this->pageTitle; ?></title>			
    <script type="text/javascript">
        // Display print dialog on load.
        $(function() {
           window.print();
        });
	</script>
</head>
<body>
    <?php echo $content; ?>
</body>
</html>