<?php

class m161213_151236_create_wds_states_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute("IF OBJECT_ID('FK_wds_states_geog_states', 'F') IS NOT NULL ALTER TABLE wds_states DROP CONSTRAINT FK_wds_states_geog_states");
        $this->execute("IF OBJECT_ID('wds_states', 'U') IS NOT NULL DROP TABLE wds_states");

        $this->execute('
            CREATE TABLE [dbo].[wds_states]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [state_id] [int] NULL,
                CONSTRAINT [PK_wds_states] PRIMARY KEY CLUSTERED ([id])
            )
        ');

        // 14 states + AK
        $this->execute("
            INSERT INTO wds_states (state_id) VALUES
            ((SELECT id FROM geog_states WHERE abbr = 'AK')),
            ((SELECT id FROM geog_states WHERE abbr = 'WA')),
            ((SELECT id FROM geog_states WHERE abbr = 'OR')),
            ((SELECT id FROM geog_states WHERE abbr = 'CA')),
            ((SELECT id FROM geog_states WHERE abbr = 'AZ')),
            ((SELECT id FROM geog_states WHERE abbr = 'UT')),
            ((SELECT id FROM geog_states WHERE abbr = 'NV')),
            ((SELECT id FROM geog_states WHERE abbr = 'ID')),
            ((SELECT id FROM geog_states WHERE abbr = 'MT')),
            ((SELECT id FROM geog_states WHERE abbr = 'WY')),
            ((SELECT id FROM geog_states WHERE abbr = 'CO')),
            ((SELECT id FROM geog_states WHERE abbr = 'NM')),
            ((SELECT id FROM geog_states WHERE abbr = 'TX')),
            ((SELECT id FROM geog_states WHERE abbr = 'ND')),
            ((SELECT id FROM geog_states WHERE abbr = 'SD'))
        ");

        $this->execute('ALTER TABLE [wds_states] ADD CONSTRAINT FK_wds_states_geog_states FOREIGN KEY ([state_id]) REFERENCES [geog_states] ([id])');
    }

    public function safeDown()
    {
        $this->execute("IF OBJECT_ID('FK_wds_states_geog_states', 'F') IS NOT NULL ALTER TABLE wds_states DROP CONSTRAINT FK_wds_states_geog_states");
        $this->execute("IF OBJECT_ID('wds_states', 'U') IS NOT NULL DROP TABLE wds_states");
    }
}