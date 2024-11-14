<?php
namespace app\api\controller;

use app\api\controller\Wxbiz;
use app\api\model\assesstoken;


class Wxlogin{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST, GET, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers:x-requested-with,Content-Type,X-CSRF-Token,token');
        header('Access-Control-Expose-Headers: *');
    }

    public function __construct()
    {
    }
    public $appid     = 'wx9619f41db13a6ecf';
    public $app_secret = 'e0ec83c8a0d7658fcccd871230b74300';

    public $secrect_key='07f9028143cc07fc327706a18ee52770';//商户支付秘钥


    /**
     * Created by PhpStorm.

     * User: lang

     * time:2020年11月11日 10:45:55

     * ps:获取公众号openid

     */
    public function getwxOpenid($js_code = ''){
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appid}&secret={$this->app_secret}&code={$js_code}&grant_type=authorization_code";
        $authorize = $this->https_request($url);
        return $authorize;
    }

    /**
     * Created by PhpStorm.

     * User: lang

     * time:2020年11月11日 10:45:55

     * ps:获取用户信息

     */
    public function getuserinfo($openid){
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$this->getAccessToken()}&openid={$openid}&lang=zh_CN";
        $authorize = $this->https_request($url);
        return $authorize;
    }
    public function getuserinfos($access_token,$openid){
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $authorize = $this->https_request($url);

        return $authorize;
    }
    /**
     * Created by PhpStorm.

     * User: lang

     * time:2020年11月11日 10:45:55

     * ps:获取用户信息

     */
    public function getuserinfosss($access_token,$openid){
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'⟨=zh_CN";
        $authorize = $this->https_request($url);

        return $authorize;
    }
    /**
     * Created by PhpStorm.

     * User: lang

     * time:2020年11月11日 10:45:55

     * ps:获取小程序授权链接

     */
    public function getOpenid($js_code = ''){
        $url = "https://api.weixin.qq.com/sns/access_token?appid={$this->appid}&secret={$this->app_secret}&js_code={$js_code}&grant_type=authorization_code";
        $authorize = $this->https_request($url);

        return $authorize;
    }
    /**
     * Created by PhpStorm.

     * User: lang

     * time:2020年11月11日 16:36:49

     * ps:获取用户unionid

     */
    public function getUnionid($encryptedData,$iv,$code,$session_key){
        header("Access-Control-Allow-Origin: ityangs.net");
        header("Access-Control-Allow-Origin: *");
        $iv =  urldecode($iv);

        $pc = new Wxbiz($this->appid,$session_key);
        $errCode = $pc->decryptData($encryptedData,$iv,$data);
        if ($errCode == 0) {
            $data_rest = json_decode($data);
            $union_id  = $data_rest->unionId;

            return  ['code'=>'success','unionid'=>$union_id];
            die;
        }else {
            switch($errCode){
                case -41001:
                    $code = -41001;
                    $msg  = "encodingAesKey 非法";
                    break;
                case -41003:
                    $code = -41003;
                    $msg  = "aes 解密失败";
                    break;
                case -41004:
                    $code = -41004;
                    $msg  = "解密后得到的buffer非法";
                    break;
                case -41005:
                    $code = -41005;
                    $msg  = "base64加密失败";
                    break;
                case -41016:
                    $code = -41016;
                    $msg  = "base64解密失败";
                    break;
                case -41002:
                    $code = -41002;
                    $msg  = "iv错误";
                    break;
            }

            return(['code'=>$code,'msg'=>$msg]);
            die;

        }
//        $url = " https://api.weixin.qq.com/wxa/getpaidunionid?access_token={$access_token}&openid={$openid}";
//        $authorize = $this->https_request($url);
//        return $authorize;
    }
    /**
     * Created by PhpStorm.

     * User: lang

     * time:2020年11月11日 14:13:26

     * ps:获取access_token

     */
    public function getAccessToken(){
        $assessToken = new assesstoken();
        $token = $assessToken::getToken();

        if (empty($token)) {
            $res =  $this->https_request("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->app_secret}");
            $res = json_decode($res, true);
            $token = $res['access_token'];
            // 注意：这里需要将获取到的token缓存起来（或写到数据库中）
            // 不能频繁的访问https://api.weixin.qq.com/cgi-bin/token，每日有次数限制
            // 通过此接口返回的token的有效期目前为2小时。令牌失效后，JS-SDK也就不能用了。
            // 因此，这里将token值缓存1小时，比2小时小。缓存失效后，再从接口获取新的token，这样
            // 就可以避免token失效。
            // S()是ThinkPhp的缓存函数，如果使用的是不ThinkPhp框架，可以使用你的缓存函数，或使用数据库来保存。
            $data['assess_token'] = $token;
            $data['type'] = 1;
            $data['createtime'] = time();
            $assessToken::addToken($data);
        }

        return $token;
    }
    /**
     * Created by PhpStorm.

     * User: lang

     * time:2021年1月14日 16:05:31

     * ps:微信小程序发送模板消息接口

     * url:{{URL}}/
     */
    public static function SendMsg($data,$access_token){
        $MsgUrl="https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$access_token; //微信官方接口，需要拼接access_token
        return json_decode(self::curl($MsgUrl,$params=json_encode($data),$ispost=1,$https=1)); //访问接口，返回参数
    }
    public static function curl($url, $params = false, $ispost = 0, $https = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'
            )
        );
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
    /**
     * Created by PhpStorm.

     * User: lang

     * time:2020年11月11日 10:59:00

     * ps:请求

     */
    function https_request($url,$data='',$type='',$times=1){

        if($type=='json'){//json $_POST=json_decode(file_get_contents('php://input'), TRUE);
            $headers = array("Content-type: application/json;charset=UTF-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
            $data=json_encode($data);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        /*if(count($output)<200&&$times<6){
            https_request($url,$data,$type,++$times);

        }*/

        return $output;
    }

    function getappid(){
        return $this->appid;
    }


    /**
     * Created by PhpStorm.

     * User: lang

     * time:2021年1月15日 8:45:55

     * ps:企业付款到零钱

     * url:{{URL}}/
     */
    /**
     * [xmltoarray xml格式转换为数组]
     * @param [type] $xml [xml]
     * @return [type]  [xml 转化为array]
     */
    function xmltoarray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }

    /**
     * [arraytoxml 将数组转换成xml格式（简单方法）:]
     * @param [type] $data [数组]
     * @return [type]  [array 转 xml]
     */
    function arraytoxml($data){
        $str='<xml>';
        foreach($data as $k=>$v) {
            $str.='<'.$k.'>'.$v.'</'.$k.'>';
        }
        $str.='</xml>';
        return $str;
    }

    /**
     * [createNoncestr 生成随机字符串]
     * @param integer $length [长度]
     * @return [type]   [字母大小写加数字]
     */
    function createNoncestr($length =32){
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";

        for($i=0;$i<$length;$i++){
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * [curl_post_ssl 发送curl_post数据]
     * @param [type] $url  [发送地址]
     * @param [type] $xmldata [发送文件格式]
     * @param [type] $second [设置执行最长秒数]
     * @param [type] $aHeader [设置头部]
     * @return [type]   [description]
     */
    function curl_post_ssl($url, $xmldata, $second = 30, $aHeader = array()){

        $isdir = $_SERVER['DOCUMENT_ROOT']."/cert/";//证书位置;绝对路径

        $ch = curl_init();//初始化curl

        curl_setopt($ch, CURLOPT_TIMEOUT, $second);//设置执行最长秒数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');//证书类型
        curl_setopt($ch, CURLOPT_SSLCERT, $isdir . 'apiclient_cert.pem');//证书位置
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型
        curl_setopt($ch, CURLOPT_SSLKEY, $isdir . 'apiclient_key.pem');//证书位置
        curl_setopt($ch, CURLOPT_CAINFO, 'PEM');
        curl_setopt($ch, CURLOPT_CAINFO, $isdir . 'rootca.pem');
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);//设置头部
        }
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);//全部数据使用HTTP协议中的"POST"操作来发送

        $data = curl_exec($ch);//执行回话
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }


    /**
     * [sendMoney 企业付款到零钱]
     * @param [type] $amount  [发送的金额（分）目前发送金额不能少于1元]
     * @param [type] $re_openid [发送人的 openid]
     * @param string $desc  [企业付款描述信息 (必填)]
     * @param string $check_name [收款用户姓名 (选填)]
     * @return [type]    [description]
     */
    function sendMoney($amount,$re_openid,$desc='测试',$check_name=''){

        $total_amount = (100) * $amount;

        $data=array(
            'mch_appid'=>'wxdedc6e9d77b2fe35',//商户账号appid
            'mchid'=> '1502078561',//商户号
            'nonce_str'=>$this->createNoncestr(),//随机字符串
            'partner_trade_no'=> date('YmdHis').rand(1000, 9999),//商户订单号
            'openid'=> $re_openid,//用户openid
            'check_name'=>'NO_CHECK',//校验用户姓名选项,
            're_user_name'=> $check_name,//收款用户姓名
            'amount'=>$total_amount,//金额
            'desc'=> $desc,//企业付款描述信息
            'spbill_create_ip'=> '117.50.119.91',//Ip地址
            //   'spbill_create_ip'=>$_SERVER['REMOTE_ADDR'],//Ip地址
        );

        //生成签名算法
        $secrect_key=$this->secrect_key;///这个就是个API密码。MD5 32位。

        $data=array_filter($data);
        ksort($data);
        $str='';
        foreach($data as $k=>$v) {
            $str.=$k.'='.$v.'&';
        }
        $str.='key='.$secrect_key;
        $data['sign']=md5($str);
        //生成签名算法


        $xml=$this->arraytoxml($data);

        $url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; //调用接口
        $res=$this->curl_post_ssl($url,$xml);
        $return=$this->xmltoarray($res);

        //返回来的结果是xml，最后转换成数组
        /*
        array(9) {
         ["return_code"]=>
         string(7) "SUCCESS"
         ["return_msg"]=>
         array(0) {
         }
         ["mch_appid"]=>
         string(18) "wx57676786465544b2a5"
         ["mchid"]=>
         string(10) "143345612"
         ["nonce_str"]=>
         string(32) "iw6TtHdOySMAfS81qcnqXojwUMn8l8mY"
         ["result_code"]=>
         string(7) "SUCCESS"
         ["partner_trade_no"]=>
         string(18) "201807011410504098"
         ["payment_no"]=>
         string(28) "1000018301201807019357038738"
         ["payment_time"]=>
         string(19) "2018-07-01 14:56:35"
        }
        */
        $responseObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        $res= $responseObj->return_code; //SUCCESS 如果返回来SUCCESS,则发生成功，处理自己的逻辑
        return $res;
    }



}