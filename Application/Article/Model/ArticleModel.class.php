<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace Article\Model;
use Think\Model;

/**
 * Class IssueModel 专辑模型
 * @package Issue\Model
 * @auth 陈一枭
 */
class ArticleModel extends Model {

    protected $tableName='article';
    protected $_validate = array(
      array('title','require','文章标题不能为空'),
      array('title', '1,20', '文章标题在20字以内', self::MUST_VALIDATE , 'length'),
      array('content','require','文章内容不能为空',self::MUST_VALIDATE ),
      array('content', '1,1000', '文章内容在1000字以内', self::MUST_VALIDATE , 'length'),
      array('category','require','文章类别必须选择',self::MUST_VALIDATE ),
    );
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('status', '0', self::MODEL_INSERT),
        array('uid', 'is_login',self::MODEL_INSERT, 'function'),
    );


    public function GetArticles($where,$page){
         $data  = $this->join('LEFT JOIN  haizhi_member ON haizhi_member.uid = haizhi_article.uid')
                ->where($where)
                ->order('haizhi_article.create_time')
                ->limit($page->firstRow.','.$page->listRows)
                ->field('haizhi_article.* , haizhi_member.nickname as nickname')
                ->select();
         return $data;
    }


}