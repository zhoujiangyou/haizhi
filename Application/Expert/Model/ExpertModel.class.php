<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace Expert\Model;
use Think\Model;

/**
 * Class IssueModel 专辑模型
 * @package Issue\Model
 * @auth 陈一枭
 */
class ExpertModel extends Model {
    protected $tableName='expert';
    protected $_validate = array(
      array('cover_id','require','请上传专家头像',self::MUST_VALIDATE ),
      array('e_name','require','专家姓名不能为空'),
      array('e_name', '1,20', '姓名在1-20长度以内', self::MUST_VALIDATE , 'length'),
      array('e_category','require','擅长领域必须选择',self::MUST_VALIDATE ),
      array('e_tele', '/^(1[3|4|5|8])[0-9]{9}$/', '手机格式不正确', self::EXISTS_VALIDATE),
      array('e_tele', '', '手机号码已存在', self::EXISTS_VALIDATE, 'unique'), //手机号被占用
      array('email', 'email', '邮箱格式不正确', self::EXISTS_VALIDATE), //邮箱格式不正确
      array('email', '4,32', '邮箱长度不合法', self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
      array('email', '', '邮箱已存在', self::EXISTS_VALIDATE, 'unique'), //邮箱被占用
      array('e_desc','require','专家描述不能为空',self::MUST_VALIDATE ),
      array('e_desc', '1,500', '专家描述在五百字以内', self::MUST_VALIDATE , 'length'),
    );
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('status', '0', self::MODEL_INSERT),
    );

    public function Lists($map,$Page){
        $result = $this->where($map)
                       ->limit($Page->firstRow.','.$Page->listRows)
                       ->select();
        return $result;


    }

}