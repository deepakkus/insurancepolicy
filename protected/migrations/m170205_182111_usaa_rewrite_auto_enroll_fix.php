<?php

class m170205_182111_usaa_rewrite_auto_enroll_fix extends CDbMigration
{
	public function safeUp()
	{
       $this->execute("
        UPDATE 
            [properties]
        SET
            [response_status] = 'enrolled',
            [response_auto_enrolled] = 1,
            [res_status_date] = [transaction_effective],
            [response_enrolled_date] = [transaction_effective],
            [last_update] = GETDATE()
        WHERE
            [properties].[client_id] = (SELECT [id] FROM [client] WHERE [name] = 'usaa')
            AND [properties].[transaction_type] = 're-write'
            AND ([properties].[policy_status] = 'active' OR [properties].[policy_status] = 'pending')
            AND [properties].[response_status] != 'enrolled'
            AND [properties].[response_status] != 'declined'
            AND
            (
                ([properties].[policy_effective] >= '2015-08-17' AND [properties].[state] IN ('CO','MT','NM','NV') AND [properties].[multi_family] != 1)
                OR
                ([properties].[policy_effective] >= '2016-10-08' AND [properties].[state] IN ('CO','ID','MT','ND','NM','NV','OR','SD','TX','UT','WA','WY'))
                OR
                ([properties].[policy_effective] >= '2016-11-01' AND [properties].[state] = 'CA' AND [properties].[lob] IN ('HO','HOM') AND [properties].[multi_family] != 1)
            )"
        );
	}

	public function safeDown()
	{
        print "this migration can not be safely down'd";
	}
}