<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AdapterController;

class WayfairAdapterController extends Controller
{
	public $baseAdapter;
	public function __construct()	{
		$baseAdapter = new AdapterController();
		$this->baseAdapter = $baseAdapter;
		//parent::__construct();
		//$this->revisionable = $revisionable;
	}
	
    public function getOrder() {
    	//return ' func getorder...';
    	return $this->baseAdapter->getMessageType();
	}
	
	public function getMessageType($filename) {
		return $this->baseAdapter->getMessageType($filename);
	}
}
