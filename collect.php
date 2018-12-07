<?php

if ($_POST['collect']) {
    $imgDir = $_POST['collect'] . ";";
    dataVerification($imgDir);
    collect($imgDir);
} elseif ($_POST['uncollect']) {
    $imgDir = $_POST['uncollect'] . ";";
    dataVerification($imgDir);
    unCollect($imgDir);
} elseif ($_POST['record']) {
    $imgDir = $_POST['record'];
    dataVerification($imgDir);
    record($imgDir);
} else {
    die ("<script>history.back();</script>");
}

function dataVerification($imgDir) {
    if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
        $imgDirArr = explode('\\', $imgDir);
        $slicer = '\\';
    } else {
        $imgDirArr = explode('/', $imgDir);
        $slicer = '/';
    }
    if (strstr($imgDirArr[0], $slicer) || strstr($imgDirArr[0], '.')) {
        die("<script>alert('是你飘了？还是我提不动刀了？');</script>");
    }
    $currentDir = '.' . $slicer . $imgDirArr[0];
    if (is_dir($currentDir)) {
        $dir = opendir($imgDirArr[0]);
        while(($filename = readdir($dir)) !== false) {
            if ($filename != "." && $filename != "..") {
                $filename = iconv("GB2312", "UTF-8", $filename);
                $imgArr[] = $filename;
            }
        }
        closedir($dir);
    } else {
        die ("<script>alert('目录不存在！');history.back();</script>");
    }
    $img = $imgDirArr[1];
    if (substr($img, -1) == ';') {
        $img = substr($img, 0, mb_strlen($img) - 1);
    }
    if (in_array($img, $imgArr)) {
        return 1;
    } else {
        die ("<script>alert('饭可以乱吃，数据不能乱改！');history.back();</script>");
    }

}
/**
 * 收藏
 *
 * @param $imgDir
 */
function collect($imgDir) {
    //根据环境判断目录
    if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
        $collectFileDir = 'textDir\collect.txt';
    } else {
        $collectFileDir = 'textDir/collect.txt';
    }
    $collectFile = fopen($collectFileDir, 'a+') or die("can't open file");
    $data = fread($collectFile, filesize($collectFileDir));
    if (strstr($data, $imgDir)) {
        die ("<script>alert('已经收藏了！');history.back();</script>");
    }
    fwrite($collectFile, $imgDir);
    fclose($collectFile);
    echo "<script>history.back();</script>";
}

/**
 * 取消收藏
 *
 * @param $imgDir
 */
function unCollect($imgDir) {
    if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
        $collectFileDir = 'textDir\collect.txt';
    } else {
        $collectFileDir = 'textDir/collect.txt';
    }
    //读取收藏内容
    $readFile = fopen($collectFileDir, 'r') or die("can't open file");
    $fileData = fread($readFile, filesize($collectFileDir));
    fclose($readFile);
    //写入新的收藏内容
    $writeFile = fopen($collectFileDir, 'w+') or die("can't open file");
    $data = str_replace($imgDir, '', $fileData);
    fwrite($writeFile, $data);
    fclose($writeFile);
    echo "<script>alert('取消成功，刷新生效！');history.back();</script>";
}

/**
 * 保存记录
 *
 * @param $imgDir
 */
function record($imgDir) {
    $logDir = ['collect_record', 'record'];
    if (in_array($_POST['catalog'], $logDir)) {
        if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
            $file = 'textDir\\' . $_POST['prefix'] . $_POST['catalog'] . '.txt';
        } else {
            $file = 'textDir/' . $_POST['prefix'] . $_POST['catalog'] . '.txt';
        }
        $collectFile = fopen($file, 'w+') or die("can't open file");
        fwrite($collectFile, $imgDir);
        fclose($collectFile);
        echo "<script>alert('记录成功！');history.back();</script>";
    } else {
        die ("<script>alert('请勿篡改数据！');history.back();</script>");
    }
}