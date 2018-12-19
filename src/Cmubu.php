<?php
/**
 * Date: 2018/10/24
 * Time: 22:17
 */

namespace Boxiaozhi\Cmubu;

class Cmubu
{
    private $cmubu;

    public function __construct()
    {
        $this->cmubu = new CmubuQueryService();
    }

    /**
     * 根据 folder ID 获取文档列表
     * @param string $folderId
     * @param string $sort
     * @param string $keywords
     * @param string $source
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
     * 根据 doc ID 获取文档内容
     * @param $docId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function docContent($docId)
    {
        if(!$docId){
            throw new \Exception('请传递 docId 参数');
        }
        $data = $this->cmubu->docContent($docId);
        if($data['code'] != 0){
            throw new \Exception($data['msg']);
        }
        $data = json_decode($data['data']['definition'], true);
        return isset($data['nodes']) ? $data['nodes'] : [];
    }

    /**
     * 根据名字获取文档信息
     * @param $name
     * @return array
     */
    public function docInfoByPath($name, $type='documents')
    {
        if(!in_array($type, ['folders', 'documents'])){
            throw new \Exception('type 参数值为[folders]或[documents]');
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
     * 根据名称获取文档信息
     * @param $name
     * @return array
     * @throws \Exception
     */
    public function docInfoByName($folderId, $name, $type='floders')
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