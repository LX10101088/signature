<?php

namespace app\common\controller;


use Endroid\QrCode\QrCode;
use think\Controller;
use think\Db;


/**
 * 公共接口
 */
class Common extends Controller
{


    public function _initialize()
    {


        parent::_initialize();
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月02月 17:38:26
     * ps:生成用户编号（1：服务商；2：个人用户；3：企业用户）
     */
    public function userNo($area,$type){
        $area = Db::name('area')->where('name','=',$area)->find();
        $no = '000000';
        if($area){
            if($area['areanumber']){
                $no = $area['areanumber'];
            }
        }
        if($type == 1){
            $kt = 'S';
        }else if($type == 2){
            $kt = 'C';
        }else if($type == 3){
            $kt = 'E';
        }
        $No = $kt.date('Ymd',time()).rand(0,1000).$no;
        return $No;
    }



    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月03月 9:47:59
     * ps:生成平台用户账户信息（1:服务商；2:个人用户；3：企业用户）
     */
    public function adduseraccount($typeId,$type){
        $data = array();
        $data['identifier'] = '';
        if($type=='custom'){
            $data['identifier'] = 'C';
        }else if($type == 'enterprise'){
            $data['identifier'] = 'E';
        }else if($type == 'service'){
            $data['identifier'] = 'S';
        }

        $data['identifier'] .= $this->accountNo();
        $data['type'] = $type;
        $data['type_id'] = $typeId;
        $data['createtime'] = time();
        $accountId = Db::name('account')->insertGetId($data);
        return $accountId;
    }

    public function accountNo(){
        $no = "QB".date('Ymd').rand('00001','99999');  //生成编号
        $accountNo = Db::name('account')->where('identifier','=',$no)->find();
        if($accountNo){   //如果有重复编号号    重新生成
            $no =$this->accountNo();
            return $no;
        }
        return $no;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月04月 16:08:07
     * ps:获取签章认证类型
     */
    public function getattestationType($type){
        switch ($type){
            case 101:
                $name = '个人运营商三要素认证';
                break;
            case 102:
                $name = '个人银行卡四要素认证';
                break;
            case 103:
                $name = '个人活体人脸认证';
                break;
            case 104:
                $name = '个人意愿核身认证';
            break;
            case 150:
                $name = '个人认证网页版';
                break;
            case 151:
                $name = '个人运营商三要素认证网页版';
                break;
            case 152:
                $name = '个人银行卡四要素认证网页版';
                break;
            case 153:
                $name = '个人人脸活体认证网页版';
                break;
            case 202:
                $name = '个人运营商三要素比对';
                break;
            case 203:
                $name = '个人银行卡三要素比对';
                break;
            case 204:
                $name = '个人银行卡四要素比对';
                break;
            case 205:
                $name = '个人活体人脸比对';
                break;
            case 301:
                $name = '企业工商数据+法人运营商三要素认证';
                break;
            case 302:
                $name = '企业工商数据+法人银联卡四要素认证';
                break;
            case 303:
                $name = '企业工商数据+法人活体人脸认证';
                break;
            case 304:
                $name = '企业打款认证';
                break;
            case 305:
                $name = '企业反向打款认证';
                break;
            case 306:
                $name = '法人授权书认证';
                break;
            case 350:
                $name = '企业认证网页版';
                break;
            case 351:
                $name = '法定代表人认证';
                break;
            case 352:
                $name = '企业法人四要素认证';
                break;
            case 354:
                $name = '对公打款认证网页版';
                break;
            case 355:
                $name = '反向对公打款认证网页版';
                break;
            case 357:
                $name = '法人授权书认证网页版';
                break;
            case 401:
                $name = '企业工商数据比对';
                break;
            case 402:
                $name = '企业法人四要素比对';
                break;
            case 403:
                $name = '企业三要素比对';
                break;
            case 404:
                $name = '企业四要素比对';
                break;
            case 451:
                $name = '企业三要素比对网页版';
                break;
            case 901:
                $name = '小程序人脸认证权限';
                break;
        }
        return $name;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月19月 18:00:22
     * ps:获取企业信息
     */
    public function getenter($ids,$field='*'){
        $enter = Db::name('enterprise')->where('id','=',$ids)->field($field)->find();
        return $enter;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月19月 18:00:22
     * ps:获取个人信息
     */
    public function getcustom($ids,$field='*'){
        $custom = Db::name('custom')->where('id','=',$ids)->field($field)->find();
        return $custom;
    }
    public function addqrcode($url){

        $res = $this->qrcode($url);
        $name = date('YmdHis',time()).$this->generateRandomString().'.png';

        // 将二维码字符串保存为图片
        $image = imagecreatefromstring($res);
        $filename = 'qrcode/'.$name; // 保存的文件名
        imagepng($image, $filename);
        // 释放图像资源
        imagedestroy($image);
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $http = 'https://';
        } else {
            $http = 'http://';
        }
        return $http.$_SERVER['HTTP_HOST'].'/'.$filename;
    }
    function generateRandomString($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function qrcode($url){
        $qrCode = new QrCode($url);
        // 设置二维码参数
        $qrCode->setSize(300); // 设置二维码的大小（像素）
        $qrCode->setMargin(10); // 设置二维码的边距（像素）

        // 获取二维码图片二进制数据
        $qrCodeData = $qrCode->writeString();
        // 将二进制数据输出到浏览器或保存到文件
        //header('Content-Type: '.$qrCode->getContentType());
        return $qrCodeData;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月25月 13:59:32
     * ps:获取归档文档信息
     */
    public function getarchive($ids,$field='*'){
        $archive = Db::name('archive')->where('id','=',$ids)->field($field)->find();
        return $archive;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月25月 13:59:32
     * ps:获取合同信息
     */
    public function getcontract($ids,$field='*'){
        $contract = Db::name('contract')->where('id','=',$ids)->field($field)->find();
        return $contract;
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月26月 11:59:38
     * ps:获取模版信息
     */
    public function gettemplate($ids,$field='*'){
        $template = Db::name('template')->where('id','=',$ids)->field($field)->find();
        return $template;
    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 14:48:19
     * ps:获取商品信息
     */
    public function getgoods($ids,$field='*'){
        $goods = Db::name('goods')->where('id','=',$ids)->field($field)->find();
        return $goods;
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月27月 15:28:56
     * ps:增加账户权益数量
     */
    public function addaccountequity($type,$typeId,$contract,$template){
        $account = Db::name('account')->where('type','=',$type)->where('type_id','=',$typeId)->find();
        if($account){
            $data['template'] = $template;
            $data['contract'] = $contract;
            $data['updatetime'] = time();
            Db::name('account')->where('type','=',$type)->where('type_id','=',$typeId)->update($data);
        }
        return true;
    }
}
