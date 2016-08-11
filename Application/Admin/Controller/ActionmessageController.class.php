<?php

namespace Admin\Controller;


use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;

class ActionmessageController extends AdminController
{

     public function configureList(){
          $model = M('action_message');
          $count = $model->count();
          $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
          $show       = $Page->show();// 分页显示输出
          $lists =$model->select();
          $this->assign('_list', $lists);
          $this->assign('_page',$show);// 赋值分页输出
          $this->display('list');
      }

     public function add(){
        if(IS_POST){
         $data = I('post.');
         if(empty($data['action_name'])){
             $this->ajaxReturn(array('info'=>'行为动作不能为空','status'=>0));
         }

         if($data['is_sms'] ==1 && empty($data['sms_template'])){
                $this->ajaxReturn(array('info'=>'选择发送，短信模板必须填写','status'=>0));
         }

        if($data['is_message'] ==1 && empty($data['sms_template'])){
                $this->ajaxReturn(array('info'=>'选择发送，站内信模板必须填写','status'=>0));
         }

        if($data['is_email'] ==1 && empty($data['sms_template'])){
                $this->ajaxReturn(array('info'=>'选择发送，站内信模板必须填写','status'=>0));
        }

         $data['action_name'] = strtolower($data['action_name']);

         if(M('action_message')->add($data)){
             $this->ajaxReturn(array('info'=>'配置信息新增的成功','status'=>1,'url'=>U('configureList')));
         }

        }else{
         $builder = new AdminConfigBuilder();
         $builder->title('新增短信邮件配置');
         $builder->keyText('action_name','行为地址','输入不区分大小写一律转换为小写');
         $builder->keySelect('is_sms','是否发送短信','选择发送，短信模板必须填写',array('1'=>'发送','0'=>'不发送'));
         $builder->keyEditor('sms_template','短信模板');
         $builder->keySelect('is_message','是否发送站内信','选择发送，站内信模板必须填写',array('1'=>'发送','0'=>'不发送'));
         $builder->keyEditor('message_template','站内信模板');
         $builder->keySelect('is_email','是否发送邮件','选择发送，站内信模板必须填写',array('1'=>'发送','0'=>'不发送'));
         $builder->keyEditor('email_template','邮件模板');
         $builder->buttonSubmit();
         $builder->display();
       }
      }

     public function update(){
         if(IS_POST){
             $data = I('post.');
             if(empty($data['action_name'])){
                 $this->ajaxReturn(array('info'=>'行为动作不能为空','status'=>0));
             }

             if($data['is_sms'] ==1 && empty($data['sms_template'])){
                 $this->ajaxReturn(array('info'=>'选择发送，短信模板必须填写','status'=>0));
             }

             if($data['is_message'] ==1 && empty($data['sms_template'])){
                 $this->ajaxReturn(array('info'=>'选择发送，站内信模板必须填写','status'=>0));
             }

             if($data['is_email'] ==1 && empty($data['sms_template'])){
                 $this->ajaxReturn(array('info'=>'选择发送，站内信模板必须填写','status'=>0));
             }

             $data['action_name'] = strtolower($data['action_name']);

             if(M('action_message')->save($data)){
                 $this->ajaxReturn(array('info'=>'配置信息新增的成功','status'=>1,'url'=>U('configureList')));
             }

         }else{
             $id = I('get.id');
             $data = M('action_message')->where('id=%d',$id)->find();
             $builder = new AdminConfigBuilder();
             $builder->title('新增短信邮件配置');
             $builder->keyText('action_name','行为地址','输入不区分大小写一律转换为小写');
             $builder->keySelect('is_sms','是否发送短信','选择发送，短信模板必须填写',array('1'=>'发送','0'=>'不发送'));
             $builder->keyEditor('sms_template','短信模板');
             $builder->keySelect('is_message','是否发送站内信','选择发送，站内信模板必须填写',array('1'=>'发送','0'=>'不发送'));
             $builder->keyEditor('message_template','站内信模板模板');
             $builder->keySelect('is_email','是否发送邮件','选择发送，站内信模板必须填写',array('1'=>'发送','0'=>'不发送'));
             $builder->keyEditor('email_template','邮件模板');
             $builder->data($data);
             $builder->buttonSubmit();
             $builder->display();
         }
     }

     public function delete(){

      $ids = I('post.id');
      $model = M('action_message');
      foreach($ids as $value){
          $model->delete($value);
      }
        $this->ajaxReturn(array('info'=>'删除成功','status'=>1));
     }
}
