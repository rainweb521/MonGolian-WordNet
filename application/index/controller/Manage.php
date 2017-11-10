<?php
namespace app\index\controller;

use app\config\model\menggu;
use \think\Request;
use think\Controller;
use \think\View;
class Manage extends Common {
    /**
     *
     */
    public function index(){
        $page = Request::instance()->get('page',1);
        $text = Request::instance()->get('text','');
//        $page = request('get','int','page',1);
//        $text = request('get','str','text','');
        $menggu_model = new menggu();
        if ($text!=''){
            $allcn = preg_match("/^[".chr(0xa1)."-".chr(0xff)."]+$/",$text[0]);
            if ($allcn){
                $where['ciyu'] = $text;
            } else {
                $where['ladin'] = $text;
            }
            $list = $menggu_model->get_Cmdic1List($where);
            $page_list = array('p'=>'','a'=>'');
//            var_dump($list);exit();
        }else{
            if ($page==-1){
                $all = ($menggu_model->get_Cmdic1Num());
                $page = intval($all/10);
                if ($page*10!=$all){
                    $page = $page + 1;
                }
            }
            $list = array();
            /**
             * 使用id来获取值的确会错，刚刚出现了一个bug，就是222506以后的所有数据在本页面中不显示，是因为我删除了一个数据，所以
             * id的连续性出现中断，导致if判断里直接break了，
             *
             * 这里的这个问题我不想重新在用别的方法去写一遍了，就用最简单的方法，挑选够10条数据就OK，不去判断什么，这样肯定是有问题的
             * ，而且以后会很严重，那就是如果删除的数据多了，用page来获取目标开始id就失效了，页面也就链接不上了，但目前先这样吧，
             *
             * 更好的解决方法有很多，1，不删除数据，设置一个state字段，修改字段的值就行，但是这样使用page获取目标开始id也会出错
             * 2. 不使用id来作为目标开始数，而用确切的位置来定位，但用什么定位我也没有想到什么好的方法，以后遇到不可避免的开发时，再去研究吧
             */

            /**
             * 直接使用thinkphp给的分页接口
             * 分页接口有问题，我还是使用limit('4,5')这个来获取指定的记录条数
             */
            $list = $menggu_model->get_Cmdic1Page($page);
//            var_dump($list);
//            exit();
//            $sum = 0;
//            $max_id = D('Cmdic1')->get_Cmdic1Max();
//            for ($i=$page*10-10+1;$i<=$max_id;$i++){
//                /**
//                 * 如果数据的条数发生改变，这种方法就会出现bug，所以用下面的判断null比较好一些
//                 * 并且还能用这个来判断页数，确定是不是最后一页
//                 */
////            if ($i>222506){
////                break;
////            }
//                $data = D('Cmdic1')->get_Cmdic1Info(array('id'=>$i));
//                if ($data['id']==''){
//                    continue;
//                }else{
//                    $sum = $sum + 1;
//                }
//                if ($sum == 11){
//                    break;
//                }
//                array_push($list,$data);
//            }
//        $all = count(D('Cmdic1')->get_Cmdic1List(array()),COUNT_RECURSIVE);
            $all = ($menggu_model->get_Cmdic1Num());

            $all2 = intval($all/10);
            if ($all2*10!=$all){
                $all2 = $all2 + 1;
            }
            $page_list = array();
            for ($i=3;$i>=1;$i--){
                if ($page-$i<=0){
                    continue;
                }else{
                    array_push($page_list,array('p'=>$page-$i,'a'=>''));
                }
            }
            array_push($page_list,array('p'=>$page,'a'=>'am-active'));
            for ($i=1;$i<=3;$i++){
                if ($page+$i>$all2){
                    continue;
                }else{
                    array_push($page_list,array('p'=>$page+$i,'a'=>''));
                }
            }
            $this->assign('now_page',$page);
            $this->assign('page_list',$page_list);
        }

//        var_dump($page_list);exit();
        $this->assign('list',$list);
        return view('index');
    }
    public function form(){
        $type = Request::instance()->get('type',0);
//        $type = request('get','int','type',0);
        $data = array();
        $state = '';
        $menggu_model = new menggu();
        if ($type == 1){
            $id = Request::instance()->post('id',0);
            $data['cixing'] = Request::instance()->post('cixing','');
            $data['ciyu'] = Request::instance()->post('ciyu','');
            $data['pinyin'] = Request::instance()->post('pinyin','');
            $data['yiwen'] = Request::instance()->post('yiwen','');
            $data['liju'] = Request::instance()->post('liju','');
            $data['ladin'] = Request::instance()->post('ladin','');
            $data['menggu'] = Request::instance()->post('menggu','');
            $data['hanyu'] = Request::instance()->post('hanyu','');
            $data['zxd'] = Request::instance()->post('zxd','');
            $data['beizhu'] = Request::instance()->post('beizhu','');
            if ($id != 0){
                $menggu_model->save_Cmdic1Info($id,$data);
                $state = '信息修改成功';
            }else{
                $menggu_model->insert_Cmdic1Info($data);
                $state = '信息添加成功';
            }
            $data = $menggu_model->get_Cmdic1Info($data);
        }else{
            $id = Request::instance()->get('id',0);
            if ($id != 0){
                $data = $menggu_model->get_Cmdic1Info(array('id'=>$id));
            }else{
                $data['id'] = '';
                $data['cixing'] = '';
                $data['ciyu'] = '';
                $data['pinyin'] = '';
                $data['yiwen'] = '';
                $data['liju'] = '';
                $data['ladin'] = '';
                $data['menggu'] = '';
                $data['hanyu'] = '';
                $data['zxd'] = '';
                $data['beizhu'] = '';
            }
        }
        $this->assign('state',$state);
        $this->assign('list',$data);
        return view('form');
    }
    public function delete(){
        $id = Request::instance()->get('id',0);
        $page = Request::instance()->get('page',1);
//        $id = request('get','int','id',0);
//        $page = request('get','int','page',1);
        $menggu_model = new menggu();
        if ($id != 0){
            $menggu_model->delete_Cmdic1Info($id);
        }
        $this->success('删除成功','/index.php/index/manage/index?page='.$page);
    }
}
