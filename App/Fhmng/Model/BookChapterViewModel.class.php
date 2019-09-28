<?php
/**日志
 * 描述：后台网站图书章节视图
 * 创建人:靖霞
 * 创建时间：2016-10-09
 * */
namespace Fhmng\Model;
use Think\Model\ViewModel;
class BookChapterViewModel extends ViewModel{
    public $viewFields = array(
        "BookChapter"=>array(
            "*",
            "_type"=>"LEFT"
        ),
        "Book"=>array(
           
            "book_name",
            "_on"=>"Book.id=BookChapter.book_id"
        )
    );
}