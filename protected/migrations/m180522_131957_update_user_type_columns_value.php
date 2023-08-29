<?php

class m180522_131957_update_user_type_columns_value extends CDbMigration
{
	 public function safeUp()
 {
        $this->execute("UPDATE [user] SET type = 'Second Look' WHERE type = 'SL'");
        $this->execute("UPDATE [user] SET type = 'FS Offered' WHERE type = 'FS'");
 }

 public function safeDown()
 {
        $this->execute("UPDATE [user] SET type = 'SL' WHERE type = 'Second Look'");
        $this->execute("UPDATE [user] SET type = 'FS' WHERE type = 'FS Offered'");
 }
}