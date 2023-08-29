<?php

class m161026_201530_update_location_field extends CDbMigration
{
	public function safeUp()
	{
        $this->alterColumn('properties', 'location', 'varchar(50) NOT NULL');
        $this->addColumn('properties', 'seq_num', "varchar(50) NULL");
	}

	public function safeDown()
	{
        $this->alterColumn('properties', 'location', 'varchar(10) NOT NULL');
        $this->dropColumn('properties', 'seq_num');
	}
}