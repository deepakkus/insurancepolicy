<?php
/**
 * 
 * EXTENDED database connector used in the config/console.php settings with a few tweaks to work around some issues.
 */
class EDbConnection extends CDbConnection 
{
    /**
     * Overridden function for connection initiation parameters to tweak the type casting for faster indexed lookups 
     * after finding the pdo was converting certain strings and causing longer lookup times.
     *
     * @param mixed $pdo 
     */
    protected function initConnection($pdo) {
        parent::initConnection($pdo);
        $pdo->setAttribute(PDO::SQLSRV_ATTR_ENCODING,PDO::SQLSRV_ENCODING_SYSTEM);
    }

    /**
     * OVERRIDDEN Function to hack around sql server backup "Stun" 
     * Opens DB connection if it is currently not
     * @throws CException if connection fails
     */
	protected function open($tries = 0)
	{
        try
        {
            $tries++;
            parent::open();
        }
        catch(PDOException $e)
		{
		    if($tries < 15)
		    {
                sleep(30);
			    $this->open($tries);
            }
		}
	}
}