<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-5-28
 * Time: 下午01:31
 * @author 郑钟良<zzl@ourstu.com>
 */

namespace Admin\Controller;


use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;

class HelpcenterController extends AdminController{

    protected $helpCenterModel;
    protected $helpCenterCategoryModel;

    function _initialize()
    {
        parent::_initialize();
        $this->helpCenterModel = D('Helpcenter/Helpcenter');
        $this->helpCenterCategoryModel = D('Helpcenter/HelpcenterCategory');
    }

    /**
     * 单页分类
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function helpCenterCategory()
    {
        //显示页面
        $builder = new AdminListBuilder();

        $list=$this->helpCenterCategoryModel->getCategoryList(array('status'=>array('egt',0)));

        $builder->title(L('_PAPER_CATEGORY_MANAGER_'))
            ->suggest(L('_PAPER_SUGGEST_'))
            ->setStatusUrl(U('Helpcenter/setCategoryStatus'))
            ->buttonNew(U('Helpcenter/editCategory'))
            ->buttonEnable()->buttonDisable()->buttonDelete()
            ->keyId()
            ->keyText('title',L('_CATEGORY_NAME_'))
            ->keyText('sort',L('_SORT_'))
            ->keyStatus('status',L('_STATUS_'))
            ->keyDoActionEdit('Helpcenter/editCategory?id=###')
            ->data($list)
            ->display();
    }

    /**分类编辑
     * @param int $id
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function editCategory($id = 0)
    {
        $title=$id?L("_EDIT_"):L("_ADD_");
        if (IS_POST) {
            if ($this->helpCenterCategoryModel->editData()) {
                $this->success($title.L('_SUCCESS_'), U('Helpcenter/HelpcenterCategory'));
            } else {
                $this->error($title.L('_FAIL_').$this->helpCenterCategoryModel->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            if ($id != 0) {
                $data = $this->helpCenterCategoryModel->find($id);
            }
            $builder->title($title.L('_CATEGORY_'))
                ->data($data)
                ->keyId()->keyText('title', L('_TITLE_'))
                ->keyInteger('sort',L('_SORT_'))->keyDefault('sort',0)
                ->keyStatus()->keyDefault('status',1)
                ->buttonSubmit(U('Helpcenter/editCategory'))->buttonBack()
                ->display();
        }

    }

    /**
     * 设置文章分类状态：删除=-1，禁用=0，启用=1
     * @param $ids
     * @param $status
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function setCategoryStatus($ids, $status)
    {
        !is_array($ids)&&$ids=explode(',',$ids);
        if($status==-1){
            if(in_array(1,$ids)){
                $this->error(L('_TIP_DELETE_CATEGORY_'));
            }
            $map['category']=array('in',$ids);
            $this->helpCenterModel->where($map)->setField('category',1);
        }
        $builder = new AdminListBuilder();
        $builder->doSetStatus('HelpcenterCategory', $ids, $status);
    }

    //分类管理end

    /**
     * 单页配置
     * @author 郑钟良<zzl@ourstu.com>\
     */
    public function config()
    {
        $builder=new AdminConfigBuilder();
        $data=$builder->handleConfig();

        $builder->title(L('_PAPER_BASIC_CONF_'))
            ->data($data);

        $builder->keyText('PAPER_CATEGORY_TITLE',L('_PAPER_TOP_TITLE_'))->keyDefault('PAPER_CATEGORY_TITLE',L('_PAPER_INTRO_'))
            ->buttonSubmit()->buttonBack()
            ->display();
    }


    //文章文章列表start
    public function index($page=1,$r=20)
    {
        $aCate=I('cate',0,'intval');
        if($aCate==-1){
            $map['category']=0;
        }else if($aCate!=0){
            $map['category']=$aCate;
        }
        $map['status']=array('neq',-1);

        list($list,$totalCount)=$this->helpCenterModel->getListByPage($map,$page,'sort asc,update_time desc','*',$r);
        $category=$this->helpCenterCategoryModel->getCategoryList(array('status'=>array('egt',0)));
        $category=array_combine(array_column($category,'id'),$category);
        foreach($list as &$val){
            if($val['category']){
                $val['category']='['.$val['category'].'] '.$category[$val['category']]['title'];
            }else{
                $val['category']=L('_NOT_CATEGORIZED_');
            }
        }
        unset($val);

        $optCategory=$category;
        foreach($optCategory as &$val){
            $val['value']=$val['title'];
        }
        unset($val);

        $builder=new AdminListBuilder();
        $builder->title(L('_PAPER_LIST_'))
            ->data($list)
            ->buttonNew(U('Helpcenter/editHelpcenter'))
            ->setStatusUrl(U('Helpcenter/setHelpcenterStatus'))
            ->buttonEnable()->buttonDisable()->buttonDelete()
            ->setSelectPostUrl(U('Admin/Helpcenter/index'))
            ->select('','cate','select','','','',array_merge(array(array('id'=>0,'value'=>L('_EVERYTHING_'))),$optCategory,array(array('id'=>-1,'value'=>L('_NOT_CATEGORIZED_')))))
            ->keyId()->keyUid()->keyLink('title',L('_TITLE_'),'Helpcenter/Index/index?id=###')->keyText('category',L('_CATEGORY_'),L('_OPTIONAL_'))->keyText('sort',L('_SORT_'))
            ->keyStatus()->keyCreateTime()->keyUpdateTime()
            ->keyDoActionEdit('Helpcenter/editHelpcenter?id=###')
            ->pagination($totalCount,$r)
            ->display();
    }

    public function setHelpcenterStatus($ids,$status=1)
    {
        !is_array($ids)&&$ids=explode(',',$ids);
        $builder = new AdminListBuilder();
        $builder->doSetStatus('Helpcenter', $ids, $status);
    }

    /**
     * 编辑单页文章
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function editHelpcenter()
    {
        $aId=I('id',0,'intval');
        $title=$aId?L("_EDIT_"):L("_ADD_");
        if(IS_POST){
            $aId&&$data['id']=$aId;
            $data['uid']=I('post.uid',get_uid(),'intval');
            $data['title']=I('post.title','','text');
            $data['content']=$_POST['content'];
            $data['category']=I('post.category',0,'intval');
            $data['sort']=I('post.sort',0,'intval');
            $data['status']=I('post.status',1,'intval');
            if(!mb_strlen($data['title'],'utf-8')){
                $this->error(L('_TIP_TITLE_EMPTY_'));
            }
            $result=$this->helpCenterModel->editData($data);
            if($result){
                $aId=$aId?$aId:$result;
                $this->success($title.L('_SUCCESS_'),U('Helpcenter/editHelpcenter',array('id'=>$aId)));
            }else{
                $this->error($title.L('_FAIL_'),$this->helpCenterModel->getError());
            }
        }else{
            if($aId){
                $data=$this->helpCenterModel->find($aId);
            }
            $category=$this->helpCenterCategoryModel->getCategoryList(array('status'=>array('egt',-1)));
            $options=array(0=>L('_NO_CATEGORY_'));
            foreach($category as $val){
                $options[$val['id']]=$val['title'];
            }
            $builder=new AdminConfigBuilder();
            $builder->title($title.L('_NEWS_'))
                ->data($data)
                ->keyId()
                ->keyReadOnly('uid',L('_PUBLISHER_'))->keyDefault('uid',get_uid())
                ->keyText('title',L('_TITLE_'))
                ->keyEditor('content',L('_CONTENT_'),'','all',array('width' => '850px', 'height' => '600px'))
                ->keySelect('category',L('_CATEGORY_'),'',$options)
                ->keyInteger('sort',L('_SORT_'))->keyDefault('sort',0)
                ->keyStatus()->keyDefault('status',1)
                ->buttonSubmit()->buttonBack()
                ->display();
        }
    }
} 