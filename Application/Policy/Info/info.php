<?php

return array(
    //模块名
    'name' => 'Policy',
    //别名
    'alias' => '政策专区',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 0,
    //模块描述
    'summary' => '政策专区',
    //开发者
    'developer' => '海智开发小组',
    //开发者网站
    'website' => 'http://haizhi.com',
    //前台入口，可用U函数
    'entry' => 'Policy/index/index',

    'admin_entry' => 'Admin/Policy/contents',

    'icon' => 'th',

    'can_uninstall' => 1
);