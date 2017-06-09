<?php
require_once './vod_api.php';

// 设置secret id 和 secret key
// Vodapi::setSecretIdAndKey('AKIDmW5UQRaAzmRvJZsrno14BRpAQVe1Io9V', 'Ur69B4mKi3ED2snfl9PetdDevCIEzcHl');
// 上传视频
Vodapi::upload('./Wildlife.wmv');
// 上传视频和封面
//Vodapi::upload('./Wildlife.wmv', './Wildlife-cover.png');
