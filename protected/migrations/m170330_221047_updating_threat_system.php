<?php

class m170330_221047_updating_threat_system extends CDbMigration
{
    /**
     * This migration alters the threat - perimeter many to many system that currently exists and
     * turns it into a one - one relationship.
     *
     * A fair bit of cleanup is performed as well.
     */
    public function safeUp()
    {
        // Deleting all unused geographise
        $this->execute('DELETE FROM [res_perimeters] WHERE [geog] IS NULL');
        $this->execute('DELETE FROM [res_threat] WHERE [geog] IS NULL');

        // Deleting old file table entries
        $this->execute('
            DELETE FROM [file] WHERE [id] IN (
                SELECT DISTINCT [file_id] FROM [res_perimeters] WHERE [file_id] IS NOT NULL
                UNION
                SELECT DISTINCT [file_id] FROM [res_threat] WHERE [file_id] IS NOT NULL
            )
        ');

        // Deleting unused threats
        $this->execute('
            DELETE FROM [res_threat] WHERE [id] IN (
                SELECT t.id
                FROM res_threat t
                LEFT OUTER JOIN res_notice n ON t.id = n.threat_id
                WHERE threat_id IS NULL
            )
        ');

        // Adding location table + indexes
        $this->execute('
            CREATE TABLE [location]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [geog] [geography] NOT NULL,
                [type] [varchar](100) NULL,
                CONSTRAINT [PK_location] PRIMARY KEY CLUSTERED ([id] ASC)
            )
        ');

        $this->execute('CREATE NONCLUSTERED INDEX IX_location_type ON [location] ([type])');
        $this->execute('CREATE SPATIAL INDEX IX_location_spatial_index ON location(geog) USING GEOGRAPHY_AUTO_GRID');

        // Running migration script
        $this->createTempPerimetersTable();

        // Updating notice table with new perimeter ids
        $this->execute('
            UPDATE n
            SET n.perimeter_id = t.id
            FROM res_notice n
            LEFT OUTER JOIN tempPerimetersTable t ON
                (t.old_perimeter_id = n.perimeter_id OR t.old_perimeter_id IS NULL AND n.perimeter_id IS NULL) AND
                (t.old_threat_id = n.threat_id OR t.old_threat_id IS NULL AND n.threat_id IS NULL)
        ');

        // Updating monitoring log table
        $this->execute('
            UPDATE m
            SET m.Perimeter_ID = t.id
            FROM res_monitor_log m
            LEFT OUTER JOIN tempPerimetersTable t ON t.old_perimeter_id = m.Perimeter_ID
        ');

        // Truncate res_perimeters table and add new fields
        $this->execute('TRUNCATE TABLE res_perimeters');
        $this->execute('ALTER TABLE [res_perimeters] ADD [perimeter_location_id] [int] NOT NULL');
        $this->execute('ALTER TABLE [res_perimeters] ADD [threat_location_id] [int] NULL');

        // Re-populated res_perimeters table
        $this->execute('
            INSERT INTO res_perimeters (
                [fire_id],
                [date_created],
                [date_updated],
                [perimeter_location_id],
                [threat_location_id]
            )
            SELECT
                [fire_id],
                [date_created],
                [date_updated],
                [perimeter_location_id],
                [threat_location_id]
            FROM tempPerimetersTable
            ORDER BY id ASC
        ');

        // Adding foreign keys
        $this->execute('ALTER TABLE [res_perimeters] ADD CONSTRAINT [FK_perimeter_perimeter_location] FOREIGN KEY ([perimeter_location_id]) REFERENCES [location] ([id])');
        $this->execute('ALTER TABLE [res_perimeters] ADD CONSTRAINT [FK_perimeter_threat_location] FOREIGN KEY ([threat_location_id]) REFERENCES [location] ([id])');

        // Dropping tables and columns
        $this->execute('ALTER TABLE [res_notice] DROP COLUMN [threat_id]');
        $this->execute('DROP TABLE [res_threat]');
        $this->execute('DROP INDEX [res_perimeters_spatial_index] ON [res_perimeters]');
        $this->execute('ALTER TABLE [res_perimeters] DROP COLUMN [file_id]');
        $this->execute('ALTER TABLE [res_perimeters] DROP COLUMN [zipcode_id]');
        $this->execute('ALTER TABLE [res_perimeters] DROP COLUMN [geog]');
    }

    /**
     * This migration:
     *     - Drops 2 foreign key constraints in the threat table
     *     - Drops 1 foreign key constraint in the perimeter table
     *     - Drops the perimeter_id column in the threat table
     */
    public function safeDown()
    {
        print "This migration cannot be undone\n";
        return false;
    }

    /**
     * This method create a "tempPerimetersTable" table and populated the "location" table with geographies
     */
    private function createTempPerimetersTable()
    {
        $this->execute("
            SET NOCOUNT ON

            DECLARE
                @fireID int,
                @dateCreated datetime,
                @dateUpdated datetime,
                @oldPerimeterID int,
                @oldThreatID int,
                @perimeterWKT varchar(max),
                @threatWKT varchar(max),
                @locationPerimeterInsertID int,
                @locationThreatInsertID int

            CREATE TABLE tempPerimetersTable (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [fire_id] [int] NOT NULL,
                [date_created] [datetime] NULL,
                [date_updated] [datetime] NULL,
                [perimeter_location_id] [int] NULL,
                [threat_location_id] [int] NULL,
                [old_perimeter_id] [int] NULL,
                [old_threat_id] [int] NULL,
            )

            DECLARE db_cursor CURSOR FOR
                SELECT DISTINCT p.id perimeter_id, t.id threat_id
                FROM res_perimeters p
                LEFT OUTER JOIN res_notice n ON p.id = n.perimeter_id
                LEFT OUTER JOIN res_threat t ON t.id = n.threat_id
                ORDER BY p.id ASC

            OPEN db_cursor
            FETCH NEXT FROM db_cursor INTO @oldPerimeterID, @oldThreatID

            WHILE @@FETCH_STATUS = 0
            BEGIN

                SELECT
                    @fireID = [fire_id],
                    @dateCreated = [date_created],
                    @dateUpdated = [date_updated],
                    @perimeterWKT = [geog].STAsText()
                FROM [res_perimeters]
                WHERE [id] = @oldPerimeterID

                INSERT INTO [location] ([geog], [type]) VALUES (@perimeterWKT, 'perimeter')

                SET @locationPerimeterInsertID = (SELECT IDENT_CURRENT('[location]'))
                SET @locationThreatInsertID = NULL

                -- If this record has an associated threat, update the location table with threat geog too
                IF @oldThreatID IS NOT NULL
                BEGIN
                    SET @threatWKT = (SELECT geog.STAsText() FROM res_threat WHERE id = @oldThreatID)
                    INSERT INTO [location] ([geog], [type]) VALUES (@threatWKT, 'threat')
                    SET @locationThreatInsertID = (SELECT IDENT_CURRENT('[location]'))
                END

                INSERT INTO tempPerimetersTable (
                    [fire_id],
                    [date_created],
                    [date_updated],
                    [perimeter_location_id],
                    [threat_location_id],
                    [old_perimeter_id],
                    [old_threat_id]
                )
                VALUES (
                    @fireID,
                    @dateCreated,
                    @dateUpdated,
                    @locationPerimeterInsertID,
                    @locationThreatInsertID,
                    @oldPerimeterID,
                    @oldThreatID
                )

                FETCH NEXT FROM db_cursor INTO @oldPerimeterID, @oldThreatID
            END
            CLOSE db_cursor
            DEALLOCATE db_cursor

            SET NOCOUNT OFF
        ");
    }
}