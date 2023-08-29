<?php

class CodePromotionCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        $time_start = microtime(true);

        print '-----STARTING COMMAND--------' . PHP_EOL;

        Yii::setPathOfAlias('sessions', Yii::getPathOfAlias('application.sessions'));
        Yii::setPathOfAlias('assets', dirname(Yii::getPathOfAlias('application')) . DIRECTORY_SEPARATOR . 'assets');

        // Delete session files
        $this->deleteSessionFiles(Yii::getPathOfAlias('sessions'));

        // Clear assets folder
        $this->deleteFoldersInDirectory(Yii::getPathOfAlias('assets'));

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

    /**
     * Remove all php session files from a given directory
     * @param string $directory
     */
    private function deleteSessionFiles($directory)
    {
        $iterator = new \DirectoryIterator($directory);

        $filterIterator = new \CallbackFilterIterator($iterator, function($fileInfo) {
            return (strpos($fileInfo->getFilename(), 'sess_') !== false) ? true : false;
        });

        foreach ($filterIterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                // The @ error suppression is in case the session file is in use
                // unlink doesn't throw expections, it only throws errors
                @unlink($fileInfo->getPathName());
            }
        }
    }

    /**
     * Recursively remove all file and folders in a directory.  Leave the root directory.
     * @param string $directory 
     */
    private function deleteFoldersInDirectory($directory)
    {
        $iterator = new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($recursiveIterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                if ($fileInfo->getExtension() !== 'gitkeep') {
                    @unlink($fileInfo->getPathName());
                }
            } else {
                rmdir($fileInfo);
            }
        }
    }
}