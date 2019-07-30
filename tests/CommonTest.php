<?php

require '../vendor/autoload.php';

use Cmubu\Cmubu;

$config = [
    'username' => $argv[1],
    'password' => $argv[2],
    'cookies' => '',
];
$folderName = $argv[3];
$docName = $argv[4];

try{
    $cmubu = new Cmubu($config);
    $folderInfo = $cmubu->docInfoByPath($folderName, 'folders');
    $docInfo = $cmubu->docInfoByPath($docName, 'documents');
    $cookies = $cmubu->cookies();
    $docContent = $cmubu->docContent($docInfo['id']);
    $res = [
        'cookies' => $cookies,
        'folderInfo' => $folderInfo,
        'docInfo' => $docInfo,
        'docContent' => $docContent,
    ];
    exit(json_encode($res));
} catch(\Exception $e){
    exit($e->getMessage());
}
