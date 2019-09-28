<?php
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class CouponController extends BaseController{
	public function index(){
		$this->display();
	}
	
	/**
	 * 获取特色邀请函数据列表
	 * yuhj
	 */
	function getList(){
		$page = I("post.page", 1);
		$rows = I("post.rows", 10);  //每页显示的条数
		$param = I('post.searchValue');
		$stime = I('post.start_time');
		$etime = I('post.end_time');
		$forum = I('post.forum',0);
	
		$where['project'] = '1803';
		if(!empty($stime) && !empty($etime)){
			$where['addtime'] = array(array('EGT',$stime),array('ELT',$etime),'and');
		}else if(!empty($stime)){
			$where['addtime'] = array('EGT',$stime);
		}else if(!empty($etime)){
			$where['addtime'] = array('ELT',$etime);
		}
	
		if (!empty($param)){
			$where['name'] = array('like','%'.$param.'%');
		}
	
		$im = M('invitation');
	
		$total=$im->where($where)->count();
	
		$res = $im->field("id,IFNULL(name,'') name,IFNULL(field,'') field,IFNULL(number,'') number,addtime,imgurl")->where($where)
		->page($page,$rows)
		->order('addtime desc')->select();
	
		$result["total"] = $total;
		$result["rows"] = $res;
		$result['str']=$param;
		$this->ajaxReturn($result);
	}
	
	/**
	 * 导出数据
	 * yuhj
	 */
	function exportData(){
		$param = I('get.searchValue');
		$stime = I('get.start_time');
		$etime = I('get.end_time');
	
		$where['project'] = '1803';
		if(!empty($stime) && !empty($etime)){
			$where['addtime'] = array(array('EGT',$stime),array('ELT',$etime),'and');
		}else if(!empty($stime)){
			$where['addtime'] = array('EGT',$stime);
		}else if(!empty($etime)){
			$where['addtime'] = array('ELT',$etime);
		}
	
		if (!empty($param)){
			$where['name'] = array('like','%'.$param.'%');
		}
	
		$im = M('invitation');
	
		//$total=$im->where($where)->count();
	
		$content_list = $im->field("IFNULL(name,'') name,field,IFNULL(number,'') number,addtime")->where($where)
		->order('addtime desc')->select();
	
		$title = array();
		array_push($title, "姓名");
		array_push($title, "手机号");
		array_push($title, "锁号");
		array_push($title, "创建时间");
		export_csv('优惠卷',$title,$content_list);
	}
	
}