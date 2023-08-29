<?php

class m171106_151458_create_tables_svcoffering_uidentifier_rcode extends CDbMigration
{
	public function safeUp()
	{

        $this->execute("

            CREATE TABLE [service_offering]
            (
                [service_offering_id] [int] IDENTITY(1,1) NOT NULL,
                [service_offering_name] [varchar](200) NULL,
		        [service_offering_code] [varchar](20) NULL
            )

            CREATE TABLE [unique_identifier]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [unique_guid] [varchar](10) NOT NULL,
                [user_type_id] [int] NOT NULL,
                [is_active] [tinyint] NOT NULL DEFAULT 1
            )

            CREATE TABLE [registration_code]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [code] [varchar](15) NULL,
                [is_active] [tinyint] NOT NULL DEFAULT 1
            )
        ");

        $this->execute("
            -- Primary Keys
            ALTER TABLE [service_offering] ADD CONSTRAINT PK_service_offering PRIMARY KEY CLUSTERED ([service_offering_id]);
            ALTER TABLE [unique_identifier] ADD CONSTRAINT PK_unique_identifier PRIMARY KEY CLUSTERED ([id]);
            ALTER TABLE [registration_code] ADD CONSTRAINT PK_registration_code PRIMARY KEY CLUSTERED ([id]);
        ");
        $this->execute('ALTER TABLE [user] ADD [registration_code_id] [int] NULL');
        $this->execute('ALTER TABLE [client] ADD [client_reg_code] [int] CONSTRAINT [DF__client__client_reg_code] NULL');
        $this->execute('
            ALTER TABLE [user] ADD CONSTRAINT [FK_registration_code_id]
            FOREIGN KEY ([registration_code_id]) REFERENCES [registration_code] ([id])');
        $this->execute('ALTER TABLE [client] ADD [app] TINYINT CONSTRAINT [DF__client__app] DEFAULT 0 NULL');

	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [user] DROP CONSTRAINT [FK_registration_code_id]');
        $this->execute('ALTER TABLE [user] DROP COLUMN [registration_code_id]');
        //$this->execute('ALTER TABLE [client] DROP CONSTRAINT [DF__client__client_reg_code]');
        $this->execute('ALTER TABLE [client] DROP COLUMN [client_reg_code]');
        $this->execute('ALTER TABLE [client] DROP CONSTRAINT [DF__client__app]');
        $this->execute('ALTER TABLE [client] DROP COLUMN [app]');
        $this->execute('drop table registration_code');
        $this->execute('drop table unique_identifier');
        $this->execute('drop table service_offering');

	}
}