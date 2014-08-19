<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>

	<script type="text/javascript" src="/testvv/1/Public/js/jquery-2.0.2.js"></script>
    <script type="text/javascript" src="/testvv/1/Public/ueditor/ueditor.config.js"></script>    
    <script type="text/javascript" src="/testvv/1/Public/ueditor/ueditor.all.min.js"></script>
    <script>
    $(function(){
        var ue = UE.getEditor('container',{
            serverUrl :'<?php echo U('Home/Index/ueditor');?>'
        });
    })
    </script>
</head>
<body>
<script id="container" name="content" type="text/plain">
        这里写你的初始化内容
    </script>
</body>
</html>