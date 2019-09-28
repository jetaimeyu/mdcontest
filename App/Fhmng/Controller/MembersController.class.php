<?php
/**日志
 * 模块：报名管理
 * 创建人:刘佳
 * 创建时间：2018-08-06
 * 模块权限code:MEMBERS

 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class MembersController extends BaseController{
    /**
     * 首页
     * */
    public function index(){
        $this->checkPageAuth("MEMBERS");
        $this->display();
    }
    
    /**
     * 获取数据
     * */
    public function getData(){
        $search = I('post.');
        $field = $search['queryField'];
        if($search['queryStr']){
            if($field == 'tel'){
                $wh['tel'] = $search['queryStr'];
            }else if($field == 'name'){
                $wh['name'] = ['like','%'.$search['queryStr'].'%'];
            }
        }
        if($search['sid']){
            $wh['sid'] = $search['sid'];
        }
        $wh['subject_members.status'] = ['neq',3];
        $data = M('subject_members')
        ->join('left join subject on subject.id = subject_members.sid')
        ->field('subject_members.*,subject.title')
        ->where($wh)->order('id')->page($search['page'],$search['rows'])->select();
        
        $count = M('subject_members')->where($wh)->count();
        $return = ['rows'=>$data,'total'=>$count];
        
        $this->ajaxReturn($return);
    }
    function viewData(){
        $id = I('get.id',0,intval);
        $data = M('subject_members')
        ->join('left join subject on subject.id = subject_members.sid')
        ->field('subject_members.*,subject.title')
        ->where(['subject_members.id'=>$id])->find();
        $this->assign('data',$data);
        $this->display();
    }
   function getSubjects(){
       $list = M("subject")->field('title as text , id as value')->where('status = 1')->select();
       array_unshift($list,['value'=>'','text'=>'全部']);
       $this->ajaxReturn($list);
   }
    /**
     *  删除
     * */
    public function del(){
        $result["success"] = false;
        $result["message"]="";
        if(IS_POST){
            //用户ids字符串,逗号隔开
            $ids = I("ids");
            $where["id"] = array("in",$ids);
            M()->startTrans();
            //做假删除，status为3的是删除的
            $res = M("subject_members")->where($where)->save(['status'=>3]);
            $subjectIdArr = M("subject_members")->where($where)->getField('sid',true);
            $res1 = M('subject')->where(['id'=>['in',$subjectIdArr]])->setDec('member_counts');
            if($res !== false && $res1 !== false){
                M()->commit();
                $result["success"] = true;
                $result["message"]="操作成功";
            }else{
                M()->rollback();
                $result["success"] = false;
                $result["message"]="操作失败";
            }
        }
        $this->ajaxReturn($result);
    }
    //导出excel
    function export(){
        $search = I('get.');
        $field = $search['queryField'];
        if($search['queryStr']){
            if($field == 'tel'){
                $wh['tel'] = $search['queryStr'];
            }else if($field == 'name'){
                $wh['name'] = ['like','%'.$search['queryStr'].'%'];
            }
        }
        if($search['sid']){
            $wh['sid'] = $search['sid'];
        }
        $wh['sm.status'] = ['neq',3];
        $list = M("subject_members")
        ->alias('sm')
        ->where($wh)
        ->join('left join subject s on s.id = sm.sid')
        ->field('s.title,sm.name,sm.tel,sm.ID_num,sm.province,sm.city,sm.email,sm.company,sm.school,sm.file_name,sm.file_url,sm.file_description,sm.create_time')
        ->order('sm.id')
        ->select();
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

        foreach($list as &$v){
            if($v['file_url'])
            $v['file_url'] = $http_type.$_SERVER['HTTP_HOST'].'/Uploads'.$v['file_url'];
        }
        set_time_limit(-1);
        @ini_set('memory_limit','512M');
        $header = ['报名题目','姓名','手机号','身份证号','省份','城市','邮箱','工作单位','毕业学校','作品名称','作品链接','作品描述','创建时间','作品评级'];

        $this->exportExcel($header,$list,'报名人员');
    }
     /**
 * 导出excel
 * @author 刘佳
 * @param string $title
 * @param array $data
 * @param string $fileName
 * @param string $savePath
 * @param boolean $isDown
 */
function exportExcel($title=array(), $data=array(), $fileName=''){
	vendor('PHPExcel.PHPExcel');
	$obj = new \PHPExcel();
	//横向单元格标识
	$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
	
    $objActSheet = $obj->getActiveSheet();
    $obj->getActiveSheet(0)->setTitle('sheet1');   //设置sheet名称
	$_row = 1;   //设置纵向单元格标识
	if($title){
		$_cnt = count($title);
		$i = 0;
		foreach($title AS $v){   //设置列标题
			$obj->setActiveSheetIndex(0)->setCellValue($cellName[$i].$_row, $v);
			$i++;
		}
		$_row++;
    }
    $titleArr = M('subject')->where(['status'=>1])->getField('title',true);
	//填写数据
	if($data){
		$i = 0;
		foreach($data AS $_v){
			$j = 0;
			foreach($_v AS $_key =>$_cell){
                $obj->getActiveSheet(0)->setCellValueExplicit($cellName[$j] . ($i+$_row), $_cell, \PHPExcel_Cell_DataType::TYPE_STRING);
                if($_key == 'title'){
                    if($titleArr){
                    $str_list =  implode($titleArr, ','); 
                    $colum = $cellName[$j];
                    $row =  $i+$_row; 
                    //设置成序列下拉格式单元格
                    $objaaa = $objActSheet->getCell("$colum"."$row")->getDataValidation();
                    $objaaa-> setType( \PHPExcel_Cell_DataValidation::TYPE_LIST )   
                        ->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION )   
                        ->setAllowBlank(true)   
                        ->setShowInputMessage(true)  
                        ->setShowErrorMessage(true)   
                        ->setShowDropDown(true)  
                        ->setErrorTitle('输入的值有误')   
                        ->setError('您输入的值不在下拉框列表内.')  
                        ->setPromptTitle('下拉选择框')  
                        ->setPrompt('请从下拉框中选择您需要的值！');
                    $objaaa->setFormula1('"' . $str_list . '"');   
                    }
                }
				$j++;
            }
            $i++;
            
        }
	}
	//文件名处理
	if(!$fileName){
        $fileName = date("YmdHi");
    }else{
        $fileName .= date("YmdHi");
    }
 
	$objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
	//网页下载
    header('pragma:public');
    header("Content-Disposition:attachment;filename=$fileName.xls");
    $objWrite->save('php://output');
    exit;
	
}
    //导出作品压缩包
    function exportFile(){
        $search = I('get.');
        $field = $search['queryField'];
        if($search['queryStr']){
            if($field == 'tel'){
                $wh['tel'] = $search['queryStr'];
            }else if($field == 'name'){
                $wh['name'] = ['like','%'.$search['queryStr'].'%'];
            }
        }
        if($search['sid']){
            $wh['sid'] = $search['sid'];
        }
        $wh['status'] = ['neq',3];
        $wh['file_url'] = ['neq',''];
        $files = M("subject_members")->where($wh)->getField('id,name,tel,file_url');
        $arr = [];
        foreach($files as $k => $v){
            unset($temp);
            if($v['file_url']){
                $temp['url'] = './Uploads'.$v['file_url'];
                $ext = substr($v['file_url'],strrpos($v['file_url'],'.')+1);
                $temp['name'] = $ext ?$v['name'].'_'.$v['tel'].'.'.$ext:'';
                $arr[]  = $temp;
            }
        }
      
        export_zip('作品包',$arr);
    }
}