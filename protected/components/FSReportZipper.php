<?php 

class FSReportZipper extends ZipArchive { 
    
	public function addDir($path) 
	{
		$this->addEmptyDir($path);
		$nodes = glob($path . '/*'); 
		foreach ($nodes as $node) { 
			if (is_dir($node)) { 
				$this->addDir($node); 
			} else if (is_file($node))  { 
				if(basename($path) == 'css')
					$this->addFile($node, 'html/css/'.basename($node));
				else if(basename($path) == 'img')
					$this->addFile($node, 'html/img/'.basename($node));
				else
					$this->addFile($node, basename($path).'/'.basename($node)); 
			} 
		} 
	} 
    
} // class Zipper 

?>
