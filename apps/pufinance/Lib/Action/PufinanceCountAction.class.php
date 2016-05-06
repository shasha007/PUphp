<?php
import('home.Action.PubackAction');
class PufinanceCountAction extends PubackAction
{
	public function _initialize(){
		require_once(SITE_PATH . '/addons/libs/PHPExcel.php');
	}

    public function index(){
		if(!empty($_POST[__hash__]) && ($_SESSION['__hash__'] == $_POST['__hash__'])){
			if(!empty($_POST['start'])){
				$map['_string'] = 'o.ctime>='.strtotime($_POST['start']);
			}
			if(!empty($_POST['end'])){
				$map['o.ctime'] = array('lt',strtotime($_POST['end']));
			}
			if(!empty($_POST['province']) && (strtolower($_POST['province']) != 'all')){
				$map['s.provinceId'] = (int)$_POST['province'];
			}
			if(!empty($_POST['city']) && (strtolower($_POST['city']) != 'all')){
				$map['s.cityId'] = (int)$_POST['city'];
			}
			if(!empty($_POST['school']) && (strtolower($_POST['school']) != 'all')){
				$map['u.sid'] = (int)$_POST['school'];
			}
			if((isset($_POST['year']) && $_POST['year'] === '00') || !empty($_POST['year'])){
				$map['u.year'] = t($_POST['year']);
			}
			if(!empty($_POST['type']) && (strtolower($_POST['type']) != 'all')){
				$map['o.type'] = (int)$_POST['type'];
			}
			if(isset($_POST['status']) && (strtolower($_POST['status']) != 'all')){
				if($this->mid == '3000639' && !in_array( (int)$_POST['status'], array(2,4))){//特殊情况-康欣
					eixt;
				}
				$map['o.status'] = (int)$_POST['status'];
			}
			if(!empty($_POST['invest']) && (strtolower($_POST['invest']) != 'all')){
				if((int)$_POST['invest'] == -1){
					$map['o.bank_card_id'] = 0;//pu钱包
				}else{
					if($this->mid == '3000639' && (int)$_POST['invest'] != 1){//特殊情况-康欣
						eixt;
					}
					$map['b.invest_id'] = (int)$_POST['invest'];//资金方
				}
			}
			$this->_exportOrder($map);

		}else{
			//订单类型
			$order_types = order_type();
			//订单状态
			$order_status = order_status();
			//资金方
			$invests = M('pufinance_invest_org')->field('id,name')->select();
			$schools = get_schools(0);//所有学校
			$provinces = get_provinces();//所有省份
			$this->assign('schools_option',option_format($schools,'id','title'));
			$this->assign('provinces_option',option_format($provinces,'id','title'));
			$this->assign('citys_option',option_format(get_citys($_POST['province']),'id','city'));
			$this->assign('types',$order_types);
			$this->assign('status',$order_status);
			$this->assign('invests', $invests);
			$this->assign('uid', $this->mid);
			$this->display();
		}

	}

	//黑白名单
	public function credit(){
		$status = array(
			1 => '白名单',
			2 => '黑名单',
		);
		if(!empty($_POST[__hash__]) && ($_SESSION['__hash__'] == $_POST['__hash__'])){
			$map = array();
			if(!empty($_POST['status']) && (strtolower($_POST['status']) != 'all')){
				$map['c.status'] = (int)$_POST['status'];
			}
			$this->_exportCredit($map,L('finance_name').'名单');
		}else{
			$this->assign('status', $status);
			$this->display('creditList');
		}
	}


	//导出订单
	private function _exportOrder($map,$title='订单'){
		set_time_limit(0);
		$phpExcel = new PHPExcel();
		PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized);
		$fileName = $title.'_'.date('Ymd_His').mt_rand(100,999).".xlsx";
		$sheet_1 = $phpExcel->setActiveSheetIndex(0);

		$titles = array(
			'认证时间',
			'订单时间',
			'姓名',
			'学号',
			'身份证号',
			'性别',
			'电话',
			'学校',
			'年级',
			'学历',
			'区域',
			'借款金额',
			'免息金额',
			'日利率',
			'总利息',
			'分期数',
			'还款总额',
			'每期应还',
			'借款账户',
			'借款关联机构',
			'还款账户',
			'订单状态',
			'订单类型',
			'放款日期',
			'到期日期',
			'推荐人uid',
		);

		$init_column = ord('A');
		//输出第一行(即标题)
		foreach($titles as $k=>$title){
			$sheet_1->setCellValue(chr($init_column+$k).'1', $title);
		}

		//从数据库中取出数据
		$loop = true;
		$limit = 500;//每次循环取出的条数
		$j = 0;
		$row = 1;
		while($loop){
			$order = 'o.id desc';
			$limit_cond = $j*$limit.','.$limit;
			$datas = D('PufinanceOrder')->getOrdersData($map, $order, $limit_cond);
			if(!empty($datas)){
				$j++;//取下一批的数据的条件
				foreach($datas as $data){
					$row++;
					$n = 0;
					if($data['bank_card_id'] == $data['repay_bank_card_id']){
						$borrow = $return = get_order_bank($data['bank_card_id']);
					}else{
						$borrow = get_order_bank($data['bank_card_id']);
						$return = get_order_bank($data['repay_bank_card_id']);
					}
					$sheet_1->setCellValue(chr($init_column+$n++).$row, $data['pctime'] ? date('Y-m-d',$data['pctime']) : '')/*认证日期*/
							->setCellValue(chr($init_column+$n++).$row,date('Y-m-d',$data['ctime']))/*订单时间*/
							->setCellValue(chr($init_column+$n++).$row,$data['realname'])/*姓名*/
							->setCellValue(chr($init_column+$n++).$row,array_shift(explode('@',$data['email'])))/*学号*/
							->setCellValue(chr($init_column+$n++).$row,substr($data['ctfid'],0,6).'********'.substr($data['ctfid'],14))/*身份证号*/
							->setCellValue(chr($init_column+$n++).$row,($data['sex'] == 2) ? '保密' : (($data['sex'] == 1) ? '男'  : '女'))/*性别*/
							->setCellValue(chr($init_column+$n++).$row,"'".$data['mobile'])/*手机号*/
							->setCellValue(chr($init_column+$n++).$row,$data['sname'])/*学校*/
							->setCellValue(chr($init_column+$n++).$row,$data['year'])/*年级*/
							->setCellValue(chr($init_column+$n++).$row, ($data['tj_year'] == 4) ? '本科' : ( ($data['tj_year'] == 3) ? '专科' : '') )/*学历*/
							->setCellValue(chr($init_column+$n++).$row,$data['pname'].$data['cname'])/*区域*/
							->setCellValue(chr($init_column+$n++).$row,$data['amount'])/*借款金额*/
							->setCellValue(chr($init_column+$n++).$row,$data['free_amount'])/*免息金额*/
							->setCellValue(chr($init_column+$n++).$row,0.00045)/*日利息*/
							->setCellValue(chr($init_column+$n++).$row,$data['interest'])/*总利息*/
							->setCellValue(chr($init_column+$n++).$row,$data['stage'])/*分期数*/
							->setCellValue(chr($init_column+$n++).$row, bcadd($data['amount'], $data['interest'],2) )/*还款总额*/
							->setCellValue(chr($init_column+$n++).$row, round( bcdiv( bcadd($data['amount'], $data['interest'],2), $data['stage'], 3), 2) )/*每期应还*/
							->setCellValue(chr($init_column+$n++).$row,!empty($data['bank_card_id']) ? $borrow['bank'].':'.$borrow['card_no'] : $borrow['bank'])/*借款账户*/
							->setCellValue(chr($init_column+$n++).$row,get_invest($data['invest_id']))/*借款关联机构*/
							->setCellValue(chr($init_column+$n++).$row,!empty($data['repay_bank_card_id']) ? $return['bank'].':'.$return['card_no'] : '')/*还款账户*/
							->setCellValue(chr($init_column+$n++).$row,order_status($data['status']))/*订单状态*/
							->setCellValue(chr($init_column+$n++).$row,order_type($data['type']))/*订单类型*/
							->setCellValue(chr($init_column+$n++).$row, $data['lend_time'] ? date('Y-m-d', $data['lend_time']) : '')/*放款日期*/
							->setCellValue(chr($init_column+$n++).$row, date('Y-m-d',($data['ctime'] + ($data['stage'] * 30 * 3600) )) )/*到期日期*/
							->setCellValue(chr($init_column+$n++).$row, $data['recommend_uid'] ? $data['recommend_uid'] : '无')/*推荐人ID*/;
				}
			}else{
				$loop = false;
			}
		}
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=\"$fileName\"");
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
		$objWriter->save('php://output'); //文件通过浏览器下载
	}

	//黑白名单导出
	private function _exportCredit($map,$title='PU金名单'){
		set_time_limit(0);
		$phpExcel = new PHPExcel();
		PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized);
		$fileName = $title.'_'.date('Ymd_His').mt_rand(100,999).".xlsx";
		$sheet_1 = $phpExcel->setActiveSheetIndex(0);
		$order = 'c.uid desc';
		$titles = array(
				'用户uid',
				'姓名',
				'身份证号',
				'总额度',
				'免息额度',
				'风控金额',
				'状态',
		);

		$init_column = ord('A');
		//输出第一行(即标题)
		foreach($titles as $k=>$title){
			$sheet_1->setCellValue(chr($init_column+$k).'1', $title);
		}

		//从数据库中取出数据
		$loop = true;
		$limit = 500;//每次循环取出的条数
		$j = 0;
		$row = 1;
		while($loop){
			$limit_cond = $j*$limit.','.$limit;
			$datas = D('PufinanceCredit')->getCreditDatas($map, $order, $limit_cond);
			if(!empty($datas)){
				$j++;//取下一批的数据的条件
				foreach($datas as $data){
					$row++;
					$n = 0;
					$sheet_1->setCellValueExplicit(chr($init_column+$n++).$row,"'".$data['uid'],PHPExcel_Cell_DataType::TYPE_STRING)
							->setCellValue(chr($init_column+$n++).$row,$data['realname'])
							->setCellValueExplicit(chr($init_column+$n++).$row,"'".$data['ctfid'],PHPExcel_Cell_DataType::TYPE_STRING)
							->setCellValue(chr($init_column+$n++).$row,$data['all_amount'])
							->setCellValue(chr($init_column+$n++).$row,$data['free_amount'])
							->setCellValue(chr($init_column+$n++).$row,$data['free_risk'])
							->setCellValue(chr($init_column+$n++).$row,get_credit_status($data['status']));
				}

			}else{
				$loop = false;
			}
		}

		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=\"$fileName\"");
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
		$objWriter->save('php://output'); //文件通过浏览器下载
	}

}