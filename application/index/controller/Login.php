<?php
namespace app\index\controller;
use app\config\model\menggu;
use \think\Request;
use think\Controller;
use \think\View;
use app\config\common;
class Login extends Controller {
    public function index(){
//        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
        $this->assign('state','');
        return view('index');
    }
    public function login(){
        $username = Request::instance()->post('a_username');
        $password = Request::instance()->post('a_password');
        if ($username=='admin'&&$password=='admin'){
            $this->start_session(3600);
            session('User',array('username'=>$username,'password'=>$password));
            $this->success('登录成功','/index.php/index/manage','',1);
        }else{
            $this->error('用户名或者密码错误');
        }
    }
    public function logout(){
        session_start();
//        unset($_SESSION['User']);
        session_destroy();
        $this->success('已退出登录','/index.php/index/login','',1);
    }
    protected function start_session($expire = 0)
    {
        if ($expire == 0) {
            $expire = ini_get('session.gc_maxlifetime');
        } else {
            ini_set('session.gc_maxlifetime', $expire);
        }
        if (empty($_COOKIE['PHPSESSID'])) {
            session_set_cookie_params($expire);
            session_start();
        } else {
            session_start();
            setcookie('PHPSESSID', session_id(), time() + $expire);
        }
    }
}
