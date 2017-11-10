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
class mongolian extends Model{
    /**
     * 主键默认自动识别
     */
//    protected $pk = 'uid';
// 设置当前模型对应的完整数据表名称
    protected $table = 'mongolian';

    /**根据id返回的信息
     * @param $id id
     * @return mixed 返回
     */
    public function get_Info($where=null){
//        $where['id'] = $id;
        $data = mongolian::where($where)->find();
        return $data->getData();
    }
    public function get_Info_Mongolian($where=null){
//        $where['id'] = $id;
        $data = mongolian::where($where)->find();
        if ($data==null){
//            var_dump($data);
            return '0';
        }else{
            return $data->getData('mongolian');
        }
    }
    public function get_List($where=null){
        $data = mongolian::where($where)->find();
        return $data->getData();
    }
}