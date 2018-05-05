<?php
namespace app\index\controller;
use app\config\model\menggu;
use app\config\model\mongolian;
use app\config\model\vdt;
use app\config\model\wn_chinese;
use app\config\model\wn_hypernym;
use app\config\model\englishM;
use \think\Request;
use think\Controller;
use \think\View;
class English extends Controller {
    public function index(){
        return view('index');
    }
    public function select_json(){
        $content = Request::instance()->get('content','');
        if ($content == ''){
            echo 'queryList({"q":"","p":false,"bs":"","csor":"0","status":770,"s":[]});';
        }else{
            $ladin = array('like',$content.'%');
//            $allcn = preg_match("/^[".chr(0xa1)."-".chr(0xff)."]+$/",$content[0]);
//            $where = array();
            $english_model = new englishM();

                $where['word'] = $ladin;
                $list = $english_model->get_List($where);
                $text = '';
                foreach ($list as $item) {
                    $text = $text.'"'.$item['word'].'",';
                }

            echo 'queryList({q:"123",p:false,s:['.$text.']});';
        }
    }
    public function wordnet_show(){
        header("Content-type: text/html; charset=utf-8");
        $root = Request::instance()->get('root',0);
        $lower = Request::instance()->get('lower',0);
        $line_num = Request::instance()->get('line_num',5);
        $content = Request::instance()->get('content','');
        $root_synset_id = $now_synset_id = 0;
        $wn_hypernym_model = new wn_hypernym();

        $english_model = new englishM();
        /** 显示为英文树 */
        $data = $english_model->get_Info(['word'=>$content]);
        /** 因为后期有修改，所以在这里先渲染到页面里 */
        $this->assign('data',$data);
//            var_dump($data);exit();
        $now_No_ID = $data['synset_id'];
        /*****  先设置root的id为本id，再去查找root的id*/
        /**
         * 如果没有经过下面的if修改，那么直接查找时，用的就是本节点的上级id，而不是它自身的id
         */
        $root_synset_id = $now_No_ID;
        /** 判断root是不是等于0，如果是则表示为当前id，不是的话就需要去查找root次数的根id，lower不用查找，因为在后来直接会往下进行寻找 */
        if ($root!=0){
            /** @var  这里需要注意，传入的第二个参数应该是当前数据的上级id，这样就会减少一遍查询，直接到函数中查找上一级的数据 */

            $root_synset_id = $wn_hypernym_model->get_Root_Id($root, $now_No_ID);
        }
//            echo $root_synset_id;exit();
        $tree = $wn_hypernym_model->get_Tree_English($lower + $root, $root_synset_id,$root_synset_id, $line_num,$data['synset_id']);
//            返回的tree是类似于这种形状的{ ["14278-1"]=> int(14279) ["14278-14279-1"]=> int(3178) ["14278-14279-2"]=> int(12551)
        // 这只是提取出的形式数据，以键值对的形式来展现，需要通过get_Tree_Json来处理才能显示

//        var_dump($tree);exit();
        /*  此时假设root的id就是本id，直接使用上面查找到的data数据*/
        $root_data = $english_model->get_Info(['synset_id'=>$root_synset_id]);
//        $root_text = $root_data['word'].$root_data['word_chs'];
        $root_text = $root_data['word'];
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
            $data = $wn_hypernym_model->get_Tree_Json(count($tree),$tree,$root_synset_id,2);
        }else {$data = '';}

        $data = '{"name":"'.$root_text.'", "children":['.$data.']}';
        /** 判断显示方式是否为 直接显示Json格式的数组，便于分析复制 */
//        if ($show==2){
//            echo 'root:'.$root_synset_id.'<br>';
//            echo $data;
//            exit();
//        }
        $myfile = fopen("public/tree.json", "w");
        fwrite($myfile, $data);
        fclose($myfile);
        return \view('tree');

    }


}
