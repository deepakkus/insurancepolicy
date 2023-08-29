<?php
class FillNamesCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print "\n -----STARTING COMMAND--------\n";

        //we don NOT want to ever scramble prod
        if(Yii::app()->params['env'] == 'dev' || Yii::app()->params['env'] == 'local')
        {
            if(!isset($args[0]))
                print "please supply a client id \n";
            else
                $this->assignNames($args[0]);
        }
        else
            echo "only works in dev or local environments";

        print "\n -----DONE WITH COMMAND-------\n";
    }

    private function assignNames($clientID)
    {
        print "attaching... \n";
        $properties = true;
        $sql = "select top 1000 mid from members where client_id = :client_id and first_name is null";

        while($properties){

            $properties = Yii::app()->db->createCommand($sql)->bindParam(':client_id', $clientID, PDO::PARAM_INT)->queryAll();

            if($properties){

                foreach($properties as $property){
                    $fid = rand(1, 1000);
                    $lid = rand(1, 1000);
                    $update = "update members set first_name = (select first_name from names where id = :fid), last_name = (select last_name from names where id = :lid) where mid = :mid";

                    print "fid: $fid, lid: $lid \n"; 
                    $result = Yii::app()->db->createCommand($update)->execute(array(':mid' => $property['mid'], ':fid'=>$fid, ':lid'=>$lid));
                }
            }
        }

    }

}