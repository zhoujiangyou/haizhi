<?php


namespace Project\Controller;

use Think\Controller;


class ViewController extends Controller {
    /**
     * 业务逻辑都放在 WeiboApi 中
     * @var
     */
    public function _initialize() {

    }

    //展现项目详情页面
    public function detail() {

      $id = I('get.id');

      $data = D('Project/Project')->get($id);
      $data['competitive_edge']=json_decode($data['competitive_edge'],true);
      $data['market_research']=json_decode($data['market_research'],true);
      $data['duibiao_comp']=json_decode($data['duibiao_comp'],true);
      $data['need_res']=json_decode($data['need_res'],true);

      $growdata  = M('project_growdata')->where('proid='.$data['project_id'])->select(); //成长数据
      if(!empty($growdata)){
           foreach($growdata as $key=>$value){
               $growdata[$key]['time'] =date('Y年m月d日' ,$value['time']);
           }
      }
      $product = M('project_producttest')->where('proid='.$data['project_id'])->find();

      $news = M('project_news')->where('proid='.$data['project_id'])->select();
        if(!empty($news)){
           foreach($news as $key=>$value){
               $news[$key]['time'] = date('Y-m-d',$value['create_time']);
           }
        }
      $event = M('project_event')->where('proid='.$data['project_id'])->select();
        if(!empty($event)){
            foreach($event as $key=>$value){
                $event[$key]['time'] = date('Y-m-d',$value['create_time']);
            }
        }
      $teamers = M('project_coreteamer')->where('proid='.$data['project_id'])->select();

        if(!empty($teamers)){
            foreach($teamers as $key=>$value){
                $teamers[$key]['path'] = M('picture')->where('id='.$value['avatar'])->getField('path')?M('picture')->where('id='.$value['avatar'])->getField('path'):'/Uploads/Picture/default/default_avatar_128_128.jpg';
                $teamers[$key]['education']=M('project_coreteamer_education')->where("coreteamer_id=".$teamers[$key]['id'])->select();
                $teamers[$key]['work_experience']=M('project_coreteamer_experience')->where("coreteamer_id=".$teamers[$key]['id'])->select();
            }
        }

      $user = M('member')->where('uid='.$data['uid'])->find();
      $user['avatar'] = '/Uploads/Avatar'.M('avatar')->where('uid='.$data['uid'])->getField('path');

      $test_product =M('project_producttest')->where("proid=".$data['project_id'])->getField('pro_images');
      $test_product_paths =array();
      if(!empty($test_product)){
          if(strpos($test_product,',')){
             $arr=explode(',',$test_product);
             foreach($arr as $value){
                $test_product_paths[]=$this->GetImgPath($value);
             }
          }else{
              $test_product_paths[]=$this->GetImgPath($test_product);
          }
      }

      $data['category']=M('category')->where("id=".$data['category'])->getField('category_name');
      $data['pro_logo']=$this->GetImgPath($data['pro_logo']);
      $this->assign('product_test',$test_product_paths);
      $this->assign('user',$user);
      $this->assign('teamers',$teamers);
      $this->assign('event',$event);
      $this->assign('news',$news);
      $this->assign('product',$product);
      $this->assign('growdata',$growdata);
      $this->assign('project',$data);
      $this->display();
    }

    //展现项目列表页面
    public function index() {
        $category = I('get.category');

        if(!empty($category) && $category!=1){
            $map['haizhi_project.category'] = array('eq', $category);
        }else{
            $category=1;
        }

        $model = D('Project/Project');
        $map['haizhi_project.status'] = array('eq', 1);

        $count = $model->where($map)->count();
        $Page       = new \Think\Page($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出

        $lists =$model->projects($map,$Page);
        if(!empty($lists)){
            $lists= int2time($lists);
            foreach($lists as $key =>$value){
                $lists[$key]['User_img_path'] = '/Uploads/Avatar'.M('avatar')->where('Uid='.$value['uid'])->getField('path');
                $lists[$key]['last_time'] = timeDifference($value['project_create_time']);
                $lists[$key]['pro_logo'] = M('picture')->where('id='.$value['pro_logo'])->getField('path');
                $lists[$key]['core_teamer'] =M('project_coreteamer')->join("LEFT JOIN haizhi_picture ON  haizhi_picture.id=haizhi_project_coreteamer.avatar")
                    ->where("haizhi_project_coreteamer.proid=".$value['project_id'])->limit('0,3')->select();
            }
        }
        $categorylist = M('category')->select();
        $this->assign('category',$categorylist);
        $this->assign('categorynum',$category);
        $this->assign('_page',$show);
        $this->assign('list',$lists);
        $this->display();
    }

    private function GetImgPath($id){

      return  M('picture')->where('id='.$id)->getField('path');

    }

}