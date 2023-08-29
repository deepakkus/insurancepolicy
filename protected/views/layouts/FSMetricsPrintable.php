<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="UTF-8" />
	<title>FireShield Metrics | Wildfire Defense Systems</title>			
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />	
	<link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/printable.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" type="text/css" />
	<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script> -->
    <script type="text/javascript">
        // Display print dialog on load.
        $(function() {
            $('#analyticsParamsForm').hide();
            $('#analyticsDatesBox').show();
            $('#memberSummaryArtificialPageBreak').show();
            window.print();
        });
	</script>
</head>
<body>
    <?php echo $content; ?>
</body>
</html>