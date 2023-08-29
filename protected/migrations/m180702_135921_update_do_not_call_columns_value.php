<?php

class m180702_135921_update_do_not_call_columns_value extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->execute('UPDATE [res_call_list] SET do_not_call = 0 WHERE do_not_call IS NULL');
	}
}