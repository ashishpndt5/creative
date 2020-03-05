<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MagentoOrderController;
use App\Http\Controllers\NewOrderController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ItemController;
use Config;
use Storage;

class AdapterController extends Controller
{
	public $trader;
	public $partner;
	public $partnerId;
	public $traderId;
	
	public function __construct($traderId = null, $partnerId = null) {
		//$controller = new Controller();
		//$this->trader = $controller->getTrader();
		//$this->partner = $controller->getPartner();
		$this->setTraderId($traderId);
		$this->setPartnerId($partnerId);
	}
	
	public function setTrader($trader) {
	
		return $this->trader = $trader;
	}
	
	public function getTrader() {
		 
		return $this->trader;
	}
	
	public function setPartner($partner) {
	
		$this->partner = $partner;
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
	
	public function getTraderId() {
	
		return $this->traderId;
	}
	
    public function getMessageType($fileName, $partner) {
    	
        $msgType = "";
        $tradeInterface = strtoupper($partner->tradeInterface);
        switch ($tradeInterface) {
            case "EDI":
                $msgType = $this->getEdiMessageType($fileName, '');
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

    public function getEdiMessageType($fileName, $tPartner = null) {
    	
        $msgType = "";
        $functionId = "";
        $ediFileStream = $this->getMessageData($fileName);
        //$tPartner = $this->getPartner();
		//$tPartner = $this->getPartner();
		
		if(!empty($tPartner)) {
			$fieldSeperator = strtoupper($tPartner['fieldSeperator']);
			//$fieldSeperator = $this->getpartner()->getFieldSeperator();
			$segmentSeperator = $tPartner['ediSegmentSeperator'];
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
    
    public function getMessageData($fileName) {
    	
    	$path = Config::get('filesystems.path');
    	//$tr = $controller->getTrader();
    	$partner_id = $this->getPartnerId();
    	$trader_id = $this->getTraderId();
    	//$trader_id = Config::get('filesystems.trader_id');
    	//$partner_id = Config::get('filesystems.partner_id');
    	$fileNamePath = $trader_id. DIRECTORY_SEPARATOR .$partner_id. DIRECTORY_SEPARATOR . 'in'. DIRECTORY_SEPARATOR . $fileName;
    	$path = Storage::disk('public')->path($fileNamePath);
    	$fileContents = file_get_contents($path);
    	return $fileContents;
    }
    
	public function parseEDIMessage($contentOfTheEDIFile) {
		//$tempPartner = new partner;
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
				
                $segmentCounter = array();
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
                    $segmentCounter;
					//if ($foundDuplicate === TRUE && isset($segmentCounter[$field])) {
                    if ($foundDuplicate === TRUE) {
                    	if(!isset($segmentCounter[$field])) {
                    		$segmentCounter[$field] = 0;
                    	}
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
		$baAddress->setFirstName($ediMessage["N1"][$billingAddressIndex][2]);
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
		$order->setOrderArray($order);
		$order->setPoDate($ediMessage["BEG"][0][4]);
		return $order;
	}
	
}
