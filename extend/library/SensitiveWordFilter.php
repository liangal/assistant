<?php
namespace library;

use app\repository\SensitiveWordRepository;
use Cache;

class SensitiveWordFilter {
    protected $sensitiveWordRepository;
    protected $dict = [];

    public function __construct(SensitiveWordRepository $sensitiveWordRepository)
    {
        $this->sensitiveWordRepository = $sensitiveWordRepository;
    }

    /**
     * 构建敏感词数据树
     * @param  boolean $cache [description]
     * @return [type]         [description]
     */
    protected function loadData($cache = true)
    {
        $cache_key = 'sensitive:words:trie';
        
        if ($cache) {
            $words = Cache::get($cache_key);
            if (!empty($words)) {
                $this->dict = $words;
                return;
            }
        }
        
        $words = $this->sensitiveWordRepository->all();

        foreach ($words as $key => $item) {
            $this->addWords(trim($item->words));
        }

        if ($cache) {
            Cache::set($cache_key, $this->dict);
        }
    }

    /**
     * 分割文本(注意ascii占1个字节, unicode...)
     *
     * @param string $str
     *
     * @return string[]
     */
    protected function splitStr($str)
    {
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 往dict树中添加语句
     *
     * @param $wordArr
     */
    protected function addWords($words)
    {
        $wordArr = $this->splitStr($words);
        $curNode = &$this->dict;
        foreach ($wordArr as $char) {
            if (!isset($curNode)) {
                $curNode[$char] = [];
            }

            $curNode = &$curNode[$char];
        }
        // 标记到达当前节点完整路径为"敏感词"
        $curNode['end'] = true;
    }

    /**
     * 过滤文本
     * 
     * @param string $str 原始文本
     * @param string $replace 敏感字替换字符
     * @param int    $skipDistance 严格程度: 检测时允许跳过的间隔
     *
     * @return string 返回过滤后的文本
     */
    public function filter($str, $replace = '*', $skipDistance = 0)
    {
        $this->loadData();

        $maxDistance = max($skipDistance, 0) + 1;
        $strArr = $this->splitStr($str);
        $length = count($strArr);
        for ($i = 0; $i < $length; $i++) {
            $char = $strArr[$i];

            if (!isset($this->dict[$char])) {
                continue;
            }

            $curNode = &$this->dict[$char];
            $dist = 0;
            $matchIndex = [$i];
            for ($j = $i + 1; $j < $length && $dist < $maxDistance; $j++) {
                if (!isset($curNode[$strArr[$j]])) {
                    $dist ++;
                    continue;
                }

                $matchIndex[] = $j;
                $curNode = &$curNode[$strArr[$j]];
            }

            // 匹配
            if (isset($curNode['end'])) {
                foreach ($matchIndex as $index) {
                    $strArr[$index] = $replace;
                }

                $i = max($matchIndex);
            }
        }

        return implode('', $strArr);
    }

    /**
     * 确认所给语句是否为敏感词
     *
     * @param $strArr
     *
     * @return bool|mixed
     */
    public function isMatch($strArr)
    {
        $strArr = is_array($strArr) ? $strArr : $this->splitStr($strArr);
        $curNode = &$this->dict;
        foreach ($strArr as $char) {
            if (!isset($curNode[$char])) {
                return false;
            }
        }
        return $curNode['end'] ?? false;
    }
}