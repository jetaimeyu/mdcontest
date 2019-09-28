<?php
/**日志
 * 描述：后台网站文章视图
 * 创建人:靖霞
 * 创建时间：2016-10-10
 * */
namespace Fhmng\Model;
use Think\Model\ViewModel;
class BookContentViewModel extends ViewModel{
    public $viewFields = array(
        "BookContent"=>array(
            "*",
            "_type"=>"LEFT"
        ),
        "BookChapter"=>array(
            "chapter_name",
            "_on"=>"BookContent.chapter_id=BookChapter.id",
            "_type"=>"LEFT"
        ),
        "Book"=>array(
            "book_name",
            "_on"=>"BookContent.book_id=Book.id",
            "_type"=>"LEFT"
        )
    );
}