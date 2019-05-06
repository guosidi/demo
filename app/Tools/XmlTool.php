<?php
/**
 * Created by PhpStorm.
 * User: luanjun
 * Date: 2017/5/15
 * Time: 14:31
 */

namespace App\Tools;

class XmlTool
{

    public function __construct()
    {
        libxml_disable_entity_loader(true);
    }

    /**
     * 读取XML 将XML变为数组
     * @param string $xmlstring
     * @return bool
     */
    public static function readXml($xmlstring = "")
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xmlstring);
        return self::xmlGetArray($dom->documentElement);
    }
    /**XML转数组
    */
    public static function xmlGetArray($node)
    {
        $array = false;

        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $array[$attr->nodeName] = $attr->nodeValue;
            }
        }

        if ($node->hasChildNodes()) {
            if ($node->childNodes->length == 1) {
                $array = self::xmlGetArray($node->firstChild);
            } else {
                foreach ($node->childNodes as $childNode) {
                    if ($childNode->nodeType != XML_TEXT_NODE) {
                        if (isset($array[$childNode->nodeName]) && !is_array($array[$childNode->nodeName])) {
                            $value = $array[$childNode->nodeName];
                            unset($array[$childNode->nodeName]);

                            $array[$childNode->nodeName][] = $value;
                            $array[$childNode->nodeName][] = self::xmlGetArray($childNode);
                        } else {
                            $array[$childNode->nodeName] = self::xmlGetArray($childNode);
                        }
                    }
                }
            }
        } else {
            return $node->nodeValue;
        }
        return $array;
    }


    //将数组转换为XML格式
    public function arrayToXml($arr,$dom=0,$item=0){
        if (!$dom){
            $dom = new \DOMDocument();
        }
        if(!$item){
            $item = $dom->createElement("xml");
            $dom->appendChild($item);
        }
        foreach ($arr as $key=>$val){
            $itemx = $dom->createElement(is_string($key)?$key:"item");
            $item->appendChild($itemx);
            if (!is_array($val)){
                $text = $dom->createTextNode($val);
                $itemx->appendChild($text);
                }else {
                $this->arrayToXml($val,$dom,$itemx);
            }
        }
        return $dom->saveXML();
    }
}