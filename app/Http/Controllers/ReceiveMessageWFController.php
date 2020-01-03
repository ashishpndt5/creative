<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Trader;
use App\TraderPartner;
use App\EdiStatusNew;
use App\Http\Controller\AdapterController;
use App\Http\Controllers\WayfairAdapterController;
use App\Traits\AppTrait;
use Config;

class ReceiveMessageWFController extends Controller
{
	public $trader;
	public $partner;
	public $traderPartners;
	public $partnerAdapter;
	use AppTrait;
	
	public function execute($traderId, $partnerId, $fileName) {
		//echo $user;
		//$cc = $this->getData();
		$traderId;
		$partnerId;
		$fileName;
		$this->traderId = $traderId;
		$this->setPartnerId($partnerId);
		$data = DB::table('traders')->get();
		//$comment = Trader::find(1)->TraderPartner()->where('id', 1)->first()->get();
		//$comment = Trader::find(1)->TraderPartner()->where('id', 5)->get();
		//$comment = Trader::where("trader_id",5)->first();
		$traderPartner = Trader::find($traderId)->trader_partners->first()->getOriginal();
		$this->trader = Trader::find($traderId)->first()->getOriginal();
		//$this->traderPartners = Trader::find($traderId)->trader_partners;
		//$this->traderPartners = TraderPartner::find($partnerId);
		$this->traderPartners = TraderPartner::where('partnerId',$partnerId)->where('trader_id',$traderId)->get()->first()->getOriginal();
		
		//$controller = new Controller();
		$this->setPartner($partnerId);
		//$controller->storage($this->trader, $this->traderPartners);
		$this->storage($this->trader, $this->traderPartners);
		//$comment = TraderPartner::All();
		//dd($this->traderPartners);
		$this->transaction($fileName,$traderId,$partnerId);
	}

	public function transaction($fileName,$traderId,$partnerId) {
		
		$tr = $this->trader;
		//$p = $this->traderPartners[0]->getAttributes();
		//$dd = $this->traderPartners[0]->getOriginal();
		//$class= "App\Http\Controllers";
		//$orderAdapter = $this->traderPartners[0]->getOriginal('getOrderAdapter');
		
		//$orderAdapter = $this->traderPartners->getOrderAdapter;
		$traderPartners = TraderPartner::where('partnerId',$partnerId)->where('trader_id',$traderId)->get()->first();
		//$traderPartners = TraderPartner::find(1);
		$getOrderAdapter = $traderPartners->getOrderAdapter;
		
		//AdapterController::getMessageType('file');
		//AdminController $admin;
		//$this->partnerAdapter = new WayfairAdapterController();
		$className = __NAMESPACE__ . '\\' . $getOrderAdapter;
		$this->partnerAdapter =  new $className;
		//$this->partnerAdapter = new WayfairAdapterController();
		//$c = "WayfairAdapterController()";
		//$class = new $c;
		//$this->partnerAdapter = new $class;
		$partnerid = $this->getPartnerId();
		$transactionType =  $this->partnerAdapter->getMessageType($fileName);
		//$partnerCommunicationAdapterName = (string) $partner->getGetOrderAdapter();
		switch ($transactionType) {
			case "Order":
				//$this->logger->LogInfo("receiveMessageWF:execute read order");
				$orderArray = $this->readOrder($fileName, $transactionType);
				$isSucess = $this->writeOrder($orderArray);
				$result = true;
				return $result;
				break;
			case "Acknowledgement":
				//$this->logger->LogInfo("receiveMessageWF:execute processFunctionalAcknowledgement");
				$isSucess = $this->processFunctionalAcknowledgement($fileName, $transactionType);
				$result['isError'] = !$isSucess;				
				break;
		}
	}
	
	function readOrder($fileName, $messageType) {
		
		$ediStatusNew = new EdiStatusNew;
		//$orderArray = $this->partnerCommunicationAdapter->processIncomingMessage($fileName, $messageType, $this->partner);
		$orderArray = $this->partnerAdapter->processIncomingMessage($fileName, $messageType);
		//$rules = new znectRules($this->trader, $this->partner);
		$orderArrays[] = $orderArray;
		$trader_id = Config::get('filesystems.trader_id');
		$partner_id = Config::get('filesystems.partner_id');
		foreach ($orderArrays as $ak => $order) {
			$oo = $order->poNumber;
			//$o = $order->poNumber();
			//$isEligible = $this->dbObject->isEligibleToProcess($this->getTrader()->getId(), $this->getPartner()->getId(), $order->getPoNumber());
			$isEligible = $ediStatusNew->isEligibleToProcess($trader_id, $partner_id, $order->poNumber);
			if (!$isEligible['flag']) {
				unset($orderArrays[$ak]);
				continue;
			}
		
			if (isset($isEligible['status']) && $isEligible['status'] == 'RR') {
				$order->setReRun(true);
				$zDB = new znectDBAdapter($this->getLogger());
				$zDB->deleteCancelledOrder($this->getTrader()->getId(), $this->getPartner()->getId(), $order->getPoNumber());
			}
		
			//$this->dbObject->logOrder($this->getTrader()->getId(), $this->getPartner()->getId(), $order->getPoNumber(), $order->getSoNumber(), "RC", $order->getRawOrder());
			$this->logOrder($trader_id, $partner_id, $order->getPoNumber(), $order->getSoNumber(), "RC", $order->getRawOrder());
			/*try {
				$order->setTrader($this->getTrader());
				$order->setPartner($this->getPartner());
				$newOrder = $rules->applyRules($order);
				if (isset($isEligible['status']) && $isEligible['status'] == 'RR') {
					$this->dbObject->deleteSpecificStatus($this->getTrader()->getId(), $this->getPartner()->getId(), $order->getPoNumber(),'RR');
					$this->dbObject->deleteSpecificStatus($this->getTrader()->getId(), $this->getPartner()->getId(), $order->getPoNumber(),'ER');
				}
			} catch (Exception $ex) {
				$this->logger->LogFatal("receiveMessageWF:readOrder: Exception Caught", $ex . getMessage());
				exit;
			}*/
		}
		return $orderArrays;
	}
	
	function writeOrder($orderArray) {
		//$zDB = new znectDBAdapter($this->getLogger());
		 
		foreach ($orderArray as $thisOrder) {
			$thisOrder->setTrader($this->trader);
			$thisOrder->setPartner($this->traderPartners);
			$result = $this->saveOrder($thisOrder); // Added for Order to insert in DB
			if(!result){
				$this->logger->LogError("receiveMessageWF:writeOrder: DB Adapter:putOrder failed ");
				$updateParameters = array( "status"=>"ER", "ErrorMassage"=>"Unable to insert into znect DB");
				$this->dbObject->updateOrderInfoStatus('orderInfo' , $thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $updateParameters);
				//TODO: should the order be removed from the orderArray?
			}
	
			$response = $this->traderERPAdapter->putOrder($thisOrder);
			if (!$response['isError']) {
				//Log that order is created in the ERP system successfully.
				$zDB->updateOrder($thisOrder);
	
				$this->dbObject->logOrder($thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $thisOrder->getSoNumber(), "ET", "Order Total: " . $thisOrder->getGrandTotal());
				if($thisOrder->getRegenerateLabels() == 'yes'){
					include_once '../scripts/regenerateAmazonLabels.php';
					try{
						$orderId = $thisOrder->getSoNumber();
						$sellerpartner = $thisOrder->getTrader()->getPartner($thisOrder->getTrader()->getDefaultWarehouseID());
						$thisOrder->setSellerPartner($sellerpartner);
						generateLabels($this->traderERPAdapter , $orderId , $thisOrder->getTrader()->getId() , $thisOrder->getSellerPartner()->getId(), $thisOrder);
					}catch (Exception $e){
						sendEmail("sales@fabhabitat.com", "Order #" .$orderId. " " . $e->getMessage(). " Labels not generated. Please Check");
						$this->dbObject->logOrder($thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $thisOrder->getSoNumber(), "ER", "Labels not generated.");
						continue;
					}
				}
				if($thisOrder->getPartner()->getInvoiceCreate() == 'yes') {
					$this->traderERPAdapter->invoiceOrder($thisOrder);
					$this->dbObject->logOrder($thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $thisOrder->getSoNumber(), "IC", "");
				}
	
				// update orderInfo for order enter
				$updateParameters = array("soNumber"=>$thisOrder->getSoNumber(), "status"=>"ET", "enteredInERP"=>"YES");
				$this->dbObject->updateOrderInfoStatus('orderInfo' , $thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $updateParameters);
				$updateParameters = array("soNumber"=>$thisOrder->getSoNumber());
				$this->dbObject->updateOrderInfoStatus('newOrder', $thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $updateParameters);
			} else {
				$this->logger->LogError("receiveMessageWF:writeOrder: ERP Adapter:putOrder failed with an error: ", $response["errorMessage"]);
				//log that order errored out in ERP system
				if ($thisOrder->getHasOutofStockItems()) {
					$this->dbObject->logOrder($thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $thisOrder->getSoNumber()."CN", "CN", "", $response['errorMessage']);
					$thisOrder->setStatus("CN");
					//Notify trader that the order errored out with error message
				} else {
					$this->dbObject->logOrder($thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $thisOrder->getSoNumber(), "ER", "", addslashes($response['errorMessage']));
					$thisOrder->setStatus("ER");
				}
	
				$updateParameters = array( "status"=>"ER", "ErrorMassage"=>$response["errorMessage"]);
				$this->dbObject->updateOrderInfoStatus('orderInfo' , $thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $updateParameters);
				//TODO: should the order be removed from the orderArray?
			}
		}
		return true;
	}
}
