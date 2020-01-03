<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use View;
use Illuminate\Support\Facades\Auth;
use Config;
use Storage;
use App\Http\Controllers\MagentoOrderController;
use App\Http\Controllers\NewOrderController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ItemController;
use App\TraderPartner;
use App\RefCounter;
use App\EdiStatusNew;
use App\NewOrder;

class Controller extends BaseController
{
	public $trader;
	public $partner;
	public $partnerId;
	public $traderId;
	public $transactionSetIdentifierCode;
	public $transactionTypeCode;
	private $orderArray = array();
	
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    //public function __construct() {
    	
    	//View::share('user', Auth::user());
    	//View::share('social', Social::all());
  //  }
    
    public function storage($trader = null, $partner = null) {
    	
    	$this->trader = $trader;
    	$this->partner = $partner;
    }
    
    public function getTrader() {
    	
    	return $this->trader;
    }
    
    public function setPartner($partner) {
    
    	$this->partnerId = $partner;
    }
    
    public function getPartnerId() {
    	 
    	return $this->partnerId;
    }
    
    public function setPartnerId($partner_id) {
    
    	$this->partnerId = $partner_id;
    }
    
    public function getPartner() {
    
    	return $this->partner;
    }
    
    public function processIncomingMessage($fileName, $messageType) {
    	//echo 'processIncomingMessage';
    	$ediFileStream = $this->getMessageData($fileName);
    	//echo $ediFileStream;
    	$ediArrays = $this->parseEDIMessage($ediFileStream);
    	unset($ediArrays[0]);
    	$isaccepted = true;
    	foreach ($ediArrays as $ediMessage) {
    		if ($ediMessage['ST'][1][1] == "997") {
    			$isaccepted &= $this->process997($ediMessage);
    		} elseif ($ediMessage['ST'][1][1] == "850") {
    			$orderArray = $this->getOrder($ediMessage);
    		}
    	}
    	return $orderArray;
    	return $this->getOrderArray();
    }
    
    public function getMessageData_1($fileName) {
    	$fileContents = file_get_contents($fileName);
    	return $fileContents;
    }
    
    public function getMessageData($fileName) {
    	$path = Config::get('filesystems.path');
    	//$tr = $controller->getTrader();
    	$trader_id = Config::get('filesystems.trader_id');
    	$partner_id = Config::get('filesystems.partner_id');
    	$fileNamePath = $trader_id. DIRECTORY_SEPARATOR .$partner_id. DIRECTORY_SEPARATOR . 'in'. DIRECTORY_SEPARATOR . $fileName;
    	$path = Storage::disk('public')->path($fileNamePath);
    	$fileContents = file_get_contents($path);
    	return $fileContents;
    }
    
    public function parseEDIMessage($contentOfTheEDIFile) {
    	//$tempPartner = new partner;
    	//$tempPartner = $this->partner;
    	$tempPartner = $this->getPartner();
    	//$this->logger->LogInfo("ediAdapter: parseEdiMessage: edifile contents");
    	//$this->logger->LogInfo("$contentOfTheEDIFile");
    	if(!empty($tempPartner)) {
    		$fieldSeperator = $this->getpartner()->getFieldSeperator();
    		$segmentSeperator = $this->getpartner()->getEdiSegmentSeperator();
    	} else {
    		$fieldSeperator = "*";
    		$segmentSeperator = "~";
    	}
    	if($segmentSeperator <> '') {
    		$contentOfTheEDIFile = str_replace("\n", $segmentSeperator, $contentOfTheEDIFile);
    		$contentOfTheEDIFile = str_replace("\r", "", $contentOfTheEDIFile);
    		$contentOfTheEDIFile = str_replace("\x85", $segmentSeperator, $contentOfTheEDIFile);
    	} else {
    		$contentOfTheEDIFile = str_replace("\n", "~", $contentOfTheEDIFile);
    		$contentOfTheEDIFile = str_replace("\r", "", $contentOfTheEDIFile);
    		//TODO: get the ediSegmentSeperator from trader.xml
    		$contentOfTheEDIFile = str_replace("\x85", "~", $contentOfTheEDIFile);
    	}
    
    	if($segmentSeperator <> '') {
    		$allLines = explode($segmentSeperator, $contentOfTheEDIFile);
    	} else {
    		$allLines = explode("~", $contentOfTheEDIFile);
    	}
    	$messageNumber = 0;
    	$singleEDIMessage = "";
    	$existingSegment = array();
    	$message = array();
    	$segmentCounter = array();
    	
    	foreach ($allLines as $line) {
    		if($fieldSeperator<>'') {
    			$fields = explode($fieldSeperator, $line);
    		} else {
    			$fields = explode("*", $line);
    		}
    		if ($fields[0] == "GS") {
    			$this->setTransactionSetIdentifierCode($fields[6]);
    			$this->setTransactionTypeCode($fields[1]);
    			if($segmentSeperator <> '') {
    				$gsline = "$line".$segmentSeperator;
    			} else {
    				$gsline = "$line~";
    			}
    		} elseif ($fields[0] == "ST") {
    			$singleEDIMessage = "";
    			if($segmentSeperator <> '') {
    				//$singleEDIMessage .= $gsline.$segmentSeperator.$line.$segmentSeperator;
    				$singleEDIMessage .= $gsline.$line.$segmentSeperator;
    			} else {
    				$singleEDIMessage .= $gsline.$line."~";
    			}
    			$messageNumber++;
    			$existingSegment = array();
    			$existingSegment[] = $fields[0];
    			//$segmentCounter[$field] = 0;
    			if(!isset($segmentCounter[$fields[0]])) {
    				$segmentCounter[$fields[0]] = 0;
    			}
    
    			
    		} elseif ($fields[0] == "SE") {
    			$singleEDIMessage .=$line;
    			$message[$messageNumber]["ediMessage"] = $singleEDIMessage;
    		} else {
    			if($segmentSeperator <> '') {
    				$singleEDIMessage .= "$line"."$segmentSeperator";
    			} else {
    				$singleEDIMessage .= "$line~";
    			}
    		}
    			
    		$fieldCounter = 0;
    		foreach ($fields as $field) {
    			if ($fieldCounter == 0) {
    				$segmentType = $field;
    					
    				$foundDuplicate = in_array($field, $existingSegment);
    				//$segmentCounter[$field];
    				if ($foundDuplicate === TRUE ) {
    					$segmentCounter[$field]++;
    				} else {
    					$segmentCounter[$field] = 0;
    				}
    					
    				$existingSegment[] = $field;
    					
    				$fieldCounter++;
    			} else {
    				$counter = $segmentCounter[$segmentType];
    				$message[$messageNumber][$segmentType][$counter][$fieldCounter] = $field;
    				$fieldCounter++;
    			}
    		}
    	}
    	return $message;
    }
    
    public function getEdiMessageType($fileName) {
    	$msgType = "";
    	$functionId = "";
    	$ediFileStream = $this->getMessageData($fileName);
    	$tPartner = $this->getPartner();
    	//$tPartner = $this->getPartner();
    	if(!empty($tPartner)) {
    		$fieldSeperator = strtoupper($tPartner[0]->getOriginal('fieldSeperator'));
    		//$fieldSeperator = $this->getpartner()->getFieldSeperator();
    		$segmentSeperator = $tPartner[0]->getOriginal('ediSegmentSeperator');
    	} else {
    		$fieldSeperator = "*";
    		$segmentSeperator = "~";
    	}
    	if($segmentSeperator <> '') {
    		$contentOfTheEDIFile = str_replace("\n", $segmentSeperator, $ediFileStream);
    		$contentOfTheEDIFile = str_replace("\x85", $segmentSeperator, $contentOfTheEDIFile);
    		$allLines = explode($segmentSeperator, $contentOfTheEDIFile);
    	} else {
    		$contentOfTheEDIFile = str_replace("\n", "~", $ediFileStream);
    		$contentOfTheEDIFile = str_replace("\x85", "~", $contentOfTheEDIFile);
    		$allLines = explode("~", $contentOfTheEDIFile);
    	}
    	$dd;
    	//var_dump($allLines);
    	foreach ($allLines as $line) {
    		$fields = explode("*", $line);
    		if ($fields[0] == "GS") {
    			$functionId = $fields[1];
    			break;
    		}
    	}
    	if ($functionId == "PO") {
    		$msgType = "Order";
    	}
    	else if ($functionId == "PC") {
    		$msgType = "OrderChange";
    	}
    	else if ($functionId == "FA") {
    		$msgType = "Acknowledgement";
    	}
    	else if ($functionId == "RS") {
    		$msgType = "OrderStatus";
    	}
    	else if ($functionId == "IB") {
    		$msgType = "Inventory";
    	}else if($functionId == "RA") {
    		$msgType = "Remittance";
    	}else if($functionId == "AG") {
    		$msgType = "AppAdvice";
    	}
    	else if($functionId == "SW") {
    		$msgType = "ShipmentWarehouse";
    	}
    	return $msgType;
    }
    

    public function getMessageType($fileName) {
    	
    	//$this->partner = $this->getPartner();
    	//$partner_id = $this->getPartnerId();
    	$traderId = Config::get('filesystems.trader_id');
    	$partnerId = Config::get('filesystems.partner_id');
    	//$this->partner = $this->getPartner();
    	$traderPartners = TraderPartner::where('partnerId',$partnerId)->where('trader_id',$traderId)->get()->first();
    	$msgType = "";
    	//$tradeInterface = strtoupper($this->partner->tradeInterface());
    	//$tradeInterface = strtoupper($this->partner[0]->getOriginal('tradeInterface'));
    	$tradeInterface = strtoupper($traderPartners->tradeInterface);
    	switch ($tradeInterface) {
    		case "EDI":
    			$msgType = $this->getEdiMessageType($fileName);
    			//$msgType1 = $this->getEdiMessagecustom($msgType,$controller);
    			break;
    		case "API":
    			if(is_array($fileName))
    				$msgType = $fileName['getMessage'];
    				break;
    		case "CSV":
    			$msgType = $this->getCsvMessageType($fileName);
    			break;
    		case "CUSTOM":
    			$msgType = static::getCustomMessageType($fileName);
    			break;
    		default:
    			break;
    	}
    	$path_parts = pathinfo($fileName);
    	if($path_parts['extension'] == "html"){
    		$msgType = $this->getWebMessageType($fileName);
    	}
    
    	return $msgType;
    }
    
    public function setTransactionSetIdentifierCode($transactionSetIdentifierCode) {
    	$this->transactionSetIdentifierCode = $transactionSetIdentifierCode;
    }
    
    public function setTransactionTypeCode($transactionTypeCode) {
    	$this->transactionTypeCode = $transactionTypeCode;
    }
    
    public function getOrder($ediMessage) {
    	
    	$order = new NewOrderController();
    	$itemsArray = array();
    	$order->setPoNumber($ediMessage["BEG"][0][3]);
    	$order->setEdiTransactionType("850");
    	$order->setEdiTrasactionSetControlNumber($ediMessage["ST"][1][2]);
    	$order->setEdiTransactionSetIdentifierCode($order->getTransactionSetIdentifierCode());
    	$addressIndex = 0;
    	//Figure out which of the N1 records is billing
    	//vs. shipping
    	foreach ($ediMessage["N1"] as $addressRecord) {
    			
    		if ($addressRecord[1] == "BT") {
    			$billingAddressIndex = $addressIndex;
    			$addressIndex++;
    		}
    		if ($addressRecord[1] == "ST") {
    			$shippingAddressIndex = $addressIndex;
    			$addressIndex++;
    		}
    	}
    
    	//set addresses
    	$saAddress = new AddressController();
    	$saAddress->setFirstName($ediMessage["N1"][$shippingAddressIndex][2]);
    	$saAddress->setLastName($ediMessage["BEG"][0][3]);
    	//$saAddress->setCompany($ediMessage["N3"][$shippingAddressIndex][1]);
    	$saAddress->setStreet1($ediMessage["N3"][$shippingAddressIndex][1]);
    	$saAddress->setStreet1(isset($ediMessage["N3"][$shippingAddressIndex][2]) ? $ediMessage["N3"][$shippingAddressIndex][2] : '');
    	$saAddress->setPhone($ediMessage["PER"][$shippingAddressIndex][4]);
    	$saAddress->setCity($ediMessage["N4"][$shippingAddressIndex][1]);
    	$saAddress->setStateAbbrev($ediMessage["N4"][$shippingAddressIndex][2]);
    	$saAddress->setZip($ediMessage["N4"][$shippingAddressIndex][3]);
    	$saAddress->setCountry($ediMessage["N4"][$shippingAddressIndex][4]);
    	$order->setSaAddress($saAddress);
    
    	$baAddress = new AddressController();
    	//$baAddress->setFirstName("BBB Dropship");
    	$baAddress->setLastName($ediMessage["BEG"][0][3]);
    	//$saAddress->setCompany($ediMessage["N3"][$shippingAddressIndex][1]);
    	$baAddress->setStreet1($ediMessage["N3"][$billingAddressIndex][1]);
    	$baAddress->setStreet1(isset($ediMessage["N3"][$billingAddressIndex][2]) ? $ediMessage["N3"][$billingAddressIndex][2] : '');
    	$baAddress->setPhone($ediMessage["PER"][$billingAddressIndex][4]);
    	$baAddress->setCity($ediMessage["N4"][$billingAddressIndex][1]);
    	$baAddress->setStateAbbrev($ediMessage["N4"][$billingAddressIndex][2]);
    	$baAddress->setZip($ediMessage["N4"][$billingAddressIndex][3]);
    	$saAddress->setCountry($ediMessage["N4"][$billingAddressIndex][4]);
    	$order->setBaAddress($baAddress);
    
    
    	foreach ($ediMessage["PO1"] as $orderItem) {
    		$item = new ItemController();
    		$item->setLineNum($orderItem[1]);
    		$item->setQty($orderItem[2]);
    		$item->setPrice($orderItem[4]);
    		$item->setSku(trim($orderItem[7]));
    		$itemsArray[] = $item;
    	}
    
    	$order->setItems($itemsArray);
    	$order->setRawOrder($ediMessage["ediMessage"]);
    	//self::setOrderArray($order);
    	$order->setOrderArray($order);
    	$this->setOrderArray($order);
    	return $order;
    }
    
    public function setOrderArray($order) {
    	$this->orderArray[] = $order;
    }
    
    public function getOrderArray() {
    	return $this->orderArray;
    }
    
    public function logOrder($traderId, $partnerId, $PoNumber, $soNumber, $status, $comment=null, $errorDescription=NULL) {
    	
    	$comment = str_replace("'", "''", $comment);
                
        $workflowSql = new RefCounter();
        $workflowId = "";
        if ($status == "RC") {
            $workflow = $workflowSql->getNewWorkFlowId();
            $workflowId = $workflow['counterValue'];
        }
        else {
            $workflowId = $workflowSql->getWorkFlowId($traderId, $partnerId, $PoNumber);
        }
        $dd;
        //ensure workflow id is not null or empty
        if (empty($workflowId)) {
            $workflowId = $workflowSql->getNewWorkFlowId();
        }
        
        if($status == "ER") {
        	/*$sql = "Update edi_status_new set errored = 'Y', errorDescription = '$errorDescription'";
        	$sql .= " where workflowId = '$workflowId' ";
        	$sql .= " order by status_id desc limit 1";*/
        	$ediStatusUpd = EdiStatusNew::find($workflowId)->orderBy('status_id', 'desc');
        	$ediStatusUpd->errored = 'Y';
        	$ediStatusUpd->errorDescription = $errorDescription;
        	$ediStatusUpd->errored = 'Y';
        	$upd = $ediStatusUpd->save();
        } else {
        	/*$sql = "INSERT INTO edi_status_new (workflowId, trader_id, customer_id , customer_po , order_number, status, comment, errorDescription)";
        	$sql .= " VALUES";
        	$sql .= " ('" . $workflowId . "', '". $traderId . "', '" . $partnerId . "', '" . $PoNumber . "',  '" . $soNumber . "' ,'" . $status . "', '" . $comment . "', '" . $errorDescription . "')";*/
        	
        	$ediStatusNew = new EdiStatusNew;
        	
        	$ediStatusNew->workflowId = isset($workflowId) ? $workflowId : 0;
        	$ediStatusNew->trader_id = isset($traderId) ? $traderId : 0;
        	$ediStatusNew->customer_id = isset($partnerId) ? $partnerId : 0;
        	$ediStatusNew->customer_po = isset($PoNumber) ? $PoNumber : 0;
        	$ediStatusNew->order_number = isset($soNumber) ? $soNumber : '';
        	$ediStatusNew->status = isset($status) ? $status : 0;        	
        	$ediStatusNew->comment = isset($comment) ? $comment : 0;
        	$ediStatusNew->errorDescription = isset($errorDescription) ? $errorDescription : 0;
        	$eStatusNew = $ediStatusNew->save();
        	
        	if($eStatusNew && $status == "RC") {
        		//$workflowUpd = new RefCounter($workflow['id']);
        		$workflowUpd = RefCounter::find($workflow['id']);
        		$workflowUpd->counterValue = $workflow['counterValue'] + 1;
        		$upd = $workflowUpd->save();
        		$upd;
        	}
        	
        	if($status == "ER") {
        		/*$sql = "UPDATE newOrder SET workflowId= '$workflowId', soNumber='$soNumber', errored='Y', errorDescription='$errorDescription' where ";
        		$sql .= " traderId='{$traderId}' and partnerId='{$partnerId}' and poNumber = '{$PoNumber}'";*/
        		/*$ediStatusUpd = NewOrder::find($workflowId);
        		$ediStatusUpd->errored = 'Y';
        		$ediStatusUpd->errorDescription = $errorDescription;
        		$ediStatusUpd->errored = 'Y';
        		$upd = $ediStatusUpd->save();*/
        		$up = NewOrder::where(
        				['traderId'=>$traderId,'partnerId'=>$partnerId,'poNumber'=>$PoNumber])->update(
        						['workflowId'=>$workflowId,'soNumber'=>$soNumber,'errored'=>'Y','errorDescription'=>$errorDescription]
        						);
        		 
        	} else {
        		//$sql = "UPDATE newOrder SET workflowId= '$workflowId', soNumber='$soNumber', status='$status', comment='$comment', errored='N', errorDescription='' where ";
        		//$sql .= " traderId='{$traderId}' and partnerId='{$partnerId}' and poNumber = '{$PoNumber}'";
        		$up = NewOrder::where(
        				['traderId'=>$traderId,'partnerId'=>$partnerId,'poNumber'=>$PoNumber])->update(
        						['status'=>$status,'comment'=>$comment,'workflowId'=>$workflowId,'soNumber'=>$soNumber,'errored'=>'N','errorDescription'=>'']
        						);
        	}
        }
    }
    
    public function saveOrder($order) {
    	//$db = $this->db;
    	//$order->setTraderId($order->getTrader()->getId());
    	//$order->setPartnerId($order->getPartner()->getId());
    	return $this->putOrder($order);
    }
    
    public function putOrder($order) {
    	
    	$checkSku = $order->getTrader()->getCheckSku();
    	if($checkSku && !$order->getReRun()){
    		$resultArr = $this->isSkuAvailable($order);
    	}else{
    		$resultArr = $this->setSkuAvailable($order);
    	}
    	$arrProducts = array();
    	
    	if (count($resultArr['inStockItem']) > 0) {
    		$arrProducts = $resultArr['inStockItem'];
    	}
    	
    	if (((integer) $partner->getShipComplete()) && !($resultArr['is_in_stock'])) {
    			
    		if (isset($resultArr['exceptionMessage'])) {//count of out of stock ,,,, count of invalid
    			$resp["errorMessage"] = $resultArr['exceptionMessage'];
    		} else {
    			$resp["errorMessage"] = '';
    			if (isset($resultArr['outOfStockSKU'])) {
    				$resp["errorMessage"] = "Received order for out of stock sku " . implode(',', $resultArr['outOfStockSKU']);
    			}
    			if (isset($resultArr['wrongSKU'])) {
    				$resp["errorMessage"] .= " Received order for incorrect sku " . implode(',', $resultArr['wrongSKU']);
    			}
    		}
    		$resp["isError"] = true;
    		$this->logger->LogWarn("znectDBAdapter:putOrder: " . $resp["errorMessage"]);
    		return $resp;
    	} elseif (!((integer) $partner->getShipComplete())) {
    		if(isset($resultArr['outOfStockSKU'])){
    			$resp['outOfStock'] = $resultArr['outOfStockSKU'];
    		}elseif(isset($resultArr['wrongSKU'])){
    			$resp['wrongSKU'] = $resultArr['wrongSKU'];
    		}
    	}
    	
    	if (count($arrProducts) > 0) {
    		$resp["errorMessage"] = "";
    		$resp['isError'] = false;
    		return $resp;
    		} else {
    			$resp["isError"] = true;
    			$resp["errorMessage"] = "No ordered products in stock";
    			$this->logger->LogWarn("znectDBAdapter:putOrder: " . $resp["errorMessage"]);
    		}
    		return $resp;
    }
    
    public function isSkuAvailable($order) {
    	
    	$passedItems = $order->getItems(); //items are updated in this method
    	foreach ($passedItems as $oi) {
    		$passedSku[] = $oi->getSku($order->getPartner ()->getSkuChangeCase ());
    	}
    	try {
    		$inventory = $this->db->getInventory($order->getTrader()->getId(), 0 /*partner 0*/, $passedSku);
    		foreach($inventory as $p){
    			$pItem['sku'] = strtolower($p->getSku());
    			$pItem['product_id'] = $p->getId();
    			$pItem['qty'] = intval ($p->getQuantity());
    			$pItem['qty'] > 0 ? $pItem['is_in_stock'] = true :$pItem['is_in_stock'] = false;
    			$returnSku[] = $pItem;
    		}
    			
    		$retArr = array(); //
    		$retArr["is_in_stock"] = true; //
    		foreach ($passedItems as $passedItem) {
    			$found = false;
    			foreach ($returnSku as $ak => $returnItem) {
    				$passedItemSku = strtolower($passedItem->getSku());
    				$index = array_search($passedItemSku, $returnItem);
    				if ($index == 'sku') {
    					if (($returnItem['qty']) > 0 &&
    							($returnItem['is_in_stock'] == true) &&
    							intval($returnItem['qty']) >= intval($passedItem->getQty())) {
    								$passedItem->setInStock(true);
    								$passedItem->setHasChanged(true);
    								$order->setHasInStockItems(true);
    								if($order->getPartner()->getCustomPrice() == "yes") {
    									$retArr["inStockItem"][] = array(
    											'product_id' => $returnItem['product_id'],
    											'qty' => intval($passedItem->getQty()),
    											'custom_price' => $passedItem->getPrice()
    									);
    								} else {
    									$retArr["inStockItem"][] = array(
    											'product_id' => $returnItem['product_id'],
    											'qty' => intval($passedItem->getQty())
    									);
    								}
    								$returnItem['qty'] = intval ($returnItem['qty']) - intval($passedItem->getQty());
    							} else {
    								$passedItem->setInStock(false);
    								$passedItem->setType('denySku');
    								$retArr["is_in_stock"] = false;
    								$order->setHasOutofStockItems(true);
    								$passedItem->setHasChanged(true);
    								$retArr["outOfStockSKU"][] = $passedItem->getSku($order->getPartner ()->getSkuChangeCase ());
    							}
    							$found = true;
    				}
    			}
    			if (!$found) {
    				$passedItem->setInStock(false);
    				$order->setHasOutofStockItems(true);
    				$passedItem->setType('denySku');
    				$passedItem->setInvalidSku(true);
    				$passedItem->setHasChanged(true);
    				$retArr["is_in_stock"] = false;
    				$retArr["wrongSKU"][] = $passedItem->getSku();
    			}
    		}
    	} catch (Exception $e) {
    		$retArr["is_in_stock"] = false;
    		$order->setHasOutofStockItems(true);
    		$retArr["exceptionMessage"] = $e->getMessage();
    		$this->logger->LogWarn("znectDBAdapter:isSkuAvailable: ". $retArr["exceptionMessage"]);
    		return false;
    	}
    
    	return $retArr;
    }
}
