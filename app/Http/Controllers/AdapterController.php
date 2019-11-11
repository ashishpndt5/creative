<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdapterController extends Controller
{
	public function __construct() {
	
	}
	
    public function getMessageType($fileName) {
        $msgType = "";
        $tradeInterface = strtoupper($this->partner->getTradeInterface());
        switch ($tradeInterface) {
            case "EDI":
                $msgType = $this->getEdiMessageType($fileName);
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

    private function getEdiMessageType($fileName) {
        $msgType = "";
        $functionId = "";
        $ediFileStream = $this->getMessageData($fileName);
		$tPartner = $this->getPartner();
		if(!empty($tPartner)) {
			$fieldSeperator = $this->getpartner()->getFieldSeperator();
			$segmentSeperator = $this->getpartner()->getEdiSegmentSeperator();
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
}
