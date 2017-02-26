<?php
    error_reporting(E_ALL & ~E_NOTICE);
    include('config.php');
    include('files.php');


    if($_REQUEST['file']){
        $file=$config['base_path'].'/'.$_REQUEST['file'];
        $file=str_replace('\\','/',$file);
        if(file_exists($file)){
            $parts=explode('/',$file);
            $name=$parts[count($parts)-1];
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-disposition: attachment; filename=\"".$name."\"");
            readfile($file);
            exit();
        }
    }
    header('Location: index.php');
?>