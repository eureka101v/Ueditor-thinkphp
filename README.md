Ueditor-thinkphp
================

Ueditor for thinkphp插件
兼容本地与sae平台
暂只支持thinkphp3.2后的版本

### 使用说明
安装：

将Ueditor 目录下 Public，Thinkphp 文件夹与你的项目目录下的Public ， Thinkphp文件夹合并，ueditor.json放置在你项目的配置文件夹

使用：

- 给你的控制器添加ueditor方法
```php
public function ueditor(){
    	$data = new \Org\Util\Ueditor();
		echo $data->output();
    }
```
- 添加以下代码到你视图的view文件
```javascript
    <js href="__PUBLIC__/js/jquery-2.0.2.js" />
    <js href="__PUBLIC__/ueditor/ueditor.config.js" />    
    <js href="__PUBLIC__/ueditor/ueditor.all.min.js" />
    <script>
    $(function(){
        var ue = UE.getEditor('container',{
            serverUrl :'{:U('模块/控制器/ueditor')}'
        });
    })
    </script>
```
上传的话本地最后会默认上传到项目目录下Uploads文件夹，sae平台会上传到名字为uploads的domain，如果想更改可通过配置Org\Util\Ueditor下的rootpath变量实现，上传子目录的更改可通过配置ueditor.json实现
    
