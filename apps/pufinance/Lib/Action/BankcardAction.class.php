<?php

/**
 * 银行卡控制器
 */
class BankcardAction extends BaseAction
{
    /**
     * 我的银行卡列表页
     */
    public function index()
    {
        //dump($this->user);
        $cards = D('PufinanceBankcard')->getUserUsableBankcardListByUid($this->mid);
        //dump($cards);
        $this->assign('cards', $cards);
        $this->display();
    }

    /**
     * 银行卡详情
     */
    public function show()
    {
        $id = intval($_GET['id']);
        $cards = D('PufinanceBankcard')->getUserUsableBankcardListByUid($this->mid);
        if (!isset($cards[$id])) {
            $this->error('错误');
        }
        $this->assign('info', $cards[$id]);
        $this->display();
    }

    /**
     * 绑定银行卡
     */
    public function bindBankcard()
    {
        $banks = D('PufinanceBankOrg')->getBankList();
        $this->assign('banks', $banks);

        $invests = D('PufinanceInvestOrg')->getInvestOrgList();
        $this->assign('invests', $invests);

        $this->assign('provinces', get_provinces());
        $this->display();
    }

    /**
     * 提交绑定信息
     */
    public function doBindBankcard()
    {
        $bank_id = intval($_POST['bank_id']);
        $bank = D('PufinanceBankOrg')->getBankInfoById($bank_id);
        if (empty($bank)) {
            $this->error('请选择银行');
        }
        $realname = t($_POST['realname']);
        if (empty($realname)) {
            $this->error('请输入真实姓名');
        }

        $ctfid = t($_POST['ctfid']);
        if (empty($ctfid)) {
            $this->error('请输入身份证号');
        }
        $cardNo = t($_POST['card_no']);
        if (empty($cardNo)) {
            $this->error('请输入银行卡号');
        }

        $province_id = intval($_POST['province_id']);
        if (empty($province_id)) {
            $this->error('请选择省份');
        }

        $city_id = intval($_POST['city_id']);
        if (empty($city_id)) {
            $this->error('请选择城市');
        }

        $mobile = t($_POST['mobile']);
        if (empty($mobile)) {
            $this->error('请输入银行预留手机号码');
        }

        $captcha = t($_POST['captcha']);
        if (empty($captcha)) {
            $this->error('请输入短信验证码');
        }

        $puUser = D('PufinanceUser')->getByUid($this->mid);
        if ($puUser) {
            if ($puUser['realname'] != $realname) {
                $this->error('姓名与当前用户姓名不一致！');
            }
            if ($puUser['ctfid'] != $ctfid) {
                $this->error('身份证与当前用户身份证不一致！');
            }
        } else {
            if ($this->user['realname'] != $realname) {
                $this->error('姓名与当前账号不一致！');
            }
        }

        $invest = D('PufinanceInvestOrg')->getInvestOrgInfoById($bank['invest_id']);
        if (!$invest) {
            $this->error('银行参数有误，请联系管理员');
        }

        $bankcards = D('PufinanceBankcard')->getUserAllBankcardListByUid($this->mid);
        foreach ($bankcards as $bankcard) {
            if ($bankcard['card_no'] == $cardNo && $bankcard['status']) { // 银行卡已存在 正常状态
                $this->error('该银行卡已存在');
            }
        }

        // 验证短信验证码
        $service = service(ucfirst($invest['code']));
        $res = $service->verifyBankCaptcha($captcha, array(
            'realname' => $realname,
            'ctfid' => $ctfid,
            'card_no' => $cardNo,
            'mobile' => $mobile,
        ));
        if ($res['status']) {
            if (!$puUser) {
                $user = array(
                    'uid' => $this->mid,
                    'realname' => $realname,
                    'ctfid' => $ctfid,
                    'mobile' => $mobile,
                    'ctime' => time(),
                );
                $result = D('PufinanceUser')->add($user);
                if ($result === false) {
                    $this->error('绑定银行卡失败，请稍后再试！');
                }
            }

            $result = D('PufinanceBankcard')->addUserBankcard($this->mid, array(
                'bank_id' => $bank['id'],
                'card_no' => $cardNo,
                'mobile' => $mobile,
                'card_sign_no' => $res['data']['cardSignNo'],
                'cust_no' => $res['data']['custNo'],
                'bank_name' => $res['data']['bankName'],
                'province_id' => $province_id,
                'city_id' => $city_id,

            ));
            if ($result === false) {
                $this->error('银行卡信息保存失败，请稍后再试！');
            }
            if (empty($puUser['paypassword'])) {    // 设置支付密码
                $_SESSION['step1'] = 1;
                $this->ajaxReturn(array('url' => U('pufinance/service/resetPwd', array('from' => 'bindBankcard'))));
            }
            if (empty($puUser['mobile']) || empty($puUser['email'])) { // 设置用户信息
                $this->ajaxReturn(array('url' => U('pufinance/service/personalInfo', array('from' => 'bindBankcard'))));
            }

            $this->success('ok');

        } else {
            $this->error($res['info']);
        }


    }

    public function unbindBankcard()
    {
        $id = intval($_POST['id']);
        $condition = array(
            'uid' => $this->mid,
            //'bank_card_id' => $id,
            'status' => array('not in', array(1,5)),
            '_string' => "bank_card_id={$id} OR repay_bank_card_id={$id}"
        );
        
        $res = D('PufinanceOrder')->where($condition)->find();
        if ($res) {
            $this->error('该银行卡有'.L('finance_name').'交易，暂禁止解绑！');
        } else {
            try {
                M()->startTrans();
                /*$bankcards = D('PufinanceBankcard')->getUserUsableBankcardListByUid($this->mid);
                $bankInfo = $bankcards[$id];

                if ($bankInfo['card_sign_no'] && $bankInfo['cust_no']) { // 金农
                    $service = service('Kangxin');
                    $puUser = D('PufinanceUser')->field('uid,realname,ctfid')->where(array('uid' => $this->mid))->find();
                    $res = $service->contractUnbinding(array(
                        'realname' => $puUser['realname'],
                        'ctfid' => $puUser['ctfid'],
                        'card_no' => $bankInfo['card_no'],
                        'mobile' => $bankInfo['mobile'],
                        'card_sign_no' => $bankInfo['card_sign_no'],
                    ));
                    if ($res['status'] === false) {
                        throw_exception('金农解绑接口失败');
                    }
                }*/
                $res = D('PufinanceBankcard')->delUserBankcard($this->mid, $id);
                if ($res === false) {
                    throw_exception('删除银行卡失败');
                }
                M()->commit();
                $this->success('ok');
            } catch (ThinkException $e) {
                //dump($e->getMessage());
                M()->rollback();
                $this->error('解绑失败，请稍后再试！');
            }
        }
    }

    /**
     * 验证银行卡信息并发送短信验证码
     */
    public function sendBankCaptcha()
    {
        $bank_id = intval($_POST['bank_id']);
        $bank = D('PufinanceBankOrg')->getBankInfoById($bank_id);
        if (empty($bank)) {
            $this->error('请选择银行');
        }
        $realname = t($_POST['realname']);
        if (empty($realname)) {
            $this->error('请输入真实姓名');
        }

        $ctfid = t($_POST['ctfid']);
        if (empty($ctfid)) {
            $this->error('请输入身份证号');
        }
        $cardNo = t($_POST['card_no']);
        if (empty($cardNo)) {
            $this->error('请输入银行卡号');
        }
        $mobile = t($_POST['mobile']);
        if (empty($mobile)) {
            $this->error('请输入银行预留手机号码');
        }
        $puUser = D('PufinanceUser')->getByUid($this->mid);
        if ($puUser) {
            if ($puUser['realname'] != $realname) {
                $this->error('姓名与当前用户姓名不一致！');
            }
            if ($puUser['ctfid'] != $ctfid) {
                $this->error('身份证与当前用户身份证不一致！');
            }
        } else {
            if ($this->user['realname'] != $realname) {
                $this->error('姓名与当前账号不一致！');
            }
        }

        $invest = D('PufinanceInvestOrg')->getInvestOrgInfoById($bank['invest_id']);
        if (!$invest) {
            $this->error('银行参数有误，请联系管理员');
        }
        $service = service(ucfirst($invest['code']));
        // 验证银行信息
        $res = $service->checkBankInfo(array(
            'realname' => $realname,
            'ctfid' => $ctfid,
            'card_no' => $cardNo,
            'mobile' => $mobile
        ));
        if ($res['status']) {
            $this->success('ok');
        } else {
            $this->error('短信验证码发送失败，请核实银行卡信息后再试！');
        }


    }

    /**
     * 上传用户身份证
     */
    public function uploadCtfid()
    {
        if ($this->mid && !empty($_FILES['file']['tmp_name'])) {
            $image = tsUploadImg($this->mid, 'puctfid', true);
            $this->ajaxReturn('', $image['info'], $image['status']);
        }
    }

    public function getCitys()
    {
        $provinceid = intval($_POST['province_id']);
        if ($provinceid) {
            $this->ajaxReturn(get_citys($provinceid));

        }

    }


}