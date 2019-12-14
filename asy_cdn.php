<?php
namespace Cdn;

use Cdn\AutoCdnCache;
use Cdn\Util;
use Cdn\Http;

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 *  CDN回源脚本
 *  
 * @author   m.y
 * @example
 *
 *
 * 使用说明：
 *
 * $map = [
 *    "http://cdn.wayada.com/video_user/z5/bd/12z5bd0f17e6e00738e6c476c3be7e9fee98548532.m3u8",
 *    "http://cdn.wayada.com/video_user/w9/8y/12w98yac03c81ae3063d4078ab4a8533aa86856783.m3u8",
 *    "http://cdn.wayada.com/video_user/mg/yk/12mgykcc5171b415047b0e2545b3a1121168fd1979.m3u8",
 *    "http://cdn.wayada.com/video_user/e5/k6/12e5k61ef418559abc60f9b1bffd89f8cbb1272241.m3u8",
 *    "http://cdn.wayada.com/video_user/hk/wd/12hkwd12eaa587da3a01d4502e9ed154bee1373137.m3u8",
 *    "http://cdn.wayada.com/video_user/7m/jq/127mjqe5e6403ad918639583f2d8eeacaa70b59072.m3u8",
 *    "http://cdn.wayada.com/video_user/rj/sq/12rjsq73d71a9f15533d46a9a1ef4ac26439d14123.m3u8",
 *    "http://cdn.wayada.com/video_user/zf/0v/12zf0va623965b29b02b77c53047e78ada85609621.m3u8",
 * ];
 * 
 * (new AutoCdnCache())->run($map);
 * exit(0);
 */
class AutoCdnCache
{
    /**
     * 地址前缀
     * @var string
     * 列：http://cdn.wayada.com/video_user/rg/gq
     * 
     */
    public $domain = '';

    /**
     * $tss 
     * @var array
     */
    public static $tss = [];

    /**
     *
     * 允许
     *
     * @param  array  $map
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     * 说明：
     *
     *  尝试3次
     *
     */
    public function run(array $map = null)
    {
        $len = count($map);
        while ($len--) {
            $indexM3u8Url = $map[$len];
            $this->domain = Util::getCdnDomain($indexM3u8Url);
            $this->__checkIndex($indexM3u8Url);
            $this->__checkTs();
        }
    }

    /**
     *
     * 检查索引页
     *
     * @param  int    $cC
     * @param  string $url
     *
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     * 说明：
     *
     *  尝试3次
     *
     */
    public function __checkIndex(string $indexM3u8Url = null)
    {
        $this->retry(3, $indexM3u8Url);
    }

     /**
     *
     * 检查Ts文件
     *
     * @param  int    $cC
     * @param  string $url
     *
     * @return mixed $data default array,夹
     *  else Exception
     *
     * @example
     *
     * 说明：
     *
     *  尝试3次
     *
     */
    public function __checkTs()
    {
        $len = count(AutoCdnCache::$tss);
        while ($len--) {
            $this->retry(3, $this->domain.'/'.AutoCdnCache::$tss[$len]);
        }
    }

    /**
     *
     * 重试
     *
     * @param  int    $cC
     * @param  string $url
     *
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     * 说明：
     *
     *  尝试3次
     *
     */
    public function retry(int $cC = 3, string $url = null)
    {
        while ($cC--) {
            // 如果命中退出循环
            if (Util::isCache(Util::curl($url))) {
                break;
            }
        }
    }
}




class Util
{
    /**
     * curl -i
     * @var string
     */
    public static $baseCurl  = "curl -i";
    
    /**
     * 检查缓存是否命中规则
     * @var array
     */
    public static $cacheRule = [
        '/X-Qnm-Cache: Hit/',
        '/X-Cache: HIT TCP_MEM_HIT/',
    ];

    /**
     *  抽取ts地址规则
     * @var array
     */
    public static $drawTsRule =  [
        '/(.+?.ts)/'
    ];

    /**
     *
     *  curl
     *
     * @param  string  $url
     *
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     * 说明：
     *
     *  1.基于linux -curl 组件
     *  2.基于Php shell_exec执行系统命令，注php.ini配置请开启
     *
     *
     */
    public static function curl(string $url = null)
    {
        $cmd = self::$baseCurl." ".$url;
        return shell_exec($cmd);
    }
    
    /**
     *
     *  是否已缓存
     *
     * @param  string  $url
     *
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     * 说明：
     *
     *  1.基于linux -curl 组件
     *  2.基于Php shell_exec执行系统命令，注php.ini配置请开启
     */
    public static function isCache(string $content)
    {
        //检查ts-map是否为空
        if (empty(AutoCdnCache::$tss)) {
            Util::JoinTs($content);
        }
    
        //默认未命中
        $flag = false;
        $len  = count(self::$cacheRule);
        for ($i = 0; $i < $len; $i++) {
            preg_match_all(
                self::$cacheRule[$i],
                $content,
                $matches
            );
            if (!empty($matches[0])) {
                $flag = true;
                break;
            }
        }
        return $flag;
    }

    /**
     *
     *  是否已缓存
     *
     * @param  string  $url
     *
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     * 说明：
     *
     *  1.基于linux -curl 组件
     *  2.基于Php shell_exec执行系统命令，注php.ini配置请开启
     */
    public static function JoinTs(string $content)
    {
        preg_match_all(
            self::$drawTsRule[0],
            $content,
            $matches
        );

        if (empty($matches[0])) {
            AutoCdnCache::$tss = $matches[0];
        }
    }

    /**
     *
     *  获取cdn域名
     *
     * @param  string  $url
     *
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     * 说明：
     */
    public static function getCdnDomain(string $indexM3u8Url = null)
    {
        $ll = pathinfo($indexM3u8Url);
        return $ll['dirname'];
    }

    /**
     *
     *  释放数组
     *
     * @param  string  $url
     *
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     * 说明：
     */
    public static function Idestruct(array &$arr)
    {
        while (!empty($arr)) {
            $sub = array_splice($arr, 0, 1000);
            unset($sub);
        }
        unset($arr);
    }
}



class Http
{
    public static function Get($url, $timeout = 40, $header = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
       // curl_setopt($ch, CURLOPT_CAINFO, APPROOT . '/etc/cacert.pem');
        /**************測試環境先不驗證ssl準確性**************/
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        /**************測試環境先不驗證ssl準確性**************/
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}

$map = json_decode(Http::Get('http://154.206.46.180:8093/api/getDownUrl?secret=asy'), true);
(new AutoCdnCache())->run($map);
exit(0);
