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


class ArticleController extends AdminController
{
    protected $projectmodel;

    function _initialize()
    {
        parent::_initialize();
    }

    public function index(){

        $articlename = I('name', '', 'text')?I('name', '', 'text'):'';
        $map['haizhi_article.status'] = array('eq', 0);
        if (is_numeric($articlename)) {
            $map['haizhi_article.id|haizhi_article.title'] = array(intval($articlename), array('like', '%' . $articlename . '%'), '_multi' => true);
        } else {
            if ($articlename !== '') {
                $map['haizhi_article.title'] = array('like', '%' . (string)$articlename . '%');
            }
        }
        $articlemodel = new ArticleModel();
        $count = $articlemodel->where('ststus=0')->count();
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $lists = $articlemodel->GetArticles($map,$Page);
        $lists= int2time_article($lists);
        $this->assign('_list', $lists);
        $this->assign('_page',$show);// 赋值分页输出
        $this->display(T('Article@Article/index'));
    }
    //文章修改
    public function update(){
        if(IS_POST){
            $id =I('get.id');
            $articleModel = M('Expert');
            $articleData = I('post.');
            if($articleModel->where('id='.$id)->save($articleData)){
              $this->ajaxReturn(array('info'=>'新增文章成功','status'=>1));
            }else{
              $this->ajaxReturn(array('info'=>$articleModel->getError(),'status'=>0));
            }
        }else{
            $id = I('id');
            $data = M('article')->where('id='.$id)->find();
            $builder = new AdminConfigBuilder();
            $model = M('category');
            $categorys = $model->getField('id,category_name');
            $builder->title('文章修改');
            //$builder->keySingleImage('cover_img', '项目封面', '项目封面建议上传尺寸为500*400尺寸');
            $builder->keyMultiImage('article_img','文章封面');
            $builder->keyRadio('category', '文章类别类别', '',$categorys);
            $builder->keyText('title', '文章标题','必须填写');
            $builder->keyEditor('content', '文章内容描述', '');
            $builder->data($data);
            $builder->buttonSubmit();
            $builder->display();
        }
    }

    //文章新增
    public function add(){

        if(IS_POST){
         $articleModel = D('Expert/Expert');
         $articleData = I('post.');
         if($articleModel->create($articleData)){
            $result =  $articleModel->add();
            if($result){
                $this->ajaxReturn(array('info'=>'新增文章成功','status'=>1,'url'=>U('Expert/index')));
            }
         }else{
             $this->ajaxReturn(array('info'=>$articleModel->getError(),'status'=>0,'url'=>U('Expert/index')));
          }
        }else{
        $builder = new AdminConfigBuilder();
        $model = M('category');
        $categorys = $model->getField('id,category_name');
        $builder->title('文章新增');
        //$builder->keySingleImage('cover_img', '项目封面', '项目封面建议上传尺寸为500*400尺寸');
        $builder->keyMultiImage('article_img','文章封面');
        $builder->keyRadio('category', '文章类别类别', '',$categorys);
        $builder->keyText('title', '文章标题','必须填写');
        $builder->keyEditor('content', '文章内容描述', '');
        $builder->buttonSubmit();
        $builder->display();
        }
    }
    //文章删除
    public function delete(){
      $this->changeStatus(L('_ARTICLE_DELETE_SUCCESS_'),L('_ARTICLE_DELETE_FAILUE_'),-1);
    }

    //文章审核通过发布
    public function audit(){
      $this->changeStatus(L('_ARTICLE_AUDIT_SUCCESS_'),L('_ARTICLE_AUDIT_FAILUE_'),1);
    }

    //文章发布管理
    public function auditmanger(){

        $articlename = I('name', '', 'text')?I('name', '', 'text'):'';
        $map['haizhi_article.status'] = array('eq', 1);
        if (is_numeric($articlename)) {
            $map['haizhi_article.id|haizhi_article.title'] = array(intval($articlename), array('like', '%' . $articlename . '%'), '_multi' => true);
        } else {
            if ($articlename !== '') {
                $map['haizhi_article.title'] = array('like', '%' . (string)$articlename . '%');
            }
        }
        $articlemodel = new ArticleModel();
        $count = $articlemodel->where('ststus=0')->count();
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $lists = $articlemodel->GetArticles($map,$Page);
        $lists= int2time_article($lists);
        $this->assign('_list', $lists);
        $this->assign('_page',$show);// 赋值分页输出
        $this->display(T('Expert@Expert/auditmanager'));
    }

    //文章回收站
    public function recycle($page=1, $r=10){
            $map = array('status' => -1);
            $model = M('article');
            $list = $model->where($map)->page($page, $r)->select();

            foreach($list as $key=>$value){
                $list[$key]['status'] ='已删除';
            }
            $totalCount = $model->where($map)->count();

            $builder = new AdminListBuilder();
            $builder->title('文章回收站')
                ->buttonDisable(U('recycleStatus'), '还原') //项目还原
                ->keyId()
                ->keyLink('title', '文章标题', 'Expert/Index/ProjectDetail?id=###')
                ->keyUid('uid','创建者')
                ->keyCreateTime()
                ->keyText('status','文章状态')
                ->data($list)
                ->pagination($totalCount, $r)
                ->display();
    }

    //文章回收状态更改
    public function recycleStatus(){
       $this->changeStatus(L('_ARTICLE_RESTORE_SUCCESS_'),L('_ARTICLE_RESTORE_FAILUE_'),0);
    }

    private  function changeStatus($success ,$fail,$status){
        $ids= I('id');
        if(empty($ids)){
            $ids= I('ids');
        }
        foreach($ids as $value){
            $model = M('article');
            $model->status = $status;
            if(!$model->where("id=$value")->save()){
                $this->ajaxReturn(array('info'=>$fail,'status'=>0));
            }
        }
        $this->ajaxReturn(array('info'=>$success,'status'=>1));

    }

}
