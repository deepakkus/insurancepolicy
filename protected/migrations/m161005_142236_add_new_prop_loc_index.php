<?php

class m161005_142236_add_new_prop_loc_index extends CDbMigration
{
	public function safeUp()
	{
        $this->createIndex('mid_policy_location', 'properties', 'member_mid,policy,location');
	}

	public function safeDown()
	{
        $this->dropIndex('mid_policy_location','properties');
	}
}