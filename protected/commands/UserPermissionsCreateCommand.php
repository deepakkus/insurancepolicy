<?php

class UserPermissionsCreateCommand extends CConsoleCommand
{
    public function run($args)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        $time_start = microtime(true); 

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $this->cleanUserSystem();

        $authManager = Yii::app()->authManager;

        // ------------- WDS OPERATIONS/TASKS -------------

        // Platform
        $authManager->createOperation('wds.access_platform', 'User can access the WDSadmin platform.');
        $wdsPlatformAccess = $authManager->createTask('wds.platform_access', 'User can access the WDSadmin platform.');
        $wdsPlatformAccess->addChild('wds.access_platform');

        // Engines
        $authManager->createOperation('wds.create_engines', 'User can create items in the engine system.');
        $authManager->createOperation('wds.edit_engines', 'User can edit items in the engine system.');
        $authManager->createOperation('wds.edit_limited_enginge_schedule', 'User has some limited edit permissions on the engine scheduling view.');
        $authManager->createOperation('wds.delete_engines', 'User can delete items in the engine system.');
        $authManager->createOperation('wds.view_engines', 'User can only view information in the engine system.');

        $engineManager = $authManager->createTask('wds.engines_manager', 'User has full permissions in the engine system.');
        $engineManager->addChild('wds.create_engines');
        $engineManager->addChild('wds.edit_engines');
        $engineManager->addChild('wds.edit_limited_enginge_schedule');
        $engineManager->addChild('wds.delete_engines');
        $engineManager->addChild('wds.view_engines');

        $engineEditSchedule = $authManager->createTask('wds.engines_edit_schedule', 'User has view only permissions, but can edit part of the engine scheduling view.');
        $engineEditSchedule->addChild('wds.edit_limited_enginge_schedule');
        $engineEditSchedule->addChild('wds.view_engines');

        $engineViewOnly = $authManager->createTask('wds.engines_view_only', 'User has view only access to the engine system.');
        $engineViewOnly->addChild('wds.view_engines');
        
        // System Settings
        $authManager->createOperation('wds.edit_system_announcements', 'User can edit WDSadmin system announcements.');
        $authManager->createOperation('wds.edit_system_settings', 'User can edit WDSadmin system settings.');
        $authManager->createOperation('wds.edit_api_documentation', 'User can edit API documentation.');

        $systemSettingsManager = $authManager->createTask('wds.system_settings_manager', 'User has administrative permissions in system settings.');
        $systemSettingsManager->addChild('wds.edit_system_announcements');
        $systemSettingsManager->addChild('wds.edit_system_settings');
        $systemSettingsManager->addChild('wds.edit_api_documentation');

        $systemSettingsEditor = $authManager->createTask('wds.system_settings_editor', 'User can edit WDSadmin system announcements.');
        $systemSettingsEditor->addChild('wds.edit_system_announcements');

        // Risk
        $authManager->createOperation('wds.view_risk', 'User can view the risk tab and a couple basic menu items.');
        $authManager->createOperation('wds.view_risk_scores', 'User can view the risk scores view.');
        $authManager->createOperation('wds.manage_risk_import', 'User has access to the risk bulk run section.');
        $authManager->createOperation('wds.manage_risk_state_means', 'User has access to the risk state means section.');

        $riskView = $authManager->createTask('wds.risk_view', 'User has view only access to the risk tab, including the risk map + risk data menu items.');
        $riskView->addChild('wds.view_risk');

        $riskManager = $authManager->createTask('wds.risk_manager', 'User has full access to all items in the risk menu.');
        $riskManager->addChild('wds.view_risk');
        $riskManager->addChild('wds.view_risk_scores');
        $riskManager->addChild('wds.manage_risk_import');
        $riskManager->addChild('wds.manage_risk_state_means');

        // Users
        $authManager->createOperation('wds.view_users', 'User can view users.');
        $authManager->createOperation('wds.create_users', 'User can create users.');
        $authManager->createOperation('wds.edit_users', 'User can edit users.');
        $authManager->createOperation('wds.edit_user_roles', 'User can only edit user roles.');
        $authManager->createOperation('wds.manage_oa2_users', 'User has access to oa2 users.');
        $authManager->createOperation('wds.view_user_tracking', 'User can view user tracking.');
        $authManager->createOperation('wds.manage_user_permissions', 'User has full access to user permissions system.');
        $authManager->createOperation('wds.manage_user_permission_roles', 'User has only access to roles in user permission system.');

        $usersView = $authManager->createTask('wds.users_view', 'User has view only access to users.');
        $usersView->addChild('wds.view_users');

        $usersAssignRoles = $authManager->createTask('wds.users_assign_roles', 'User can assign roles to users.');
        $usersAssignRoles->addChild('wds.view_users');
        $usersAssignRoles->addChild('wds.edit_user_roles');

        $usersRoleManagement = $authManager->createTask('wds.users_role_management', 'User can assign roles to users and create new roles.');
        $usersRoleManagement->addChild('wds.view_users');
        $usersRoleManagement->addChild('wds.edit_user_roles');
        $usersRoleManagement->addChild('wds.manage_user_permission_roles');

        $usersAdmin = $authManager->createTask('wds.users_admin', 'User has full access to user system.');
        $usersAdmin->addChild('wds.view_users');
        $usersAdmin->addChild('wds.create_users');
        $usersAdmin->addChild('wds.edit_users');
        $usersAdmin->addChild('wds.edit_user_roles');
        $usersAdmin->addChild('wds.manage_oa2_users');
        $usersAdmin->addChild('wds.view_user_tracking');
        $usersAdmin->addChild('wds.manage_user_permissions');
        $usersAdmin->addChild('wds.manage_user_permission_roles');

        // Property - Member
        $authManager->createOperation('wds.create_member', 'User can create a new member.');
        $authManager->createOperation('wds.update_member', 'User can update a new member.');
        $authManager->createOperation('wds.view_member', 'User has view only access to members.');
        $authManager->createOperation('wds.create_property', 'User can create a property.');
        $authManager->createOperation('wds.update_property', 'User can update a property.');
        $authManager->createOperation('wds.update_property_risk', 'User can update a property\'s risk');
        $authManager->createOperation('wds.view_property', 'User has view only access to properties.');

        $memberView = $authManager->createTask('wds.member_view', 'User has view only access to members.');
        $memberView->addChild('wds.view_member');

        $memberManage = $authManager->createTask('wds.member_manage', 'User has full access to member management.');
        $memberManage->addChild('wds.create_member');
        $memberManage->addChild('wds.update_member');
        $memberManage->addChild('wds.view_member');

        $propertyView = $authManager->createTask('wds.property_view', 'User has view only access to properties.');
        $propertyView->addChild('wds.view_property');

        $propertyUpdateRisk = $authManager->createTask('wds.property_update_risk', 'User can view properties and update their risk value.');
        $propertyUpdateRisk->addChild('wds.view_property');
        $propertyUpdateRisk->addChild('wds.update_property_risk');

        $propertyManage = $authManager->createTask('wds.property_manage', 'User has full access to property management.');
        $propertyManage->addChild('wds.create_property');
        $propertyManage->addChild('wds.update_property');
        $propertyManage->addChild('wds.update_property_risk');
        $propertyManage->addChild('wds.view_property');

        // Clients
        $authManager->createOperation('wds.manage_clients', 'User has full access to clients system.');

        $clientsManage = $authManager->createTask('wds.clients_manage', 'User has full access to to clients system.');
        $clientsManage->addChild('wds.manage_clients');

        // Import Files
        $authManager->createOperation('wds.view_import_files', 'User has view only access to import files.');
        $authManager->createOperation('wds.manage_import_files', 'User has full access to import files.');

        $importFilesView = $authManager->createTask('wds.import_files_view', 'User has view only access to import files.');
        $importFilesView->addChild('wds.view_import_files');

        $importFilesManage = $authManager->createTask('wds.import_files_manage', 'User has full access to import files.');
        $importFilesManage->addChild('wds.view_import_files');
        $importFilesManage->addChild('wds.manage_import_files');

        // PreRisk
        $authManager->createOperation('wds.manage_usaa_prerisk', 'User has access to the usaa prerisk section.');

        $usaaPreriskManage = $authManager->createTask('wds.usaa_prerisk_manage', 'User has access to the usaa prerisk section.');
        $usaaPreriskManage->addChild('wds.manage_usaa_prerisk');

        // App
        $authManager->createOperation('wds.manage_app', 'User has full access to app tab.');
        $authManager->createOperation('wds.manage_app_all_reports', 'User can access to the "All Reports" menu item of the App tab.');
        $authManager->createOperation('wds.manage_app_fs_reports', 'User can access to the "FS Reports" menu item of the App tab.');
        $authManager->createOperation('wds.manage_app_agent_reports', 'User can access to the "Agent Reports" menu item of the App tab.');

        $appManager = $authManager->createTask('wds.app_manager', 'User has full access to app tab.');
        $appManager->addChild('wds.manage_app');
        $appManager->addChild('wds.manage_app_all_reports');
        $appManager->addChild('wds.manage_app_fs_reports');
        $appManager->addChild('wds.manage_app_agent_reports');

        $appReportEditor = $authManager->createTask('wds.app_report_editor', 'User can access the "Reports" menu items in the App tab.');
        $appReportEditor->addChild('wds.manage_app_all_reports');
        $appReportEditor->addChild('wds.manage_app_fs_reports');
        $appReportEditor->addChild('wds.manage_app_agent_reports');

        // Response
        $authManager->createOperation('wds.manage_response_fire', 'User has access to the basic fire response workflow.');
        $authManager->createOperation('wds.manage_dedicated_service', 'User has access to the dedicated services view.');
        $authManager->createOperation('wds.manage_dailies', 'User has access to the dailies view.');
        $authManager->createOperation('wds.manage_unmatched', 'User has access to the unmatched view.');
        $authManager->createOperation('wds.manage_policy_action_types', 'User has access to the policy action types view.');
        $authManager->createOperation('wds.view_dispatched_fires', 'User has access to the "View Dispatched Fires" view.');
        $authManager->createOperation('wds.view_enrollment_tracking', 'User has access to wdsfire enrollment tracking.');
        $authManager->createOperation('wds.view_engine_visit_status', 'User has access to the "Engine Visit Status" view.');
        $authManager->createOperation('wds.manage_work_zones', 'User has access to the work zones view.');
        $authManager->createOperation('wds.manage_post_incident_summary', 'User has access to the post incident summary view.');

        $responseFire = $authManager->createTask('wds.response_fire', 'User has access to the basic fire response workflow.');
        $responseFire->addChild('wds.manage_response_fire');

        $responseDedicatedService = $authManager->createTask('wds.response_dedicated_service', 'User has access to the dedicated services view.');
        $responseDedicatedService->addChild('wds.manage_dedicated_service');

        $responseDailies = $authManager->createTask('wds.response_dailies', 'User has access to the dailies view.');
        $responseDailies->addChild('wds.manage_dailies');

        $responseUnmatched = $authManager->createTask('wds.response_unmatched', 'User has access to the unmatched view.');
        $responseUnmatched->addChild('wds.manage_unmatched');

        $responsePhActionTypes = $authManager->createTask('wds.response_policy_action_types_manage', 'User has access to the policy action types view.');
        $responsePhActionTypes->addChild('wds.manage_policy_action_types');

        $responseDispatchedFires = $authManager->createTask('wds.response_dispatched_fires_view', 'User has access to the "View Dispatched Fires" view.');
        $responseDispatchedFires->addChild('wds.view_dispatched_fires');

        $responseEnrollmentTracking = $authManager->createTask('wds.response_enrollment_tracking', 'User has access to wdsfire enrollment tracking.');
        $responseEnrollmentTracking->addChild('wds.view_enrollment_tracking');

        $responseEngineVisitStatus = $authManager->createTask('wds.response_engine_visit_status', 'User has access to the "Engine Visit Status" view.');
        $responseEngineVisitStatus->addChild('wds.view_engine_visit_status');

        $responseWorkZones = $authManager->createTask('wds.response_work_zones', 'User has access to the work zones view.');
        $responseWorkZones->addChild('wds.manage_work_zones');

        $responsePIS = $authManager->createTask('wds.response_post_incident_summary', 'User has access to the post incident summary view.');
        $responsePIS->addChild('wds.manage_post_incident_summary');

        // ------------- WDS ROLES -------------

        $roleTechnologyManager = $authManager->createRole('wds.Technology Manager', 'These permissions are for the Technology Manager for development and IT.');
        $roleOpsManager = $authManager->createRole('wds.OPS Manager', 'These permissions are for the employee in charge of OPS.');
        $roleProgramManager = $authManager->createRole('wds.OPS Program Manager', 'These permissions are for OPS program managers.');
        $roleProgramCoordinator = $authManager->createRole('wds.OPS Program Coordinator', 'These permissions are for OPS program coordinators.');
        $roleOpsStaff = $authManager->createRole('wds.OPS Staff', 'These permissions are for OPS floor staff.');
        $roleEngineManager = $authManager->createRole('wds.Engine Manager', 'These permissions are for fire officers.');
        $roleDutyOfficer = $authManager->createRole('wds.Duty Officer', 'These permissions are for Duty Officers.');
        $roleProductionStaff = $authManager->createRole('wds.Production Staff', 'These permissions are for production staff who work with app support and app reports.');
        $roleAccountingStaff = $authManager->createRole('wds.Accounting Staff', 'These permissions are for Red Lodge staff.');
        $roleITStaff = $authManager->createRole('wds.IT Staff', 'These permissions are for IT staff.');

        // Everything for developers
        foreach (array('wds.platform_access','wds.app_manager','wds.clients_manage','wds.engines_manager','wds.import_files_manage','wds.member_manage','wds.property_manage',
            'wds.response_dailies','wds.response_dedicated_service','wds.response_dispatched_fires_view','wds.response_engine_visit_status','wds.response_enrollment_tracking',
            'wds.response_fire','wds.response_policy_action_types_manage','wds.response_post_incident_summary','wds.response_unmatched','wds.response_work_zones','wds.risk_manager',
            'wds.system_settings_manager','wds.usaa_prerisk_manage','wds.users_admin') as $task)
        {
            $roleITStaff->addChild($task);
        }

        // Josh/Leland ... most everything but some of the Admin permissions
        foreach (array('wds.platform_access','wds.app_manager','wds.clients_manage','wds.engines_manager','wds.import_files_view','wds.member_view','wds.property_update_risk','wds.property_view',
            'wds.response_dailies','wds.response_dedicated_service','wds.response_dispatched_fires_view','wds.response_engine_visit_status','wds.response_enrollment_tracking',
            'wds.response_fire','wds.response_policy_action_types_manage','wds.response_post_incident_summary','wds.response_unmatched','wds.response_work_zones','wds.risk_manager',
            'wds.system_settings_editor','wds.usaa_prerisk_manage','wds.users_assign_roles','wds.users_role_management','wds.users_view') as $task)
        {
            $roleTechnologyManager->addChild($task);
        }

        // Robert ... Dave ?
        foreach (array('wds.platform_access','wds.engines_view_only','wds.member_view','wds.property_view','wds.response_fire',
            'wds.risk_view','wds.users_view','wds.system_settings_editor','wds.usaa_prerisk_manage') as $task)
        {
            $roleOpsManager->addChild($task);
        }

        // OPS Program Manager
        foreach (array('wds.platform_access','wds.engines_edit_schedule','wds.engines_view_only','wds.import_files_view','wds.member_view','wds.property_view','wds.response_dailies',
            'wds.response_dedicated_service','wds.response_dispatched_fires_view','wds.response_engine_visit_status','wds.response_enrollment_tracking',
            'wds.response_fire','wds.response_post_incident_summary','wds.response_unmatched','wds.response_work_zones','wds.risk_view','wds.system_settings_editor',
            'wds.usaa_prerisk_manage','wds.users_view') as $task)
        {
            $roleProgramManager->addChild($task);
        }

        // OPS Program Coordinator (santa's little helpers)
        foreach (array('wds.platform_access','wds.engines_edit_schedule','wds.engines_view_only','wds.member_view','wds.property_view','wds.response_dailies',
            'wds.response_dedicated_service','wds.response_dispatched_fires_view','wds.response_engine_visit_status','wds.response_enrollment_tracking',
            'wds.response_fire','wds.response_post_incident_summary','wds.response_unmatched','wds.response_work_zones','wds.risk_view',
            'wds.usaa_prerisk_manage','wds.users_view') as $task)
        {
            $roleProgramCoordinator->addChild($task);
        }

        // OPS Floor Staff
        foreach (array('wds.platform_access','wds.member_view','wds.property_view','wds.response_dailies',
            'wds.response_dedicated_service','wds.response_dispatched_fires_view','wds.response_engine_visit_status','wds.response_enrollment_tracking',
            'wds.response_fire','wds.response_post_incident_summary','wds.response_unmatched','wds.response_work_zones') as $task)
        {
            $roleOpsStaff->addChild($task);
        }

        // Eric / Steve ... Engines,Response,Users View Only
        foreach (array('wds.platform_access','wds.engines_manager','wds.member_view','wds.property_view',
            'wds.response_dailies','wds.response_dedicated_service','wds.response_dispatched_fires_view','wds.response_engine_visit_status',
            'wds.response_enrollment_tracking','wds.response_fire','wds.response_post_incident_summary','wds.response_unmatched','wds.response_work_zones',
            'wds.users_view') as $task)
        {
            $roleEngineManager->addChild($task);
        }

        // Duty Officers
        foreach (array('wds.platform_access','wds.engines_manager','wds.response_fire','wds.response_dailies') as $task)
        {
            $roleDutyOfficer->addChild($task);
        }

        // Production Staff
        foreach (array('wds.platform_access','wds.app_report_editor','wds.member_view','wds.property_update_risk','wds.property_view','wds.risk_view','wds.usaa_prerisk_manage') as $task)
        {
            $roleProductionStaff->addChild($task);
        }

        // Red Lodge Staff
        foreach (array('wds.platform_access','wds.engines_view_only','wds.member_view','wds.property_manage','wds.property_view','wds.risk_view') as $task)
        {
            $roleAccountingStaff->addChild($task);
        }

        // ------------- DASHBOARD -------------

        $authManager->createOperation('dash.access_platform', 'User can access the Dashboard platform.');
        $authManager->createOperation('dash.analytics', 'User can access analytics.');
        $authManager->createOperation('dash.manage_user_permissions', 'User can manage users, but can\'t pass on ability to manage users.');
        $authManager->createOperation('dash.manage_super_user_permissions', 'User has full access to user management system.');
        $authManager->createOperation('dash.view_lm_sf', 'User has combine lm/sf views.');
        $authManager->createOperation('dash.manage_calls', 'User can log phone calls.');
        $authManager->createOperation('dash.manage_enrollments', 'User can enroll policyholders.');
        $authManager->createOperation('dash.view_post_incident_summary', 'User can view the post incident summary view.');
        $authManager->createOperation('dash.view_api', 'User has access to the wdsapi section.');
        $authManager->createOperation('dash.manage_risk', 'User has access to the wdsrisk section.');

        // ------------- ENGINES -------------

        $authManager->createOperation('engine.access_platform', 'User can access the Engine Website platform.');

        // ------------- OA2 -------------

        $authManager->createOperation('oa2.oauth_user', 'Oauth 2 User');
        $authManager->createOperation('oa2.oauth_user_legacy', 'Oauth 2 User with NULL expiration dates on tokens');

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

    /**
     * Resets all data for the user system.
     * Source SQL for this method: \\tanker\IT\SQL\Sprint Updates\sprint16.sql
     */
    private function cleanUserSystem()
    {
        $sql1 = "
            IF OBJECT_ID('auth_assignment', 'U') IS NOT NULL DROP TABLE dbo.auth_assignment
            IF OBJECT_ID('auth_item_child', 'U') IS NOT NULL DROP TABLE dbo.auth_item_child
            IF OBJECT_ID('auth_item', 'U') IS NOT NULL DROP TABLE dbo.auth_item
            IF OBJECT_ID('trigger_auth_item_child', 'TR') IS NOT NULL DROP TRIGGER trigger_auth_item_child
        ";

        $sql2 = "
            CREATE TABLE [auth_item]
            (
               [name] VARCHAR(64) NOT NULL,
               [type] INTEGER NOT NULL,
               [description] VARCHAR(MAX),
               [bizrule] VARCHAR(MAX),
               [data] VARCHAR(MAX)
            )
        ";

        $sql3 = "
            ALTER TABLE [auth_item] ADD CONSTRAINT PK_auth_item PRIMARY KEY CLUSTERED ([name])
        ";

        $sql4 = "
            CREATE TABLE [auth_item_child]
            (
               [parent] VARCHAR(64) NOT NULL,
               [child] VARCHAR(64) NOT NULL
            )
        ";

        $sql5 = "
            ALTER TABLE [auth_item_child] ADD CONSTRAINT PK_auth_item_child PRIMARY KEY CLUSTERED ([parent],[child]);
            ALTER TABLE [auth_item_child] ADD CONSTRAINT FK_auth_item_child_auth_item_parent FOREIGN KEY ([parent]) REFERENCES [auth_item] ([name])
            ALTER TABLE [auth_item_child] ADD CONSTRAINT FK_auth_item_child_auth_item_child FOREIGN KEY ([child]) REFERENCES [auth_item] ([name])
        ";

        $sql6 = "
            CREATE TABLE [auth_assignment]
            (
               [itemname] VARCHAR(64) NOT NULL,
               [userid] INT NOT NULL,
               [bizrule] VARCHAR(MAX),
               [data] VARCHAR(MAX)
            )
        ";

        $sql7 = "
            ALTER TABLE [auth_assignment] ADD CONSTRAINT PK_auth_assignment PRIMARY KEY CLUSTERED ([itemname],[userid]);
            ALTER TABLE [auth_assignment] ADD CONSTRAINT FK_auth_assignment_auth_item_itemname FOREIGN KEY ([itemname]) REFERENCES [auth_item] ([name])
                ON DELETE CASCADE ON UPDATE CASCADE
        ";

        $sql8 = "
            CREATE TRIGGER dbo.trigger_auth_item_child
                ON dbo.[auth_item]
                INSTEAD OF DELETE, UPDATE
                AS
                DECLARE @old_name VARCHAR (64) = (SELECT name FROM deleted)
                DECLARE @new_name VARCHAR (64) = (SELECT name FROM inserted)
                BEGIN
                    IF COLUMNS_UPDATED() > 0
                    BEGIN
                        IF @old_name <> @new_name
                            BEGIN
                                ALTER TABLE auth_item_child NOCHECK CONSTRAINT FK_auth_item_child_auth_item_child;
                                UPDATE auth_item_child SET child = @new_name WHERE child = @old_name;
                            END
                            UPDATE auth_item
                            SET name = (SELECT name FROM inserted),
                                type = (SELECT type FROM inserted),
                                description = (SELECT description FROM inserted),
                                bizrule = (SELECT bizrule FROM inserted),
                                data = (SELECT data FROM inserted)
                            WHERE name IN (SELECT name FROM deleted)
                            IF @old_name <> @new_name
                                BEGIN
                                    ALTER TABLE auth_item_child CHECK CONSTRAINT FK_auth_item_child_auth_item_child;
                                END
                            END
                            ELSE
                                BEGIN
                                    DELETE FROM dbo.[auth_item_child] WHERE parent IN (SELECT name FROM deleted) OR child IN (SELECT name FROM deleted);
                                    DELETE FROM dbo.[auth_item] WHERE name IN (SELECT name FROM deleted);
                                END
                    END
        ";

        $command = Yii::app()->db->createCommand();
        $command->setText($sql1)->execute();
        $command->setText($sql2)->execute();
        $command->setText($sql3)->execute();
        $command->setText($sql4)->execute();
        $command->setText($sql5)->execute();
        $command->setText($sql6)->execute();
        $command->setText($sql7)->execute();
        $command->setText($sql8)->execute();
    }
}