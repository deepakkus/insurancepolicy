<?php

class m170815_212945_dropping_risk_state_means_county_id extends CDbMigration
{
    public function safeUp()
    {
        $this->execute('ALTER TABLE [risk_state_means] DROP COLUMN [county_id]');
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE [risk_state_means] ADD [county_id] [int] NULL');
    }
}