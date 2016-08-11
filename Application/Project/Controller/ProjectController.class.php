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
use Project\Model\ProjectModel;


class ProjectController extends AdminController
{
    protected $projectmodel;

    function _initialize()
    {
        $this->projectmodel = M('project');
        parent::_initialize();
    }
    //待审核项目管理
    public function index(){
            $projectname = I('name', '', 'text')?I('name', '', 'text'):'';
            $map['haizhi_project.status'] = array('in', '0,-2');
            if (is_numeric($projectname)) {
                $map['haizhi_project.id|haizhi_project.name'] = array(intval($projectname), array('like', '%' . $projectname . '%'), '_multi' => true);
            } else {
                if ($projectname !== '') {
                    $map['haizhi_project.name'] = array('like', '%' . (string)$projectname . '%');
                }
            }
            $projectmodel = new ProjectModel();
            $count = $projectmodel->where('status=0')->count();
            $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
            $show       = $Page->show();// 分页显示输出
            $lists =$projectmodel->projects($map,$Page);
            $lists= int2time($lists);
            $this->assign('_list', $lists);
            $this->assign('_page',$show);// 赋值分页输出
            $this->display(T('Project@Project/index'));
    }
    //已发布项目管理
    public function auditmanager(){
        $projectname = I('name', '', 'text')?I('name', '', 'text'):'';
        $map['haizhi_project.status'] = array('eq', 1);
        if (is_numeric($projectname)) {
            $map['haizhi_project.id|haizhi_project.name'] = array(intval($projectname), array('like', '%' . $projectname . '%'), '_multi' => true);
        } else {
            if ($projectname !== '') {
                $map['haizhi_project.name'] = array('like', '%' . (string)$projectname . '%');
            }
        }
        $projectmodel = new ProjectModel();
        $count = $projectmodel->where('status=1')->count();
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $lists =$projectmodel->projects($map,$Page);
        $lists= int2time($lists);
        $this->assign('_list', $lists);
        $this->assign('_page',$show);// 赋值分页输出
        $this->display(T('Project@Project/auditmanager'));
    }
    //项目修改
    public function update(){
        if(IS_POST){
            $projectid =I('get.id');
            $this->checkaudit($projectid);
            $login_user_info = session('user_auth'); // 当前登录用户信息
            $pro_data= I('post.');
            $pro_data['uid'] =$login_user_info['uid'];
            $model = new ProjectModel();
            if($model->where("id=%d",$projectid)->save($pro_data)){
                $this->ajaxReturn(array('info'=>L('_PROJECT_SAVE_SUCESS_'),
                    'status'=>1,'url'=>U('index')
                ));
            }else{
                $this->ajaxReturn(
                    array('info'=>'已经是最近数据了',  //$model->getError()
                        'status'=>0,'url'=>U('index')
                    ));
            }
        }else{

        $id = I('id');
        $data =  $this->projectmodel->where("id=$id")->find();
        $builder = new AdminConfigBuilder();
        $model = M('category');
        $modelres = M('resource');
        $res = $modelres->getField('id,name');
        $categorys = $model->getField('id,category_name');
            $builder->title('项目修改');
            $builder->keySingleImage('pro_logo', '项目logo', '项目logo建议上传尺寸为400*500尺寸');
            $builder->keySingleImage('cover_img', '项目封面', '项目封面建议上传尺寸为500*400尺寸');
            $builder->keyRadio('category', '项目类别', '',$categorys);
            $builder->keyCheckBox('need_res','所需资源',null,$res);
            $builder->keyText('name', '项目名称','必须填写');
            $builder->keyText('shortdesc', '项目简述','描述，为了让别人更好的认识你的项目');
            $builder->keyEditor('description', '项目描述', '项目描述，简单介绍一下你的项目');
            $builder->keyText('video_url', '项目展示视频地址','相关视屏介绍项目');
            $builder->keyEditor('othercoreteamers', '项目核心成员', '优秀的团队优秀的成员');
            $builder->keyEditor('proinstruction', '产品介绍', '简单描述一下项目产品信息');
            $builder->keyEditor('market_research', '市场调研', '项目市场调研情况介绍');
            $builder->keyEditor('competitive_edge', '竞争优势', '项目有何竞争优势？');
            $builder->keyEditor('business_model', '商业模式', '商业模式是什么？');
            $builder->keyCheckBox('own_res','已拥有资源',null,$res);
            $builder->keySingleImage('business_image', '营业执照', '公司营业执照正面照');
            $builder->keyText('organizationcode', '组织机构代码','');
            $builder->keyText('yingyecode', '营业执照代码','');
            $builder->keyText('taxcode', '税务登记证号','');
            $builder->group('项目基本信息','pro_logo,cover_img,category,need_res,name,shortdesc,description,video_url,own_res');
            $builder->group('项目成员信息','othercoreteamers,proinstruction,market_research,competitive_edge,business_model');
            $builder->group('项目其他相关信息','business_image,organizationcode,yingyecode,taxcode');
        $builder->data($data);
        $builder->buttonSubmit();
        $builder->display();
        }
    }
    //项目新增
    public function add(){
        if(IS_POST){
            $login_user_info = session('user_auth'); // 当前登录用户信息
            $pro_data= I('post.');
            $pro_data['uid'] =$login_user_info['uid'];
            $model = new ProjectModel();
            //配合亮哥适应前端项目新增
        if($model->create($pro_data)){
            $pro_data['status'] =0;
            $pro_data['create_time'] =time();
            $pro_data['update_time'] =time();
            $model->add($pro_data);
            $this->ajaxReturn(array('info'=>L('_PROJECT_ADD_SUCESS_'),
                                    'status'=>1,'url'=>U('index')
                                   ));
        }else{
            $this->ajaxReturn(
                array('info'=>$model->getError(),
                      'status'=>0
                 ));
             }
        }else{
            $builder = new AdminConfigBuilder();
            $model = M('category');
            $modelres = M('resource');
            $res = $modelres->getField('id,name');
            $categorys = $model->getField('id,category_name');
            $builder->title('项目新增');
            $builder->keySingleImage('pro_logo', '项目logo', '项目logo建议上传尺寸为400*500尺寸');
            $builder->keySingleImage('cover_img', '项目封面', '项目封面建议上传尺寸为500*400尺寸');
            $builder->keyRadio('category', '项目类别', '',$categorys);
//            $builder->keyCheckBox('need_res','所需资源',null,$res);
            $builder->keyText('name', '项目名称','必须填写');
            $builder->keyCity('address','地点','项目所在城市');
            $builder->keySelect('stage','项目阶段','',
                                array(''=>'请选择所属阶段',
                                      '概念阶段'=>'概念阶段',
                                      '研发阶段'=>'研发阶段',
                                       '正式发布'=>'正式发布',
                                       '已有用户'=>'已有用户',
                                       '已有收入'=>'已有收入'));
            $builder->keyText('shortdesc', '一句话介绍','描述，为了让别人更好的认识你的项目');
            $builder->keyTextArea('description', '项目描述', '项目描述，简单介绍一下你的项目');
            $builder->keyTextArea('market_research', '市场调研', '项目市场调研情况介绍');
            $builder->keyTextArea('competitive_edge', '竞争优势', '项目有何竞争优势？');
            $builder->keyTextArea('business_model', '商业模式', '商业模式是什么？');
            $builder->keyCheckBox('own_res','已拥有资源',null,$res);
            $builder->keySingleImage('business_image', '营业执照', '公司营业执照正面照');
            $builder->keyText('video_url', '项目展示视频地址','相关视屏介绍项目');
            $builder->group('项目基本信息','address,pro_logo,cover_img,category,need_res,name,shortdesc,description,video_url,own_res,stage,uid');
            $builder->group('项目成员信息','othercoreteamers,proinstruction,market_research,competitive_edge,business_model');
            $builder->keyHidden('uid',' ');
            $builder->data(array('uid'=>is_login()));
            $builder->buttonSubmit();
            $builder->display();
        }
    }
    //项目删除(页面展示)
    public function prorestore($page=1, $r=10){
        $map = array('status' => -1);
        $model = M('Project');
        $list = $model->where($map)->page($page, $r)->select();
        foreach($list as $key=>$value){
            $list[$key]['status'] ='已删除';
        }
        $totalCount = $model->where($map)->count();
        //显示页面
        $builder = new AdminListBuilder();
        $builder->title('项目回收站')
            ->buttonDisable(U('restore'), '还原') //项目还原
            ->keyId()
            ->keyLink('name', '项目名称', 'Project/Index/ProjectDetail?id=###')
            ->keyUid('uid','创建者')
//            ->keyHtml('description','项目描述')
            ->keyCreateTime()
            ->keyText('status','项目状态')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    //项目审核通过发布(将status置为 1)
    public function audit(){
        $ids= I('id');
        if(count($ids)>1){
            $this->ajaxReturn(array('info'=>'暂不支持多条记录审核，请重新勾选','status'=>1));
        }
         foreach($ids as $value){
             $projectmodel = M('project');
             $projectmodel->status = 1; //项目发布 status 置为1
             if(!$projectmodel->where("id=%d",$value)->save()){
                 $this->ajaxReturn(array('info'=>L('_PROJECT_AUDIT_FAILUE_'),'status'=>0));
             }
         }
        $this->ajaxReturn(array('info'=>L('_PROJECT_AUDIT_SUCESS_'),'status'=>1));
    }

    //项目删除(将status置为 -1)
    public function setstatus(){
        $ids= I('id');
        foreach($ids as $value){
            $projectmodel = M('project');
            $projectmodel->status = -1; //项目发布 status 置为1
            if(!$projectmodel->where("id=%d",$value)->save()){
                $this->ajaxReturn(array('info'=>L('_PROJECT_DELETE_FAILUE_'),'status'=>0));
            }
        }
        $this->ajaxReturn(array('info'=>L('_PROJECT_DELETE_SUCESS_'),'status'=>1));
    }

    //项目还原(将status置为 0)
    public function restore(){
        $ids= I('ids');
        foreach($ids as $value){
            $projectmodel = M('project');
            $projectmodel->status = 0; //项目发布 status 置为1
            if(!$projectmodel->where("id=%d",$value)->save()){
                $this->ajaxReturn(array('info'=>L('_PROJECT_RESTORE_FAILUE_'),'status'=>0));
            }
        }
        $this->ajaxReturn(array('info'=>L('_PROJECT_RESTORE_SUCESS_'),'status'=>1));
    }
    //项目类别列表
    public function categorylist($page = 1, $r = 10){
        //读取列
        $model = M('category');
        $list = $model->page($page, $r)->select();
        $totalCount = $model->count();
        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';
        $builder->title('项目行业管理')
            ->buttonNew(U('addcategory'),'新增行业')
            ->ajaxButton(U('deletecategory'),'','删除')
            ->keyId()->key('category_name','行业类别','label')
            ->key('category_code','行业类别编码','label')
            ->pagination($totalCount, $r)
            ->data($list)
            ->display();
        unset($list);
        unset($totalCount);
    }
    //新增行业类别
    public function addcategory(){

        if(IS_POST){
            $categoryName = I('name');
            $category_code =I('code');
            if($categoryName =='' || $category_code==''){
                    $this->ajaxReturn(array('info'=>L('_PROJECT_CATEGORY_ADD_FAILUE_'),'status'=>0));
            }else{
                $model = M('category');
                if($model->add(array('category_name'=>$categoryName,'category_code'=>$category_code))){
                    $this->ajaxReturn(array('info'=>L('_PROJECT_CATEGORY_ADD_SUCCESSS_'),'status'=>1));
                }else{
                    $this->ajaxReturn(array('info'=>$model->getError(),'status'=>0));
                }
            }
        }else{
            $builder = new AdminConfigBuilder();
            $builder->title('项目类别新增');
            $builder->keyText('name', '行业名称','(必须填写)');
            $builder->keyText('code', '行业名称编码','(必须填写)');
            $builder->buttonSubmit();
            $builder->display();
        }
    }
    //删除项目行业类别
    public function deletecategory($ids){
        if(!empty($ids)){
           foreach($ids as $key=>$value ){
               $model = M('category');
               $model->delete($value);
               unset($model);
           }
            $this->ajaxReturn(array('info'=>L('_PROJECT_CATEGORY_delete_SUCCESSS_'),'status'=>1));
        }
    }


/*************** kengdie de 项目附加信息 核心成员信息********************/
    public function coreteamer(){
        $projectid = I('get.id');
        $model = M('project_coreteamer');
        $count = $model->where("proid=%d",$projectid)->count();
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $lists =$model->where("proid=%d",$projectid)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $lists);
        $this->assign('_page',$show);// 赋值分页输出
        $this->assign('projectid',$projectid);
        $this->display(T('Project@Project/coreteamer'));
    }

    public function addCoreteam(){
        if(IS_POST){
            $data = I('post.');
            $id = I('get.id');
            $data['proid'] =$id;
            if(M('project_coreteamer')->add($data)){
               $this->ajaxReturn(array('info'=>'新增成功','status'=>1,'url'=>U('coreteamer',array('id'=>$id))));
            }
              $this->ajaxReturn(array('info'=>'新增失败','status'=>0));
        }else{
            $builder = new AdminConfigBuilder();
            $builder->title('新增项目核心成员')->keySingleImage('avatar','核心成员头像')
                    ->keyText('name','核心成员真实姓名')->keyText('position','项目职位名称')
                    ->keyText('email','常用邮箱')->keyText('address','所在地')->keyTextArea('self_instruction','个人介绍');
            $builder->buttonSubmit();
            $builder->display();
        }
    }

    public function delCoreteam(){
      $ids = I('post.ids');
      foreach($ids as $value){
          M('project_coreteamer')->where("id=%d",$value)->delete();
      }
        $this->ajaxReturn(array('info'=>'删除成功','status'=>1));
    }

    public function coreteamEducation($page = 1, $r = 10){
        $coreteamid = I('get.id');
        $model = M('project_coreteamer_education');
        $list = $model->where('coreteamer_id=%d',$coreteamid)->page($page, $r)->select();
        $totalCount = $model->count();
        //显示页面
        $builder = new AdminListBuilder();
        $builder->title('核心成员教育经历')
            ->buttonNew(U('addCoreteamEducation',array('id'=>$coreteamid)),'新增教育经历')
            ->ajaxButton(U('delCoreteamEducation'),'','删除教育经历')
            ->keyId()->key('school','学校','label')
            ->key('degree','学位','label')
            ->pagination($totalCount, $r)
            ->data($list)
            ->display();
        unset($list);
        unset($totalCount);


    }

     public function addCoreteamEducation(){
         if(IS_POST){
             $coreteamId = I('get.id');
             $data  = I('post.');
             $data['coreteamer_id'] = $coreteamId;
             if(M('project_coreteamer_education')->add($data)){
                 $this->ajaxReturn(array('info'=>'新增成功','status'=>1,'url'=>U('coreteamEducation',array('id'=>$coreteamId))));
             }
             $this->ajaxReturn(array('info'=>'新增失败','status'=>0));
         }else{
             $builder = new AdminConfigBuilder();
             $builder ->title('新增核心成员教育经历');
             $builder->keyText('school','学校')->keyText('degree','学位');
             $builder->buttonSubmit();
             $builder->display();
         }
     }

     public function delCoreteamEducation(){

         $ids = I('ids');
         foreach($ids as $value ){
             M('project_coreteamer_education')->where("id=%d",$value)->delete();
         }
         $this->ajaxReturn(array('info'=>'删除成功','status'=>1));
     }

    public function coreteamExperience($page = 1, $r = 10){
        $coreteamid = I('get.id');
        $model = M('project_coreteamer_experience');
        $list = $model->where("coreteamer_id=%d",$coreteamid)->page($page, $r)->select();
        $totalCount = $model->where('coreteamer_id='.$coreteamid)->count();
        //显示页面
        $builder = new AdminListBuilder();
        $builder->title('核心成员工作经历')
            ->buttonNew(U('addCoreteamExperience',array('id'=>$coreteamid)),'新增工作经历')
            ->ajaxButton(U('delCoreteamExperience'),'','删除工作经历')
            ->keyId()->key('company','公司','label')
            ->key('e_position','职位','label')
            ->pagination($totalCount, $r)
            ->data($list)
            ->display();
        unset($list);
        unset($totalCount);


    }

    public function addCoreteamExperience(){
        if(IS_POST){
            $coreteamId = I('get.id');
            $data  = I('post.');
            $data['coreteamer_id'] = $coreteamId;
            if(M('project_coreteamer_experience')->add($data)){
                $this->ajaxReturn(array('info'=>'新增成功','status'=>1,'url'=>U('coreteamExperience',array('id'=>$coreteamId))));
            }
            $this->ajaxReturn(array('info'=>'新增失败','status'=>0));
        }else{
            $builder = new AdminConfigBuilder();
            $builder ->title('新增核心成员工作经历');
            $builder->keyText('company','公司')->keyText('e_position','职位');
            $builder->buttonSubmit();
            $builder->display();
        }
    }

    public function delCoreteamExperience(){

        $ids = I('ids');
        foreach($ids as $value ){
            M('project_coreteamer_experience')->where("id=%d",$value)->delete();
        }
        $this->ajaxReturn(array('info'=>'删除成功','status'=>1));
    }

/********************* 项目大纪事********************************/
    public function event($page = 1, $r = 10){
        $projectid = I('get.id');
        $model = M('project_event');
        $list = $model->where('proid=%d',$projectid)->page($page, $r)->select();
        $totalCount = $model->where('proid=%d',$projectid)->count();
        //显示页面
        $builder = new AdminListBuilder();
        $builder->title('项目大纪事')
            ->buttonNew(U('addevent',array('id'=>$projectid)),'新增项目大纪事')
            ->ajaxButton(U('delevent'),'','删除项目大纪事')
            ->keyId()->keyCreateTime('create_time','时间')
            ->key('content','大纪事内容','label')
            ->pagination($totalCount, $r)
            ->data($list)
            ->display();
        unset($list);
        unset($totalCount);
    }

    public function addevent(){
        if(IS_POST){
            $id = I('get.id');
            $data = I('post.');
            $data['proid']=$id;
            if(M('project_event')->add($data)){
               $this->ajaxReturn(array('info'=>'新增成功','status'=>1,'url'=>U('event',array('id'=>$id))));
            }else{
               $this->ajaxReturn(array('info'=>'新增失败','status'=>0,'url'=>U('event',array('id'=>$id))));
            }

        }else{
         $builder = new AdminConfigBuilder();
         $builder->title('添加新闻大纪事');
         $builder->keyCreateTime('create_time','时间')->keyEditor('content','大纪事内容');
         $builder->buttonSubmit();
         $builder->display();
        }

    }

    public function delevent(){

        $ids = I('ids');
        foreach($ids as $key=>$value){
            M('project_event')->where("id=%d",$value)->delete();
        }
        $this->ajaxReturn(array('info'=>'删除成功','status'=>1));
    }
    /*****************************成长数据********************************/
    public function growdata($page = 1, $r = 10){
        $proid = I('get.id');
        $model = M('project_growdata');
        $list = $model->where('proid=%d',$proid)->page($page, $r)->select();
        $totalCount = $model->where('proid=%d',$proid)->count();
        $builder = new AdminListBuilder();
        $builder->title('项目成长数据')
            ->buttonNew(U('addGrowdata',array('id'=>$proid)),'新增项目成长数据')
            ->ajaxButton(U('delGrowdata'),'','删除项目成长数据')
            ->keyId()->keyCreateTime('time','时间')
            ->key('category','类别','label')
            ->key('data','数据','label')
            ->pagination($totalCount, $r)
            ->data($list)
            ->display();
        unset($list);
        unset($totalCount);
    }

    public function addGrowdata(){
        if(IS_POST){
            $proId = I('get.id');
            $data  = I('post.');
            $data['proid'] = $proId;
            if(M('project_growdata')->add($data)){
                $this->ajaxReturn(array('info'=>'新增成功','status'=>1,'url'=>U('growdata',array('id'=>$proId))));
            }
            $this->ajaxReturn(array('info'=>'新增失败','status'=>0));
        }else{
            $arr = array(
                '营业额'=>'营业额',
                '下载量'=>'下载量',
                '注册用户量'=>'注册用户量',
                '日活'=>'日活',
                '付费用户数'=>'付费用户数',
                'PV'=>'PV',
                '合作商家'=>'合作商家',
                '净利润'=>'净利润',
                '收入'=>'收入',
                'UV'=>'UV',
                '订单数'=>'订单数',
                '用户平均停留时间'=>'用户平均停留时间',
                '独立IP'=>'独立IP',
                '转化率'=>'转化率',
                '活跃用户数'=>'活跃用户数',
            );
            $builder = new AdminConfigBuilder();
            $builder ->title('新增项目成长数据');
            $builder->keyCreateTime('time','时间')
                    ->keySelect('category','成长数据类别','',$arr)
                    ->keyText('data','数据');
            $builder->buttonSubmit();
            $builder->display();
        }
    }

    public function delGrowdata(){

        $ids = I('ids');
        foreach($ids as $value ){
            M('project_growdata')->where('id=%d',$value)->delete();
        }
        $this->ajaxReturn(array('info'=>'删除成功','status'=>1));
    }
    /*****************************新闻 *************************************/
    public function news($page = 1, $r = 10){
        $proid = I('get.id');
        $model = M('project_news');
        $list = $model->where('proid=%d',$proid)->page($page, $r)->select();
        $totalCount = $model->where('proid=%d',$proid)->count();
        $builder = new AdminListBuilder();
        $builder->title('项目新闻报道')
            ->buttonNew(U('addNew',array('id'=>$proid)),'新增项目新闻')
            ->ajaxButton(U('delNew'),'','删除项目新闻')
            ->keyId()->keyCreateTime('create_time','时间')
            ->key('new_website','新闻链接','label')
            ->key('new_title','新闻标题','label')
            ->keyYesNo('application','是否申请海智汇媒体报道')
            ->pagination($totalCount, $r)
            ->data($list)
            ->display();
        unset($list);
        unset($totalCount);
    }

    public function addNew(){
        if(IS_POST){
            $id = I('get.id');
            $data = I('post.');
            $data['proid']=$id;
            if(M('project_news')->add($data)){
                $this->ajaxReturn(array('info'=>'新增成功','status'=>1,'url'=>U('news',array('id'=>$id))));
            }else{
                $this->ajaxReturn(array('info'=>'新增失败','status'=>0,'url'=>U('news',array('id'=>$id))));
            }
        }else{
            $builder = new AdminConfigBuilder();
            $builder->title('添加项目新闻');
            $builder->keyCreateTime('create_time','时间')->keyText('new_website','新闻链接')->keyText('new_title','新闻标题');
            $builder->keySelect('application','是否申请海智汇媒体报道','',array('1'=>'申请','2'=>'不申请'));
            $builder->buttonSubmit();
            $builder->display();
        }

    }

    public function delNew(){

        $ids = I('ids');
        foreach($ids as $key=>$value){
            M('project_news')->where("id=%d",$value)->delete();
        }
        $this->ajaxReturn(array('info'=>'删除成功','status'=>1));
    }
    /*****************************产品测试 ******************************/
    public function producttest(){
        if(IS_POST){
         $proid = I('get.id');
         $data  = I('post.');
         $data['proid']=$proid;
         $model =M('project_producttest');
         if(empty($model->where("proid=%d",$proid)->find())){
             $model->add($data);
             $this->ajaxReturn(array('info'=>'成功','status'=>1,'url'=>U('producttest',array('id'=>$proid))));
         }else{
             if($model->where("proid=%d",$proid)->save($data)){
                 $this->ajaxReturn(array('info'=>'成功','status'=>1,'url'=>U('producttest',array('id'=>$proid))));
             }else{
                 $this->ajaxReturn(array('info'=>'信息未修改','status'=>0,'url'=>U('producttest',array('id'=>$proid))));
             }
         }
        }else{
          $proid = I('get.id');
          $data = M('project_producttest')->where("proid=%d",$proid)->find();
          $builder = new AdminConfigBuilder();
          $builder->title('项目产品测试');
          $builder ->keyText('weibo','官方微博')->keyText('wechat','官方微信')->keyText('website','官方站点')
                   ->keyText('apk','安卓端下载')->keyText('ios','ios端下载')
                   ->keyText('account','账号密码')->keyText('password','试用账号密码');
          $builder ->buttonSubmit('','保存');
          $builder->data($data);
          $builder->display();
        }
    }
    /********************************** 顾问导师*************************/
    public function teacher(){
        if(IS_POST){
            $proid = I('get.id');
            $data  = I('post.');
            $data['proid']=$proid;
            $model =M('project_teacher');
            if(empty($model->where("proid=%d",$proid)->find())){
                $model->add($data);
                $this->ajaxReturn(array('info'=>'成功','status'=>1,'url'=>U('teacher',array('id'=>$proid))));
            }else{
                if($model->where("proid=%d",$proid)->save($data)){
                    $this->ajaxReturn(array('info'=>'成功','status'=>1,'url'=>U('teacher',array('id'=>$proid))));
                }else{
                    $this->ajaxReturn(array('info'=>'信息未修改','status'=>0,'url'=>U('teacher',array('id'=>$proid))));
                }
            }
        }else{
            $proid = I('get.id');
            $data = M('project_teacher')->where("proid=%d",$proid)->find();
            $builder = new AdminConfigBuilder();
            $builder->title('项目产品测试');
            $builder ->keySingleImage('avatar','头像','请上传500*500的尺寸')->keyText('email','常用邮箱')->keyText('name','姓名')
                ->keyEditor('introduction','导师介绍');
            $builder ->buttonSubmit('','保存');
            $builder->data($data);
            $builder->display();
        }
    }
    /************************************ ******************************/
    //检查是否传递projectid 以及 项目状态是否为 0
    private function checkaudit($id){
        if(empty($id)){
            $this->ajaxReturn(array('status'=>0,'info'=>'请联系系统管理员'));
        }
        $projectstatus = M('project')->where("id=%d",$id)->getField('status');
        if($projectstatus != 0){
            $this->ajaxReturn(array('status'=>0,'info'=>L('_PROJECT_STATUS_NOTALLOW_')));
        }
    }
}
