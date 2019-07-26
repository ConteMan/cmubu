<?php

namespace Cmubu;

class Cmubu
{
    private $cmubu;

    public function __construct($config)
    {
        $this->cmubu = new CmubuQuery($config);
    }

    /**
     * get cookies
     *
     * @return array
     */
    public function cookies()
    {
        return $this->cmubu->cookies();
    }

    /**
     * get doc list by folder ID 
     *
     * @param string $folderId
     * @param string $sort
     * @param string $keywords
     * @param string $source
     *
     * @return array
     * @throws \Exception
     */
    public function docList($folderId='', $sort='time', $keywords='', $source='')
    {
        $data = $this->cmubu->docList($folderId, $sort, $keywords, $source);
        if($data['code'] != 0){
            throw new \Exception($data['msg']);
        }
        return isset($data['data']) ? $data['data'] : [];
    }

    /**
     * get doc content by doc ID
     *
     * @param $docId
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function docContent($docId)
    {
        if(!$docId){
            throw new \Exception('need docId');
        }
        $data = $this->cmubu->docContent($docId);
        if($data['code'] != 0){
            throw new \Exception($data['msg']);
        }
        $data = json_decode($data['data']['definition'], true);
        return isset($data['nodes']) ? $data['nodes'] : [];
    }

    /**
     * get doc info by path
     *
     * @param $name 
     * @param $type folders,documents
     *
     * @return array
     */
    public function docInfoByPath($name, $type='documents')
    {
        if(!in_array($type, ['folders', 'documents'])){
            throw new \Exception('type error');
        }
        $pathArr = explode('/', $name);
        $res = [];
        $folderId = 0;
        $deepLength = count($pathArr);
        foreach($pathArr as $index => $pathname){
            if($index == $deepLength-1){
                $currentType = $type;
            } else {
                $currentType = 'folders';
            }
            $res = $this->docInfoByFolderName($folderId, $pathname, $currentType);
            if(!$res){
                return [];
            }
            $folderId = $res['id'];
        }
        return $res;
    }

    /**
     * get doc info by folerId and folderName
     *
     * @param $name
     *
     * @return array
     * @throws \Exception
     */
    public function docInfoByFolderName($folderId, $name, $type='floders')
    {
        $listData = $this->docList($folderId);
        if(!$listData) return [];
        if(!isset($listData[$type]) || !$listData[$type]) return [];
        foreach($listData[$type] as $item){
            if($item['name'] == $name){
                return $item;
            }
        }
        return [];
    }
}
