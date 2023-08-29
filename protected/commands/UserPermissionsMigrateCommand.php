<?php

class UserPermissionsMigrateCommand extends CConsoleCommand
{
    private $authManager;

    public function run($args)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        $time_start = microtime(true); 

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $this->authManager = Yii::app()->authManager;

        $this->migrateDashEmailGroups();
        $this->migrateNonWdsUsers();
        $this->migrateWdsUsers();
        $this->migrateEngineUsers();

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

    private function migrateDashEmailGroups()
    {
        $db = Yii::app()->db;

        $ids = $db->createCommand()->select('id')->from('user')->where("type LIKE '%Dash Email Group Non-Dispatch%'")->queryColumn();
        
        if ($ids)
        {
            $db->createCommand()->update('user', array('dash_email_non_dispatch' => '1'), 'id IN (' . implode(',', $ids) . ')');
        }

        $ids = $db->createCommand()->select('id')->from('user')->where("type LIKE '%Dash Email Group Dispatch%'")->queryColumn();

        if ($ids)
        {
            $db->createCommand()->update('user', array('dash_email_dispatch' => '1'), 'id IN (' . implode(',', $ids) . ')');
        }

        $ids = $db->createCommand()->select('id')->from('user')->where("type LIKE '%Dash Email Group Noteworthy%'")->queryColumn();

        if ($ids)
        {
            $db->createCommand()->update('user', array('dash_email_noteworthy' => '1'), 'id IN (' . implode(',', $ids) . ')');
        }
    }

    private function migrateNonWdsUsers()
    {
        // OA2 Users

        $users = Yii::app()->db->createCommand()->select('id,type')->from('user')->where('wds_staff IS NULL AND active = 1 AND client_secret IS NOT NULL')->queryAll();

        foreach ($users as $user)
        {
            $usertypes = explode(',', $user['type']);
            $userid = $user['id'];

            if (in_array('OAuth2', $usertypes))
                $this->authManager->assign('oa2.oauth_user', $userid);

            if (in_array('OAuth2 Legacy', $usertypes))
                $this->authManager->assign('oa2.oauth_user_legacy', $userid);
        }

        // Dash Users

        $users = Yii::app()->db->createCommand()->select('id,type')->from('user')->where("wds_staff IS NULL AND client_secret IS NULL AND [type] NOT LIKE 'Engine User'")->queryAll();

        foreach ($users as $user)
        {
            $usertypes = explode(',', $user['type']);
            $userid = $user['id'];

            $this->authManager->assign('dash.access_platform', $userid);

            if (in_array('Dash Enrollment', $usertypes))
                $this->authManager->assign('dash.manage_enrollments', $userid);

            if (in_array('Dash Caller', $usertypes))
                $this->authManager->assign('dash.manage_calls', $userid);

            if (in_array('Dash Analytics', $usertypes))
                $this->authManager->assign('dash.analytics', $userid);

            if (in_array('Dash Post Incident Summary', $usertypes))
                $this->authManager->assign('dash.view_post_incident_summary', $userid);

            if (in_array('Dash User Admin', $usertypes))
                $this->authManager->assign('dash.manage_super_user_permissions', $userid);

            if (in_array('Dash User Manager', $usertypes))
                $this->authManager->assign('dash.manage_user_permissions', $userid);

            if (in_array('Dash API', $usertypes))
                $this->authManager->assign('dash.view_api', $userid);

            if (in_array('Dash LM All', $usertypes))
                $this->authManager->assign('dash.view_lm_sf', $userid);

            if (in_array('Dash Risk', $usertypes))
                $this->authManager->assign('dash.manage_risk', $userid);
        }
    }

    private function migrateWdsUsers()
    {
        $users = Yii::app()->db->createCommand()->select('id,name,type')->from('user')->where("wds_staff = 1 AND active = 1 AND type != 'Engine User' AND type != 'PR Assessor,Engine User'")->queryAll();

        $roleTechnologyManager = array('Josh Amidon');
        $roleOpsManager = array('Robert Drake','David Torgerson ');
        $roleProgramManager = array('Jami Morris','Nick Lauria','Carson Monson');
        $roleProgramCoordinator = array('Andy Coats','Jill Pancerz','Scott Roden','Kevin McKelvy');
        $roleOpsStaff = array('Ryan Sadowski','Chris Olsen','Jami Sanddal','Monica Berchan','Whitney Peterson','Andrew Joseph','Dee Townsend','Brett Beagley');
        $roleEngineManager = array('Eric Morris','Steve Gilson');
        $roleDutyOfficer = array('Glen McNitt','Mike Benefield');
        $roleProductionStaff = array('Heather Drake','Gayle Torgerson','Monica Iverson');
        $roleAccountingStaff = array('Brenda Martin','April Greer','Beth Graham');
        $roleITStaff = array('Tyler Cross','Chris Riddle','Clark Corey','J Chadwick','Joe Collins','Matt Eiben');

        foreach ($users as $user)
        {
            $usertypes = explode(',', $user['type']);
            $username = $user['name'];
            $userid = $user['id'];

            if (in_array($username, $roleTechnologyManager))
                $this->authManager->assign('wds.Technology Manager', $userid);

            if (in_array($username, $roleOpsManager))
                $this->authManager->assign('wds.OPS Manager', $userid);

            if (in_array($username, $roleProgramManager))
                $this->authManager->assign('wds.OPS Program Manager', $userid);

            if (in_array($username, $roleProgramCoordinator))
                $this->authManager->assign('wds.OPS Program Coordinator', $userid);

            if (in_array($username, $roleOpsStaff))
                $this->authManager->assign('wds.OPS Staff', $userid);

            if (in_array($username, $roleEngineManager))
                $this->authManager->assign('wds.Engine Manager', $userid);

            if (in_array($username, $roleDutyOfficer))
                $this->authManager->assign('wds.Duty Officer', $userid);

            if (in_array($username, $roleProductionStaff))
                $this->authManager->assign('wds.Production Staff', $userid);

            if (in_array($username, $roleAccountingStaff))
                $this->authManager->assign('wds.Accounting Staff', $userid);

            if (in_array($username, $roleITStaff))
                $this->authManager->assign('wds.IT Staff', $userid);

            // Dash/Engines base level access

            $this->authManager->assign('dash.access_platform', $userid);
            $this->authManager->assign('engine.access_platform', $userid);

            // Dash permissions

            if (in_array('Dash Enrollment', $usertypes))
                $this->authManager->assign('dash.manage_enrollments', $userid);

            if (in_array('Dash Caller', $usertypes))
                $this->authManager->assign('dash.manage_calls', $userid);

            if (in_array('Dash Analytics', $usertypes))
                $this->authManager->assign('dash.analytics', $userid);

            if (in_array('Dash Post Incident Summary', $usertypes))
                $this->authManager->assign('dash.view_post_incident_summary', $userid);

            if (in_array('Dash User Admin', $usertypes))
                $this->authManager->assign('dash.manage_super_user_permissions', $userid);

            if (in_array('Dash User Manager', $usertypes))
                $this->authManager->assign('dash.manage_user_permissions', $userid);

            if (in_array('Dash API', $usertypes))
                $this->authManager->assign('dash.view_api', $userid);

            if (in_array('Dash LM All', $usertypes))
                $this->authManager->assign('dash.view_lm_sf', $userid);

            if (in_array('Dash Risk', $usertypes))
                $this->authManager->assign('dash.manage_risk', $userid);
        }
    }

    private function migrateEngineUsers()
    {
        $users = Yii::app()->db->createCommand()->select('id,type')->from('user')->where("type = 'Engine User' OR type = 'PR Assessor,Engine User'")->queryAll();

        foreach ($users as $user)
        {
            $usertypes = explode(',', $user['type']);
            $userid = $user['id'];

            $this->authManager->assign('engine.access_platform', $userid);
        }
    }
}