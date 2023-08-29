<?php
/**
	 * Fetches the data from the persistent data storage.
	 * @return array list of data items
	 */
class WDSCSqlDataProvider extends CSqlDataProvider
{
	protected function fetchData()
	{
		if(!($this->sql instanceof CDbCommand))
		{
			$db=$this->db===null ? Yii::app()->db : $this->db;
			$command=$db->createCommand($this->sql);
		}
		else
			$command=clone $this->sql;

		if(($sort=$this->getSort())!==false)
		{
			$order=$sort->getOrderBy();
			if(!empty($order))
			{
				if(preg_match('/\s+order\s+by\s+[\w\s,\.]+$/i',$command->text))
					$command->text.=', '.$order;
				else
					$command->text.=' ORDER BY '.$order;
			}
		}

		if(($pagination=$this->getPagination())!==false)
		{
			$pagination->setItemCount($this->getTotalItemCount());
			$limit=$pagination->getLimit();
			$offset=$pagination->getOffset();
             if ($offset+$limit > $pagination->getItemCount())
            {
                $limit = $pagination->getItemCount() - $offset;
            }
			$command->text=$command->getConnection()->getCommandBuilder()->applyLimit($command->text,$limit,$offset);
		}

		foreach($this->params as $name=>$value)
			$command->bindValue($name,$value);

		return $command->queryAll();
	}
}
?>