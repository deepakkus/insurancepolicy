<?php

class m170321_222945_creating_analytics_index_on_res_notice extends CDbMigration
{
    public function safeUp()
    {
        $this->execute('CREATE NONCLUSTERED INDEX IX_res_notice_client_id_date_created ON [dbo].[res_notice] ([client_id],[date_created])');
        $this->execute('CREATE NONCLUSTERED INDEX IX_res_notice_client_id_wds_status_date_created ON [dbo].[res_notice] ([client_id],[wds_status],[date_created])');
    }

    public function safeDown()
    {
        $this->execute('DROP INDEX IX_res_notice_client_id_date_created ON dbo.res_notice');
        $this->execute('DROP INDEX IX_res_notice_client_id_wds_status_date_created ON dbo.res_notice');
    }
}