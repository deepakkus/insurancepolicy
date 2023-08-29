<?php

class m161214_151018_new_ds_hours_states extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE res_dedicated ADD [FL] [varchar](8) NULL');
        $this->execute('ALTER TABLE res_dedicated ADD [GA] [varchar](8) NULL');
        $this->execute('ALTER TABLE res_dedicated ADD [NC] [varchar](8) NULL');
        $this->execute('ALTER TABLE res_dedicated ADD [SC] [varchar](8) NULL');
        $this->execute('ALTER TABLE res_dedicated ADD [TN] [varchar](8) NULL');

        $this->execute("UPDATE res_dedicated SET [FL] = '', [GA] = '', [NC] = '', [SC] = '', [TN] = ''");
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE res_dedicated DROP COLUMN [FL]');
        $this->execute('ALTER TABLE res_dedicated DROP COLUMN [GA]');
        $this->execute('ALTER TABLE res_dedicated DROP COLUMN [NC]');
        $this->execute('ALTER TABLE res_dedicated DROP COLUMN [SC]');
        $this->execute('ALTER TABLE res_dedicated DROP COLUMN [TN]');
	}
}