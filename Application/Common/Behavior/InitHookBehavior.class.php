<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Common\Behavior;

use Think\Behavior;
use Think\Hook;

defined('THINK_PATH') or exit();

// 初始化钩子信息
class InitHookBehavior extends Behavior
{

    // 行为扩展的执行入口必须是run
    /**
     * @param mixed $content
     * 页面钩子的 完成的定义流程
     *
     * 先 Hook::add('hookname', 'behavior_path');\
     * 行为的名称：Behavior\\adBehavior
     * 路径 ThinkPHP->Library->Behavior->adBehavior.class.php
     *  class adBehavior{
     *       function run($arg){
     *           echo '我是一条'.$arg['name'].'广告,'.$arg['value'].'代言';        //在此介绍下，run必须的 ，细心的会在Think核心找到Behavior.class.php里面有这样一句操蛋的话  abstract public function run(&$params); 你懂的
     *       }
     *   }
     *
     * 视图页面  {:hook('ad', array('name'=>'AV','value'=>'*老师'))}
     */
    public function run(&$content)
    {
        if (defined('BIND_MODULE') && BIND_MODULE === 'Install') return;

        // 优先获取缓存的hook
        // S方法支持有效期，F()方法不支持。
        // S('data',$Data,3600);
        // S('data',$Data);
        // S('data',NULL); 删除
        $data = S('hooks');
        if (!$data) {

            // 获取hook中所有name 和addons字段;
            $hooks = M('Hooks')->getField('name,addons');

            foreach ($hooks as $key => $value) {
                if ($value) {

                    // $map 为 thinkphp 中的表达式
                    // $map['字段名']  = array('表达式', '操作条件');
                    // http://www.cnblogs.com/martin1009/archive/2012/08/24/2653718.html
                    // 这里应该是取状态为正在使用的hooks
                    $map['status'] = 1;

                    // explode 相当于 split，获取单个hook的所有插件名称。
                    $names = explode(',', $value);

                    //
                    $map['name'] = array('IN', $names);

                    // 根据条件获取所有插件。这里获取了所有使用中的插件。实际的插件注册在hook中。
                    $data = M('Addons')->where($map)->getField('id,name');

                    if ($data) {
                        // array_intersect 获取2数组的交集。这里获取的是从hooks中以及addons中插件的交集
                        $addons = array_intersect($names, $data);

                        // array_map，每个值运行对应的方法 'get_addon_class',添加多个行为每个行为依次执行
                        Hook::add($key, array_map('get_addon_class', $addons));
                    }
                }
            }
            // 将所有的hook放缓存里面
            S('hooks', Hook::get());
        } else {

            // 批量导入 ，第二个参数指定 合并和覆盖导入，如果是true 为合并导入，false 为覆盖方式。
            Hook::import($data, false);
        }
    }
}