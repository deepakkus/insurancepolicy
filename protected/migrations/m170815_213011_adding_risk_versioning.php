<?php

class m170815_213011_adding_risk_versioning extends CDbMigration
{
    public function safeUp()
    {
        // Create risk_version table

        $this->execute('
            CREATE TABLE [risk_version]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [version] [varchar](10) NOT NULL,
                [year_dataset] [varchar](4) NOT NULL,
                [comment] [varchar](300) NULL,
                [is_live] [tinyint] NOT NULL,
                CONSTRAINT [PK_risk_version] PRIMARY KEY CLUSTERED ([id] ASC)
            )
        ');

        $this->execute('CREATE NONCLUSTERED INDEX IX_risk_version_version ON [risk_version] ([version])');

        // Adding risk versions

        $this->execute("
            INSERT INTO [risk_version] (version, year_dataset, comment, is_live) VALUES
            (
                'v1.0',
                '2014',
                'This version of the risk DB is being stored in SQL Server as vector data.  It was converted using a variety of ArcMap scripting processes.',
                0
            )
        ");

        $this->execute("
            INSERT INTO [risk_version] (version, year_dataset, comment, is_live) VALUES
            (
                'v2.0',
                '2016',
                'This version of the risk DB is being stored in a Postgres DB as raster data using the Postgis plugin.',
                1
            )
        ");

        // Adding new risk version_id columns to related tables

        $this->execute('ALTER TABLE [risk_score] ADD [version_id] [int] NULL');
        $this->execute('ALTER TABLE [risk_state_means] ADD [version_id] [int] NULL');
        $this->execute('ALTER TABLE [risk_batch_file] ADD [version_id] [int] NULL');

        // Updating risk version_id fields

        $this->execute("
            UPDATE [risk_batch_file] SET [version_id] = CASE
                WHEN [file_name] = 'State Means 2016' THEN 2
                ELSE 1
            END
        ");


        $this->execute('UPDATE [risk_state_means] SET [version_id] = 1');

        $this->execute("
            DECLARE @fileBatchID INT = (SELECT id FROM risk_batch_file WHERE [file_name] = 'State Means 2016')

            IF @fileBatchID IS NOT NULL
            BEGIN
                UPDATE risk_score SET [version_id] = 2 WHERE batch_file_id = @fileBatchID
            END
        ");

        $this->execute('UPDATE risk_score SET [version_id] = 1 WHERE [version_id] IS NULL');

        // Adding foreign keys

        $this->execute('ALTER TABLE [risk_score] ADD CONSTRAINT [FK_risk_score_risk_version] FOREIGN KEY ([version_id]) REFERENCES [risk_version] ([id])');
        $this->execute('ALTER TABLE [risk_state_means] ADD CONSTRAINT [FK_risk_state_means_risk_version] FOREIGN KEY ([version_id]) REFERENCES [risk_version] ([id])');
        $this->execute('ALTER TABLE [risk_batch_file] ADD CONSTRAINT [FK_risk_batch_file_risk_version] FOREIGN KEY ([version_id]) REFERENCES [risk_version] ([id])');

        // Updating state means field types

        $this->execute('ALTER TABLE risk_state_means ALTER COLUMN mean DECIMAL(16, 14)');
        $this->execute('ALTER TABLE risk_state_means ALTER COLUMN std_dev DECIMAL(16, 14)');

        // Adding new state means

        $this->execute("
            DECLARE
                @DateTimeStamp varchar(20) = FORMAT(GETDATE(), 'yyyy-MM-dd hh:mm'),
                @CA int = (SELECT id FROM geog_states WHERE abbr = 'CA'),
                @OR int = (SELECT id FROM geog_states WHERE abbr = 'OR'),
                @WA int = (SELECT id FROM geog_states WHERE abbr = 'WA'),
                @ID int = (SELECT id FROM geog_states WHERE abbr = 'ID'),
                @NV int = (SELECT id FROM geog_states WHERE abbr = 'NV'),
                @AZ int = (SELECT id FROM geog_states WHERE abbr = 'AZ'),
                @UT int = (SELECT id FROM geog_states WHERE abbr = 'UT'),
                @NM int = (SELECT id FROM geog_states WHERE abbr = 'NM'),
                @CO int = (SELECT id FROM geog_states WHERE abbr = 'CO'),
                @WY int = (SELECT id FROM geog_states WHERE abbr = 'WY'),
                @MT int = (SELECT id FROM geog_states WHERE abbr = 'MT'),
                @ND int = (SELECT id FROM geog_states WHERE abbr = 'ND'),
                @SD int = (SELECT id FROM geog_states WHERE abbr = 'SD'),
                @TX int = (SELECT id FROM geog_states WHERE abbr = 'TX'),
                @OK int = (SELECT id FROM geog_states WHERE abbr = 'OK')

            INSERT INTO risk_state_means (mean,std_dev,state_id,date_created,date_updated,version_id)
            VALUES
                (0.00000153262191,0.00000319227032,@CA,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000185244343,0.00000342266909,@OR,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000170366991,0.00000251087362,@WA,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000231674916,0.00000343196292,@ID,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000141823393,0.00000251583210,@NV,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000205249919,0.00000274250677,@AZ,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000175933774,0.00000322886224,@UT,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000288011924,0.00000448711459,@NM,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000207155980,0.00000356858923,@CO,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000257713764,0.00000325024436,@WY,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000315094372,0.00000422717320,@MT,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000068854665,0.00000108178566,@ND,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000318910017,0.00000482659117,@SD,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000173613984,0.00000219538946,@TX,@DateTimeStamp,@DateTimeStamp,2),
                (0.00000144894978,0.00000187845222,@OK,@DateTimeStamp,@DateTimeStamp,2)
        ");
    }

    public function safeDown()
    {
        // Deleting version 2 risk state menas

        $this->execute('DELETE FROM risk_state_means WHERE version_id = 2');

        // Dropping foreign key constraints

        $this->execute('ALTER TABLE risk_score DROP CONSTRAINT FK_risk_score_risk_version');
        $this->execute('ALTER TABLE risk_state_means DROP CONSTRAINT FK_risk_state_means_risk_version');
        $this->execute('ALTER TABLE risk_batch_file DROP CONSTRAINT FK_risk_batch_file_risk_version');

        // Dropping columns

        $this->execute('ALTER TABLE [risk_score] DROP COLUMN [version_id]');
        $this->execute('ALTER TABLE [risk_state_means] DROP COLUMN [version_id]');
        $this->execute('ALTER TABLE [risk_batch_file] DROP COLUMN [version_id]');

        // Dropping risk version table

        $this->execute('DROP TABLE [risk_version]');

        // Updating state means field types

        $this->execute('ALTER TABLE risk_state_means ALTER COLUMN mean REAL');
        $this->execute('ALTER TABLE risk_state_means ALTER COLUMN std_dev REAL');
    }
}