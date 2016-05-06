<?php


class PufinanceUserModel extends Model
{
    //分页显示用户信息
    public function getUsersListByPage($map, $order="puser.uid desc"){

        $lists = $this->table(C('DB_PREFIX').'pufinance_user puser')
                ->field('puser.uid,puser.recommend_uid,puser.realname,puser.ctfid,puser.position,puser.ethnic,puser.imid,puser.mobile,puser.address,puser.email pemail,user.sid,user.sid1,user.year,school.tj_year,school.title sname,user.mobile umobile,user.score,user.email uemail,p.title pname,c.city,cx.total,cx.attend')
                ->join(C('DB_PREFIX').'user user on puser.uid=user.uid')/*关联用户表*/
                ->join(C('DB_PREFIX').'school school on school.id=user.sid')/*关联学校*/
                ->join(C('DB_PREFIX').'province p on p.id=school.provinceId')/*关联省份*/
                ->join(C('DB_PREFIX').'citys c on c.id=school.cityId')/*关联城市*/
                 /*->join(C('DB_PREFIX').'pufinance_bankcard pb on pb.uid=puser.uid')银行卡*/
                ->join(C('DB_PREFIX').'event_cx cx on cx.uid=puser.uid')/*诚信表*/
                ->where($map)
                ->order($order)
                ->findPage(15);
        return $lists;
    }

    //通过Uid获取用户信息
    public function getUserInfoByUid($uid=0){
        $infos = $this->table(C('DB_PREFIX').'pufinance_user puser')
            ->field('puser.uid,puser.recommend_uid,puser.realname,puser.ctfid,puser.position,puser.ethnic,puser.imid,puser.mobile,puser.address,puser.email pemail,puser.remark,puser.ctime,user.sid,user.sid1,user.year,school.tj_year,school.title sname,user.mobile umobile,user.score,user.email uemail,p.title pname,c.city,cx.total,cx.attend')
            ->join(C('DB_PREFIX').'user user on puser.uid=user.uid')/*关联用户表*/
            ->join(C('DB_PREFIX').'school school on school.id=user.sid')/*关联学校*/
            ->join(C('DB_PREFIX').'province p on p.id=school.provinceId')/*关联省份*/
            ->join(C('DB_PREFIX').'citys c on c.id=school.cityId')/*关联城市*/
            /* ->join(C('DB_PREFIX').'pufinance_bankcard pb on pb.uid=puser.uid')银行卡*/
            ->join(C('DB_PREFIX').'event_cx cx on cx.uid=puser.uid')/*诚信表*/
            ->where('puser.uid='.$uid)
            ->find();
        return $infos;
    }

    public function getUserByUid($uid){
        return $this->where('uid='.$uid)->find();
    }
}