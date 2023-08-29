<?php

class m170228_115523_alter_dedicatedhours_datatype extends CDbMigration
{
	public function up()
	{
        $this->alterColumn('res_dedicated', 'AZ', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'CA', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'CO', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'FL', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'GA', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'ID', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'MT', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'NC', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'ND', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'NM', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'NV', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'OK', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'OR', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'SC', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'SD', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'TN', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'TX', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'UT', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'WA', 'varchar(20)');
        $this->alterColumn('res_dedicated', 'WY', 'varchar(20)');
	}

	public function down()
	{
		echo "m170228_115523_alter_dedicatedhours_datatype does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}