<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AdapterController;

//include_once '../adapters/adapter.php';
//include_once '../shipping/Wayfair/wayfair_packingSlip.php';

class wayfairPackingSlipAdapterController  extends AdapterController {

	public function execute($order){
		$p = $this->getPartner();
		if(!$p){//for backward compatibility
			$p = $order->getPartner();
		}
		$ps = new WayfairPackingSlip($this->logger);
		return $ps->execute($order, $p);
		//LogStatus($order, "Generated", mysql_real_escape_string($this->Output('', 'S')), "sent", $fileName);
	}
	
	function __construct() {
		//parent::__construct();
	}
}

?>
