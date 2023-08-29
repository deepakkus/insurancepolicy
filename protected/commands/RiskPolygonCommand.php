<?php
class RiskPolygonCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
        
        print "\n-----STARTING COMMAND--------\n\n";

        $this->runUpdate();

        print "\n-----DONE WITH COMMAND-------\n";
    }
    
    /**
     * Runs the update on risk model
     */
    public function runUpdate()
    {
        //Create db
        $db = new PDO(Yii::app()->riskdb->connectionString, Yii::app()->riskdb->username, Yii::app()->riskdb->password);
        $db->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);

        //Get first ID to 
        $sql = "select top 1 id from risk_model where poly is null order by id asc;";
        $result = self::runQuery($db, $sql);
        
        //Get starting and ending numbers
        $start = $result['id'];
        $end = $start + 1000;

        //get last id
        $sql = "select top 1 id from risk_model order by id desc;";
        $result = self::runQuery($db, $sql);
        $lastID = $result['id'];

        while($start < $lastID){
            //Batch it out to 1000 entries at a time
            $sql = "update risk_model set poly = geometry::STGeomFromText(point.STBuffer(15.25).STEnvelope().ToString(), 6952) where id between $start AND $end";
            $result = self::runQuery($db, $sql);

            //Output
            print "\n-----updated through id " . number_format($end) . " of " . number_format($lastID) . "-------\n";

            //Increment numbers
            $start = $end + 1; //Because of between, don't want to run the same entry twice
            $end += 1000;
        }
        
    }

    /**
     * Runs the given sql statment
     * @param mixed $db the db object
     * @param string $sql the sql query to run
     * @return mixed result of query
     */
    private static function runQuery($db, $sql)
    {
        $stmt  = $db->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        return $stmt->fetch();

    }

}