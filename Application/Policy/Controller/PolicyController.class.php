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


class PolicyController extends AdminController {
    function _initialize()
    {
        parent::_initialize();
    }


    /**
     * 政策列表展示
     */
//    public function contents(){
//        $projectname = I('name', '', 'text')?I('name', '', 'text'):'';
//        $map['haizhi_project.status'] = array('in', '0,-2');
//        if (is_numeric($projectname)) {
//            $map['haizhi_project.id|haizhi_project.name'] = array(intval($projectname), array('like', '%' . $projectname . '%'), '_multi' => true);
//        } else {
//            if ($projectname !== '') {
//                $map['haizhi_project.name'] = array('like', '%' . (string)$projectname . '%');
//            }
//        }
//        $projectmodel = new ProjectModel();
//        $count = $projectmodel->where('status=0')->count();
//        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
//        $show       = $Page->show();// 分页显示输出
//        $lists =$projectmodel->projects($map,$Page);
//        $lists= int2time($lists);
//        $this->assign('_list', $lists);
//        $this->assign('_page',$show);// 赋值分页输出
//        $this->display(T('Policy@Policy/contents'));
//    }


    /**
     * 新增政策
     */
    public function policyAdd(){

    }


    /**
     * 修改政策
     */
    public function policyUpdate(){

    }


    /**
     * 删除政策
     */
    public function policyDelete(){

    }



}
