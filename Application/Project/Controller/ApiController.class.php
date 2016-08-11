<?php


namespace Project\Controller;

use Think\Controller;
use Project\Model\ProjectModel;


class ApiController extends Controller {

    //展现项目详情接口
    public function detail() {
        $this->display();
    }

    //展现项目列表页面
    public function index() {

        $category = I('category');
        $skip = I('skip');
        $count = I('count');
        $search = I('search', '', 'text')?I('search', '', 'text'):'';
        $map['haizhi_project.status'] = array('eq', 1);
        $map['haizhi_project.category'] = array('eq', $category);
        if (is_numeric($search)) {
            $map['haizhi_project.id|haizhi_project.name'] = array(intval($search), array('like', '%' . $search . '%'), '_multi' => true);
        } else {
            if ($search !== '') {
                $map['haizhi_project.name'] = array('like', '%' . (string)$search . '%');
            }
        }
        $projectmodel = new ProjectModel();
        $lists =$projectmodel->projects($map,$skip,$count);
        if(!empty($lists)){
            $lists= int2time($lists);
            $this->ajaxReturn(array('info'=>'获取成功','status'=>1,'data'=>$lists));
        }else{
            $this->ajaxReturn(array('info'=>'获取成功','status'=>1,'data'=>array()));
        }
        $this->ajaxReturn(array('info'=>$projectmodel->getError().$projectmodel->getDbError(),'status'=>0));


    }

}