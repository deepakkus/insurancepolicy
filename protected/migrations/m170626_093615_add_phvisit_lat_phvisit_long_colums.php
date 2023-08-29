<?php

class m170626_093615_add_phvisit_lat_phvisit_long_colums extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [res_ph_visit] ADD [phvisit_lat] decimal(12,7) NULL');
	    $this->execute('ALTER TABLE [res_ph_visit] ADD [phvisit_long] decimal(12,7) NULL');
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [res_ph_visit] DROP COLUMN [phvisit_lat]');
	    $this->execute('ALTER TABLE [res_ph_visit] DROP COLUMN [phvisit_long]');
	}
}