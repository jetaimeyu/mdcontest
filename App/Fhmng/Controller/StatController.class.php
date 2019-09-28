<?php
/**
* 统计分析  已弃用
* @date: 2017-6-28 下午4:40:22
* @author: zhucy
*/
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class StatController extends BaseController{
	function index(){
		$this->display();
	}
	
	/**
	 * 获取列表数据
	 * yuhj
	 */
	public function getList(){
		$page = I("page", 1);
		$rows = I("rows", 10);  //每页显示的条数
		$type = I('post.hide_type', 0);
		$one = I('post.hide_model_one', 0);
		$two = I('post.hide_model_two', 0);
		$three = I('post.hide_model_three', 0);
		$param = I('post.searchValue');
		$stime = I('post.start_time');
		$etime = I('post.end_time');
		
		$where['1'] = '1';
		if(!empty($type)){
			$where['operate_type'] = $type;
		}
		if(!empty($param)){
			$where['url'] = array('like','%'.str_replace("http://".$_SERVER['HTTP_HOST'],'',$param).'%');
		}
		if($one == -10){
			//2017峰会
			if($two == '-70'){
				$where['controller'] = 'Summit';
				$where['action'] = 'index';
			}else if($two == '-80'){
				$where['controller'] = 'Expertmore';
				$where['action'] = array(array('eq','morelist'),array('eq','expertdetail'),'or');
			}else if($two == '-90'){
				$where['controller'] = 'News';
				$where['action'] = array(array('eq','index'),array('eq','detail'),'or');
			}else if($two == '-100'){
				$where['controller'] = 'Report';
				$where['action'] = array(array('eq','index'),array('eq','sub_view'),array('eq','sub'),array('eq','view'),'or');
			}else{
				$where['controller'] = array(array('eq','Summit'),array('eq','News'),array('eq','Report'),array('eq','Expertmore'),'or');
			}
		}else if($one == -20){
			//达人堂
			$where['controller'] = 'Expert';
			if($two != 0){
				$where['model_id'] = $two;
			}
			if($three != 0){
				$where['model_child_id'] = $three;
			}
		}else if($one == -30){
			//行业论坛
			$where['controller'] = 'Forum';
			if($two != 0){
				$where['model_id'] = $two;
			}
		}
		
		if(empty($stime) && empty($etime)){
			$total = M('stat')->where($where)->count();
			$res = M('stat')->field('stat.*,stat_type.type')->where($where)
			->join('inner join stat_type on stat.operate_type = stat_type.id')
			->order('stat.id desc')
			->page($page,$rows)
			->select();
		}else{
			if (empty($stime)){
				$where['create_time'] = array('ELT',$etime);
			}else if(empty($etime)){
				$where['create_time'] = array('EGT',$stime);
			}else{
				$where['create_time'] = array(array('EGT',$stime),array('ELT',$etime),'and');
			}
			$data = M('stat_detail')->where($where)
			->join('inner join stat on stat.id = stat_detail.stat_id')
			->join('inner join stat_type on stat.operate_type = stat_type.id')
			->group('stat_detail.stat_id')->select();
			$total = count($data);
			
			$res = M('stat_detail')->field('stat.id,controller,action,param,url,operate_type,model_id,model_child_id,model_name,count(stat_detail.id) number,stat_type.type')
			->where($where)
			->join('inner join stat on stat.id = stat_detail.stat_id')
			->join('inner join stat_type on stat.operate_type = stat_type.id')
			->group('stat_detail.stat_id')
			->order('stat.id desc')
			->page($page,$rows)
			->select();
		}
		
		foreach ($res as $key=>&$val){
			$val['controller'] = $this->getmc($val['controller']);
			$val['url1'] = $val['url'];
		}
		$result["total"] = $total;
		$result["rows"] = $res;
		$result['str']=$param;
		$this->ajaxReturn($result);
	}
	
	/**
	 * 获取名称
	 * yuhj
	 */
	function getmc($code){
		$code = strtolower($code);
		$stat = C('STAT');
		$mc = '';
		foreach ($stat as $key=>$val){
			if($code == $key){
				$mc = $val['name'];
			}
		}
		return $mc;
	}
	
	/**
	 * 获取模块
	 * yuhj
	 */
	function getmodel(){
		$id = I('get.pid');
		$data = array();
		if ($id == -10){
			$mm = M('menu');
			$data = array(
						array('id'=>'-70','text'=>'首页'),
						array('id'=>'-80','text'=>'大会嘉宾'),
						array('id'=>'-90','text'=>'峰会新闻'),
						array('id'=>'-100','text'=>'行业报告')
			);
			$all['id'] = 0;
			$all['text'] = '全部';
			array_unshift($data, $all);
		}
		if($id == -20){
			$data = M('online_cate')->field('id,cate_name as text')->where(array('pid'=>0,'is_open'=>1))->select();
			$all['id'] = 0;
			$all['text'] = '全部';
			array_unshift($data, $all);
		}
		if($id == -30){
			$data = M('forum_section')->field('id,title as text')->where(array('status'=>1))->select();
			$all['id'] = 0;
			$all['text'] = '全部';
			array_unshift($data, $all);
		}
		$this->ajaxReturn($data);
	}
	
	function getchildmodel(){
		$id = I('get.pid');
		$data = M('online_cate')->field('id,cate_name as text')->where(array('pid'=>$id,'is_open'=>1))->select();
		$all['id'] = 0;
		$all['text'] = '全部';
		array_unshift($data, $all);
		$this->ajaxReturn($data);
	}
	
	/**
	 * 获取操作
	 * yuhj
	 */
	function gettype(){
		$tm = M('stat_type');
		$data = $tm->field('id,type as text')->select();
		$all['id'] = 0;
		$all['text'] = '全部';
		array_unshift($data,$all);
		$this->ajaxReturn($data);
	}
	
	/**
	 * 获取详情
	 * yuhj
	 */
	function getDetail(){
		$data = I('post.');
        $wh['stat_id'] = $data['pid'];
        $stime = $data['start_time'];
        $etime = $data['end_time'];
        if(empty($stime) && empty($etime)){
        	$count = M('stat_detail')->where($wh)->count();
			$list = M('stat_detail')->where($wh)
			->page($data['page'],$data['rows'])
			->select();
        }else{
        	if (empty($stime)){
				$wh['create_time'] = array('ELT',$etime);
			}else if(empty($etime)){
				$wh['create_time'] = array('EGT',$stime);
			}else{
				$wh['create_time'] = array(array('EGT',$stime),array('ELT',$etime),'and');
			}
			$count = M('stat_detail')->where($wh)->count();
			$list = M('stat_detail')->where($wh)
			->page($data['page'],$data['rows'])
			->select();
        }
		$this->ajaxReturn(['rows'=>$list,'total'=>$count]);
	}
}