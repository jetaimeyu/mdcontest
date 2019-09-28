<?php
/**日志
 * 模块：作品评审管理
 * 创建人:刘佳
 * 创建时间：2018-08-14
 * 模块权限code:WORKS

 *
 * */
namespace Fhmng\Controller;
use Fhmng\Common\Controller\BaseController;
class WorksController extends BaseController{
    /**
     * 作品评审首页
     * */
    public function index(){
        $this->checkPageAuth("WORKS");
        $this->display();
    }
    /**
     * 获取评审数据
     * */
    public function getReviewData(){
        $search = I('post.');
        $page = $search['page'];
        $rows = $search['rows'];
       
        $map['sm.name'] = ['like','%'.$search['like_title'].'%'];
        if($search['status'] != '-1' && $search['status']){
            $map['r.status'] = $search['status'];
        }
        $map['s.status'] = 1;
        $map['sm.status']= 1;
        $count = m("subject")->alias("s")
            ->join("subject_members as sm ON s.id = sm.sid")
            ->join("review as r ON sm.id = r.sm_id")
            ->where($map)
            ->count();
            
        $data = m("subject")->alias("s")
            ->join("subject_members as sm ON s.id = sm.sid")
            ->join("review as r ON sm.id = r.sm_id")
            ->where($map)
            ->field('r.id,sm.name,sm.file_name,sm.tel,s.title,r.status,(case when r.level = 1 then "一等奖" when r.level=2 then "二等奖" when r.level=3 then "三等奖" when r.level=4 then "优胜奖" else "未获奖" end) levelname')
            ->order("r.level,r.status")
            ->page($page,$rows)
            ->select();
        $return = ['rows'=>$data,'total'=>$count];
        $this->ajaxReturn($return);
    }


    /**
     * 编辑评审页面
     * */
    public function editReview(){
        if(IS_AJAX){
            $editData = I("post.");
            $data = ['id'=>$editData['id'],'level'=>$editData['level'], 'img'=>$editData['img']];
            $res = m("review")->save($data);
            if($res === false){
                $return = ['message'=>'修改失败','success'=>false];
            }else{
                $return = ['message'=>'修改成功','success'=>true, 'data'=>$editData];
            }
            $this->ajaxReturn($return);

        }else{
            $id = I("id");
            $map['s.status'] = 1;
            $map['sm.status']= 1;
            $map['r.id']=$id;
            $data = m("subject")->alias("s")
                ->join("subject_members as sm ON s.id = sm.sid")
                ->join("review as r ON sm.id = r.sm_id")
                ->where($map)
                ->field('r.id,sm.name,sm.tel,s.title,r.level,r.img')
                ->order("r.level")
                ->find();
            $this->assign("data",$data);
            $this->display();
        }
    }

    /**
     *  删除
     * */
    public function delReview(){
        if(IS_POST){
            //ids字符串,逗号隔开
            $ids = I("ids");
            $map['id'] = array('in',$ids);
            $res = m("review")->where($map)->delete();
            if($res!==false){
                $result["success"] = true;
                $result["message"]="操作成功";
            }else{
                $result["success"] = false;
                $result["message"]="操作失败";
            }
            $this->ajaxReturn($result);
        }

    }
    /**
     * 启用和禁用评审结果
     */
   public function setReviewStatus(){
       if(IS_POST){
           //ids字符串,逗号隔开
           $ids = I("post.ids");
           $status = I("post.status");
           $map['id'] = array('in',$ids);
           $res = m("review")->where($map)->setField("status",$status);
           if($res!==false){
               $result["success"] = true;
               $result["message"]="操作成功";
           }else{
               $result["success"] = false;
               $result["message"]="操作失败";
           }
           $this->ajaxReturn($result);
       }
   }
    /**
     * 导入评审数据
     */
    public function importReviewExcel(){
        $result = upload_file('excel','temp');
        if($result['status'] ==1){
            $excelPath = $result['file']['old'];
            $fieldName = array('subjectname','name','tel','idcard','provice','city','email','workUnit','school','workname','uploadurl','workdetail','addtime','level');//字段名称
            $data = $this->implodeexcel('./Uploads'.$excelPath,0,$fieldName);
            unset($data[1]);
            $newData = [];
            $tel = [];
            foreach($data as $k=>$v){
                if($v['uploadurl']){
                    $tel[] = $v['tel'];
                    $newData[]=['tel'=>$v['tel'],'subject'=>$v['subjectname'],'level'=>$v['level']];
                }
            }

            $tel = implode(",",$tel);
            $sm_map['tel'] = array("in",$tel);
            $sm_map['sm.status'] = 1;
            $smData = m("subject_members")
            ->alias('sm')
            ->join('left join subject s on s.id = sm.sid')
            ->where($sm_map)->field("sm.id,sm.tel,s.title")->select();
            $newsmData = [];
            foreach($smData as $k=>$v){
                foreach($newData as $ko=>$vo){
                    if($v['tel']==$vo['tel'] && $v['title'] == $vo['subject']){
                        $newsmData[]=[
                            'sm_id'=>$v['id'],
                            'level'=>$vo['level'],
                        ];

                    }
                }
            };
            $this->deldir(dirname('./Uploads'.$excelPath).'/');
            $model = M();
            $model->execute("TRUNCATE review");
            m('review')->where('1')->delete();
            $res = m('review')->addAll($newsmData);
            if($res){
                $importState = ['message'=>'导入成功','success'=>true];

            }else{
                $importState = ['message'=>'导入失败','success'=>false];
            }
            $this->ajaxReturn($importState);
        }else{
            exit($result['info']);
        }
    }

    /**
     * 图片上传
     */
    public function upload_pic()
    {
        //原图片地址
        $res = upload_img('pic','works');
        if(!$res['status']) {
            $result["success"] = false;
            $result["message"] = "上传失败";
        }else{
            $result["success"] = true;
            $result["message"] = "上传成功";
            $result["savefile"] = $res['file']['old'];
        }
        $this->ajaxReturn($result);
    }

    /**
     * 删除图片
     * */
    public function removeImg(){
        $path = I('post.path');
        $id = I('id');
        if(!$id){
            $res =  removeImg($path);
        }else{
            $res = true;
        }
        if($res){
            $this->ajaxReturn(['success'=>true,'message'=>'删除成功']);
        }else{
            $this->ajaxReturn(['success'=>false,'message'=>'图片删除失败']);
        }
    }
}