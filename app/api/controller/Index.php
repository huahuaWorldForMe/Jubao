<?php
/**
 * Created by PhpStorm.
 * User: chenchunpeng
 * Date: 2018/8/26
 * Time: 00:11
 */

namespace app\api\controller;

use app\admin\model\Jubao;
use app\api\model\WxTicket;
use app\api\model\WxToken;
use app\common\controller\Common;
use EasyWeChat\Material\Temporary;
use think\Db;
use think\Loader;


//Wechat
define('BASE_URL', 'http://jubao.muying365.net');
define('IMAGE_BASE_URL', 'http://jubao.muying365.net/data/upload/image');
define('VIDEO_BASE_URL', 'http://jubao.muying365.net/data/upload/video');
define('VOICE_BASE_URL', 'http://jubao.muying365.net/data/upload/voice');

define('APP_ID', 'wxff7a21a067ba2148');
define('APP_SECRET', '24215b206cea21d22b207f31af3fe421');
define('OAUTH_URL', 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . APP_ID . '&redirect_uri=http://jubao.muying365.net/api/Index/oauth&response_type=code&scope=snsapi_base&state=STATE&connect_redirect=1#wechat_redirect');


/**
 *
 *
 * {{first.DATA}}
 * 事件类型：{{keyword1.DATA}}
 * 处理状态：{{keyword2.DATA}}
 * 处理意见：{{keyword3.DATA}}
 * {{remark.DATA}}
 */
define('WX_TEMPLATE_PROCESS', 'rDke__MWW8rJ4_jzTFJ5gDR_-3WWHjlPV2pZTk4cGYA');

Loader::import('wechat', APP_PATH, '.class.php');

class Index extends Common
{

    /**
     * 前端首页
     */
    public function index()
    {
        $banners = Db::name('cm_banner')->where('type', 1)->order('banner_create_time asc')->select();
        $package_cat = Db::name('category')->order('catid asc')->select();

//        $package_cat = array(array('id' => 1, 'name' => 'ccp','intro'=>'is a handsome boy'), array('id' => 1, 'name' => 'ccp','intro'=>'is a interesting boy'), array('id' => 1, 'name' => 'ccp','intro'=>'is a wonderful boy'));
        $data = array('banner' => $banners, 'package_cat' => $package_cat, 'banner_first' => $banners[0], 'banner_end' => $banners[sizeof($banners) - 1], 'oauth_url' => OAUTH_URL);
        $data['noticeUrl'] = 'http://www.baidu.com';
        success('获取成功', $data);
//    var_dump($banners);
    }

    public function jubao_info()
    {
        $id = input('id');
        $jubao = Db::name('category')->where('catid', $id)->find();
        if ($jubao) {
            success('获取成功', $jubao);
        } else {
            error('信息不存在', '');
        }

    }

    public function uploadVideo()
    {
        $img = isset($_POST['video']) ? $_POST['video'] : '';
        list($type, $data) = explode(',', $img);
        $ext = '.mp4';
// 判断类型


// 生成的文件名
        $date = date("Y-m-d");
        $path = UPLOAD_PATH . "video/" . $date;
        if (!file_exists($path)) {
            @mkdir($path, 0777, true);
        }
        $file_name = time() . $ext;
        $photo = $path . '/' . $file_name;

// 生成文件
        file_put_contents($photo, base64_decode($data), true);

// 返回
        header('content-type:application/json;charset=utf-8');
        $res = array('img' => VIDEO_BASE_URL . '/' . date("Y-m-d") . '/' . $file_name);
        echo json_encode($res);
    }

    public function uploadImage()
    {
        $img = isset($_POST['img']) ? $_POST['img'] : '';

// 获取图片
        list($type, $data) = explode(',', $img);
        $ext = '.png';
// 判断类型
        if (strstr($type, 'image/jpeg') != '') {
            $ext = '.jpg';
        } elseif (strstr($type, 'image/gif') != '') {
            $ext = '.gif';
        } elseif (strstr($type, 'image/png') != '') {
            $ext = '.png';
        }

// 生成的文件名
        $date = date("Y-m-d");
        $path = UPLOAD_PATH . "image/" . $date;
        if (!file_exists($path)) {
            @mkdir($path, 0777, true);
        }
        $file_name = time() . $ext;
        $photo = $path . '/' . $file_name;

// 生成文件
        file_put_contents($photo, base64_decode($data), true);

// 返回
        header('content-type:application/json;charset=utf-8');
        $res = array('img' => IMAGE_BASE_URL . '/' . date("Y-m-d") . '/' . $file_name);
        echo json_encode($res);
    }

    public function download($mediaId)
    {
        $options = array(

            'token' => 'cpWechat', //填写你设定的key

            'encodingaeskey' => '9emg2P3wLhvIuZaJV4UeHFNps2pws9av667BCQC3Pgj', //填写加密用的EncodingAESKey

            'appid' => 'wxff7a21a067ba2148', //填写高级调用功能的app id

            'appsecret' => '24215b206cea21d22b207f31af3fe421' //填写高级调用功能的密钥

        );
        $this->weObj = new \Wechat($options);
//        'gdG2Dom_Vr4VoM1cr-3U1U96HPrYaKiHk923qEdXUT8xuU4SHAgpYbVx4Z5YToKK'
        $data = $this->weObj->getMedia($mediaId);
        $date = date("Y-m-d");
        $path = UPLOAD_PATH . "voice/" . $date;
        if (!file_exists($path)) {
            @mkdir($path, 0777, true);
        }
        $file_name = time() . '.mp3';
        $voice = $path . '/' . $file_name;

// 生成文件
        $result = file_put_contents($voice, $data, true);
        if ($result) {
            return VOICE_BASE_URL . '/' . date("Y-m-d") . '/' . $file_name;
        }

    }

    public function jubao_post()
    {
        $id = input('id');
        $images = input('images');
        $openid = input('openid');
        $wfcph = input('wfcph');
        $wzsj = input('wzsj');
        $wfdd = input('wfdd');
        $sjhm = input('sjhm');
        $video = input('video');
        $serverId = input('serverId');

        if (!$id || !$images || !$openid || !$wfcph || !$wzsj || !$wfdd) {
            error('参数错误', '');
            return;
        }
        if (substr($images, strlen($images) - 1) == ',') {
            $images = substr($images, 0, strlen($images) - 1);
        }
        $insertArr = array(
            'jubao_catid' => $id,
            'jubao_no' => $this->getJubaoNo(),
            'from_user' => $openid,

            'jubao_image' => $images,
            'jubao_wfcph' => $wfcph,
            'jubao_wzsj' => $wzsj,
            'jubao_wfdd' => $wfdd,
            'jubao_sjhm' => $sjhm,
            'jubao_video' => $video,
            'jubao_show'=>0,
            'jubao_voice' => $this->download($serverId),
            'post_time' => time());

//        $result = Db::name('jubao')->insert($insertArr);
        $jubao_model = new Jubao();
        $result = $jubao_model->insert($insertArr, false, true);
        if ($result) {
//            $this->sendTemplate($id, '您好，您的举报' . $jubao_info['jubao_no'] . '已成功处理！',true);

            jubao_log($result, ($sjhm ? $sjhm : $openid) . '提交举报信息');

            $push_result=$this->sendTemplate($result, '您的举报已成功提交！', true);
            jubao_log($id, ('系统已经推送给举报人收到举报提交：' . ($push_result ? '成功' : "失败")));
            success('举报成功', OAUTH_URL);
        } else {
            error('举报失败', '');
        }

    }

    public function getJubaoNo()
    {
        $no1 = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8).str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $no1;
    }
//    public function uploadImage(){
//        $img = isset($_POST['img'])? $_POST['img'] : '';
//
//// 获取图片
////        list($type, $data) = explode(',', $img);
//        $ext='.png';
//// 判断类型
////        if(strstr($type,'image/jpeg')!=''){
////            $ext = '.jpg';
////        }elseif(strstr($type,'image/gif')!=''){
////            $ext = '.gif';
////        }elseif(strstr($type,'image/png')!=''){
////            $ext = '.png';
////        }
//
//// 生成的文件名
//        $path=UPLOAD_PATH."image/".date("Y-m-d");
//        if (!file_exists($path)) {
//            @mkdir($path,0777,true);
//        }
//        $photo = $path.'/'.time().$ext;
//
//// 生成文件
//        file_put_contents($photo, $img, true);
//
//// 返回
//        header('content-type:application/json;charset=utf-8');
//        $res = array('img'=>$photo);
//        echo json_encode($res);
//    }
    //===========================================================Wechat Relate=========================================
    public function sendTemplate($order_id = '', $content = '', $local = false)
    {
        if (empty($order_id)) {
            $order_id = input('id');
        }
        if (empty($content)) {
            $content = input('content');
        }
        $jubao_info = Db::name('jubao')->alias('a')->join(config('database.prefix') . 'category b', 'a.jubao_catid =b.catid')->where('id', $order_id)->find();

//       var_dump($jubao_info);
        if ($jubao_info) {

            $order_status_text = '';

            $status = '已提交';
            switch (intval($jubao_info['status'])) {
                case 0:
                    $status = '已提交';
                    break;
                case 1:
                    $status = '待处理';
                    break;
                case 2:
                    $status = '已处理';

                    break;
                case 3:
                    $status = '已完成';
                    break;
            }
            $data = array(
                'first' => array('value' => '您好，' . $jubao_info['jubao_sjhm'] . ',' . $content, 'color' => '#173177'),
                'keyword1' => array('value' => $jubao_info['catname'], 'color' => '#173177'),
                'keyword2' => array('value' => $status, 'color' => '#173177'),
                'keyword3' => array('value' => $jubao_info['feedback'] ? $jubao_info['feedback'] : '暂未处理', 'color' => '#173177'),
//            'keyword4' => array('value' => $order_amount . '元', 'color' => '#173177'),
                'remark' => array('value' => '感谢您的支持', 'color' => '#173177'),
            );

            $result = $this->sendTemplateToOpenWx(WX_TEMPLATE_PROCESS, $data, $jubao_info['from_user'], OAUTH_URL, true);

            $result['post_data'] = $data;
            if ($result['status'])
                $send_success = false;
            $count = 0;
            if ($result['status'] === true) {
                $send_success = true;
                if ($local)
                    return true;
                else {
                    var_dump($result);
                }
            } else {
                if ($result['data']['errcode'] == '40001') {
                    $accessToken = $this->getAccessToken(true, true);
                    $result = $this->sendTemplateToOpenWx(WX_TEMPLATE_PROCESS, $data, $jubao_info['from_user'], 'http://jubao.muying365.net/jubao/', true);
                    if ($result['status'] === true) {
                        $send_success = true;
                        if ($local)
                            return true;
                        else {
                            var_dump($result);
                        }
                    } else {
//                        $result['accessToken']=$accessToken;
                        $send_success = false;
                        if (!$local) {
                            echo '失败：' . $count . '<br>';
                            var_dump($result);
                            echo '<br>';
                        }
                    }
                } else {
                    $send_success = false;
                    if (!$local) {
                        echo '失败：' . $count . '<br>';
                        var_dump($result);
                        echo '<br>';
                    }
                }


            }

//            while (!$send_success && $count < 3) {
//                $count++;
//
//            }
//            if ($local)
//                return $send_success;
//            else var_dump($result);
//            echo $result;
//            var_dump($result);
//            echo 'openid:' . $user['openid'];

        } else {
            if ($local)
                return false;
            else var_dump('');
        }

    }

    public function sendPayedTemplate($order_id)
    {
        $order = Db::name('cm_order_list')->where(array('order_list_id' => $order_id))->find();
        if ($order) {
            $user = Db::name('cm_user_list')->where('id', $order['uid'])->find();


            $data = array(
                'first' => array('value' => '亲爱的' . $user['user_name'] . '您已经成功支付' . $order['order_amount'] . '元', 'color' => '#173177'),
                'keyword1' => array('value' => $order['order_trade_no'], 'color' => '#173177'),
                'keyword2' => array('value' => '提成：' . $order['order_return_amount'] . '元，状态' . ':已支付待审核', 'color' => '#173177'),
//            'keyword4' => array('value' => $order_amount . '元', 'color' => '#173177'),
                'remark' => array('value' => '点击查看订单', 'color' => '#173177'),
            );
            $result = $this->sendTemplateToOpenWx(WX_TEMPLATE_ORDER_NOTIFY, $data, $user['openid'], 'http://mobile.muying365.netcm/cm/order_list.html', true);
            $this->sendEmail($order['order_trade_no']);//发送通知邮件

            var_dump($result);
            echo 'openid:' . $user['openid'];
            if ($result['status'] === true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     *      发送模板消息到公众号
     * @param $template_id
     * @param $data
     * @param string $wxOpenid
     * @param $isMerchant true表示给商家发
     * @param $pages 小程序或者网页的地址  如 'pages/index/index
     * @param bool $local
     * @return array('status'=>true, 'data'=>$returnObj) 发送成功，array('status'=>false, 'data'=>$returnObj)发送失败
     */
    private function sendTemplateToOpenWx($template_id, $data, $wxOpenid = '', $click_url, $local = false)
    {
        $openid = $local ? $wxOpenid : input('openid');
        $access_token_data = $this->getWechatAccessToken(APP_ID, APP_SECRET, true);
        $access_token = $access_token_data['access_token'];
//临时
//        $data = $this->getWechatAccessTokenNew(APP_ID, APP_SECRET);
//        $access_token = $data['access_token'];
//        $templateid =TEMPLATE_OPEN_WX_NEW_ORDER;
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token;
        $requestParams = array(

            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $click_url,
            'data' => $data

        );
        $callreturn = post($url, json_encode($requestParams));
        $returnObj = json_decode($callreturn, true);
        $returnObj['openid'] = $openid;

        if ($returnObj['errcode'] === 0) {
            if ($local)
                return array('status' => true, 'data' => $returnObj, 'access_token' => $access_token);
            else
                success('发送成功' . $access_token, $returnObj, 'ajax');
        } else {
            $returnObj['access_token'] = $access_token;
            if ($local)
                return array('status' => false, 'data' => $returnObj);
            else
                error('发送失败' . $access_token, $returnObj, 'ajax');
        }
    }

    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = input('request_url');//"$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId" => APP_ID,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
//        return $signPackage;
        success('', $signPackage);
    }

    public function test()
    {
        echo 'host:' . $_SERVER[HTTP_HOST];
        echo 'url:' . $_SERVER[REQUEST_URI];

    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function getJsApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = $this->getWechatTicket(APP_ID, APP_SECRET, true);


        return $data['ticket'];
    }


    public function getWechatTicket($appid, $secret, $local = false)
    {
        $wx_token_model = new WxTicket;


        $wher_id_a = array('appid' => $appid);//根据user_id来判断是否有该用户
        $extData = $wx_token_model->where($wher_id_a)->find();
        if ($extData) {//已有对应token
            $timeSpended = time() - $extData['update_time'];//已发费多少时间
            $dtime = $timeSpended - $extData['expires_in'];

            if ($dtime > -60) {//即将过期，重新获取，预留一分钟
                $data = $this->getWechatTicketNew($appid, $secret);
                $access_token = $data['ticket'];
                $expired_in = $data['expires_in'];
                $sl_token_data = array(
                    'appid' => $appid,
                    'ticket' => $access_token,
                    'expires_in' => $expired_in,
                    'update_time' => time()
                );
                $rst_ext = $wx_token_model->update($sl_token_data, $wher_id_a);
                if ($rst_ext) {
                    if ($local) {
                        return $rst_ext;
                    } else
                        success('获取成功-update' . $dtime, $rst_ext, 'ajax');
                } else {
                    if ($local) {
                        return '';
                    } else
                        error('保存失败', '', 'ajax');
                }
            } else {
                if ($local) {
                    return $extData;
                } else
                    success('获取成功' . $timeSpended . '||dtime:' . $dtime, $extData, 'ajax');
            }
        } else {
            $data = $this->getWechatTicketNew($appid, $secret);
            $access_token = $data['ticket'];
            $expired_in = $data['expires_in'];
            $sl_token_data = array(
                'appid' => $appid,
                'ticket' => $access_token,
                'expires_in' => $expired_in,
                'update_time' => time()
            );
            $rst_ext = $wx_token_model->create($sl_token_data);
            if ($rst_ext) {
                if ($local) {
                    return $rst_ext;
                } else
                    success('获取成功-insert', $rst_ext, 'ajax');
            } else {
                if ($local) {
                    return '';
                } else
                    error('保存失败', '', 'ajax');
            }
        }
    }


    private function getAccessToken($local = false, $needGetNew = false)
    {
        $data = $this->getWechatAccessToken(APP_ID, APP_SECRET, $local, $needGetNew);
        return $data;
    }

    /**
     * @param $appid
     * @param $secrethttps ://www.aliyun.com/
     * @return mixed
     * 在线获取最新token
     */
    private function getWechatAccessTokenNew($appid, $secret)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
//        $openid = $this->getWechatOpenidInner(1, '', true);
        $return_data = get($url);
        $data = json_decode($return_data, true);
        return $data;
    }

    private function getWechatTicketNew($appid = '', $secret = '')
    {
        $accessTokenArr = $this->getAccessToken(true);
        $accessToken = $accessTokenArr['access_token'];
        // 如果是企业号用以下 URL 获取 ticket
        // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
        $res = json_decode(get($url), true);
//        $ticket = $res->ticket;
        return $res;
//        var_dump($res);
    }

    public function getNewToken()
    {
        $this->getWechatAccessToken(APP_ID, APP_SECRET, false, true);
    }

    /**
     * @param $appid
     * @param $secret
     * @param $code
     * @return mixed|string
     * 获取微信小程序模板消息access_token
     */
    public function getWechatAccessToken($appid, $secret, $local = false, $needGetNew = false)
    {
//        $appid = WX_APPID;
//        $secret = WX_SECRET;
        $wx_token_model = new WxToken;


        if (!$local) {
            $appid = input('appid');
            $secret = input('secret');
        }
        $wher_id_a = array('appid' => APP_ID);//根据user_id来判断是否有该用户
        $extData = $wx_token_model->where($wher_id_a)->find();
        if ($extData && !$needGetNew) {//已有对应token
            $timeSpended = time() - $extData['update_time'];//已发费多少时间
            $dtime = $timeSpended - $extData['expires_in'];

            if ($dtime > -60) {//即将过期，重新获取，预留一分钟
                $data = $this->getWechatAccessTokenNew($appid, $secret);

                $access_token = $data['access_token'];
                $expired_in = $data['expires_in'];
                $sl_token_data = array(
                    'appid' => $appid,
                    'access_token' => $access_token,
                    'expires_in' => $expired_in,
                    'update_time' => time()
                );
                $rst_ext = $wx_token_model->update($sl_token_data, $wher_id_a);
                if ($rst_ext) {
                    if ($local) {
                        return $rst_ext;
                    } else
                        success('获取成功-update' . $dtime, $rst_ext, 'ajax');
                } else {
                    if ($local) {
                        return '';
                    } else
                        error('保存失败', '', 'ajax');
                }
            } else {
                if ($local) {
                    return $extData;
                } else
                    success($timeSpended . ',' . date('Y-m-d H:i:s') . '获取成功' . $timeSpended . '||dtime:' . $dtime, $extData, 'ajax');
            }
        } else {
            $data = $this->getWechatAccessTokenNew($appid, $secret);
            $access_token = $data['access_token'];
            $expired_in = $data['expires_in'];
            $sl_token_data = array(
                'appid' => $appid,
                'access_token' => $access_token,
                'expires_in' => $expired_in,
                'update_time' => time()
            );
            if ($extData) {
                $rst_ext = $wx_token_model->update($sl_token_data, $wher_id_a);

            } else {
                $rst_ext = $wx_token_model->create($sl_token_data);

            }
            if ($rst_ext) {
                if ($local) {
                    return $rst_ext;
                } else
                    success($extData ? '更新成功' : '获取成功-insert:' . $access_token, $rst_ext, 'ajax');
            } else {
                if ($local) {
                    return '';
                } else
                    error('保存失败', '', 'ajax');
            }
        }
    }

    public function oauth()
    {


        $appid = APP_ID;
        $appsecret = APP_SECRET;
        $code = $_GET['code'];//获取code
        $weixin = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=" . $code . "&grant_type=authorization_code");//通过code换取网页授权access_token
        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        $openid = $array['openid'];//输出openid
        $redirect_uri = BASE_URL.'?openid=' . $openid;
        header('Location:' . $redirect_uri);


    }

    public function clearDb()
    {
//        Db::name('jubao')->where('')->delete();
        $sql='TRUNCATE TABLE jb_jubao ';
        $result =Db::name('jubao')->execute($sql);
        var_dump($result);
        $sql='TRUNCATE TABLE jb_jubao_log ';
        $result =Db::name('jubao_log')->execute($sql);
        var_dump($result);
    }

    public function auth_group_access()
    {
        $auth_group = Db::name('auth_group_access')->select();
        var_dump($auth_group);
    }

    public function admin_group()
    {
        $auth_group = Db::name('auth_group')->where('id', '>', '2')->select();
        var_dump($auth_group);
    }

    public function admins()
    {
        $admin_list = Db::name('admin')->where('admin_id', '>', 1)->order('admin_id')->select();
        var_dump($admin_list);
    }
    public function addFeedback(){
        Db::name('feedback_option')->insert(array('feedback_content'=>'斑马线不礼让行人:罚款200元记3分'));
        Db::name('feedback_option')->insert(array('feedback_content'=>'不系安全带：对乘坐人罚款20元'));
        Db::name('feedback_option')->insert(array('feedback_content'=>'不系安全带：对驾驶人罚款50元不记分'));
        Db::name('feedback_option')->insert(array('feedback_content'=>'开车打电话：罚款50元，记2分'));
        Db::name('feedback_option')->insert(array('feedback_content'=>'违法变道：罚款100元不记分'));
        Db::name('feedback_option')->insert(array('feedback_content'=>'压线掉头：罚款100元'));
        Db::name('feedback_option')->insert(array('feedback_content'=>'电动车违法：罚款50元'));
        Db::name('feedback_option')->insert(array('feedback_content'=>'学习'));
        Db::name('feedback_option')->insert(array('feedback_content'=>'批评教育'));
        Db::name('feedback_option')->insert(array('feedback_content'=>'无法联系到车主'));


    }
    public function datas(){
        $member_model=new \app\admin\model\Jubao();
        $query = $member_model
            ->alias('a')
            ->join(config('database.prefix') . 'category b', 'a.jubao_catid =b.catid')
            ->field(array('a.id','a.from_user',
                'a.post_time',
                'a.jubao_wfcph',
                'a.jubao_wzsj',
                'a.jubao_wfdd',
                'a.jubao_sjhm',
                'a.jubao_no',
                'a.status',
                'a.finish_time',
                'a.handle_time',
                'a.feedback',
                'b.catname'));

        $jubao_list = $query->order('post_time desc')->paginate(config('paginate.list_rows'), false, ['query' => get_query()]);

        echo json_encode($jubao_list);
    }
}