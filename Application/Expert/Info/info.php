<?php

return array(
    //模块名
    'name' => 'expert',
    //别名
    'alias' => '专家模块',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 0,
    //模块描述
    'summary' => '专家相关管理',
    //开发者
    'developer' => '开发小组进行开发',
    //开发者网站
    'website' => 'www.baidu.com',
    //前台入口，可用U函数
    'entry' => 'Expert/index/index',

    'admin_entry' => 'Admin/Expert/index',

    'icon' => 'th',

    'can_uninstall' => 0
);