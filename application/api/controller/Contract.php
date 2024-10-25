<?php

namespace app\api\controller;

use app\common\controller\Commonattestation;
use app\common\controller\Commoncontract;
use app\common\controller\Commonenter;
use app\common\controller\Commonuser;
use think\Controller;
use think\Db;

class Contract extends Gathercontroller
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
     * time:2024年9月19月 17:33:32
     * ps:合同列表（我收到、我发起、抄送我）
     * url:{{URL}}/index.php/api/contract/contractlist
     */
   public function contractlist(){
       $type = input('param.type');
       $typeId = input('param.typeId');
       $listtype = input('param.listtype');//1:我收到；2：我发起；3：抄送我
       $search = input('search');
       $page = input('page');
       $limit = input('limit');
       if(!$limit){
           $limit = 10;
       }
       if(!$type){
           ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
       }
       if(!$typeId){
           ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
       }
       if(!$listtype){
           ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
       }
       if($listtype == 1){
           $whereor = '';
           if($search){
               $whereor = "`contract`.`contractName` LIKE '%$search%'";
           }
           //我收到
           $contract = Db::name('contract_signing as s')
               ->join('contract','contract.id=s.contract_id')
               ->where($whereor)
               ->where('s.type','=',$type)
               ->where('s.type_id','=',$typeId)
               ->where('contract.initiate_id','<>',$typeId)
               ->order('contract.id desc')
               ->page($page,$limit)
               ->select();
           $count = Db::name('contract_signing as s')
               ->join('contract','contract.id=s.contract_id')
               ->where('s.type','=',$type)
               ->where('s.type_id','=',$typeId)
               ->where('contract.initiate_id','<>',$typeId)
               ->order('contract.id desc')
               ->count();
       }else if($listtype == 2){
           $whereor = '';
           if($search){
               $whereor = "`contractName` LIKE '%$search%'";
           }

           //我发起的
           $contract = Db::name('contract')
               ->where($whereor)
               ->where('initiateType','=',$type)
               ->where('initiate_id','=',$typeId)
               ->order('id desc')
               ->page($page,$limit)
               ->field('id as contract_id,initiateType,initiate_id,contractNo,contractName,state,createtime')

               ->select();

           $count = Db::name('contract')
               ->where($whereor)
               ->where('initiateType','=',$type)
               ->where('initiate_id','=',$typeId)
               ->order('id desc')
               ->count();
       }else if($listtype == 3){
           //抄送我的
           $whereor = '';
           if($search){
               $whereor = "`contract`.`contractName` LIKE '%$search%'";
           }

           $contract = Db::name('contract_macf as m')
               ->join('contract','contract.id=m.contract_id')
               ->where($whereor)
               ->where('m.type','=',$type)
               ->where('m.type_id','=',$typeId)
               ->order('contract.id desc')
               ->page($page,$limit)
               ->select();
           $count = Db::name('contract_macf as m')
               ->join('contract','contract.id=m.contract_id')
               ->where($whereor)
               ->where('m.type','=',$type)
               ->where('m.type_id','=',$typeId)
               ->count();
       }else if($listtype == 4){
           //待我操作
           $whereor = '';
           if($search){
               $whereor = "`contract`.`contractName` LIKE '%$search%'";
           }
           $contract = Db::name('contract_signing as s')
               ->join('contract','contract.id=s.contract_id')
               ->where($whereor)
               ->where('s.type','=',$type)
               ->where('s.type_id','=',$typeId)
               ->where('s.state','=',0)
               ->order('contract.id desc')
               ->page($page,$limit)
               ->select();
           $count = Db::name('contract_signing as s')
               ->join('contract','contract.id=s.contract_id')
               ->where('s.type','=',$type)
               ->where('s.type_id','=',$typeId)
               ->where('s.state','=',0)
               ->order('contract.id desc')
               ->count();
       }else if($listtype == 5){
           //待他人操作
           $whereor = '';
           if($search){
               $whereor = "`contract`.`contractName` LIKE '%$search%'";
           }
           $contract = Db::name('contract as c')
               ->join('contract_signing','c.id=contract_signing.contract_id')
               ->where($whereor)
               ->where('c.initiateType','=',$type)
               ->where('c.initiate_id','=',$typeId)
               ->where('contract_signing.type','<>',$type,'contract_signing.type_id','<>',$typeId)
               ->where('contract_signing.state','=',0)
               ->order('c.id desc')
               ->page($page,$limit)
               ->select();
           $count = Db::name('contract as c')
               ->join('contract_signing','c.id=contract_signing.contract_id')
               ->where($whereor)
               ->where('c.initiateType','=',$type)
               ->where('c.initiate_id','=',$typeId)
               ->where('contract_signing.type','<>',$type,'contract_signing.type_id','<>',$typeId)
               ->where('contract_signing.state','=',0)
               ->count();
       }else if($listtype == 6){
           //已完成
           $whereor = '';
           if($search){
               $whereor = "`contract`.`contractName` LIKE '%$search%'";
           }

           $contract = Db::name('contract_signing as s')
               ->join('contract','contract.id=s.contract_id')
               ->where($whereor)
               ->where('s.type','=',$type)
               ->where('s.type_id','=',$typeId)
               ->where('contract.state','=',2)
               ->order('contract.id desc')
               ->page($page,$limit)
               ->select();
           $count = Db::name('contract_signing as s')
               ->join('contract','contract.id=s.contract_id')
               ->where('s.type','=',$type)
               ->where('s.type_id','=',$typeId)
               ->where('contract.state','=',2)
               ->order('contract.id desc')
               ->count();
       }
       $data = array();
       $common = new \app\common\controller\Common();
       foreach($contract as $k=>$v){
           $data[$k]['contractId'] = $v['contract_id'];
           $data[$k]['contractNo'] = $v['contractNo'];

           $data[$k]['contractName'] = $v['contractName'];
           $data[$k]['initiateType'] = $v['initiateType'];
           $data[$k]['initiate_id'] = $v['initiate_id'];
           if($v['initiateType'] == 'custom'){
               $data[$k]['initiateName'] = $common->getcustom($v['initiate_id'],'name')['name'];
           }else{
               $data[$k]['initiateName'] = $common->getenter($v['initiate_id'],'name')['name'];
           }
           $data[$k]['state'] = $v['state'];
           if($v['state'] == 0){
               $data[$k]['stateName'] = '待签约';
           }else if($v['state'] == 1){
               $data[$k]['stateName'] = '签约中';
           }else if($v['state'] == 2){
               $data[$k]['stateName'] = '已签约';
           }else if($v['state'] == 3){
               $data[$k]['stateName'] = '过期';
           }else if($v['state'] == 4){
               $data[$k]['stateName'] = '拒签';
           }else if($v['state'] == 5){
               $data[$k]['stateName'] = '未发起';
           }else if($v['state'] == 6){
               $data[$k]['stateName'] = '作废';
           }else if($v['state'] == 7){
               $data[$k]['stateName'] = '撤销';
           }else if($v['state'] == 10){
               $data[$k]['stateName'] = '待发起';
           }
           $data[$k]['signing'] = array();
           $signing = Db::name('contract_signing')->where('contract_id','=',$v['contract_id'])->order('id desc')->select();
           foreach($signing as $kk=>$vv){


               $data[$k]['signing'][$kk]['type'] = $vv['type'];
               $data[$k]['signing'][$kk]['typeId'] = $vv['type_id'];
               if($vv['type'] == 'custom'){
                   $data[$k]['signing'][$kk]['typeName'] = $common->getcustom($vv['type_id'],'name')['name'];
               }else{
                   $data[$k]['signing'][$kk]['typeName'] = $common->getenter($vv['type_id'],'name')['name'];
               }
               $data[$k]['signing'][$kk]['state'] = $vv['state'];
               if($vv['state'] == 0){
                   $data[$k]['signing'][$kk]['stateName'] = '未签署';
               }else if($vv['state'] == 1){
                   $data[$k]['signing'][$kk]['stateName'] = '已签署';
               }else if($vv['state'] == 2){
                   $data[$k]['signing'][$kk]['stateName'] = '拒绝签署';
               }
           }
           $data[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);

       }
       $sumpage = 0;
       $sumpage  = ceil($count/$limit);
       ajaxReturn(['code'=>200,'msg'=>'获取成功','data'=>$data,'page'=>$page,'limit'=>$limit,'sum_page'=>$sumpage]);

   }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月20月 10:55:31
     * ps:合同各类型总数
     * url:{{URL}}/index.php/api/contract/getcontractcount
     */
   public function getcontractcount(){
       $type = input('param.type');
       $typeId = input('param.typeId');

       if(!$type){
           ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
       }
       if(!$typeId){
           ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
       }

           $receive = Db::name('contract_signing as s')
               ->join('contract','contract.id=s.contract_id')
               ->where('s.type','=',$type)
               ->where('s.type_id','=',$typeId)
               ->where('contract.initiateType','<>',$type,'contract.initiate_id','<>',$typeId)
               ->count();
           $initiate = Db::name('contract')
               ->where('initiateType','=',$type)
               ->where('initiate_id','=',$typeId)
               ->count();
           $sendcopy = Db::name('contract_macf as m')
               ->join('contract','contract.id=m.contract_id')
               ->where('m.type','=',$type)
               ->where('m.type_id','=',$typeId)
               ->count();
           $ioperate = Db::name('contract_signing as s')
               ->join('contract','contract.id=s.contract_id')
               ->where('s.type','=',$type)
               ->where('s.type_id','=',$typeId)
               ->where('s.state','=',0)
               ->count();
           $heoperate = Db::name('contract as c')
               ->join('contract_signing','c.id=contract_signing.contract_id')
               ->where('c.initiateType','=',$type)
               ->where('c.initiate_id','=',$typeId)
               ->where('contract_signing.type','<>',$type,'contract_signing.type_id','<>',$typeId)
               ->where('contract_signing.state','=',0)
               ->count();
       $finish = Db::name('contract_signing as s')
           ->join('contract','contract.id=s.contract_id')
           ->where('s.type','=',$type)
           ->where('s.type_id','=',$typeId)
           ->where('contract.state','=',2)
           ->count();
       $data['receive'] = $receive;
       $data['initiate'] = $initiate;
       $data['sendcopy'] = $sendcopy;
       $data['ioperate'] = $ioperate;
       $data['heoperate'] = $heoperate;
       $data['finish'] = $finish;
       ajaxReturn(['code'=>200,'msg'=>'获取成功','data'=>$data]);
   }


   public function test(){

       $data[0]['name'] = '法定代表人名称';
       $data[0]['type'] = 1;
       $data[0]['describe'] = '';
       $data[0]['content'] = '郎大大';
       $data[1]['name'] = '法人身份证号';
       $data[1]['type'] = 1;
       $data[1]['describe'] = '';
       $data[1]['content'] = '211282200001143815';
       $data[2]['name'] = '单位名称';
       $data[2]['type'] = 1;
       $data[2]['describe'] = '';
       $data[2]['content'] = '奥博森科技有限公司';
       $data[3]['name'] = '处室';
       $data[3]['type'] = 1;
       $data[3]['describe'] = '';
       $data[3]['content'] = '研发部';
       $data[4]['name'] = '负责人姓名';
       $data[4]['type'] = 1;
       $data[4]['describe'] = '';
       $data[4]['content'] = '郎骁';
       $data[5]['name'] = '负责人证件号';
       $data[5]['type'] = 1;
       $data[5]['describe'] = '';
       $data[5]['content'] = '211282200001143815';
       $rest = json_encode($data);
       print_r($rest);
   }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月20月 14:32:57
     * ps:通过模版生成合同
     * url:{{URL}}/index.php/api/contract/templatecontract
     */
    public function templatecontract(){
        $type = input('param.type');
        $typeId = input('param.typeId');
        $contractName = input('param.contractName');
        $expireTime = input('param.expireTime');//时间戳
        $signingTime = input('param.signingTime');//时间戳
        $templateId = input('param.templateId');
        $annex = input('param.annex');
        $content = input('param.content');//模板内容
        $signature = input('param.signature');//签署人信息
        $macf = input('param.macf');//抄送人信息
        if(!$type){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$typeId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$contractName){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$templateId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$content){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$signature){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }

        //查询状态余额是否充足
        $account = Db::name('account')->where('type','=',$type)->where('type_id','=',$typeId)->find();
        if($account['contract'] < 1){
            ajaxReturn(['code'=>330,'msg'=>'账户余额不足，无法生成合同']);
        }
        $commoncontract = new Commoncontract();
        //添加合同信息
        $data['contractNo'] = $commoncontract->addcontractNo($this->platformId);
        $data['contractName'] = $contractName;
        $data['expireTime'] = $expireTime;
        $data['signingTime'] = $signingTime;
        $data['template_id'] = $templateId;
        $data['state'] = 10;//待发起合同状态

        $data['template'] = 1;
        $contractId =$commoncontract->operatecontract($data,$type,$typeId);
        //添加合同模版内容
        $json_content = json_decode($content,true);
        foreach($json_content as $k=>$v){
            $condata['contract_id'] = $contractId;
            $condata['name'] = $v['name'];
            $condata['describe'] = $v['describe'];
            $condata['type'] = $v['type'];
            $condata['content'] = $v['content'];
            $condata['createtime'] = time();
            Db::name('contract_template_content')->insertGetId($condata);
            //验证必填项是否必填，不必填删除合同并且返回错误
            if($v['must'] == 1){
                if(!$v['content']){
                    //删除合同、合同内容
                    Db::name('contract')->where('id','=',$contractId)->delete();
                    Db::name('contract_template_content')->where('contract_id','=',$contractId)->delete();
                    ajaxReturn(['code'=>300,'msg'=>'请填写完成后生成合同']);
                }
            }
        }
        //添加合同签署方
        $commonuser= new Commonuser();
        $commonenter = new Commonenter();
        $json_signature = json_decode($signature,true);
        foreach($json_signature as $k=>$v){
            if($v['type'] == 'custom'){
                //个人用户
                $custom = Db::name('custom')->where('phone','=',$v['phone'])->find();
                if(!$custom){
                    $cudata['name'] = $v['name'];
                    $cudata['phone'] = $v['phone'];
                    $customId = $commonuser->operatecustom($cudata);
                }else{
                    $signdata['account'] = $custom['account'];
                    $customId = $custom['id'];
                }
                $signdata['type'] = $v['type'];
                $signdata['type_id'] = $customId;
                $signdata['custom_id'] = $customId;
            }else{
                //企业用户
                $enter = Db::name('enterprise')->where('name','=',$v['entername'])->find();
                $custom = Db::name('custom')->where('phone','=',$v['phone'])->find();
                if(!$custom){
                    $cudata['name'] = $v['name'];
                    $cudata['phone'] = $v['phone'];
                    $customId = $commonuser->operatecustom($cudata);
                }else{
                    $customId = $custom['id'];
                }
                if(!$enter){
                    $endata['name'] = $v['entername'];
                    $enterId = $commonenter->operateenter($endata);
                }else{
                    $signdata['account'] = $enter['account'];

                    $enterId = $enter['id'];
                }
                $encu = Db::name('enterprise_custom')->where('enterprise_id','=',$enterId)->where('custom_id','=',$customId)->find();
                if(!$encu){
                    $encudata['enterprise_id'] = $enterId;
                    $encudata['custom_id'] = $customId;
                    $encudata['createtime'] = time();
                    $encuId = Db::name('enterprise_custom')->insertGetId($encudata);
                    $commonenter->addmember($encuId);
                }
                $signdata['type'] = $v['type'];
                $signdata['type_id'] = $enterId;
                $signdata['custom_id'] = $customId;
            }
            $signdata['TCN'] = $v['TCN'];
            $signdata['createtime'] = time();
            $signdata['contract_id'] = $contractId;
            if($signdata['type_id']){
                Db::name('contract_signing')->insertGetId($signdata);
            }

            //添加相对方
            if($typeId ==$signdata['type_id'] && $type ==$signdata['type']){

            }else{
                $counterpart = Db::name('counterpart')
                    ->where('ownerType','=',$type)
                    ->where('owner_id','=',$typeId)
                    ->where('type','=',$signdata['type'])
                    ->where('type_id','=',$signdata['type_id'])->find();
                //如果没有相对方就添加
                if(!$counterpart){
                    $cpartdata['ownerType'] = $type;
                    $cpartdata['owner_id'] = $typeId;
                    $cpartdata['type'] = $signdata['type'];
                    $cpartdata['type_id'] = $signdata['type_id'];
                    $cpartdata['createtime'] = time();
                    Db::name('counterpart')->insertGetId($cpartdata);
                }
            }

        }
        $json_annex = json_decode($annex,true);
        foreach($json_annex as $k=>$v){
            $annexdata['contract_id'] = $contractId;
            $annexdata['name'] = $v['name'];
            $annexdata['file'] = $v['file'];
            $annexdata['createtime'] = time();
            Db::name('contract_annex')->insertGetId($annexdata);
        }

        $json_macf = json_decode($macf,true);
        foreach($json_macf as $k=>$v){
            if($v['type'] == 'custom'){
                //个人用户
                $custom = Db::name('custom')->where('phone','=',$v['phone'])->find();
                if(!$custom){
                    $cudata['name'] = $v['name'];
                    $cudata['phone'] = $v['phone'];
                    $customId = $commonuser->operatecustom($cudata);
                }else{
//                    $macfdata['account'] = $custom['account'];

                    $customId = $custom['id'];
                }
                $macfdata['type'] = $v['type'];
                $macfdata['type_id'] = $customId;
                $macfdata['custom_id'] = $customId;
            }else{
                //企业用户
                $enter = Db::name('enterprise')->where('name','=',$v['entername'])->find();
                $custom = Db::name('custom')->where('phone','=',$v['phone'])->find();
                if(!$custom){
                    $cudata['name'] = $v['name'];
                    $cudata['phone'] = $v['phone'];
                    $customId = $commonuser->operatecustom($cudata);
                }else{
                    $customId = $custom['id'];
                }
                if(!$enter){
                    $endata['name'] = $v['entername'];
                    $enterId = $commonenter->operateenter($endata);
                }else{

                    $enterId = $enter['id'];
                }
                $encu = Db::name('enterprise_custom')->where('enterprise_id','=',$enterId)->where('custom_id','=',$customId)->find();
                if(!$encu){
                    $encudata['enterprise_id'] = $enterId;
                    $encudata['custom_id'] = $customId;
                    $encudata['createtime'] = time();
                    $ecId = Db::name('enterprise_custom')->insertGetId($encudata);
                    $commonenter->addmember($ecId);
                }
                $macfdata['type'] = $v['type'];
                $macfdata['type_id'] = $enterId;
                $macfdata['custom_id'] = $customId;
            }
            $macfdata['createtime'] = time();
            $macfdata['contract_id'] = $contractId;
            Db::name('contract_macf')->insertGetId($macfdata);

        }

        $res = $commoncontract->initiatecontract($contractId);

        if($res['code'] == 200){
            //扣除账户合同份数
            $acedit['contract'] = $account['contract'] -1;
            $acedit['usecontract'] = $account['usecontract'] +1;
            $acedit['updatetime'] = time();
            Db::name('account')->where('type','=',$type)->where('type_id','=',$typeId)->update($acedit);
            ajaxReturn(['code'=>200,'msg'=>'发起成功','contractId'=>$contractId,'url'=>$res['url']]);
        }else{
            ajaxReturn(['code'=>303,'msg'=>$res['msg']]);
        }

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月20月 16:05:52
     * ps:获取合同地址(请求为签署方且未签署获取签署地址；其他为详情地址)
     * url:{{URL}}/index.php/api/contract/getcontracturl
     */
    public function getcontracturl(){
        $type = input('param.type');
        $typeId = input('param.typeId');
        $contractId = input('param.contractId');
        $redirectUrl = input('param.redirectUrl');
        if(!$type){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$typeId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $sign = Db::name('contract_signing')->where('type','=',$type)->where('type_id','=',$typeId)->where('contract_id','=',$contractId)->find();

        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        $commoncontract = new Commoncontract();
        $url = '';
        if($contract['state'] == 10){
            //未发起状态合同
            $res = $commoncontract->getapicontracturl($contractId);
        }else if($contract['state'] == 6){
            //作废合同
            $res = $commoncontract->getapicontracturl($contractId);
        }else if($contract['state'] == 7){
            //撤销合同
            $res = $commoncontract->getapicontracturl($contractId);
        }else if($contract['state'] == 3){
            //过期合同
            $res = $commoncontract->getapicontracturl($contractId);
        } else{
            if($sign){
                if($sign['state'] == 0){
                    $res = $commoncontract->getsignerurl($sign['id'],$redirectUrl);
                }else{
                    $res = $commoncontract->getapicontracturl($contractId);
                }
            }else{
                $res = $commoncontract->getapicontracturl($contractId);
            }
        }

        if($res['code'] == 200){
            ajaxReturn(['code'=>200,'msg'=>'获取成功','url'=>$res['url']]);

        }else{
            ajaxReturn(['code'=>303,'msg'=>$res['msg']]);
        }
    }


    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年9月23月 14:42:03
     * ps:发起合同（上传文件）
     * url:{{URL}}/index.php/api/contract/filecontract
     */
    public function filecontract(){
        $type = input('param.type');
        $typeId = input('param.typeId');
        $contractName = input('param.contractName');
        $expireTime = input('param.expireTime');//时间戳
        $file = input('param.file');//合同文件
        $filename = input('param.filename');//合同名称带后缀
        $signature = input('param.signature');//签署人信息
        $annex = input('param.annex');//合同附件（文件地址逗号隔开）
        $signingTime = input('param.signingTime');//时间戳
        $macf = input('param.macf');//抄送人信息


        if(!$type){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$typeId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$contractName){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$signature){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$filename){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        if(!$file){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        //查询状态余额是否充足
        $account = Db::name('account')->where('type','=',$type)->where('type_id','=',$typeId)->find();
//        if($account['contract'] < 1){
//            ajaxReturn(['code'=>330,'msg'=>'账户余额不足，无法生成合同']);
//        }

        //添加合同信息
        $commoncontract = new Commoncontract();
        $data['contractNo'] = $commoncontract->addcontractNo($this->platformId);
        $data['contractName'] = $contractName;
        $data['expireTime'] = $expireTime;
        $data['fileName'] = $filename;
        $data['contractFile'] = $file;
        $data['signingTime'] = $signingTime;
        $data['state'] = 10;

        $contractId =$commoncontract->operatecontract($data,$type,$typeId);
        //添加合同签署方
        $commonuser= new Commonuser();
        $commonenter = new Commonenter();
        $json_signature = json_decode($signature,true);
        foreach($json_signature as $k=>$v){
            if($v['type'] == 'custom'){
                //个人用户
                $custom = Db::name('custom')->where('phone','=',$v['phone'])->find();
                if(!$custom){
                    $cudata['name'] = $v['name'];
                    $cudata['phone'] = $v['phone'];
                    $customId = $commonuser->operatecustom($cudata);
                }else{
                    $signdata['account'] = $custom['account'];

                    $customId = $custom['id'];
                }
                $signdata['type'] = $v['type'];
                $signdata['type_id'] = $customId;
                $signdata['custom_id'] = $customId;
            }else{
                //企业用户
                $enter = Db::name('enterprise')->where('name','=',$v['entername'])->find();
                $custom = Db::name('custom')->where('phone','=',$v['phone'])->find();
                if(!$custom){
                    $cudata['name'] = $v['name'];
                    $cudata['phone'] = $v['phone'];
                    $customId = $commonuser->operatecustom($cudata);
                }else{
                    $customId = $custom['id'];
                }
                if(!$enter){
                    $endata['name'] = $v['entername'];
                    $enterId = $commonenter->operateenter($endata);
                }else{
                    $signdata['account'] = $enter['account'];

                    $enterId = $enter['id'];
                }
                $encu = Db::name('enterprise_custom')->where('enterprise_id','=',$enterId)->where('custom_id','=',$customId)->find();
                if(!$encu){
                    $encudata['enterprise_id'] = $enterId;
                    $encudata['custom_id'] = $customId;
                    $encudata['createtime'] = time();
                    Db::name('enterprise_custom')->insertGetId($encudata);
                }
                $signdata['type'] = $v['type'];
                $signdata['type_id'] = $enterId;
                $signdata['custom_id'] = $customId;
            }
            $signdata['TCN'] = '签署方'.$k;//$v['TCN'];
            $signdata['createtime'] = time();
            $signdata['contract_id'] = $contractId;
            Db::name('contract_signing')->insertGetId($signdata);
            //添加相对方
            if($typeId ==$signdata['type_id'] && $type ==$signdata['type']){

            }else{
                $counterpart = Db::name('counterpart')
                    ->where('ownerType','=',$type)
                    ->where('owner_id','=',$typeId)
                    ->where('type','=',$signdata['type'])
                    ->where('type_id','=',$signdata['type_id'])->find();
                //如果没有相对方就添加
                if(!$counterpart){
                    $cpartdata['ownerType'] = $type;
                    $cpartdata['owner_id'] = $typeId;
                    $cpartdata['type'] = $signdata['type'];
                    $cpartdata['type_id'] = $signdata['type_id'];
                    $cpartdata['createtime'] = time();
                    Db::name('counterpart')->insertGetId($cpartdata);
                }
            }
        }

        $json_annex = json_decode($annex,true);

        foreach($json_annex as $k=>$v){
            $annexdata['contract_id'] = $contractId;
            $annexdata['name'] = $v['name'];
            $annexdata['file'] = $v['file'];
            $annexdata['createtime'] = time();
            Db::name('contract_annex')->insertGetId($annexdata);
        }

        $json_macf = json_decode($macf,true);
        foreach($json_macf as $k=>$v){
            if($v['type'] == 'custom'){
                //个人用户
                $custom = Db::name('custom')->where('phone','=',$v['phone'])->find();
                if(!$custom){
                    $cudata['name'] = $v['name'];
                    $cudata['phone'] = $v['phone'];
                    $customId = $commonuser->operatecustom($cudata);
                }else{
//                    $macfdata['account'] = $custom['account'];

                    $customId = $custom['id'];
                }
                $macfdata['type'] = $v['type'];
                $macfdata['type_id'] = $customId;
                $macfdata['custom_id'] = $customId;
            }else{
                //企业用户
                $enter = Db::name('enterprise')->where('name','=',$v['entername'])->find();
                $custom = Db::name('custom')->where('phone','=',$v['phone'])->find();
                if(!$custom){
                    $cudata['name'] = $v['name'];
                    $cudata['phone'] = $v['phone'];
                    $customId = $commonuser->operatecustom($cudata);
                }else{
                    $customId = $custom['id'];
                }
                if(!$enter){
                    $endata['name'] = $v['entername'];
                    $enterId = $commonenter->operateenter($endata);
                }else{

                    $enterId = $enter['id'];
                }
                $encu = Db::name('enterprise_custom')->where('enterprise_id','=',$enterId)->where('custom_id','=',$customId)->find();
                if(!$encu){
                    $encudata['enterprise_id'] = $enterId;
                    $encudata['custom_id'] = $customId;
                    $encudata['createtime'] = time();
                    Db::name('enterprise_custom')->insertGetId($encudata);
                }
                $macfdata['type'] = $v['type'];
                $macfdata['type_id'] = $enterId;
                $macfdata['custom_id'] = $customId;
            }
            $macfdata['createtime'] = time();
            $macfdata['contract_id'] = $contractId;
            Db::name('contract_macf')->insertGetId($macfdata);

        }
        $res = $commoncontract->initiatecontractfile($contractId);
        if($res['code'] == 200){
            //扣除账户合同份数
            $acedit['contract'] = $account['contract'] -1;
            $acedit['usecontract'] = $account['usecontract'] +1;
            $acedit['updatetime'] = time();
            Db::name('account')->where('type','=',$type)->where('type_id','=',$typeId)->update($acedit);
            ajaxReturn(['code'=>200,'msg'=>'发起成功','contractId'=>$contractId,'url'=>$res['url']]);
        }else{
            ajaxReturn(['code'=>303,'msg'=>$res['msg']]);
        }
    }

    public function test3(){
        $data[0]['name'] = '附件1.png';
        $data[0]['file'] = 'https://sign.obsend.com/upload/image/20240923/4183e1bfde27bda0468bca3ae4da5744.png';
        $data[1]['name'] = '附件2.png';
        $data[1]['file'] = 'https://sign.obsend.com/upload/image/20240923/4183e1bfde27bda0468bca3ae4da5744.png';
        $rest = json_encode($data);
        print_r($rest);
    }
    public function test2(){
        $data[0]['name'] = '郎骁';
        $data[0]['type'] = 'enterprise';
        $data[0]['phone'] = '13841046298';
        $data[0]['entername'] = '辽宁鑫通网络货运有限公司';
        $data[0]['TCN'] = '甲方';
        $data[1]['name'] = '房璐伟';
        $data[1]['type'] = 'custom';
        $data[1]['phone'] = '17647696691';
        $data[1]['entername'] = '';
        $data[1]['TCN'] = '乙方';
        $rest = json_encode($data);
        print_r($rest);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月22月 10:23:14
     * ps:撤销合同
     * url:{{URL}}/index.php/api/contract/revoke
     */
    public function revoke(){
        $contractId = input('param.contractId');
        $type = input('param.type');
        $typeId = input('param.typeId');
        $reason = input('param.reason');
        if(!$contractId && $type && $typeId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        if(!$contract){
            ajaxReturn(['code'=>301,'msg'=>'合同不存在']);
        }
        if($contract['state'] == 2){
            ajaxReturn(['code'=>301,'msg'=>'已签署合同无法撤销']);
        }
        if($contract['initiateType'] !=$type && $contract['initiate_id'] != $typeId){
            ajaxReturn(['code'=>301,'msg'=>'操作人不是合同发起人，撤销失败']);
        }
        $commoncontract = new Commoncontract();
        $res = $commoncontract->revokecontract($contractId,$reason);
        if($res['code'] == 200){
            ajaxReturn(['code'=>200,'msg'=>'操作成功']);
        }else{
            ajaxReturn(['code'=>303,'msg'=>'操作失败']);
        }

    }
    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月22月 10:28:58
     * ps:作废合同
     * url:{{URL}}/index.php/api/contract/cancel
     */
    public function cancel(){
        $contractId = input('param.contractId');
        $type = input('param.type');
        $typeId = input('param.typeId');
        $reason = input('param.reason');
        if(!$contractId && $type && $typeId && $reason){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }

        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        if(!$contract){
            ajaxReturn(['code'=>301,'msg'=>'合同不存在']);
        }
        if($contract['state'] != 2){
            ajaxReturn(['code'=>302,'msg'=>'未签署完成合同无法作废']);

        }
        if($contract['initiateType'] !=$type && $contract['initiate_id'] != $typeId){
            ajaxReturn(['code'=>302,'msg'=>'操作人不是合同发起人，作废失败']);
        }
        $commoncontract = new Commoncontract();
        $res = $commoncontract->cancelcontract($contractId,$reason);
        if($res['code'] == 200){
            ajaxReturn(['code'=>200,'msg'=>'操作成功']);
        }else{
            ajaxReturn(['code'=>302,'msg'=>'操作失败']);
        }

    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月24月 16:37:14
     * ps:发起合同签署
     * url:{{URL}}/index.php/api/contract/initiatecontract
     */
    public function initiatecontract(){
        $contractId = input('param.contractId');
        if(!$contractId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        if($contract['state'] !=10){
            ajaxReturn(['code'=>301,'msg'=>'当前状态不需要发起']);
        }
        $data['state'] = 0;
        $commoncontract = new Commoncontract();
        $commoncontract->operatecontract($data,$contract['initiateType'],$contract['initiate_id'],$contractId);
        $commoncontract->initiatesign($contractId);
        ajaxReturn(['code'=>200,'msg'=>'操作成功']);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年10月24月 16:46:07
     * ps:删除合同
     * url:{{URL}}/index.php/api/contract/delcontract
     */
    public function delcontract(){
        $contractId = input('param.contractId');
        if(!$contractId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $contract = Db::name('contract')->where('id','=',$contractId)->find();
        if($contract['state'] !=10){
            ajaxReturn(['code'=>301,'msg'=>'当前状态不能删除合同']);
        }
        $commoncontract = new Commoncontract();
        $commoncontract->delcontract($contractId);
        ajaxReturn(['code'=>200,'msg'=>'操作成功']);
    }
}