<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-11
 * Time: PM5:41
 */

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;
use Article\Model\ArticleModel;


class ExpertController extends AdminController
{
    protected $projectmodel;

    function _initialize()
    {
        parent::_initialize();
    }

    //未审核专家列表展示
    public function index(){
        $expertName = I('name', '', 'text')?I('name', '', 'text'):'';
        $map['status'] = array('eq', 0);
        if (is_numeric($expertName)) {
            $map['id|e_name'] = array(intval($expertName), array('like', '%' . $expertName . '%'), '_multi' => true);
        } else {
            if ($expertName !== '') {
                $map['e_name'] = array('like', '%' . (string)$expertName . '%');
            }
        }
        $Expertmodel = D('Expert/Expert');
        $count = $Expertmodel->where('status=0')->count();
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $lists =$Expertmodel->Lists($map,$Page);

        $lists= int2time($lists);
        $this->assign('_list', $lists);
        $this->assign('_page',$show);// 赋值分页输出

        $this->display(T('Expert@Expert/index'));
    }

    public function add(){
        $data =M('category')->select();
        $this->assign('tree',$data);
        $this->display(T('Expert@Expert/add'));
    }

    public function doAdd(){
      $model = D('Expert/Expert');
      if($model->create(I('post.'))){
          $model->add();
          $this->ajaxReturn(array('status'=>1,'info'=>'新建成功','url'=>U('index')));
      }else{
          $this->ajaxReturn(array('status'=>0,'info'=>$model->getError()));
        }
    }

    public function detail(){
        $this->display(T('Expert@Expert/detail'));
    }

    public function delete(){
        $this->changeStatus('删除成功','删除失败',-1);
    }

    public function audit(){
        $this->changeStatus('审核成功','审核失败',1);
    }

    public function education(){
        $expertId = I('id');
        $date = M('education')->where("otid=$expertId")->select();
        $builder = new AdminListBuilder();
        $builder->title('专家教育经历列表');
        $builder->buttonNew(U('addeducation',array('id'=>$expertId)),'新增教育经历');
        $builder->ajaxButton(U('deleducation',array('id'=>$expertId)),'','删除');
        $builder->keyId();
        $builder->keyCreateTime('begin_time','开始时间');
        $builder->keyCreateTime('end_time','结束时间');
        $builder->keyText('school','学校名称');
        $builder->keyText('major','专业名称');
        $builder->data($date);
        $builder->pagination(count($date), 10);
        $builder ->display();
    }

    public function addEducation(){
       if(IS_POST){
           $id = I('get.id');
           $data = I('post.');
           $data['otid']=$id;
           $model=M('education');
           if($model->add($data)){
               $this->ajaxReturn(array('status'=>1,'info'=>'新建成功','url'=>U('education',array('id'=>$id))));
           }else{
               $this->ajaxReturn(array('status'=>0,'info'=>$model->getError(),'url'=>U('education',array('id'=>$id))));
           }
       }else{
           $builder = new AdminConfigBuilder();
           $builder->title('教育经历新增');
           $builder->keyCreateTime('begin_time','开始时间');
           $builder->keyCreateTime('end_time','结束时间');
           $builder->keyText('school', '学校名称');
           $builder->keyText('major', '专业名称');
           $builder->buttonSubmit();
           $builder->display();
       }
    }

    public function deleducation(){
       $ids = I('post.ids');
        foreach($ids as $value){
           if(M('education')->delete($value)){

           }else{
             $this->ajaxReturn(array('info'=>'删除失败','status'=>0));
           }
        }
    }

    public function experience(){
        $expertId = I('id');
        $date = M('experience')->where("otid=$expertId")->select();
        $builder = new AdminListBuilder();
        $builder->title('专家教育经历列表');
        $builder->buttonNew(U('addexperience',array('id'=>$expertId)),'新增工作经历');
        $builder->ajaxButton(U('delexperience',array('id'=>$expertId)),'','删除');
        $builder->keyId();
        $builder->keyCreateTime('begin_time','开始时间');
        $builder->keyCreateTime('end_time','结束时间');
        $builder->keyText('company','公司名称');
        $builder->keyText('position','岗位');
        $builder->data($date);
        $builder->pagination(count($date), 10);
        $builder ->display();
    }

    public function addexperience(){
        if(IS_POST){
            $id = I('get.id');
            $data = I('post.');
            $data['otid']=$id;
            $model=M('experience');
            if($model->add($data)){
                $this->ajaxReturn(array('status'=>1,'info'=>'新建成功','url'=>U('experience',array('id'=>$id))));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>$model->getError(),'url'=>U('experience',array('id'=>$id))));
            }
        }else{
            $builder = new AdminConfigBuilder();
            $builder->title('工作经历新增');
            $builder->keyCreateTime('begin_time','开始时间');
            $builder->keyCreateTime('end_time','结束时间');
            $builder->keyText('company','公司名称');
            $builder->keyText('position','岗位');
            $builder->buttonSubmit();
            $builder->display();
        }
    }
    public function delexperience(){
        $ids = I('post.ids');
        foreach($ids as $value){
            if(M('experience')->delete($value)){
            }else{
                $this->ajaxReturn(array('info'=>'删除失败','status'=>0));
            }
        }
    }
    private  function changeStatus($success ,$fail,$status){
        $ids= I('id');
        if(empty($ids)){
            $ids= I('ids');
        }
        $model = M('expert');
        foreach($ids as $value){
            $model->status = $status;
            if(!$model->where("id=$value")->save()){
                $this->ajaxReturn(array('info'=>$fail,'status'=>0));
            }
        }
        $this->ajaxReturn(array('info'=>$success,'status'=>1));

    }
}
