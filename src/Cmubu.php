<?php
/**
 * Date: 2018/10/24
 * Time: 22:17
 */

namespace Boxiaozhi\Cmubu;

use TheSeer\Tokenizer\Exception;

class Cmubu
{
    private $cmubu;

    public function __construct()
    {
        $this->cmubu = new CmubuQueryService();
    }

    /**
     * 文档列表
     * @return mixed
     */
    public function docList($folderId='', $sort='time', $keywords='', $source='')
    {
        return $this->cmubu->docList($folderId, $sort, $keywords, $source);
    }

    /**
     * 文档内容
     * @param $docId
     * @return mixed
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function docContent($docId)
    {
        if(!$docId){
            throw new Exception('请传递 docId', 404);
        }
        return $this->cmubu->docContent($docId);
    }
}