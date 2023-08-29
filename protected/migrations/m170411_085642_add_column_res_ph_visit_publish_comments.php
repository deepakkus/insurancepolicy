<?php

class m170411_085642_add_column_res_ph_visit_publish_comments extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('res_ph_visit', 'publish_comments', 'varchar(3000)');          
	}

	public function safeDown()
	{
        $this->dropColumn('res_ph_visit', 'publish_comments');        
	}
}