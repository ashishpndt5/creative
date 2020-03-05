<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Trader;
use App\TraderPartner;
use App\EdiStatusNew;
use App\Http\Controllers\AdapterController;
use App\Http\Controllers\WayfairAdapterController;
use App\Http\Controllers\OrbitDBAdapterController;
use App\Http\Controllers\TraderController;
use App\Traits\AppTrait;
use Config;
use Log;

class ReceiveMessageWFController extends AdapterController
{
	public $trader;
	public $partner;
	public $traderId;
	public $partnerId;
	public $traderPartners;
	public $partnerAdapter;
	public $traderERPAdapter;
	use AppTrait;
	
	function __construct() {
		//parent::__construct();
	}
	/*
	public function setTraderId($traderId) {
		$this->traderId = $traderId;
	}
	
	public function getTraderId() {
		return $this->traderId;
	}
	
	public function setPartnerId($partnerId) {
		$this->partnerId = $partnerId;
	}
	
	public function getPartnerId() {
		return $this->partnerId;
	}
	
	public function setPartner($partner) {
		$this->partner = $partner;
	}
	
	public function getPartner() {
		return $this->partner;
	}
	
	public function setTrader($trader) {
		$this->trader = $trader;
	}
	
	public function getTrader() {
		return $this->trader;
	}*/
	
	public function setPartnerAdapter($pAdapter) {
		$this->partnerAdapter = $pAdapter;
	}
	
	public function getPartnerAdapter() {
		return $this->partnerAdapter;
	}
	
	public function execute($fileName) {
		
		$traderId = $this->getTraderId();
		$partnerId = $this->getPartnerId();
		$traderController =  new TraderController();		
		$traderPartner = $traderController->initializeTraderPartner($traderId);		
		$this->setTrader($traderController);
		$partner = $traderController->getPartner($partnerId); //This is the id in the trader.xml
		$this->setPartner($partner);		
		$this->transaction($fileName);
	}

	public function transaction($fileName) {
		
		$traderId = $this->getTraderId();
		$partnerId = $this->getPartnerId();
		$partner = $this->getPartner();
		
		$getOrderAdapter = $partner->getOrderAdapter();	
		if (!empty($getOrderAdapter)) {
			$getOrderAdapterClass = __NAMESPACE__ . '\\' . $getOrderAdapter;
			$partnerAdapter =  new $getOrderAdapterClass($traderId,$partnerId);
			$this->setPartnerAdapter($partnerAdapter);
		} else {
			Log::warning("receiveMessageWFController:transaction Cannot load empty orderApapter controller");
		}
		
		$traderERPAdapter = (string) $this->getTrader()->getErpAdapter();
		if (!empty($traderERPAdapter)) {
			$traderERPAdapterClass = __NAMESPACE__ . '\\' . $traderERPAdapter;
			$this->traderERPAdapter = new $traderERPAdapterClass();
		} else {
			Log::warning("receiveMessageWFController:transaction Cannot load empty traderERP controller");
		}
		
		$partnerPackingSlipAdapter = (string) $partner->getPackingSlipAdapter();
		if (!empty($partnerPackingSlipAdapter)) {
			$partnerPackingSlipAdapterClass = __NAMESPACE__ . '\\' . $partnerPackingSlipAdapter;
			//include_once '../adapters/' . $partnerPackingSlipAdapterName . ".php";
			$this->packingSlipAdapter = new $partnerPackingSlipAdapterClass();			
			$this->packingSlipAdapter->setPartner($this->partner);
			$this->packingSlipAdapter->setTrader($this->trader);
		} else {
			Log::warning("receiveMessageWFController:transaction Cannot load empty partnerPackingSlipAdapter Controller");
		}
		
		$partnerShippingLabelAdapter = (string) $partner->getShippingLabelAdapter();
		if (!empty($partnerShippingLabelAdapter)) {
			$partnerShippingLabelAdapterClass = __NAMESPACE__ . '\\' . $partnerShippingLabelAdapter;
			//include_once '../adapters/' . $partnerPackingSlipAdapterName . ".php";
			$this->shippingLabelAdapter = new $partnerShippingLabelAdapterClass();
			$t = $this->trader;
			$p = $this->partner;
			$this->packingSlipAdapter->setPartner($this->partner);
			$this->packingSlipAdapter->setTrader($this->trader);
		} else {
			Log::warning("receiveMessageWFController:transaction Cannot load empty partnerShippingAdapter Controller");
		}
		
		$transactionType =  $partnerAdapter->getMessageType($fileName,$partner);
		//$partnerCommunicationAdapterName = (string) $partner->getGetOrderAdapter();
		switch ($transactionType) {
			case "Order":
				//$this->logger->LogInfo("receiveMessageWF:execute read order");
				Log::warning("receiveMessage method :execute case order");
				$orderArray = $this->readOrder($fileName, $transactionType);
				$isSucess = $this->writeOrder($orderArray);
				$result = true;
				return $result;
				break;
			case "Acknowledgement":
				//$this->logger->LogInfo("receiveMessageWF:execute processFunctionalAcknowledgement");
				Log::warning("receiveMessageWF:execute processFunctionalAcknowledgement");
				$isSucess = $this->processFunctionalAcknowledgement($fileName, $transactionType);
				$result['isError'] = !$isSucess;				
				break;
		}
	}
	
	function readOrder($fileName, $messageType) {
		
		$ediStatusNew = new EdiStatusNew;
		//$orderArray = $this->partnerCommunicationAdapter->processIncomingMessage($fileName, $messageType, $this->partner);
		$orderArray[] = $this->getPartnerAdapter()->processIncomingMessage($fileName, $messageType);
		
		$trader_id = $this->getTraderId();
		$partner_id = $this->getPartnerId();
		
		foreach ($orderArray as $ak => $order) {
			
			$poNumber = $order->poNumber;
			$soNumber = $order->getSoNumber();
			$isEligible = $ediStatusNew->isEligibleToProcess($trader_id, $partner_id, $poNumber);
			if (!$isEligible['flag']) {
				unset($orderArray[$ak]);
				continue;
			}
			//$isEligible['status'] = 'RR';
			if (isset($isEligible['status']) && $isEligible['status'] == 'RR') {
				$order->setReRun(true);
				//$zDB = new znectDBAdapter($this->getLogger());
				$this->deleteCancelledOrder($this->getTrader()->getId(), $this->getPartner()->getPartnerId(), $order->getPoNumber());
			}
		
			//$this->dbObject->logOrder($this->getTrader()->getId(), $this->getPartner()->getId(), $order->getPoNumber(), $order->getSoNumber(), "RC", $order->getRawOrder());
			$workFlowId = $this->logOrder($trader_id, $partner_id, $poNumber, $soNumber, "RC", $order->getRawOrder());
			//$order->setWorkFlowId($workFlowId);
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
		return $orderArray;
	}
	
	function writeOrder($orderArray) {
		
		$trader = $this->getTrader();
		$this->traderERPAdapter = $trader->getErpAdapter();		
		$tClassName = __NAMESPACE__ . '\\' . $this->traderERPAdapter;
		$traderErpAdapter =  new $tClassName;
		
		foreach ($orderArray as $thisOrder) {
			
			$thisOrder->setTrader($this->trader);
			$thisOrder->setPartner($this->partner);
			$thisOrder->setStatus('RC');			
			
			$traderId = $thisOrder->getTrader()->getId();
			$partnerId = $thisOrder->getPartner()->getPartnerId();
			$poNumber = $thisOrder->getPoNumber();
						
			$result = $this->saveOrder($thisOrder); // Added for Order to insert in DB
			//$result = true;
			if(!$result) {
				Log::warning("ReceiveMessageWFController:writeOrder : DB Adapter:saveOrder putOrder failed");
				//$this->logger->LogError("receiveMessageWF:writeOrder: DB Adapter:putOrder failed ");
				//$workFlowId = $this->logOrder($trader_id, $partner_id, $poNumber, $soNumber, "RC", $order->getRawOrder());
				
				$updateParameters = array( "status"=>"ER", "ErrorMassage"=>"Unable to insert into znect DB");
				$keys = array("traderId" => $traderId, "partnerId" => $partnerId, "poNumber" => $poNumber);
				$this->updateOrderInfoStatus('orderinfo' , $keys, $updateParameters);
				//TODO: should the order be removed from the orderArray?
			}
	
			$response = $traderErpAdapter->putOrder($thisOrder);
			//$response['isError'] = true;
			if (!$response['isError']) {
				//Log that order is created in the ERP system successfully.
				//$this->updateObject($thisOrder);
				$soNumber = $thisOrder->getSoNumber();
				//$this->dbObject->logOrder($thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $thisOrder->getSoNumber(), "ET", "Order Total: " . $thisOrder->getGrandTotal());
				$workFlowId = $this->logOrder($traderId, $partnerId, $poNumber, $soNumber, "ET", "Order Total: " . $thisOrder->getGrandTotal());
				/*if($thisOrder->getRegenerateLabels() == 'yes'){
					//include_once '../scripts/regenerateAmazonLabels.php'; // to work on
					try{
						$orderId = $thisOrder->getSoNumber();
						$sellerpartner = $thisOrder->getTrader()->getPartner($thisOrder->getTrader()->getDefaultWarehouseID());
						$thisOrder->setSellerPartner($sellerpartner);
						generateLabels($this->traderERPAdapter , $orderId , $thisOrder->getTrader()->getId() , $thisOrder->getSellerPartner()->getId(), $thisOrder);
					}catch (Exception $e){
						sendEmail("ashblaze@gmail.com", "Order #" .$orderId. " " . $e->getMessage(). " Labels not generated. Please Check");
						$this->logOrder($thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getPartnerId(), $thisOrder->getPoNumber(), $thisOrder->getSoNumber(), "ER", "Labels not generated.");
						continue;
					}
				}*/
				if($thisOrder->getPartner()->getInvoiceCreate() == 'yes') {
					//$this->traderERPAdapter->invoiceOrder($thisOrder);
					$invoiceOrder = $traderErpAdapter->invoiceOrder($thisOrder);
					$this->logOrder($thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getPartnerId(), $thisOrder->getPoNumber(), $thisOrder->getSoNumber(), "IC", "");
				}
	
				// update orderInfo for order enter
				$updateParameters = array("soNumber"=>$thisOrder->getSoNumber(), "status"=>"ET", "enteredInERP"=>"YES");
				$keys = array( "traderId"=>$this->getTraderId(), "partnerId"=>$this->getPartnerId(), "poNumber"=>$thisOrder->getPoNumber());
				$this->updateOrderInfoStatus('orderInfo' , $keys, $updateParameters);
				
				$updateParameters = array("soNumber"=>$thisOrder->getSoNumber());
				$keys = array( "traderId"=>$this->getTraderId(), "partnerId"=>$this->getPartnerId(), "poNumber"=>$thisOrder->getPoNumber());
				//$this->updateOrderInfoStatus('new_orders', $thisOrder->getTrader()->getId(), $thisOrder->getPartner()->getId(), $thisOrder->getPoNumber(), $updateParameters);
			} else {
				//$this->logger->LogError("receiveMessageWF:writeOrder: ERP Adapter:putOrder failed with an error: ", $response["errorMessage"]);
				Log::warning("receiveMessageWF:writeOrder: ERP Adapter:putOrder failed with an error: ".$response["errorMessage"]);
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
