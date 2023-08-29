<?php

class m170727_075057_add_columns_properties extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [properties] ADD [wds_score_whp] VARCHAR(30) NULL');
        $this->execute('ALTER TABLE [properties] ADD [wds_score_v] VARCHAR(30) NULL');
        $this->execute('ALTER TABLE [properties] ADD [wds_std_dev] VARCHAR(30) NULL');
        $this->execute('ALTER TABLE [properties] ADD [wds_mean] VARCHAR(30) NULL');
        $this->execute('ALTER TABLE [properties] ADD [wds_date_created] DATETIME NULL');
        $this->execute("IF EXISTS( SELECT TOP 1 1 FROM sys.objects o INNER JOIN sys.columns c ON o.object_id = c.object_id WHERE o.name = 'properties' AND c.name = 'risk_score') ALTER TABLE dbo.properties DROP COLUMN risk_score");
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [properties] DROP COLUMN [wds_score_whp]');
        $this->execute('ALTER TABLE [properties] DROP COLUMN [wds_score_v]');
        $this->execute('ALTER TABLE [properties] DROP COLUMN [wds_std_dev]');
        $this->execute('ALTER TABLE [properties] DROP COLUMN [wds_mean]');
        $this->execute('ALTER TABLE [properties] DROP COLUMN [wds_date_created]');
	}
}