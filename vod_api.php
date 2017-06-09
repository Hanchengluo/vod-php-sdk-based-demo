<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once './include.php';
use qcloudcos\Cosapi;
use qcloudcos\Conf;

class Vodapi {
/**
* 上传视频（和封面）文件，自动完成向VOD的发起上传操作，向COS的数据上传和向VOD的确认上传
*/
    public static function upload($videoPath, $coverPath = null) {
        $secretId = Conf::SECRET_ID;
        $secretKey = Conf::SECRET_KEY;
        $videoType = end(explode(".", $videoPath));
        if (isset($coverPath)) {
             $coverType = end(explode(".", $coverPath));
        }
        
        if (isset($coverType)) {
            $package = array (
                'videoType' => $videoType,
                'coverType' => $coverType,
            );
        } else {
            $package = array (
                'videoType' => $videoType,
            );
        }
    
        // 初始化VOD api
        $config = array('SecretId'       => $secretId,
                        'SecretKey'      => $secretKey,
                        'RequestMethod'  => 'POST');
    
        $vod = QcloudApi::load(QcloudApi::MODULE_VOD, $config);
        $rsp = $vod->ApplyUpload($package);
        echo "ApplyUpload|recv:" . json_encode($rsp) . "\n";
    
        // 获取ApplyUpload响应结果
        $bucket = $rsp['storageBucket'];
        $region = $rsp['storageRegion'];
        $vodSessionKey = $rsp['vodSessionKey'];
        $videoDst = $rsp['video']['storagePath'];

        if (isset($coverType)) {
            $coverDst = $rsp['cover']['storagePath'];
        }
    
        // 第二步，上传文件到COS
        Cosapi::setTimeout(180);
        Cosapi::setRegion($region);
        $rsp = Cosapi::upload($bucket, $videoPath, $videoDst);
        echo "Upload video to cos|recv:" . json_encode($rsp) . "\n";
        if (isset($coverType)) {
            $rsp = Cosapi::upload($bucket, $coverPath, $coverDst);
            echo "Upload cover to cos|recv:" . json_encode($rsp) . "\n";
        }
        
        // 第三步，调用VOD的CommitUpload确认上传
        $package = array(
                'Action' => "CommitUpload",
                'vodSessionKey' => $vodSessionKey,
        );
        $rsp = $vod->CommitUpload($package);
        echo "CommitUpload|recv:" . json_encode($rsp) . "\n";
    }
}
