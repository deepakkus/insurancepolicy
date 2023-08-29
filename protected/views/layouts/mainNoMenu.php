<!DOCTYPE html>
<html lang="en" <?php echo $this->htmlManifest; ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- blueprint CSS framework -->
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
    <!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
    <link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/images/logo.png" />

    <title>
        <?php echo CHtml::encode($this->pageTitle); ?>
    </title>
    <script type="text/javascript">
        var IS_DEV = <?php echo Yii::app()->params['env'] == 'dev' || Yii::app()->params['env'] == 'local' ? 'true' : 'false'; ?>;

        // Helper logger that only displays output in dev.
        function log(message) {
            if (IS_DEV) {
                console.log(message);
            }
        }
    </script>

    <?php Yii::app()->bootstrap->init(); ?>

</head>

<body class="grey">

    <div class="tint"></div>
    <div id="page">
        <div id="header">
            <div class="clearfix">
                <img class="mainHeaderLogo" src="<?php echo Yii::app()->request->baseUrl; ?>/images/WDS_Logo.png" />
                <div class="mainHeaderTitle">
                    <?php echo CHtml::encode(Yii::app()->name); ?>

                    <?php

                    if(Yii::app()->params['env']=='dev')
                    {
                        echo "<span class='environment'>(DEVELOPMENT)</span>";
                        echo "<span style='font-size:12px;'> (User IP ".$_SERVER['REMOTE_ADDR'].")</span>";
                    }
                    else if (Yii::app()->params['env'] == 'local')
                    {
                        echo "<span class='environment'>(LOCAL)</span>";
                    }

                    ?>

                    <div class="mainHeaderSubtitle">
                        Wildfire Defense Systems
                        <span id="offlineText" class="gray"></span>
                    </div>
                </div>
            </div>
            <?php

            Yii::app()->clientScript->registerScript('fadeFlash',
                '$(".flash").delay(15000).fadeOut("slow");',
                CClientScript::POS_READY
            );

            $flashMessages = Yii::app()->user->getFlashes();

            if ($flashMessages) {
                echo '<div class="flash-wrapper">';
                echo '<div class="flash">';
                echo '  <ul class="flashes">';
                foreach($flashMessages as $key => $message) {
                    echo '  <li><div class="flash-' . $key . '">' . $message . "</div></li>\n";
                }
                echo '  </ul></div></div>';
            }

            ?>
        </div>
        <!-- header -->
        
        <!--Main content-->
        <div style="width:90%;margin:auto;">

            <?php echo $content; ?>
        
        </div>

        <!-- Footer -->
        <div id="footer">
            Copyright &copy; <?php echo date('Y'); ?> by Wildfire Defense Systems.
            <br />
            All Rights Reserved.
            <br />
        </div>
        <!-- footer -->

    </div>
    <!-- page -->
</body>
</html>

<script>
    $('body').on('touchstart.dropdown', '.dropdown-menu', function (e) { e.stopPropagation(); });
</script>
