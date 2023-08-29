<!DOCTYPE html>
<html lang="en" <?php echo $this->htmlManifest; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
	<meta name="language" content="en" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
    <link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/images/logo.png">

    <?php

    // Reloading view if user is logged out and triggers an ajax request

    if (Yii::app()->user->loginRequiredAjaxResponse)
    {
        Yii::app()->clientScript->registerScript('ajaxLoginRequired', '
            jQuery("body").ajaxSuccess(
                function(event, request, options) {
                    if (request.responseText == "' . Yii::app()->user->loginRequiredAjaxResponse.'") {
                        window.location.reload();
                    }
                }
            );
        ');
    }

    ?>

    <!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script> -->
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <script type="text/javascript">
        var IS_DEV = <?php echo Yii::app()->params['env'] == 'pro' ? 'false' : 'true'; ?>;

        // Helper logger that only displays output in dev.
        function log(message) {
            if (IS_DEV) {
                console.log(message);
            }
        }
    </script>

    <?php Yii::app()->bootstrap->init(); ?>

</head>

<body class ="grey">
   
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
				else if (Yii::app()->params['env'] == 'trn')
                {
                    echo "<span class='environment'>(TRAINING)</span>";
					echo "<span style='font-size:12px;'> (User IP ".$_SERVER['REMOTE_ADDR'].")</span>";
                }

                ?>
                
                <div class="mainHeaderSubtitle">
                    Wildfire Defense Systems
                    <span id="offlineText" class="gray"></span>
                </div>
            </div>            
        </div>
        <?php

        // Setting flash messages

        Yii::app()->clientScript->registerScript('fadeFlash', '$(".flash").delay(15000).fadeOut("slow");', CClientScript::POS_READY);

        $flashMessages = Yii::app()->user->getFlashes();
            
        if ($flashMessages)
        {
            echo '<div class="flash-wrapper">';
            echo '<div class="flash">';
            echo '  <ul class="flashes">';
            foreach ($flashMessages as $key => $message)
                echo '  <li><div class="flash-' . $key . '">' . $message . "</div></li>\n";
            echo '  </ul></div></div>';
        }

        ?>
    </div><!-- header -->

    <div style="width:100%;">

        <?php if (Yii::app()->user->isGuest): ?>

        <?php $this->widget('application.extensions.mbmenu.MbMenu', array(
            'items' => array(
                array('label' => 'Login', 'url' => array('/site/login'), 'visible' => Yii::app()->user->isGuest)
            )
        )); ?>

        <?php else: ?>

        <?php

        $IS_DEV = (Yii::app()->params['env'] != 'pro') ? true : false;

		$showPR = false;
		$showFS = false;
		$showMemsProps = true; //all logged in users can see these
        $showResponse = false;
        $showRisk = false;
        $showEngines = false;
        $editEngineUsers = false;

        $admin = false;
        $manager = false;

		if(isset(Yii::app()->user->types))
		{
			foreach(Yii::app()->user->types as $type)
			{
				$prTypes = array('Admin', 'Manager', 'PR Caller', 'PR Assessor', 'PR Fire Reviewer', 'PR Writer', 'PR Reviser', 'PR Final');
				$fsTypes = array('Admin', 'Manager', 'FS FRA', 'FS Editor', 'FS Fire Reviewer');
                $responseTypes = array('Response', 'Response Manager');
                $riskTypes = array('Risk', 'Risk Manager');
                $engineTypes = array('Engine View', 'Engine', 'Engine Manager');
                $engineManagerTypes = array('Admin', 'Engine Manager');

				if (in_array($type, $prTypes)) $showPR = true;
				if (in_array($type, $fsTypes)) $showFS = true;
                if (in_array($type, $responseTypes)) $showResponse = true;
                if (in_array($type, $riskTypes)) $showRisk = true;
                if (in_array($type, $engineTypes)) $showEngines = true;
                if (in_array($type, $engineManagerTypes)) $editEngineUsers = true;

                if (in_array('Admin', Yii::app()->user->types)) $admin = true;
                if (in_array('Manager', Yii::app()->user->types)) $manager = true;
			}
		}

        // This code is here to work around the issue of the "Fire Shield" and "Agent App" tabs
        // being simultaneously highlighted when the "Agent Reports" menu option is selected. If we
        // are on the "Agent Reports" page, tell the "Fire Shield" tab to not highlight itself.
        $disableFireShieldTabHighlighting = false;

        if (array_key_exists('r', Yii::app()->controller->actionParams))
        {
            if (Yii::app()->controller->actionParams['r'] == 'fsReport/admin' && array_key_exists('types', Yii::app()->controller->actionParams))
            {
                if (Yii::app()->controller->actionParams['types'] == 'agent')
                {
                    $disableFireShieldTabHighlighting = true;
                }
            }
        }

        $this->widget('application.extensions.bootstrap.widgets.TbNavbar', array(
            'type' => 'inverse', // null or 'inverse'
            'brand' => 'WDS Admin',
            'brandUrl' => '#',
            'collapse' => true,
            'fixed' => false,
            'fluid' => true,
            'items' => array(
                array(
                    'class' => 'bootstrap.widgets.TbMenu',
            	    'type' => 'navbar',
                    'items' => array(

                        array('label' => 'Home', 'url' => array('/site/index')),

                        array('label' => 'System', 'visible' => ($admin || $manager),
                            'items' => array(
                                array('label' => 'Announcements', 'url' => array('/systemSettings/announcements'), 'visible' => ($admin || $manager)),
                                array('label' => 'System Settings', 'url' => array('/systemSettings/update'), 'visible' => $admin),
                                array('label' => 'Api Docs', 'url' => array('/apiDocumentation/admin'), 'visible' => $admin),
                                array('label' => 'WDS States', 'url' => array('/wdsStates/update'), 'visible' => $admin)
                            )
                        ),

                        array('label' => 'Users', 'visible' => ($admin || $manager),
                            'items' => array(
                                array('label' => 'List Users', 'url' => array('/user/admin'), 'visible' => ($admin || $manager)),
                                array('label' => 'List OAuth2 Users', 'url' => array('/user/adminOauth'), 'visible' => $admin),
                                array('label' => 'List Client Users', 'url' => array('/user/manageClientUsers'), 'visible' => in_array('Dash User Admin', Yii::app()->user->types)),
                                array('label' => 'List Engine Users', 'url' => array('/user/adminEngineUsers'), 'visible' => $editEngineUsers),
                                array('label' => 'User Tracking', 'url' => array('/userTracking/admin'), 'visible' => $admin),
                                array('label' => 'Role Management', 'url' => array('/wdsauth'), 'visible' => $admin)
                            )
                        ),

                        array('label' => 'Clients', 'visible' => ($admin),
                            'items' => array(
                                array('label'=> 'Clients', 'url' => array('/client/admin'), 'visible' => $admin),
                                array('label'=> 'Clients Dedicated Hours', 'url' => array('/clientDedicatedHours/admin'), 'visible' => $admin),
                            )
                        ),

				        array('label' => 'Members', 'url' => array('/member/admin'), 'visible' => $showMemsProps),

				        array('label' => 'Properties', 'url' => array('/property/admin'), 'visible' => $showMemsProps),

				        array('label' => 'App', 'visible' => $showFS, 'id'=>'dropdown1',
					        'items' => array(
                                array('label'=> 'All Reports', 'url' => array('/fsReport/allReports',), 'visible' => $showFS),
                                array('label'=> 'Agents', 'url' => array('/agent/admin'), 'visible' => ($admin || $manager)),
                                array('label'=> 'Agent Properties', 'url' => array('/agentProperty/admin'), 'visible' => ($admin || $manager)),
                                array('label'=> 'FS Metrics', 'url' => array('/fsAnalytics/admin'), 'visible' => ($admin || $manager)),
                                array('label' => 'FS Offered', 'url' => array('/fsAnalytics/fsOffered'), 'visible' => ($admin || $manager)),
                                array('label'=> 'Agent Users', 'url' => array('/fsUser/admin', 'type'=>'agent'), 'visible' => ($admin || $manager)),
                                array('label'=> 'Member Users', 'url' => array('/fsUser/admin', 'type'=>'fs'), 'visible' => ($admin || $manager)),
                                array('label'=> 'Contact Us', 'url' => array('/fsContactUs/admin'), 'visible' => ($admin || $manager)),
                                array('label'=> 'Report Texts', 'url' => array('/fsReportText/admin'), 'visible' => ($admin || $manager)),
                                array('label'=> 'App Settings', 'url' => array('/appSetting/admin'), 'visible' => ($admin)),
					        ),
				        ),

                        array('label' => 'Response', 'url' => array('/resNotice/landing'), 'visible' => $showResponse),

                        array('label' => 'Engines', 'url' => array('/engEngines/index'), 'visible' => $showEngines),

                        array('label' => 'Risk', 'visible' => $showRisk,
                            'items' => array(
                                array('label' => 'Risk Query', 'url' => array('/riskModel/risk'), 'visible' => $showRisk),
                                array('label' => 'Risk Version', 'url' => array('/riskVersion/versions'), 'visible' => in_array('Risk Manager', Yii::app()->user->types)),
                                array('label' => 'Risk Import', 'url' => array('/riskBatch/admin'), 'visible' => in_array('Risk Manager', Yii::app()->user->types)),
                                array('label' => 'State Means', 'url' => array('/riskStateMeans/admin'), 'visible' => in_array('Risk Manager', Yii::app()->user->types)),
                                array('label' => 'Risk Scores', 'url' => array('/riskScore/riskScores'), 'visible' => $showRisk),
                                array('label' => 'Risk Data', 'url' => array('/riskModel/riskQueryData'), 'visible' => $showRisk),
                                array('label' => 'Risk API Test', 'url' => array('/riskModel/testApi'), 'visible' => $showRisk)
                            )
                        ),

				        array('label' => 'Pre Risk', 'visible' => $showPR,
					        'items' => array(
                                array('label'=> 'Member Search', 'url' => array('/preRisk/admin')),
                                array('label'=> 'Metrics', 'url' => array('/preRisk/metrics'), 'visible' => ($admin || $manager)),
					        ),
				        ),

                        array('label' => 'Import Files', 'visible' => ($admin || $manager),
                            'items' => array(
                                array('label' => 'Add Import File', 'url' => array('/importFile/create'), 'visible' => ($admin)),
                                array('label' => 'List Import Files', 'url' => array('/importFile/admin'), 'visible' => ($admin || $manager)),
                            ),
                        ),

                        array('label' => 'Logout (' . Yii::app()->user->name . ')', 'url' => array('/site/logout'))
                    )
                )
            )
		));


        ?>

        <?php endif; ?>

        </div><!-- mainmenu -->

        <?php if(isset($this->breadcrumbs)){
            $this->widget('zii.widgets.CBreadcrumbs', array(
                    'links'=>$this->breadcrumbs,
            ));
        } ?>

        <?php echo $content; ?>

        <div id="footer">
                Copyright &copy; <?php echo date('Y'); ?> by Wildfire Defense Systems.<br/>
                All Rights Reserved.<br/>
        </div><!-- footer -->

    </div><!-- page -->
    </body>
</html>