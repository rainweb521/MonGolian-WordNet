<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2017/7/26
 * Time: 16:10
 */
namespace app\config\model;

use phpDocumentor\Reflection\Types\Null_;
use think\image\Exception;
use think\Model;
class wn_hypernym extends Model{

    /**
     * 主键默认自动识别
     */
//    protected $pk = 'uid';
// 设置当前模型对应的完整数据表名称
    protected $table = 'wn_hypernym';
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
//        $where['id'] = $id;
        $data = wn_hypernym::where($where)->find();
        return $data->getData();
    }
    public function get_Root_Id($num,$synset_id){
        for ($i=0;$i<$num;$i++){
            $tmp = $this->get_Info_Id(array('synset_id_1'=>$synset_id));
            if ($tmp!=0){
                $synset_id = $tmp;
            }
        }
        return $synset_id;
    }
    public function get_Info_Id($where=null){
        header("Content-type: text/html; charset=utf-8");
//        $where['id'] = $id;
        $data = wn_hypernym::where($where)->find();
        if ($data==null){
            return 0;
        }else{
//            $wn = new wn_synset();
//            var_dump($wn->get_List_Id(array('synset_id'=>$data->getData('synset_id_2'))));
//            echo '<br>';
            return $data->getData('synset_id_2');
        }
    }
    public function get_List_Id1($where=null){
//        $map['name'] = array('like','thinkphp%');
        $list = wn_hypernym::where($where)->column('synset_id_1');
        return $list;
    }
    public function get_Tree($num, $root_synset_id, $up_synset_id){
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
        if ($num!=0){
            $synset_id_arr = wn_hypernym::get_List_Id1(array('synset_id_2'=>$root_synset_id));
//            var_dump($synset_id_arr);
            if ($synset_id_arr!=null){
                $sum = 1;
                foreach ($synset_id_arr as $synset_id){
                    $mongolian_model = new mongolian();
                    $value = $mongolian_model->get_Info_Mongolian(array('synset_id'=>$synset_id));
                    if ($value=='0'){
                        continue;
                    }
                    /**
                     * 在使用try的时候，因为未定义数组会报错，所以用了try，但是，在catch中，数组赋予空值，得重新赋值
                     * 而在try中，因为我使用的是键值对的形式，所以出现了值的覆盖情况，因为一个根部对应好几个子节点，最后
                     * 覆盖的就只剩下一个子节点了，但又不能加入两个相同的键来进行赋值，所以，把所有的值放在一起
                     *
                     * 完全不可以，很痛苦的未定义数组提示，这个提示怎么也去不掉，我想到了编号的方法，就数据编号，这样也比较好看一些
                     * 希望传出去以后，好截取和替换一些，数据整体没有问题了，
                     *
                     * tree不是数组，无论如何，都不是数组，不知道为什么，就不是数组
                     */
                    try{
                        $tree[strval($up_synset_id.'-'.$sum)] = $synset_id;
                    }catch (Exception $exception){
                        $tree[strval($up_synset_id.'-'.$sum)] = '';
                    }
                    $tree[strval($up_synset_id.'-'.$sum)] = $synset_id;
                    $sum ++ ;
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
                    wn_hypernym::get_Tree($num-1, $synset_id, $up_synset_id.'-'.$synset_id);
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
    public function get_Tree_Json($num, $tree,$root){
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
            /** 英文的显示 **/
//            $value2 = $value;
//            $wn_synset_model = new wn_synset();
//            $value = $wn_synset_model->get_Info_Word(array('synset_id'=>$value));

            /** 蒙古文的显示 */
            $value2 = $value;
            $mongolian_model = new mongolian();
            $value = $mongolian_model->get_Info_Mongolian(array('synset_id'=>$value));
            /** 判断蒙古文是否存在 **/
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
            /** 不同层级关闭的时候，子节点有一个}需要关闭，在这里写一个变量，用作判断**/
            $tmp_k = 1;

            if ($old_num==0){
                /**
                 * 第一个不去截取字符串，因为我觉得，第一个肯定是只有根节点相对于，不管他是单节点，还是多节点，一开始都是由根节点直接对应的
                 *
                 * 后来因为涉及到蒙古文的显示，蒙古文很少，有的没有，所以这样判断是草率的，
                 */
                $json = $json.'{"name":"'.$value.'"';
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
}