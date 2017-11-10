<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2017/7/26
 * Time: 16:10
 */
namespace app\config\model;

use phpDocumentor\Reflection\Types\Null_;
use think\Model;
class menggu extends Model{
    /**
     * 主键默认自动识别
     */
//    protected $pk = 'uid';
// 设置当前模型对应的完整数据表名称
    protected $table = 'cmdic1';

    /**根据id返回的信息
     * @param $id id
     * @return mixed 返回
     */
    public function get_Cmdic1Info($where=null){
//        $where['id'] = $id;
        $data = menggu::where($where)->find();
        return $data->getData();
    }
    public function get_Cmdic1List($where=null){
//        $map['name'] = array('like','thinkphp%');
        $list = menggu::where($where)->select();
        return $list;
    }
    public function get_Cmdic1Num($where=null){
//        $map['name'] = array('like','thinkphp%');
        $list = menggu::count('id');
        return $list;
    }
    public function insert_Cmdic1Info($data){
        menggu::save($data);
    }
    public function save_Cmdic1Info($id,$data){
        menggu::save($data,array('id'=>$id));
    }
    public function delete_Cmdic1Info($id){
        menggu::where(array('id'=>$id))->delete();
//        $data = $this->get_Cmdic1Info(array('id'=>$id));
//        $this->_db->delete(array('id'=>$id));
    }
    public function get_Cmdic1Max(){
        $max = menggu::max('id');
        return $max;
    }
    public function get_Cmdic1Page($page){
//        $all_num = $this->get_Cmdic1Num();
//        $max_id = $this->get_Cmdic1Max();
//            for ($i=$page*10-10+1;$i<=$max_id;$i++){
//                /**
//                 * 如果数据的条数发生改变，这种方法就会出现bug，所以用下面的判断null比较好一些
//                 * 并且还能用这个来判断页数，确定是不是最后一页
//                 */
//            if ($i>$all_num){
//                break;
//            }
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
//        $Page = $this->_db->limit(($page*10-10).','.$page*10-1)->select();
        $Page = menggu::limit(($page*10-10).',10')->select();
//        $list = $this->_db->limit($p->firstRow.','.$p->listRows)->select();
        return $Page;
    }
}