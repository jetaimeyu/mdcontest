<?php
/**
 * Created by PhpStorm.
 * User: yuyuyu
 * Date: 2018/8/6 0006
 * Time: 上午 10:20
 */
namespace Mobile\controller;

use Home\Model\HomeindexModel;
use Think\Controller;
use Think\Model;
use Think\Verify;

class IndexController extends Controller
{
    public $verifyCode;
    private $indexData;
    public function __construct()
    {
        parent::__construct();
        $this->indexData = new HomeindexModel();

    }
    /**
     * 报名首页
     */
    public function index()
    {
        $mobilePrize = $this->indexData->getMobilePrize();
        $subject = $this->indexData->getCompetitionTopic();
        $gameDetail = $this->indexData->getGameDetail();
        $scheduleData = $this->indexData->getSchedule();
        $time = M('sys_config')->where(['config_code'=>['in','m_starttime,m_attend_endtime']])->getField('config_code,config_value');
        if (strtotime(date('Y-m-d')) <= strtotime($time['m_attend_endtime']) && strtotime($time['m_starttime']) <= strtotime(date('Y-m-d'))){
            $isEnd = 0;
        }else{
            $isEnd = 1;
        }
        $this->assign('mobilePrize', $mobilePrize);
        $this->assign('isEnd',$isEnd);
        $this->assign('scheduleData',$scheduleData);
        $this->assign('gameDetail',$gameDetail);
        $this->assign('subject',$subject);
        $this->assign('title','会议介绍');
        $this->display();
    }
    /**
     * 题目详情
     */
    public function detail()
    {
        $id =I('get.id',0,intval);
        $content = M('subject_content')->where(['sid'=>$id,'status'=>1])->select();

        $this->assign('content',$content);
        $this->assign('title','我要参赛 ');

        $this->display();

    }

    /**
     * 报名提交页面
     */
    public function submit()
    {
        //报名按钮开关
        $time = M('sys_config')->where(['config_code'=>['in','m_attend_endtime,m_starttime']])->getField('config_code,config_value');
        if(strtotime($time['m_starttime']) <= strtotime(date('Y-m-d')) &&  strtotime($time['m_attend_endtime']) >= strtotime(date('Y-m-d'))){
            $isopen = 1;
        }else{
            $isopen = 0;
        }
        if(!$isopen){
            $this->error('不处于报名阶段','',1);
        }
        $subject = M('subject')->where(['status' => 1])->order('order_no')->select();
        //用户协议内容
        $agreement =  M('content')->where(['content_code'=>'yhxy','status'=>1])->getField('content');
        $this->assign('agreement', $agreement);
        $this->assign('subject', $subject);
        $this->assign('title','赛题报名');

        $this->display();
    }

    /**
     * 地址联动
     */
    public function addressSelect()
    {
        $code = intval(I('post.code'));
        $result = M('dictionary')->where(['pid_key'=>$code, 'type_code'=>'government_area', 'status'=>1])->select();

        $this->ajaxReturn($result);

    }

    /**
     * 验证手机号是否被注册
     */
    public function isPhoneRegister()
    {
        $phoneNumber = I('post.phoneNumber');
        $sid = I('post.sid');
        $info = M('subject_members')->where(['tel' => $phoneNumber, 'status' => 1])->select();
        if (count($info) == 2){
            $result = [];
            $result['status'] = 1;
            $result['title'] = "您已报名两个题目，无法再次报名";
            $this->ajaxReturn($result);
        }
        if (count($info) == 1 && in_array($info[0]['sid'], $sid) ){
            $subject = M('subject')->where(['status' => 1, 'id' => $info[0]['sid']])->field('title')->select();
            $registerName = $subject[0]['title'];
            $result['status'] = 1;
            $result['title'] = "您已报名".$registerName."无法再次报名";
            $this->ajaxReturn($result);
        }
        $result = [];
        $result['status'] = 0;
        $this->ajaxReturn($result);
    }

    /**
     * 验证身份证号是否被报名
     */
    public function isCardRegister($cardNumber,$sid)
    {
        foreach ($sid as $value){
            $info = M('subject_members')->where(['ID_num' => $cardNumber, 'status' => 1, 'sid' => $value])->select();
            if($info){
                $subject = M('subject')->where(['id' => $value])->field('title')->select();
                $title = $subject[0]['title'];
                $errorMessage = '该身份证号已报名'.$title;
                return $errorMessage;
            }
        }
        return '';
    }

    /**
     * 图片验证码校验
     * @param $code
     * @param string $id
     */
    public function check_verify($code='',$id = ""){
        $code = I('post.pictureVerify');
        $verify = new Verify();
        $res = $verify->check($code,$id);
        $this->ajaxReturn($res,'json');
    }

    /**
     * 获取图片验证码
     */
    public function verify()
    {
        $config = array(
            "expire"=>300,
            "length" =>4,
            'imageW'=>210,
            'imageH'=>60,
            'useImgBg'=>false,
            'fontttf' => '4.ttf',
            'fontSize'=>30,
            'useCurve'=>true,
            'useNoise'=>false
        );
        $Verify = new Verify($config);
        $Verify->entry();
    }

    //检查手机号验证码，已经报名一次时，直接进入成功
    //status: 0 错误（验证码不对；不能报名），1 正确，可继续报名（针对没有报名的情况），2 正确，直接成功（针对第二次报名的情况）
    public  function checkPhoneVerify()
    {
        $phoneNumber = I('post.phoneNumber');
        $verifyCode = I('post.verifyCode');
        $sid = I('post.sid');
        if(session('phoneNumber') != $phoneNumber ){
            $errorMessage = "接受验证码的手机号与输入手机号不一致";
        }
        if (session('verifyCode') !=  $verifyCode){
            $errorMessage = '验证码错误';
        }
        if ($errorMessage){
            $result = [];
            $result['status'] = 1;
            $result['error'] = $errorMessage;
            $this->ajaxReturn($result);
        }
        $info = M('subject_members')->where(['tel' => $phoneNumber, 'status' => 1])->select();
        if(count($info) == 2){
            $result = [];
            $result['status'] =1;
            $result['error'] = "您已报名两个题目，无法再次报名";
            $this->ajaxReturn($result);
        }
        if (count($info) == 1 && in_array($info[0]['sid'], $sid) ){
            $subject = M('subject')->where(['status' => 1, 'id' => $info[0]['sid']])->field('title')->select();
            $registerName = $subject[0]['title'];
            $result['status'] = 1;
            $result['error'] = "您已报名".$registerName."无法再次报名";
            $this->ajaxReturn($result);
        }
        if (count($info) == 1 && !in_array($info[0]['sid'], $sid)){
            $data = $info[0];
            $saveData['uid']= $data['uid'];
            $saveData['sid']= $sid[0];
            $saveData['name']= $data['name'];
            $saveData['email']= $data['email'];
            $saveData['tel']= $data['tel'];
            $saveData['ID_num']= $data['ID_num'];
            $saveData['province']= $data['province'];
            $saveData['city']= $data['city'];
            $saveData['company']= $data['company'];
            $saveData['school']= $data['school'];
            $saveData['status']= 1;
            $saveData['type']= 2;
            $saveData['create_time']= date("Y-m-d H:i:s", time());
            M()->startTrans();
            $res = M('subject_members')->add($saveData);
            $res2 = M('subject')->where(['id' =>$sid[0]])->setInc('member_counts');
            if ($res && $res2){
                M()->commit();
                //报名成功发送短信
                sendSmsForBM($data['tel']);
                $result = [];
                $result['status'] = 2;
                $result['message'] = "报名成功";
                $this->ajaxReturn($result);
            }else{
                M()->rollback();
                $result = [];
                $result['status'] =1;
                $result['error'] = "数据保存失败";
            }
        }
        $result = [];
        $result['status'] = 0;
        $this->ajaxReturn($result);

//        $info = M('subject_members')->where(['tel' => $phoneNumber, 'status' => 1])->select();

//        if($info && count($info)==1){
//            //如果有报名记录，但是又同时报名两个题目，则直接报错
//            if (count($sid)==2) {
//                $result['status'] = 0;
//                $result['error'] = '您已经报名，不能进行下一步';
//                $this->ajaxReturn($result);
//                return;
//            }
//            //此处可以再次判断 需要报名的题目类型 和 已经报名的是不是一样，如果是的话报错
//
//            $datas =$info[0];
//            $datas['id']="";
//            $datas['sid']=$sid[0];
//
//            M()->startTrans();
//            $res = M('subject_members')->add($datas);
//            $res2 = M('subject')->where(['id' =>$sid[0]])->setInc('member_counts');
//            if ($res && $res2){
//                //成功
//            }else{
//                M()->rollback();
//                $result['status'] = 0;
//                $result['error'] = '数据保存失败';
//                $this->ajaxReturn($result);
//            }
//            M()->commit();
//
//            $result['status'] = 2;
//            $result['data'] = "第二次报名时，自动报名成功";
//            $this->ajaxReturn($result);
//        }
//        else if ($info&&  count($info)>1){
//            //已经报名两个，报错
//            $result['status'] = 0;
//            $result['error'] = '您已经报名，不能进行下一步';
//            $this->ajaxReturn($result);
//        }
//        else{
//            //未进行报名，可进行下一步的报名信息填报
//            $result['status'] = 1;
//            $result['data'] = '未报名，可进行下一步';
//            $this->ajaxReturn($result);
//        }
    }

    /**
     * 验证提交数据
     */
    public function getSubmit()
    {
        $isNew =0;
        $data = I('post.');
        $sid = $data['sid'];
        // if(session('phoneNumber') != $data['phoneNumber']){
        //     $errorMessage = "接受验证码的手机号与输入手机号不一致";
        // }
        // if (session('verifyCode') != $data['verifyCode']){
        //     $errorMessage = '验证码错误';
        // }
//        if (!$errorMessage || $errorMessage == ""){
            $errorMessage = $this->isCardRegister($data['cardNumber'],$sid);
//        }
        $userInfo = M('user')->where(['telephone'=>$data['phoneNumber']])->find();
        if (!$userInfo){
            $userAdd = M('user');
            $user['true_name'] =  $data['username'];
            $user['telephone'] =  $data['phoneNumber'];
            $user['pwd'] =  md5($data['phoneNumber']);
            $user['create_time'] =  date('Y-m-d H:i:s', time());
            $user['is_user'] =  2;
            $userId = $userAdd->add($user);
            $registerMd = checkPhoneToMD($data['phoneNumber']);
            if ($registerMd['State'] == 2){
                $isNew =1;
            }
        }else{
            foreach ($sid as $value){
                $info = M('subject_members')->where(['tel' => $data['phoneNumber'], 'status' => 1, 'sid' => $value])->select();
                if($info){
                    $subject = M('subject')->where(['id' => $value])->field('title')->select();
                    $title = $subject[0]['title'];
                    $errorMessage = '该手机号已报名题目'.$title;
                }
            }
            $userId = $userInfo['id'];
        }
        if(!$errorMessage && $userId){
            M()->startTrans();
            foreach ($sid as $value) {
                $subject = M('subject_members');
                $datas['uid'] = $userId;
                $datas['sid'] = $value;
                $datas['name'] = $data['username'];
                $datas['email'] = $data['email'];
                $datas['tel'] = $data['phoneNumber'];
                $datas['ID_num'] = $data['cardNumber'];
                $datas['province'] = $data['provinceText'];
                $datas['city'] = $data['cityText'];
                $datas['company'] = $data['workPlace'];
                $datas['school'] = $data['school'];
                $datas['type'] =2;
                $datas['create_time'] = date('Y-m-d H:i:s', time());
                $datas['status'] = 1;

                $res = $subject->add($datas);
                $subjectRecord = M('subject');
                $res2 = $subjectRecord->where(['id' =>$value])->setInc('member_counts');
                if ($res && $res2){
                    continue;
                }else{
                    M()->rollback();
                    $result['status'] = 0;
                    $result['error'] = '数据保存失败';
                    $this->ajaxReturn($result);
                }
            }
            M()->commit();
            //报名成功发送短信
            sendSmsForBM($data['phoneNumber']);
            $result['status'] = 1;
            $result['error'] = '';
            $result['isNew'] = $isNew;
            $this->ajaxReturn($result);
        }else{
            $result['status'] = 0;
            $result['error'] = $errorMessage;
            $this->ajaxReturn($result);
        }
    }

    /**
     * 发送验证码
     */
    public function sendVerify()
    {
        $phoneNumber = I('post.phoneNumber');
        $result = sendVerify($phoneNumber);
//        $result = ['State'=>2, 'Data'=>111111];
        if($result['State'] == 2){
            session('verifyCode',$result['Data']);
            session('phoneNumber', $phoneNumber);
        }
        $this->ajaxReturn($result);

    }

    /**
     * 报名成功页面
     */
    public function submitSuccess()
    {
        $this->assign('title','报名成功页面');
        $this->display();

    }
}
