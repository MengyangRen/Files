<?php
namespace Base;

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 *  ICP检查脚本
 *
 * @author   m.y
 * @example
 *
 *
 * (new AutoCdnCache())->run($map);
 * exit(0);
 */

class Check
{
    /**
     * 地址前缀
     * @var string
     * 列：http://cdn.wayada.com/video_user/rg/gq
     *
     */
    public $icpApi = 'http://wx.rrbay.com/pro/icpCheck2.ashx?key=a73199c373451e1127241ba8a0ba152e&url=';
    public $wxApi  = 'http://wx.rrbay.com/pro/wxUrlCheck2.ashx?key=a73199c373451e1127241ba8a0ba152e&url=';

    /**
     *
     * 检查微信域名是否被封
     *
     * @param  string  $url
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     */
    public function wx(string $url = null)
    {
        
        $status = Http::Get($this->wxApi.$url);
        $status = json_decode($status);
        $this->code($status->Code, $url, "微信域名检测");
    }

    /**
     *
     * 检查域名ipc是否备案
     *
     * @param  array  $map
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     */
    public function ipc(string $url = null)
    {
        $status = Http::Get($this->icpApi.$url);
        $status = json_decode($status);
        $this->code($status->Code, $url, "ICP备案检测");
    }


    /**
     *
     * 开始检查
     *
     * @param  array  $map
     * @return mixed $data default array,
     *  else Exception
     *
     * @example
     *
     */
    public function run(array $map)
    {
        $len = count($map);
        while ($len--) {
            $this->wx($map[$len]);
            $this->ipc($map[$len]);
            usleep(500000);
        }
    }


    public function code(int $code, string $url, $type = "ICP备案检测")
    {
        switch ($code) {
            //域名别墙
            case 101:
                Notce::telegeram($type, "域名被屏蔽", $url);
                break;

            default:
                // code...
                break;
        }
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

    public static function Post(
        $url,
        $header,
        $data
    ) {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}


class Notce
{

    public static $api = "http://logs.srv.kz2.site/api/v1/telegram/push/message/text";
    public static $token = "bd114a2319fc3df87dd74b4545e02339";

    //通知tg
    public static function telegeram($type, $msg, $url)
    {
        $body  = [
            'monitor'=>'chengdu.prod',
            'from'   =>$type,
            'subject'=>"域名检测",
            'date'   => date('Y-m-d h:i:s', time()),
            'content'=> '域名:'.$url.",".$msg,
        ];

        $ret = Http::Post(
            Notce::$api,
            [
                "Content-Type:application/json",
                "Accept:application/json",
                "X-TOKEN:".Notce::$token,
            ],
            json_encode($body)
        );
        
        var_dump(json_decode($ret));
    }
}

$map = [
    'sharedy.me',
    'app.doyin.me',
    'app.5dy.me',
    'app.doyin.me',
    'erokun.me',
    'channel.gkoj.me',
    'share.xiudadademeigui.me',
    'share.gongkoujun.me',
    'vm.cateslor.me',
    'vmg.cateslor.me',
    'gkoss.banzhengkuai.com',
    'share.xiaomeimeizaixian.me',
    'share.ziweipian.me',
    'admin.porndy.net',
    'admin.dy-porn.net',
    'mg.admincloud.me',
    'share.ziweipian.me',
    'share01.ziweipian.me',
    'share02.ziweipian.me',
    'share03.ziweipian.me',
    'vm.lustai.me',
    'share.lustai.me',
    'vmg.lustai.me',
    'channel.lustai.me',
    'share.lustai.me',
];

(new Check())->run($map);
exit(0);
