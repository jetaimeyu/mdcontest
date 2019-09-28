<?php
/**
 * Created by PhpStorm.
 * User: "李明冬"
 * Date: 2018/8/8
 * Time: 14:29
 */

namespace Home\Controller;


use Home\Common\Controller\BaseController;
use Home\Model\UserModel;

class PersoninfoController extends BaseController
{
    public function index()
    {
        $user_id = session('Match_User_Id');
        if($user_id){
            $subjects = M('subject_members')
                ->alias('sm')
                ->where(['uid'=>$user_id,'sm.status'=>1])
                ->join('left join subject s on s.id = sm.sid')
                ->field('sm.*,s.title,s.description,s.member_counts')
                ->select();
            $user = M('user')->field('id,true_name,telephone,telephone as truetelephone')->find($user_id);
            $user['telephone'] =get_hide_tel($user['telephone']);
            //按钮开关
            $time = M('sys_config')->where(['config_code'=>['in','m_starttime,m_attend_endtime,m_dowloadtime,m_first_uploadtime,m_second_uploadtime,m_endtime']])->getField('config_code,config_value');
            if(strtotime($time['m_first_uploadtime']) == strtotime(date('Y-m-d')) ||  strtotime($time['m_second_uploadtime']) == strtotime(date('Y-m-d')) ){
                $is['upload'] = 1;
            }else{
                $is['upload'] = 0;
            }
            if(strtotime($time['m_second_uploadtime']) < strtotime(date('Y-m-d')) ){
                $is['endupload'] = 1;
            }else{
                $is['endupload'] = 0;
            }
            if(strtotime($time['m_starttime']) <= strtotime(date('Y-m-d')) &&  strtotime($time['m_attend_endtime']) >= strtotime(date('Y-m-d'))){
                $is['open'] = 1;
            }else{
                $is['open'] = 0;
                if(strtotime($time['m_starttime']) > strtotime(date('Y-m-d'))){
                    $is['isnotstart'] = 1;
                }
            }
            if(strtotime($time['m_endtime']) < strtotime(date('Y-m-d'))){
                $is['end'] = 1;
            }else{
                $is['end'] = 0;
            }
            if(strtotime($time['m_attend_endtime']) < strtotime(date('Y-m-d'))){
                $is['signOver'] =1;
            }else{
                $is['signOver'] =0;
            }
            $this->assign('is',$is);
            $this->assign('time',$time);
            $this->assign('user',$user);
            $this->assign('subjects',$subjects);
            $this->display();
        }else{
            $this->redirect("Home/Login/index");
        }

    }
    //取消报名
    function cancel(){
        $ids = I('post.');
        M()->startTrans();
        $res = M("subject_members")->where(['id'=>$ids['id']])->save(['status'=>3]);
        $res1 = M('subject')->where(['id'=>$ids['sid']])->setDec('member_counts');
        if($res !== false && $res1 !== false){
            M()->commit();
            $this->ajaxReturn(true);
        }else{
            M()->rollback();
            $this->ajaxReturn(false);
        }
    }
    //修改个人信息
    public function setUserInfo()
    {
        $data = I("post.");
        $setUserData = new UserModel();
        $backdata =  $setUserData->setUserName($data);
        if($backdata) {
            $msg = ['State' => 1, "Msg" => "修改成功", "Data" => 1];
        }else{
            $msg = ['State' => 1, "Msg" => "修改失败", "Data" => 0];
        }
        $this->ajaxReturn($msg);
    }
    //上传作品
    function upload_file(){
        $result = upload_file('file','zuopin');
        $data['file_url'] = $result['file']['old'];
        $data['file_name'] = $result['file']['name'];
        if($result['status'] == 1){
            $this->ajaxReturn(['success'=>true,'url'=>$data['file_url'],'name'=>$data['file_name']]);
        }else{
            $this->ajaxReturn(['success'=>false,'msg'=>$result['info']]);
        }
    }
    function saveFile(){
        $data = I('post.');
        $res = M('subject_members')->save($data);
        if($res !== false){
            $this->ajaxReturn(true);
        }else{
            M()->rollback();
            $this->ajaxReturn(false);
        }
    }
}