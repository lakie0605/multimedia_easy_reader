<?php
//获取类型和目录
if (isset($_GET['type'])) {
    $type = $_GET['type'];
    if (isset($_GET['dir'])) {
        if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
            $slicer = '\\';
        } else {
            $slicer = '/';
        }
        if (strstr($_GET['dir'], $slicer) || strstr($_GET['dir'], '.')) {
            die("<script>alert('是你飘了？还是我提不动刀了？');</script>");
        }
        if ($_GET['dir'] !== 'collect') {
            $currentDir = '.' . $slicer . $_GET['dir'];
            if (!is_dir($currentDir)) {
                die ("<script>alert('目录不存在！');</script>");
            }
        }
    }
} else {
    die("<script>alert('饭可以乱吃，数据不能乱传！');</script>");
}

//调用函数
if ($type == 'p') {
    //获取目录
    if (!isset($_GET['dir'])) {
        $dir = 'image';
    } else {
        $dir = $_GET['dir'];
    }
    //获取显示模式
    if (!isset($_GET['auto'])) {
        $pattern = 0;
    } else {
        $pattern = 1;
    }
    image($dir, $pattern);
} else {
    if (!isset($_GET['dir'])) {
        $dir = 'video';
    } else {
        $dir = $_GET['dir'];
    }
    video($dir);
}

function video($dir) {
    //根据环境判断目录
    if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
        $firstDir = '.\\textDir\\';
    } else {
        $firstDir = './textDir/';
    }
    if ($dir === 'collect') {
        $collectFileDir = $firstDir . 'video_' . $dir . '.txt';
        $collectFile = fopen($collectFileDir,'r') or die ("can't open file");
        $fileSize = filesize($collectFileDir) > 0 ? filesize($collectFileDir) : 1;
        $data = fread($collectFile, $fileSize);
        $srcArr = explode(';', $data);
        //删除最后一个空白字符
        if (!$srcArr[count($srcArr) - 1]) {
            unset($srcArr[count($srcArr) - 1]);
        }
        if (count($srcArr) < 1) {
            $oldDir = $_GET['oldDir'];
            $url = 'index.php?type=v&dir=' . $oldDir;
            $urlJson = json_encode($url);
            die ("<script>var url = $urlJson;window.location.href=url;</script>");
        }
        fclose($collectFile);
        foreach ($srcArr as $src) {
            $path = __DIR__;
            if (strstr($path, '/')) {
                $arrPath = explode('/', $path);
            } else {
                $arrPath = explode('\\', $path);
            }
            if (strstr($src, '/')) {
                $arrUrl = explode('/', $src);
            } else {
                $arrUrl = explode('\\', $src);
            }
            $presentFolder =  $arrPath[count($arrPath) - 1];
            $filename = $arrUrl[count($arrUrl) - 1];
            $url = '/' .  $presentFolder . '/video.php?c&dir=' . $arrUrl[0] . '&name=' . $filename;
            echo "<a href=$url>$filename</a><br>";
        }
    } else {
        if (is_dir($dir)) {
            $handler = opendir($dir);
            while(($filename = readdir($handler)) !== false)
            {
                if($filename != "." && $filename != "..") {
                    $filename = iconv("GB2312", "UTF-8", $filename);
                    $path = __DIR__;
                    if (strstr($path, '/')) {
                        $arrPath = explode('/', $path);
                    } else {
                        $arrPath = explode('\\', $path);
                    }
                    $presentFolder =  $arrPath[count($arrPath) - 1];
                    $url = '/' .  $presentFolder . '/video.php?dir=' . $dir . '&name=' . $filename;
                    echo "<a href=$url>$filename</a><br>";
                }
            }
            closedir($handler);
        } else {
            die ("<script>alert('目录不存在！');</script>");
        }
    }
}

function image($dir, $pattern) {
    //根据环境判断目录
    if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
        $firstDir = '.\\textDir\\';
    } else {
        $firstDir = './textDir/';
    }
    if ($dir === 'collect') {
        $collectFileDir = $firstDir . $dir . '.txt';
        $collectFile = fopen($collectFileDir,'r') or die ("can't open file");
        $fileSize = filesize($collectFileDir) > 0 ? filesize($collectFileDir) : 1;
        $data = fread($collectFile, $fileSize);
        $srcArr = explode(';', $data);
        //删除最后一个空白字符
        if (!$srcArr[count($srcArr) - 1]) {
            unset($srcArr[count($srcArr) - 1]);
        }
        if (count($srcArr) < 1) {
            die ("<script>alert('没有内容！');history.back();</script>");
        }
        fclose($collectFile);
    } else {
        if (is_dir($dir)) {
            $handler = opendir($dir);
            /*其中$filename = readdir($handler)
            每次循环时将读取的文件名赋值给$filename，$filename !== false。
            一定要用!==，因为如果某个文件名如果叫'0′，或某些被系统认为是代表false，用!=就>会停止循环
            */
            while(($filename = readdir($handler)) !== false)
            {
                //略过linux目录的名字为'.'和‘..'的文件
                if($filename != "." && $filename != "..") {
                    $filename = iconv("GB2312", "UTF-8", $filename);
                    $srcArr[] = $dir . '/' . $filename;
                }
            }
            //关闭目录
            closedir($handler);
        } else {
            die ("<script>alert('目录不存在！');</script>");
        }
    }
    //读取浏览记录
    $index = 0;
    $user = '';
    $catalogPrefix = '';
    if (isset($_GET['user']) && !is_null($_GET['user'])) {
        //根据不同用户读取不同浏览记录
        $user = $_GET['user'];
        $catalogPrefix = $_GET['user'] . '_';
    }
    if (isset($_GET['h'])) {
        if ($dir === 'collect') {
            $recordFileDir = $firstDir . $catalogPrefix . 'collect_record.txt';
        } else {
            $recordFileDir = $firstDir . $catalogPrefix . 'record.txt';
        }
        $recordFile = fopen($recordFileDir,'a+');
        $size = filesize($recordFileDir) > 0 ? filesize($recordFileDir) : 1;
        $record = fread($recordFile, $size);
        if (strlen($record) > 0) {
            foreach ($srcArr as $key => $value) {
                if ($record === $value) {
                    $index = $key;
                }
            }
        } else {
            $index = 0;
        }
        fclose($recordFile);
    }
    //数据加密
    $secretKey = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    foreach ($srcArr as $value) {
        $place = rand(0, strlen($secretKey) - 1);
        $key = substr($secretKey, $place, 1);
        //获取‘/’位指标
        $subscript1 = stripos($value, '/');
        //获取‘.’位指标
        $subscript2 = stripos($value, '.');
        if ($subscript2 - $subscript1 == 2) {
            $srcEncrypt[] = substr_replace($value, $key, $subscript2 - 1, 0);
        } else {
            $srcEncrypt[] = substr_replace($value, $key, $subscript2 - 3, 0);
        }
    }
    //自动模式所需变量
    $srcJson = json_encode($srcEncrypt);
    $dirJson = json_encode($dir);
    $indexJson = json_encode($index);
    $userJson = json_encode($user);
    //根据需求显示不同模式
    if (!$pattern) {
        foreach ($srcArr as $src) {
            echo "<img src=$src alt = $filename><br><br>";
        }
    } else {
        echo "<html lang='en' style='overflow-x:hidden'>
              <head>
                <meta charset='UTF-8'>
                <title>image</title>
                <script type='text/javascript'>
                    /*定义全局变量*/
                    var index = $indexJson;
                    var img = $srcJson;
                    var imgObject;
                    var timer;
                    var dir = $dirJson;
                    var subscript1;
                    var subscript2;
                    var imgSrc;
                    var user = $userJson;
 
                    //收藏与取消收藏
                    function collect() {
                        if (dir === 'collect') {
                            document.getElementById('collect').name = 'uncollect';
                            document.getElementById('buttonName').value = '取消收藏';
                            document.getElementById('link').href = 'javascript:history.back();';
                            document.getElementById('link').innerHTML = '所有列表';
                        } else {
                            document.getElementById('collect').name = 'collect';
                            document.getElementById('buttonName').value = '收藏';
                            document.getElementById('link').href = '/bt?type=p&&dir=collect&&auto=1&h&user=' + user;
                        }
                        dirDecode();
                        record();
                        document.getElementById('collect').value = imgSrc;
                        return 1;
                    }
                    
                    //保存浏览记录
                    function record() {
                        if (dir === 'collect') {
                            document.getElementById('catalog').value = 'collect_record';
                        } else {
                            document.getElementById('catalog').value = 'record';
                        }
                        
                        document.getElementById('record').value = imgSrc;
                    }
                    
                    window.onload = function () {/*当页面加载完成后再执行这部分js代码*/
                        imgObject = document.getElementsByTagName('img')[0];
                        var info = document.getElementById('info');
                        info.innerHTML = '一共是'+img.length+'张图片，现在是第'+(index+1)+'张';
                        collect();
                    }
                    
                    function dirDecode() {
                        subscript1 = img[index].indexOf('/');
                        subscript2 = img[index].indexOf('.');
                        if (subscript2 - subscript1 == 3) {
                            imgSrc = img[index].substring(0, subscript2 - 2) + img[index].substring(subscript2 - 1, img[index].length);
                        } else {
                            imgSrc = img[index].substring(0, subscript2 - 4) + img[index].substring(subscript2 - 3, img[index].length);
                        }
                        info.innerHTML = '一共是'+img.length+'张图片，现在是第'+(index+1)+'张';
                        return 1;
                    }
                    
                    function start() {
                        clearInterval(timer);
                        timer = setInterval(function () {
                            if (imgObject.complete) {  /*如果图片加载完成，进入下一步*/
                                index++;
                                if(index > img.length-1){
                                    index = 0;
                                }
                                dirDecode();
                                imgObject.src = imgSrc;
                                collect();
                            }
                        },3000);
                    }
                    
                    function stop() {
                        //alert('停止播放');
                        clearInterval(timer);
                    }
                    
                    function getNextImg() {
                        var info = document.getElementById('info');
                        index++;
                        if(index > img.length-1){  /*当图片已经翻到最后一张时，跳转到第一张图片*/
                            index = 0;
                        }
                        dirDecode();
                        imgObject.src = imgSrc;
                        collect();
                    }
                    
                    function getProImg() {
                        var info = document.getElementById('info');
                        index--;
                        if(index < 0){            /*当图片已经翻到第一张时，跳转到最后一张图片*/
                            index = img.length - 1;
                        }
                        dirDecode();
                        imgObject.src = imgSrc;
                        collect();
                    }
                    
                    function goToImg() {
                        var info = document.getElementById('info');
                        index = document.getElementById('page').value -1;
                        if(index > img.length-1){  /*如果值大于最大值，就显示最后一张*/
                            index = img.length-1;
                        }
                        if(index < 0){            /*如果值小于最小值，就显示第一张*/
                            index = 0;
                        }
                        dirDecode();
                        imgObject.src = imgSrc;
                        collect();
                    }
                </script>
             </head>
             <body style='top: 0;left: 0;width: 100%;height: 100%' bgcolor='#c6c6c6'>
                <div id='outer' style='text-align: center;top: 0;left: 0;width: 100%;overflow-x:hidden'>
                    <p style='display: inline-block;color: #937c1a' id='info'>这是第X张图片</p>
                    <a style='display: inline-block;' id='link' href=''>收藏列表</a>
                </div>
                <div style='text-align: center;'>
                    <img src=$srcArr[$index] alt='' style='width: auto;height: 1345px'><br>
                </div>
                <div style='text-align: center;top: 0;left: 0;width: 100%;overflow-x:hidden'>
                    <button id='prev' onclick='getProImg()' style='width: 120px;height: 50px'>上一张</button>
                    <button id='next' onclick='getNextImg()' style='width: 120px;height: 50px'>下一张</button>
                    <button id='start' onclick='start();' style='width: 120px;height: 50px'>开始</button>
                    <button id='stop' onclick='stop();' style='width: 120px;height: 50px'>停止</button>
                    <span style='font-size: 12px;color: #937c1a'>第</span>
                    <input id='page' type='text' name='num' style='width: 60px;height: 25px'/>
                    <span style='font-size: 12px;color: #937c1a'>张</span>
                    <button id='goto' onclick='goToImg();' style='width: 120px;height: 50px'>跳转</button>
                    <form style='display: inline;' method='post' action='collect.php'>
                        <input type='hidden' id='collect' name='' value=''>
                        <input style='margin-top: 5px;width: 120px;height: 50px;background: red; color: #ff0;font-size:20px;' type='submit' id='buttonName' value=''>
                    </form>
                    <form style='display: inline;' method='post' action='collect.php'>
                        <input type='hidden' id='record' name='record' value=''>
                        <input type='hidden' id='catalog' name='catalog' value=''>
                        <input type='hidden' id='prefix' name='prefix' value=$catalogPrefix>
                        <input style='margin-top: 5px;width: 120px;height: 50px;background: #470293; color: #933d03;font-size:20px;' type='submit' value='保存记录'>
                    </form>
                </div>
                <script type='text/javascript'>
                    function load (){
                        var beginX, beginY, endX, endY, swipeUp, swipeDown;
                        var ele = document.getElementsByTagName('img')[0];
                        ele.addEventListener('touchstart',touch, false);
                        ele.addEventListener('touchmove',touch, false);
                        ele.addEventListener('touchend',touch, false);

                        function touch (event){
                            var event = event || window.event;
                            switch(event.type){
                                case 'touchstart':
                                    beginX = event.targetTouches[0].screenX;
                                    beginY = event.targetTouches[0].screenY;
                                    swipeUp = false, swipeDown = false;
                                    break;
                                case 'touchmove':
                                    endX = event.targetTouches[0].screenX;
                                    endY = event.targetTouches[0].screenY;
                                    //上下滑动
                                    if (Math.abs(endX - beginX) - Math.abs(endY - beginY) < 0) {
                                        /*向下滑动*/
                                        if (endY - beginY > 112) {
                                            swipeUp = false;
                                            swipeDown = true;
                                        }
                                        /*向上滑动*/
                                        else if (beginY - endY > 112){
                                            swipeDown = false;
                                            swipeUp = true;
                                        }
                                        /*不动*/
                                        else {
                                            swipeDown = false;
                                            swipeUp = false;
                                        }
                                    }
                                    break;
                                case 'touchend':
                                    if (Math.abs(endX - beginX) - Math.abs(endY - beginY) < 0) {
                                        event.stopPropagation();
                                        event.preventDefault();
                                        if (swipeUp) {
                                            swipeUp = !swipeUp;
                                            /*向上滑动*/
                                            getNextImg();
                                        }
                                        if(swipeDown) {
                                            swipeDown = !swipeDown;
                                            /*向下滑动*/
                                            getProImg();
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                    window.addEventListener('load',load, false);
             </script>
             </body>
             </html>";
    }
}
