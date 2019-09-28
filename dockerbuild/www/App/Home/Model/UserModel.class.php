<?php namespace  Home\Model;
use Think\Model;

/**
 * Created by PhpStorm.
 * User: "李明冬"
 * Date: 2018/8/6
 * Time: 16:52
 */


class UserModel extends Model
{
    public function addUserData($data)
    {
//        $map['telephone'] = array('neq',$data['tel']);
        $map['telephone'] = $data['telephone'];
        $data['pwd'] = md5($data['pwd']);
        $data['create_time'] = date('Y-m-d H:i:s');
        $res = $this->where($map)->find();
        if($res){
            return false;
        }else{
            $id = $this->add($data);
            session(array('name'=>'Match_User_Id','expire'=>3600));
            session("Match_User_Id",$id);
            return true;
        }

    }
    public function getUserData($data)
    {
        $map["telephone"] = $data['telephone'];
        $map["pwd"] = md5($data['pwd']);
        $res = $this->where($map)->find();
        if($res){
            session(array('name'=>'Match_User_Id','expire'=>3600));
            session("Match_User_Id",$res['id']);
            return true;
        }else{
            return false;
        }
    }
    public function setUserPwd($data)
    {
       $map['telephone'] = $data['telephone'];
       $res = $this->where($map)->find();

       if($res){
           $data['id'] = $res['id'];
           $data['pwd'] = md5($data['pwd']);
           $type =  $this->save($data);
           if($type === false){
               return false;
           }
           session(array('name'=>'Match_User_Id','expire'=>3600));
           session("Match_User_Id",$res['id']);
           return true;
       }else{
           return false;
       }
    }
    public function setUserName($data)
    {
        $map['telephone'] = $data['telephone'];
        $res = $this->where($map)->find();
        if($res){
            $data['id'] = $res['id'];
            $data['true_name'] = ($data['true_name']);
            $type =  $this->save($data);
            if($type === false){
                return false;
            }
            return true;

        }else{
            return false;
        }
    }


}