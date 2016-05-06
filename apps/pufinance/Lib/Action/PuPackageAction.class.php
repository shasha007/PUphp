<?php
/**
 * 提额包相关操作
 */
import('home.Action.PubackAction');
class PuPackageAction extends PubackAction
{
	/*
	 * 提额包列表
	*/
    public function index(){
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['package_searchUser'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['package_searchUser']);
        } else {
            unset($_SESSION['package_searchUser']);
        }
        $map = array();
        if(!empty($_POST['uid'])){
            $map['uid'] = (int)$_POST['uid'];
        }

        $lists = D('PufinanceAmount')->getAmountListsByPage($map);
        $this->assign($lists);
        $this->display();
	}

    /*
     * 导入提额包页面
     * */
    public function importPackage(){
        $this->display();
    }

    //ajax删除导入记录
    public function deleteImportRecordByAjax(){
        if (empty($_POST['id'])) {
            echo 0;
            exit;
        }
        $map['id'] = array('in', t($_POST['id']));
        $detele = M('pufinance_amount')->where($map)->delete();
        echo !empty($detele) ? 1 : 0;
    }

    /*
     * 检查导入信息
     * */
    public function checkImport(){
        $res = $this->_readExcel();

        $titles = array('用户uid','金额','类型');
        $this->_checkFirstLine($titles,$res[0]);
        unset($res[0]);
        $suc = 0;
        $fail = 0;
        $msg = '';
        foreach ($res as $k=>$v){
            if(!$v[0]&&!$v[1]&&!$v[2]&&!$v[3]&&!$v[4]){//空行不管继续
                continue;
            }
            $line = $k+1;
            $lineEmpty = '';
            foreach ($titles as $x => $y) {
                if(!isset($v[$x])){
                    $lineEmpty .= $y.'为空 ';
                }
            }
            if($lineEmpty!=''){
                $fail++;
                $msg .= "第$line 行 $lineEmpty <br/>";
                continue;
            }

            //检查用户是否存在
            $uid = t($v[0]);
            $is_exist = $this->_checkUserIsExist($uid);
            if(empty($is_exist)){
                $fail++;
                $msg .= "第$line 行 该用户uid {$uid} 不存在 <br/>";
                continue;
            }

            //检查金额是不是数字
            $amount = t($v[1]);
            if(!is_numeric($amount)){
                $fail++;
                $msg .= "第$line 行 金额 {$amount} 不是数字 <br/>";
                continue;
            }

            //检查类型
            $type = intval($v[2]);
            if(!in_array($type, array(0,1))){
                $fail++;
                $msg .= "第$line 行 类型只能为 0 或 1 <br/>";
                continue;
            }

            $suc++;
        }
        $this->_endCheck($msg, $suc,$fail);
    }

    /*
     * 读取excel
     * */
    private function _readExcel(){
        if(empty($_FILES['file']) || strpos($_FILES['file']['name'], '.xls')===false){
            $this->error('请上传excel文档');
        }
        $excel = service('Excel');
        $res = $excel->read($_FILES['file']['tmp_name']);
        if(empty($res)){
            $this->error('文档无内容');
        }
        return $res;
    }

    /*
     * 检查首行
     * */
    private function _checkFirstLine($soll,$row){
        $cnt = count($soll);
        if(count($row)!=$cnt){
            $this->error('表格第一行错误，请检查拼写及顺序');
            return false;
        }
        for($i=0;$i<$cnt;$i++){
            if($row[$i] != $soll[$i]){
                $hang = $i+1;
                $this->error('第'.$hang.'列【'.$row[$i].'】错误，应该为 【'.$soll[$i].'】');
                return false;
            }
        }
        return true;
    }

    /*
     * 检查结果
     * */
    private function _endCheck($msg,$suc,$fail){
        if($msg!=''){
            $this->error("共$fail 个错误<br/>".$msg);
        }else{
            $file_name = time() . '_' . rand(1000,9999) . '.xls'; //使用时间来模拟图片的ID.
            $file_path = SITE_PATH . '/data/tmp/' . $file_name;
            $file = @$_FILES['file']['tmp_name'];
            file_exists($file_path) && @unlink($file_path);
            if (@copy($file, $file_path) || @move_uploaded_file($file, $file_path)) {
                @unlink($file);
                $this->ajaxReturn($file_name,"检查通过，共$suc 行有效数据，可以进行发放操作",1);
            }
            $this->error('保存文件失败');
        }
    }

    //检查用户是否存在
    private function _checkUserIsExist($uid=0){
        return M('user')->getField('uid','uid='.$uid);
    }

    /*
     * 执行导入
     * */
    public function doImportPackage(){
        $filePath = SITE_PATH . '/data/tmp/' .$_POST['filePath'];
        $excel = service('Excel');
        $res = $excel->read($filePath);
        if(empty($res)){
            $this->error('文档无内容');
        }
        unset($res[0]);
        $suc = 0;
        $fail = 0;
        $msg = '';
        $time = time();
        $etime = $time + 30*24*3600;//领取结束时间
        foreach ($res as $k=>$v){
            if(!$v[0]&&!$v[1]&&!$v[2]&&!$v[3]&&!$v[4]){
                continue;
            }
            $line = $k+1;

            $data['uid'] = intval($v[0]);//用户id
            $data['amount'] = t($v[1]);//金额
            $data['type'] = intval($v[2]);//类型
            $data['ctime'] = $time;//导入时间
            $data['etime'] = $etime;//领取结束时间
            $data['is_receive'] = 0;//是否领取

            $id = M('pufinance_amount')->add($data);
            if($id){
                $suc++;
            }else{
                $fail++;
                $msg .= $line.' 写入失败 '.$v[0].'<br/>';
            }
        }
        @unlink($filePath);
        $this->success('导入完毕<br/><span class="cGreen">成功：'.$suc.'条 </span><span class="cRed">失败：'.$fail.'条<br/>'.$msg.'</span>');
    }



}