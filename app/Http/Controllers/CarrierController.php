<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarrierController extends Controller
{
    private $name;
	private $senderAccountNumber;
	private $accountNumber;
	private $accountZipCode;
	private $meterNumber;
	private $key;
	private $password;
	private $labels = array(); //images
	private $masterShipmentId;
	private $logger;
	private $username;
 	private $serviceType;
 	private $serviceTypeDescription;
 	    
 	public function getServiceTypeDescription() 
 	{
 	  return $this->serviceTypeDescription;
 	}
 	
 	public function setServiceTypeDescription($value) 
 	{
 	  $this->serviceTypeDescription = $value;
 	}
 	    
 	public function getServiceType() 
 	{
 	  return $this->serviceType;
 	}
 	
 	public function setServiceType($value) 
 	{
 	  $this->serviceType = $value;
 	}
	    
	public function getUsername(){
	  return $this->username;
	}
	
	public function setUsername($value)	{
	  $this->username = $value;
	}
	    
	public function getLogger()	{
	  return $this->logger;
	}
	
	public function setLogger($value){
	  $this->logger = $value;
	}
	
	public function getLabels(){
		return $this->labels;
	}
	
	public function getMasterShipmentId(){
		return $this->masterShipmentId;
	}	
		
	
	public function getCarrierInfo($order){
		$db = new ediSqlNew($order->getTrader());
		$scacArr = $db->getCarrierShippingCodeInfo ( $order->getTrader()->getId (), $order->getShipmentCarrier () );
		$this->setServiceType($scacArr['partner_carrier_code']);
		$this->setName($scacArr['partner_id']);
		$this->setServiceTypeDescription($scacArr['description']);
echo "servicetype: {$scacArr['partner_carrier_code']}\n";
	}
	
	private function createCarrierShipment(){
		if($this->name == 'FEDEX'){
			require '../thirdparty/fedex/autoload.php';
			$carrierShipment = new \RocketShipIt\Shipment('fedex');				
		}elseif($this->name == 'UPS'){
			include_once '../thirdparty/ups_api/RocketShipIt.php';			
			$carrierShipment = new RocketShipShipment('UPS');
		}else{
			throw new Exception("carrier:prepCarrier Unknown carrier " . $this->name);
		}
		return $carrierShipment;		
	}
	
	public function createShippingLabels($shipment, $order){
		$numOfPackages = count($shipment->getPackage()); 
		if($numOfPackages > 1){
			$this->createMPSLabel($carrierShipment, $shipment, $order);
		}elseif($numOfPackages == 1){							
			$this->createSSLabel($shipment, $order);
		}else{
			throw new Exception("carrier:createShippingLabels: No packages avaialable for creating shipping labels");
		}
	}
	
	private function prepCarrierShipment($carrierShipment, $order){
		$partner = $order->getPartner();
		$trader = $order->getTrader();
		
		$address = $partner->getShippingInfo()->getFromAddress();
		
		$carrierShipment->setParameter('shipper', $address->getName());
		$traderPhone = $address->getPhone();
		$carrierShipment->setParameter('shipPhone', $address->getPhone());
		$carrierShipment->setParameter('shipAddr1', $address->getStreet1());
		$street2 = $address->getStreet2();

		if (!empty($street2)) {
			$carrierShipment->setParameter('shipAddr2', $address->getStreet2());
		}
		$carrierShipment->setParameter('shipCity', $address->getCity());
		$carrierShipment->setParameter('shipState', $address->getStateAbbrev());
		$carrierShipment->setParameter('shipCode', $address->getZip());
		$carrierShipment->setParameter('shipCountry', $address->getCountry());
		$carrierShipment->setParameter('shipmentDescription', $order->getPoNumber ());
		
		$address = $order->getSaAddress();
		
		$carrierShipment->setParameter('toCompany', '');
		$carrierShipment->setParameter('toName', $address->getFirstName() . " " . $address->getLastName());
		$phone = trim($address->getPhone());
		
		if (strlen($phone) > 10) {
			if (strstr($phone, 'or')) {
				$phones = explode('or', $phone);
				$phone = trim($phones[0]);
			}
		}
		
		$regex = "/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i";
		$isPhone = preg_match($regex, $phone) ? true : false;
		
		if (!$isPhone) {
			$phone = $traderPhone;
		}
		
		$carrierShipment->setParameter('toPhone', $phone);
		$carrierShipment->setParameter('toAddr1', $address->getStreet1());
		$street2 = $address->getStreet2();
                                
                //SK- check if address line 2 size is greater than 35 for UPS
                // if greater than 35, truncate the address line 2 to 35 characters
                if (($this->name == 'UPS') && (strlen($street2) > 35))  {
                    $street2 = substr($street2, 0, 35);
                }
		if (!empty($street2)) {
			$carrierShipment->setParameter('toAddr2', $street2);
		}
		$carrierShipment->setParameter('toCity', $address->getCity());
		$carrierShipment->setParameter('toState', $address->getStateAbbrev());
		$carrierShipment->setParameter('toCode', $address->getZip());
		$carrierShipment->setParameter('toCountry', $address->getCountry());
		$carrierShipment->setParameter('shipmentDescription', $order->getPoNumber ());
		
		$serviceType = $this->getServiceType();
		if (empty ($serviceType)) {
			$errorMessage = "Carrier:prepCarrierShipment: Did not find the shipping priority for sales order: " . $order->getPoNumber ();
			$this->logger->LogError ( $errorMessage );
			throw new Exception($errorMessage);
		}
		
		$carrierShipment->setParameter ( 'service', $serviceType );
	}

	private function createSSLabel($shipment, $order){
		$carrierShipment = $this->createCarrierShipment();
		$packages = $shipment->getPackage();
		$this->prepCarrierShipment($carrierShipment, $order);

		if($this->name == "FEDEX"){
			$packageCount = 1;
			$seqNum = 1;
			$ret = $this->createFedexLabel($carrierShipment, $packages[0], $order, $packageCount, $seqNum);
			$this->masterShipmentId = $ret['shipmentId']; //TODO: use a setter
		}elseif($this->name == "UPS"){
			$this->createUPSLabel($carrierShipment, $shipment, $order);
			$this->masterShipmentId = $this->labels[0]['shipmentId'];
		}else{
			throw new Exception("Carrier:createSSLabel: unknown carrier: " . $this->name);
		}
		
		return true;
	}
	
	private function createMPSLabel($carrierShipment, $shipment, $order){
		$this->masterShipmentId = "";
		if($this->name == "FEDEX"){
			$seqNum = 1;
			$packageCount = count($shipment->getPackage());
			foreach($shipment->getPackage() as $package){
				$carrierShipment = $this->createCarrierShipment();
				$this->prepCarrierShipment($carrierShipment, $order);
								
				$ret = $this->createFedexLabel($carrierShipment, $package, $order, $packageCount, $seqNum, $this->masterShipmentId);
				if (empty($this->masterShipmentId)) { // run only once
					$this->masterShipmentId = $ret['shipmentId'];
				}
				$seqNum++;
			}
		}elseif($this->name == "UPS"){
			$carrierShipment = $this->createCarrierShipment();
			$this->prepCarrierShipment($carrierShipment, $order);
			$this->createUPSLabel($carrierShipment, $shipment, $order);
			$this->masterShipmentId = $this->labels[0]['shipmentId'];
			
		}else{
			throw new Exception("Carrier:createMPSLabel: unknown carrier: " . $this->name);
		}
		return true;
	}
	
	private function createFedexLabel($carrierShipment, $package, $order, $packageCount, $seqNum, $masterShipmentId = null){
		$partner = $order->getPartner();
		$si = $partner->getShippingInfo();	
		
		if ($si->getShippingType () == "TPB") {
			$carrierShipment->setParameter ( 'paymentType', 'THIRD_PARTY' );
			$carrierShipment->setParameter ( 'thirdPartyAccount', $this->getAccountNumber () );
		} else {
			$carrierShipment->setParameter ( 'paymentType', 'SENDER' );
		}
		$carrierShipment->setParameter ( 'key', $this->getKey () );
		$carrierShipment->setParameter ( 'password', $this->getPassword () );
		$carrierShipment->setParameter ( 'accountNumber', $this->getSenderAccountNumber() );
		$carrierShipment->setParameter ( 'meterNumber', $this->getMeterNumber() );
		
		$p = $package->getProduct();
		if(empty($p)){
			throw new Exception("Carrier:createFedexLabel failed. Error: product details not found for PO{$order->getPoNumber()} and trader Id {$order->getTrader()->getId()}");
		}else{
			$len = floatval($p->getLength());
			$width= floatval($p->getWidth());
			$height= floatval($p->getHeight());
			$weight = floatval($p->getWeight());
			
			if($Len <= 0 && $width <= 0 && $height  <= 0 && $weight  <= 0){
				throw new Exception("Carrier:createFedexLabel failed. Error: product details not found for PO{$order->getPoNumber()} and trader Id {$order->getTrader()->getId()}");
			}
			
			$len = intval($len) > 0 ? intval($len) : 1;
			$width= intval($width) > 0 ? intval($width) : 1;
			$height= intval($height) > 0 ? intval($height) : 1;
			
		}
		$carrierShipment->setParameter('length', $len );
		$carrierShipment->setParameter('width',  $width);
		$carrierShipment->setParameter('height',  $height);
		$carrierShipment->setParameter('weight',  $weight);
		
		$carrierShipment->setParameter('packageCount', $packageCount);
		$carrierShipment->setParameter('sequenceNumber', $seqNum);
		
		if (!empty($masterShipmentId)) {
			$carrierShipment->setParameter('shipmentIdentification', $masterShipmentId);
		}
		$rv = $package->getRefCodeValuePairs();
		
		if($rv[0]['code'] == 'poNumber'){
			$carrierShipment->setParameter ( 'referenceCode2', 'P_O_NUMBER' ); //TODO: change this to customer_reference - DOESN'T WORK
			$carrierShipment->setParameter ( 'referenceValue2', $rv[0]['value']);
			$carrierShipment->setParameter ( 'referenceCode', 'CUSTOMER_REFERENCE' );
			$carrierShipment->setParameter ( 'referenceValue', $rv[1]['value']);
		}else{
			$carrierShipment->setParameter ( 'referenceCode2', 'P_O_NUMBER' ); //TODO: change this to customer_reference - DOESN'T WORK
			$carrierShipment->setParameter ( 'referenceValue2', $rv[1]['value']);
			$carrierShipment->setParameter ( 'referenceCode', 'CUSTOMER_REFERENCE' );
			$carrierShipment->setParameter ( 'referenceValue', $rv[0]['value']);
		}
		
		
		$carrierShipment->setParameter('referenceCode3', 'SHIPMENT_INTEGRITY');
		$carrierShipment->setParameter('referenceValue3', $order->getSoNumber());
		
		$response = $carrierShipment->submitShipment();
		//echo $carrierShipment->debug();
		if (!in_array('status', array_keys($response))) {
			$resp = $carrierShipment->getXmlResponse();
			$xmlResp = $resp['error'];
			$xmlResp = stristr($xmlResp, '<');
			if(!empty($xmlResp)){
				$e = simplexml_load_string($xmlResp);
				if(!empty($e->message)){
					throw new Exception($e->message);
				}
			}
			if (in_array('Fault', array_keys($response))) {
				throw new Exception("Carrier:createFedexLabel Create Shipment Failed; Error Code: " . $response['Fault']['faultcode'] . " Error Message: " . $response['Fault']['faultstring']);
			} else if (!count($response)) {
				//throw new Exception("Carrier:createFedexLabel Create Shipment Failed: Unknown Error");
			} else if (strstr($response, "Error")) {
				throw new Exception($response);
			} else {
				//throw new Exception("shippingLabelAdapters Create Shipment Failed: Unknown Error");
			}
			$xmlResp = $carrierShipment->getXmlResponse();
			$xmlResp = stristr($xmlResp['error'], '<');
			if(!empty($xmlResp)){
				$e = simplexml_load_string($xmlResp);
				if(!empty($e->message)){
					throw new Exception($e->message);
				}
			}else{
				throw new Exception("shippingLabelAdapters Create Shipment Failed: Unknown Error");
			}
			
		}
		
		
		$ret = array();
		$package->setTrackingNumber($response['tracking_id']);
		$ret['shipmentId'] = $response['tracking_id'];
		$ret['label_img'] = base64_decode($response['label_img']);
		$this->labels[] = $ret;
		return $ret;
	}

	private function createUPSLabel($carrierShipment, $shipment, $order){
		$partner = $order->getPartner();
		$si = $partner->getShippingInfo();
	
		if ($si->getShippingType () == "TPB") {
			$carrierShipment->setParameter('billThirdParty', True);
			$carrierShipment->setParameter('thirdPartyAccount', $this->getAccountNumber()); // UPS Account Number Goes Here
			$carrierShipment->setParameter('thirdPartyPostalCode', $this->getAccountZipCode());
			$carrierShipment->setParameter('thirdPartyCountryCode', 'US'); //TODO add an element
		} else {
			$carrierShipment->setParameter ( 'paymentType', 'SENDER' );
		}
		$carrierShipment->setParameter ( 'license', $this->getKey () );
		$carrierShipment->setParameter ( 'password', $this->getPassword () );
		$carrierShipment->setParameter ( 'accountNumber', $this->getSenderAccountNumber() );
		$carrierShipment->setParameter ( 'username', $this->getUsername() );
		
		
		foreach($shipment->getPackage() as $package){
			$shipmentPackage = new RocketShipPackage('UPS');

			$p = $package->getProduct();
			if(empty($p)){
				throw new Exception("Carrier:createUPSLabel failed. Error: product details not found for PO{$order->getPoNumber()} and trader Id {$order->getTrader()->getId()}");
			}else{
				$len = floatval($p->getLength());
				$width= floatval($p->getWidth());
				$height= floatval($p->getHeight());
				$weight = floatval($p->getWeight());
				
				if($Len <= 0 && $width <= 0 && $height  <= 0 && $weight  <= 0){
					throw new Exception("Carrier:createUPSLabel failed. Error: product details not found for PO{$order->getPoNumber()} and trader Id {$order->getTrader()->getId()}");
				}
			}
			$shipmentPackage->setParameter('length', $len);
			$shipmentPackage->setParameter('width',  $width);
			$shipmentPackage->setParameter('height',  $height);
			$shipmentPackage->setParameter('weight', $weight);
			
			$rvPairs = $package->getRefCodeValuePairs();
			$i=1;
			$rocketParam = array();
			foreach($rvPairs as $rv){
				if($i == 1){
					$rocketParam[0] = 'referenceCode';
					$rocketParam[1] = 'referenceValue';
				}else{
					$rocketParam[0] = 'referenceCode' . $i;
					$rocketParam[1] = 'referenceValue' . $i;
				}
				$shipmentPackage->setParameter ( $rocketParam[0], 'TN'  ); 
				$shipmentPackage->setParameter ( $rocketParam[1], $rv['value']); 
				$i++;
				if($i == 4) break; //rocketship takes only 3 refcodes
			}
			
			$carrierShipment->addPackageToShipment($shipmentPackage);
		}
		
		$response = $carrierShipment->submitShipment();
		if(!is_array($response)){			
			throw new Exception("Carrier:createUPSLabel failed. Error: " . $response);
		}
		$labels = array();
		
		$i = 0;
		$packages = $shipment->getPackage();
		foreach($response['pkgs'] as $pkg){
			if(empty($pkg['label_img'])){
				$this->logger->LogError("Carrier:createUPSLabel: UPS Error - label not created for order: {$order->getSoNumber()}");
				continue;
			}
			if(empty($pkg['pkg_trk_num'])){
				$this->logger->LogError("Carrier:createUPSLabel: UPS Error - Tracking Number not created for order: {$order->getSoNumber()}");
				continue;
			}
			$label = array();
			$label['shipmentId'] = $pkg['pkg_trk_num'];
			$label['label_img'] = base64_decode($pkg['label_img']);
			$this->labels[] = $label;	
			$packages[$i++]->setTrackingNumber($pkg['pkg_trk_num']);
		}		
		if(empty($this->labels)){
			throw new Exception("Carrier:createUPSLabel failed. Shipping labels are not created.");
		}
	}
	
	
    function __construct($logger) {
        $this->logger = $logger;
    }
    
	public function getSenderAccountNumber() {
		return $this->senderAccountNumber;
	}
	public function setSenderAccountNumber($value) {
		$this->senderAccountNumber = $value;
	}
	public function getPassword() {
		return $this->password;
	}
	public function setPassword($value) {
		$this->password = $value;
	}
	public function getKey() {
		return $this->key;
	}
	public function setKey($value) {
		$this->key = $value;
	}
	public function getMeterNumber() {
		return $this->meterNumber;
	}
	public function setMeterNumber($value) {
		$this->meterNumber = $value;
	}
	public function getAccountZipCode() {
		return $this->accountZipCode;
	}
	public function setAccountZipCode($value) {
		$this->accountZipCode = $value;
	}
	public function getAccountNumber() {
		return $this->accountNumber;
	}
	public function setAccountNumber($value) {
		$this->accountNumber = $value;
	}
	public function getName() {
		return $this->name;
	}
	public function setName($value) {
		$this->name = strtoupper($value);
	}
	public function get($property) {
		if (property_exists($this, $property)) {
			return $this->$property;
		}
	}
	
	public function set($property, $value) {
		if (property_exists($this, $property)) {
			$this->$property = $value;
		}
	}
}
