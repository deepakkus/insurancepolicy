<?php

class m180409_145826_add_columns_res_ph_action_type_sub_category_itemorder extends CDbMigration
{
	
	public function safeUp()
	{
        $this->execute("ALTER TABLE [res_ph_action_type] ADD [app_sub_category] VARCHAR(30) NULL");
        $this->execute("ALTER TABLE [res_ph_action_type] ADD [action_item_order] int NULL");
	}

	public function safeDown()
	{
        $this->execute("ALTER TABLE [res_ph_action_type] DROP COLUMN [app_sub_category]");
        $this->execute("ALTER TABLE [res_ph_action_type] DROP COLUMN [action_item_order]");
	}
	
}