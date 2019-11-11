<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{	
	
    /**
     * The table associated with the model.
     *
     * @var string
     */
	 
    protected $table = 'trader_partners';
	
	/**
     * The primary key associated with the table.
     *
     * @var string
     */
	 
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function getMerchantId() {
        return $this->merchantId;
    }
    
    public function setMerchantId($merchantId) {
        $this->merchantId = $merchantId;
    }
    
    public function getMarketPlaceId() {
        return $this->marketPlaceId;
    }
    
    public function setMarketPlaceId($marketPlaceId) {
        $this->marketPlaceId = $marketPlaceId;
    }        
        
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
