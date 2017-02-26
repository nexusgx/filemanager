<?php
    error_reporting(E_ALL & ~E_NOTICE);
    require_once('config.php');
    include('files.php');
    $fm=new Files($config['base_path']);
    $dir='';
    if($_REQUEST['dir']!='/'){
        $dir=$_REQUEST['dir'];
        $fm->set_location($dir);
    }

    
    function get_files($files){
        global $config;
        $icon='';
        $icons=$config['icons'];
        
        if($config['allow_all_files'])
            $filter=false;
        else
            $filter=true;
        $temp=array();
        for($i=0;$i<count($files);$i++){
            if(($filter && in_array($files[$i]['content-type'],$config['allowed_file_types'])) || !$filter || $files[$i]['type']=='dir'){
                $temp[]=$files[$i];
            }
        }
        
        $files=$temp;
        //edit files array
        for($i=0;$i<count($files);$i++){

            //remove the full path for whatever protection that may offer
            //unset($files[$i]['full_filename']);

            $files[$i]['modified']=date('m/d/Y g:i:s',$files[$i]['modified_timestamp']);
            $files[$i]['date']=date('m/d/Y',$files[$i]['modified_timestamp']);
            //add icon
            if(array_key_exists($files[$i]['type'],$icons))
                $files[$i]['icon']=$icons[$files[$i]['type']];
            else
                $files[$i]['icon']='file-o';
        }
        return $files;
    }
    function get_nav($dir){
        $nav=array();
        $nav[]=array(
            'rel_path'=>'/',
            'name'=>'<i class="glyphicon glyphicon-home"></i>'
        );

        //if a dir is provided, get the rest of the breadcrumbs
        if($dir){
            $dir_temp='';
            $nav_temp=explode('/',$dir);
            for($i=0;$i<count($nav_temp);$i++){
                if($dir_temp!='')
                    $dir_temp=$dir_temp.'/'.$nav_temp[$i];
                else
                    $dir_temp=$nav_temp[$i];
                $nav[]=array(
                    'rel_path'=>$dir_temp,
                    'name'=>$nav_temp[$i]
                );
            }
        }
        return $nav;
    }
    
    
    

    //handle any actions
    if(isset($_POST['action'])){
        switch($_POST['action']){
            case 'get_dir':
                echo json_encode(get_files($fm->get_files()));
            break;
            case 'get_nav':
                echo json_encode(get_nav($dir));
            break;
            case 'check_file':
                if($config['allow_all_files'])
                    $filter=false;
                else
                    $filter=true;
                $file=$fm->get_file($_REQUEST['file']);
                $file['action']='none';
                
                //filter file types
                if(($filter && in_array($file['content-type'],$config['allowed_file_types'])) || !$filter){
                    
                    //get the appropriate file action based off the config
                    if(in_array($file['type'],$config['viewable_file_types']))
                        $file['action']='view';
                    elseif(in_array($file['type'],$config['editable_file_types'])){
                        $file['action']='edit';
                        $file['contents']=file_get_contents($file['full_filename']);
                    }
                    else
                        $file['action']='download';
                    unset($file['full_filename']);
                        
                    echo json_encode($file); 
                }
                else
                    echo 'false';
            break;
                
            case 'new_dir':
                if($fm->create_dir($_REQUEST['name']))
                    echo 'true';
                else
                    echo 'false';
            break;
            case 'new_file':
                //any file can be made, but that doesn't mean it can be opened
                $handle = fopen($fm->location.$_REQUEST['name'], 'w') or die('false');
                fclose($handle);
                echo 'true';
            break;
            case 'upload':
                $info=array();
                while(list($key,$value) = each($_FILES['upload']['type'])){
                    if(($filter && !in_array($value,$config['allowed_file_types']))){
                        $info['status'] = "error";
                        $info['message'] = "'". $value. "' Not A Valid File Type";
                        $info['last_upload_type']=$value;
                        $err=true;
                    }
                }
                if(!$err){
                    while(list($key,$value) = each($_FILES['upload']['name'])){
                        if(!empty($value)){
                            $add = $fm->location.$value;
                            if(@move_uploaded_file($_FILES['upload']['tmp_name'][$key], $add)){

                                $info[] = array(
                                    "name"=>$value,
                                    "size"=>filesize($add),
                                    "url"=>$add,
                                    "thumbnail_url"=>$add,
                                    "delete_url"=>$add,
                                    "delete_type"=>"DELETE"
                                );
                            }
                        }
                    }
                }
                echo json_encode($info);
            break;
            case 'delete':
                if(is_array($_REQUEST['file']) && !empty($_REQUEST['file'])){
                    if($fm->delete_files($_REQUEST['file']))
                        echo 'true';
                    else
                        echo 'false';
                }
                else
                    echo 'false';
            break;
            case 'rename':
                if($fm->rename_file($_REQUEST[file],$_REQUEST['name']))
                    echo 'true';
                else
                    echo 'false';
            break;
            default:
            break;
        }
        die();
    }

    //$nav=get_nav($dir);
    //$files=get_files($fm->get_files()); 
//prnt($nav);
    include('html.php');
?>