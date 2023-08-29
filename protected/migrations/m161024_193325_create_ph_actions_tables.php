<?php

class m161024_193325_create_ph_actions_tables extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("

            -- Dropping foreign key constraints, if exists
            IF OBJECT_ID('FK_res_ph_action_type_category', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_action_type DROP CONSTRAINT FK_res_ph_action_type_category
            IF OBJECT_ID('FK_res_ph_action_visit', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_action DROP CONSTRAINT FK_res_ph_action_visit
            IF OBJECT_ID('FK_res_ph_action_type', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_action DROP CONSTRAINT FK_res_ph_action_type
            IF OBJECT_ID('FK_res_ph_photos_visit', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_photos DROP CONSTRAINT FK_res_ph_photos_visit
            IF OBJECT_ID('FK_res_ph_photos_file', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_photos DROP CONSTRAINT FK_res_ph_photos_file
            IF OBJECT_ID('FK_res_ph_visit_property', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_visit DROP CONSTRAINT FK_res_ph_visit_property
            IF OBJECT_ID('FK_res_ph_visit_client', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_visit DROP CONSTRAINT FK_res_ph_visit_client
            IF OBJECT_ID('FK_res_ph_visit_fire', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_visit DROP CONSTRAINT FK_res_ph_visit_fire
            IF OBJECT_ID('FK_res_ph_visit_approval_user', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_visit DROP CONSTRAINT FK_res_ph_visit_approval_user
            IF OBJECT_ID('FK_res_ph_visit_user', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_visit DROP CONSTRAINT FK_res_ph_visit_user

            -- Dropping tables, if exists
            IF OBJECT_ID('res_ph_action_category', 'U') IS NOT NULL DROP TABLE dbo.res_ph_action_category
            IF OBJECT_ID('res_ph_action_type', 'U') IS NOT NULL DROP TABLE dbo.res_ph_action_type
            IF OBJECT_ID('res_ph_visit', 'U') IS NOT NULL DROP TABLE dbo.res_ph_visit
            IF OBJECT_ID('res_ph_photos', 'U') IS NOT NULL DROP TABLE dbo.res_ph_photos
            IF OBJECT_ID('res_ph_action', 'U') IS NOT NULL DROP TABLE dbo.res_ph_action
        
        ");

        $this->execute("

            CREATE TABLE [res_ph_action_category]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [category] [varchar](100) NOT NULL
            )

            CREATE TABLE [res_ph_action_type]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [name] [varchar](100) NOT NULL,
                [active] [tinyint] NOT NULL DEFAULT 1,
                [category_id] [int] NOT NULL,
                [definition] [varchar](255) NULL
            )

            CREATE TABLE [res_ph_visit]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [property_pid] [int] NOT NULL,
                [client_id] [int] NOT NULL,
                [fire_id] [int] NULL,
                [date_created] datetime NOT NULL,
                [date_updated] datetime NOT NULL,
                [date_action] datetime NOT NULL,
                [status] [varchar](255) NOT NULL,
                [comments] [varchar](3000) NULL,
                [approval_user_id] [smallint] NULL,
                [user_id] [smallint] NULL,
                [publish] [int] NOT NULL DEFAULT 0
            )

            CREATE TABLE [res_ph_photos]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [visit_id] [int] NOT NULL,
                [file_id] [int] NOT NULL
            )

            CREATE TABLE [res_ph_action]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [visit_id] [int] NOT NULL,
                [action_type_id] [int] NOT NULL
            )

        ");

        $this->execute("

            -- Primary Keys
            ALTER TABLE [res_ph_action_category] ADD CONSTRAINT PK_res_ph_action_category PRIMARY KEY CLUSTERED ([id]);
            ALTER TABLE [res_ph_action_type] ADD CONSTRAINT PK_res_ph_action_type PRIMARY KEY CLUSTERED ([id]);
            ALTER TABLE [res_ph_visit] ADD CONSTRAINT PK_res_ph_visit PRIMARY KEY CLUSTERED ([id]);
            ALTER TABLE [res_ph_photos] ADD CONSTRAINT PK_res_ph_photos PRIMARY KEY CLUSTERED ([id]);
            ALTER TABLE [res_ph_action] ADD CONSTRAINT PK_res_ph_action PRIMARY KEY CLUSTERED ([id]);

            -- Foreign Keys
            ALTER TABLE [res_ph_action_type] ADD CONSTRAINT FK_res_ph_action_type_category FOREIGN KEY ([category_id]) REFERENCES [res_ph_action_category] ([id])
            ALTER TABLE [res_ph_action] ADD CONSTRAINT FK_res_ph_action_visit FOREIGN KEY ([visit_id]) REFERENCES [res_ph_visit] ([id]) ON DELETE CASCADE
            ALTER TABLE [res_ph_action] ADD CONSTRAINT FK_res_ph_action_type FOREIGN KEY ([action_type_id]) REFERENCES [res_ph_action_type] ([id])
            ALTER TABLE [res_ph_photos] ADD CONSTRAINT FK_res_ph_photos_visit FOREIGN KEY ([visit_id]) REFERENCES [res_ph_visit] ([id]) ON DELETE CASCADE
            ALTER TABLE [res_ph_photos] ADD CONSTRAINT FK_res_ph_photos_file FOREIGN KEY ([file_id]) REFERENCES [file] ([id]) ON DELETE CASCADE
            ALTER TABLE [res_ph_visit] ADD CONSTRAINT FK_res_ph_visit_property FOREIGN KEY ([property_pid]) REFERENCES [properties] ([pid])
            ALTER TABLE [res_ph_visit] ADD CONSTRAINT FK_res_ph_visit_client FOREIGN KEY ([client_id]) REFERENCES [client] ([id])
            ALTER TABLE [res_ph_visit] ADD CONSTRAINT FK_res_ph_visit_fire FOREIGN KEY ([fire_id]) REFERENCES [res_fire_name] ([Fire_ID])
            ALTER TABLE [res_ph_visit] ADD CONSTRAINT FK_res_ph_visit_approval_user FOREIGN KEY ([approval_user_id]) REFERENCES [user] ([id])
            ALTER TABLE [res_ph_visit] ADD CONSTRAINT FK_res_ph_visit_user FOREIGN KEY ([user_id]) REFERENCES [user] ([id])

            -- Removing a ph_visit will remove all corresponding ph_photos & ph_action related table entries
            -- Removing a file table entry will remove the corresponding ph_photos related table entry
            -- Trying to remove a used action_type will result in a constraint error, this is by design ... a user should never delete an action_type entry, only make inactive

        ");
	}

	public function safeDown()
	{
        $this->execute("
            IF OBJECT_ID('FK_res_ph_action_type_category', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_action_type DROP CONSTRAINT FK_res_ph_action_type_category
            IF OBJECT_ID('FK_res_ph_action_visit', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_action DROP CONSTRAINT FK_res_ph_action_visit
            IF OBJECT_ID('FK_res_ph_action_type', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_action DROP CONSTRAINT FK_res_ph_action_type
            IF OBJECT_ID('FK_res_ph_photos_visit', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_photos DROP CONSTRAINT FK_res_ph_photos_visit
            IF OBJECT_ID('FK_res_ph_photos_file', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_photos DROP CONSTRAINT FK_res_ph_photos_file
            IF OBJECT_ID('FK_res_ph_visit_property', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_visit DROP CONSTRAINT FK_res_ph_visit_property
            IF OBJECT_ID('FK_res_ph_visit_client', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_visit DROP CONSTRAINT FK_res_ph_visit_client
            IF OBJECT_ID('FK_res_ph_visit_fire', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_visit DROP CONSTRAINT FK_res_ph_visit_fire
            IF OBJECT_ID('FK_res_ph_visit_approval_user', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_visit DROP CONSTRAINT FK_res_ph_visit_approval_user
            IF OBJECT_ID('FK_res_ph_visit_user', 'F') IS NOT NULL ALTER TABLE dbo.res_ph_visit DROP CONSTRAINT FK_res_ph_visit_user

            IF OBJECT_ID('res_ph_action_category', 'U') IS NOT NULL DROP TABLE dbo.res_ph_action_category
            IF OBJECT_ID('res_ph_action_type', 'U') IS NOT NULL DROP TABLE dbo.res_ph_action_type
            IF OBJECT_ID('res_ph_visit', 'U') IS NOT NULL DROP TABLE dbo.res_ph_visit
            IF OBJECT_ID('res_ph_photos', 'U') IS NOT NULL DROP TABLE dbo.res_ph_photos
            IF OBJECT_ID('res_ph_action', 'U') IS NOT NULL DROP TABLE dbo.res_ph_action
        ");
	}
}