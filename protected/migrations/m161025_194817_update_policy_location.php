<?php

class m161025_194817_update_policy_location extends CDbMigration
{
	public function safeUp()
	{
        // Update location number with correct location when policy has location tacked on to it
        $this->execute("UPDATE properties SET location = RIGHT(policy, LEN(policy) - CHARINDEX('-', policy)) WHERE policy LIKE '%-%'");

        // Update policy to not inlucde the location number when policy has location tacked on to it
        $this->execute("UPDATE properties SET policy = SUBSTRING(policy, 0, CHARINDEX('-', policy)) WHERE policy LIKE '%-%'");
	}

	public function safeDown()
	{
        echo "m161025_194817_update_policy_location does not support migration down.\n";
		return false;
	}
}