<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AdapterController;

class WayfairAdapterController extends AdapterController
{
	public $baseAdapter;
	public function __construct($traderId = null, $partnerId = null) {
		$baseAdapter = new Controller();
		$this->baseAdapter = $baseAdapter;
		//parent::__construct();
		//$this->revisionable = $revisionable;
		$this->setTraderId($traderId);
		$this->setPartnerId($partnerId);
	}
}
