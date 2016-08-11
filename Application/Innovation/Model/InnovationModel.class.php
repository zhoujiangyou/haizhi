<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace Innovation\Model;
use Think\Model;

/**
 * Class PolicyModel 政策模型
 * @package Policy\Model
 * @auth zhouhaitian
 */
class InnovationModel extends Model {

    protected $tableName='policy';
    protected $_validate = array(
        array('title','require','政策名称必须填写', self::MUST_VALIDATE ), //默认情况下用正则进行验证
        array('content', '1,10000', '政策内容长度必须在1——10000之内', self::MUST_VALIDATE , 'length'), //描述长度不合法
        array('category_main','require','必须选择政策类型', self::MUST_VALIDATE ),
        array('category_minor','require','必须选择政策第二类型', self::MUST_VALIDATE ),
    );
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );


    /**
     *获取政策  , 根据条件删选
     * 参数: array()
     */
    public function get(){


    }


}