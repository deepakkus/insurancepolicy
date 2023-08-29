<?php

class m170626_131807_add_do_not_call_column extends CDbMigration
{
	public function safeUp()
    {
          $this->execute('ALTER TABLE [res_call_list] ADD [do_not_call] tinyint NULL');
 
    }

    public function safeDown()
    {
            $this->execute('ALTER TABLE [res_call_list] DROP COLUMN [do_not_call]');
    }
}