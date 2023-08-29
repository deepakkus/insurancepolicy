
<?php

$this->breadcrumbs=array(
	'Response'
);

Yii::app()->bootstrap->registerAssetCss('bootstrap-box.css');

Yii::app()->clientScript->registerCss('responseLandingCSS','
    .jumbotron {
        margin: 40px 0;
        text-align: center;
    }
    .jumbotron h1 {
        font-size: 40px;
        line-height: 1;
    }
    p.text-large {
        font-size: 120%;
    }
    hr.notice-button-separator {
        border: 0;
        height: 1px;
        opacity: 0.9;
        background-image: -webkit-linear-gradient(left, rgba(0,0,0,0), rgba(0,0,0,0.75), rgba(0,0,0,0));
        background-image:    -moz-linear-gradient(left, rgba(0,0,0,0), rgba(0,0,0,0.75), rgba(0,0,0,0));
        background-image:     -ms-linear-gradient(left, rgba(0,0,0,0), rgba(0,0,0,0.75), rgba(0,0,0,0));
    }
');

$admin = false;
$manager = false;

if (in_array('Admin', Yii::app()->user->types)) $admin = true;
if (in_array('Manager', Yii::app()->user->types)) $manager = true;

?>

<div class="container-fluid">

    <div class="jumbotron">
        <h1>Response Home Page</h1>
        <h4 class = 'center'><i><?php echo date('F d, Y'); ?></i></h4>
        <p class = 'center'>
            Daily Threat Created: <b><?php echo (ResDailyThreat::isDailyThreat()) ? "<span class = 'green'>&#x2713;</span>" : "<span class = 'red'>&#x2716;</span>"; ?></b> 
            | Dailies Published: <b><?php echo (ResDaily::isDailyPublished()) ? "<span class = 'green'>&#x2713;</span>" : "<span class = 'red'>&#x2716;</span>"; ?></b> 
        </p>
    </div>

    <div class="bootstrap-widget center">
        <div class="bootstrap-widget-header" style="height:auto;">
            <h2>Fire Workflow</h2>
        </div>
        <div class="bootstrap-widget-content">    
            <div class="row-fluid center" style="padding:25px 0;">
                <div class="span4">
                    <h3>Monitor A Fire</h3>
                    <p class="text-large">Find out what policyholders are in proximity to a fire.</p>
                    <p><a href="<?php echo $this->createUrl('resMonitorLog/monitorFire'); ?>" class="btn btn-primary btn-large">New Fire</a></p>
                </div>
                <div class="span4">
                    <h3>Manage Fires</h3>
                    <p class="text-large">Create & edit fires, update perimeters, threats</p>
                    <p><a href="<?php echo $this->createUrl('resFireName/admin'); ?>" class="btn btn-primary btn-large">Manage Fires</a></p>
                </div>
                <div class="span4">
                    <h3>Fire Details</h3>
                    <p class="text-large">Information on the fire such as size, containment, etc.</p>
                    <p><a href="<?php echo $this->createUrl('resFireObs/admin'); ?>" class="btn btn-primary btn-large">Fire Details</a></p>
                </div>
            </div>

            <div class="row-fluid center" style="padding:25px 0;">
                <div class="span4">
                    <h3>Monitor Log</h3>
                    <p class="text-large">All monitored fires.</p>
                    <p><a href="<?php echo $this->createUrl('resMonitorLog/admin'); ?>" class="btn btn-primary btn-large">Monitor Log</a></p>
                </div>
                <div class="span4">
                    <h3>Smoke Check</h3>
                    <p class="text-large">View significant new fires.</p>
                    <p><a href="<?php echo $this->createUrl('resMonitorLog/smokeCheck'); ?>" class="btn btn-primary btn-large">Smoke Check</a></p>
                </div>
                <div class="span4">
                    <h3>Notifications</h3>
                    <p class="text-large">Add/Edit notifications for fires.</p>
                    <p><a href="<?php echo $this->createUrl('resNotice/admin'); ?>" class="btn btn-primary btn-large">Notifications</a></p>
                </div>
            </div>

            <div class="row-fluid center" style="padding:25px 0;">
                <div class="span4">
                    <h3>Call Log</h3>
                    <p class="text-large">Call management for policyholders impacted by fires.</p>
                    <p><a href="<?php echo $this->createUrl('resCallList/admin'); ?>" class="btn btn-primary btn-large">Call Log</a></p>
                </div>
                <div class="span4">
                    <h3>Fire Scraper</h3>
                    <p class="text-large">Fire dispatch scraper that refreshes from WildCAD every 15 minutes.</p>
                    <p><a href="<?php echo $this->createUrl('resFireScrape/index'); ?>" class="btn btn-primary btn-large" target="_blank">Fire Scraper</a></p>
                </div>
            </div>
        </div>
    </div>

    <div class="bootstrap-widget center">
        <div class="bootstrap-widget-header" style="height:auto;">
            <h2>Other Tasks</h2>
        </div>
        <div class="bootstrap-widget-content">
            <div class="row-fluid center" style="padding:25px 0;">
                <div class="span4">
                    <h3>Dedicated Service</h3>
                    <p class="text-large">Manage dedicated service hours used.</p>
                    <p><a href="<?php echo $this->createUrl('resDedicated/admin'); ?>" class="btn btn-primary btn-large">Dedicated Service</a></p>
                </div>
                <div class="span4">
                    <h3>Dailies</h3>
                    <p class="text-large">Add/Edit the daily danger ratings for the service area.</p>
                    <p><a href="<?php echo $this->createUrl('resDailyThreat/admin'); ?>" class="btn btn-primary btn-large">Dailies</a></p>
                </div>
                <div class="span4">
                    <h3>Unmatched</h3>
                    <p class="text-large">Found unmatched on a Fire or Dedicated Service</p>
                    <p><a href="<?php echo $this->createUrl('unmatched/index'); ?>" class="btn btn-primary btn-large">Unmatched</a></p>
                </div>
            </div>
            <?php if ($admin): ?>
            <div class="row-fluid center" style="padding:25px 0;">
                <div class="span12">
                    <h3>Manage Policy Action Types</h3>
                    <p class="text-large">Manage Policyholder Action Categories and Types</p>
                    <p><a href="<?php echo $this->createUrl('resPhActionType/manage'); ?>" class="btn btn-primary btn-large">Manage Policy Actions</a></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dispatched Fires -->
    <div class = "boostrap-widget center paddingTop20 paddingBottom20">
        <div class = "bootstrap-widget-header" style="height:auto;">
            <h2>Dispatched Fire Tasks</h2>
        </div>
        <div class = "bootstrap-widget-content">
            <div class="row-fluid center" style="padding:25px 0;">
                <div class="span4">
                    <h3>View Dispatched Fires</h3>
                    <p class="text-large">View currently dispatched fires.</p>
                    <p><a href="<?php echo $this->createUrl('/resNotice/fires') ?>" class="btn btn-primary btn-large">Dispatched Fires</a></p>
                </div>
                <div class="span4">
                    <h3>WDSFire Enrollment Tracking</h3>
                    <p class="text-large">View enrollments made by clients through the WDSFire interface either on fires or not.</p>
                    <p><a href="<?php echo $this->createUrl('/wdsfireEnrollments/admin') ?>" class="btn btn-primary btn-large">WDSFire Enrollment Tracking</a></p>
                </div>
                <div class="span4">
                    <h3>Engine Visit Status</h3>
                    <p class="text-large">View policyholders who have been visited by an engine per fire.</p>
                    <p><a href="<?php echo $this->createUrl('/resPhVisit/viewPolicyAction') ?>" class="btn btn-primary btn-large">Policy Status</a></p>
                </div>
            </div>

            <div class="row-fluid center" style="padding:25px 0;">
                <div class="span4">
                    <h3>Work Zones</h3>
                    <p class="text-large">Create and update work zones for any dispatched fire.</p>
                    <p><a href="<?php echo $this->createUrl('/resTriageZone/admin') ?>" class="btn btn-primary btn-large">Work Zones</a></p>
                </div>
                <?php if(Yii::app()->params['env'] !== 'pro') { ?>
                <div class="span4">
                    <h3>Evac Zones</h3>
                    <p class="text-large">Create and update evac zones for notifications.</p>
                    <p>
                        <a href="<?php echo $this->createUrl('/resEvacZone/admin') ?>" class="btn btn-primary btn-large">Evac Zones</a>
                    </p>
                </div>
                <?php } ?>
                <?php if ($admin || $manager): ?>
                <div class="span4">
                    <h3>Post Incident Summary</h3>
                    <p class="text-large">Make Post Incident Summaries</p>
                    <p><a href="<?php echo $this->createUrl('/resPostIncidentSummary/admin') ?>" class="btn btn-primary btn-large">Post Incident Summary</a></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Downloads -->
    <div class = "boostrap-widget center">
        <div class = "bootstrap-widget-header" style="height:auto;">
            <h2>Downloads</h2>
        </div>
        <div class = "bootstrap-widget-content">
            <div class="row-fluid center" style="padding:25px 0;">
                <div class="span12">
                    <p><a href="<?php echo $this->createUrl('/resPerimeters/downloadRecentMonitoredFiresKML'); ?>">WDS KML - Recently Monitored Fires</a></p>
                    <p><a href="<?php echo $this->createUrl('/property/getWdsGeocodeReport'); ?>">WDS Matched Geocode Report</a></p>
                    <p>( Any WDS found coordinates for active policies that are over a mile away from a pair of client supplied parcel level accuracy coordiantes. )</p>
                    <?php

                    $mouNames = array_map(function($file) { return basename($file); }, glob(Yii::app()->basePath . '\response\mou\*.pdf'));

                    echo '<p class="lead"><u>Colorado MOU Documents</u></p>';

                    foreach ($mouNames as $fileName)
                    {
                        echo CHtml::link($fileName, $this->createUrl('/file/downloadMou',  array('fileName' => $fileName)), array('class' => 'show marginBottom10'));
                    }

                    ?>
                </div>
            </div>
        </div>
    </div>

</div>