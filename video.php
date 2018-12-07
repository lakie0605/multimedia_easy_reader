<?php
if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
    $slicer = '\\';
} else {
    $slicer = '/';
}
if (strstr($_GET['dir'], $slicer) || strstr($_GET['dir'], '.')) {
    die("<script>alert('是你飘了？还是我提不动刀了？');</script>");
}
$dir = $_GET['dir'] . $slicer;
$beginName = $_GET['name'];
//打开目录获取播放列表
$currentDir = '.' . $slicer . $dir;
if (is_dir($currentDir)) {
    $handler = opendir($dir);
    while(($filename = readdir($handler)) !== false)
    {
        if($filename != "." && $filename != "..") {
            $filename = iconv("GB2312", "UTF-8", $filename);
            $videoNames[] = $filename;
        }
    }
    closedir($handler);
} else {
    die ("<script>alert('目录不存在！');</script>");
}
//获取视频相对路径
$path = __DIR__;
if (strstr($path, '/')) {
    $arrPath = explode('/', $path);
} else {
    $arrPath = explode('\\', $path);
}
$presentFolder =  $arrPath[count($arrPath) - 1];
//转换json数据
$videoNamesJson = json_encode($videoNames);
$beginNameJson = json_encode($beginName);
$dirJson = json_encode($dir);
$subscript = json_encode(array_search($beginName, $videoNames));

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
        <html xmlns=\"http://www.w3.org/1999/xhtml\">
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
            <title>video</title>
        </head>
        <script type='text/javascript'>
            var index;
            var video = $videoNamesJson;
            var beginVideo = $beginNameJson;
            var videoObject;
            var dir = $dirJson;
            var subscript = $subscript;
            
            window.onload = function(){
                index = subscript;
                videoObject = document.getElementById('videoID');
                videoObject.onended = function() 
                {
                    index++;
                    if(index > video.length-1){
                        index = 0;
                    }
                    videoObject.src = dir.concat(video[index]);
                };
            }
            
            function getNextVideo() {
                var info = document.getElementById('info');
                index++;
                if(index > video.length-1){  /*当视频已经翻到最后一个时，跳转到第一个视频*/
                    index = 0;
                }
                videoObject.src = dir.concat(video[index]);
                info.innerHTML = video[index];
            }
                    
            function getProVideo() {
                var info = document.getElementById('info');
                index--;
                if(index == -1){            /*当视频已经翻到第一个时，跳转到最后一个视频*/
                    index = video.length - 1;
                }
                videoObject.src = dir.concat(video[index]);
                info.innerHTML = video[index];
            }
            
            function goBack() {
                history.back();
            }
        </script>
        <body>
            <div>
                <p id='info' style='text-align: center;font-size: 35px;color: #987408'>$beginName</p>
            </div>
            <div style='background: #090909'>
                <video id='videoID' style='width: 100%;height: 100%' src='$dir$beginName' autoplay controls='controls' ></video>
            </div>
            <div style='text-align: center;font-size: 55px;'>
                <a href='javascript:getProVideo()' style='text-decoration : none'><<</a>&nbsp&nbsp&nbsp&nbsp
                <a href='javascript:goBack()' style='text-decoration : none'>><</a>&nbsp&nbsp&nbsp&nbsp
                <a href='javascript:getNextVideo()' style='text-decoration : none'>>></a>
            </div>
        </body>
        </html>";