<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TraderController extends Controller {
    private $id;
    private $name;
    private $erpAdapter;
    private $erpURL;
    private $erpUser;
    private $erpPassword;
    private $emailAddress;
    private $orderEmailAddress;
    private $ediInterchangeID;
    private $ediInterchangeQualifier;
    private $erpDatabaseURL;
    private $erpDatabaseUserName;
    private $erpDatabasePassword;
    private $erpEdiDatabaseName;
    private $partners = array();
    private $address;
    private $defaultWarehouseID;
    private $packingSlipGenTiming;
    private $DUNSNumber;
    private $erpAdapterVersion;
    private $vatCheckRequired;
    private $addressCheckRequired;
    private $AS2Id;
    private $venderIdOrderFile;
  	private $checkSku = false;

  	function __construct() { }

  	

    public function getPartner($partnerId) {
        foreach ($this->partners as $thisPartner) {
            //var_dump($thisPartner);
            if ($thisPartner->getId() != strval($partnerId)) {
                continue;
            } else {
                return $thisPartner;
            }
        }
		
		if($partnerId == 0){
			$zeroPartner = new partner();
			$zeroPartner->setId(0);
			return $zeroPartner;
		}
    }

    public function getPartnerByName($partnerName, $type='buyer') {
    	foreach ($this->partners as $thisPartner) {
    		//var_dump($thisPartner);
    		if (strtoupper($thisPartner->getName()) != strtoupper(($partnerName))) {
    			continue;
    		} elseif(strtoupper($thisPartner->getType()) == strtoupper($type)){
    			return $thisPartner;
    		}
    	}
    	return null;    	
    }
    
    public function getPartnerByEdiInterchangeID($ediInterchangeID, $type='buyer'){
        foreach ($this->partners as $thisPartner) {
    		//var_dump($thisPartner);
    		if (strtoupper($thisPartner->getEdiInterchangeID()) != strtoupper(($ediInterchangeID))) {
    			continue;
    		} elseif(strtoupper($thisPartner->getType()) == strtoupper($type)){
    			return $thisPartner;
    		}
    	}
    	return null;   
        
    }

    
    
    public function set($property, $value) {
    	if (property_exists($this, $property)) {
    		$this->$property = $value;
    	}
        else {
            echo "\n" . $property . " does not exist for set\n";
        }
    }
    
    

   public function initializeFromDB($traderId) {
        //set  db parameters
        $znectConfig = new znect_config();
        $dbParams = $znectConfig->getDBParameters();
        $this->setErpDatabaseURL((string)$dbParams->dbUrl);
        $this->setErpDatabaseUserName((string)$dbParams->dbUser);
        $this->setErpDatabasePassword((string)$dbParams->dbPwd);
        $this->setErpEdiDatabaseName((string)$dbParams->dbName);
        
        $dbSqls = new znectDbSqls();
        //get trader details from db
        $details = $dbSqls->getTraderDetails($traderId);
        foreach ($details as $varName => $value) {
            if (!is_numeric($varName)) {
                $this->set($varName, $value);
            }
        }
        //get trader address from db
        $traderAddress = $dbSqls->getTraderAddress($traderId);
        $address = new address();
        foreach ($traderAddress as $varName => $value) {
            if (!is_numeric($varName)) {
                $address->set($varName, $value);
            }
        }
        $this->setAddress($address);
        
        //get partners for the Trader
        $partnerRecords = $dbSqls->getPartners($traderId);
        foreach ($partnerRecords as $partnerDetails) {
            $tempPartner = new partner();
            $tempPartner->initializePartner($partnerDetails);
            if (strtoupper($tempPartner->getType()) != strtoupper("buyer") && strtoupper($tempPartner->getType()) != strtoupper("seller")) {
                throw new Exception('Unknown partner type: ' . $tempPartner->getType());
            }
            $this->partners[] = $tempPartner;
        }
    }
}
