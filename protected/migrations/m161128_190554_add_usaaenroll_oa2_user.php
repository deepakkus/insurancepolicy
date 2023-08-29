<?php

class m161128_190554_add_usaaenroll_oa2_user extends CDbMigration
{
	public function safeUp()
	{
        $usaa_client = Client::model()->findByAttributes(array('name'=>'USAA'));
        
        if(Yii::app()->params['env'] == 'pro')
            $redirect_uri = 'https://pro.wildfire-defense.com/?r=oa2/authRedirect';
        else if(Yii::app()->params['env'] == 'dev')
            $redirect_uri = 'https://dev.wildfire-defense.com/?r=oa2/authRedirect';
        else if(Yii::app()->params['env'] == 'local')
            $redirect_uri = 'http://localhost:81/?r=oa2/authRedirect';
        else
        {
            echo 'ERROR: console config env param missing or unexpected value';
            return false;
        }

        $this->insert('user', array(
                'client_id'=>$usaa_client->id,
                'username'=>'usaaenroll',
                'client_secret'=>'882e3095-6517-b21e-6bfa-daff0980bfea36',
                'redirect_uri'=>$redirect_uri,
                'scope'=>WDSAPI::SCOPE_USAAENROLLMENT,
                'type'=>'OAuth2',
                'active'=>1
            )
        );
	}

	public function safeDown()
	{
        $this->delete('user',"username = 'usaaenroll'");
	}
}