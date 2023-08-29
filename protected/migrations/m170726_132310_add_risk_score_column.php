<?php

class m170726_132310_add_risk_score_column extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [properties] ADD [risk_score] VARCHAR(30) NULL');
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [properties] DROP COLUMN [risk_score]');
	}
}