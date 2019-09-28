<?php
/*我要参赛*/
namespace Home\Controller;
use Home\Common\Controller\BaseController;

class SubjectController extends BaseController {
    public function index()
    {
      //报名按钮开关
      $time = M('sys_config')->where(['config_code'=>['in','m_attend_endtime,m_starttime,m_endtime']])->getField('config_code,config_value');
      if(strtotime($time['m_starttime']) <= strtotime(date('Y-m-d')) &&  strtotime($time['m_attend_endtime']) >= strtotime(date('Y-m-d')) &&  strtotime($time['m_endtime']) >= strtotime(date('Y-m-d'))){
        $isopen = 1;
      }else{
        if(strtotime($time['m_starttime']) > strtotime(date('Y-m-d'))){
          $isnotstart = 1;
        }
        $isopen = 0;
      }
      //判断用户是否登录
      $user_id = session("Match_User_Id");
      if($user_id){
        $tel = M('user')->where(['id'=>$user_id])->getField('telephone');
        $this->assign('tel',$tel);
      }
      $list = M('subject')->where(['status'=>1])->order('order_no')->select();
      $this->assign('isnotstart',$isnotstart);
      $this->assign('isopen',$isopen);
      $this->assign('list',$list);
      $this->display();
    }
    public function detail(){
      //内容
      $id =I('get.id',0,intval);
      $data = M('subject')->find($id);
      $content = M('subject_content')->order('order_no')->where(['sid'=>$id,'status'=>1])->select();
      $data['content'] = $content;
      //报名按钮开关
      $time = M('sys_config')->where(['config_code'=>['in','m_attend_endtime,m_starttime,m_endtime']])->getField('config_code,config_value');
      if(strtotime($time['m_starttime']) <= strtotime(date('Y-m-d')) &&  strtotime($time['m_attend_endtime']) >= strtotime(date('Y-m-d')) &&  strtotime($time['m_endtime']) >= strtotime(date('Y-m-d'))){
        $isopen = 1;
      }else{
        $isopen = 0;
      }
      $this->assign('isopen',$isopen);
      $this->assign('data',$data);
      $this->display();
      
    }
    //报名页面
    public function signup(){
      //报名按钮开关
      $time = M('sys_config')->where(['config_code'=>['in','m_attend_endtime,m_starttime,m_endtime']])->getField('config_code,config_value');
      if(strtotime($time['m_starttime']) <= strtotime(date('Y-m-d')) &&  strtotime($time['m_attend_endtime']) >= strtotime(date('Y-m-d')) &&  strtotime($time['m_endtime']) >= strtotime(date('Y-m-d'))){
        $isopen = 1;
      }else{
        $isopen = 0;
      }
      if(!$isopen){
        $this->error('不处于报名阶段','',1);
      }
      //判断这个题目禁用了吗
      $sid = I('get.sid',0,intval);
      $subject = M('subject')->where(['id'=>$sid,'status'=>1])->field('id,title')->find();
      if(!$subject){
        $this->error('题目不存在','',1);
      }
      //判断用户是否登录
      $user_id = session("Match_User_Id");
      if(!$user_id){
        $this->redirect('Login/index');
      }
      $tel = M('user')->where(['id'=>$user_id])->getField('telephone');
      //判断是否重复报名
      $count = M('subject_members')->where(['tel'=>$tel,'status'=>['neq',3]])->count();
      if($count >= 1){
        //或者直接跳转到个人中心页面
        $this->error('您已经报过名了','',1);
      }
      //省份
      $provinces = M('dictionary')
      ->where(['type_code'=>'government_area','status'=>1,'pid_key'=>0])
      ->field('id,dic_value')
      ->cache('government_area')
      ->select();
      $this->assign('subject',$subject);
      $this->assign('provinces',$provinces);
      $this->assign('tel',$tel);
      $this->display();
    }
    //动态获取城市
    function getCitys(){
      $province = I("post.province");
      $province = M('dictionary')->where(['id'=>$province])->getField('dic_key');
      $list = M('dictionary')
      ->cache(true)
      ->where(['pid_key'=>$province,'status'=>1])
      ->field('id,dic_key,dic_value')
      ->select();
      $this->ajaxReturn($list);
    }
    //ajax校验手机号
    function checkOrder(){
      $data = I('post.');
      $count = M('subject_members')->where(['tel'=>$data['value'],'status'=>1,'sid'=>$data['sid']])->count();
      if($count >= 1){
        $return['state'] = 0;
        $return['msg'] = '您已经报名该题目';
      }else{
        $sid = M('subject_members')->where(['tel'=>$data['value'],'status'=>1,'sid'=>['neq',$data['sid']]])->getField('sid');
        if($sid){
          $title = M('subject')->where(['id'=>$sid])->getField('title');
          $return['state'] = 2;
          $return['msg'] = '您已经报名过 [ '. $title .' ] ，是否使用已有报名信息报名该题目？';
        }else{
          $return['state'] = 1;
          $return['msg'] = '未报名过任何题目';
        }
        
      }
      $this->ajaxReturn($return);

    }
    function checkRepeat(){
      $data = I('post.');
      $count = M('subject_members')->where(['tel'=>$data['value'],'status'=>1,'sid'=>$data['sid']])->count();
      if($count >= 1){
        $this->ajaxReturn(false);
      }else{
        $this->ajaxReturn($true);
      }
      
    }
    //保存报名
    function saveMember(){
      $data = I('post.');
      $data['email'] =strtolower($data['email']);
      $uid = session("Match_User_Id");
      $data['uid'] = $uid;
      $data['status'] = 1;
      $data['create_time'] = date('Y-m-d H:i:s');
      //判断当前手机号是不是当前登录账号
      $ucount = M('user')->where(['id'=>$uid,'telephone'=>$data['tel']])->count();
      if($ucount < 1){
        $this->ajaxReturn(['success'=>false,'msg'=>'手机号与当前登录账号不符']);
      }
      //判断重复报名
      $tcount = M('subject_members')->where(['tel'=>$data['tel'],'status'=>['neq',3],'sid'=>$data['sid']])->count();
      if($tcount >= 1){
        $this->ajaxReturn(['success'=>false,'msg'=>'您的手机号已经报名该题目']);
      }
      $icount = M('subject_members')->where(['ID_num'=>$data['ID_num'],'status'=>['neq',3],'sid'=>$data['sid']])->count();
      if($icount >= 1){
        $this->ajaxReturn(['success'=>false,'msg'=>'您的证件号已经报名该题目']);
      }
      M()->startTrans();
      $res = M('subject_members')->add($data);
      $res1 = M('subject')->where(['id'=>$data['sid']])->setInc('member_counts');
      if($res !== false && $res1 !== false){
        M()->commit();
          //报名成功发送短信
          sendSmsForBM($data['tel']);
        $this->ajaxReturn(['success'=>true,'msg'=>'报名成功']);
      }else{
        M()->rollback();
        $this->ajaxReturn(['success'=>false,'msg'=>'操作失败，请稍后重试']);
      }
    }
    //已经报名一个题目后，再报名另一个题目
    function saveMemberAnother(){
      $data = I('post.');
      $formdata = M('subject_members')->field('name,email,tel,ID_num,province,city,company,school')->where(['tel'=>$data['tel'],'status'=>1])->find();
      $uid = session("Match_User_Id");
      $formdata['sid'] = $data['sid'];
      $formdata['uid'] = $uid;
      $formdata['tel'] = $data['tel'];
      $formdata['type'] = 1;
      $formdata['create_time'] = date('Y-m-d H:i:s');
      M()->startTrans();
      $res =  M('subject_members')->add($formdata);
      $res1 = M('subject')->where(['id'=>$data['sid']])->setInc('member_counts');
      if($res !== false && $res1 !== false){
        M()->commit();
          //报名成功发送短信
          sendSmsForBM($data['tel']);
        $this->ajaxReturn(['success'=>true,'msg'=>'报名成功']);
      }else{
        M()->rollback();
        $this->ajaxReturn(['success'=>false,'msg'=>'操作失败，请稍后重试']);
      }
    }
}