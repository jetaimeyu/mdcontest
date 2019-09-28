<?php
/*作品展示*/
namespace Home\Controller;
use Home\Common\Controller\BaseController;
use Think\Page;


class WorksController extends BaseController {

    public function index()
    {
    //tab页签数据
    $workTitle = M('subject')->where('status=1')->field('id,title')->select();
    $toplist = $this->getTopList($workTitle);
    //tab数据
    $this->assign('titleName',$workTitle);
    //获取右侧基本排名 参数用于拼接数据
    $this->assign('toplist',$toplist);
    //设置页面标题
    $menuinfo = M('menu')->where(['menu_code'=>'ZPZS','status'=>1])->field("id,website_title")->find();
    $this->assign('webtitle',$menuinfo['website_title']);
      //获取比赛结束时间 判断有无作品展示！
      $endTime = m("sys_config")->where("`group`='报名设置' and config_code = 'm_endtime'")->getField('config_value');;
      if($endTime >  date("Y-m-d",time())){
          $isAllTop=1;
      }else{
          $isAllTop=2;
      }
      $this->assign('isAllTop',$isAllTop);
      //获取比赛上传作品图
      $this->display();
    }
    /*
     *获取左侧排名 type
     */
    public function workTopList($type)
    {
        $map['a.status'] = 1;
        $map['b.status'] = 1;
        $map['c.status'] = 1;
        $map['c.level'] = array('GT',0);
        if($type != 0){
            $map['a.id'] = $type;
        }
        //分页相关
        $count =M("subject") ->alias('a')
            ->join('subject_members b ON a.id = b.sid')
            ->join('review c ON b.id = c.sm_id')
            ->where($map)
            ->count();
        $Page = new Page($count,12);
        $show = $Page->show();
        $res = M("subject") ->alias('a')
            ->join('subject_members b ON a.id = b.sid')
            ->join('review c ON b.id = c.sm_id')
            ->where($map)
            ->field('c.sm_id as userid,c.level,a.id as workid,c.img,b.file_description as workdes,a.title as worktitle,b.file_name as workname,(case when c.level = 1 then "一等奖" when c.level = 2 then "二等奖" when c.level = 3 then "三等奖" else "优胜奖" end) toptitle , (case when c.level = 1 then "100000" when c.level = 2 then "30000" when c.level = 3 then "10000" else "3000" end) money')
            ->order('c.level,a.order_no,c.sm_id')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->assign('data',$res);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输
        $this->display();
    }
    /*
     * 获取右侧排名
     */
    public function getTopList($workTitle)
    {
        $map['a.status'] = 1;
        $map['b.status'] = 1;
        $map['c.status'] = 1;
        $res = M("subject") ->alias('a')
            ->join('subject_members b ON a.id = b.sid')
            ->join('review c ON b.id = c.sm_id')
            ->where($map)
            ->field('c.sm_id as userid,c.level,c.img,a.id as workid,b.file_description as workdes,a.title as worktitle,b.file_name as workname,(case when c.level = 1 then "一等奖" when c.level = 2 then "二等奖" when c.level = 3 then "三等奖" else "优胜奖" end) toptitle , (case when c.level = 1 then "100000" when c.level = 2 then "30000" when c.level = 3 then "10000" else "3000" end) money')
            ->order('c.level,a.order_no,c.sm_id')
            ->select();
        $listData = [];
        //数据处理
        foreach ($workTitle as $k => $v){
            $listData[$v['id']] = [];
        }
        foreach ($listData as $key => $val){
            foreach ($res as $k => $v){
                if($key == $v['workid']){
                    $listData[$key][] = $v;
                }
            }
        }
       // dump($listData);
        return $listData;
    }
}