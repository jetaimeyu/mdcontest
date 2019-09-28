<?php
namespace Fhmng\Model;

use Think\Model;
class HeaderRelationModel extends Model {
	protected $tableName = 'header_relation';
	public $orderFiled = array(
			array('id'=>'A','field_name'=>'支付金额','param_name'=>'v_amount','is_note'=>'1','is_basic'=>1),
			array('id'=>'C','field_name'=>'订单金额','param_name'=>'total_price','is_note'=>'1','is_basic'=>1),
			array('id'=>'B','field_name'=>'缴费情况','param_name'=>'is_pay','is_note'=>'1','is_basic'=>1),
			array('id'=>'D','field_name'=>'创建时间','param_name'=>'create_time','is_note'=>'1','is_basic'=>1),
	);
	public $memberFiled = array(
			array('id'=>'A','field_name'=>'金额','param_name'=>'total_price','is_note'=>'1','table'=>'signup_member','is_basic'=>1),
			array('id'=>'C','field_name'=>'缴费情况','param_name'=>'is_pay','is_note'=>'1','table'=>'signup_member','is_basic'=>1),
			array('id'=>'D','field_name'=>'发票状态','param_name'=>'is_invoice','is_note'=>'1','table'=>'signup_member','is_basic'=>1),
			array('id'=>'F','field_name'=>'报名方式 ','param_name'=>'sign_mode','is_note'=>'1','table'=>'signup_member','is_basic'=>1),
			array('id'=>'B','field_name'=>'创建时间','param_name'=>'create_time','is_note'=>'1','table'=>'signup_member','is_basic'=>1),
			array('id'=>'E','field_name'=>'是否报到','param_name'=>'sign','is_note'=>'1','table'=>'signup_member','is_basic'=>1),
	);
	/**
	 * 给每个用户的订单关联表头的内容
	 * @param mid 所属会议id
	 * @param adminid 所属管理员id
	 */
	public function insertOrder($mid,$adminid){
		$model = M('header_relation');
		$list = $model->where(array('mid'=>$mid,'adminid'=>$adminid,'keyword'=>'orders'))->find();
		//当该用户已经有相关会议的内容
		if(empty($list)){
			$arr['mid'] = $mid;
			$arr['adminid'] = $adminid;
			$arr['name'] = '订单自定义列表';
			$arr['keyword'] = 'orders';
			
			$where['form_model.mid'] = $mid;
			$where['form_field.is_show'] = 1;
			$where['form_field.is_show'] = 1;
			$where['form_field.status'] = 1;
			$where['form_model.status'] = 1;
			$where['form_model.`key`'] = array('between',array('company','invoice'));
			$fields = M('form_field')->field('form_field.id,form_field.field_name,form_field.param_name')->where($where)->join('left join form_model on form_model.id=form_field.fid')->select();
			$fields = array_merge($fields,$this->orderFiled);
			$arr['fileds'] = json_encode($fields);
			$arr['all_fileds'] = json_encode($fields);
			$arr['create_time'] = date("Y-m-d H:i:s");
			$model->add($arr);
		}
	}
	
	/**
	 * 给每个订单的人员列表关联表头的内容
	 * @param mid 所属会议id
	 * @param adminid 所属管理员id
	 */
	public function insertMember($mid,$adminid){
		$model = M('header_relation');
		$list = $model->where(array('mid'=>$mid,'adminid'=>$adminid,'keyword'=>'member'))->find();
		//当该用户已经有相关会议的内容
		if(empty($list)){
			$arr['mid'] = $mid;
			$arr['adminid'] = $adminid;
			$arr['name'] = '人员管理自定义列表';
			$arr['keyword'] = 'member';
			$where['form_model.mid'] = $mid;
			$where['form_field.is_show'] = 1;
			$where['form_field.is_show'] = 1;
			$where['form_field.status'] = 1;
			$where['form_model.status'] = 1;
			$where['form_model.`key`'] = 'basic';
			$fields = M('form_field')->field('form_field.id,form_field.field_name,form_field.param_name')->where($where)->join('left join form_model on form_model.id=form_field.fid')->select();
			$fields = array_merge($fields,$this->memberFiled);
			$arr['fileds'] = json_encode($fields);
			$arr['all_fileds'] = json_encode($fields);
			$arr['create_time'] = date("Y-m-d H:i:s");
			$model->add($arr);
		}
	}
}