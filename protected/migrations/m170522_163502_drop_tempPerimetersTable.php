<?php

class m170522_163502_drop_tempPerimetersTable extends CDbMigration
{
    public function safeUp()
    {
        $this->execute("
            IF OBJECT_ID('tempPerimetersTable', 'U') IS NOT NULL
            BEGIN
                DROP TABLE tempPerimetersTable
            END
        ");
    }

    public function safeDown()
    {
        print "m170522_163502_drop_tempPerimetersTable does not support migration down.\n";
        return false;
    }
}