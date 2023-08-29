<?php

class m170713_163303_adding_risk_score_pid_index extends CDbMigration
{
    public function safeUp()
    {
        $this->execute('CREATE NONCLUSTERED INDEX IX_risk_score_property_pid ON [risk_score] ([property_pid])');
    }

    public function safeDown()
    {
        $this->execute('DROP INDEX IX_risk_score_property_pid ON [risk_score]');
    }
}