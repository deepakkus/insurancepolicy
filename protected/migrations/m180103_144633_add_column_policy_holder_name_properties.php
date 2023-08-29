<?php

class m180103_144633_add_column_policy_holder_name_properties extends CDbMigration
{
	//safe up
	public function safeUp()
	{
        $this->execute("
            ALTER TABLE [properties] ADD [policyholder_name] [VARCHAR](255) CONSTRAINT DF_POLICYHOLDER_NAME DEFAULT NULL
        ");
	}

    // safe down
    public function safeDown()
	{
        $this->execute("
            ALTER TABLE [properties] DROP CONSTRAINT DF_POLICYHOLDER_NAME
            ALTER TABLE [properties] DROP COLUMN [policyholder_name]
        ");
	}
}