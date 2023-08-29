<?php

class m170327_085649_add_notes_in_photos extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('res_ph_photos', 'notes', 'varchar(300)');          
	}

	public function safeDown()
	{
        $this->dropColumn('res_ph_photos', 'notes');        
	}
}