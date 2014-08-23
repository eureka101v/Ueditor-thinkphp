<?php
/**
 * Ueditor插件
 * @author Nintendov
 */

namespace Org\Util;

class Ueditor{
	
	//public $uid;//要操作的用户id 如有登录需要则去掉注释
	
	private $output;//要输出的数据
	
	private $st;
	
	private $rootpath = '/Uploads';
	
	public function __construct($uid = ''){
		//uid 为空则导入当前会话uid
		//if(''===$uid) $this->uid = session('uid');
		
		\Vin\FileStorage::connect(STORAGE_TYPE);
		//导入设置
		$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(CONF_PATH."ueditor.json")), true);
		
		$action = htmlspecialchars($_GET['action']);
		
		switch($action){
			case 'config':
		        $result = json_encode($CONFIG);
		        break;
		        
		    case 'uploadimage':
				$config = array(
		            "pathFormat" => $CONFIG['imagePathFormat'],
		            "maxSize" => $CONFIG['imageMaxSize'],
		            "allowFiles" => $CONFIG['imageAllowFiles']
				);
				$fieldName = $CONFIG['imageFieldName'];
				$result = $this->uploadFile($config, $fieldName);
				break;
				
			case 'uploadscrawl':
				$config = array(
		            "pathFormat" => $CONFIG['scrawlPathFormat'],
		            "maxSize" => $CONFIG['scrawlMaxSize'],
		            "allowFiles" => $CONFIG['scrawlAllowFiles'],
		            "oriName" => "scrawl.png"
		            );
		            $fieldName = $CONFIG['scrawlFieldName'];
		            $result=$this->uploadBase64($config,$fieldName);
		            break;
		            
		    case 'uploadvideo':
				$config = array(
		            "pathFormat" => $CONFIG['videoPathFormat'],
		            "maxSize" => $CONFIG['videoMaxSize'],
		            "allowFiles" => $CONFIG['videoAllowFiles']
				);
				$fieldName = $CONFIG['videoFieldName'];
				$result=$this->uploadFile($config, $fieldName);
				break;
				
			case 'uploadfile':
				// default:
				$config = array(
		            "pathFormat" => $CONFIG['filePathFormat'],
		            "maxSize" => $CONFIG['fileMaxSize'],
		            "allowFiles" => $CONFIG['fileAllowFiles']
				);
				$fieldName = $CONFIG['fileFieldName'];
				$result=$this->uploadFile($config, $fieldName);
				break;
				
			case 'listfile':
				$config=array(
					'allowFiles' => $CONFIG['fileManagerAllowFiles'],
					'listSize' => $CONFIG['fileManagerListSize'],
					'path' => $CONFIG['fileManagerListPath'],
				);
				$result = $this->listFile($config);
				break;
				
			case 'listimage':
				$config=array(
					'allowFiles' => $CONFIG['imageManagerAllowFiles'],
					'listSize' => $CONFIG['imageManagerListSize'],
					'path' => $CONFIG['imageManagerListPath'],
				);
				$result = $this->listFile($config);
				break;
			
			case 'catchimage':
				$config = array(
					"pathFormat" => $CONFIG['catcherPathFormat'],
				    "maxSize" => $CONFIG['catcherMaxSize'],
				    "allowFiles" => $CONFIG['catcherAllowFiles'],
				    "oriName" => "remote.png"
				);
				$fieldName = $CONFIG['catcherFieldName'];
				$result = $this->saveRemote($config , $fieldName);
				break;
				
			default:
		        $result = json_encode(array(
		            'state'=> 'wrong require'
		        ));
		        break;
			
		}
		
		if (isset($_GET["callback"])) {
			if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
				$this->output = htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
			} else {
				$this->output = json_encode(array(
		            'state'=> 'callback参数不合法'
		            ));
			}
		} else {
			$this->output = $result;
		}
	}
	
	
	/**
	 * 
	 * 输出结果
	 * @param data 数组数据
	 * @return 组合后json格式的结果
	 */
	public function output(){
		return $this->output;
	}
	
	/**
	 * 上传文件方法
	 * 
	 */
	private function uploadFile($config,$fieldName){
		
		
		$upload = new \Think\Upload();
		$upload->maxSize   =     $config['maxSize'] ;// 设置附件上传大小
		$upload->exts      =     $this->format_exts($config['allowFiles']);// 设置附件上传类型
		$upload->rootPath  =     '.'.$this->rootpath; // 设置附件上传根目录
		$upload->autoSub = false;
		$upload->savePath  =     $this->getFullPath($config['pathFormat']); // 设置附件上传（子）目录
		$info=$upload->uploadOne($_FILES[$fieldName]);
		$rootpath = $this->rootpath;
		
		if(!$info){
			$data = array(
				"state"=>$upload -> getError(),
			);
		}else{
			$data = array(
				'state'=>"SUCCESS",
				'url'=>\Vin\FileStorage::getPath($rootpath,$info['savepath'].$info['savename']),
				'title'=>$info['savename'],
				'original'=>$info['name'],
				'type'=>'.' . $info['ext'],
				'size'=>$info['size'],
			);
		}
		return json_encode($data);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	private function uploadBase64($config,$fieldName){
		$data = array();
		
		$base64Data = $_POST[$fieldName];
        $img = base64_decode($base64Data);
        $path = $this->getFullPath($config['pathFormat']);
        
        if(strlen($img)>$config['maxSize']){
        	$data['states'] = 'too large';
        	return json_encode($data);
        }
        
        $rootpath = $this->rootpath;
        
        //替换随机字符串
        $imgname = uniqid().'.png';
        $filename = $path.$imgname;
        
        if(\Vin\FileStorage::put($rootpath,$filename,$img)){
        	$data=array(
        		'state'=>'SUCCESS',
        		'url'=>\Vin\FileStorage::getPath($rootpath,$filename),
        		'title'=>$imgname,
        		'original'=>'scrawl.png',
        		'type'=>'.png',
        		'size'=>strlen($img),
        	
        	);
        }else{
        	$data=array(
        		'state'=>'cant write',
        	);
        }
        return json_encode($data);
	}
	
	/**
	 * 列出文件夹下所有文件，如果是目录则向下
	 */
	private function listFile($config){
		$allowFiles = substr(str_replace(".", "|", join("", $config['allowFiles'])), 1);
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $config['listSize'];
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
		$end = $start + $size;
		
		$rootpath = $this->rootpath;
		
		$path = $config['path'];
		$files = \Vin\FileStorage::listFile($rootpath,$path, $allowFiles);
		//return $files;
		if (!count($files)) {
		    return json_encode(array(
		        "state" => "no match file",
		        "list" => array(),
		        "start" => $start,
		        "total" => count($files)
		    ));
		}
		
		/* 获取指定范围的列表 */
		$len = count($files);
		for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
		    $list[] = $files[$i];
		}
		//倒序
		//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
		//    $list[] = $files[$i];
		//}
		
		/* 返回数据 */
		$result = json_encode(array(
		    "state" => "SUCCESS",
		    "list" => $list,
		    "start" => $start,
		    "total" => count($files)
		));
		
		return $result;
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	private function saveRemote($config , $fieldName){
		$list = array();
		if (isset($_POST[$fieldName])) {
		    $source = $_POST[$fieldName];
		} else {
		    $source = $_GET[$fieldName];
		}
		foreach ($source as $imgUrl) {
		    $upload = new \Think\Upload();
		    
			$imgUrl = htmlspecialchars($imgUrl);
	        $imgUrl = str_replace("&amp;", "&", $imgUrl);
	
	        //http开头验证
	        if (strpos($imgUrl, "http") !== 0) {
	            $data = array('state'=>'不是http链接');
	            return json_encode($data);
	        }
	        //格式验证(扩展名验证和Content-Type验证)
	        $fileType = strtolower(strrchr($imgUrl, '.'));
	        if (!in_array($fileType, $config['allowFiles']) || stristr($heads['Content-Type'], "image")) {
	            $data = array("state"=>"错误文件格式");
	            return json_encode($data);
	        }
	        
	         //打开输出缓冲区并获取远程图片
	        ob_start();
	        $context = stream_context_create(
	            array('http' => array(
	                'follow_location' => false // don't follow redirects
	            ))
	        );
	        readfile($imgUrl, false, $context);
	        $img = ob_get_contents();
	        ob_end_clean();
	        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);
	        
	        $path = $this->getFullPath($config['pathFormat']);
			if(strlen($img)>$config['maxSize']){
	        	$data['states'] = 'too large';
	        	return json_encode($data);
	        }
	        
	        $rootpath = $this->rootpath;
	        
	        $imgname = uniqid().'.png';
	        $filename = $path.$imgname;
	        
	        $oriName = $m ? $m[1]:"";
		    
        	if(\Vin\FileStorage::put($rootpath,$filename,$img)){
			    array_push($list, array(
			        "state" => 'SUCCESS',
			        "url" => \vin\FileStorage::getPath($rootpath,$filename),
			        "size" => strlen($img),
			        "title" => $imgname,
			        "original" => $oriName,
			        "source" => htmlspecialchars($imgUrl)
			    ));
        	}else{
        		array_push($list,array('state'=>'文件写入失败'));
        	}
		}
		
		/* 返回抓取数据 */
		return json_encode(array(
		    'state'=> count($list) ? 'SUCCESS':'ERROR',
		    'list'=> $list
		));
	}

	/**
     * 规则替换命名文件
     * @param $path
     * @return string
     */
    private function getFullPath($path)
    {
        //替换日期事件
        $t = time();
        $d = explode('-', date("Y-y-m-d-H-i-s"));
        $format = $path;
        $format = str_replace("{yyyy}", $d[0], $format);
        $format = str_replace("{yy}", $d[1], $format);
        $format = str_replace("{mm}", $d[2], $format);
        $format = str_replace("{dd}", $d[3], $format);
        $format = str_replace("{hh}", $d[4], $format);
        $format = str_replace("{ii}", $d[5], $format);
        $format = str_replace("{ss}", $d[6], $format);
        $format = str_replace("{uid}", $this->uid, $format);
        
        return $format;
    }
    
	private function format_exts($exts){
		$data=array();
		foreach ($exts as $key => $value) {
			$data[]=ltrim($value,'.');
		}
		return $data;
	}
	
}