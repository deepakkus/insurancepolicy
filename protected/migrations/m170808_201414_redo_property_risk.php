<?php

class m170808_201414_redo_property_risk extends CDbMigration
{

	public function safeUp()
	{
        //Drop columns created in other migration
        $this->dropColumn('properties', 'wds_score_whp');
        $this->dropColumn('properties', 'wds_score_v');
        $this->dropColumn('properties', 'wds_std_dev');
        $this->dropColumn('properties', 'wds_mean');
        $this->dropColumn('properties', 'wds_date_created');
        $this->dropColumn('properties', 'wds_risk');

        //Add back in but with different names and slightly different types
        $this->addColumn('properties', 'risk_score', 'varchar(12) null');
        $this->addColumn('properties', 'risk_score_whp', 'varchar(12) null');
        $this->addColumn('properties', 'risk_score_v', 'varchar(12) null');
        $this->addColumn('properties', 'risk_date_created', 'datetime');

        $this->execute('
            SET NOCOUNT ON;
            DECLARE @pid INT,
            @date_created DATETIME,
            @score_v VARCHAR(12),
            @score_whp VARCHAR(12),
            @score_wds VARCHAR(12);

            DECLARE db_cursor CURSOR FOR

            SELECT property_pid, date_created, score_v, score_whp, score_wds
            FROM risk_score
            WHERE property_pid > 0

            OPEN db_cursor
            FETCH NEXT FROM db_cursor INTO
            @pid,
            @date_created,
            @score_v,
            @score_whp,
            @score_wds;

            WHILE @@FETCH_STATUS = 0
            BEGIN
	            UPDATE properties
	            SET
		            risk_date_created = @date_created,
		            risk_score_v = @score_v,
		            risk_score_whp =  @score_whp,
		            risk_score = @score_wds
	            WHERE pid = @pid
	            FETCH NEXT FROM db_cursor INTO
		            @pid,
		            @date_created,
		            @score_v,
		            @score_whp,
		            @score_wds;
            END

            CLOSE db_cursor
            DEALLOCATE db_cursor
        ');

	}

	public function safeDown()
	{
        //Put columns back
        $this->dropColumn('properties', 'risk_score_whp');
        $this->dropColumn('properties', 'risk_score_v');
        $this->dropColumn('properties', 'risk_score');
        $this->dropColumn('properties', 'risk_date_created');

        //Re-add previous columns
        $this->addColumn('properties', 'wds_risk', 'varchar(12) null');
        $this->execute('ALTER TABLE [properties] ADD [wds_score_whp] VARCHAR(30) NULL');
        $this->execute('ALTER TABLE [properties] ADD [wds_score_v] VARCHAR(30) NULL');
        $this->execute('ALTER TABLE [properties] ADD [wds_std_dev] VARCHAR(30) NULL');
        $this->execute('ALTER TABLE [properties] ADD [wds_mean] VARCHAR(30) NULL');
        $this->execute('ALTER TABLE [properties] ADD [wds_date_created] DATETIME NULL');
        $this->execute("IF EXISTS( SELECT TOP 1 1 FROM sys.objects o INNER JOIN sys.columns c ON o.object_id = c.object_id WHERE o.name = 'properties' AND c.name = 'risk_score') ALTER TABLE dbo.properties DROP COLUMN risk_score");
	}
}