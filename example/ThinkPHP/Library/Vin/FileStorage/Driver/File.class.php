<?php
/**
 * 本地文件处理类
 * @author Nintendov
 */

namespace Vin\FileStorage\Driver;
use Vin\FileStorage;

class File extends FileStorage{
	/**
	 * 本地写文件
	 */
	public function put($rootpath,$filename, $content,$maxSize=-1, $cover = TRUE){
		$filename = '.'.$rootpath.$filename;
		if($maxSize!=-1){
			if(strlen($content>$maxSize)){
				return '文件大小超过限制';
			}
		}
		$dir         =  dirname($filename);
        if(!is_dir($dir))
            mkdir($dir,0755,true);
        if(false === file_put_contents($filename,$content)){
            E(L('_STORAGE_WRITE_ERROR_').':'.$filename);
        }else{
            $this->contents[$filename]=$content;
            return true;
        }
	}

	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	
	public function listFile($rootpath, $path ,$allowFiles='all'){
		$path = $_SERVER['DOCUMENT_ROOT'].__ROOT__.$rootpath.$path;
		return $this->getList($path, $allowFiles);
	}
	
	public function getList($path ,$allowFiles='all' , &$files = array()){
		if (!is_dir($path)) return null;
	    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
	    $handle = opendir($path);
	    while (false !== ($file = readdir($handle))) {
	        if ($file != '.' && $file != '..') {
	            $path2 = $path . $file;
	            if (is_dir($path2)) {
	                $this->getList($path2, $allowFiles, $files);
	            } else {
	            	if($allowFiles!='all'){
		                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
		                    $files[] = array(
		                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
		                        'mtime'=> filemtime($path2)
		                    );
		                }
	            	}else{
	            		$files[] = array(
		                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
		                        'mtime'=> filemtime($path2)
	            		);
	            	}
	            }
	        }
	    }
	    return $files;
	}

	/**
	 * 得到路径
	 */
	public function getPath($rootpath,$path){
		$path = __ROOT__.$rootpath.$path;
		return $path;
	}
}