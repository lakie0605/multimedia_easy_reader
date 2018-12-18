<?php

if (isset($_POST['collect']) && !is_null($_POST['collect'])) {
    $value = $_POST['collect'] . ";";
    dataVerification($value);
    collect($value);
} elseif (isset($_POST['uncollect']) && !is_null($_POST['uncollect'])) {
    $value = $_POST['uncollect'] . ";";
    dataVerification($value);
    unCollect($value);
} elseif (isset($_POST['record']) && !is_null($_POST['record'])) {
    $value = $_POST['record'];
    dataVerification($value);
    record($value);
} else {
    die ("<script>history.back();</script>");
}

function dataVerification($value) {
    if (strstr($value, '/')) {
        $valueArr = explode('/', $value);
        $slicer = '/';
    } else {
        $valueArr = explode('\\', $value);
        $slicer = '\\';
    }
    if (strstr($valueArr[0], $slicer) || strstr($valueArr[0], '.')) {
        print_r($valueArr[0]);
        die("<script>alert('是你飘了？还是我提不动刀了？');</script>");
    }
    $currentDir = '.' . $slicer . $valueArr[0];
    if (is_dir($currentDir)) {
        $dir = opendir($valueArr[0]);
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
    $img = $valueArr[1];
    if (substr($img, -1) == ';') {
        $img = substr($img, 0, strlen($img) - 1);
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
 * @param $value
 */
function collect($value) {
    //根据环境判断目录
    if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
        if (isset($_POST['category']) && $_POST['category'] === 'video') {
            $collectFileDir = 'textDir\video_collect.txt';
        } else {
            $collectFileDir = 'textDir\collect.txt';
        }
    } else {
        if (isset($_POST['category']) && $_POST['category'] === 'video') {
            $collectFileDir = 'textDir/video_collect.txt';
        } else {
            $collectFileDir = 'textDir/collect.txt';
        }
    }
    $collectFile = fopen($collectFileDir, 'a+') or die("can't open file");
    $fileSize = filesize($collectFileDir) > 0 ? filesize($collectFileDir) : 1;
    $data = fread($collectFile, $fileSize);
    if (strstr($data, $value)) {
        die ("<script>alert('已经收藏了！');history.back();</script>");
    }
    fwrite($collectFile, $value);
    fclose($collectFile);
    echo "<script>history.back();</script>";
}

/**
 * 取消收藏
 *
 * @param $value
 */
function unCollect($value) {
    if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
        if (isset($_POST['category']) && $_POST['category'] === 'video') {
            $collectFileDir = 'textDir\video_collect.txt';
        } else {
            $collectFileDir = 'textDir\collect.txt';
        }
    } else {
        if (isset($_POST['category']) && $_POST['category'] === 'video') {
            $collectFileDir = 'textDir/video_collect.txt';
        } else {
            $collectFileDir = 'textDir/collect.txt';
        }
    }
    //读取收藏内容
    $readFile = fopen($collectFileDir, 'r') or die("can't open file");
    $fileSize = filesize($collectFileDir) > 0 ? filesize($collectFileDir) : 1;
    $fileData = fread($readFile, $fileSize);
    fclose($readFile);
    //写入新的收藏内容
    $writeFile = fopen($collectFileDir, 'w+') or die("can't open file");
    $data = str_replace($value, '', $fileData);
    fwrite($writeFile, $data);
    fclose($writeFile);
    $arrValue = explode('/', $value);
    $oldDir = $arrValue[0];
    if (isset($_POST['category']) && $_POST['category'] === 'video') {
        $url = 'index.php?type=v&dir=collect&oldDir=' . $oldDir;
        $urlJson = json_encode($url);
        echo "<script>var url = $urlJson;alert('取消成功!');window.location.href=url;</script>";
    } else {
        echo "<script>alert('取消成功，刷新生效！');history.back();</script>";
    }
}

/**
 * 保存记录
 *
 * @param $value
 */
function record($value) {
    $logDir = ['collect_record', 'record'];
    if (in_array($_POST['catalog'], $logDir)) {
        if (strtoupper(substr(PHP_OS,0,3)) === 'WIN') {
            $file = 'textDir\\' . $_POST['prefix'] . $_POST['catalog'] . '.txt';
        } else {
            $file = 'textDir/' . $_POST['prefix'] . $_POST['catalog'] . '.txt';
        }
        $collectFile = fopen($file, 'w+') or die("can't open file");
        fwrite($collectFile, $value);
        fclose($collectFile);
        echo "<script>alert('记录成功！');history.back();</script>";
    } else {
        die ("<script>alert('请勿篡改数据！');history.back();</script>");
    }
}