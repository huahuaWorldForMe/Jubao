<?php
/**
 * Created by PhpStorm.
 * User: chenchunpeng
 * Date: 2018/8/26
 * Time: 14:13
 */

namespace app\api\controller;


use app\common\controller\Common;

class Upload extends Common
{

    public function uploadImage(){
        $img = isset($_POST['img'])? $_POST['img'] : '';

// 获取图片
        list($type, $data) = explode(',', $img);
        $ext='.png';
// 判断类型
        if(strstr($type,'image/jpeg')!=''){
            $ext = '.jpg';
        }elseif(strstr($type,'image/gif')!=''){
            $ext = '.gif';
        }elseif(strstr($type,'image/png')!=''){
            $ext = '.png';
        }

// 生成的文件名
        $photo = UPLOAD_PATH."image/".date("Y-m-d").'/'.time().$ext;

// 生成文件
        file_put_contents($photo, base64_decode($data), true);

// 返回
        header('content-type:application/json;charset=utf-8');
        $res = array('img'=>$photo);
        echo json_encode($res);
    }
}