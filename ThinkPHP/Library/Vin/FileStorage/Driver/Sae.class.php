<?php
/**
 * 
 * @author Nintendov
 */

namespace Vin\FileStorage\Driver;
use Vin\FileStorage;

class Sae extends FileStorage{
	private $st;
	
	private $error;
	
	public function __construct(){
		if(!function_exists('memcache_init')){
              header('Content-Type:text/html;charset=utf-8');
              exit('请在SAE平台上运行代码。');
        }
        
        $this->st = new \SaeStorage();
        
	}
	
	public function getFilename($filename){
		if(strpos($filename, __ROOT__)) $filename = str_replace(__ROOT__, '', $filename,1);
		return $filename;
	}
	
	/**
	 * 写文件
	 * @param 文件名
	 * @param 文件内容
	 * @param 限制尺寸 默认不限制
	 * @param 是否覆盖 默认是
	 */
	public function put($rootpath,$filename, $content,$maxSize=-1, $cover = TRUE){
		$rootpath = trim($rootpath,'/');
		
		$filename = $this->getFilename($filename);
		
		if($maxSize!=-1) if(strlen($content)>$maxSize) return '文件大小超过限制';
		
		if($cover){
			if($this->st->fileExists($rootpath,$filename)) $this->st->delete($rootpath,$filename);
		}
		
		return $this->st->write($rootpath,$filename,$content);
	}
	
	
	public function listFile($rootpath,$path , $allowFiles='all'){
		$rootpath = trim($rootpath,'/');
		$path = trim($path,'/');
		return $this->getList($rootpath, $path,$allowFiles);
	}
	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	public function getList($domain, $path, $allowFiles='all' , &$list=array()){
		$allowFiles = 'all';
		$handle = $this->st->getListByPath($domain , $path , 1000);
		
		if($handle['dirNum'] > 0){
			foreach ($handle['dirs'] as $dir) {
				$dirname = trim($dir['fullName'],'/');
				$this->getList($domain, $dirname,$allowFiles, $list);
			}
		}
		
		foreach ($handle['files'] as $file){
			if($allowFiles!='all'){
	            if (preg_match("/\.(".$allowFiles.")$/i", $file['fullName'])) {
	                $list[] = array(
	                    'url'=> $this->st->getUrl($domain,$file['fullName']),
	                    'mtime'=> $file['uploadTime']
	                );
	            }
          	}else{
          		$list[] = array(
	                'url'=> $this->st->getUrl($domain,$file['fullName']),
                    'mtime'=> $file['uploadTime']
            	);
            }
		}
		return $list;
	}
	
	/**
	 * 得到路径
	 */
	public function getPath($rootpath,$path){
		$rootpath = trim($rootpath,'/');
		$url = $this->st->getUrl($rootpath,$path);
		return $url;
	}
}