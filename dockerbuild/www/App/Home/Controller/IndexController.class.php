<?php
/*首页*/
namespace Home\Controller;
use Home\Common\Controller\BaseController;
use Home\Model\HomeindexModel;

class IndexController extends BaseController {
    private $indexData;
    public function __construct()
    {
        parent::__construct();
        $this->indexData = new HomeindexModel();
    }

    //首页
    public function index()
    {
      //获取banner
      $bannerData = $this->indexData->getBanner();
      //获取大赛简介
      $gameDetail  = $this->indexData->getGameDetail();
      //获取大赛主题
      $gameTitle = $this->indexData->getGameTitle();
      //获取赛程安排
      $scheduleData = $this->indexData->getSchedule();
      //获取比赛题目
      $competitionTopicData = $this->indexData->getCompetitionTopic();
      //获取合作单位pc首页
      $cooperativeUnitData = $this->indexData->getCooperativeUnit();
      foreach($cooperativeUnitData as $k =>&$v){
         $v = array_chunk($v,4);
      }
      $pcPrize = $this->indexData->getPcPrize();
      $data = [
          'pcPrize' => $pcPrize,
          'bannerData'=>$bannerData,
          'gameDetail'=>$gameDetail,
          'gameTitle'=>$gameTitle,
          'scheduleData'=>$scheduleData,
          'competitionTopicData'=>$competitionTopicData,
          'cooperativeUnitData'=>$cooperativeUnitData,
      ];
      $this->assign('data',$data);
      $this->display();
    }

}
