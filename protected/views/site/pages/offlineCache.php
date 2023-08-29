<?php
	// offlineCache.php
	// Purpose: output an offline cache manifest file.

	header('Content-Type: text/cache-manifest');
    
    // Get the assets paths
    $mbMenuAssetsPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.extensions.mbmenu.source'));
    $bootstrapPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('bootstrap.assets'));
    $gridViewPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('zii.widgets.assets')); 
    $webJsSourcePath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('system.web.js.source')); 
    $dateTimePickerPath = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.extensions.CJuiDateTimePicker.assets'));
    
    // Returns true if a string "ends with" another string.
    function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    // Echoes all image files in a given directory.
    function echoAllImageFilesInDir($dirPath)
    {
        $dir = new RecursiveDirectoryIterator('.' . $dirPath);
        foreach (new RecursiveIteratorIterator($dir) as $file)
        {
            if ($file->IsFile() && (endsWith($file, '.gif') || endsWith($file, '.png')))
            {
                echo $dirPath . "/" . $file->getFilename() . "\n";
            }
        }
    }
?>
CACHE MANIFEST
# v1.0.0
/css/screen.css
/css/print.css
/css/main.css
/css/form.css
<?php
    echo "$bootstrapPath/css/bootstrap-yii.css\n";
    echo "$bootstrapPath/css/jquery-ui-bootstrap.css\n";
    echo "$webJsSourcePath/jui/css/base/jquery-ui.css\n";
    echo "$dateTimePickerPath/jquery-ui-timepicker-addon.css\n";
    echo "$bootstrapPath/css/bootstrap-notify.css\n";
    echo "$mbMenuAssetsPath/mbmenu.css\n";
?>
/css/wdsExtendedGridView.css
/css/resPropertyStatus/admin.css
/css/resPropertyStatus/update.css
/css/resPropertyStatus/print.css
/css/printable.css
/images/logo.png
/images/WDS_Logo.png
/images/grid-bg.gif
/images/glyphicons-halflings.png
<?php 
    echo "$bootstrapPath/bootstrap/img/glyphicons-halflings.png\n";
    echo "$webJsSourcePath/jui/css/base/images/ui-bg_flat_75_ffffff_40x100.png\n";    
    echo "$webJsSourcePath/jui/css/base/images/ui-bg_glass_75_e6e6e6_1x400.png\n";
    echo "$webJsSourcePath/jui/css/base/images/ui-bg_glass_65_ffffff_1x400.png\n";
    echo "$webJsSourcePath/jui/css/base/images/ui-bg_glass_55_fbf9ee_1x400.png\n";
    echo "$webJsSourcePath/jui/css/base/images/ui-bg_highlight-soft_75_cccccc_1x100.png\n";
    echo "$webJsSourcePath/jui/css/base/images/ui-icons_222222_256x240.png\n";
    echo "$webJsSourcePath/jui/css/base/images/ui-bg_glass_75_dadada_1x400.png\n";
    echo "$webJsSourcePath/jui/css/base/images/ui-icons_454545_256x240.png\n";    
    echoAllImageFilesInDir($mbMenuAssetsPath); 
?>
//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js
<?php
    echo "$bootstrapPath/bootstrap/js/bootstrap.min.js\n";
    echo "$bootstrapPath/js/bootstrap.bootbox.min.js\n";
    echo "$bootstrapPath/js/bootstrap.notify.js\n";
    echo "$gridViewPath/gridview/jquery.yiigridview.js\n";
    echo "$webJsSourcePath/jquery.ba-bbq.js\n";
    echo "$webJsSourcePath/jui/js/jquery-ui.min.js\n";
    echo "$webJsSourcePath/jui/js/jquery-ui-i18n.min.js\n";
    echo "$dateTimePickerPath/jquery-ui-timepicker-addon.js\n";
?>
/js/resPropertyStatus/admin.js
/js/resPropertyStatus/update.js
/js/resPropertyStatus/print.js
/index.php?r=resPropertyStatus/admin
/index.php?r=resPropertyStatus/admin&print=1
/index.php?r=resPropertyStatus/update

# Resources that require an Internet connection
NETWORK:
*