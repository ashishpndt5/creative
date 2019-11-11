<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppConfigController extends Controller
{
    private $data_path;
    private $config;
    
	public function __construct(){
		$zDir = dirname(dirname(__FILE__));
		$value = config('database.connections.mysql.host');
		//$this->config = simplexml_load_file($zDir . "../../config/znect_config.xml");
		$this->data_path = (string) $value;	
	}
	
	public function getDataPath() {
		return $this->data_path;
	}
	
	public function getConfig() {
		return $this->config;
	}
	
	public function getDBParameters() {
		return $this->config->database;
	}
}
