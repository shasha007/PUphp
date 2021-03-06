<?php

function orderState($id) {
    $roles = array('0'=>'未提交收货地址','1'=>'等待发货','2'=>'已发货，未确认收货','3'=>'定金已付，等待开团','4'=>'未付尾款','10'=>'交易完成','11'=>'交易取消');
    if(isset($roles[$id])){
        return $roles[$id];
    }
    return '';
}
function tgRestTime($day){
    $second = strtotime($day.'10:00:00')-time();
    return time2string($second);
}
function currentPrice($sprice,$eprice,$eprice_attended,$has_attended,$dec){
    $sprice = $sprice*100;
    $eprice = $eprice*100;
    if($has_attended<=1){
        return $sprice;
    }
    if($has_attended>=$eprice_attended){
        return $eprice;
    }
    $decNum = $has_attended-1;
    $cprice = $sprice-($dec*$decNum);
    if($cprice<$eprice){
        return $eprice;
    }
    return $cprice;
}

function getCityName($id){
   return M('citys')->getField('city', 'id='.$id);
}

function getPriceStag(){
    $staging = array('0'=>1,'1'=>3,'2'=>6,'3'=>9,'4'=>12,'5'=>18,'6'=>24);
    return $staging;
}
//获取口袋金金额
function getPrice(){
    $price= array('0'=>500, '1'=>1000,'2'=>1500,'3'=>2000,'4'=>2500, '5'=>3000,'6'=>3500,'7'=>4000,'8'=>5000);
    return $price;
}

//获取区号
function areaCode($name){
    $area = array('南京'=>'025','无锡'=>'0510','徐州'=>'0516','常州'=>'0519','苏州'=>'0512','南通'=>'0513','连云港'=>'0518','淮安'=>'0517','盐城'=>'0515','扬州'=>'0514','镇江'=>'0511','泰州'=>'0523','宿迁'=>'0527');
    return $area[$name];

}
//获取邮编
function areaPost($name){
    $area = array('南京'=>'210000','无锡'=>'214000','徐州'=>'221000','常州'=>'213000','苏州'=>'215000','南通'=>'226000','连云港'=>'222000','淮安'=>'223000','盐城'=>'224000','扬州'=>'225000','镇江'=>'212000','泰州'=>'225300','宿迁'=>'223800');
    return $area[$name];

}
//获取每月还款总额
function getStagingPrice($m,$n){
    $list['1000']['1'] = 1015;
    $list['1000']['3'] = 343.3;
    $list['1000']['6'] = 175;
    $list['1000']['9'] = 119.4;
    $list['1000']['12'] = 92.5;
    $list['1000']['18'] = 65.6;
    $list['1000']['24'] = 51.7;
    $list['2000']['1'] = 2030;
    $list['2000']['3'] = 686.7;
    $list['2000']['6'] = 350;
    $list['2000']['9'] = 238.9;
    $list['2000']['12'] = 185;
    $list['2000']['18'] = 131.1;
    $list['2000']['24'] = 103.3;
    $list['3000']['1'] = 3045;
    $list['3000']['3'] = 1030;
    $list['3000']['6'] = 525;
    $list['3000']['9'] = 358.3;
    $list['3000']['12'] = 277.5;
    $list['3000']['18'] = 196.7;
    $list['3000']['24'] = 155;
    $list['4000']['1'] = 4063.3;
    $list['4000']['3'] = 1380;
    $list['4000']['6'] = 706.7;
    $list['4000']['9'] = 484.4;
    $list['4000']['12'] = 376.6;
    $list['4000']['18'] = 268.9;
    $list['4000']['24'] = 213.4;
    $list['5000']['1'] = 5083.3;
    $list['5000']['3'] = 1729.2;
    $list['5000']['6'] = 887.5;
    $list['5000']['9'] = 609.8;
    $list['5000']['12'] = 470.9;
    $list['5000']['18'] = 340.3;
    $list['5000']['24'] = 270.8;
    $list['500']['1'] = 507.5;
    $list['500']['3'] = 171.7;
    $list['500']['6'] = 87.5;
    $list['500']['9'] = 59.8;
    $list['500']['12'] = 46.3;
    $list['500']['18'] = 32.8;
    $list['500']['24'] = 25.8;
    $list['1500']['1'] = 1522.5;
    $list['1500']['3'] = 515;
    $list['1500']['6'] = 262.5;
    $list['1500']['9'] = 179.2;
    $list['1500']['12'] = 138.8;
    $list['1500']['18'] = 98.3;
    $list['1500']['24'] = 77.5;
    $list['2500']['1'] = 2537.5;
    $list['2500']['3'] = 858.3;
    $list['2500']['6'] = 437.5;
    $list['2500']['9'] = 298.6;
    $list['2500']['12'] = 231.2;
    $list['2500']['18'] = 163.9;
    $list['2500']['24'] = 129.2;
    $list['3500']['1'] = 3552.5;
    $list['3500']['3'] = 1201.7;
    $list['3500']['6'] = 612.5;
    $list['3500']['9'] = 418.1;
    $list['3500']['12'] = 323.8;
    $list['3500']['18'] = 229.4;
    $list['3500']['24'] = 180.8;
    return $list[$m][$n];
}

//获取每月还款总额
function getStagingPrice1($m,$n){
    $list['1000']['1'] = 1014.17;
    $list['1000']['3'] = 345.83;
    $list['1000']['6'] = 178.34;
    $list['1000']['9'] = 123.61;
    $list['1000']['12'] = 96.66;

    $list['2000']['1'] = 2028.33;
    $list['2000']['3'] = 691.67;
    $list['2000']['6'] = 356.66;
    $list['2000']['9'] = 247.22;
    $list['2000']['12'] = 193.34;

    $list['3000']['1'] = 3042.5;
    $list['3000']['3'] = 1037.5;
    $list['3000']['6'] = 535;
    $list['3000']['9'] = 370.83;
    $list['3000']['12'] = 290;

    $list['4000']['1'] = 4056.67;
    $list['4000']['3'] = 1383.33;
    $list['4000']['6'] = 713.34;
    $list['4000']['9'] = 494.44;
    $list['4000']['12'] = 386.66;

    $list['5000']['1'] = 5070.83;
    $list['5000']['3'] = 1729.17;
    $list['5000']['6'] = 891.66;
    $list['5000']['9'] = 618.06;
    $list['5000']['12'] = 483.34;

    $list['500']['1'] = 507.08;
    $list['500']['3'] = 172.92;
    $list['500']['6'] = 89.16;
    $list['500']['9'] = 61.81;
    $list['500']['12'] = 48.34;

    $list['1500']['1'] = 1521.25;
    $list['1500']['3'] = 518.75;
    $list['1500']['6'] = 267.5;
    $list['1500']['9'] = 185.42;
    $list['1500']['12'] = 145;

    $list['2500']['1'] = 2535.42;
    $list['2500']['3'] = 864.58;
    $list['2500']['6'] = 445.84;
    $list['2500']['9'] = 309.03;
    $list['2500']['12'] = 241.66;

    $list['3500']['1'] = 3549.58;
    $list['3500']['3'] = 1210.42;
    $list['3500']['6'] = 624.16;
    $list['3500']['9'] = 432.64;
    $list['3500']['12'] = 338.34;

    $list['4500']['1'] = 4563.75;
    $list['4500']['3'] = 1556.25;
    $list['4500']['6'] = 802.5;
    $list['4500']['9'] = 556.25;
    $list['4500']['12'] = 435;
    return $list[$m][$n];
}

//获取康欣每种金额的服务费  传入参数为金额
function getKxService($m){
    $list['500'] = 3.33;
    $list['1000'] = 6.67;
    $list['1500'] = 10;
    $list['2000'] = 13.33;
    $list['2500'] = 16.67;
    $list['3000'] = 20;
    $list['3500'] = 23.33;
    $list['4000'] = 26.67;
    $list['4500'] = 30;
    $list['5000'] = 33.33;
    return $list[$m];
}

//获取每月还款的本金
function getMouPrice($m,$n){
    $list['1000']['1'] = 1000;
    $list['1000']['3'] = 333.33;
    $list['1000']['6'] = 166.67;
    $list['1000']['9'] = 111.11;
    $list['1000']['12'] = 83.33;
    $list['1000']['18'] = 55.6;
    $list['1000']['24'] = 41.7;
    $list['2000']['1'] = 2000;
    $list['2000']['3'] = 666.67;
    $list['2000']['6'] = 333.33;
    $list['2000']['9'] = 222.22;
    $list['2000']['12'] = 166.67;
    $list['2000']['18'] = 111.1;
    $list['2000']['24'] = 83.3;
    $list['3000']['1'] = 3000;
    $list['3000']['3'] = 1000;
    $list['3000']['6'] = 500;
    $list['3000']['9'] = 333.33;
    $list['3000']['12'] = 250;
    $list['3000']['18'] = 166.7;
    $list['3000']['24'] = 125;
    $list['4000']['1'] = 4000;
    $list['4000']['3'] = 1333.33;
    $list['4000']['6'] = 666.67;
    $list['4000']['9'] = 444.44;
    $list['4000']['12'] = 333.33;
    $list['4000']['18'] = 222.2;
    $list['4000']['24'] = 166.7;
    $list['5000']['1'] = 5000;
    $list['5000']['3'] = 1666.67;
    $list['5000']['6'] = 833.33;
    $list['5000']['9'] = 555.56;
    $list['5000']['12'] = 416.67;
    $list['5000']['18'] = 277.8;
    $list['5000']['24'] = 208.3;
    $list['500']['1'] = 500;
    $list['500']['3'] = 166.67;
    $list['500']['6'] = 83.33;
    $list['500']['9'] = 55.56;
    $list['500']['12'] = 41.67;
    $list['500']['18'] = 27.8;
    $list['500']['24'] = 20.8;
    $list['1500']['1'] = 1500;
    $list['1500']['3'] = 500;
    $list['1500']['6'] = 250;
    $list['1500']['9'] = 166.67;
    $list['1500']['12'] = 125;
    $list['1500']['18'] = 83.3;
    $list['1500']['24'] = 62.5;
    $list['2500']['1'] = 2500;
    $list['2500']['3'] = 833.33;
    $list['2500']['6'] = 416.67;
    $list['2500']['9'] = 277.78;
    $list['2500']['12'] = 208.33;
    $list['2500']['18'] = 138.9;
    $list['2500']['24'] = 104.2;
    $list['3500']['1'] = 3500;
    $list['3500']['3'] = 1166.67;
    $list['3500']['6'] = 583.33;
    $list['3500']['9'] = 388.89;
    $list['3500']['12'] = 291.67;
    $list['3500']['18'] = 194.4;
    $list['3500']['24'] = 145.8;
    $list['4500']['1'] = 4500;
    $list['4500']['3'] = 1500;
    $list['4500']['6'] = 750;
    $list['4500']['9'] = 500;
    $list['4500']['12'] = 375;
    return $list[$m][$n];
}