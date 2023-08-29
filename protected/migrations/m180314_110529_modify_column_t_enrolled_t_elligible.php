<?php

class m180314_110529_modify_column_t_enrolled_t_elligible extends CDbMigration
{
	public function safeUp()
    {
        $this->execute('ALTER TABLE [res_notice] ALTER COLUMN [triggered_enrolled_exp] bigint NULL');
        $this->execute('ALTER TABLE [res_notice] ALTER COLUMN [triggered_eligible_exp] bigint NULL');
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE [res_notice] ALTER COLUMN [triggered_enrolled_exp] int NULL');
        $this->execute('ALTER TABLE [res_notice] ALTER COLUMN [triggered_eligible_exp] int NULL');
    }
}