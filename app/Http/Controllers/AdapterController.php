<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Config;
use Storage;

class AdapterController extends Controller
{
	public $trader;
	public $partner;
	public function __construct() {
		//$controller = new Controller();
		//$this->trader = $controller->getTrader();
		//$this->partner = $controller->getPartner();
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

    private function getEdiMessageType_1($fileName,$controller = NULL) {
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
    
    public function getMessageData_1($fileName,$controller = NULL) {
    	$path = Config::get('filesystems.path');
    	//$tr = $controller->getTrader();
    	$trader_id = Config::get('filesystems.trader_id');
    	$partner_id = Config::get('filesystems.partner_id');
    	$fileNamePath = $trader_id. DIRECTORY_SEPARATOR .$partner_id. DIRECTORY_SEPARATOR . 'in'. DIRECTORY_SEPARATOR . $fileName;
    	$path = Storage::disk('public')->path($fileNamePath);
    	$fileContents = file_get_contents($path);
    	return $fileContents;
    }
    
	public function parseEDIMessage_1($contentOfTheEDIFile) {
		$tempPartner = new partner;
        $tempPartner = $this->getPartner();
        $this->logger->LogInfo("ediAdapter: parseEdiMessage: edifile contents");
        $this->logger->LogInfo("$contentOfTheEDIFile");
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
					
					if ($foundDuplicate === TRUE) {
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
}
