<?php
// +----------------------------------------------------------------------
// | Author: liux-q <liu-q@glodon.com>
// | Description: 解忧市场版块管理
// +----------------------------------------------------------------------

namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;

class SectionController extends BaseController
{
	/**
	 * 版块列表  
	 * */
	public function index()
	{
		$this->checkPageAuth('FORUM_SECTION');
		$this->display();
	}

	/*列表*/
	public function getSectionlist()
	{
		$config = ['table'=>'forum_section','where'=>['neq_forum_section.status'=>3],
		'field'=>'forum_section.*'
		];
		$list = $this->get_datas($config);
		$section_userObj = M('section_user');

		foreach ($list['rows'] as $key => &$value) {
			$where['seid'] = $value['id'];
			$where['user.status'] = ['neq',3];
			$users = $section_userObj->where($where)->join('LEFT JOIN user ON section_user.uid = user.id')->field('id,true_name')->select();
			if($users){
				$tmp = '';
				foreach ($users as $key2 => $value2) {
					$tmp .= $value2['true_name'].'，';
				}
				$value['users'] = trim($tmp,'，');
			}
		}
		$this->ajaxReturn($list);
	}
	//管理员列表
	public function getadminlist()
	{
		$queryStr = I('post.queryStr');
		if($queryStr){
			$condition['login_name'] = array('like','%'."\\$queryStr".'%');
			$condition['true_name'] = array('like','%'."\\$queryStr".'%');
			$condition['_logic'] = 'OR';
			$where['_complex'] = $condition;
		}
		
        //页码
        $page = I("page");
        //每页显示的条数
        $rows = I("rows");
     	// $where['id'] = ['neq',1];
     	$where['is_user'] = 1;
     	$where['status'] = ['neq',3];
        $userObj  = M("user");
        $total = $userObj->where($where)->count();
        $res = $userObj->order("create_time desc")->where($where)->page($page,$rows)->select();
        $seid = I('get.seid');
        if($seid){
        	$adminusers = array_keys(M('section_user')->where(['seid'=>$seid])->getField('uid,seid'));
        	foreach ($res as $key => $value) {
        		if(in_array($value['id'],$adminusers)){
        			$res[$key]['checked'] = true;
        		}
        	}
    	}
        $result["total"] = $total;
        $result["rows"] = $res;
        $this->ajaxReturn($result);
	}
	//获取用户信息
	public function getuserinfo()
	{
		$str = trim(I('post.uidStr'),',');
		$where['id'] = ['IN',$str];
		$userObj = M('user');
		$res = $userObj->where($where)->order('create_time desc')->field('id,true_name')->select();
		$this->ajaxReturn($res);
	}

	/*版块编辑*/
	public function edit()
	{
		if(IS_GET){
			$id = I('get.id');
			if($id){
				$data = M('forum_section')->find($id);
				$user = array_keys(M('section_user')->where(['seid'=>$id])->getField('uid,seid'));
				if($user){
					$userinfo = M('user')->where(['id'=>['IN',$user]])->field('id,true_name')->select();
					foreach ($userinfo as $key => $value) {
						$data['adminlist'] .= $value['true_name'].'，';
						$data['users'] .= $value['id'].',';
					}
					$data['adminlist'] = trim($data['adminlist'],'，');
				}
				$this->assign('data',$data);
			}
			$this->display();
		}else if(IS_POST){
			$data = I('post.');
			$data['create_time'] = date('Y-m-d H:i:s');
			// $forum_sectionObj = M('forum_section');
			$seid = '';
			$res = $this->saveRow('forum_section',$data,function ($id) use(&$seid)
			{
				$seid = $id;
			});
			$users =array_filter(explode(',' ,$data['users']));
			//设置版块管理员
			$section_user = [];
			$seid = $data['id']?:$seid;
			foreach ($users as $key => $value) {
				$tmp['seid'] = $seid;
				$tmp['uid'] = $value;
				$section_user[] = $tmp;
				unset($tmp);
			}
			$section_userObj = M('section_user');
			$section_userObj->where(['seid'=>$seid])->delete();
			if($section_user){
				$section_userObj->addAll($section_user);
			}
			$this->ajaxReturn($res);
		}
	}

	/*禁用 启用 删除*/
	public function enableOrdisable()
	{
		    $post = I('post.');
            $value = $post['status'];
            $table = $post['table'];
            $where['id'] = ['IN',$post['ids']];
            $res = $this->updateField($table,$where,'status',$value);
            $this->ajaxReturn($res);
	}
	 /**
     * 设置角色人员页面
     * */
    public function setadmin(){
    	$seid =I('get.seid');
    	$uid = M('section_user')->where(['seid'=>$seid])->getField('uid,seid');
    	$uid = array_keys($uid);
    	$uid = implode(',',$uid);
    	$this->assign('uid',$uid);
        //检查权限
        $this->display();
    }
    public function adver()
    {
    	$this->display();
    }

    public function getadver()
    {
    	$uid = $this->session_user['id'];
		$user = M('user')->find($uid);
		$config = ['table'=>'adver','where'=>['adver.status'=>['neq',3],'type'=>4],
		'join'=>'LEFT JOIN forum_section s ON adver.pid = s.id',
		'field'=>'adver.*,s.title as section','order'=>'adver.create_time desc'
		];
		if($user['is_admin'] != 1){
			$seid = array_keys(M('section_user')->where(['uid'=>$uid])->getField('seid,uid'));

			if($seid){
				$config['where']['adver.pid'] = ['IN',$seid];
			}else{
				$config['where']['adver.pid'] = ['IN','0'];
			}
		}
		$post = I('post.');
		if(isset($post['status'])){
			$post['adver.status'] = $post['status'];
			unset($post['status']);
		}		
		if($post['like_title']){
			$post['like_adver.title'] = $post['like_title'];
			unset($post['like_title']);
		}
		$list = $this->get_datas($config,$post);
		$this->ajaxReturn($list);
    }
    public function editadver()
    {
    	if(IS_GET){
    		$id  = I('get.id');
    		if($id){
    			$data = M('adver')->find($id);
    			$this->assign('data',$data);
    		}
    		$uid = $this->session_user['id'];
			$user = M('user')->find($uid);
			if($user['is_admin'] != 1){
				$seid = array_keys(M('section_user')->where(['uid'=>$uid])->getField('seid,uid'));
				if($seid){
					$where['id'] = ['IN',$seid];

				}else{
					$where['id'] = ['IN','0'];
				}
			}
			$where['status'] = 1;
			$section = M('forum_section')->where($where)->select();
			$this->assign('section',$section);
			$this->display();
    	}else{
    		$post = I('post.');
    		if(!$post['id']){
    			$post['create_time'] = date('Y-m-d H:i:s');
    		}
    		$post['type'] = 4;
    		$res = $this->saveRow('adver',$post);
    		$this->ajaxReturn($res);
    	}
    }
    public function getSection()
	{
		$uid = $this->session_user['id'];
		$user = M('user')->find($uid);
		if($user['is_admin'] != 1){
			$seid = array_keys(M('section_user')->where(['uid'=>$uid])->getField('seid,uid'));
			if($seid){
				$where['id'] = ['IN',$seid];

			}else{
				$where['id'] = ['IN','0'];
			}
		}
		$where['status'] = 1;
		$list = M('forum_section')->where($where)->field('title as text,id as value')->select();
		$all['text'] = '全部';
		$all['value'] = '';
		array_unshift($list, $all);
		$this->ajaxReturn($list);
	}
	public function uploadImg(){
     	//原图片地址
        $res = upload_img('Filedata','section_ad',$size=array(),$thumb=array());
        $this->ajaxReturn($res);
        
    }

    public function deladver()
    {
    	$id = I('post.id');
    	$res = M('adver')->where(['id'=>['IN',$id]])->setField('status',3);
    	if($res !== false){
    		$this->ajaxReturn(['success'=>true]);
    	}else{
    		$this->ajaxReturn(['success'=>false]);
    	}
    }
}

