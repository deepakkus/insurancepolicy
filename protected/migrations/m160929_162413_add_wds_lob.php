<?php

class m160929_162413_add_wds_lob extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('properties', 'wds_lob', "varchar(3) null DEFAULT 'HOM'");
        $this->update('properties', array('wds_lob'=>'BUS'), "lob = 'BOP'");
        $this->update('properties', array('wds_lob'=>'HOM'), 'lob IS NULL');
	}

	public function safeDown()
	{
        $this->dropColumn('properties', 'wds_lob');
	}

}