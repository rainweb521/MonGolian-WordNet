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
class fanyici extends Model{
    /**
     * 主键默认自动识别
     */
//    protected $pk = 'uid';
// 设置当前模型对应的完整数据表名称
    protected $table = 'fanyici';

    /**根据id返回的信息
     * @param $id id
     * @return mixed 返回
     */
    public function get_Info($where=null){
        $data = fanyici::where($where)->find();
        if ($data!=null){
            return $data->getData();
        }else{
            return '';
        }
    }
    public function get_List($where=null){
        $data = $this->get_Info($where);
        if ($data==''){
            return '';
        }
        $list = fanyici::where($where)->select();
        return $list;
    }



}