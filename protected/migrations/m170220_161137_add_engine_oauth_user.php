<?php

class m170220_161137_add_engine_oauth_user extends CDbMigration
{
    public function safeUp()
    {
        $redirectUri = '';
        $env = Yii::app()->params['env'];

        if ($env == 'local')
        {
            $redirectUri = 'http://localhost:81/?r=oa2/authRedirect';
        }
        else if ($env == 'dev')
        {
            $redirectUri = 'https://dev.wildfire-defense.com/?r=oa2/authRedirect';
        }
        else
        {
            $redirectUri = 'https://pro.wildfire-defense.com/?r=oa2/authRedirect';
        }

        $this->insert('user', array(
            'username' => 'wds_engine',
            'type' => 'OAuth2',
            'active' => 1,
            'client_secret' => 'fd36794d-a8ac-2eea-5b37-598b9f33ead336',
            'redirect_uri' => $redirectUri,
            'scope' => 'engine',
            'api_mode' => 'live'
        ));
    }

    public function safeDown()
    {
        $this->delete('user', 'username = :username', array(
            ':username' => 'wds_engine'
        ));
    }
}