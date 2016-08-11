<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;
use Project\Model\ProjectModel;
/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends Controller {


    protected function _initialize() {
        /*读取站点配置*/
        $config = api('Config/lists');

        C($config); //添加配置
        if (!C('WEB_SITE_CLOSE')) {
            $this->error(L('_ERROR_WEBSITE_CLOSED_'));
        }
    }


    public function index() {

       $data = M('temp_home')
            ->join('LEFT JOIN haizhi_picture ON haizhi_picture.id = haizhi_temp_home.img')
            ->field('haizhi_temp_home.* ,haizhi_picture.path')
            ->select();
        $new = M('new_flash')->order('create_time')->limit(0,10)->select();

        foreach($new as $key=>$value){
            $new[$key]['time'] =timeDifference(date('Y-m-d h:i:s',$value['create_time']));
        }

        $category = I('get.category');
        if($category!=1 && !empty($category)){
            $map['haizhi_project.category'] = array('eq', $category);
        }else{
            $category=1;
        }
        $map['haizhi_project.status'] = array('eq', 1);
        $projectmodel = new ProjectModel();
        $count = $projectmodel->where($map)->count();
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $lists =$projectmodel->projects($map,$Page);
        if(!empty($lists)){
            $lists= int2time($lists);
            foreach($lists as $key =>$value){
                $lists[$key]['User_img_path'] = '/Uploads/Avatar'.M('avatar')->where('Uid='.$value['uid'])->getField('path');
                $lists[$key]['last_time'] = timeDifference($value['project_create_time']);
            }
        }

        $categorylist = M('category')->select();
        $this->assign('categorynum',$category);
        $this->assign('category',$categorylist);
        $this->assign('_page',$show);// 赋值分页输出
        $this->assign('project',$lists);
        $this->assign('new',$new);
        $this->assign('data',$data);
        $this->display('home');
    }

    public function lists(){
        $category = empty(I('category'))? :$map['haizhi_project.category']=array('eq', I('category'));
        $skip = empty(I('skip'))?0:I('skip');
        $num = empty(I('count'))?10:I('count');
        $map['haizhi_project.status'] = array('eq', 1);
        $projectmodel = new ProjectModel();
        $lists =$projectmodel->lists($map,$skip,$num);
        if(!empty($lists)){
            $lists= int2time($lists);
            foreach($lists as $key =>$value){
                $lists[$key]['User_img_path'] = '/Uploads/Avatar'.M('avatar')->where('Uid='.$value['uid'])->getField('path');
                $lists[$key]['last_time'] = timeDifference($value['project_create_time']);
            }
        }
        $this->ajaxReturn(array('info'=>'获取数据成功','status'=>1,'data'=>$lists));
    }

    public function overSeaService(){

     $this->display('oversea');

    }

    public function webSite(){

    $this->display('website');

    }

}