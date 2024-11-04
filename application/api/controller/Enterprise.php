<?php

namespace app\api\controller;

use app\common\controller\Commonattestation;
use app\common\controller\Commonenter;
use think\Controller;
use think\Db;

class Enterprise extends Controller
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:POST, GET, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers:*');
        header('Access-Control-Expose-Headers: *');
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月09月 13:54:20
     * ps:操作企业信息(添加、修改)
     * url:{{URL}}/index.php/api/enterprise/operateenter
     */
    public function operateenter(){
        $enterId = input('param.enterId');
        $customId = input('param.customId');
        $name = input('param.name');
        $proveNo = input('param.proveNo');
        $legalName = input('param.legalName');
        $legalNo = input('param.legalNo');
        $legalPhone = input('param.legalPhone');
        $province = input('param.province');
        $city = input('param.city');
        $area = input('param.area');
        $address = input('param.address');
        $license = input('param.license');
        if(!$customId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $data['name'] = $name;
        $data['proveNo'] = $proveNo;
        $data['province'] = $province;
        $data['city'] = $city;
        $data['area'] = $area;
        $data['address'] = $address;
        $data['legalName'] = $legalName;
        $data['legalNo'] = $legalNo;
        $data['legalPhone'] = $legalPhone;
        $data['license'] = $license;

        $commonenter = new Commonenter();
        if($enterId){
            $enterproveNo = Db::name('enterprise')->where('id','<>',$enterId)->where('proveNo','=',$proveNo)->find();
            if($enterproveNo){
                ajaxReturn(['code'=>301,'msg'=>'企业信息已存在平台']);
            }
            //传输$enterId代表修改
            $commonenter->operateenter($data,$enterId,0);
        }else{
            $enterproveNo = Db::name('enterprise')->where('proveNo','=',$proveNo)->find();

            if($enterproveNo){
                ajaxReturn(['code'=>301,'msg'=>'企业信息已存在平台']);
            }
            //不传代表添加
            $enterId = $commonenter->operateenter($data);
            $encu = Db::name('enterprise_custom')
                ->where('enterprise_id','=',$enterId)
                ->where('custom_id','=',$customId)
                ->find();
            //绑定个人与企业关系
            if(!$encu){
                $encudata['enterprise_id'] = $enterId;
                $encudata['custom_id'] = $customId;
                $encudata['createtime'] = time();
                Db::name('enterprise_custom')->insert($encudata);
            }
        }
        ajaxReturn(['code'=>200,'msg'=>'操作成功','enterId'=>$enterId]);
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月09月 14:04:57
     * ps:获取企业信息
     * url:{{URL}}/index.php/api/enterprise/getenter
     */
    public function getenter(){
        $enterId = input('param.enterId');
        if(!$enterId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $enter = Db::name('enterprise')->where('id','=',$enterId)->find();
        $account = Db::name('account')->where('type_id','=',$enterId)->where('type','=','enterprise')->find();
        $data['enterId'] = $enterId;
        $data['name'] = trim($enter['name']);
        $data['proveNo'] = trim($enter['proveNo']);
        $data['province'] = trim($enter['province']);
        $data['city'] = trim($enter['city']);
        $data['area'] = trim($enter['area']);
        $data['address'] = trim($enter['address']);
        $data['attestation'] = trim($enter['attestation']);
        $data['serialNo'] = trim($enter['serialNo']);
        $data['attestationType'] = trim($enter['attestationType']);
        if($enter['finishedTime']){
            $data['finishedTime'] = date('Y-m-d H:i:s',$enter['finishedTime']);
        }else{
            $data['finishedTime'] = '';
        }
        $data['account'] = trim($enter['account']);
        $data['createtime'] =  date('Y-m-d H:i:s',$enter['createtime']);
        $data['legalName'] = trim($enter['legalName']);
        $data['legalNo'] = trim($enter['legalNo']);
        $data['legalPhone'] = trim($enter['legalPhone']);
        $data['template'] = trim($account['template']);
        $data['contract'] = trim($account['contract']);
        $data['license'] = trim($enter['license']);

        ajaxReturn(['code'=>200,'msg'=>'获取成功','data'=>$data]);
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月09月 14:23:15
     * ps:认证
     * url:{{URL}}/index.php/api/enterprise/attestation
     */
    public function attestation(){
        $enterId = input('param.enterId');
        $url = input('param.url');

        if(!$enterId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $enter = Db::name('enterprise')->where('id','=',$enterId)->find();
        if(!$enter['proveNo'] && !$enter['name'] && !$enter['legalPhone']){
            ajaxReturn(['code'=>301,'msg'=>'信息不全，无法进行认证']);
        }
        $commonattestation = new Commonattestation();
        $res = $commonattestation->enterprise($enter['id'],$url);
        if($res['code'] == 200){
            ajaxReturn(['code'=>200,'msg'=>'请求成功','url'=>$res['identifyUrl']]);
        }else{
            ajaxReturn(['code'=>303,'msg'=>$res['msg'],'url'=>'']);
        }
    }





}