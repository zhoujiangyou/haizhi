<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace Project\Model;
use Think\Model;

/**
 * Class projectModel 项目模型
 * @package Project\Model
 * @auth zhouhaitian
 */
class ProjectModel extends Model {

    protected $tableName='Project';
    protected $_validate = array(
        array('name','require','项目名称必须填写', self::MUST_VALIDATE ), //默认情况下用正则进行验证
        array('description','require','项目介绍必须填写', self::MUST_VALIDATE ),
        array('description', '1,300', '项目描述长度必须在1——300之内', self::MUST_VALIDATE , 'length'), //描述长度不合法
        array('shortdesc', '1,50', '一句话描述在25字以内', self::MUST_VALIDATE , 'length'), //描述长度不合法
        array('cover_img','require','项目Logo和封面图必须填写', self::MUST_VALIDATE ),
        array('uid','require','创建人id不得为空', self::MUST_VALIDATE ),
        array('pro_logo','require','项目logo必须填写', self::MUST_VALIDATE ),
        array('category','require','项目类型必须选择', self::MUST_VALIDATE ),
        array('stage','require','所属阶段必须选择', self::MUST_VALIDATE ),
    );
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        // -2：草稿 -1：删除 0：审核 1：发布 2：审核不通过打回
        array('status', '-2', self::MODEL_INSERT),
    );

    //获取所以项目
    //-1 : 删除数据  0：待审核数据  1:已发布项目
    public function projects($where,$Page){
        $result = $this->join('LEFT JOIN  haizhi_member ON haizhi_member.uid = haizhi_project.uid')
            ->join(' LEFT JOIN haizhi_category ON haizhi_project.category = haizhi_category.id')
            ->join(' LEFT JOIN haizhi_picture ON haizhi_picture.id = haizhi_project.cover_img')
            ->where($where)
            ->order('haizhi_project.create_time')
            ->field('haizhi_project.id as project_id,
                     haizhi_project.name as project_name,
                     haizhi_project.description,
                     haizhi_project.shortdesc,
                     haizhi_project.pro_logo,
                     haizhi_project.create_time as project_create_time,
                     haizhi_project.status as project_status,
                     haizhi_category.category_name,
                     haizhi_project.cover_img,
                     haizhi_picture.path ,
                     haizhi_member.uid,
                     haizhi_member.nickname
                      ')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        return $result;
    }


    //获取已发布项目项目 适配接口
    //-1 : 删除数据  0：待审核数据  1:已发布项目
    public function lists($where,$skip,$count){
        $result = $this->join('LEFT JOIN haizhi_member ON haizhi_member.uid = haizhi_project.uid')
            ->join(' LEFT JOIN haizhi_category ON haizhi_project.category = haizhi_category.id')
            ->join(' LEFT JOIN haizhi_picture ON haizhi_picture.id = haizhi_project.cover_img')
            ->where($where)
            ->order('haizhi_project.create_time')
            ->field('haizhi_project.id as project_id,
                     haizhi_project.name as project_name,
                     haizhi_project.shortdesc as short_desc,
                     haizhi_project.description ,
                     haizhi_project.status ,
                     haizhi_project.create_time as project_create_time,
                     haizhi_project.update_time as project_update_time,
                     haizhi_project.status as project_status,
                     haizhi_category.category_name,
                     haizhi_member.uid,
                     haizhi_project.cover_img,
                     haizhi_picture.path,
                     haizhi_member.nickname
                      ')
            ->limit($skip . ',' . $count)
            ->select();
        return $result;
    }


    //获取已发布项目项目 适配接口
    //-1 : 删除数据  0：待审核数据  1:已发布项目
    public function projectTypeList(){
        $result = M('category')
            ->select();
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     *
     * 获取单条记录
     */
    public function get($id) {
        $result = $this->join('LEFT JOIN haizhi_member ON haizhi_member.uid = haizhi_project.uid')
            ->join(' LEFT JOIN haizhi_category ON haizhi_project.category = haizhi_category.id')
            ->join(' LEFT JOIN haizhi_picture as cover ON cover.id = haizhi_project.cover_img')
            ->join(' LEFT JOIN haizhi_picture as logo ON logo.id = haizhi_project.pro_logo')
            ->join(' LEFT JOIN haizhi_picture as product ON product.id = haizhi_project.pro_imgs')
            ->join(' LEFT JOIN haizhi_picture as team ON team.id = haizhi_project.team_imgs')
            ->join(' LEFT JOIN haizhi_picture as business ON business.id = haizhi_project.business_image')
            ->where('haizhi_project.id=%d', array($id))
            ->field('haizhi_project.id as project_id,
                     haizhi_project.name as project_name,
                     haizhi_project.shortdesc as short_desc,
                     haizhi_project.description ,
                     haizhi_project.status ,
                     haizhi_project.stage ,
                     haizhi_project.shortdesc,
                     haizhi_project.category,
                     haizhi_project.othercoreteamers,
                     haizhi_project.market_research,
                     haizhi_project.competitive_edge,
                     haizhi_project.business_model,
                     haizhi_project.video_url,
                     haizhi_project.need_res,
                     haizhi_project.own_res,
                     haizhi_project.province,
                     haizhi_project.city,
                     haizhi_project.country,
                     haizhi_project.proinstruction,
                     haizhi_project.business_image,
                     haizhi_project.organizationcode,
                     haizhi_project.yingyecode,
                     haizhi_project.taxcode,
                     haizhi_project.team_desc,
                     haizhi_project.create_time as project_create_time,
                     haizhi_project.update_time as project_update_time,
                     haizhi_project.status as project_status,
                     haizhi_category.category_name,
                     haizhi_member.uid,
                     haizhi_project.cover_img,
                     haizhi_project.duibiao_comp,
                     haizhi_project.other_edge,
                     haizhi_project.pro_imgs as pro_imgs_code,
                     haizhi_project.team_imgs as team_imgs_code,
                     haizhi_project.team_size,
                     haizhi_project.pro_video_url,
                     haizhi_project.pro_logo,
                     business.path as business_path,
                     cover.path as cover_path,
                     logo.path as logo_path,
                     product.path as pro_imgs,
                     team.path as team_imgs,
                     haizhi_member.nickname
                      ')->find();
        return $result;
    }
    // 获取大事记信息
    // 默认只取1000条数据
    public function getEvents($id, $page = 1, $order = 'create_time desc', $field = '*', $r = 1000) {
        $list = $this->join('INNER JOIN haizhi_project_event ON haizhi_project_event.proid = haizhi_project.id')->field('haizhi_project_event.id,
                haizhi_project_event.create_time, haizhi_project_event.type,
                haizhi_project_event.content')->where('haizhi_project.id=%d', array($id))->page($page, $r)->order($order)->select();
        return array('list'=>$list);
    }
    public function getGrowData($id, $page = 1, $order = 'time desc', $field = '*', $r = 1000) {
        $list = $this->join('INNER JOIN haizhi_project_growdata ON haizhi_project_growdata.proid = haizhi_project.id')->field('haizhi_project_growdata.id,
                haizhi_project_growdata.time,
                haizhi_project_growdata.category,
                haizhi_project_growdata.data')->where('haizhi_project.id=%d', array($id))->page($page, $r)->order($order)->select();
        return $list;
    }

    public function getProducttest($id, $page = 1, $field = '*', $r = 1000) {
        $list = $this->join('INNER JOIN haizhi_project_producttest ON haizhi_project_producttest.proid = haizhi_project.id')
            ->field('
                haizhi_project_producttest.id,
                haizhi_project_producttest.weibo,
                haizhi_project_producttest.wechat,
                haizhi_project_producttest.apk,
                haizhi_project_producttest.ios,
                haizhi_project_producttest.account,
                haizhi_project_producttest.password,
                haizhi_project_producttest.website')
            ->where('haizhi_project.id=%d', array($id))
            ->page($page, $r)
            ->select();
        return $list;
    }

    public function getTeacher($id, $page = 1, $field = '*', $r = 1000) {
        $list = $this->join('INNER JOIN haizhi_project_teacher ON haizhi_project_teacher.proid = haizhi_project.id')
            ->join('LEFT JOIN haizhi_picture ON haizhi_picture.id = haizhi_project_teacher.avatar')
            ->field('haizhi_project_teacher.id as teacher_id,
                haizhi_picture.id as picture_id,
                haizhi_project.id as project_id,
                haizhi_project_teacher.name,
                haizhi_project_teacher.email,
                haizhi_project_teacher.introduction,
                haizhi_project_teacher.avatar,
                haizhi_picture.path')
            ->where('haizhi_project.id=%d', array($id))
            ->page($page, $r)
            ->select();
        return $list;
    }

    public function getCorePersons($id, $page = 1, $field = '*', $r = 1000) {
        $list = $this->join('INNER JOIN haizhi_project_coreteamer ON haizhi_project_coreteamer.proid = haizhi_project.id')
            ->join('LEFT JOIN haizhi_picture ON haizhi_picture.id = haizhi_project_coreteamer.avatar')
            ->field('haizhi_project_coreteamer.id as coreteamer_id,
                haizhi_picture.id as picture_id,
                haizhi_project.id as project_id,
                haizhi_project_coreteamer.name,
                haizhi_project_coreteamer.position,
                haizhi_project_coreteamer.email,
                haizhi_project_coreteamer.address,
                haizhi_project_coreteamer.self_instruction,
                haizhi_project_coreteamer.avatar,
                haizhi_picture.path')
            ->where('haizhi_project.id=%d', array($id))
            ->page($page, $r)
            ->select();
        return $list;
    }

    public function getNews($id, $page = 1, $order = 'create_time desc', $field = '*', $r = 1000) {
        $list = $this->join('INNER JOIN haizhi_project_news ON haizhi_project_news.proid = haizhi_project.id')->field('
                haizhi_project_news.id,
                haizhi_project_news.create_time,
                haizhi_project_news.new_title,
                haizhi_project_news.application,
                haizhi_project_news.new_website')
            ->where('haizhi_project.id=%d', array($id))->page($page, $r)->order($order)->select();
        return array('list'=>$list);
    }

}