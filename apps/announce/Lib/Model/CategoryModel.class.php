<?php

class CategoryModel extends Model {

    var $tableName = 'announce_category';

    //生成分类Tree
    public function _makeTree($pid) {
        if ($pid == 0 && $cache = S('Cache_Announce_Cate_' . $pid)) { // pid=0 才缓存
            return $cache;
        }

        if ($c = $this->where("pid='$pid'")->order('display_order ASC, id ASC')->findAll()) {
            if ($pid == 0) {
                foreach ($c as $v) {
                    $cTree['t'] = $v['title'];
                    $cTree['a'] = $v['id'];
                    $cTree['d'] = $this->_makeTree($v['id']);
                    $cTrees[] = $cTree;
                }
            } else {
                foreach ($c as $v) {
                    $cTree['t'] = $v['title'];
                    $cTree['a'] = $v['id'];
                    $cTree['d'] = ''; //$v['id'];
                    $cTrees[] = $cTree;
                }
            }
        }
        $pid == 0 && S('Cache_Announce_Cate_' . $pid, $cTrees); // pid=0 才缓存
        return $cTrees;
    }

    public function _makeTopTree() {

        $pid = 0;
        if ($pid == 0 && $cache = S('Cache_Announce_Cate_top_' . $pid)) { // pid=0 才缓存
            return $cache;
        }

        if ($c = $this->where("pid='$pid'")->order('id ASC')->findAll()) {
            if ($pid == 0) {
                foreach ($c as $v) {
                    $cTree['t'] = $v['title'];
                    $cTree['a'] = $v['id'];
                    $cTrees[] = $cTree;
                }
            } else {
                foreach ($c as $v) {
                    $cTree['t'] = $v['title'];
                    $cTree['a'] = $v['id'];
                    $cTrees[] = $cTree;
                }
            }
        }
        $pid == 0 && S('Cache_Announce_Cate_top_' . $pid, $cTrees); // pid=0 才缓存
        return $cTrees;
    }

    //获取LI列表
    public function getCategoryList($pid = '0') {
        $list = $this->_makeLiTree($pid);
        return $list;
    }

    public function _makeLiTree($pid) {

        if ($c = $this->where("pid='$pid'")->order( 'display_order ASC, id ASC' )->findAll()) {

            $list .= '<ul>';
            foreach ($c as $p) {
                @extract($p);

                $ptitle = "<span id='category_" . $id . "' title='" . $title . "'><a href='javascript:void(0)' onclick=\"edit('" . $id . "')\">" . $title . "</a></span>";
                $title = '[' . $id . '] ' . $ptitle;

                $list .= '
					<li id="li_' . $id . '">
					<span style="float:right;">
						<a href="javascript:void(0)" onclick="edit(\'' . $id . '\')" style="font-size:9px">修改</a>
						<a href="javascript:void(0)" onclick="del(\'' . $id . '\')" style="font-size:9px">删除</a>
					</span> ' . $title . '
					</li>
					<hr style="height:1px;color:#ccc" />';

                $list .= $this->_makeLiTree($id);
            }
            $list .= '</ul>';
        }
        return $list;
    }

    //解析分类
    public function _digCate($array) {

        foreach ($array as $k => $v) {

            $nk = str_replace('pid', '', $k);
            if (is_numeric($nk) && !empty($v)) {
                $cates[$nk] = intval($v);
            }
        }
        $pid = is_array($cates) ? end($cates) : 0;

        unset($cates);
        return intval($pid);
    }

    //解析分类树
    public function _digCateTree($array) {
        foreach ($array as $k => $v) {
            $nk = str_replace('pid', '', $k);
            if (is_numeric($nk) && !empty($v)) {
                $cates[$nk] = intval($v);
            }
        }
        if (is_array($cates)) {
            return implode(',', $cates);
        } else {
            return intval($cates);
        }
    }

    //生成分类树
    public function _makeParentTree($id, $onlyShowPid = false) {
        $tree = $this->_makeCateTree($id);
        if ($onlyShowPid) {
            $tree = str_replace(',' . $id, '', $tree);
        }
        return $tree;
    }

    public function _makeCateTree($id) {
        //$pid	=	$this->find($id,'pid')->pid;

        $pid = $this->getField('pid', 'id=' . $id);
        if ($pid > 0) {
            $tree = $this->_makeCateTree($pid) . ',' . $id;
        } else {
            $tree = $id;
        }
        return $tree;
    }

    /**
     * GiftToCategory
     * 获取分组列表
     * @return unknown_type
     */
    public function __getCategory() {
        $categorys = $this->order('display_order ASC, id ASC')->findAll();
        foreach ($categorys as $v) {
            $categorys_[$v['id']] = $v['title'];
        }
        return $categorys_;
    }

}

?>