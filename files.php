<?php
	
	class Files{
		
		var $root='/'; //root folder where we will find all files 
		var $location=''; //the folder we're in right now
		var $error='';
		var $files=array();
		var $exclude=array();
        var $os='';
		
		function __construct($dir){
            if(substr($this->root,0,-1)!='/')
                $dir=$dir.'/';
			$this->exclude=array('.','..','Thumbs.db');
			$this->root=$dir;
            $this->location=$dir;
            if(defined('PHP_OS_FAMILY'))
                $os=strtolower(PHP_OS_FAMILY);
            else
                if(strtolower(substr(PHP_OS,0,3))=='win')
                    $os='win';
                else
                    $os=strtolower(PHP_OS);
            $this->os=$os;
		}
		
		function get_files($sort='filename',$direction='ASC'){
			$dir=$this->location;
			$ret=array();
			if($dir==''){
				$this->add_error('No directory is selected.'."\r\n".$dir);
				return false;
			}
			if(is_dir($dir)){
				if($handle = opendir($dir)){
					while((false !== ($file = readdir($handle)))){
                        //skip any files with problems or excluded files
                        if(!in_array($file,$this->exclude) && is_readable($dir.$file)){
                            $temp=array();
                            $temp=array(
                                'filename'=>$file,
                                'full_filename'=>$dir.$file,
                                'rel_filename'=>str_replace($this->root,'',$dir.$file),
                                'type'=>$this->get_filetype($dir.$file),
                                'content-type'=>$this->get_file_content_type($dir.$file)
                            );
                            $temp['raw_size']=filesize($dir.$file);
                            $temp['filesize']=$this->get_filesize($dir.$file);
                            $temp['modified_timestamp']=filemtime($dir.$file);
                            $temp['modified_date']=date('Y-m-d H:i:s',$temp['modified_timestamp']);
                            
                            if(is_dir($dir.$file)){
                                $temp['empty']=$this->is_dir_empty($file);
                                $dirs[]=$temp;
                            }
                            else
                                $files[]=$temp;
                        }
					}
					closedir($handle);
				}
				else{
					$this->add_error('There was a problem opening the directory');
					return false;
				}
			}
			else{
				$this->add_error('The directory you choose does not exist.');
				return false;
			}
			
            if(!empty($files))
                $files=$this->sort_files($files,$sort,$direction);
            if(!empty($dirs))
                $dirs=$this->sort_files($dirs,$sort,$direction);
			
			if(empty($files)){
				$this->files=$dirs;
			}
			elseif(empty($dirs))
				$this->files=$files;
			else{
				$this->files=$dirs;
				foreach($files as $f)
					$this->files[]=$f;
			}
			
			return $this->files;
		}
		
		function get_recursive_directories(){
			$dir=$this->location;
			
			$ret=array();
			
			if($dir==''){
				$this->add_error('No directory is selected.'."\r\n".$dir);
				die();
			}
			if(is_dir($dir)){
				if($handle = opendir($dir)){
					while((false !== ($file = readdir($handle)))){
						if(!in_array($file,$this->exclude) && is_dir($dir.$file)){
							$ret[]=array(
								'filename'=>$file,
								'full_filename'=>$dir.$file,
								'trunc_filename'=>str_replace($base,'',$dir.$file),
								'sub_folders'=>$this->get_recursive_directories($dir.$file.'/')
							);
						}
					}
					closedir($handle);
				}
				else{
					$this->add_error('There was a problem opening the directory');
					return false;
				}
			}
			else{
				$this->add_error('The directory you choose does not exist.');
				return false;
			}
			$ret=sort_files($ret,'filename','ASC');
			return $ret;
		}
        
        function get_file($filename){
            $type=$this->get_filetype($this->location.$filename);
            $file=array();
            if($type!='dir'){
                $file=array(
                    'filename'=>$filename,
                    'full_filename'=>$this->location.$filename,
                    'rel_filename'=>str_replace($this->root,'',$this->location.$filename),
                    'raw_size'=>filesize($this->location.$filename),
                    'filesize'=>$this->get_filesize($this->location.$filename),
                    'type'=>$type,
                    'content-type'=>$this->get_file_content_type($this->location.$filename)
                );
                $file['modified_timestamp']=filemtime($this->location.$filename);
                $file['modified_date']=date('Y-m-d H:i:s',$file['modified_timestamp']);
            }
            return $file;
        }
		
		function is_dir_empty($dir){
			if(is_dir($dir)){
				if($handle = opendir($dir))
				while((false !== ($file = readdir($handle)))){
					if(!in_array($file,$this->exclude))
						return false;
				}
				closedir($handle);
			}
			return true;
		}
		
		function sort_files($files, $key, $direction='ASC'){
			$arsort=array();
			if($files)
				foreach($files as $value){
					$arsort[]=$value[$key];
				}
                        
			if($direction=='ASC')
				array_multisort($arsort,SORT_ASC,$files);
			else
				array_multisort($arsort,SORT_DESC,$files);
			return $files;
		}
		
		function format_file_date($date='Y-m-d H:i:s'){
			for($i=0;$i<count($this->files);$i++)
				$this->files[$i]['modified']=date($date,$this->files[$i]['modified']);
		}
		
		function set_location($dir){
			if(substr($dir,0,-1)!='/')
                $dir.='/';
            if($dir=='/')
                $dir='';
            
            $dir=$this->root.$dir;
            
            if(is_dir($dir))
                $this->location=$dir;
			else
				return false;
		}
		function get_location(){
			return $this->location;
		}
		
		function create_dir($str,$chmod=0755){
			if(!is_dir($this->location.$str))
				if(mkdir($this->location.$str,$chmod))
					return true;
				else
					$this->add_error("There was a problem creating the folder.");
            else
                $this->add_error("This folder already exists. (".$this->location.$str.")");
			return false;
		}
		
		function rename_file($old,$new){
			
            if(is_dir($this->location.$old) || file_exists($this->location.$old))
                if(!is_dir($this->location.$new) && !file_exists($this->location.$new))
                    if(rename($this->location.$old,$this->location.$new))
                        return true;
                    else
                        $this->add_error("There was a problem renaming the file or folder.");
                else
                    $this->add_error('This file or directory already exists');
            else
                $this->add_error('This file or directory does not exist');
			return false;
		}
		
		function move_files($old,$new){
			$base=str_replace(array('http://','www.','/'),'',$thisSite);
			$base=$doc_base.$base.'/media/';
			$temp=explode('|',$old);
			for($i=0;$i<count($temp);$i++){
				if(!empty($temp[$i])){
					$links=explode('/',$temp[$i]);
					$t=$links[count($links)-1];
					//echo $this->location.$temp[$i].'<br />';
					//echo $this->location.$t;
					if(!rename($this->location.$temp[$i],$base.$new.'/'.$t))
						$this->add_error("There was a problem renaming the file or folder.");
				}
			}
		}
		
		function delete_dir($dir) {
			if ($this->os=='win'){
                exec(sprintf("rd /s /q %s", escapeshellarg($this->location.$dir)));
            }
            else{
                exec(sprintf("rm -rf %s", escapeshellarg($this->location.$dir)));
            }
            return is_dir($this->location.$dir);
		} 
		
		function delete_files($files){
			if($files){
				if(is_array($files)){
					foreach($files as $t){
						if(is_dir($this->location.$t))
							$this->delete_dir($t);
						else
							unlink($this->location.$t);
					}
                    return true;
				}
				else{
					if(is_dir($this->location.$files))
						return $this->delete_dir($files);
					else
						return unlink($this->location.$files);
				}
			}
		}
		
		function add_error($str){
			$this->error[]=$str;
		}
		
		
		function get_filesize($str,$raw=false) {
			$bytes=filesize($str);
			if($raw)return $bytes;
			$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
			
			$bytes = max($bytes, 0);
			$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
			$pow = min($pow, count($units) - 1);
			
			$bytes /= pow(1024, $pow);
			
			return round($bytes) . ' ' . $units[$pow]; 
		} 
		
		function get_filetype($file_path){
			$mtype = '';
			
			if(is_dir($file_path))
				$mtype="dir";
			else{
                $mtype=strtolower(pathinfo($file_path,PATHINFO_EXTENSION));
			}
			
			return $mtype;
		}
		function get_file_content_type($file){
			//if the file doesn't exist, cancel everything
			if(!file_exists($file))return false;
			
			//array of content types to double check to ensure the correct type is returned
			$double_check=array('text/html','text/x-c','text/troff','text/plain','application/octet-stream');
			
			//looks for the file type via the OS.
			$t=exec("file -bi -- ".escapeshellarg($file));
            if($t)
                $mime=explode(';',$t)[0];
            else{
               $mime=mime_content_type($file);
            }
			
			//account for mime type db error
			if(in_array($mime,$double_check)){
				$ext=strtolower(pathinfo($file,PATHINFO_EXTENSION));
				switch($ext){
					case 'css':$mime='text/css'; break;
					case 'ttf':$mime[0]='application/font-ttf'; break;
					case 'woff':$mime[0]='application/font-woff'; break;
					case 'js':$mime='application/javascript'; break;
				}
			}
			return $mime;
		}
	}
    
	
?>