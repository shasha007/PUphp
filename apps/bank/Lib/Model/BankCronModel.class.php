<?php

/**
 * 
 * @author Administrator
 * 
 */
class BankCronModel extends Model
{
	public function getCronById($id)
	{
		return $this->where(array('id'=>$id))->find();
	}
	
	public function getCron($type,$path,$filename)
	{
		return $this->where(array('type'=>$type,'path'=>$path,'filename'=>$filename))->find();
	}
	
	public function getCronCount($type,$path,$filename)
	{
		return $this->where(array('type'=>$type,
									'path'=>$path,
									'filename'=>$filename,
									'status'=>array('LT',2)))
					->count();
	}
	
	public function addData($type,$path,$filename)
	{
		return $this->add(array('type'=>$type,'path'=>$path,'filename'=>$filename,'ctime'=>date('Y-m-d H:i:s')));
	}
}

?>
