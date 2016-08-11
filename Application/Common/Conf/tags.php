<?php
return array(
    'app_begin' => array('Behavior\CheckLangBehavior'),
    'app_init' => array('Common\Behavior\InitHookBehavior'),
    'action_begin' => array('Common\Behavior\InitModuleInfoBehavior'),
    'my_tags' => array('Common\Behavior\SendSmsBehavior'),
    // 添加下面一行定义即可
   //,'Common\Behavior\RunMessageBehavior'
    // 如果是3.2.1版本 需要改成
);