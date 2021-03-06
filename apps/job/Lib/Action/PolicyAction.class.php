<?php

/*
 * 地方政策
 * 
 */
class PolicyAction extends Action{
    
    /*
     * 地方政策首页
     */
    public function policy(){
        //城市列表
        $map['provinceId'] = 320000;
        $citys = D('HatProvince')->provinceList($map,false);
        unset($map);
        $this->assign('citys',$citys[0]['city']);
        $cid = $_GET['city'] ? $_GET['city'] : '320500';//默认苏州（暂时）
        $map['area'] = $cid;
        $policyList = D('JobLocalpolicy')->polictList($map);
	foreach($policyList['data'] as &$val){
	$val['content'] = strip_tags($val['content']);
	}
        $this->assign('policyList',$policyList);
        $this->display();
    }
    
    public function policy_map(){
        $this->display();
    }
    
    public function policy_policy_map1(){
        $this->display();
    }
    
    public function policy_desc(){
        $sinfo = M('JobLocalpolicy')->where('id='.$_GET['id'])->field('id,title,ctime,author,content')->select();
        if(!$sinfo){
            $this->error(L('信息不存在'));
        }
        $this->assign('list',$sinfo[0]);
        $this->display();
    }
    
    /**
     * 返回地图信息 AJAX
     */
    public function returnAreaInfo(){
        $aid = t($_POST['aid']);
        $data = $this->streetdata();
        $returndata['street'] = $data['street'][$aid]; 
        $returndata['detail'] = $data['detail'][$aid];
        echo json_encode($returndata);
    }
    
    /**
     * 地图数据
     */
    public function streetdata(){
        $stree = array();
        $stree[1] = '白洋湾街道';
        $stree[2] = '城北街道';
        $stree[3] = '苏锦街道';
        $stree[4] = '虎丘街道';
        $stree[5] = '金阊街道';
        $stree[6] = '留园街道';
        $stree[7] = '石路街道';
        $stree[8] = '胥江街道';
        $stree[9] = '友新街道';
        $stree[10] = '吴门桥街道';
        $stree[11] = '桃花坞街道';
        $stree[12] = '沧浪街道';
        $stree[13] = '葑门街道';
        $stree[14] = '双塔街道';
        $stree[15] = '姑苏区人社局';
        $stree[16] = '观前街道';
        $stree[17] = '平江街道  ';
        $stree[18] = '娄门街道';
        $stree[19] = '人力资源市场';
        
        $array = array();
        $array[1][] = array('劳动保障所','富中街2号','0512-69321220');    
        $array[1][] = array('路南社区','齐白路1号','0512-67232802');         
        $array[1][] = array('自由村','齐白路1号','0512-67232802');  
        $array[1][] = array('西站社区','民主村部280号','0512-65560015');    
        $array[1][] = array('民主村','民主村部280号','0512-65560015');    
        $array[1][] = array('富强社区','金筑街468号','0512-69329055');    
        $array[1][] = array('长泾社区','金筑街468号','0512-69329055');    
        $array[1][] = array('富强村社区','藕巷新村16幢','0512-69329070');   
        $array[1][] = array('藕巷社区','藕巷新村16幢','0512-69329070');    
        $array[1][] = array('南山社区','南山金成家园63幢1楼','0512-69329006');    
        $array[1][] = array('宝邻社区','宝邻苑29幢109室','0512-69329050');    
        $array[1][] = array('张网村','张网村部','0512-65352173');    
        $array[1][] = array('新渔村','新渔村部','0512-65358976');    
        $array[1][] = array('申庄村','申庄村部','0512-65353620');    
        $array[1][] = array('新城村','富中街2号','0512-69321220');    
        $array[1][] = array('新益村','新益村部','0512-65358767');    
        $array[1][] = array('金筑社区','宝祥苑7幢1-2楼','0512-69329170');    

        $array[2][] = array('劳动保障所','平泷路1288号','0512-67221529');      
        $array[2][] = array('万达社区','平泷路50-8号','0512-69355216');
        $array[2][] = array('锦华社区','苏江花园3幢','0512-67225036');
        $array[2][] = array('金光社区','广济北路金光社区','0512-82105002');
        $array[2][] = array('金星社区','星光路口','0512-67214931');
        $array[2][] = array('新塘社区','汇翠花园61-104','0512-67550639');
        $array[2][] = array('大观名园社区','大观名园44-110','0512-69291782');

        $array[3][] = array('劳动保障所','广济北路388号5楼','0512-67728535');        
        $array[3][] = array('苏锦一社区','苏锦一村180幢','0512-67728742');
        $array[3][] = array('苏锦二社区','苏锦二村141幢','0512-67728702');
        $array[3][] = array('火车站社区','苏锦二村145幢-1','0512-67215652');
        $array[3][] = array('光华社区','锦荷苑6幢1楼北','0512-67728722');
        $array[3][] = array('新天地家园社区','苏站路新天地家园81幢','0512-67728732');

        $array[4][] = array('劳动保障所','虎丘路388号','0512-65337684');      
        $array[4][] = array('山塘社区','山塘街816号','0512-65831715');
        $array[4][] = array('桐星社区','山塘街374号','0512-65327996');
        $array[4][] = array('清塘社区','清塘新村6幢西','0512-65331753');
        $array[4][] = array('红星社区','木耳场75号北','0512-65319244');
        $array[4][] = array('曹杨社区','北浩弄49号','0512-65560876');
        $array[4][] = array('虎丘社区','虎阜花园西区81幢102室','0512-65337872');
        $array[4][] = array('茶花社区','昌华集团内','0512-67232845');
        $array[4][] = array('路北村','312国道9号桥西','0512-65316723');
        $array[4][] = array('虎阜社区','虎阜花园西区81幢102室','0512-65337872');

        $array[5][] = array('劳动保障所','三元二村9号','0512-68261715');     
        $array[5][] = array('三元一村社区','三元一村35幢西南','0512-68284934');
        $array[5][] = array('金夏社区','三元二村62—1幢','0512-68660213');
        $array[5][] = array('白莲社区','三元二村23幢西','0512-68665610');
        $array[5][] = array('滨河社区','三元三村20幢东','0512-68663506');
        $array[5][] = array('运河社区','三元三村112幢东侧','0512-68669107');
        $array[5][] = array('三元四村社区','三元四村1幢东','0512-68276518');
        $array[5][] = array('毛家桥社区','金门路280号虹桥小区3幢北','0512-65319613');
        $array[5][] = array('彩香二村南社区','彩香二村33幢南','0512-68651391');
        $array[5][] = array('彩香二村北社区','彩香二村公园内','0512-68651392');
        $array[5][] = array('双虹社区','彩虹一区3幢北侧','0512-68650975');
        $array[5][] = array('彩虹社区','彩虹一区20幢南','0512-68281777');
        $array[5][] = array('采香花园社区','采香花园内超市旁','0512-68650461');
        $array[5][] = array('虹桥社区','张家浜4幢北车库边','0512-68654972');
        $array[5][] = array('新兴村','金门路1299号','0512-68268797');

        $array[6][] = array('劳动保障所','西园路571号','0512-65571870');   
        $array[6][] = array('来运社区','新庄二村南门物业楼','0512-65576058');
        $array[6][] = array('嘉业阳光城社区','嘉业阳光城B组团','0512-67236759');
        $array[6][] = array('新庄社区','新庄新村24幢-1号','0512-67235059');
        $array[6][] = array('西园社区','品园21-205','0512-65573244');
        $array[6][] = array('观景社区','观景新村41幢北侧','0512-65326803');
        $array[6][] = array('玻纤路社区','玻纤路38号(玻纤路新村6幢旁）','0512-65577587');
        $array[6][] = array('留园社区','留园街道三间头10号','0512-65572847');
        $array[6][] = array('湖田社区','后宝元街19-1号','0512-65576672');
        $array[6][] = array('仁安社区','金阊区航西新村0幢','0512-65565469');
        $array[6][] = array('虎丘路社区','虎丘路198号（虎丘路一号桥旁）','0512-67039811');
        $array[6][] = array('硕房庄社区','倪家苑北区17号','0512-65335341');

        $array[7][] = array('劳动保障所','彩香一村二区1号','0512-68655473');    
        $array[7][] = array('佳菱社区','菱塘新村18幢北','0512-68700031');
        $array[7][] = array('三乐湾社区','金石街39—16号','0512-65337503');
        $array[7][] = array('信记社区','南浩街17号','0512-68653508');
        $array[7][] = array('彩香一村南区社区','一村二区17幢西则','0512-68651441');
        $array[7][] = array('彩香一村三区社区','一村三区85幢B','0512-68654572');
        $array[7][] = array('彩香一村四区社区','一村四区电子新村内','0512-65335452');
        $array[7][] = array('朱家庄社区','朱家庄新村40幢','0512-65338804');

        $array[8][] = array('劳动保障所','枣市街80号','0512-65190884');      
        $array[8][] = array('胥江社区','胥江路军休所内','0512-68114116');
        $array[8][] = array('泰南社区','泰南路120号','0512-68116043');
        $array[8][] = array('胥虹社区','胥江路胥虹苑内','0512-68116141');
        $array[8][] = array('潼泾社区','劳动路386号','0512-68362845');
        $array[8][] = array('万年社区','劳动路443号','0512-68667786');
        $array[8][] = array('三香社区','三香新村4幢南','0512-68654439');
        $array[8][] = array('新沧社区','新沧花园10-103','0512-68652096');

        $array[9][] = array('劳动保障所','长吴路188号','0512-68221248');       
        $array[9][] = array('友联第一社区','友联一村33幢东侧','0512-68110715');
        $array[9][] = array('友联第二社区','友联一村32幢','0512-68555503');
        $array[9][] = array('友联第三社区','友联二村72幢附房','0512-68111378');
        $array[9][] = array('福星社区','福星小区1号楼','0512-67867522');
        $array[9][] = array('新康第一社区','桐泾南路183-5','0512-69155232');
        $array[9][] = array('姑香社区','劳动路1100号','0512-68631031');
        $array[9][] = array('象牙社区','象牙新村25幢南','0512-68290138');
        $array[9][] = array('梅亭社区','梅亭苑61幢北侧','0512-68154461');
        $array[9][] = array('四季晶华社区','宝带西路977号','0512-69152361');
        $array[9][] = array('新郭社区','友联运河大桥西侧郭运路','0512-68153815');
        $array[9][] = array('双桥社区','云庭花园7-203','0512-68275390');
        $array[9][] = array('友联社区','西环路8号','0512-68201330');
        $array[9][] = array('沧浪新城社区','吴中西路909号(汇邻中心)','0512-68117925');

        $array[10][] = array('劳动保障所','朱公桥南型69号','0512-65308972');      
        $array[10][] = array('南华社区','南华公寓31幢南侧','0512-65099346');   
        $array[10][] = array('南环第一社区','南环广场文化站','0512-65293002');
        $array[10][] = array('南环第二社区','南环新村109幢北','0512-65108022');
        $array[10][] = array('南环第三社区','南环新村109幢北','0512-65108022');
        $array[10][] = array('湄长社区','湄长路58号','0512-65617201');
        $array[10][] = array('内马路社区','内马路17号4幢南','0512-65180736');
        $array[10][] = array('兴隆桥社区','保兴里30号5幢北侧','0512-68125847');
        $array[10][] = array('盘溪第一社区','朱公桥南弄69号','0512-68225456');

        $array[11][] = array('劳动保障所','桃花桥路101号','0512-67721101');    
        $array[11][] = array('金门社区','吴趋坊57号','0512-67702833');
        $array[11][] = array('阊门社区','阊门内下塘街323号','0512-67701431');
        $array[11][] = array('环秀社区','王洗马巷18号','0512-67294309');
        $array[11][] = array('中街路社区','马大籙巷21号','0512-67271403');
        $array[11][] = array('养育巷社区','古吴路60号','0512-65211779');
        $array[11][] = array('学士社区','高井头6-1号','0512-65224909');
        $array[11][] = array('石幢社区','宝城桥弄16号','0512-67704439');
        $array[11][] = array('西街社区','阊门内下塘街140号','0512-67701013');
        $array[11][] = array('桃花坞社区','廖家巷18号','0512-67294325');

        $array[12][] = array('劳动保障所','书院巷111号','0512-65103195');    
        $array[12][] = array('桂花社区','桂花新村133幢','0512-65263907');
        $array[12][] = array('玉兰社区','玉兰新村28幢三楼','0512-65204409');
        $array[12][] = array('养蚕里第一社区','养蚕里村100幢','0512-65263207');
        $array[12][] = array('养蚕里第二社区','养蚕里村14幢','0512-65203304');
        $array[12][] = array('竹辉社区','竹辉新村6幢','0512-65160326');
        $array[12][] = array('瑞光社区','南园新村33幢101、102室','0512-65203450');
        $array[12][] = array('东大街社区','东大街12号16幢','0512-65263407');
        $array[12][] = array('西大街社区','吴县新村3－1幢','0512-65100129');
        $array[12][] = array('佳安社区','中军弄7－3号','0512-65108029');
        $array[12][] = array('吉庆社区','侍其巷36号','0512-65292357');
        $array[12][] = array('金狮社区','吕公桥弄12号','0512-65263406');
        $array[12][] = array('道前社区','瓣莲巷4号','0512-65811716');

        $array[13][] = array('劳动保障所','徐公桥弄1号','0512-65108098');  
        $array[13][] = array('葑溪社区','相门前庄20号','0512-67426941');
        $array[13][] = array('横街社区','杨枝新村25幢东','0512-67429723');
        $array[13][] = array('宏葑社区','宏葑新村80号','0512-67501055');
        $array[13][] = array('杏秀社区','现代花园31幢西','0512-67426235');
        $array[13][] = array('里河社区','里河新村84-4','0512-65263504');
        $array[13][] = array('觅渡社区','里河新村120幢','0512-65263507');
        $array[13][] = array('长岛社区','长岛花园13幢','0512-65183687');
        $array[13][] = array('杨枝社区','杨枝新村81幢南侧','0512-67427523');
        $array[13][] = array('联青社区','计家岸108号','0512-65624499');
        $array[13][] = array('翠园社区','翠园新村27幢南','0512-65296160');
        
        $array[14][] = array('劳动保障所','十梓街463号','0512-65221183');      
        $array[14][] = array('二郎巷社区','二郎巷56号（第二联合工作站  吴衙场15-1号','0512-65193069');
        $array[14][] = array('网师巷社区','十全街149号102（第二联合工作站  吴衙场15-1号）','0512-65260071');
        $array[14][] = array('百步街社区','吴衙场15-1号（第二联合工作站  吴衙场15-1号）','0512-65196062');
        $array[14][] = array('定慧寺巷社区','八宝街15号（第二联合工作站  吴衙场15-1号）','0512-65240413');
        $array[14][] = array('钟楼社区','望星桥北堍21号','0512-65234514');
        $array[14][] = array('唐家巷社区','钟楼新村10幢103室','0512-65243757');
        $array[14][] = array('大公园社区','沈衙弄4-1号（第一联合工作站  十梓街463号）','0512-65211791');
        $array[14][] = array('锦帆路社区','孝义坊3-1号（第一联合工作站  十梓街463号）','0512-65227905');
        $array[14][] = array('沧浪亭社区','长洲路42号（第一联合工作站  十梓街463号）','0512-65810289');
        $array[14][] = array('滚绣坊社区','五龙堂1-1号（第一联合工作站  十梓街463号）','0512-65191864');
        
        $array[15][] = array('就业管理中心','解放东路117号九楼','0512-65528112');   
        $array[15][] = array('创业指导中心','解放东路117号九楼','0512-65580817');
        $array[15][] = array('行政服务窗口','解放东路117号二楼','0512-65233351');
        
        $array[16][] = array('劳动保障所','人民路1719号','0512-67270213');
        $array[16][] = array('小公园社区','碧风坊一弄3号（联合工作站乔司空巷45号）','0512-69353205');
        $array[16][] = array('玄妙观社区','社坛巷6号（联合工作站乔司空巷45号）','0512-69353206');
        $array[16][] = array('察院场社区','雍熙寺弄8号','0512-65222015');
        $array[16][] = array('旧学前社区','史家巷55号','0512-67702371');
        $array[16][] = array('香花桥社区','承天寺36号','0512-67701992');
        $array[16][] = array('装驾桥社区','谢衙前30号','0512-67279609');
        $array[16][] = array('西北街社区','北大弄25号','0512-67545369');
        $array[16][] = array('北寺塔社区','石塘桥弄35号','0512-67536745');

        $array[17][] = array('劳动保障所','普福寺路27-5号','0512-67523738-8026');  
        $array[17][] = array('拙政园社区','狮林寺巷34号','0512-67272116');
        $array[17][] = array('东园社区','葛百户巷24-1号','0512-67274810');
        $array[17][] = array('北园社区','普福寺路27-1号','0512-67547330');
        $array[17][] = array('钮家巷社区','建新巷17号','0512-65244155');
        $array[17][] = array('大儒巷社区','菉葭巷52号','0512-67287161');
        $array[17][] = array('历史街区社区','小新桥巷2号','0512-67708206');

        $array[18][] = array('劳动保障所','日规路9号','0512-67666239');     
        $array[18][] = array('东环社区','永林二区70幢','0512-67426025');
        $array[18][] = array('永林社区','永林新村14幢北','0512-67426935');
        $array[18][] = array('新湘苑社区','三星路中段南侧文体中心内','0512-67169417');
        $array[18][] = array('娄江社区','塘坊苑内','0512-67247847');
        $array[18][] = array('齐门社区','挹秀新村5幢','0512-67539441');
        $array[18][] = array('梅巷社区','齐门外大街319号','0512-67534290');
        $array[18][] = array('相门社区','东环新村64幢西二楼','0512-67427431');
        $array[18][] = array('官渎社区','苏站路418号','0512-67540845');
        $array[18][] = array('鼎尚社区','苏站路225号','0512-67535373');
        
        $array[19][] = array('人力资源市场','旧学前21号','0512-65128951');
        $data['street'] = $stree;     
        $data['detail'] = $array;

        return $data;

    }
}
?>
