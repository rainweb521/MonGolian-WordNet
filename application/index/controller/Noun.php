<?php
namespace app\index\controller;
use app\config\model\cilei;
use app\config\model\fanyici;
use app\config\model\jinyici;
use app\config\model\menggu;
use app\config\model\mongolian;
use app\config\model\nounM;
use app\config\model\wn_chinese;
use app\config\model\wn_hypernym;
use app\config\model\wn_synset;
use \think\Request;
use think\Controller;
use \think\View;
class Noun extends Controller {
    public function index(){
        $mengu_model = new menggu();
        $mengu =array();
        $mengu = $mengu_model->get_Cmdic1Info(['id'=>'110134']);
//        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
        return view('index',array('title'=>$mengu['menggu']));
    }
    public function index3(){

        return \view('index3');
    }

    /**
     * 在查找时用来在输入框进行匹配提示的
     */
    public function select_json(){
        $content = Request::instance()->get('content','');
        /** 如果没有输入，则返回空的json格式 */
        if ($content == ''){
            echo 'queryList({"q":"","p":false,"bs":"","csor":"0","status":770,"s":[]});';
        }else{
            $ladin = array('like',$content.'%');
            /** @var  $allcn  用来判断中文还是英文 */
//            $allcn = preg_match("/^[".chr(0xa1)."-".chr(0xff)."]+$/",$content[0]);
//            $where = array();
            $noun_model = new nounM();
//            if ($allcn){
                $where['Mongolian'] = $ladin;
//                $where['Chinese'] = $ladin;
                $list = $noun_model->get_NounList($where);
                $text = '';
                /** @var  $item 拼接json字符串 */
            foreach ($list as $item) {
                    $text = $text.'"'.$item['Mongolian'].'",';
                }
//            }

            echo 'queryList({q:"123",p:false,s:['.$text.']});';
        }
    }
    public function wordnet_show(){
        header("Content-type: text/html; charset=utf-8");
        $root = Request::instance()->get('root',0);
        $lower = Request::instance()->get('lower',0);
        $line_num = Request::instance()->get('line_num',20);
        $content = Request::instance()->get('content','');
        $type = Request::instance()->get('type',0);
        $lang = Request::instance()->get('lang',1);
        $wn_synset_model = new wn_synset();
        $wn_hypernym_model = new wn_hypernym();
        $mongolian_model = new mongolian();
        $wn_chinese_model = new wn_chinese();
        $root_synset_id = $now_synset_id = 0;

        $noun_model = new nounM();
            /** 显示为蒙古文树 */
            if ($type==2){
                $data = $noun_model->get_Info_noun(['Mongolian'=>$content]);
            }else{
                $data = $noun_model->get_Info_noun(['Chinese'=>$content]);
            }
        /** 因为后期有修改，所以在这里先渲染到页面里 */
            $this->assign('data',$data);
        /**
         *  获取近义词和词类的数据列表，近义词为wordnet_synet1
         */
        $fanyici_model = new fanyici();
        /** 在导入Excel数据时，存在空格，所以使用like来查询 */
        $fanyici_data = $fanyici_model->get_Info(['mongolian'=>array('like','%'.$content.'%')]);
        $fanyici_list = array();
        if ($fanyici_data!=''){
            $fanyici_list = $fanyici_model->get_List(['flag'=>$fanyici_data['flag']]);
        }
//        var_dump($fanyici_data);exit();
        $this->assign('fanyici',$fanyici_list);
//            $jinyici_model = new jinyici();
//            $jinyici_data = $jinyici_model->get_List(['MONGOL'=>$content]);
//            $cilei_model = new cilei();
//            $cilei_data = $cilei_model->get_List(['MONGOL'=>$content]);
//            var_dump($jinyici_data);exit();

//            $this->assign('jinyici',$jinyici_data);
//            $this->assign('cilei',$cilei_data);
//            var_dump($data);exit();

//            return \view('tree');

            $now_Semantic_class_No = $data['Semantic_class_No'];
            $now_No_ID = $data['No_ID'];

            /*****  先设置root的id为本id，再去查找root的id*/
            $root_synset_id = $now_No_ID;
            /** 判断root是不是等于0，如果是则表示为当前id，不是的话就需要去查找root次数的根id，lower不用查找，因为在后来直接会往下进行寻找 */
            if ($root!=0){
                /** @var  这里需要注意，传入的第二个参数应该是当前数据的上级id，这样就会减少一遍查询，直接到函数中查找上一级的数据 */
                /**
                 *  这里get_Root_Id修改过，因为我发现，当前数据本来是没有子节点的，但当root的数量变了以后，lower的增加，节点数量也会增加
                 * 原来是get_Info_Id返回的数据为Semantic_class_No，而不是No_ID，
                 * 所以在这里加了一个判断，当是最后一次循环时，就返回No_ID，而不是Semantic_class_No
                 */
                $root_synset_id = $noun_model->get_Root_Id($root, $now_Semantic_class_No);
            }
//            echo $root_synset_id;exit();
            $tree = $noun_model->get_Tree($lower + $root, $root_synset_id,$root_synset_id,$line_num,$now_No_ID);
//            返回的tree是类似于这种形状的{ ["14278-1"]=> int(14279) ["14278-14279-1"]=> int(3178) ["14278-14279-2"]=> int(12551)
        // 这只是提取出的形式数据，以键值对的形式来展现，需要通过get_Tree_Json来处理才能显示

//        var_dump($tree);exit();
            /*  此时假设root的id就是本id，直接使用上面查找到的data数据*/
        if ($root_synset_id==''){
            /** 这种情况是可能查找根节点的时候，根节点不存在上级节点，所以会查找到空值，为了防止报错，直接使用根节点id */
            $root_synset_id = $now_No_ID;
        }
            $root_data = $noun_model->get_Info_noun(['No_ID'=>$root_synset_id]);
            $root_text = $root_data['Mongolian'];
//            var_dump($root_synset_id);exit();
//            if ($root_text=='0'){
//                $root_text = '蒙古文中没有对应的此节点';
//            }


        /** 判断显示方式是否为 直接显示数组键值对，便于分析 */
//        if ($show==1){
//            echo 'root:'.$root_synset_id.'<br>';
//            foreach ($tree as $key=>$value){echo $key.'=>'.$value.'<br>';}
//            exit();
//        }
        if ($tree!=null){
            $data = $noun_model->get_Tree_Json(count($tree),$tree,$root_synset_id,$lang);
        }else {$data = '';}

        $data = '{"name":"'.$root_text.'", "children":['.$data.']}';
        /** 判断显示方式是否为 直接显示Json格式的数组，便于分析复制 */
//        if ($show==2){
//            echo 'root:'.$root_synset_id.'<br>';
//            echo $data;
//            exit();
//        }
//        return ($data);
//        exit();
        $myfile = fopen("public/tree.json", "w");
        fwrite($myfile, $data);
        fclose($myfile);
        if ($lang==1){
            return \view('tree2');
        }else{
            return \view('english_tree');
        }
    }



    public function wordnet_show_backup(){
        $root = Request::instance()->get('root',0);
        $lower = Request::instance()->get('lower',0);
        $content = Request::instance()->get('content','');
        $show = Request::instance()->get('show',0);
        $lang = Request::instance()->get('lang',1);
        $wn_synset_model = new wn_synset();
        $wn_hypernym_model = new wn_hypernym();
        $now_synset_id = $wn_synset_model->get_Info_Id(array('word'=>$content));
//        var_dump($now_synset_id);
//        exit();
        $root_synset_id = $now_synset_id;
        if ($root!=0){
            /**
             * 去寻找对应的上级id，查找指定的级别
             */
            $root_synset_id = $wn_hypernym_model->get_Root_Id($root, $now_synset_id);
        }
        /**
         *  已经获得根部的ID，可以使用递归去查找子ID了
         * 传值的时候，我设置递归有三个参数，num代表级别，root_synset_id是查找到的根部id，也就是从根部查询，
         * 而第三个是up_synset_id，是当前子节点的上一节点，这样就能将所有的节点都连接起来，一级一级的，很棒的一个设计，
         * 这是后来往回传值时我所想到的，有了这个值的设定，能够建立起二叉树的整个连接，哈哈哈哈哈
         * 当然，在递归中，up_synset_id是不断用.来连接的，递归越深，up_synset_id也越长
         */
        if ($lang==2){
            /** 显示为英文树 */
            $tree = $wn_hypernym_model->get_Tree_English($lower + $root, $root_synset_id,$root_synset_id);
        }elseif ($lang==1){
            $tree = $wn_hypernym_model->get_Tree($lower + $root, $root_synset_id,$root_synset_id);
        }elseif ($lang==3){
            $tree = $wn_hypernym_model->get_Tree_Chinese($lower + $root, $root_synset_id,$root_synset_id);
//            var_dump($tree);
//            exit();
        }
        /** 判断显示方式是否为 直接显示数组键值对，便于分析 */
        if ($show==1){
            echo 'root:'.$root_synset_id.'<br>';
            foreach ($tree as $key=>$value){echo $key.'=>'.$value.'<br>';}
            exit();
        }
        /***
         * 因为传回来的数组是0-102001223-102002490-102003441-1=>102003573，这种格式的，数组的键已经包括了根节点所对应的子节点
         * 所以我得先遍历数组，然后再将数组的键进行分割，依造名字来进行存储，这就是我的笨办法
         */

        /**
         * 下面这个get_Tree_Json是第四个方法，是忽然想到的，以前的思路是添加到数组里，然后再将数组转换为json格式，但后来发现，为什么我不直接
         * 在分隔的时候拼接成json字符串，注意是拼接，想法很简单，但实现起来异常复杂，拼接过程有很多需要注意的地方，什么时候添加子节点，什么时候闭合括号
         * 闭合括号有两种，子节点也有各种各样的，整个设计出来的很巧妙，采用许多判断，临时值，最后直接返回json字符串，
         * 这个方法有弊端，但能写出来已经是很完美，毕竟写了100多行实现，我是对比着数组键值一个一个对比出来的
         */
        /** Tree 得到的树有时是空的，因为蒙文太少了，当为空的时候，就不进行下一步操作，防止在生成树的时候报错
         lang 用作语言判断
         */
        if ($tree!=null){
            $data = $wn_hypernym_model->get_Tree_Json(count($tree),$tree,$root_synset_id,$lang);
        }else {$data = '';}
        $root_text = $wn_synset_model->get_Info(array('synset_id'=>$root_synset_id));
        $data = '{"name":"'.$root_text['word'].'", "children":['.$data.']}';
        /** 判断显示方式是否为 直接显示Json格式的数组，便于分析复制 */
        if ($show==2){
            echo 'root:'.$root_synset_id.'<br>';
            echo $data;
            exit();
        }
        $myfile = fopen("public/tree.json", "w");
        fwrite($myfile, $data);
        fclose($myfile);
        if ($lang==1){
            return \view('tree');
        }else{
            return \view('english_tree');
        }
//        foreach ($tree as $key=>$value) {

//            echo $key."=>".$value;
//            echo '<br>';
//            $local = explode('-',$key);
//            $local_num = count($local);
//            for ($i=0;$i<$local_num-1;$i++)
//            {

                /**
                 * 表示如果存在以这个名字为索引的数组，我就不管了，如果不存在，我就新建一个，以防止数组未索引的报错
                 * 第一次出错了，array_push($data[$local[$i]],$value);，代表所有的数组都是以data数组作为根部数组的
                 * 这样失去了连接的意义，
                 * 第二个方法如下，一堆的代码就是，每一层我都重新写一个if去判断，然后存储，好处就是应该实现了，坏处就是，我只写到第六级，
                 * 所以只能判断第六级，而且每级代码巨长，再写到20级，我怕屏幕不够放，
                 * 第三个办法是在写到第六级的时候想到的，又看了一个遍历数，因为级别是确定的，先用一个循环，条件就是num级别，然后倒着去分隔每一个数组
                 * 分隔完一级后，添加进去，每次都只分隔固定的级别，不然会打乱节点之间的连接，没错就是这样，然后到根节点
                 */
                // 第一层
//                echo 'local::::'.$local[$i];
//                if ($local_num-2==0){
//                    if (!array_key_exists($local[$local_num-1],$data)){
//                        $data[$local[$local_num-1]] = array();
//                        array_push($data[$local[$local_num-1]],$value);
//                    }else{
//                        array_push($data[$local[$local_num-1]],$value);
//                    }
//                }elseif (($local_num-2)==1){
//                    if (!array_key_exists($local[$local_num-1],$data[$local[$local_num-1]])){
//                        $data[$local[$local_num-2]][$local[$local_num-1]] = array();
//                        array_push($data[$local[$local_num-2]][$local[$local_num-1]],$value);
//                    }else{
//                        array_push($data[$local[$local_num-2]][$local[$local_num-1]],$value);
//                    }
//                }elseif ($local_num-2==2){
//                    if (!array_key_exists($local[$local_num-1],$data[$local[$local_num-2]][$local[$local_num-1]])){
//                        $data[$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]] = array();
//                        array_push($data[$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]],$value);
//                    }else{
//                        array_push($data[$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]],$value);
//                    }
//                }elseif ($local_num-2==3){
//                    if (!array_key_exists($local[$local_num-1],$data[$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]])){
//                        $data[$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]] = array();
//                        array_push($data[$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]],$value);
//                    }else{
//                        array_push($data[$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]],$value);
//                    }
//                }elseif ($local_num-2==4){
//                    if (!array_key_exists($local[$local_num-1],$data[$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]])){
//                        $data[$local[$local_num-5]][$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]] = array();
//                        array_push($data[$local[$local_num-5]][$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]],$value);
//                    }else{
//                        array_push($data[$local[$local_num-5]][$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]],$value);
//                    }
//                }elseif ($local_num-2==5){
//                    if (!array_key_exists($local[$local_num-1],$data[$local[$local_num-5]][$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]])){
//                        $data[$local[$local_num-6]][$local[$local_num-5]][$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]] = array();
//                        array_push($data[$local[$local_num-6]][$local[$local_num-5]][$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]],$value);
//                    }else{
//                        array_push($data[$local[$local_num-6]][$local[$local_num-5]][$local[$local_num-4]][$local[$local_num-3]][$local[$local_num-2]][$local[$local_num-1]],$value);
//                    }
//                }

//            }
//            exit();
//        }
//        var_dump($data);
//        var_dump(json_encode(array_values($data), JSON_FORCE_OBJECT));
//        exit();

    }
    public function wordnet_json(){
        $content = Request::instance()->get('content','');
        if ($content == ''){
            echo 'queryList({"q":"","p":false,"bs":"","csor":"0","status":770,"s":[]});';
        }else{
            $ladin = array('like',$content.'%');
            $allcn = preg_match("/^[".chr(0xa1)."-".chr(0xff)."]+$/",$content[0]);
//            $where = array();
            $wn_synset_model = new wn_synset();
            $wn_chinese_model = new wn_chinese();
//                $list = $wn_synset_model->get_List($where);
//                $text = '';
//                foreach ($list as $item) {
//                    $text = $text.'"'.$item['word'].'",';
//                }
            if ($allcn){
                $where['chinese'] = $ladin;
                $list = $wn_chinese_model->get_List($where);
                $text = '';
                foreach ($list as $item) {
                    $text = $text.'"'.$item['chinese'].'",';
                }
            } else {
                $where['word'] = $ladin;
                $list = $wn_synset_model->get_List($where);
                $text = '';
                foreach ($list as $item) {
                    $text = $text.'"'.$item['word'].'",';
                }
            }
            echo 'queryList({q:"123",p:false,s:['.$text.']});';
        }
    }
    public function vdt(){

        return view('vdt');
    }
    public function vdt_show(){
        header("Content-type: text/html; charset=utf-8");
        $root = Request::instance()->get('root',0);
        $lower = Request::instance()->get('lower',0);
        $content = Request::instance()->get('content','');
        $show = Request::instance()->get('show',0);
        $lang = Request::instance()->get('lang',1);
        $lang = 3;
        $wn_synset_model = new wn_synset();
        $wn_hypernym_model = new wn_hypernym();
        $mongolian_model = new mongolian();
        $wn_chinese_model = new wn_chinese();
        $vdt_model = new vdt();
        $root_synset_id = $now_synset_id = 0;
        if ($lang==2){
            /** 显示为英文树 */
            $now_synset_id = $wn_synset_model->get_Info_Id(array('word'=>$content));
            $root_synset_id = $now_synset_id;
            if ($root!=0){
                $root_synset_id = $wn_hypernym_model->get_Root_Id($root, $now_synset_id);
            }
            $tree = $wn_hypernym_model->get_Tree_English($lower + $root, $root_synset_id,$root_synset_id);
            $root_text = $wn_synset_model->get_Info_Word(array('synset_id'=>$root_synset_id));
            if ($root==0){
                $root_text = $content;
            }
        }elseif ($lang==1){
            /** 显示为蒙古文树 */
            $now_synset_id = $wn_synset_model->get_Info_Id(array('word'=>$content));

            $root_synset_id = $now_synset_id;
            if ($root!=0){
                $root_synset_id = $wn_hypernym_model->get_Root_Id($root, $now_synset_id);
            }
            $tree = $wn_hypernym_model->get_Tree($lower + $root, $root_synset_id,$root_synset_id);
            $root_text = $mongolian_model->get_Info_Mongolian(array('synset_id'=>$root_synset_id));
            if ($root_text=='0'){
                $root_text = '蒙古文中没有对应的此节点';
            }
        }elseif ($lang==3){
            /** 显示为中文树 */
            $now_vdbt = $vdt_model->get_Info_Vdbt(array('chinese'=>$content));
//            echo $now_vdbt;
            $root_vdbt = $now_vdbt;
            if ($root!=0){
                $root_vdbt = $vdt_model->get_Root_Vdbt($root, $now_vdbt);
            }
            echo $root_vdbt;
//            exit();
            $tree = $vdt_model->get_Tree_Chinese($lower + $root, $root_vdbt,$root_vdbt);
            $root_text = $vdt_model->get_Info_Chinese(array('vdt'=>$root_vdbt));
            if ($root==0){
                $root_text = $content;
            }
        }

        /** 判断显示方式是否为 直接显示数组键值对，便于分析 */
        if ($show==1){
            echo 'root:'.$root_vdbt.'<br>';
//            var_dump($tree);
            foreach ($tree as $key=>$value){echo $key.'=>'.$value.'<br>';}
            exit();
        }
        if ($tree!=null){
            $data = $vdt_model->get_Tree_Json(count($tree),$tree,$root_vdbt,$lang);
        }else {$data = '';}

        $data = '{"name":"'.$root_text.'", "children":['.$data.']}';
        /** 判断显示方式是否为 直接显示Json格式的数组，便于分析复制 */
        if ($show==2){
            echo 'root:'.$root_synset_id.'<br>';
            echo $data;
            exit();
        }
        $myfile = fopen("public/tree.json", "w");
        fwrite($myfile, $data);
        fclose($myfile);
        if ($lang==1){
            return \view('tree');
        }else{
            return \view('english_tree');
        }
    }
    public function vdt_detele(){
        $vdt_model = new vdt();
        for ($i=6001;$i<=6893;$i++){
            $vdt_model->delete_Info($i);
        }
    }
    public function update(){
        $wn_chinese_model = new wn_chinese();
        $list = $wn_chinese_model->get_List();
        foreach ($list as $line){
            $line = $line->getdata();
            if ($line['synset_id']>500000000){
                echo $line['synset_id'].'<br>';
                $line['synset_id'] = $line['synset_id'] - 500000000;
                $wn_chinese_model->save_Info($line['id'],$line);
            }
        }
    }
}
