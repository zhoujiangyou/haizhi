<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/18
 * Time: 11:20
 */
namespace Admin\Model;
use Think\Model;

class VertifyModel extends  Model{
    /** 获取个人认证申请列表
     *
     */
    public function lists($Page){
      $model  = M('personal_vertify');
      $data = $model->join('LEFT JOIN haizhi_ucenter_member ON haizhi_personal_vertify.uid = haizhi_ucenter_member.id')
              ->field('haizhi_personal_vertify.*,haizhi_ucenter_member.username')
              ->order('haizhi_personal_vertify.is_pass,haizhi_personal_vertify.id')
              ->limit($Page->firstRow.','.$Page->listRows)
              ->select();
       return $data;
    }

    public function get($id){
        $where = array('haizhi_personal_vertify.id'=>$id);
        $model  = M('personal_vertify');
        $data = $model->join('LEFT JOIN haizhi_ucenter_member ON haizhi_personal_vertify.uid = haizhi_ucenter_member.id')
            ->field('haizhi_personal_vertify.*,haizhi_ucenter_member.username')
            ->where($where)
            ->find();
        $data['identify_image_facei'] =M('picture')->where(array('id'=>$data['identify_image_face']))->getField('path');
        $data['identify_image_backi'] =M('picture')->where(array('id'=>$data['identify_image_back']))->getField('path');
        $data['identify_image_personi'] =M('picture')->where(array('id'=>$data['identify_image_person']))->getField('path');
        return $data;
    }


    public function sum(){
      return M('personal_vertify')->count();
    }


    /*********************************获取组织机构认证相关数据********************************************/

   public function sumB(){
       return M('business_vertify')->count();
   }

    public function getB($id){
        $where = array('haizhi_business_vertify.id'=>$id);
        $model  = M('business_vertify');
        $data = $model->join('LEFT JOIN haizhi_ucenter_member ON haizhi_business_vertify.uid = haizhi_ucenter_member.id')
            ->field('haizhi_business_vertify.*,haizhi_ucenter_member.username')
            ->where($where)
            ->find();
        $data['company_logo'] =M('picture')->where(array('id'=>$data['company_logo']))->getField('path');
        $data['business_image'] =M('picture')->where(array('id'=>$data['business_image']))->getField('path');
        return $data;
    }

    public function listsB($Page){
        $model  = M('business_vertify');
        $data = $model->join('LEFT JOIN haizhi_ucenter_member ON haizhi_business_vertify.uid = haizhi_ucenter_member.id')
            ->field('haizhi_business_vertify.*,haizhi_ucenter_member.username,haizhi_ucenter_member.mobile')
            ->order('haizhi_business_vertify.is_pass,haizhi_business_vertify.id')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        return $data;
    }
    /*********************************专家认证相关信息********************************************/

    public function sumE(){
        return M('expert_vertify')->count();
    }

    public function getE($id){
        $where = array('haizhi_expert_vertify.id'=>$id);
        $model  = M('expert_vertify');
        $data = $model->join('LEFT JOIN haizhi_ucenter_member ON haizhi_expert_vertify.uid = haizhi_ucenter_member.id')
            ->field('haizhi_expert_vertify.*,haizhi_ucenter_member.username,haizhi_ucenter_member.mobile')
            ->where($where)
            ->find();
        $data['title_image'] =M('picture')->where(array('id'=>$data['title_image']))->getField('path');
        $data['education_image'] =M('picture')->where(array('id'=>$data['education_image']))->getField('path');
        return $data;
    }

    public function listsE($Page){
        $model  = M('expert_vertify');
        $data = $model->join('LEFT JOIN haizhi_ucenter_member ON haizhi_expert_vertify.uid = haizhi_ucenter_member.id')
            ->field('haizhi_expert_vertify.*,haizhi_ucenter_member.username,haizhi_ucenter_member.mobile')
            ->order('haizhi_expert_vertify.is_pass,haizhi_expert_vertify.id')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        return $data;
    }








}