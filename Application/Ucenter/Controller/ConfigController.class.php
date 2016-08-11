<?php

namespace Ucenter\Controller;

use Project\Model\ProjectModel;
use Think\Controller;

class ConfigController extends BaseController {
    public function _initialize()
    {
        parent::_initialize();
        if (!is_login()) {
            $this->error(L('_ERROR_FIRST_LOGIN_'));
        }
        $this->accessAction();
        $this->setTitle(L('_DATA_EDIT_'));
        $this->_assignSelf();
        $this->_haveOtherRole();
    }

    final  protected function accessAction()
    {
        $actionmessage = M('action_message');
        $action = strtolower(MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME);
        $authdata = $actionmessage->where("action_name ='$action'")->find();
        if (!empty($authdata)) {
            //执行短信发送以及站内信发送
            if (strtolower(CONTROLLER_NAME) == 'project') {
                $data = $this->comeFromProject($authdata);
            } else {
                $data = $this->comeFromArticle($authdata);
            }

            if (!empty($data)) {
                //判断是否需要发送短信
                if ($authdata['is_sms'] == 1) {
                    hook('my_tags', $data);
                }
                //判断是否需要发送站内信
                if ($authdata['is_message'] == 1) {
                    $messageModel = D('Common/Message');
                    $messageModel->sendMessageWithoutCheckSelf($data['uid'], '', $data['message_template'], $action);
                }
                //判断是否需要发送邮件
                if ($authdata['is_email'] == 1) {
                    send_mail($data['useremail'], '海智之星', $data['email_template'], '海智之星');
                }
            }

        }
    }

    //判断如果是project 过来的 根据projectid获取相关用户信息，根据配置获取sms数据表中的信息
    final protected function comeFromProject($authdata)
    {
        if (IS_POST) {
            $uid = I('post.uid');
            $projectName = I('post.name');
            $id = I('id');
            if (empty($uid)) {
                $uid = M('project')->where('id=%d', $id[0])->getField('uid');
            }
            if (empty($projectName) && !empty($id)) {
                $projectName = M('project')->where('id=%d', $id[0])->getField('name');
            }
            $user = M('ucenter_member')->where('id=%d', $uid)->find();
            $data = array('username' => $user['username'], 'userphone' => $user['mobile'], 'useremail' => $user['email'], 'sms_template' => $authdata['sms_template'], 'message_template' => $authdata['message_template'], 'email_template' => $authdata['email_template'], 'uid' => $uid,);
            return $data;
        } else {
            return array();
        }
    }

    /**关联自己的信息
     *
     * @auth 陈一枭
     */
    private function _assignSelf()
    {
        $self = query_user(array('avatar128', 'nickname', 'space_url', 'space_link', 'score', 'title'));
        $this->assign('self', $self);
    }

    public function verify($id = 1)
    {
        verify($id);
    }

    /**
     * 是否拥有其他角色或可拥有角色
     *
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function _haveOtherRole()
    {
        $have = 0;

        $roleModel = D('Role');
        $userRoleModel = D('UserRole');

        $register_type = modC('REGISTER_TYPE', 'normal', 'Invite');
        $register_type = explode(',', $register_type);
        if (!in_array('invite', $register_type)) {//开启邀请注册
            $map['status'] = 1;
            $map['invite'] = 0;
            if ($roleModel->where($map)->count() > 1) {
                $have = 1;
            } else {
                $map_user['uid'] = is_login();
                $map_user['role_id'] = array('neq', get_login_role());
                $map_user['status'] = array('egt', 0);
                $role_ids = $userRoleModel->where($map_user)->field('role_id')->select();
                if ($role_ids) {
                    $role_ids = array_column($role_ids, 'role_id');
                    $map_can['status'] = 1;
                    $map_can['id'] = array('in', $role_ids);
                    if ($roleModel->where($map_can)->count()) {
                        $have = 1;
                    }
                }
            }
        } else {
            $map['status'] = 1;
            if ($roleModel->where($map)->count() > 1) {
                $have = 1;
            }
        }
        $this->assign('can_show_role', $have);
        $this->assign('can_show_project_publish', 1);
        $this->assign('can_show_article_publish', 1);
    }

    private function _setTab($name)
    {
        $this->assign('tab', $name);
    }

    public function password()
    {

        $this->_setTab('password');
        $this->display();
    }

    public function score()
    {

        $scoreModel = D('Ucenter/Score');
        $scores = $scoreModel->getTypeList();
        foreach ($scores as &$v) {
            $v['value'] = $scoreModel->getUserScore(is_login(), $v['id']);
        }
        unset($v);
        $this->assign('scores', $scores);


        $level = nl2br(modC('LEVEL', '
0:Lv1 ' . L('_PRACTICE_') . '
50:Lv2 ' . L('_PROBATION_') . '
100:Lv3 ' . L('_POSITIVE_') . '
200:Lv4 ' . L('_AID_') . '
400:Lv5 ' . L('_MANAGER_') . '
800:Lv6 ' . L('_DIRECTOR__') . '
1600:Lv7 ' . L('_CHAIRMAN__') . '
        ', 'UserConfig'));
        $this->assign('level', $level);

        $self = query_user(array('score', 'title'));

        $this->assign('self', $self);

        $action = D('Admin/Action')->getAction(array('status' => 1));
        $action_module = array();
        foreach ($action as &$v) {
            $v['rule_array'] = unserialize($v['rule']);
            foreach ($v['rule_array'] as &$o) {
                if (is_numeric($o['rule'])) {
                    $o['rule'] = $o['rule'] > 0 ? '+' . intval($o['rule']) : $o['rule'];
                }
                $o['score'] = D('Score')->getType(array('id' => $o['field']));
            }
            if ($v['rule_array'] != false) {
                $action_module[$v['module']]['action'][] = $v;
            }

        }
        unset($v);

        foreach ($action_module as $key => &$a) {
            if (empty($a['action'])) {
                unset($action_module[$key]);
            }
            $a['module'] = D('Common/Module')->getModule($key);
        }

        $this->assign('action_module', $action_module);
        $this->_assignSelf();
        $this->_setTab('score');
        $this->display();
    }

    public function other()
    {

        $this->_setTab('other');
        $this->display();
    }

    public function avatar()
    {

        $this->_setTab('avatar');
        $this->display();
    }

    public function role()
    {
        $userRoleModel = D('UserRole');
        if (IS_POST) {
            $aShowRole = I('post.show_role', 0, 'intval');
            $map['role_id'] = $aShowRole;
            $map['uid'] = is_login();
            $map['status'] = array('egt', 1);
            if (!$userRoleModel->where($map)->count()) {
                $this->error(L('_ERROR_PARAM_') . L('_EXCLAMATION_'));
            }
            $result = D('Member')->where(array('uid' => is_login()))->setField('show_role', $aShowRole);
            if ($result) {
                clean_query_user_cache(is_login(), array('show_role'));
                $this->success(L('_SUCCESS_SETTINGS_') . L('_EXCLAMATION_'));
            } else {
                $this->error(L('_FAIL_SETTINGS_') . L('_EXCLAMATION_'));
            }
        } else {
            $role_id = get_login_role();//当前登录角色
            $roleModel = D('Role');
            $userRoleModel = D('UserRole');

            $already_role_list = $userRoleModel->where(array('uid' => is_login()))->field('role_id,status')->select();
            $already_role_ids = array_column($already_role_list, 'role_id');
            $already_role_list = array_combine($already_role_ids, $already_role_list);

            $map_already_roles['id'] = array('in', $already_role_ids);
            $map_already_roles['status'] = 1;
            $already_roles = $roleModel->where($map_already_roles)->order('sort asc')->select();
            $already_group_ids = array_unique(array_column($already_roles, 'group_id'));

            foreach ($already_roles as &$val) {
                $val['user_status'] = $already_role_list[$val['id']]['status'] != 2 ? ($already_role_list[$val['id']]['status'] == 1) ? '<span style="color: green;">' . L('_AUDITED_') . '</span>' : '<span style="color: #ff0000;">' . L('_DISABLED_') . '<span style="color: 333">' . L('_CONTACT_ADMIN_') . '</span></span>' : '<span style="color: #0003FF;">' . L('_AUDITING_') . '</span>';;
                $val['can_login'] = $val['id'] == $role_id ? 0 : 1;
                $val['user_role_status'] = $already_role_list[$val['id']]['status'];
            }
            unset($val);

            $already_group_ids = array_diff($already_group_ids, array(0));//去除无分组角色组
            if (count($already_group_ids)) {
                $map_can_have_roles['group_id'] = array('not in', $already_group_ids);//同组内的角色不显示
            }
            $map_can_have_roles['id'] = array('not in', $already_role_ids);//去除已有角色
            $map_can_have_roles['invite'] = 0;//不需要邀请注册
            $map_can_have_roles['status'] = 1;
            $can_have_roles = $roleModel->where($map_can_have_roles)->order('sort asc')->select();//可持有角色

            $register_type = modC('REGISTER_TYPE', 'normal', 'Invite');
            $register_type = explode(',', $register_type);
            if (in_array('invite', $register_type)) {//开启邀请注册
                $map_can_have_roles['invite'] = 1;
                $can_up_roles = $roleModel->where($map_can_have_roles)->order('sort asc')->select();//可升级角色
                $this->assign('can_up_roles', $can_up_roles);
            }

            $show_role = query_user(array('show_role'));
            $this->assign('show_role', $show_role['show_role']);
            $this->assign('already_roles', $already_roles);
            $this->assign('can_have_roles', $can_have_roles);

            $this->_setTab('role');
            $this->display();
        }

    }

    public function tag()
    {
        $userTagLinkModel = D('Ucenter/UserTagLink');
        if (IS_POST) {
            $aTagIds = I('post.tag_ids', '', 'op_t');
            $result = $userTagLinkModel->editData($aTagIds);
            if ($result) {
                $res['status'] = 1;
            } else {
                $res['status'] = 0;
                $res['info'] = L('_FAIL_OPERATE_') . L('_EXCLAMATION_');
            }
            $this->ajaxReturn($res);
        } else {
            $userTagModel = D('Ucenter/UserTag');
            $map = getRoleConfigMap('user_tag', get_login_role());
            $ids = M('RoleConfig')->where($map)->getField('value');
            if ($ids) {
                $ids = explode(',', $ids);
                $tag_list = $userTagModel->getTreeListByIds($ids);
                $this->assign('tag_list', $tag_list);
            }
            $myTags = $userTagLinkModel->getUserTag(is_login());
            $this->assign('my_tag', $myTags);
            $my_tag_ids = array_column($myTags, 'id');
            $my_tag_ids = implode(',', $my_tag_ids);
            $this->assign('my_tag_ids', $my_tag_ids);
            $this->_setTab('tag');
            $this->display();
        }
    }

    public function index()
    {
        $aUid = I('get.uid', is_login(), 'intval');
        $aTab = I('get.tab', '', 'op_t');
        $aNickname = I('post.nickname', '', 'op_t');
        $aSex = I('post.sex', 0, 'intval');
        $aEmail = I('post.email', '', 'op_t');
        $aSignature = I('post.signature', '', 'op_t');
        $aCommunity = I('post.community', 0, 'intval');
        $aDistrict = I('post.district', 0, 'intval');
        $aCity = I('post.city', 0, 'intval');
        $aProvince = I('post.province', 0, 'intval');

        if (IS_POST) {
            $this->checkNickname($aNickname);
            $this->checkSex($aSex);
            $this->checkSignature($aSignature);


            $user['pos_province'] = $aProvince;
            $user['pos_city'] = $aCity;
            $user['pos_district'] = $aDistrict;
            $user['pos_community'] = $aCommunity;

            $user['nickname'] = $aNickname;
            $user['sex'] = $aSex;
            $user['signature'] = $aSignature;
            $user['uid'] = get_uid();

            $rs_member = D('Member')->save($user);

            $ucuser['id'] = get_uid();
            $rs_ucmember = UCenterMember()->save($ucuser);
            clean_query_user_cache(get_uid(), array('nickname', 'sex', 'signature', 'email', 'pos_province', 'pos_city', 'pos_district', 'pos_community'));

            //TODO tox 清空缓存

            S('weibo_at_who_users', null);

            if ($rs_member || $rs_ucmember) {
                $this->success(L('_SUCCESS_SETTINGS_') . L('_PERIOD_'));

            } else {
                $this->success(L('_DATA_UNMODIFIED_') . L('_PERIOD_'));
            }

        } else {
            //调用API获取基本信息
            //TODO tox 获取省市区数据
            $user = query_user(array('nickname', 'signature', 'email', 'mobile', 'avatar128', 'rank_link', 'sex', 'pos_province', 'pos_city', 'pos_district', 'pos_community'), $aUid);
            //显示页面
            $this->assign('user', $user);

            $this->accountInfo();

            $this->assign('tab', $aTab);
            $this->getExpandInfo();
            $this->_setTab('info');
            $this->display();
        }

    }

    // zl-，项目发布的部分
    public function projectlist()
    {
        $projects = D('Project/project');
        $map['haizhi_project.uid'] = is_login();
        $map['haizhi_project.status'] = array('neq', -1);
        $result = $projects->lists($map, 0, 10000);
        foreach ($result as $key => $value) {
            $result[$key]['project_create_time'] = date('Y年m月d日 H:m:s', $value['project_create_time']);
            $result[$key]['project_update_time'] = date('Y年m月d日 H:m:s', $value['project_update_time']);
        }

        $this->assign('projects', $result);
        $this->assign('empty', '<span class="empty_data">您还没有发布任何项目</span>');
        $this->display();
    }

    public function changeProjectStatus()
    {
        $id = I('get.id', '', 'intval');
        $status = I('get.status');

        $model = M('project');
        $data['status'] = $status;

        if ($model->where('id=%d', array($id))->save($data)) {
            $this->ajaxReturn(array('info' => '修改成功', 'status' => 1, 'url' => U('projectlist')));
        } else {
            $this->ajaxReturn(array('info' => '修改失败', 'status' => 0));
        }
    }

    public function deleteProject()
    {
        $id = I('get.id', '', 'htmlspecialchars');

        $model = M('project');
        if ($model->delete($id)) {
            $this->ajaxReturn(array('info' => '删除成功', 'status' => 1, 'url' => U('projectlist')));
        } else {
            $this->ajaxReturn(array('info' => '删除出错', 'status' => 0, 'url' => U('projectlist')));
        }
    }

    public function deleteNews()
    {
        $id = I('get.newsid', '', 'htmlspecialchars');
        $model = M('project_news');
        if ($model->delete($id)) {
            $this->ajaxReturn(array('info' => '删除成功', 'status' => 1, 'url' => 'refresh'));
        } else {
            $this->ajaxReturn(array('info' => '删除出错', 'status' => 0, 'url' => 'refresh'));
        }
    }

    public function eventPublish()
    {
        $project_event = M('project_event');
        $arr = I('post.');
        $arr['create_time'] = strtotime($arr['create_time']);
        $success = $project_event->create($arr);

        if ($success) {
            if ($arr['id'] == '') {
                $resultId = $project_event->add();
                $this->ajaxReturn(array('info' => '添加大事记信息成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 2))));
            } else {
                $project_event->save();
                $this->ajaxReturn(array('info' => '保存大事记信息成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 2))));
            }
        } else {
            $this->ajaxReturn(array('info' => $project_event->getError(), 'status' => 0));
        }
    }


    public function publishAdditionInfo()
    {
        $proid = I('get.id', '', 'intval');
        $project = D('Project/project');
        $model = M('project')->where('id=%d', array($proid))->select()[0];
        $data = I('post.');
        $model['market_research'] = json_encode(
            array(
                'yonghuguimo' => $data['yonghuguimo'],
                'yonghuguimodanwei' => $data['yonghuguimodanwei'],
                'shichangguimo' => $data['shichangguimo'],
                'shichangguimodanwei' => $data['shichangguimodanwei'],
                'shichangleibie' => $data['shichangleibie']));
        if ($data['advantageCount'] > 0) {
            for ($i = 0; $i < $data['advantageCount']; $i++) {
                $arr['num' . $i] = array(
                    'advantage' . $i => $data['advantage' . $i],
                    'describe' . $i => $data['describe' . $i],
                );
            }
        }
        $model['competitive_edge'] = json_encode($arr);
        $model['other_edge'] = $data['other_edge'];
        $model['business_model'] = $data['business_model'];
        $model['duibiao_comp'] = json_encode(array(
            'duibiaocomp' => $data['duibiaocomp'],
            'website' => $data['website'],
            'jinqiguzhi' => $data['jinqiguzhi'],
            'guzhishijian' => $data['guzhishijian']));


        $success = $project->create($model);

        if ($success) {
            $project->save();
            $this->ajaxReturn(array('info' => '保存企业资料成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $proid, 'tab' => 6))));
        } else {
            $this->ajaxReturn(array('info' => $project->getError(), 'status' => 0));
        }
    }


    public function publishCompanyInfo()
    {
        $proid = I('get.id', '', 'intval');
        $project = D('Project/project');
        $model = M('project')->where('id=%d', array($proid))->select()[0];

        $business_image = I('post.business_image', '', 'htmlspecialchars');
        $organizationcode = I('post.organizationcode', '', 'htmlspecialchars');
        $yingyecode = I('post.yingyecode', '', 'htmlspecialchars');
        $taxcode = I('post.taxcode', '', 'htmlspecialchars');


        $model['business_image'] = $business_image;
        $model['organizationcode'] = $organizationcode;
        $model['yingyecode'] = $yingyecode;
        $model['taxcode'] = $taxcode;


        $success = $project->create($model);

        if ($success) {
            $project->save();
            $this->ajaxReturn(array('info' => '保存企业资料成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $proid, 'tab' => 5))));
        } else {
            $this->ajaxReturn(array('info' => $project->getError(), 'status' => 0));
        }
    }


    // zl----
    public function projectEdit()
    {
        $tab = I('get.tab', '');
        $id = I('get.id', '');
        $project = D('Project/project')->get($id);
        $project['need_res'] = json_decode($project['need_res'], true);
        $project['competitive_edge'] = json_decode($project['competitive_edge'], true);
        $project['edge'] = count($project['competitive_edge']);
        $project['market_research'] = json_decode($project['market_research'], true);
        $project['duibiao_comp'] = json_decode($project['duibiao_comp'], true);
        if ($tab == '') {
            $this->assign('tab', 1);
        } else {
            $this->assign('tab', $tab);
        }

        if ($id == '') {
            $this->assign('isedit', 0);
        } else {
            // 工作经历
            $works = D('Project/project')->get($id);

            // 大事记
            $events = D('Project/project')->getEvents($id);
            foreach ($events['list'] as &$val) {
                $val['create_time'] = date('Y-m-d', $val['create_time']);
            }
            $this->assign('events', $events['list']);

            // 新闻报道
            $news = D('Project/project')->getNews($id);

            foreach ($news['list'] as &$val) {
                $val['create_time'] = date('Y-m-d', $val['create_time']);
            }
            $this->assign('news', $news['list']);

            // 基本数据
            $this->assign('address', array('province' => $project['province'], 'city' => $project['city'], 'district' => $project['district']));
            $this->assign('model', $project);
            $this->assign('isedit', 1);

            // 核心成员
            $corePerson = D('Project/project')->getCorePersons($id);

            foreach ($corePerson as $key => $value) {
                $corePerson[$key]['education'] = M('project_coreteamer_education')->where('coreteamer_id=' . $corePerson[$key]['coreteamer_id'])->select();
                $corePerson[$key]['experience'] = M('project_coreteamer_experience')->where('coreteamer_id=' . $corePerson[$key]['coreteamer_id'])->select();
            }
            $this->assign('corePerson', $corePerson);

            // 成长数据
            $growdata = D('Project/project')->getGrowData($id);
            foreach ($growdata as &$val) {
                $val['time'] = date('Y-m-d', $val['time']);
            }
            $this->assign('growdata', $growdata);

            // 产品测试
            $producttest = D('Project/project')->getProducttest($id);
            $this->assign('producttest', $producttest);

            // 导师
            $teacher = D('Project/project')->getTeacher($id);
            $this->assign('teacher', $teacher);
        }

        $modelres = M('resource')->select();
        $this->assign('all_res', $modelres);
        $this->assign('project_type', D('Project/project')->projectTypeList());
        $this->assign('empty', '<div class="nodata">暂无数据</div>');

        $this->display();
    }

    public function deleteEvent()
    {
        $id = I('get.eventid', '', 'htmlspecialchars');
        $model = M('project_event');
        if ($model->delete($id)) {
            $this->ajaxReturn(array('info' => '删除成功', 'status' => 1, 'url' => 'refresh'));
        } else {
            $this->ajaxReturn(array('info' => '删除出错', 'status' => 0, 'url' => 'refresh'));
        }
    }

    public function publishBaseInfo()
    {
        $id = I('post.id', '');
        if (IS_POST) {
            $project = D('Project/project');
            $arr = I('post.');
            $arr['uid'] = is_login();

            $resource = array();
            if (!empty($arr['need_resource1']) && !empty($arr['need_resource1_desc'])) {
                $resource['yi'] = array("need_resource1" => $arr['need_resource1'], "need_resource1_desc" => $arr['need_resource1_desc']);
            }
            if (!empty($arr['need_resource2']) && !empty($arr['need_resource2_desc'])) {
                $resource['er'] = array("need_resource2" => $arr['need_resource2'], "need_resource2_desc" => $arr['need_resource2_desc']);
            }
            if (!empty($arr['need_resource3']) && !empty($arr['need_resource3_desc'])) {
                $resource['san'] = array("need_resource3" => $arr['need_resource3'], "need_resource3_desc" => $arr['need_resource3_desc']);
            }

            $arr['need_res'] = json_encode($resource);
            $success = $project->create($arr);

            if ($success) {
                if ($id == '') {
                    $resultId = $project->add();
                    $this->ajaxReturn(array('info' => '添加项目信息成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $resultId))));
                } else {
                    $project->save();
                    $this->ajaxReturn(array('info' => '保存项目信息成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $id))));
                }
            } else {
                $this->ajaxReturn(array('info' => $project->getError(), 'status' => 0));
            }
        } else {
            $this->ajaxReturn(array("status" => 0, "info" => "不支持的方法", 'url' => U('projectEdit')));
        }
    }

    public function deleteCoreteamer()
    {
        $id = I('get.coreteamerid', '', 'htmlspecialchars');
        $model = M('project_coreteamer');
        if ($model->delete($id)) {
            $this->ajaxReturn(array('info' => '删除成功', 'status' => 1, 'url' => 'refresh'));
        } else {
            $this->ajaxReturn(array('info' => '删除出错', 'status' => 0, 'url' => 'refresh'));
        }
    }

    public function coreteamerPublish()
    {
        $project_coreteamer = M('project_coreteamer');
        $arr = I('post.');
        $arr['address'] = $arr['telephone'];
        $success = $project_coreteamer->create($arr);
        if ($success) {
            if ($arr['id'] == '') {
                $resultId = $project_coreteamer->add();
                $this->ajaxReturn(array('info' => '添加核心成员成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 3))));
            } else {
                $project_coreteamer->save();
                $this->ajaxReturn(array('info' => '保存核心成员成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 3))));
            }
        } else {
            $this->ajaxReturn(array('info' => $project_coreteamer->getError(), 'status' => 0));
        }
    }

    public function newsPublish()
    {
        $project_news = M('project_news');
        $arr = I('post.');
        $arr['create_time'] = strtotime($arr['create_time']);
        $arr['application'] = $arr['application'] == 'on' ? 1 : 0;
        $success = $project_news->create($arr);

        if ($success) {
            if ($arr['id'] == '') {
                $resultId = $project_news->add();
                $this->ajaxReturn(array('info' => '添加新闻报道成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 4))));
            } else {
                $project_news->save();
                $this->ajaxReturn(array('info' => '保存新闻报道成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 4))));
            }
        } else {
            $this->ajaxReturn(array('info' => $project_news->getError(), 'status' => 0));
        }
    }

    public function deleteGrowdata()
    {
        $id = I('get.growdataid', '', 'htmlspecialchars');
        $model = M('project_growdata');
        if ($model->delete($id)) {
            $this->ajaxReturn(array('info' => '删除成功', 'status' => 1, 'url' => 'refresh'));
        } else {
            $this->ajaxReturn(array('info' => '删除出错', 'status' => 0, 'url' => 'refresh'));
        }
    }


    public function growdataPublish()
    {
        $project_growdata = M('project_growdata');
        $arr = I('post.');
        $arr['time'] = strtotime($arr['time']);

        $success = $project_growdata->create($arr);
        if ($success) {
            if ($arr['id'] == '') {
                $resultId = $project_growdata->add();
                $this->ajaxReturn(array('info' => '添加成长数据成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 7))));
            } else {
                $project_growdata->save();
                $this->ajaxReturn(array('info' => '保存成长数据成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 7))));
            }
        } else {
            $this->ajaxReturn(array('info' => $project_growdata->getError(), 'status' => 0));
        }
    }

    public function deleteProducttest()
    {
        $id = I('get.producttestid', '', 'htmlspecialchars');
        $model = M('project_producttest');
        if ($model->delete($id)) {
            $this->ajaxReturn(array('info' => '删除成功', 'status' => 1, 'url' => 'refresh'));
        } else {
            $this->ajaxReturn(array('info' => '删除出错', 'status' => 0, 'url' => 'refresh'));
        }
    }


    public function producttestPublish()
    {
        $project_producttest = M('project_producttest');
        $arr = I('post.');

        $success = $project_producttest->create($arr);
        if ($success) {
            if ($arr['id'] == '') {
                $resultId = $project_producttest->add();
                $this->ajaxReturn(array('info' => '添加产品测试成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 8))));
            } else {
                $project_producttest->save();
                $this->ajaxReturn(array('info' => '保存产品测试成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 8))));
            }
        } else {
            $this->ajaxReturn(array('info' => $project_producttest->getError(), 'status' => 0));
        }
    }

    public function deleteTeacher()
    {
        $id = I('get.teacherid', '', 'htmlspecialchars');
        $model = M('project_teacher');
        if ($model->where('id=' . $id)->delete()) {
            $this->ajaxReturn(array('info' => '删除成功', 'status' => 1, 'url' => 'refresh'));
        } else {
            $this->ajaxReturn(array('info' => '删除出错', 'status' => 0, 'url' => 'refresh'));
        }
    }

    public function teacherPublish()
    {
        $project_teacher = M('project_teacher');
        $arr = I('post.');
        $success = $project_teacher->create($arr);
        if ($success) {
            if ($arr['id'] == '') {
                $resultId = $project_teacher->add();
                $this->ajaxReturn(array('info' => '添加导师成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 9))));
            } else {
                $project_teacher->save();
                $this->ajaxReturn(array('info' => '保存导师成功', 'status' => 1, 'url' => U('projectEdit', array('id' => $arr['proid'], 'tab' => 9))));
            }
        } else {
            $this->ajaxReturn(array('info' => $project_teacher->getError(), 'status' => 0));
        }
    }

    public function renzheng()
    {
        $this->display();
    }

    /**
     *
     * 认证内容提交后的处理
     *
     * @auth  周海天
     */
    public function renzPublish()
    {
        $type = I('get.type', '', 'htmlspecialchars');

        if (!$type) {
            $this->ajaxReturn(array('info' => '未指定认证类型', 'status' => 0, 'url' => 'refresh'));
        }


        switch ($type) {
            // 个人
            case 1:
                $identify_image_face = I('post.idcard', '', 'htmlspecialchars');
                $identify_image_back = I('post.idcard_back', '', 'htmlspecialchars');
                $identify_image_person = I('post.idcard_hand', '', 'htmlspecialchars');
                if (!$identify_image_face) {
                    $this->ajaxReturn(array('info' => '身份证正面照必须上传', 'status' => 0));
                }
                if (!$identify_image_back) {
                    $this->ajaxReturn(array('info' => '身份证反面照必须上传', 'status' => 0));
                }
                if (!$identify_image_person) {
                    $this->ajaxReturn(array('info' => '本人手持身份证照必须上传', 'status' => 0));
                }
                $data = array(
                    'identify_image_face' => $identify_image_face,
                    'identify_image_back' => $identify_image_back,
                    'identify_image_person' => $identify_image_person,
                    'uid' => is_login(),
                    'is_pass' => 0,
                );

                if (M('personal_vertify')->add($data)) {
                    $this->ajaxReturn(array('info' => '申请认证成功', 'status' => 1, 'url' => 'refresh'));
                }


                break;
            // 机构
            case 2:
                $company_logo = I('post.bussiness_card', '', 'htmlspecialchars');
                $business_image = I('post.bussiness_logo', '', 'htmlspecialchars');
                if (empty($company_logo)) {
                    $this->ajaxReturn(array('info' => '公司logo必须上传', 'status' => 0));
                }
                if (empty($business_image)) {
                    $this->ajaxReturn(array('info' => '公司营业执照必须上传', 'status' => 0));
                }
                $data = array(
                    'company_logo' => $company_logo,
                    'business_image' => $business_image,
                    'uid' => is_login(),
                    'is_pass' => 0,
                );
                $model = M('business_vertify');
                if ($model->add($data)) {
                    $this->ajaxReturn(array('info' => '申请认证成功', 'status' => 1, 'url' => 'refresh'));
                }
                break;
            // 专家
            case 3:
                break;
        }

    }

    /**
     *
     * 增加教育经历
     *
     * @auth  周海天
     */

    public function addEducation()
    {

        $coreteamer_id = I('post.proid');
        $proid = I('post.id');

        if(empty(I("post.degree0"))&&empty( I("post.edu0"))&&empty(I("post.degree1"))&&empty( I("post.edu1"))&&empty(I("post.degree2"))&&empty( I("post.edu2"))){
            $this->ajaxReturn(array('info' => '数据不能为空', 'status' => 0));
        }

        if (!empty($coreteamer_id)) {
            if(M("project_coreteamer_education")->where('coreteamer_id='.$coreteamer_id)->select()){
                M('project_coreteamer_education')->where('coreteamer_id='.$coreteamer_id)->delete();
            }
            for ($i = 0; $i < 3; $i++) {
                if(!empty(I("post.edu$i")) || !empty(I("post.degree$i"))){
                    $model = M("project_coreteamer_education");
                    $model->add(array('school' => I("post.edu$i"),
                        'degree' => I("post.degree$i"),
                        'coreteamer_id' => $coreteamer_id));
                    unset($model);
                 }
            }
        }
        $this->ajaxReturn(array('info' => '数据增加成功', 'status' => 1, 'url' => U('projectedit', array('id' => $proid, 'tab' => 3))));
    }

    /**
     *
     * 增加工作经历
     * @auth  周海天
     */
public function addExperience(){
    $coreteamer_id=I('post.proid');
    $proid= I('post.id');
    if(empty(I("post.position0"))&&empty( I("post.company0"))&&empty(I("post.position1"))&&empty( I("post.company1"))&&empty(I("post.position2"))&&empty( I("post.company2"))){
        $this->ajaxReturn(array('info' => '数据不能为空', 'status' => 0));
    }
    if(!empty($coreteamer_id)){
        if(M("project_coreteamer_experience")->where('coreteamer_id='.$coreteamer_id)->select()){
            M('project_coreteamer_experience')->where('coreteamer_id='.$coreteamer_id)->delete();
        }

        for($i=0;$i<3;$i++){
            if(!empty(I("post.company$i"))||!empty(I("post.position$i"))){
                $model=M("project_coreteamer_experience");
                $model->add(array('company'=>I("post.company$i"),
                    'e_position'=>I("post.position$i"),
                    'coreteamer_id'=>$coreteamer_id));
                unset($model);
            }
        }
        $this->ajaxReturn(array('info' =>'数据增加成功', 'status' => 1,'url'=>U('projectedit',array('id'=>$proid,'tab'=>3))));
    }
}

    /**验证用户名
     * @param $nickname
     * @auth 陈一枭
     */
    private function checkNickname($nickname) {
        $length = mb_strlen($nickname, 'utf8');
        if ($length == 0) {
            $this->error(L('_ERROR_NICKNAME_INPUT_') . L('_PERIOD_'));
        } else {
            if ($length > modC('NICKNAME_MAX_LENGTH', 32, 'USERCONFIG')) {
                $this->error(L('_ERROR_NICKNAME_1_') . modC('NICKNAME_MAX_LENGTH', 32, 'USERCONFIG') . L('_ERROR_NICKNAME_2_') . L('_PERIOD_'));
            } else {
                if ($length < modC('NICKNAME_MIN_LENGTH', 2, 'USERCONFIG')) {
                    $this->error(L('_ERROR_NICKNAME_LENGTH_1_') . modC('NICKNAME_MIN_LENGTH', 2, 'USERCONFIG') . L('_ERROR_NICKNAME_2_') . L('_PERIOD_'));
                }
            }
        }
        $match = preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $nickname);
        if (!$match) {
            $this->error(L('_ERROR_NICKNAME_LIMIT_') . L('_PERIOD_'));
        }

        $map_nickname['nickname'] = $nickname;
        $map_nickname['uid'] = array('neq', is_login());
        $had_nickname = D('Member')->where($map_nickname)->count();
        if ($had_nickname) {
            $this->error(L('_ERROR_NICKNAME_USED_') . L('_PERIOD_'));
        }
        $denyName = M("Config")->where(array('name' => 'USER_NAME_BAOLIU'))->getField('value');
        if ($denyName != '') {
            $denyName = explode(',', $denyName);
            foreach ($denyName as $val) {
                if (!is_bool(strpos($nickname, $val))) {
                    $this->error(L('_ERROR_NICKNAME_FORBIDDEN_') . L('_PERIOD_'));
                }
            }
        }
    }


    /**验证签名
     * @param $signature
     * @auth 陈一枭
     */
    private function checkSignature($signature) {
        $length = mb_strlen($signature, 'utf8');
        if ($length >= 100) {
            $this->error(L('_ERROR_SIGNATURE_LENGTH_'));
        }
    }


    /**
     * 获取用户扩展信息
     * @param null $uid
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function getExpandInfo($uid = null) {
        $profile_group_list = $this->_profile_group_list($uid);
        if ($profile_group_list) {
            $info_list = $this->_info_list($profile_group_list[0]['id'], $uid);
            $this->assign('info_list', $info_list);
            $this->assign('profile_group_id', $profile_group_list[0]['id']);
            //dump($info_list);exit;
        }
        foreach ($profile_group_list as &$v) {
            $v['fields'] = $this->_getExpandInfo($v['id']);
        }

        $this->assign('profile_group_list', $profile_group_list);
    }


    /**显示某一扩展分组信息
     * @param null $profile_group_id
     * @param null $uid
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function _getExpandInfo($profile_group_id = null) {
        $res = D('field_group')->where(array('id' => $profile_group_id, 'status' => '1'))->find();
        if (!$res) {
            return array();
        }
        $info_list = $this->_info_list($profile_group_id);

        return $info_list;
        $this->assign('info_list', $info_list);
        $this->assign('profile_group_id', $profile_group_id);
        //dump($info_list);exit;
        $this->assign('profile_group_list', $profile_group_list);
    }

    /**修改用户扩展信息
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function edit_expandinfo($profile_group_id) {
        $field_list = $this->getRoleFieldIds();
        if ($field_list) {
            $map_field['id'] = array('in', $field_list);
        } else {
            $this->error(L('_ERROR_INFO_SAVE_NONE_') . L('_EXCLAMATION_'));
        }
        $map_field['profile_group_id'] = $profile_group_id;
        $map_field['status'] = 1;
        $field_setting_list = D('field_setting')->where($map_field)->order('sort asc')->select();

        if (!$field_setting_list) {
            $this->error(L('_ERROR_INFO_CHANGE_NONE_') . L('_EXCLAMATION_'));
        }

        $data = null;
        foreach ($field_setting_list as $key => $val) {
            $data[$key]['uid'] = is_login();
            $data[$key]['field_id'] = $val['id'];
            switch ($val['form_type']) {
                case 'input':
                    $val['value'] = op_t($_POST['expand_' . $val['id']]);
                    if (!$val['value'] || $val['value'] == '') {
                        if ($val['required'] == 1) {
                            $this->error($val['field_name'] . L('_ERROR_CONTENT_NONE_') . L('_EXCLAMATION_'));
                        }
                    } else {
                        $val['submit'] = $this->_checkInput($val);
                        if ($val['submit'] != null && $val['submit']['succ'] == 0) {
                            $this->error($val['submit']['msg']);
                        }
                    }
                    $data[$key]['field_data'] = $val['value'];
                    break;
                case 'radio':
                    $val['value'] = op_t($_POST['expand_' . $val['id']]);
                    $data[$key]['field_data'] = $val['value'];
                    break;
                case 'checkbox':
                    $val['value'] = $_POST['expand_' . $val['id']];
                    if (!is_array($val['value']) && $val['required'] == 1) {
                        $this->error(L('_ERROR_AT_LIST_ONE_') . L('_COLON_') . $val['field_name']);
                    }
                    $data[$key]['field_data'] = is_array($val['value']) ? implode('|', $val['value']) : '';
                    break;
                case 'select':
                    $val['value'] = op_t($_POST['expand_' . $val['id']]);
                    $data[$key]['field_data'] = $val['value'];
                    break;
                case 'time':
                    $val['value'] = op_t($_POST['expand_' . $val['id']]);
                    $val['value'] = strtotime($val['value']);
                    $data[$key]['field_data'] = $val['value'];
                    break;
                case 'textarea':
                    $val['value'] = op_t($_POST['expand_' . $val['id']]);
                    if (!$val['value'] || $val['value'] == '') {
                        if ($val['required'] == 1) {
                            $this->error($val['field_name'] . L('_ERROR_CONTENT_NONE_') . L('_EXCLAMATION_'));
                        }
                    } else {
                        $val['submit'] = $this->_checkInput($val);
                        if ($val['submit'] != null && $val['submit']['succ'] == 0) {
                            $this->error($val['submit']['msg']);
                        }
                    }
                    $val['submit'] = $this->_checkInput($val);
                    if ($val['submit'] != null && $val['submit']['succ'] == 0) {
                        $this->error($val['submit']['msg']);
                    }
                    $data[$key]['field_data'] = $val['value'];
                    break;
            }
        }
        $map['uid'] = is_login();
        $map['role_id'] = get_login_role();
        $is_success = false;
        foreach ($data as $dl) {
            $dl['role_id'] = $map['role_id'];

            $map['field_id'] = $dl['field_id'];
            $res = D('field')->where($map)->find();
            if (!$res) {
                if ($dl['field_data'] != '' && $dl['field_data'] != null) {
                    $dl['createTime'] = $dl['changeTime'] = time();
                    if (!D('field')->add($dl)) {
                        $this->error(L('_ERROR_INFO_ADD_') . L('_EXCLAMATION_'));
                    }
                    $is_success = true;
                }
            } else {
                $dl['changeTime'] = time();
                if (!D('field')->where('id=' . $res['id'])->save($dl)) {
                    $this->error(L('_ERROR_INFO_CHANGE_') . L('_EXCLAMATION_'));
                }
                $is_success = true;
            }
            unset($map['field_id']);
        }
        clean_query_user_cache(is_login(), 'expand_info');
        if ($is_success) {
            $this->success(L('_SUCCESS_SAVE_') . L('_EXCLAMATION_'));
        } else {
            $this->error(L('_ERROR_SAVE_') . L('_EXCLAMATION_'));
        }
    }

    /**input类型验证
     * @param $data
     * @return mixed
     * @author 郑钟良<zzl@ourstu.com>
     */
    function _checkInput($data) {
        if ($data['form_type'] == "textarea") {
            $validation = $this->_getValidation($data['validation']);
            if (($validation['min'] != 0 && mb_strlen($data['value'], "utf-8") < $validation['min']) || ($validation['max'] != 0 && mb_strlen($data['value'], "utf-8") > $validation['max'])) {
                if ($validation['max'] == 0) {
                    $validation['max'] = '';
                }
                $info['succ'] = 0;
                $info['msg'] = $data['field_name'] . L('_INFO_LENGTH_1_') . $validation['min'] . "-" . $validation['max'] . L('_INFO_LENGTH_2_');
            }
        } else {
            switch ($data['child_form_type']) {
                case 'string':
                    $validation = $this->_getValidation($data['validation']);
                    if (($validation['min'] != 0 && mb_strlen($data['value'], "utf-8") < $validation['min']) || ($validation['max'] != 0 && mb_strlen($data['value'], "utf-8") > $validation['max'])) {
                        if ($validation['max'] == 0) {
                            $validation['max'] = '';
                        }
                        $info['succ'] = 0;
                        $info['msg'] = $data['field_name'] . L('_INFO_LENGTH_1_') . $validation['min'] . "-" . $validation['max'] . L('_INFO_LENGTH_2_');
                    }
                    break;
                case 'number':
                    if (preg_match("/^\d*$/", $data['value'])) {
                        $validation = $this->_getValidation($data['validation']);
                        if (($validation['min'] != 0 && mb_strlen($data['value'], "utf-8") < $validation['min']) || ($validation['max'] != 0 && mb_strlen($data['value'], "utf-8") > $validation['max'])) {
                            if ($validation['max'] == 0) {
                                $validation['max'] = '';
                            }
                            $info['succ'] = 0;
                            $info['msg'] = $data['field_name'] . L('_INFO_LENGTH_1_') . $validation['min'] . "-" . $validation['max'] . L('_INFO_LENGTH_2_') . L('_COMMA_') . L('_INFO_AND_DIGITAL_');
                        }
                    } else {
                        $info['succ'] = 0;
                        $info['msg'] = $data['field_name'] . L('_INFO_DIGITAL_');
                    }
                    break;
                case 'email':
                    if (!preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i", $data['value'])) {
                        $info['succ'] = 0;
                        $info['msg'] = $data['field_name'] . L('_INFO_FORMAT_EMAIL_');
                    }
                    break;
                case 'phone':
                    if (!preg_match("/^\d{11}$/", $data['value'])) {
                        $info['succ'] = 0;
                        $info['msg'] = $data['field_name'] . L('_INFO_FORMAT_PHONE_');
                    }
                    break;
            }
        }
        return $info;
    }

    /**处理$validation
     * @param $validation
     * @return mixed
     * @author 郑钟良<zzl@ourstu.com>
     */
    function _getValidation($validation) {
        $data['min'] = $data['max'] = 0;
        if ($validation != '') {
            $items = explode('&', $validation);
            foreach ($items as $val) {
                $item = explode('=', $val);
                if ($item[0] == 'min' && is_numeric($item[1]) && $item[1] > 0) {
                    $data['min'] = $item[1];
                }
                if ($item[0] == 'max' && is_numeric($item[1]) && $item[1] > 0) {
                    $data['max'] = $item[1];
                }
            }
        }
        return $data;
    }

    /**分组下的字段信息及相应内容
     * @param null $id 扩展分组id
     * @param null $uid
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function _info_list($id = null, $uid = null) {

        $fields_list = $this->getRoleFieldIds($uid);
        $info_list = null;

        if (isset($uid) && $uid != is_login()) {
            //查看别人的扩展信息
            $field_setting_list = D('field_setting')->where(array('profile_group_id' => $id, 'status' => '1', 'visiable' => '1', 'id' => array('in', $fields_list)))->order('sort asc')->select();

            if (!$field_setting_list) {
                return null;
            }
            $map['uid'] = $uid;
        } else {
            if (is_login()) {
                $field_setting_list = D('field_setting')->where(array('profile_group_id' => $id, 'status' => '1', 'id' => array('in', $fields_list)))->order('sort asc')->select();

                if (!$field_setting_list) {
                    return null;
                }
                $map['uid'] = is_login();

            } else {
                $this->error(L('_ERROR_PLEASE_LOGIN_') . L('_EXCLAMATION_'));
            }
        }
        foreach ($field_setting_list as $val) {
            $map['field_id'] = $val['id'];
            $field = D('field')->where($map)->find();
            $val['field_content'] = $field;
            $info_list[$val['id']] = $val;
            unset($map['field_id']);
        }

        return $info_list;
    }

    private function getRoleFieldIds($uid = null) {
        $role_id = get_role_id($uid);
        $fields_list = S('Role_Expend_Info_' . $role_id);
        if (!$fields_list) {
            $map_role_config = getRoleConfigMap('expend_field', $role_id);
            $fields_list = D('RoleConfig')->where($map_role_config)->getField('value');
            if ($fields_list) {
                $fields_list = explode(',', $fields_list);
                S('Role_Expend_Info_' . $role_id, $fields_list, 600);
            }
        }
        return $fields_list;
    }


    /**扩展信息分组列表获取
     * @return mixed
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function _profile_group_list($uid = null) {
        $profile_group_list = array();
        $fields_list = $this->getRoleFieldIds($uid);
        if ($fields_list) {
            $fields_group_ids = D('FieldSetting')->where(array('id' => array('in', $fields_list), 'status' => '1'))->field('profile_group_id')->select();
            if ($fields_group_ids) {
                $fields_group_ids = array_unique(array_column($fields_group_ids, 'profile_group_id'));
                $map['id'] = array('in', $fields_group_ids);

                if (isset($uid) && $uid != is_login()) {
                    $map['visiable'] = 1;
                }
                $map['status'] = 1;
                $profile_group_list = D('field_group')->where($map)->order('sort asc')->select();
            }
        }
        return $profile_group_list;
    }


    public function changeAvatar() {
        $this->defaultTabHash('change-avatar');
        $this->display();
    }


    private function iframeReturn($result) {
        $json = json_encode($result);
        $json = htmlspecialchars($json);
        $html = "<textarea data-type=\"application/json\">$json</textarea>";
        echo $html;
        exit;
    }


    public function doChangePassword($old_password, $new_password) {
        //调用接口
        $memberModel = UCenterMember();
        $res = $memberModel->changePassword($old_password, $new_password);
        if ($res) {
            $this->success(L('_SUCCESS_PASSWORD_ALTER_') . L('_PERIOD_'), 'refresh');
        } else {
            $this->error($memberModel->getErrorMessage());
        }

    }

    /**
     * @param $sex
     * @return int
     * @auth 陈一枭
     */
    private function checkSex($sex) {

        if ($sex < 0 || $sex > 2) {
            $this->error(L('_ERROR_SEX_') . L('_PERIOD_'));
            return $sex;
        }
        return $sex;
    }

    /**
     * @param $email
     * @param $email
     * @auth 陈一枭
     */
    private function checkEmail($email) {
        $pattern = "/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
        if (!preg_match($pattern, $email)) {
            $this->error(L('_ERROR_EMAIL_FORMAT_') . L('_PERIOD_'));
        }

        $map['email'] = $email;
        $map['id'] = array('neq', get_uid());
        $had = UCenterMember()->where($map)->count();
        if ($had) {
            $this->error(L('_ERROR_EMAIL_USED_') . L('_PERIOD_'));
        }
    }

    /**
     * accountInfo   账户信息
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    private function accountInfo() {
        $info = UCenterMember()->field('id,username,email,mobile,type')->find(is_login());
        $this->assign('accountInfo', $info);
    }

    /**
     * saveUsername  修改用户名
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function saveUsername() {
        $aUsername = $cUsername = I('post.username', '', 'op_t');

        if (!check_reg_type('username')) {
            $this->error(L('_ERROR_USERNAME_CONG_CLOSED_') . L('_EXCLAMATION_'));
        }


        //判断是否登录
        if (!is_login()) {
            $this->error(L('_ERROR_AFTER_LOGIN_') . L('_EXCLAMATION_'));
        }
        //判断提交的用户名是否为空
        if (empty($aUsername)) {
            $this->error(L('_USERNAME_NOT_EMPTY_') . L('_EXCLAMATION_'));
        }
        check_username($cUsername, $cEmail, $cMobile);
        if (empty($cUsername)) {
            !empty($cEmail) && $str = L('_EMAIL_');
            !empty($cMobile) && $str = L('_PHONE_');
            $this->error(L('_USERNAME_NOT_') . $str);
        }

        //验证用户名是否是字母和数字
        preg_match("/^[a-zA-Z0-9_]{" . modC('USERNAME_MIN_LENGTH', 2, 'USERCONFIG') . "," . modC('USERNAME_MAX_LENGTH', 32, 'USERCONFIG') . "}$/", $aUsername, $match);
        if (!$match) {
            $this->error(L('_ERROR_USERNAME_LIMIT_1_') . modC('USERNAME_MIN_LENGTH', 2, 'USERCONFIG') . '-' . modC('USERNAME_MAX_LENGTH', 32, 'USERCONFIG') . L('_ERROR_USERNAME_LIMIT_2_') . L('_EXCLAMATION_'));

        }

        $uid = get_uid();
        $mUcenter = UCenterMember();
        //判断用户是否已设置用户名
        $username = $mUcenter->where(array('id' => $uid))->getField('username');
        if (empty($username)) {
            //判断修改的用户名是否已存在
            $id = $mUcenter->where(array('username' => $aUsername))->getField('id');
            if ($id) {
                $this->error(L('_ERROR_USERNAME_EXIST_') . L('_EXCLAMATION_'));
            } else {
                //修改用户名
                $rs = $mUcenter->where(array('id' => $uid))->save(array('username' => $aUsername));
                if (!$rs) {
                    $this->error(L('_FAIL_SETTINGS_') . L('_EXCLAMATION_'));
                }
                $this->success(L('_SUCCESS_SETTINGS_') . L('_EXCLAMATION_'), 'refresh');
            }
        }
        $this->error(L('_ERROR_USERNAME_CANNOT_MODIFY_') . L('_EXCLAMATION_'));
    }

    /**
     * changeaccount  修改帐号信息
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function changeAccount() {
        $aTag = I('get.tag', '', 'op_t');
        $aTag = $aTag == 'mobile' ? 'mobile' : 'email';
        $this->assign('cName', $aTag == 'mobile' ? L('_PHONE_') : L('_EMAIL_'));
        $this->assign('type', $aTag);
        $this->display();

    }

    public function doSendVerify($account, $verify, $type) {
        switch ($type) {
            case 'mobile':
                $content = modC('SMS_CONTENT', '{$verify}', 'USERCONFIG');
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                $res = send_sms($account,'qwe', $content);
                return $res;
                break;
            case 'email':
                //发送验证邮箱

                $content = modC('REG_EMAIL_VERIFY', '{$verify}', 'USERCONFIG');
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                $res = send_mail($account, modC('WEB_SITE_NAME', L('_OPENSNS_'), 'Config') . L('_EMAIL_VERIFY_2_'), $content);

                return $res;
                break;
        }

    }

    /**
     * checkVerify  验证验证码
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function checkVerify() {

        $aAccount = I('account', '', 'op_t');
        $aType = I('type', '', 'op_t');
        $aVerify = I('verify', '', 'op_t');
        $aUid = I('uid', 0, 'intval');

        if (!is_login() || $aUid != is_login()) {
            $this->error(L(''));
        }
        $aType = $aType == 'mobile' ? 'mobile' : 'email';
        $res = D('Verify')->checkVerify($aAccount, $aType, $aVerify, $aUid);
        if (!$res) {
            $this->error(L('_FAIL_VERIFY_'));
        }
        UCenterMember()->where(array('id' => $aUid))->save(array($aType => $aAccount));
        $this->success(L('_SUCCESS_VERIFY_'), U('ucenter/config/index'));

    }


    public function cleanRemember() {
        $uid = is_login();
        if ($uid) {
            D('user_token')->where('uid=' . $uid)->delete();
            $this->success(L('_SUCCESS_CLEAR_') . L('_EXCLAMATION_'));
        }
        $this->error(L('_FAIL_CLEAR_') . L('_EXCLAMATION_'));
    }

}