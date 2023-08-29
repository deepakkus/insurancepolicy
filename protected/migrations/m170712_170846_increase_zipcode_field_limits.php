<?php

class m170712_170846_increase_zipcode_field_limits extends CDbMigration
{
    public function safeUp()
    {
        $this->execute('ALTER TABLE [res_monitor_log] ALTER COLUMN [Zip_Codes] VARCHAR(1000) NULL');
        $this->execute('ALTER TABLE [res_notice] ALTER COLUMN [zip_codes] VARCHAR(1000) NULL');
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE [res_monitor_log] ALTER COLUMN [Zip_Codes] VARCHAR(200) NULL');
        $this->execute('ALTER TABLE [res_notice] ALTER COLUMN [zip_codes] VARCHAR(200) NULL');
    }
}