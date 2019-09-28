<?php namespace Home\Model;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/7
 * Time: 11:35
 */
class HomeindexModel
{
    //获取pc端奖励图片
    public function getPcPrize()
    {
        $pic =  m("banner")->cache(true)->where("status=1")->where(['title'=>'奖励1'])->order("order_no")->field("title,banner_img,link_url")->select();
        return $pic;
    }
    //获取手机端奖励图片
    public function getMobilePrize()
    {
        $pic =  m("banner")->cache(true)->where("status=1")->where(['title'=>'奖励2'])->order("order_no")->field("title,banner_img,link_url")->select();
        return $pic;
    }
    //获取轮播图
    public function getBanner(){
        $bannerData = m("banner")->cache(true)->where("status=1")->where(['title'=>['not in',['奖励1', '奖励2']]])->order("order_no")->field("title,banner_img,link_url")->select();
        return $bannerData;
    }
    //获取大赛简介
    public function getGameDetail(){
       $gameDetail = m("sys_config")->where("config_code='S_description'")->getField("config_value");
       return $gameDetail;
    }
    //获取大赛主题
    public function getGameTitle(){
        $GameTitle = m("sys_config")->where("config_code='S_topic'")->getField("config_value");
        return $GameTitle;
    }
    //获取赛程安排
    public function getSchedule(){
        $scheduleData = m("sys_config")->where("`group`='报名设置'")->order("order_no")->field("config_name,description,config_value,config_code")->select();
        return $scheduleData;
    }
    //获取比赛题目
    public function getCompetitionTopic(){
        $subjectData = m("subject")->where("status=1")->order("order_no")
            ->field('id, title, description,(case when subject_type = 2 then "工程设计" else "创意设计" end) subtypename')
            ->select();
        return $subjectData;

    }
    //获取合作伙伴
    public function  getCooperativeUnit(){
        $listData = S('listData');
     	if(!$listData){
          $modular_Id = m("menu")->where("menu_code='HZDW' and status=1")->getField("id");
          $map['m.status'] = 1;
          $map['m.pid'] = $modular_Id;
          $map['c.status'] =1;
          $cooperativeUnitData = m("menu")->alias('m')
              ->join("content as c ON m.id = c.menu_id")
              ->where($map)
              ->order('m.order_no,m.id,c.order_no,c.id')
              ->field("m.menu_name,c.title,c.menu_id,c.content_img")
              ->select();
          $listData =[];
          foreach($cooperativeUnitData as $k=>$v){
              $listData[$v['menu_id']][]=$v;
          }
          S('listData',$listData);
        }
        return $listData;
    }
}
