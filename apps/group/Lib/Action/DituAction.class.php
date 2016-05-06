<?php

class DituAction extends AdministratorAction
{

	var $Category;
	public function _initialize() {
		parent::_initialize ();
		$this->Category = D ( 'Category' );
	}
    protected function index()
    {
		$this->display('Admin/ditulist');
    }

}