<?php

class m170110_165217_add_user_clients_table extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        //create user_client table
        $this->createTable('user_client', array(
            'id' => '[int] IDENTITY(1,1) NOT NULL PRIMARY KEY',
            'user_id' => '[SMALLINT] NOT NULL',
            'client_id' => '[INT] NOT NULL',
            )
        );
        //add FKs
        $this->addForeignKey('user_id_fk', 'user_client', 'user_id', 'user', 'id');
        $this->addForeignKey('client_id_fk', 'user_client', 'client_id', 'client', 'id');
        //update current users to have a user_client for any that already have a client_id (parent) set
        $allClientUsers = User::model()->findAll('client_id IS NOT NULL');
        foreach($allClientUsers as $user)
        {
            $userClient = new UserClient();
            $userClient->user_id = $user->id;
            $userClient->client_id = $user->client_id;
            if(!$userClient->save())
                return false;
        }
	}

	public function safeDown()
	{
        $this->dropTable('user_client');
	}
}