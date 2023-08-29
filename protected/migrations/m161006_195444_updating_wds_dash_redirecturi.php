<?php

class m161006_195444_updating_wds_dash_redirecturi extends CDbMigration
{
	public function safeUp()
	{
        $redirectUrl = '';

        switch (Yii::app()->params['env'])
        {
            case 'local': $redirectUrl = 'http://localhost:81/'; break;
            case 'dev': $redirectUrl = 'https://dev.wildfire-defense.com/'; break;
            case 'pro': $redirectUrl = 'https://pro.wildfire-defense.com/'; break;
        }

        $this->update('user', array('redirect_uri' => $redirectUrl . '?r=oa2/authRedirect'), 'username = :username', array(
            ':username' => 'wds_dash'
        ));
	}

	public function down()
	{
        $this->update('user', array('redirect_uri' => 'dashauth://authorization'), 'username = :username', array(
            ':username' => 'wds_dash'
        ));
	}
}