<?php


namespace Expert\Controller;

use Think\Controller;


class IndexController extends Controller
{
    /**
     * 业务逻辑都放在 WeiboApi 中
     * @var
     */
    public function _initialize()
    {
        var_dump(check_auth('addproject'));
        if(check_auth('addproject')){
            echo 123123;
        }else{
            return ;
        }

    }

    public function index(){


    }

    public function lists(){

        $model = D('Expert');
        if($model->create(array('title'=>'name'))){
        }else{
            echo  $model->getError();
        }

    }
    public function detail(){}


}