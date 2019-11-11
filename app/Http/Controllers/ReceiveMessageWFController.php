<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Trader;
use App\TraderPartner;
use App\Http\Controller\AdapterController;
use App\Http\Controllers\WayfairAdapterController;

class ReceiveMessageWFController extends Controller
{
	public $trader;
	public $traderPartners;
	
	public function execute($traderId, $partnerId, $fileName) {
		$traderId;
		$partnerId;
		$fileName;
		
		$data = DB::table('traders')->get();
		//$comment = Trader::find(1)->TraderPartner()->where('id', 1)->first()->get();
		//$comment = Trader::find(1)->TraderPartner()->where('id', 5)->get();
		//$comment = Trader::where("trader_id",5)->first();
		$trader = Trader::find($traderId)->trader_partners;
		$this->trader = Trader::find($traderId);
		//$this->traderPartners = Trader::find($traderId)->trader_partners;
		//$this->traderPartners = TraderPartner::find($partnerId);
		$this->traderPartners = TraderPartner::where('partnerId',$partnerId)->where('trader_id',5)->get();
		$controller = new Controller();
		$controller->storage($this->trader, $this->traderPartners);
		//$comment = TraderPartner::All();
		//dd($this->traderPartners);
		$this->transaction($controller);
	}

	public function transaction($controller) {
		
		$tr = $controller->getTrader();
		$p = $this->traderPartners[0]->getAttributes();
		//$dd = $this->traderPartners[0]->getOriginal();
		$orderAdapter = $this->traderPartners[0]->getOriginal('getOrderAdapter');		
		//AdapterController::getMessageType('file');
		$n = new WayfairAdapterController();
		$rr =  $n->getMessageType('abc.edi');
		//$partnerCommunicationAdapterName = (string) $partner->getGetOrderAdapter();
	}
}
