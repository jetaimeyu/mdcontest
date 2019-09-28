<?php

namespace Fhmng\Controller;
use Think\Controller;
class UploadimgController extends Controller{
  
    /*上传图片 get参数 dir为 上传到七牛中的前缀 */
    public function upload_pic()
    {
        $ret=upload_img('Filedata',I('get.dir')?:'img');
        if($ret['status']){
            list($width, $height, $type, $attr) = getimagesize(C('QINIU_DOMAIN').$ret['file']['old']);

            $this->ajaxReturn(array('success'=>true,'message'=>'上传成功！','file'=>$ret['file']['old'],'width'=>$width,'height'=>$height));
        }
        else{
            $this->ajaxReturn(array('success'=>false,'message'=>'上传失败！'));
        } 
    }

    //裁剪 x y 开始裁剪的位置  w h 裁剪的宽高
    public function crop()
    {
        $post = I('post.');
        if(!$post['x'] && !$post['y'] && !$post['w'] && !$post['h']){
            $this->ajaxReturn(['success'=>true,'path'=>$post['img']]);
        }else{

            $image = new \Think\Image(); 
            $image->open(C('QINIU_DOMAIN').$post['img']);
            $imgname = rtrim(rtrim($post['img'],'imageslim'),'?');
            $dir = C('UPLOAD_PATH').'crop/';
            if(!is_dir($dir)){
                mkdir($dir);
            }
            //将图片裁剪为w h并保存
            $image->crop((int)$post['w'], (int)$post['h'],(int)$post['x'],(int)$post['y'])->save($dir.$imgname);
            //上传到七牛
            $qiniuConfig = C('UPLOAD_SITEIMG_QINIU')['driverConfig'];
             // $file['name'] = $file['savepath'] . $file['savename'];
            $file['savepath'] = 'crop/';
            $file['savename'] = $imgname;
            $file['tmp_name'] = $dir.$imgname;
            $qiniu = new \Think\Upload\Driver\Qiniu($qiniuConfig);
            removeImg($imgname);
            $qiniu->save($file);
            //删除本地
            unlink($file['tmp_name']);
            $this->ajaxReturn(['success'=>true,'path'=>str_replace('/','_',$file['name']).'?imageslim']);
        }

    }
}