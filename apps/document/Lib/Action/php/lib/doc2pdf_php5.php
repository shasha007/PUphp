<?php

require_once("config.php");
require_once("common.php");

class doc2pdf
{
	private $configManager = null;

	/**
	* Constructor
	*/
	function __construct()
	{
		$this->configManager = new Config();
	}

	/**
	* Destructor
	*/
	function __destruct() {
        //echo "doc2pdf destructed\n";
    	}

	/**
	* Method:convert
	*/
	public function convert($doc,$target)
	{
		$output=array();
		
		$docFilePath = $doc;
		$pdfFilePath = $target;
		
		$command = $this->configManager->getConfig('cmd.conversion.jodconverter');
		
		$command = str_replace("{docfile}",$doc,$command);
		$command = str_replace("{pdffile}",$target,$command);
				
		try {
			if (!$this->isNotConverted($docFilePath,$pdfFilePath)) {
				//array_push ($output, utf8_encode("[Converted]"));			
				return true;
			}
		} catch (Exception $ex) {
			array_push ($output, "Error," . utf8_encode($ex->getMessage()));
			return false;
		}
		
		//echo $command;
		$return_var=0;
		exec($command,$output,$return_var);	
		//print_r($output);
		
		//exec(getForkCommandStart() . $command . getForkCommandEnd());
			
		try {
			if (!$this->isNotConverted($docFilePath,$pdfFilePath)) {
				array_push ($output, utf8_encode("[Converted]"));
				return true;
			}
		} catch (Exception $ex) {
			array_push ($output, "Error," . utf8_encode($ex->getMessage()));
			return false;
		}
		return false;
	}

	/**
	* Method:isNotConverted
	*/
	public function isNotConverted($docFilePath,$pdfFilePath)
	{
		if (!file_exists($docFilePath)) {
			throw new Exception("Document does not exist");
		}
		if ($pdfFilePath==null) {
			throw new Exception("Document output file name not set");
		} else {
			if (!file_exists($pdfFilePath)) {
				return true;
			} else {
				if (filemtime($docFilePath)>filemtime($pdfFilePath)) return true;
			}
		}
		return false;
	}
}
?>
