<?php

namespace app\api\controller;

use app\common\controller\Commonattestation;
use app\common\controller\Commonenter;
use think\Controller;
use think\Db;
/**
 * Created by PhpStorm.
 * User:lang
 * time:2024年11月06月 13:41:11
 * ps:模版批量任务
 */
class Task extends Controller
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
     * time:2024年11月06月 16:17:05
     * ps:操作模版任务
     * url:{{URL}}/index.php/api/task/operatetask
     */
    public function operatetask(){
        $type = input('param.type');
        $typeId = input('param.typeId');
        $templateId = input('param.templateId');
        $name = input('param.name');
        $validTime = input('param.validTime');
        $contract = input('param.contract');
        $stamp = input('param.stamp');
        $signatureId = input('param.signatureId');
        $content = input('param.content');
        $signature = input('param.signature');//签署人信息
        //传任务id为修改不传为添加
        $taskId = input('param.taskId');
        if(!$type || !$typeId || !$templateId || !$name || !$validTime){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $tdata['type'] = $type;
        $tdata['type_id'] = $typeId;
        $tdata['template_id'] = $templateId;
        $tdata['name'] = $name;
        $tdata['validTime'] = strtotime($validTime);
        $tdata['contract'] = $contract;
        $tdata['stamp'] = $stamp;
        $tdata['signature_id'] = $signatureId;
        if($taskId){
            //修改
            $tdata['updatetime'] = time();
            Db::name('template_task')->where('id','=',$taskId)->update($tdata);
            //删除其他内容
            Db::name('task_content')->where('task_id','=',$taskId)->delete();
            Db::name('task_signing')->where('task_id','=',$taskId)->delete();


        }else{
            //添加
            $tdata['createtime'] = time();
            $taskId = Db::name('template_task')->insertGetId($tdata);
        }
        //添加任务模版内容
        $json_content = json_decode($content,true);
        foreach($json_content as $k=>$v){
            $condata['task_id'] = $taskId;
            $condata['name'] = $v['name'];
            $condata['describe'] = $v['describe'];
            $condata['type'] = $v['type'];
            $condata['content'] = $v['content'];
            $condata['createtime'] = time();
            Db::name('task_content')->insertGetId($condata);
        }
        //添加任务签署方
        $json_signature = json_decode($signature,true);
        foreach($json_signature as $k=>$v){
            $signdata['name'] = $v['name'];
            $signdata['phone'] = $v['phone'];
            $signdata['type'] = $v['type'];
            $signdata['entername'] = $v['entername'];
            $signdata['TCN'] = $v['TCN'];
            $signdata['createtime'] = time();
            $signdata['task_id'] = $taskId;
            Db::name('task_signing')->insertGetId($signdata);
        }
        ajaxReturn(['code'=>200,'msg'=>'操作成功','taskId'=>$taskId]);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月07月 10:49:57
     * ps:获取任务详情
     * url:{{URL}}/index.php/api/task/gettask
     */
    public function gettask(){
        $taskId = input('param.taskId');
        if(!$taskId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $task = Db::name('template_task')->where('id','=',$taskId)->find();
        $data['type'] = $task['type'];
        $data['typeId'] = $task['type_id'];
        $data['templateId'] = $task['template_id'];
        $data['name'] = $task['name'];
        $data['validTime'] = date('Y-m-d H:i:s',$task['validTime']);
        $data['contract'] = $task['contract'];
        $data['state'] = $task['state'];
        if($task['state'] == 0){
            $data['stateName'] = '执行中';
        }else if($task['state'] == 1){
            $data['stateName'] = '已停止';
        }else if($task['state'] == 2){
            $data['stateName'] = '已到期';
        }else if($task['state'] == 3){
            $data['stateName'] = '无合同';
        }
        $data['createtime'] =  date('Y-m-d H:i:s',$task['createtime']);
        $data['content'] = array();
        $data['signing'] = array();
        $content = Db::name('task_content')->where('task_id','=',$taskId)->order('id asc')->select();
        $signing = Db::name('task_signing')->where('task_id','=',$taskId)->order('id asc')->select();
        foreach($content as $k=>$v){
            $data['content'][$k]['name'] = $v['name'];
            $data['content'][$k]['content'] = $v['content'];
            $data['content'][$k]['describe'] = $v['describe'];
            $data['content'][$k]['type'] = $v['type'];
            $data['content'][$k]['must'] = $v['must'];
        }
        foreach($signing as $k=>$v){
            $data['signing'][$k]['name'] = $v['name'];
            $data['signing'][$k]['type'] = $v['type'];
            $data['signing'][$k]['phone'] = $v['phone'];
            $data['signing'][$k]['entername'] = $v['entername'];
            $data['signing'][$k]['TCN'] = $v['TCN'];
        }
        ajaxReturn(['code'=>200,'msg'=>'获取成功','data'=>$data]);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月07月 14:55:07
     * ps:操作状态
     * url:{{URL}}/index.php/api/task/operatestate
     */
    public function operatestate(){
        $taskId = input('param.taskId');
        $state = input('param.state');
        if(!$taskId || !$state){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $data['state'] = $state;
        $data['updatetime'] = time();
        Db::name('state')->where('id','=',$taskId)->update($data);
        ajaxReturn(['code'=>200,'msg'=>'操作成功']);
    }

    /**
     * Created by PhpStorm.
     * User:lang
     * time:2024年11月07月 15:24:09
     * ps:任务列表
     * url:{{URL}}/index.php/api/task/gettasklist
     */
    public function gettasklist(){
        $type = input('param.type');
        $typeId = input('param.typeId');
        $templateId = input('param.templateId');
        $search = input('search');
        $state = input('state');
        $page = input('page');
        $limit = input('limit');
        if(!$limit){
            $limit = 10;
        }
        if(!$type || !$typeId){
            ajaxReturn(['code'=>300,'msg'=>'缺少参数']);
        }
        $where = array();
        if($state){
            $where['t.tate'] = $templateId;
        }
        if($templateId){
            $where['t.template_id'] = $state;

        }
        $whereor = '';
        if($search){
            $whereor = " `t`.`name` LIKE '%$search%'
                           OR `template`.`name` LIKE '%$search%'";
        }
        $task = Db::name('template_task as t')
            ->join('template','template.id = t.template_id')
            ->where('t.type','=',$type)
            ->where('t.type_id','=',$typeId)
            ->where($whereor)
            ->where($where)
            ->order('t.id desc')
            ->page($page,$limit)
            ->field('t.*,template.name as templateName')
            ->select();

        $count = Db::name('template_task as t')
            ->join('template','template.id = t.template_id')
            ->where('t.type','=',$type)
            ->where('t.type_id','=',$typeId)
            ->where($whereor)
            ->where($where)
            ->order('t.id desc')
            ->count();
        $data = array();
        foreach($task as $k =>$v){
            $data[$k]['taskId'] = $v['id'];
            $data[$k]['type'] = $v['type'];
            $data[$k]['typeId'] = $v['type_id'];
            $data[$k]['templateId'] = $v['template_id'];
            $data[$k]['name'] = $v['name'];
            $data[$k]['validTime'] = date('Y-m-d H:i:s',$v['validTime']);
            $data[$k]['contract'] = $v['contract'];
            $data[$k]['state'] = $v['state'];
            if($v['state'] == 0){
                $data[$k]['stateName'] = '执行中';
            }else if($v['state'] == 1){
                $data[$k]['stateName'] = '已停止';
            }else if($v['state'] == 2){
                $data[$k]['stateName'] = '已到期';
            }else if($v['state'] == 3){
                $data[$k]['stateName'] = '无合同';
            }
            $data[$k]['createtime'] =  date('Y-m-d H:i:s',$v['createtime']);
            $data[$k]['templateName'] = $v['templateName'];
            $contractnum = Db::name('contract')->where('task_id','=',$v['id'])->count();
            $wccontract = Db::name('contract')->where('task_id','=',$v['id'])->where('state','=',2)->count();
            $wfqcontract = Db::name('contract')->where('task_id','=',$v['id'])->where('state','=',10)->count();
            $qyzcontract = Db::name('contract')->where('task_id','=',$v['id'])->where('state','=',1)->count();
            $data[$k]['contractnum'] = $contractnum;
            $data[$k]['contractdone'] = $wccontract;
            $data[$k]['contractwfq'] = $wfqcontract;
            $data[$k]['contractsip'] = $qyzcontract;
        }
        $sumpage = 0;
        $sumpage  = ceil($count/$limit);
        ajaxReturn(['code'=>200,'msg'=>'获取成功','data'=>$data,'page'=>$page,'limit'=>$limit,'sum_page'=>$sumpage]);
    }

}