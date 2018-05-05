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
class chineseM extends Model{
    /**
     * 主键默认自动识别
     */
//    protected $pk = 'uid';
// 设置当前模型对应的完整数据表名称
    protected $table = 'cn_word';

    /**根据id返回的信息
     * @param $id id
     * @return mixed 返回
     */
    public function get_Info($where=null){
//        $where['id'] = $id;
        $data = chineseM::where($where)->find();
        return $data->getData();
    }
    public function get_Info_Word($where=null){
        $data = chineseM::where($where)->find();
        return $data->getData('word');
    }
    public function get_Info_Id($where=null){
        $data = chineseM::where($where)->find();
        $result = $data->getData('synset_id');
    }
    public function get_List($where=null){
        $list = chineseM::where($where)->select();
        return $list;
    }
    public function get_List_Id($where=null){
        $list = chineseM::where($where)->column('synset_id');
        return $list;
    }
    public function get_Num($where=null){
        $list = chineseM::count('id');
        return $list;
    }
    public function insert_Info($data){
        chineseM::save($data);
    }
    public function save_Info($id,$data){
        chineseM::save($data,array('id'=>$id));
    }
    public function delete_Info($id){
        chineseM::where(array('id'=>$id))->delete();
//        $data = $this->get_Cmdic1Info(array('id'=>$id));
//        $this->_db->delete(array('id'=>$id));
    }
    public function get_Max(){
        $max = chineseM::max('id');
        return $max;
    }

}