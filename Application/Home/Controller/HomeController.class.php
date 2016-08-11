<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;


class HomeController extends AdminController
{
    public function config()
    {
        $builder = new AdminConfigBuilder();
        $data = $builder->handleConfig();

        $data['OPEN_LOGIN_PANEL'] = $data['OPEN_LOGIN_PANEL'] ? $data['OPEN_LOGIN_PANEL'] : 1;
        $builder->title(L('_HOME_SETTING_'));

        $modules = D('Common/Module')->getAll();
        foreach ($modules as $m) {
            if ($m['is_setup'] == 1 && $m['entry'] != '') {
                if (file_exists(APP_PATH . $m['name'] . '/Widget/HomeBlockWidget.class.php')) {
                    $module[] = array('data-id' => $m['name'], 'title' => $m['alias']);
                }
            }
        }
        $module[] = array('data-id' => 'slider', 'title' => L('_CAROUSEL_'));

        $default = array(array('data-id' => 'disable', 'title' => L('_DISABLED_'), 'items' => $module), array('data-id' => 'enable', 'title' =>L('_ENABLED_'), 'items' => array()));
        $builder->keyKanban('BLOCK', L('_DISPLAY_BLOCK_'),L('_TIP_DISPLAY_BLOCK_'));
        $data['BLOCK'] = $builder->parseKanbanArray($data['BLOCK'], $module, $default);
        $builder->group(L('_DISPLAY_BLOCK_'), 'BLOCK');

        $show_blocks = get_kanban_config('BLOCK_SORT', 'enable', array(), 'Home');
        $builder->buttonSubmit();


        $builder->data($data);


        $builder->display();
    }
    public function homeConfig(){
        $data = M('temp_home')
               ->join('LEFT JOIN haizhi_picture ON haizhi_picture.id = haizhi_temp_home.img')
               ->field('haizhi_temp_home.* ,haizhi_picture.path')
               ->select();
        $this->assign('_list',$data);
        $this->display(T('Home@home/index'));
    }
    public function update(){

        if(IS_POST){

            $id = I('get.id');
            $data = I('post.');
           $model =  M('temp_home');
            if($model->where("id=$id")->save($data)){
               $this->ajaxReturn(array('info'=>'保存成功','status'=>1,'url'=>U('homeConfig')));
            }else{
                $this->ajaxReturn(array('info'=>$model->getError(),'status'=>0));
            }

        }else{
            $id = I('get.id');
            $data = M('temp_home')->where("id=$id")->find();
            $builder = new AdminConfigBuilder();
            $builder->title('修改首页广告配置')
                ->keySingleImage('img','广告位图片')->keyText('title','广告标题')
                ->keySelect('category','广告类型','',array('文章'=>'文章','项目'=>'项目'))
                ->keyText('url','链接地址');
            $builder->data($data);
            $builder->buttonSubmit();
            $builder->display();
        }

    }
    public function newConfig(){
        $name = I('name', '', 'text')?I('name', '', 'text'):'';
        if (is_numeric($name)) {
            $map['id|title'] = array(intval($name), array('like', '%' . $name . '%'), '_multi' => true);
        } else {
            if ($name !== '') {
                $map['title'] = array('like', '%' . (string)$name . '%');
            }
        }
        $model = M('new_flash');
        $count = $model->count();
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $lists =$model->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $lists);
        $this->assign('_page',$show);// 赋值分页输出
        $this->display(T('Home@home/new'));
    }
    public function dleNews(){
        $ids = I('id');
        foreach($ids as $key=>$value){
           M('new_flash')->delete($value);
        }
        $this->ajaxReturn(array('info'=>'删除成功','status'=>1));
    }
    public function addNews(){
        if(IS_POST){
           $data = I('post.');
           $data['create_time'] = time();

            if(M('new_flash')->add($data)){
                $this->ajaxReturn(array('info'=>'新增成功','status'=>1,'url'=>U('newConfig')));
            }else{
                $this->ajaxReturn(array('info'=>'新增失败','status'=>0,'url'=>U('newConfig')));
            }
        }else {
            $builder = new AdminConfigBuilder();
            $builder->title('添加快讯');
            $builder->keyText('title', '标题')->keyText('content', '内容');
            $builder->buttonSubmit();
            $builder->display();
        }
    }
    public function updateNew(){
        if(IS_POST){
            $data = I('post.');
            $id= I('get.id');
            if(M('new_flash')->where("id=$id")->save($data)){
                $this->ajaxReturn(array('info'=>'新增成功','status'=>1,'url'=>U('newConfig')));
            }else{
                $this->ajaxReturn(array('info'=>'新增失败','status'=>0,'url'=>U('newConfig')));
            }
        }else {
            $id= I('get.id');
            $data = M('new_flash')->where("id=$id")->find();
            $builder = new AdminConfigBuilder();
            $builder->title('添加快讯');
            $builder->keyText('title', '标题')->keyText('content', '内容');
            $builder->data($data);
            $builder->buttonSubmit();
            $builder->display();
        }


    }


}
