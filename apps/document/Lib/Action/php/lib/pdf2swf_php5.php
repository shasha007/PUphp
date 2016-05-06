<?php
/**
* █▒▓▒░ The FlexPaper Project 
* 
* Copyright (c) 2009 - 2011 Devaldi Ltd
*
* GNU GENERAL PUBLIC LICENSE Version 3 (GPL).
* 
* The GPL requires that you not remove the FlexPaper copyright notices
* from the user interface. 
*  
* Commercial licenses are available. The commercial player version
* does not require any FlexPaper notices or texts and also provides
* some additional features.
* When purchasing a commercial license, its terms substitute this license.
* Please see http://flexpaper.devaldi.com/ for further details.
* 
*/

require_once("config.php");
require_once("common.php");

class pdf2swf
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
        //echo "pdf2swf destructed\n";
    	}

	/**
	* Method:convert
	*/
	public function convert($doc,$target)
	{
		$output=array();
		$pdfFilePath = $doc;
		$swfFilePath = $target;

		$command = $this->configManager->getConfig('cmd.conversion.singledoc');
			
		$command = str_replace("{pdffile}",$doc,$command);
		$command = str_replace("{swffile}",$target,$command);
	
		try {
			if (!$this->isNotConverted($pdfFilePath,$swfFilePath)) {
				//array_push ($output, utf8_encode("[Converted]"));
				return true;
			}
		} catch (Exception $ex) {
			//array_push ($output, "Error," . utf8_encode($ex->getMessage()));
			return false;
		}

		$return_var=0;
		

		//echo $command;
		
		exec($command,$output,$return_var);
		
		//print_r($output);
		//echo $return_var;
		
		try {
			if (!$this->isNotConverted($pdfFilePath,$swfFilePath)) {
				//array_push ($output, utf8_encode("[Converted]"));
				return true;
			}
		} catch (Exception $ex) {
			//array_push ($output, "Error," . utf8_encode($ex->getMessage()));
			return false;
		}
		
		return false;
	}

	/**
	* Method:isNotConverted
	*/
	public function isNotConverted($pdfFilePath,$swfFilePath)
	{
		if (!file_exists($pdfFilePath)) {
			throw new Exception("Document does not exist");
		}
		if ($swfFilePath==null) {
			throw new Exception("Document output file name not set");
		} else {
			if (!file_exists($swfFilePath)) {
				return true;
			} else {
				if (filemtime($pdfFilePath)>filemtime($swfFilePath)) return true;
			}
		}
		return false;
	}
}
?>
