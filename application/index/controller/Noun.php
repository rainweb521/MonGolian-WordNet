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

        return \view('tree3',array('tree_str2'=>$data));

    }




}
