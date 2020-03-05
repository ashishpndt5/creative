<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\PartnerController;

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
	
  	public function getId() {
  		return $this->id;
  	}
  	 
  	public function setId($id) {
  		$this->id = $id;
  	}
  	
  	public function getCheckSku() {
  	  return $this->checkSku;
  	}
  	
  	public function setCheckSku($value) {
  	  $this->checkSku = $value;
  	}  
        
    public function getAS2Id() {
      return $this->AS2Id;
    }
    
    public function setAS2Id($value) {
      $this->AS2Id = $value;
    }

    function __construct() {
        
    }
    
    public function getVatCheckRequired() {
    	return $this->vatCheckRequired;
    }
    
    public function setVatCheckRequired($vatCheckRequired) {
    	$this->vatCheckRequired = $vatCheckRequired;
    }
    
    
    public function getAddressCheckRequired() {
    	return $this->addressCheckRequired;
    }
    
    public function setaddressCheckRequired($addressCheckRequired) {
    	$this->addressCheckRequired = $addressCheckRequired;
    }
    
    
    public function getErpAdapterVersion() {
    	return $this->erpAdapterVersion;
    }
    
    public function setErpAdapterVersion($erpAdapterVersion) {
    	$this->erpAdapterVersion = $erpAdapterVersion;
    }
  	

    public function getPartner($partnerId) {
        foreach ($this->partners as $thisPartner) {
            //var_dump($thisPartner);
            //if ($thisPartner->getId() != strval($partnerId)) {
        	if ($thisPartner->getPartnerId() != strval($partnerId)) {
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
        $partnerRecords = $dbSqls->getTraderPartners($traderId);
        foreach ($partnerRecords as $partnerDetails) {
            $tempPartner = new PartnerController();
            $tempPartner->initializePartner($partnerDetails);
            if (strtoupper($tempPartner->getType()) != strtoupper("buyer") && strtoupper($tempPartner->getType()) != strtoupper("seller")) {
                throw new Exception('Unknown partner type: ' . $tempPartner->getType());
            }
            $this->partners[] = $tempPartner;
        }
    }
    
    public function initializeTraderPartner($traderId) {
    	
    	$details = $this->getTraderDetails($traderId);
    	foreach ($details as $varName => $value) {
    		if (!is_numeric($varName)) {
    			$this->set($varName, $value);
    		}
    	}
    	
    	/*
    	 * //get trader address from db
	        $traderAddress = $dbSqls->getTraderAddress($traderId);
	        $address = new address();
	        foreach ($traderAddress as $varName => $value) {
	            if (!is_numeric($varName)) {
	                $address->set($varName, $value);
	            }
	        }
	        $this->setAddress($address);
    	 */
    	
    	//get partners for the Trader
    	$partnerRecords = $this->getTraderPartners($traderId);
    	foreach ($partnerRecords as $partnerDetails) {
    		$tempPartner = new PartnerController();
    		$tempPartner->initializePartner($partnerDetails);
    		if (strtoupper($tempPartner->getType()) != strtoupper("buyer") && strtoupper($tempPartner->getType()) != strtoupper("seller")) {
    			//throw new Exception('Unknown partner type: ' . $tempPartner->getType());
    		}
    		$this->partners[] = $tempPartner;
    	}
    	
    }
    public function getErpDatabaseURL() {
    	return $this->erpDatabaseURL;
    }
    
    public function setErpDatabaseURL($erpDatabaseURL) {
    	$this->erpDatabaseURL = $erpDatabaseURL;
    }
    
    public function getErpDatabaseUserName() {
    	return $this->erpDatabaseUserName;
    }
    
    public function setErpDatabaseUserName($erpDatabaseUserName) {
    	$this->erpDatabaseUserName = $erpDatabaseUserName;
    }
    
    public function getErpDatabasePassword() {
    	return $this->erpDatabasePassword;
    }
    
    public function setErpDatabasePassword($erpDatabasePassword) {
    	$this->erpDatabasePassword = $erpDatabasePassword;
    }
    
    public function getErpEdiDatabaseName() {
    	return $this->erpEdiDatabaseName;
    }
    
    public function setErpEdiDatabaseName($erpEdiDatabaseName) {
    	$this->erpEdiDatabaseName = $erpEdiDatabaseName;
    }
    
    public function setPartners($partners) {
    	$this->partners [] = $partners;
    }
    
    public function getPartners()   {
    	return $this->partners;
    }
    
    public function setOrderEmailAddress($orderEmailAddress)    {
    	$this->orderEmailAddress = $orderEmailAddress;
    }
    
    public function getOrderEmailAddress()  {
    	return $this->orderEmailAddress;
    }
    
    public function get($property) {
    	if (property_exists($this, $property)) {
    		return $this->$property;
    	}
    	else {
    		echo "\n" . $property . " does not exist for get \n";
    	}
    }
    
    public function getDUNSNumber() {
    	return $this->DUNSNumber;
    }
    
    public function setDUNSNumber($dunsNumber) {
    	$this->DUNSNumber = $dunsNumber;
    }
    
  
    
    public function getVenderIdOrderFile() {
    	return $this->venderIdOrderFile;
    }
    
    public function setVenderIdOrderFile($value) {
    	$this->venderIdOrderFile = $value;
    }
    
    public function getAddress() {
    	return $this->address;
    }
    
    public function setAddress($address) {
    	$this->address = $address;
    }
    
    public function getDefaultWarehouseID() {
    	return $this->defaultWarehouseID;
    }
    public function setDefaultWarehouseID($defaultWarehouseID) {
    	$this->defaultWarehouseID = $defaultWarehouseID;
    }
    public function getPackingSlipGenTiming() {
    	return $this->packingSlipGenTiming;
    }
    public function setPackingSlipGenTiming($packingSlipGenTiming) {
    	$this->packingSlipGenTiming = $packingSlipGenTiming;
    }
    
    public function getEdiInterchangeID() {
    	return $this->ediInterchangeID;
    }
    
    public function setEdiInterchangeID($ediInterchangeID) {
    	$this->ediInterchangeID = $ediInterchangeID;
    }
    
    public function getEdiInterchangeQualifier() {
    	return $this->ediInterchangeQualifier;
    }
    
    public function setEdiInterchangeQualifier($ediInterchangeQualifier) {
    	$this->ediInterchangeQualifier = $ediInterchangeQualifier;
    }
    
    public function getErpAdapter() {
    	return $this->erpAdapter;
    }
    
    public function setErpAdapter($erpAdapter) {
    	$this->erpAdapter = $erpAdapter;
    }
    
    public function getEmailAddress() {
    	return $this->emailAddress;
    }
    
    public function setEmailAddress($emailAddress) {
    	$this->emailAddress = $emailAddress;
    }
    
    public function getErpURL() {
    	return $this->erpURL;
    }
    
    public function setErpURL($erpURL) {
    	$this->erpURL = $erpURL;
    }
    
    public function getErpUser() {
    	return $this->erpUser;
    }
    
    public function setErpUser($erpUser) {
    	$this->erpUser = $erpUser;
    }
    
    public function getErpPassword() {
    	return $this->erpPassword;
    }
    
    public function setErpPassword($erpPassword) {
    	$this->erpPassword = $erpPassword;
    }
    public function getName() {
    	return $this->name;
    }
    
    public function setName($name) {
    	$this->name = $name;
    }
}
