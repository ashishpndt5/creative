<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AdapterController;

class WayfairAdapterController extends Controller
{
	public $baseAdapter;
	public function __construct()	{
		$baseAdapter = new Controller();
		$this->baseAdapter = $baseAdapter;
		//parent::__construct();
		//$this->revisionable = $revisionable;
	}
	
    public function getOrder($ediMessage) {
    	//return ' func getorder...';
    	//return $this->baseAdapter->getMessageType();
    	$messageType = $this->baseAdapter->getOrder($ediMessage);
    	return $messageType;
	}
	
	public function getMessageType($filename,$controller) {
		//return $this->baseAdapter->getMessageType($filename,$controller);
		$messageType = $controller->getMessageType($filename,$controller);
		return $messageType;
	}
	
	public function processIncomingMessage_1($fileName, $messageType) {
		return $this->baseAdapter->processIncomingMessage($filename,$controller);
	}
}
