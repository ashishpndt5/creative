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
use App\Trader;
use App\TraderPartner;
use App\RefCounter;
use App\EdiStatusNew;
use App\NewOrder;
use App\OrderInfo;
use App\Address;
use App\Items;
use App\Inventory;
use App\Http\Controller\AdapterController;
use DB;
use Illuminate\Support\Facades\Schema;
use Log;

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
    
    public function __construct() {
    	
    	//View::share('user', Auth::user());
    	//View::share('social', Social::all());
   }
    
   /* public function storage($trader = null, $partner = null) {
    	
    	$this->trader = $trader;
    	$this->partner = $partner;
    }
    
    public function setTrader($trader) {
    	 
    	return $this->trader = $trader;
    }
    
    public function getTrader() {
    	
    	return $this->trader;
    }
    
    public function setPartner($partner) {
    
    	$this->partnerId = $partner;
    }
    
    public function getPartner() {
    
    	return $this->partner;
    }
    
    public function getPartnerId() {
    	 
    	return $this->partnerId;
    }
    
    public function setPartnerId($partner_id) {
    
    	$this->partnerId = $partner_id;
    }
    
    public function setTraderId($trader_id) {
    
    	$this->traderId = $trader_id;
    }
    
    public function getTraderId($trader_id) {
    
    	return $this->traderId;
    }    
    */   

    
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
    
    public function parseEDIMessage_old($contentOfTheEDIFile) {
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
    
    public function getEdiMessageType_11($fileName) {
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
    

    public function getMessageType_00($fileName) {
    	
    	//$this->partner = $this->getPartner();
    	//$partner_id = $this->getPartnerId();
    	$tempPartner = $this->getPartner();
    	$t = $tempPartner['id'];
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
            $workflow = $this->getNewWorkFlowId();
            $workflowId = $workflow->counterValue;
        }
        else {
            $workflowId = $this->getWorkFlowId($traderId, $partnerId, $PoNumber);
        }
        $dd;
        //ensure workflow id is not null or empty
        if (empty($workflowId)) {
            $workflowId = $this->getNewWorkFlowId();
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
        		$workflowUpd = RefCounter::find($workflow->id);
        		$workflowUpd->counterValue = $workflow->counterValue + 1;
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
        		if (NewOrder::where('poNumber', '=', $PoNumber)->exists() && $soNumber) {
	        		$up = NewOrder::where(
	        				['traderId'=>$traderId,'partnerId'=>$partnerId,'poNumber'=>$PoNumber])->update(
	        						['status'=>$status,'comment'=>$comment,'workflowId'=>$workflowId,'soNumber'=>$soNumber,'errored'=>'N','errorDescription'=>'']
	        						);
	        	} else {
	        		$PoNumber;
	        	}
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
		//$this->getDbConnection ();
		//$logger = $this->getLogger();
		$poNumber = $order->getPoNumber();
		//$traderId = $order->getTrader()['id'];
		//$partnerId = $order->getPartner()['id'];
		$traderId = $order->getTrader()->getId();
		$partnerId = $order->getPartner()->getPartnerId();
		
		$errKVParams['traderId'] = $traderId;
		$errKVParams['partnerId'] = $partnerId;
		$errKVParams['poNumber'] = $poNumber;
		
		
		try {
			$status = $this->orderIsEligibleToProcess($traderId, $partnerId, $poNumber);
			if(!$status){
				return false;
			}
			$addlFields = array();
			//$addlFields['traderId'] = $traderId;
			//$addlFields['partnerId'] = $partnerId;
			
			$result = $this->serializeTableObjects ( 'newOrder', $order, $addlFields);
			//$result = $this->serializeObjects ( 'newOrder', array($order), $addlFields);
			//$result = false;
			if (!$result) {
				Log::warning("newOrder table insert failed for Trader :". $traderId .' Partner Id: '.$partnerId);
				//throw new Exception("newOrder table insert failed");
				return false;
			}
			$newOrderId = $result; //mysql_insert_id ();
			$order->setId($newOrderId);
			
			$soNumber = $order->getSoNumber();
			if(empty($soNumber)){
				$soNumber = strval($newOrderId);
				//if(sizeof($soNumber) < 9){
				if(strlen($soNumber) < 9) {
					$soNumber = str_pad($newOrderId, 8, "0", STR_PAD_LEFT);
					$soNumber = '1' . $soNumber;
				}
				$order->setSoNumber($soNumber);
			}
			
			/*$sqlOrderInfo = "INSERT INTO `orderInfo` (`traderId` , `partnerId` , `newOrderId` , `poNumber` , `status` , `createdDate`) VALUES ('" . $traderId . "','" . $partnerId . "','" . $newOrderId . "','" . $order->getPoNumber () . "','RC','" . date ( 'd-m-Y' ) . "')";
			$result = $this->execQueryHelper ( $sqlOrderInfo );
			if(!$result){
				throw new Exception("orderInfo table insert failed");
			}*/
			
			$orderInfo = new OrderInfo();
			//$orderInfo->workflowId = ($order->getWorkFlowId) ? $order->getWorkFlowId : 0;
			$orderInfo->traderId = $traderId;
			$orderInfo->partnerId = $partnerId;
			$orderInfo->newOrderId = $newOrderId;
			$orderInfo->poNumber = ($poNumber) ? $poNumber : 0;
			$orderInfo->soNumber = ($soNumber) ? $soNumber : '';
			$orderInfo->fileName = '';
			$orderInfo->status = ($order->getStatus()) ? $order->getStatus() : '';
			$orderInfo->ErrorMassage = '';
			$orderInfo->exception = '';
			$orderInfo->enteredInERP = '';
			//$orderInfo->createdDate = date('d-m-Y');
			$result = $orderInfo->save();
						
			/*$qCommnets = $order->getOrderComments ();
			$orderComment = new OrderComment();
			
			$sqlOrderComments = "INSERT INTO `orderComments` ( `newOrderId`, `comments` ) VALUES ( '" . $newOrderId . "' ,'" . $qCommnets . "' )";
			
			$result = $this->execQueryHelper ( $sqlOrderComments );*/
			if(!$result){
				Log::warning("OrderInfo table insert failed for Trader :". $traderId .' Partner Id: '.$partnerId);
				//throw new Exception("OrderInfo table insert failed");
			}
			
			
			$addlFields = array();
			$addlFields['type'] = 'shipping';
		//	$order->getSaAddress()->setNewOrderId($newOrderId);
			//$result = $this->serializeObjects ( 'address', array($order->getSaAddress()), $addlFields);
			
			$address = new Address();
			$address->newOrderId = $newOrderId;
			$address->type = 'shipping';
			$address->firstName = ($order->getSaAddress()->getFirstName()) ? ($order->getSaAddress()->getFirstName()) : '';
			$address->lastName = ($order->getSaAddress()->getLastName()) ? ($order->getSaAddress()->getLastName()) : '';
			$address->company = ($order->getSaAddress()->getCompany()) ? ($order->getSaAddress()->getCompany()) : '';
			$address->phone = ($order->getSaAddress()->getPhone()) ? ($order->getSaAddress()->getPhone()) : '';
			$address->street1 = ($order->getSaAddress()->getStreet1()) ? ($order->getSaAddress()->getStreet1()) : '';
			$address->street2 = ($order->getSaAddress()->getStreet2()) ? ($order->getSaAddress()->getStreet2()) : '';
			$address->city = ($order->getSaAddress()->getCity()) ? ($order->getSaAddress()->getCity()) : '';
			$address->stateAbbrev = ($order->getSaAddress()->getStateAbbrev()) ? ($order->getSaAddress()->getStateAbbrev()) : '';
			$address->zip = ($order->getSaAddress()->getZip()) ? ($order->getSaAddress()->getZip()) : 0;
			$address->country = ($order->getSaAddress()->getCountry()) ? ($order->getSaAddress()->getCountry()) : '';
			$address->dcNumber = '';
			$result = $address->save();
			$order->getSaAddress()->setNewOrderId($newOrderId);
			
			if(!$result){
				Log::warning("shipping address table insert failed for Trader :". $traderId .' Partner Id: '.$partnerId);
				//throw new Exception("shipping address table insert failed");
			}
	
			$addlFields['type'] = 'billing';
			$order->getBaAddress()->setNewOrderId($newOrderId);
			//$result = $this->serializeObjects ( 'address', array($order->getBaAddress()), $addlFields);
			$address = new Address();
			$address->newOrderId = $newOrderId;
			$address->type = 'billing';
			$address->firstName = ($order->getBaAddress()->getFirstName()) ? ($order->getBaAddress()->getFirstName()) : '';
			$address->lastName = ($order->getBaAddress()->getLastName()) ? ($order->getBaAddress()->getLastName()) : '';
			$address->company = ($order->getBaAddress()->getCompany()) ? ($order->getBaAddress()->getCompany()) : '';
			$address->phone = ($order->getBaAddress()->getPhone()) ? ($order->getBaAddress()->getPhone()) : '';
			$address->street1 = ($order->getBaAddress()->getStreet1()) ? ($order->getBaAddress()->getStreet1()) : ''; 
			$address->street2 = ($order->getBaAddress()->getStreet2()) ? ($order->getBaAddress()->getStreet2()) : '' ;
			$address->city = ($order->getBaAddress()->getCity()) ? ($order->getBaAddress()->getCity()) : '';
			$address->stateAbbrev = ($order->getBaAddress()->getStateAbbrev()) ? ($order->getBaAddress()->getStateAbbrev()) : '';
			$address->zip = ($order->getBaAddress()->getZip()) ? ($order->getBaAddress()->getZip()) : '';
			$address->country = ($order->getBaAddress()->getCountry()) ? ($order->getBaAddress()->getCountry()) : '';
			$address->dcNumber = '';
			$result = $address->save();
			if(!$result){
				Log::warning("billing address table insert failed for Trader :". $traderId .' Partner Id: '.$partnerId);
				//throw new Exception("billing address table insert failed");
			}
			//$dd;
			unset($addlFields['type']);
			foreach ( $order->getItems () as $item ) {
				$itype = $item->getType(); 
				if(empty($itype)){
					$item->setType('sku');
					$type = 'sku';
				}
				
				$item->setNewOrderId($newOrderId);
				//$result = $this->serializeObjects ( 'item', array($item), $addlFields);
				$items = new Items();
				$items->newOrderId = $newOrderId;
				$items->type = $type;
				$items->sku = ($item->getSku()) ? ($item->getSku()) : '';
				$items->partnerSku = ($item->getPartnerSku()) ? ($item->getPartnerSku()) : '';
				$items->price = ($item->getPrice()) ? ($item->getPrice()) : '';
				$items->total = ($item->getTotal()) ? ($item->getTotal()) : '';
				$items->qty = ($item->getQty()) ? ($item->getQty()) : '';
				$items->description = ($item->getDescription()) ? ($item->getDescription()) : '';
				$items->lineNum = ($item->getLineNum()) ? ($item->getLineNum()) : '';
				$items->sdq = ($item->getSdq()) ? ($item->getSdq()) : '';
				$items->inStock = ($item->getInStock()) ? ($item->getInStock()) : '';
				$items->invalidSku = ($item->getInvalidSku()) ? ($item->getInvalidSku()) : '';
				$items->manufacturer = ($item->getManufacturer()) ? ($item->getManufacturer()) : '';
				$items->weight = ($item->getWeight()) ? ($item->getWeight()) : '';
				$items->msg = ($item->getMsg()) ? ($item->getMsg()) : '';
				
				$result = $items->save();
				
				if(!$result) {
					$errKVParams['sku'] = $item->getSku();
					Log::warning("item table insert failed for Trader :". $traderId .' Partner Id: '.$partnerId. ' order id:'. $newOrderId);
					//throw new Exception("item table insert failed");
				}
				//$itemId = mysql_insert_id ();
				//$item->setId($itemId);
				/*foreach ( $item->getItemWarehouses() as $iw ) {
					$iw->setItemId($itemId);
					//$result = $this->serializeObjects ( 'itemWarehouse', array($iw));
					
					if(!$result){
						$errKVParams['sku'] =  $item->getSku();
						$errKVParams['itemId'] =  $itemId;
						throw new Exception("itemWarehouse table insert failed");
					}
				}*/
			}
			$rejectedItems = $order->getRejectedItems ();
			if (!empty($rejectedItems)) {
				$addlFields['type'] = 'denySku';
				//$result = $this->serializeObjects ('item', $rejectedItems, $addlFields);
				$items = new Items();
				$items->newOrderId = $newOrderId;
				$items->sku = ($item->getSku()) ? ($item->getSku()) : '';
				$items->partnerSku = ($item->getPartnerSku()) ? ($item->getPartnerSku()) : '';
				$items->price = ($item->getPrice()) ? ($item->getPrice()) : '';
				$items->total = ($item->getTotal()) ? ($item->getTotal()) : '';
				$items->qty = ($item->getQty()) ? ($item->getQty()) : '';
				$items->description = ($item->getDescription()) ? ($item->getDescription()) : '';
				$items->lineNum = ($item->getLineNum()) ? ($item->getLineNum()) : '';
				$items->sdq = ($item->getSdq()) ? ($item->getSdq()) : '';
				$items->inStock = ($item->getInStock()) ? ($item->getInStock()) : '';
				$items->invalidSku = ($item->getInvalidSku()) ? ($item->getInvalidSku()) : '';
				$items->manufacturer = ($item->getManufacturer()) ? ($item->getManufacturer()) : '';
				$items->weight = ($item->getWeight()) ? ($item->getWeight()) : '';
				$items->msg = ($item->getMsg()) ? ($item->getMsg()) : '';
				
				$result = $items->save();
				
				if(!$result){
					Log::warning("rejected item table insert failed for Trader :". $traderId .' Partner Id: '.$partnerId. ' order id:'. $newOrderId);
					//throw new Exception("rejected item table insert failed");
				}
			}
		} catch (Exception $e) {
			$errKVParams['Exception'] = $e->getMessage();
			//$this->loggerHelper("znectDbSqls:putOrder", "failed. Exception caught.", $errKVParams);
			Log::warning("Controller:putOrder", "failed. Exception caught.", $errKVParams);
			return false;
		}
		return true;
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
    		//$this->logger->LogWarn("znectDBAdapter:isSkuAvailable: ". $retArr["exceptionMessage"]);
    		Log::warning("Controller:isSkuAvailable: ". $retArr["exceptionMessage"]);
    		return false;
    	}
    
    	return $retArr;
    }
    
    public function orderIsEligibleToProcess($traderId, $partnerId, $poNumber) {
    	/*$this->getDbConnection ();
    
    	$sql = "SELECT * FROM orderInfo WHERE ";
    	$sql .="traderId='" . $traderId . "' AND  partnerId = '" . $partnerId . "' AND poNumber ='" . $poNumber . "'";
    	
    	$up = OrderInfo::where(
    			['traderId'=>$traderId,'partnerId'=>$partnerId,'poNumber'=>$poNumber])->update(
    					['status'=>$status,'comment'=>$comment,'workflowId'=>$workflowId,'soNumber'=>$soNumber,'errored'=>'N','errorDescription'=>'']
    					);
    					*/
    	$result = OrderInfo::where(['partnerId'=>$partnerId,'traderId'=>$traderId,'poNumber'=>$poNumber])->get()->first();
    	//$rs = mysql_query($sql);
    	//$result = mysql_fetch_assoc($rs);
    	//$dd;
    	if ($result) {
    		if ($result['status'] == 'RR' || $result['status'] == 'RC')  {
    			return true;
    		} else {
    			return false;
    		}
    	} else {
    		return true;
    	}
    }
    
    public function serializeTableObjects($table, $object, $addData) {
    	
    	$newOrder = new NewOrder();
    	$sono = $object->getSoNumber();
    	$newOrder->traderId = ($object->getTrader()->getId()) ? $object->getTrader()->getId() : 0;
    	//$d = $object['trader_id'];
    	$newOrder->partnerId = ($object->getPartner()->getPartnerId()) ? $object->getPartner()->getPartnerId() : 0;
    						   
    	$newOrder->poNumber = $object->getPoNumber();
    	$newOrder->workflowId = 1;
    	$newOrder->soNumber = ($object->getSoNumber()) ? ($object->getSoNumber()) : '';
    	$newOrder->status = ($object->getStatus()) ? $object->getStatus() : '';
    	$newOrder->comment = '';
    	$newOrder->genCartonLabel = ($object->getGenCartonLabel()) ? $object->getGenCartonLabel() : 0;
    	$newOrder->error_number = 0;
    	$newOrder->sent_to_seller = 'N';
    	$newOrder->invoiceNumber = '';
    	$newOrder->errorDescription = '';
    	$newOrder->packingSlipFileName = ($object->getPackingSlipFileName()) ? $object->getPackingSlipFileName() : '';
    	$newOrder->shippingLabelFileName = ($object->getShippingLabelFileName()) ? $object->getShippingLabelFileName() : '';
    	$newOrder->shipmentPriority = ($object->getShipmentPriority()) ? $object->getShipmentPriority() : '';
    	$newOrder->shipmentCarrier = ($object->getShipmentCarrier()) ? $object->getShipmentCarrier() : '';
    	$newOrder->sellerPartner = 1;
    	$newOrder->poDate = ($object->getPoDate()) ? $object->getPoDate() : '';
    	$newOrder->shipDate = ($object->getShipDate()) ? $object->getShipDate() : '';
    	$newOrder->ediTrasactionSetControlNumber = ($object->getEdiTrasactionSetControlNumber()) ? $object->getEdiTrasactionSetControlNumber() : '';
    	$newOrder->ediTransactionSetIdentifierCode = ($object->getEdiTransactionSetIdentifierCode()) ? $object->getEdiTransactionSetIdentifierCode() : '';
    	$newOrder->ediTransactionType = ($object->getEdiTransactionType()) ? $object->getEdiTransactionType() : '';
    	$newOrder->generateShippingLabel = ($object->getGenerateShippingLabel()) ? $object->getGenerateShippingLabel() : '';   	
    	$newOrder->rawOrder = ($object->getRawOrder()) ? $object->getRawOrder() : '';
    	$newOrder->hasOutofStockItems = ''; //($object->getRawOrder()) ? $object->getRawOrder() : '';
    	$newOrder->shippingLabel = ($object->getShippingLabel()) ? $object->getShippingLabel() : '';
    	$newOrder->packingSlip = ($object->getPackingSlip()) ? $object->getPackingSlip() : '';
    	$newOrder->shipByDate = ($object->getShipDate()) ? $object->getShipDate() : '';
    	$newOrderSave = $newOrder->save();
    	$insertedId = $newOrder->id;
    	return $insertedId;
    }
    
    public function updateOrderInfoStatus($tableName, $keys, $updateArray) {
    	//$datetime = Carbon::now();
    	
    	$result = DB::table($tableName)->where([[$keys]])->update($updateArray);
    	return $result;
    }
    
    public function getInventory($traderId, $partnerId, $skus = null) {
    	
    	$errKVParams['traderId'] = $traderId;
    	$errKVParams['partnerId'] = $partnerId;
    
    	//$sql = "select * from znectInventory where traderId = '{$traderId}' and partnerId = '{$partnerId}'"; //inventory by traderId
    	$data = Inventory::where(['partnerId'=>$partnerId,'traderId'=>$traderId]);
    	
    	if(isset($skus)) {  		
    		$data->whereIn('sku', $skus);
    	};
    	
    	$result = $data->first()->getOriginal();
    	//$result;
    	
    	if ($result === false) {
    		//$this->loggerHelper("znectDbSqls:getInventory", "get inventory query failed", $errKVParams);
    		//throw new Exception("znectDbSqls:getInventory failed");
    		Log::warning("Controller:getInventory failed for sku : ". $skus. ' Trader id :'. $traderId. ' Partner id:  '.$partnerId);
    	}
    	if(empty($result)) {
    		return $result;
    	}
    	return $result;
    	//return $this->hydrateObjects( "inventories", Inventory, $result);
    }
    
    public function hydrateObjects($class,$classObj, $records) {
    	//fetch column names
    	$tableName = $class;
    	//$sql = "SHOW COLUMNS FROM {$tableName}";
    	$data = $this->getTableColumns($tableName);
    	
    	//$data = $this->execQueryandFetchRowsHelper($sql);
    	if(empty($data)) {
    		Log::warning("Controller:hydrateObjects failed. Error: ". $class);
    		//throw new Exception("hydrateObjects failed. Error: " . mysql_error());
    	}
    
    	$zObjects = array();
    	foreach($records as $rec) {
    		$zObj = new $classObj($this->getLogger());
    		foreach ($data as $colName) {
    			$method = 'set'.ucfirst($colName['Field']);
    			if(method_exists($classObj, $method)){
    				$zObj->{$method}($rec[$colName['Field']]);
    			}
    		}
    		$zObjects[] = $zObj;
    	}
    	return $zObjects;
    }
    
    public function getTableColumns($table)
    {
    	//return DB::getSchemaBuilder()->getColumnListing($table);
    
    	// OR
    
    	return Schema::getColumnListing($table);
    
    }
    
    public function getTraderDetails($trader_id = null) {
    	//$trader_id;
    	if(isset($trader_id)) {
    		$traderDetails = Trader::where('id',$trader_id)->first()->getOriginal();
    		return $traderDetails;
    	} else {
    		return null;
    	}
    }
    
    public function getTraderPartners($trader_id) {
    	$trader_id;
    	if(isset($trader_id)) {
    		//$partnerList = TraderPartner::where('traderId',$trader_id)->all();
    		$partnerLists = DB::table('trader_partners')->where('traderId', '=', $trader_id)->get()->toArray();
    		return $partnerLists;
    	} else {
    		return null;
    	}
    }
    
    public function updateObject($zObj, $errKVParams=null) {
    	
    	//$this->getDbConnection ();
    	//$logger = $this->getLogger();
    
    	if(!is_object($zObj)){
    		return;
    	}
    	if(!method_exists($zObj, "getHasChanged")){//doesn't require to be persisted into the database
    		return;
    	}
    
    	$reflect = new ReflectionClass($zObj);
    	$props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
    	$parents = array();
    	while ($parent = $reflect->getParentClass()) {
    		$p = $parent->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
    		$props = array_merge($props, $p);
    		$reflect = $parent;
    	}
    
    	foreach($props as $prop){
    		$method = 'get'.ucfirst($prop->getName());
    
    		if(method_exists($zObj, $method)){
    			$zProp = $zObj->{$method}();
    		}else{// no getter assume not a property to be worried about!
    			continue;
    		}
    		if(is_array($zProp)){
    			foreach($zProp as $zChildObj){
    				$this->updateObject($zChildObj);
    			}
    		}else{
    			if(!is_object($zProp)){
    				continue;
    			}
    			if(!method_exists($zProp, "getHasChanged")){//doesn't require to be persisted into the database
    				continue;
    			}
    			$this->updateObject($zProp);
    		}
    	}
    
    	if($zObj->getHasChanged()){
    		try {
    			$result = $this->serializeObjects(get_class($zObj), array($zObj));
    			if(!$result) {
    				//$this->loggerHelper("znectDbSqls:updateOrder", "{$class} table insert failed", $errKVParams);
    				Log::warning("Controller : updateOrder ". $class. ' table insert failed');
    				return false;
    			}
    		} catch (Exception $e) {
    			$errKVParams['Error'] = $e->getMessage();
    			Log::warning("Controller : updateOrder ". $class. ' table insert failed. '. $errKVParams);
    			//$this->loggerHelper("znectDbSqls:updateObject", "{$class} table insert failed", $errKVParams);
    			return false;
    		}
    	}
    }
    
    public function getNewWorkFlowId() {
    	try {
    		$workFlowId = '';
    		$refCounters = DB::table('ref_counters')->where('counterName', '=', 'workflowId')->get()->toArray();
    		/*$sql = "SELECT counterValue FROM znectRefCounter WHERE counterName = 'workflowId'";
    		$this->getDbConnection();
    		$result = mysql_query($sql) or die(mysql_error());
    		$row = mysql_fetch_array($result);*/
    		$workFlowId = $refCounters[0]->counterValue;
    		if($workFlowId) {
    			//update the counter value by 1
	    		//$sql = "UPDATE znectRefCounter SET counterValue = counterValue+1 WHERE counterName = 'workflowId'";
	    		//$result = mysql_query($sql) or die(mysql_error());
    			$up = DB::table('ref_counters')->where(
    					['counterName'=>'workflowId'])->update(['counterValue'=>$workFlowId+1]);
    		}
    		return $refCounters[0];
    	}  catch (Exception $ex) {
    		$errorMsg = "Controller class getNewWorkFlowId() - Errror while getting a new workflow Id";
    		echo "\n " . $errorMsg . "\n" . $ex->getTraceAsString();
    		//throw new Exception($errorMsg, "43", $ex);
    		//$this->logger->LogError($errorMsg . "\n" . $ex->getTraceAsString());
    		Log::warning($errorMsg . "\n" . $ex->getTraceAsString());
    		return false;
    	}
    }
    
	public function getWorkFlowId($traderId, $partnerId, $poNumber) {
        try {
        	//$workflowId = DB::table('edi_status_news')->where(['trader_id', '=', $traderId,'customer_id', '=',$partnerId,'customer_po', '=',$poNumber])->pluck('workFlowId');
        	//$workflowId = EdiStatusNew::where(['partnerId'=>$partnerId,'traderId'=>$traderId,'poNumber'=>$poNumber])->get()->pluck('workFlowId');
        	$workflowId = EdiStatusNew::where(['customer_id'=>$partnerId,'trader_id'=>$traderId,'customer_po'=>$poNumber])->first()->workflowId;
        /*$sql = "SELECT workflowId FROM edi_status_new WHERE trader_id = '" . $traderId . "'" .
                "AND customer_id = '" . $partnerId . "' AND customer_po = '" . $poNumber . "' AND status = 'RC'";
        $this->getDbConnection();
        $result = mysql_query($sql) or die(mysql_error());
        $row = mysql_fetch_array($result);*/
        return $workflowId;
       // return $row['workflowId'];
        }  catch (Exception $ex) {
            $errorMsg = "znectDbSqls.php getWorkFlowId() - Errror " .
                    "while getting workflow Id for p.o number # " . $poNumber;
            echo "\n " . $errorMsg . "\n" . $ex->getTraceAsString();
            //throw new Exception($errorMsg, "43", $ex);
           // $this->logger->LogError($errorMsg . "\n" . $ex->getTraceAsString());
            Log::warning($errorMsg . "\n" . $ex->getTraceAsString());
            return false;
        } 
    }
    
    public function deleteCancelledOrder($traderId, $partnerId, $poNumber){
    	$errKVParams['traderId'] = $traderId;
    	$errKVParams['partnerId'] = $partnerId;
    	$errKVParams['$poNumber'] = $poNumber;
    	 
    	if(empty($traderId) || empty($partnerId) || empty($poNumber)) {
    		return false;
    	}
    	//$sql = "delete from newOrder where traderId = '{$traderId}' and partnerId= '$partnerId' and poNumber = '$poNumber'";
    	$result = DB::table('new_orders')->where(['traderId' => $traderId, 'partnerId' => $partnerId, 'poNumber' => $poNumber])->delete();
    	//$result = $this->execQueryHelper($sql);
    	if ($result=== false) {
    		//$this->loggerHelper("znectDbSqls:deleteCancelledOrder", " failed", $errKVParams);
    		Log::warning("Controller:deleteCancelledOrder", " failed", $errKVParams);
    		return false;
    	}
    	return $result;
    }
    
}
