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
class nounM extends Model{
    /**
     * 主键默认自动识别
     */
//    protected $pk = 'uid';
// 设置当前模型对应的完整数据表名称
    protected $table = 'new_noun';
    /**
     * @var array tree_root 用来存储根部的id
     *  tree_tmp 用来判断是否是第一次存储根部id
     */
    public $tree = array();
    public $tree_root = 0;
    public $tree_tmp = 0;
    /**根据id返回的信息
     * @param $id id
     * @return mixed 返回
     */
    public function get_Info($where=null){
        $data = nounM::where($where)->find();
        if ($data!=null){
            return $data->getData();
        }else{
            return '';
        }
    }
    public function get_Info_noun($where=null){
        $data = nounM::where($where)->find();
        if ($data==null){
            return '1';
        }else{
//            echo $data->getData('vdbt').'4444';
            return $data->getData();
        }
    }

    /** 用于查找根id 的函数，num表示查询次数，synset_id是要查询的上级id
     * @param $num
     * @param $synset_id
     * @return mixed
     */
    public function get_Root_Id($num,$synset_id){
        for ($i=0;$i<$num;$i++){
            $data = $this->get_Info_Id(array('No_ID'=>$synset_id));
            if ($data==''){
                break;
            }
            $tmp = $data['Semantic_class_No'];
            if ($tmp!=0){
                $synset_id = $tmp;
                /**
                 *  这里这段是新加入的，因为我发现，当前数据本来是没有子节点的，但当root的数量变了以后，lower的增加，节点数量也会增加
                 * 原来是get_Info_Id返回的数据为Semantic_class_No，而不是No_ID，
                 * 所以在这里加了一个判断，当是最后一次循环时，就返回No_ID，而不是Semantic_class_No
                 */
                if ($i==$num-1){
                    $synset_id = $data['No_ID'];
                }
            }

        }
        return $synset_id;
    }

    /** 与get_Root_Id函数配套使用，
     * @param null $where
     * @return mixed
     */
    public function get_Info_Id($where=null){
        $data = nounM::where($where)->find();
        if ($data!=null){
            return $data->getData();
        }else{
            return '';
        }
    }
    public function get_Root_noun($num,$vdbt){
        for ($i=0;$i<$num;$i++){
            $data = $this->get_Info(array('vdbt'=>$vdbt));
            $tmp = $this->get_Info_Vdbt(array('vdbt'=>$data['vdt']));
//            echo $tmp.'5555';
            if ($tmp!='1'){
//                echo $vdbt.'1111';
                $vdbt = $tmp;
            }
        }
//        echo $vdbt.'1111';
        return $vdbt;
    }
    /** 查找id个数，返回一个数组 */
    public function get_List_Id1($where=null){
//        $map['name'] = array('like','thinkphp%');
        /** @var 这里输入的是本级别字段名称，因为要查找的是归属于上级id 的所有其他字段 */
        $list = nounM::where($where)->column('No_ID');
        return $list;
    }

    /** 查询树状结构的函数，返回一个键值对形式的数据数组
     * @param $num 根id已经知道，num表示要往下查找的级别
     * @param $root_synset_id  当前id，是id不是上级id
     * @param $up_synset_id 当前id 的上级id，第一次进入时，写本id就可以
     * @param $line_num 每个节点的数量
     * @return mixed
     */
    public function get_Tree($num, $root_synset_id, $up_synset_id,$line_num,$select_id){
        global $tree_root,$tree_tmp;
        global $tree;
        /**
         * 当num为0，即查找级别为0时，退出，还是查找到的子节点为空时，退出
         */
//        if (($tree_root==$root_synset_id)&&($tree_tmp==1)){
//            return $tree;
//        }
//        if ($tree_tmp == 0){
//            $tree_root = $root_synset_id;
//            $tree_tmp = 1;
//        }
//        echo $num;
        if ($num!=0){
            /** 查找id 的个数，返回一个数组  所查询到的是归属于rootid 的子类的id */
            /** @var  这里输入的是上级id的字段，因为这是查找条件，查找那些上级id是本id 的数据，也就是归属于本id的数据 */
            $synset_id_arr = nounM::get_List_Id1(array('Semantic_class_No'=>$root_synset_id));
//            var_dump(count($synset_id_arr));exit();
            if ($synset_id_arr!=null){
                $sum = 1;
                $type = 0;
                /** 这里添加对select_id是否在数组中的判断 */
                $isin = in_array($select_id,$synset_id_arr);
                if($isin){
                    $type = 1;
                }
                foreach ($synset_id_arr as $synset_id){
//                    echo $synset_id.'<br>';
//                    $wn_chinese_model = new wn_chinese();
//                    $value = $wn_chinese_model->get_Info_Chinese(array('synset_id'=>$synset_id));
//                    if ($value=='1'){
//                        continue;
//                    }
                    /** 在这里加入...，判断line_num是否小于数组的元素个数 */
                    if ($line_num<count($synset_id_arr)){
                        $tree[strval($up_synset_id.'-'.'0')] = '.'.$synset_id;
                    }
                    /** 判断是不是最后一个要录入的子节点，如果 */
                    if ($synset_id==$select_id){
                        $type = 0;
                    }

                    if ($sum==$line_num){
                        if ($type==1){
                            if ($synset_id!=$select_id){
                                continue;
                            }
                        }
                    }

                    try{
                        $tree[strval($up_synset_id.'-'.$sum)] = $synset_id;
                    }catch (Exception $exception){
                        $tree[strval($up_synset_id.'-'.$sum)] = '';
                    }
                    $tree[strval($up_synset_id.'-'.$sum)] = $synset_id;
                    $sum ++ ;
                    if($sum>$line_num){
                        /** 因为树状图是反转90度的，所以在这里加...，会变成开头 */
//                        $tree[strval($up_synset_id.'-'.$sum)] = '...';
                        return $tree;
                    }
//                    if (array_key_exists(strval($up_synset_id),$tree[strval($up_synset_id)])){
//                        $tree[strval($up_synset_id)] = $tree[strval($up_synset_id)].$synset_id;
//                    }else{
//                        $tree[strval($up_synset_id)] = '';
//                    }
                    /***
                     * 这是打印出所有的数据，并且以一定的格式，输出的是一个很长的字符串，不同好截取，所以我放弃了，只作为打印参考
                     *
                     */
//                    $tree = $tree.'['.$up_synset_id.'=>'.$synset_id.'],<br>';
                    nounM::get_Tree($num-1, $synset_id, $up_synset_id.'-'.$synset_id,$line_num,$select_id);
//                    $tree = $tree.'],<br>';
//                    echo $tree.'<br>';
//                    var_dump($synset_id);echo "<br>";
//                    return $synset_id;
                }
//                if ($root_synset_id)
            }
        }
//        echo $root_synset_id.'<br>';
        return $tree;
    }

    /*** 直接生成语义树的函数，传入已经有键值对的数组，逐行分析，直接用json的格式添加，最后生成字符串
     * @param $num 语义树中数组的数量
     * @param $tree 语义树 这样的结构 0-102001223-102002490-102003441-2=>102003848
     * @param $root 根节点
     * @return string 返回的json字符串
     */
    public function get_Tree_Json($num, $tree,$root,$lang){
        /**
         * 保存上一级的数组，上一级数组中个数，上一级的结果集，空的json字符串
         * 判断是否为最后一个的sum判断
         */
        $old_local = array();
        $old_num = 0;
        $result = 0;
        $json = '';
        $sum = 1;
        /** 遍历数组，分别显示键名和值 **/
        foreach ($tree as $key=>$value) {
            /** 分隔键名 **/
            $local = explode('-', $key);
            $local_num = count($local);
            /**
             * 将synset_id更换为字符串了了了了了了
             */
            if ($lang==3){

                $value = '...';
//                $wn_chinese_model = new wn_chinese();
//                $value = $wn_chinese_model->get_Info_Chinese(array('synset_id'=>$value));
            }elseif ($lang==1){
                /** 扩展的.....的显示 **/
                $value2 = $value;
                if ($value[0]!='.'){
                    /** 蒙古文的显示 */
                    $data = nounM::get_Info_noun(['No_ID'=>$value]);
                    $value = $data['Mongolian'];
                }else{
                    /** 这里这么写，是因为在前端要获取跟随的字符串，如果只是传一个id，那么后台还需要再进行改动，这里我将.去除掉
                     *  然后再查找，查找到以后再加上.，前端直接过滤掉.，就可以用蒙文查询了
                     */
                    $value=str_replace('.','',$value);
                    $data = nounM::get_Info_noun(['No_ID'=>$value]);
                    $value = '.'.$data['Mongolian'];
                }

//                $mongolian_model = new mongolian();
//                $value = $mongolian_model->get_Info_Mongolian(array('synset_id'=>$value));
                /** 判断蒙古文是否存在
                后来在二叉树生成过程中判断了，就不在这里判断
                 **/
                //            if ($value=='0'){
                //                /**
                //                 * 判断是不是最后一个，如果是，进行关闭操作
                //                 */
                //                $sum ++;
                //                if ($sum==$num){
                //                    $json = $json.'}';
                //                    for ($i=0;$i<$local_num-2;$i++){
                //                        $json = $json.']}';
                //                    }
                //                }
                //                continue;
                //            }
            }
            /** 不同层级关闭的时候，子节点有一个}需要关闭，在这里写一个变量，用作判断**/
            $tmp_k = 1;

            if ($old_num==0){
                /**
                 * 第一个不去截取字符串，因为我觉得，第一个肯定是只有根节点相对于，不管他是单节点，还是多节点，一开始都是由根节点直接对应的
                 *
                 * 后来因为涉及到蒙古文的显示，蒙古文很少，有的没有，所以这样判断是草率的，
                 */
                $json = $json.'{"name":"'.$value.'"';
                /**
                 * 判断如果是第一个又是最后一个，进行关闭操作
                 */
//                $sum ++;
                if ($sum==$num){
                    $json = $json.'}';
                }
            }else{
//                $local = array_reverse($local);
//                $old_local = array_reverse($old_local);
                /** 如果子节点与结果集相同，说明是上一节点的子节点,不能闭合掉
                 ** 这里有个很傻的错误，数组是小一位的，而我不要最后一位，应该是减去2，不是减去1，好像之前一直都是这么干的
                 **/
                if ($local[$local_num-2]==$result){
                    $json = $json.', "children":[{"name":"'.$value.'"';
                }else{
                    /** 如果层级相同，说明是同级关系，则关闭上一个标签，然后再添加一个子节点**/
                    if ($old_num==$local_num){
                        $json = $json.'},{"name":"'.$value.'"';
                    }elseif ($old_num>$local_num){
                        /** 层级不同，先判断上一级比下一级大，
                         *  就用两个层级想对应的节点去判断，如果一样，说明是同级，如果上一级比下一级大，则大一级，填充一个关闭
                         **  直到判断到层级相同，然后填入节点值，退出循环
                         * */
                        for ($i=$old_num-2;$i>=0;$i--){
                            if ($i>$local_num-2){
                                /** 用作上一小级的关闭 */
                                if ($tmp_k==1){$json = $json.'}';$tmp_k=0;}
                                $json = $json.']}';
                            }else{
                                if ($local[$i]==$old_local[$i]){
                                    $json = $json.',{"name":"'.$value.'"';
                                    break;
                                }
                            }
                        }
                    }else{
                        /**
                         * 判断上一级小于下一级，
                         */
                        for ($i=$local_num-2;$i>=0;$i--){
                            if ($i>$old_num-2){
                                $json = $json.']}';
                            }else{
                                if ($local[$i]==$old_local[$i]){
                                    $json = $json.',{"name":"'.$value.'"';
                                    break;
                                }
                            }
                        }
                    }
                }
                /**
                 * 判断是不是最后一个，如果是，进行关闭操作
                 */
                $sum ++;
                if ($sum==$num){
                    $json = $json.'}';
                    for ($i=0;$i<$local_num-2;$i++){
                        $json = $json.']}';
                    }
                }
            }
            $old_local = $local;$old_num = $local_num;$result = $value2;
        }
//        echo $json;
        return $json;
    }
    public function get_Info_Chinese($where=null){
        $data = vdt::where($where)->find();
        if ($data==null){
            return '1';
        }else{
            return $data->getData('chinese');
        }
    }

    public function get_NounList($where=null){
        $list = nounM::where($where)->select();
        return $list;
    }
    public function get_List_Id($where=null){
        $list = vdt::where($where)->column('synset_id');
        return $list;
    }
    public function get_Num($where=null){
//        $map['name'] = array('like','thinkphp%');
        $list = vdt::count('id');
        return $list;
    }
    public function insert_Info($data){
        vdt::save($data);
    }
    public function save_Info($id,$data){
        vdt::save($data,array('id'=>$id));
    }
    public function delete_Info($id){
        vdt::where(array('id'=>$id))->delete();
//        $data = $this->get_Cmdic1Info(array('id'=>$id));
//        $this->_db->delete(array('id'=>$id));
    }
    public function get_Max(){
        $max = vdt::max('id');
        return $max;
    }

}