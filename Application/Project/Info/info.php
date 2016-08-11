<?php

return array(//模块名
    'name' => 'Project', //别名
    'alias' => '项目', //版本号
    'version' => '1.0.0', //是否商业模块,1是，0，否
    'is_com' => 0, //是否显示在导航栏内？  1是，0否
    'show_nav' => 1, //模块描述
    'summary' => '项目相关管理', //开发者
    'developer' => '开发小组进行开发', //开发者网站
    'website' => 'www.baidu.com', //前台入口，可用U函数
    'entry' => 'Project/view/index',
    'admin_entry' => 'Admin/Project/index',
    'icon' => 'th', 'can_uninstall' => 1);