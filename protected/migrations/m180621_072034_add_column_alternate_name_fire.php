<?php

class m180621_072034_add_column_alternate_name_fire extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("ALTER TABLE [res_fire_name] ADD [Alternate_Name] [varchar](255) CONSTRAINT DF_Alternate_Name DEFAULT NULL");
	}

	public function safeDown()
	{
         $this->execute("
         ALTER TABLE [res_fire_name] DROP CONSTRAINT DF_Alternate_Name
         ALTER TABLE [res_fire_name] DROP COLUMN [Alternate_Name]");
	}

}