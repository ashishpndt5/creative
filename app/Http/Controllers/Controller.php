<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
	public $trader;
	public $partner;
	
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function storage($trader = null, $partner = null) {
    	
    	$this->trader = $trader;
    	$this->partner = $partner;
    }
    
    public function getTrader() {
    	
    	return $this->trader;
    }
    
    public function getPartner() {
    	 
    	return $this->partner;
    }
}
