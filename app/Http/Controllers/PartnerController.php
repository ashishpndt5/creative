<?php

namespace App\Http\Controllers;

use App\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    private $logger;
    private $id;
    public $traderId;
    public $partnerId;
    private $name;
    private $type;
    private $emailAddress;
    private $ediInterchangeID;
    private $ediInterchangeQualifier;
    private $traderInterchangeID;
    private $traderIDQualifier;
    private $ediGSIndustryIdentifierCode;
    private $ediX12Version;
    private $ediLocationCode;
    private $warehouseID;
    private $sacValue;
    private $netTerms;
    private $termDiscountDays;
    private $downloadURL;
    private $downloadUserID;
    private $downloadPassword;
    private $downloadFolder;
    private $uploadURL;
    private $uploadUserID;
    private $uploadPassword;
    private $uploadFolder;
    private $uploadInventoryFolder;
    private $getOrderAdapter;
    private $packingSlipAdapter;
    private $shippingLabelAdapter;
    private $cartonLabelAdapter;
    private $ediSegmentSeperator;
    private $coupon;
    public $tradeInterface;
    private $shippingItemSKU;
    private $shippingItemPrice;
    private $vendorId;
    private $rules = array();
    private $header;
    private $token;
    private $url;
    private $ftpType;
    private $genShippingLabel = 'no'; //yes or no
    private $shippingInfo;
    private $emailFormat;
    private $shipmentNAL;
    private $initialNST;
    private $customerProfileId;
    private $messageTransfer;
    private $customPrice;
    private $customerPaymentProfileId;
    private $validWarehouses = array();
    private $checkInventory;
    private $sellerPartnerWarehouses = array();
    private $partnerEdiVersion;
    private $fieldSeperator;
    private $skuChangeCase;
    private $invoiceCreate;
    private $partnerVatNumber;
    private $validatePrice;
    private $ftpActive = false;
    private $accessToken;
    private $accessTokenSecret;
    private $erpCustomerId; 
    private $erpCustomerCreate;
    private $sendOrderConfirmationEmail;    
    private $overrideInventoryParameters;
    private $shippingTerms;
    private $uploadInvoiceFolder;
    private $AS2Id;
    private $updateAmazonShippingTemplate;
    //---- Adding the code for merchant_id and marketplace_id
    private $merchantId;
    private $marketPlaceId;    
    

    function __construct() {
        
    }
    
    public function getMerchantId() {
    	return $this->merchantId;
    }
    
    public function setMerchantId($merchantId) {
    	$this->merchantId = $merchantId;
    }
    
    public function getTraderId() {
    	return $this->traderId;
    }
    
    public function setTraderId($treaderId) {
    	$this->traderId = $merchantId;
    }
    
    public function getPartnerId() {
    	return $this->partnerId;
    }
    
    public function setPartnerId($partnerId) {
    	$this->partnerId = $partnerId;
    }
    
    
    public function getMarketPlaceId() {
    	return $this->marketPlaceId;
    }
    
    public function setMarketPlaceId($marketPlaceId) {
    	$this->marketPlaceId = $marketPlaceId;
    }
    
    //--------- End code
    
    public function getAS2Id() {
    	return $this->AS2Id;
    }
    
    public function setAS2Id($value) {
    	$this->AS2Id = $value;
    }
    
    public function getOverrideInventoryParameters(){
    	return $this->overrideInventoryParameters;
    }
    
    public function setOverrideInventoryParameters($overrideInventoryParameters){
    	$this->overrideInventoryParameters = $overrideInventoryParameters;
    }
    public function getUpdateAmazonShippingTemplate(){
    	return $this->updateAmazonShippingTemplate;
    }
    
    public function setUpdateAmazonShippingTemplate($updateAmazonShippingTemplate){
    	$this->updateAmazonShippingTemplate = $updateAmazonShippingTemplate;
    }
    
    public function getShippingTerms(){
    	return $this->shippingTerms;
    }
    
    public function setShippingTerms($shippingTerms){
    	$this->shippingTerms = $shippingTerms;
    }
    
    public function getUploadInvoiceFolder(){
    	return $this->uploadInvoiceFolder;
    }
    
    public function setUploadInvoiceFolder($uploadInvoiceFolder){
    	$this->uploadInvoiceFolder = $uploadInvoiceFolder;
    }
    
    public function getErpCustomerGroupId(){
    	return $this->erpCustomerGroupId;
    }
    
    public function setErpCustomerGroupId($erpCustomerGroupId){
    	$this->erpCustomerGroupId = $erpCustomerGroupId;
    }
    
    public function getSendOrderConfirmationEmail(){
    	return $this->sendOrderConfirmationEmail;
    }
    
    public function setSendOrderConfirmationEmail($sendOrderConfirmationEmail){
    	$this->sendOrderConfirmationEmail = $sendOrderConfirmationEmail;
    }
    
    public function getErpCustomerCreate(){
    	return $this->erpCustomerCreate;
    }
    
    public function setErpCustomerCreate($erpCustomerCreate){
    	$this->erpCustomerCreate = $erpCustomerCreate;
    }
    
    public function getErpCustomerEmail(){
    	return $this->erpCustomerEmail;
    }
    
    public function setErpCustomerEmail($erpCustomerEmail){
    	$this->erpCustomerEmail = $erpCustomerEmail;
    }
    
    public function getErpCustomerId(){
    	return $this->erpCustomerId;
    }
    
    public function setErpCustomerId($erpCustomerId){
    	$this->erpCustomerId = $erpCustomerId;
    }
    
    
    public function getAccessToken(){
    	return $this->accessToken;
    }
    	
    public function setAccessToken($accessToken){
    	$this->accessToken = $accessToken;
    }
    
    public function getAccessTokenSecret(){
    	return $this->accessTokenSecret;
    }
    
    public function setAccessTokenSecret(){
    	return $this->accessTokenSecret = $accessTokenSecret;
    }
    
    public function getFtpActive() {
    	return $this->ftpActive;
    }
    
    public function setFtpActive($value) {
    	if($value > 0){
    		$this->ftpActive = true;
    	}
    }
    
    
    public function setPartnerEdiVersion($partnerEdiVersion){
    	$this->partnerEdiVersion = $partnerEdiVersion;
    }
    
    public function getPartnerEdiVersion(){
    	return $this->partnerEdiVersion;
    }
    
    public function setPartnerVatNumber($partnerVatNumber){
    	$this->partnerVatNumber = $partnerVatNumber;
    }
    
    public function getPartnerVatNumber(){
    	return $this->partnerVatNumber;
    }
    public function setValidatePrice($validatePrice){
    	$this->validatePrice = $validatePrice;
    }
    public function getValidatePrice(){
    	return $this->validatePrice;
    }
    
    public function setInvoiceCreate($invoiceCreate){
    	$this->invoiceCreate = $invoiceCreate;
    }
    public function getInvoiceCreate(){
    	return $this->invoiceCreate;
    }
    public function setSkuChangeCase($skuChangeCase){
    	$this->skuChangeCase = $skuChangeCase;
    }
    
    public function getSkuChangeCase(){
    	return $this->skuChangeCase;
    }
    public function setFieldSeperator($fieldSeperator){
    	$this->fieldSeperator = $fieldSeperator;
    }
    public function getFieldSeperator(){
    	return $this->fieldSeperator;
    }
    public function setInventoryCheck($checkInventory){
    	$this->checkInventory = $checkInventory;
    }
    
    public function getInventoryCheck(){
    	return $this->checkInventory;
    }
    
    public function setValidWarehouses($warehouses){
    	$this->validWarehouses = $warehouses;
    }
    
    public function getValidWarehouses(){
    	return $this->validWarehouses;
    }
    
    public function getShippingInfo() {
    	return $this->shippingInfo;
    }
    
    public function setShippingInfo($value) {
    	$this->shippingInfo = $value;
    }

    function initializePartner($partnerInfo) {
        
       // $logger = new Logger("log.txt", Logger::WARN);
                
        if (empty($partnerInfo)) {
            echo "Partner Info is empty";
            return;
        }
        $partner;
        //set partner values
        foreach ($partnerInfo as $varName => $value) {
            if (!is_numeric($varName)) {
                $this->set($varName, $value);
            }
        }
        //call the set functions explicitly to match older design
        $this->setUploadInventoryFolder($partnerInfo->uploadInventoryFolder);
        $this->setEdiInterchangeQualifier($partnerInfo->ediQualifier);
        $this->setPartnerVatNumber($partnerInfo->vatNumber);
        $this->setEdiLocationCode($partnerInfo->ediLocationCode);
        if (!empty($partnerInfo->ftpType)) {
            $this->setFtpType((string) $partnerInfo->ftpType);
        } else {
            $this->setFtpType("ftp");
        }
       /* if (!empty($partnerInfo['authenticationFIle'])) {
            //echo "../config/" . $partnerInfo['authenticationFIle'] . '.xml';
            $authInfo = simplexml_load_file("../config/" . $partnerInfo['authenticationFIle'] . '.xml');
            $tmp = (array) $authInfo->headers;
            foreach ($tmp['header'] as $h) {
                $this->setHeader((array) $h);
            }
            $this->setToken($authInfo->token->id);
            $this->setUrl($authInfo->url);
        }*/
        $this->setAS2Id($partnerInfo->AS2Id);
        //if (isset($partnerInfo['genShippingLabel'])) {
        //  $this->setGenShippingLabel($partnerInfo['genShippingLabel']);
        //}
        if (!empty($partnerInfo->emailFormat)) {
            $this->setEmailFormat($partnerInfo->emailFormat);
        }
        if (!empty($partnerInfo->shipmentNAL)) {
            $this->setShipmentNAL($partnerInfo->shipmentNAL);
        }
        
        if (!empty($partnerInfo->initialNST)) {
            $this->setInitialNST($partnerInfo->initialNST);
        }
        if (!empty($partnerInfo->customerProfileId)) {
            $this->setCustomerProfileId($partnerInfo->customerProfileId);
        }
        if (!empty($partnerInfo->customerPaymentProfileId)) {
            $this->setCustomerPaymentProfileId($partnerInfo->customerPaymentProfileId);
        }

        if (!empty($partnerInfo->merchant_id)) {
            $this->setMerchantId($partnerInfo->merchant_id);
        }
        
        if (!empty($partnerInfo->marketplace_id)) {
            $this->setMarketPlaceId($partnerInfo->marketplace_id);
        }
        
        
        $this->setMessageTransfer ( $partnerInfo->messageTransfer );
        $this->setCustomPrice( $partnerInfo->customPrice );
        $traderId = $partnerInfo->traderId;
        /*
        $dbSqls = new znectDbSqls();

        //set custom variables
        $rvPairs = array();
        $customVariables = $dbSqls->getCustomVariables($traderId, $this->getId());
        foreach ($customVariables as $customVariable) {
            //check for refCodeValuePair
            if ($customVariable['type'] == 'refCodeValuePair') {
                $rvPair = array();
                $rvPair['code'] = $customVariable['name'];
                $rvPair['value'] = $customVariable['value'];
                $rvPairs[] = $rvPair;
            }
        }
        //get shipping info
        $shippingDetails = $dbSqls->getPartnerShippingDetails($traderId, $this->getId());
        $si = new shippingInfo();
        if (!empty($shippingDetails)) {
            $si->setShippingType($shippingDetails['shippingType']);
            $si->setDefaultPriority($shippingDetails['priority']);
            $si->setShippingCarrier($shippingDetails['shippingCarrier']);
            $si->setShipComplete($shippingDetails['shipComplete']);
            $si->setPackaging($shippingDetails['packaging']);

            $address = new address();
            $address->setFirstName($shippingDetails['fromFirstName']);
            $address->setLastName($shippingDetails['fromLastName']);
            $address->setStreet1($shippingDetails['fromStreet1']);
            $address->setStreet2($shippingDetails['fromStreet2']);
            $address->setCity($shippingDetails['fromCity']);
            $address->setStateAbbrev($shippingDetails['fromState']);
            $address->setZip($shippingDetails['fromZip']);
            $address->setCountry($shippingDetails['fromCountry']);
            $address->setPhone($shippingDetails['fromPhoneNumber']);
            $si->setFromAddress($address);

            $c = new carrier($logger);
            $c->setName($shippingDetails['carrier']);
            $c->setSenderAccountNumber($shippingDetails['senderAccountNumber']);
            $c->setAccountNumber($shippingDetails['accountNumber']);
            $c->setAccountZipCode($shippingDetails['zipCode']);
            $c->setMeterNumber($shippingDetails['meterNumber']);
            $c->setKey($shippingDetails['accountKey']);
            $c->setUsername($shippingDetails['username']);
            $c->setPassword($shippingDetails['accountPassword']);
            $si->setCarrier($c);

            $si->setRefCodeValuePairs($rvPairs);
        }
        $this->setShippingInfo($si);

        //get partner rules
        $partnerRules = $dbSqls->getPartnerRules($traderId, $this->getId());
        foreach ($partnerRules as $currentRule) {
            $this->setRule(intval($currentRule['ruleId']), (string) $currentRule['ruleName']);
        }
        
        //get valid warehouses
        $warehouses = $dbSqls->getValidTraderPartnerWarehouse($traderId, $this->getId());
        $this->setValidWarehouses($warehouses);
        
        //get seller partner warehouse ids
        $partnerWarehouses = $dbSqls->getPartnerWarehouses($traderId, $this->getId());
        $this->setSellerPartnerWarehouses($partnerWarehouses);
        */
        
    }

    function initialize($thisPartner) {
        $logger = new Logger("log.txt", Logger::WARN);
        $this->setId((string) $thisPartner->id);
        $this->setType((string) $thisPartner->type);
        $this->setName((string) $thisPartner->name);
        $this->setEmailAddress((string) $thisPartner->emailAddress);
        $this->setEdiInterchangeID((string) $thisPartner->ediInterchangeID);
        $this->setEdiGSIndustryIdentifierCode((string) $thisPartner->ediGSIndustryIdentifierCode);
        $this->setEdiInterchangeQualifier((string) $thisPartner->ediInterchangeQualifier);
        $this->setEdiX12Version((string) $thisPartner->ediX12Version);
        $this->setEdiLocationCode((string) $thisPartner->ediLocationCode);
        $this->setWarehouseID((string) $thisPartner->warehouseID);
        $this->setSacValue((string) $thisPartner->sacValue);
        $this->setNetTerms((string) $thisPartner->netTerms);
        $this->setTermDiscountDays((string) $thisPartner->termDiscountDays);
        $this->setDownloadURL((string) $thisPartner->downloadURL);
        $this->setDownloadUserID((string) $thisPartner->downloadUserID);
        $this->setDownloadPassword((string) $thisPartner->downloadPassword);
        $this->setDownloadFolder((string) $thisPartner->downloadFolder);
        $this->setUploadURL((string) $thisPartner->uploadURL);
        $this->setUploadUserID((string) $thisPartner->uploadUserID);
        $this->setUploadPassword((string) $thisPartner->uploadPassword);
        $this->setUploadFolder((string) $thisPartner->uploadFolder);
        $this->setUploadInventoryFolder((string) $thisPartner->uploadInventoryFolder);
        $this->setGetOrderAdapter((string) $thisPartner->getOrderAdapter);
        $this->setPackingSlipAdapter((string) $thisPartner->packingSlipAdapter);
        $this->setShippingLabelAdapter((string) $thisPartner->shippingLabelAdapter);
        $this->setCartonLabelAdapter((string) $thisPartner->cartonLabelAdapter);
        $this->setEdiSegmentSeperator((string) $thisPartner->ediSegmentSeperator);
        $this->setFieldSeperator((string) $thisPartner->fieldSeperator);
        $this->setCoupon((string) $thisPartner->coupon);
        $this->setTradeInterface((string) $thisPartner->tradeInterface);
        $this->setShippingItemSKU((string) $thisPartner->shippingItemSKU);
        $this->setShippingItemPrice((string) $thisPartner->shippingItemPrice);
        $this->setRules($thisPartner->znectRules);
        $this->setVendorId((string) $thisPartner->vendorId);
        $this->setMessageTransfer ( ( string ) $thisPartner->messageTransfer );
        $this->setCustomPrice( ( string ) $thisPartner->customPrice );
        $this->setInvoiceCreate(( string ) $thisPartner->invoiceCreate);
        $this->setValidatePrice(( string ) $thisPartner->validatePrice);
        $this->setPartnerVatNumber(( string ) $thisPartner->vatNumber);
        if (isset($thisPartner->ftpType)) {
            $this->setFtpType((string) $thisPartner->ftpType);
        } else {
            $this->setFtpType("ftp");
        }
        if (isset($thisPartner->authenticationFIle)) {
            //echo "../config/" . $thisPartner->authenticationFIle . '.xml';
            $authInfo = simplexml_load_file("../config/" . $thisPartner->authenticationFIle . '.xml');
            $tmp = (array) $authInfo->headers;
            foreach ($tmp['header'] as $h) {
                $this->setHeader((array) $h);
            }
            $this->setToken($authInfo->token->id);
            $this->setUrl($authInfo->url);
        }
        if (isset($thisPartner->genShippingLabel)) {
            $this->setGenShippingLabel($thisPartner->genShippingLabel);
        }//else default value 'no'

        if (isset($thisPartner->emailFormat)) {
            $this->setEmailFormat((string) $thisPartner->emailFormat);
        }

        $si = new shippingInfo();
        if (isset($thisPartner->shipping)) {
            $si->setShippingType((string) $thisPartner->shipping->shippingType);
            $si->setDefaultPriority((string) $thisPartner->shipping->priority);
            $si->setShippingCarrier((string) $thisPartner->shipping->shippingCarrier);
            /* ref code value pairs */

            $rvPairs = array();
            foreach ($thisPartner->shipping->refCodeValuePair as $rv) {
                $rvPair = array();
                $rvPair['code'] = (string) $rv->code;
                $rvPair['value'] = (string) $rv->value;
                $rvPairs[] = $rvPair;
            }
            $si->setRefCodeValuePairs($rvPairs);
            /* fromAddress */
            $address = new address();
            $address->setFirstName((string) $thisPartner->shipping->fromAddress->firstName);
            $address->setLastName((string) $thisPartner->shipping->fromAddress->lastName);
            $address->setStreet1((string) $thisPartner->shipping->fromAddress->street1);
            $address->setStreet2((string) $thisPartner->shipping->fromAddress->street2);
            $address->setCity((string) $thisPartner->shipping->fromAddress->city);
            $address->setStateAbbrev((string) $thisPartner->shipping->fromAddress->state);
            $address->setZip((string) $thisPartner->shipping->fromAddress->zip);
            $address->setCountry((string) $thisPartner->shipping->fromAddress->country);
            $address->setPhone((string) $thisPartner->shipping->fromAddress->phoneNumber);

            $si->setFromAddress($address);

            $c = new carrier($logger);
            $c->setName((string) $thisPartner->shipping->carrier);
            $c->setSenderAccountNumber((string) $thisPartner->shipping->accountDetails->senderAccountNumber);
            $c->setAccountNumber((string) $thisPartner->shipping->accountDetails->accountNumber);
            $c->setAccountZipCode((string) $thisPartner->shipping->accountDetails->zipCode);
            $c->setMeterNumber((string) $thisPartner->shipping->accountDetails->meterNumber);
            $c->setKey((string) $thisPartner->shipping->accountDetails->key);
            $c->setUsername((string) $thisPartner->shipping->accountDetails->username);
            $c->setPassword((string) $thisPartner->shipping->accountDetails->password);

            $si->setCarrier($c);
            $si->setShipComplete((string) $thisPartner->shipping->shipComplete);
            $si->setPackaging((string) $thisPartner->shipping->packaging);
        }

        $this->setShippingInfo($si);
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
    
    public function getGenShippingLabel() {
    	return $this->genShippingLabel;
    }
    
    public function setGenShippingLabel($genShippingLabel) {
    	$this->genShippingLabel = $genShippingLabel;
    }
    
    public function getFtpType() {
    	return $this->ftpType;
    }
    
    public function setFtpType($ftpType) {
    	$this->ftpType = $ftpType;
    }
    
    public function getUrl() {
    	return $this->url;
    }
    
    public function setUrl($url) {
    	$this->url = $url;
    }
    
    public function getToken() {
    	return $this->token;
    }
    
    public function setToken($token) {
    	$this->token = $token;
    }
    
    public function getHeader() {
    	return $this->header;
    }
    
    public function setHeader($header) {
    	$this->header[] = $header;
    }
    
    public function getEdiGSIndustryIdentifierCode() {
    	return $this->ediGSIndustryIdentifierCode;
    }
    
    public function setEdiGSIndustryIdentifierCode($ediGSIndustryIdentifierCode) {
    	$this->ediGSIndustryIdentifierCode = $ediGSIndustryIdentifierCode;
    }
    
    public function getTradeInterface() {
    	return $this->tradeInterface;
    }
    
    public function setTradeInterface($tradeInterface) {
    	$this->tradeInterface = $tradeInterface;
    }
    
    public function getShipComplete() {
    	return $this->getShippingInfo()->getShipComplete();
    }
    
    public function getCoupon() {
    	return $this->coupon;
    }
    
    public function setCoupon($coupon) {
    	$this->coupon = $coupon;
    }
    
    public function getShippingType() {
    	return $this->getShippingInfo()->getShippingType();
    }
    
    public function getEdiSegmentSeperator() {
    	return $this->ediSegmentSeperator;
    }
    
    public function setEdiSegmentSeperator($ediSegmentSeperator) {
    	$this->ediSegmentSeperator = $ediSegmentSeperator;
    }
    
    public function setGetOrderAdapter($getOrderAdapter) {
    	$this->getOrderAdapter = $getOrderAdapter;
    }
    
    public function getGetOrderAdapter() {
    	return $this->getOrderAdapter;
    }
    public function getOrderAdapter() {
    	return $this->getOrderAdapter;
    }
    
    public function getPackingSlipAdapter() {
    	return $this->packingSlipAdapter;
    }
    
    public function setPackingSlipAdapter($packingSlipAdapter) {
    	$this->packingSlipAdapter = $packingSlipAdapter;
    }
    
    public function getShippingLabelAdapter() {
    	return $this->shippingLabelAdapter;
    }
    
    public function setShippingLabelAdapter($shippingLabelAdapter) {
    	$this->shippingLabelAdapter = $shippingLabelAdapter;
    }
    
    public function getId() {
    	return $this->id;
    }
    
    public function setId($id) {
    	$this->id = $id;
    }
    
    public function getName() {
    	return $this->name;
    }
    
    public function setName($name) {
    	$this->name = $name;
    }
    
    public function getType() {
    	return $this->type;
    }
    
    public function setType($type) {
    	$this->type = $type;
    }
    
    public function getPartnerType() {
    	return $this->type;
    }
    
    public function setPartnerType($type) {
    	$this->type = $type;
    }
    
    public function getEmailAddress() {
    	return $this->emailAddress;
    }
    
    public function setEmailAddress($emailAddress) {
    	$this->emailAddress = $emailAddress;
    }
    
    public function getEdiInterchangeID() {
    	return $this->ediInterchangeID;
    }
    
    public function setEdiInterchangeID($ediReceiverID) {
    	$this->ediInterchangeID = $ediReceiverID;
    }
    
    public function getTraderInterchangeID() {
    	return $this->traderInterchangeID;
    }
    
    public function setTraderInterchangeID ($traderInterchangeID) {
    	$this->traderInterchangeID = $traderInterchangeID;
    }
    
    public function getTraderIDQualifier() {
    	return $this->traderIDQualifier;
    }
    
    public function setTraderIDQualifier ($traderIDQualifier) {
    	$this->traderIDQualifier = $traderIDQualifier;
    }
    
    public function getEdiInterchangeQualifier() {
    	return $this->ediQualifier;
    }
    
    public function setEdiInterchangeQualifier($ediQualifier) {
    	$this->ediQualifier = $ediQualifier;
    }
    
    public function getEdiX12Version() {
    	return $this->ediX12Version;
    }
    
    public function setEdiX12Version($ediX12Version) {
    	$this->ediX12Version = $ediX12Version;
    }
    
    public function getWarehouseID() {
    	return $this->warehouseID;
    }
    
    public function setWarehouseID($warehouseID) {
    	$this->warehouseID = $warehouseID;
    }
    
    public function getSacValue() {
    	return $this->sacValue;
    }
    
    public function setSacValue($sacValue) {
    	$this->sacValue = $sacValue;
    }
    
    public function getNetTerms() {
    	return $this->netTerms;
    }
    
    public function setNetTerms($netTerms) {
    	$this->netTerms = $netTerms;
    }
    
    public function getTermDiscountDays() {
    	return $this->termDiscountDays;
    }
    
    public function setTermDiscountDays($termDiscountDays) {
    	$this->termDiscountDays = $termDiscountDays;
    }
    
    public function getDownloadURL() {
    	return $this->downloadURL;
    }
    
    public function setDownloadURL($downloadURL) {
    	$this->downloadURL = $downloadURL;
    }
    
    public function getDownloadUserID() {
    	return $this->downloadUserID;
    }
    
    public function setDownloadUserID($downloadUserID) {
    	$this->downloadUserID = $downloadUserID;
    }
    
    public function getDownloadPassword() {
    	return $this->downloadPassword;
    }
    
    public function setDownloadPassword($downloadPassword) {
    	$this->downloadPassword = $downloadPassword;
    }
    
    public function getDownloadFolder() {
    	return $this->downloadFolder;
    }
    
    public function setDownloadFolder($downloadFolder) {
    	$this->downloadFolder = $downloadFolder;
    }
    
    public function getUploadURL() {
    	return $this->uploadURL;
    }
    
    public function setUploadURL($uploadURL) {
    	$this->uploadURL = $uploadURL;
    }
    
    public function getUploadUserID() {
    	return $this->uploadUserID;
    }
    
    public function setUploadUserID($uploadUserID) {
    	$this->uploadUserID = $uploadUserID;
    }
    
    public function getUploadPassword() {
    	return $this->uploadPassword;
    }
    
    public function setUploadPassword($uploadPassword) {
    	$this->uploadPassword = $uploadPassword;
    }
    
    public function getUploadFolder() {
    	return $this->uploadFolder;
    }
    
    public function setUploadFolder($uploadFolder) {
    	$this->uploadFolder = $uploadFolder;
    }
    
    public function getUploadInventoryFolder() {
    	return $this->uploadInventoryFolder;
    }
    
    public function setUploadInventoryFolder($uploadInventoryFolder) {
    	if (!empty($uploadInventoryFolder)) {
    		$this->uploadInventoryFolder = $uploadInventoryFolder;
    	} else {
    		$this->uploadInventoryFolder = $this->getUploadFolder();
    	}
    }
    
    public function getShippingCarrier() {
    	return $this->getShippingInfo()->getShippingCarrier();
    }
    
    public function getShippingAccountNumber() {
    	return $this->getShippingInfo()->getCarrier()->getAccountNumber();
    }
    
    public function getShippingAccountZipCode() {
    	return $this->getShippingInfo()->getCarrier()->getAccountZipCode();
    }
    
    public function setShippingItemSKU($shippingItemSKU) {
    	$this->shippingItemSKU = $shippingItemSKU;
    }
    
    public function getShippingItemSKU() {
    	return $this->shippingItemSKU;
    }
    
    public function setShippingItemPrice($shippingItemPrice) {
    	$this->shippingItemPrice = $shippingItemPrice;
    }
    
    public function getShippingItemPrice() {
    	return $this->shippingItemPrice;
    }
    
    private function setRule($ruleID, $ruleName) {
    	$this->rules[$ruleID] = $ruleName;
    }
    
    public function getRule($ruleID) {
    
    }
    
    public function getVendorId() {
    	return $this->vendorId;
    }
    
    public function setVendorId($vendorId) {
    	$this->vendorId = $vendorId;
    }
    
    public function setRules($znectRules) {
    
    	if (!empty($znectRules)) {
    		foreach ($znectRules->znectRule as $currentRule) {
    			$this->setRule(intval($currentRule->ruleRefNo), (string) $currentRule->ruleName);
    		}
    	}
    }
    
    public function getRules() {
    	return $this->rules;
    }
    
    public function getRuleIDs() {
    
    	return array_combine(array_keys($this->rules), array_keys($this->rules));
    }
    
    public function getCartonLabelAdapter() {
    	return $this->cartonLabelAdapter;
    }
    
    public function getEmailFormat() {
    	return $this->emailFormat;
    }
    
    public function setEmailFormat($emailFormat) {
    	$this->emailFormat = $emailFormat;
    }
    public function getShipmentNAL() {
    	return $this->shipmentNAL;
    }
    public function setShipmentNAL($shipmentNAL) {
    	$this->shipmentNAL = $shipmentNAL;
    	return $this;
    }
    public function getInitialNST() {
    	return $this->initialNST;
    }
    public function setInitialNST($initialNST) {
    	$this->initialNST = $initialNST;
    	return $this;
    }
    
    public function getCustomerProfileId() {
    	return $this->customerProfileId;
    }
    public function setCustomerProfileId($customerProfileId) {
    	$this->customerProfileId = $customerProfileId;
    }
    public function getCustomerPaymentProfileId() {
    	return $this->customerPaymentProfileId;
    }
    public function setCustomerPaymentProfileId($customerPaymentProfileId) {
    	$this->customerPaymentProfileId = $customerPaymentProfileId;
    }
    
    public function setCartonLabelAdapter($value) {
    	$this->cartonLabelAdapter = $value;
    }
    
    public function getEdiLocationCode() {
    	return $this->ediLocationCode;
    }
    
    public function setEdiLocationCode($ediLocationCode) {
    	$this->ediLocationCode = $ediLocationCode;
    }
    
    public function getMessageTransfer() {
    	return $this->messageTransfer;
    }
    public function setMessageTransfer($messageTransfer) {
    	$this->messageTransfer = $messageTransfer;
    }
    public function getCustomPrice() {
    	return $this->customPrice;
    }
    public function setCustomPrice($customPrice) {
    	$this->customPrice = $customPrice;
    }
    
    private function setSellerPartnerWarehouses($array) {
    	$this->sellerPartnerWarehouses = $array;
    }
    
    public function getSellerPartnerWarehouses()    {
    	return $this->sellerPartnerWarehouses;
    }
    
    public function getSellerPartnerWarehouseId($sellerPartnerId)   {
    	if(empty($sellerPartnerId))
    		return null;
    		return $this->sellerPartnerWarehouses[$sellerPartnerId];
    }
}
