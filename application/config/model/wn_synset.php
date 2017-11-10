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
class wn_synset extends Model{
    /**
     * 主键默认自动识别
     */
//    protected $pk = 'uid';
// 设置当前模型对应的完整数据表名称
    protected $table = 'wn_synset';

    /**根据id返回的信息
     * @param $id id
     * @return mixed 返回
     */
    public function get_Info($where=null){
//        $where['id'] = $id;
        $data = wn_synset::where($where)->find();
        return $data->getData();
    }
    public function get_Info_Word($where=null){
        $data = wn_synset::where($where)->find();
        return $data->getData('word');
    }
    public function get_Info_Id($where=null){
        $data = wn_synset::where($where)->find();
        return $data->getData('synset_id');
    }
    public function get_List($where=null){
//        $map['name'] = array('like','thinkphp%');
        $list = wn_synset::where($where)->select();
        return $list;
    }
    public function get_List_Id($where=null){
//        $map['name'] = array('like','thinkphp%');
        $list = wn_synset::where($where)->column('synset_id');
        return $list;
    }
    public function get_Num($where=null){
//        $map['name'] = array('like','thinkphp%');
        $list = wn_synset::count('id');
        return $list;
    }
    public function insert_Info($data){
        wn_synset::save($data);
    }
    public function save_Info($id,$data){
        wn_synset::save($data,array('id'=>$id));
    }
    public function delete_Info($id){
        wn_synset::where(array('id'=>$id))->delete();
//        $data = $this->get_Cmdic1Info(array('id'=>$id));
//        $this->_db->delete(array('id'=>$id));
    }
    public function get_Max(){
        $max = wn_synset::max('id');
        return $max;
    }

}