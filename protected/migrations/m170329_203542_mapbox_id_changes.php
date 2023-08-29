<?php

class m170329_203542_mapbox_id_changes extends CDbMigration
{
    public function safeUp()
    {
        $this->execute("UPDATE [client] SET [mapbox_layer_id] = 'cj0vkrh4y00bz2smy9ehb1u5s' WHERE [name] = 'USAA'");
        $this->execute("UPDATE [client] SET [mapbox_layer_id] = 'cj0vksveg00c52slfnc2nxxvw' WHERE [name] = 'Chubb'");
        $this->execute("UPDATE [client] SET [mapbox_layer_id] = 'cj0vksnzx00c12rny7xhhqkzu' WHERE [name] = 'Liberty Mutual'");
        $this->execute("UPDATE [client] SET [mapbox_layer_id] = 'cj0vkse5100c02smyc1v4s732' WHERE [name] = 'Nationwide'");
        $this->execute("UPDATE [client] SET [mapbox_layer_id] = 'cj0vks4fs00c02rnyal08li3r' WHERE [name] = 'Safeco'");
    }

    public function safeDown()
    {
        $this->execute("UPDATE [client] SET [mapbox_layer_id] = 'wdsresponse.9zy9cnmi' WHERE [name] = 'USAA'");
        $this->execute("UPDATE [client] SET [mapbox_layer_id] = 'wdsresponse.2x2edn29' WHERE [name] = 'Chubb'");
        $this->execute("UPDATE [client] SET [mapbox_layer_id] = 'wdsresponse.53kp4x6r' WHERE [name] = 'Liberty Mutual'");
        $this->execute("UPDATE [client] SET [mapbox_layer_id] = 'wdsresponse.fppq4cxr' WHERE [name] = 'Nationwide'");
        $this->execute("UPDATE [client] SET [mapbox_layer_id] = 'wdsresponse.ajypsyvi' WHERE [name] = 'Safeco'");
    }
}