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

class Controller extends BaseController
{
	public $trader;
	public $partner;
	public $transactionSetIdentifierCode;
	public $transactionTypeCode;
	private $orderArray = array();
	
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function __construct() {
    	
    	//View::share('user', Auth::user());
    	//View::share('social', Social::all());
    }
    
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
    
    public function getMessageData($fileName,$controller = NULL) {
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
    
    public function getEdiMessageType($fileName,$controller = NULL) {
    	$msgType = "";
    	$functionId = "";
    	$ediFileStream = $this->getMessageData($fileName,$controller);
    	$tPartner = $controller->getPartner();
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
    

    public function getMessageType($fileName,$controller) {
    	$this->partner = $controller->getPartner();
    	$msgType = "";
    	//$tradeInterface = strtoupper($this->partner->tradeInterface());
    	$tradeInterface = strtoupper($this->partner[0]->getOriginal('tradeInterface'));
    	switch ($tradeInterface) {
    		case "EDI":
    			$msgType = $this->getEdiMessageType($fileName,$controller);
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
    
}
