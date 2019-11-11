<?php

namespace App\Http\Controllers;

use App\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    private $logger;
    private $id;
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
    private $tradeInterface;
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

    function initializePartner($partnerInfo) {
        
        $logger = new Logger("log.txt", Logger::WARN);
                
        if (empty($partnerInfo)) {
            echo "Partner Info is empty";
            return;
        }
        //set partner values
        foreach ($partnerInfo as $varName => $value) {
            if (!is_numeric($varName)) {
                $this->set($varName, $value);
            }
        }
        //call the set functions explicitly to match older design
        $this->setUploadInventoryFolder($partnerInfo['uploadInventoryFolder']);
        $this->setEdiInterchangeQualifier($partnerInfo['ediQualifier']);
        $this->setPartnerVatNumber($partnerInfo['vatNumber']);
        $this->setEdiLocationCode($partnerInfo['ediLocationCode']);
        if (!empty($partnerInfo['ftpType'])) {
            $this->setFtpType((string) $partnerInfo['ftpType']);
        } else {
            $this->setFtpType("ftp");
        }
        if (!empty($partnerInfo['authenticationFIle'])) {
            //echo "../config/" . $partnerInfo['authenticationFIle'] . '.xml';
            $authInfo = simplexml_load_file("../config/" . $partnerInfo['authenticationFIle'] . '.xml');
            $tmp = (array) $authInfo->headers;
            foreach ($tmp['header'] as $h) {
                $this->setHeader((array) $h);
            }
            $this->setToken($authInfo->token->id);
            $this->setUrl($authInfo->url);
        }
        $this->setAS2Id($partnerInfo['AS2Id']);
        //if (isset($partnerInfo['genShippingLabel'])) {
        //  $this->setGenShippingLabel($partnerInfo['genShippingLabel']);
        //}
        if (!empty($partnerInfo['emailFormat'])) {
            $this->setEmailFormat($partnerInfo['emailFormat']);
        }
        if (!empty($partnerInfo['shipmentNAL'])) {
            $this->setShipmentNAL($partnerInfo['shipmentNAL']);
        }
        
        if (!empty($partnerInfo['initialNST'])) {
            $this->setInitialNST($partnerInfo['initialNST']);
        }
        if (!empty($partnerInfo['customerProfileId'])) {
            $this->setCustomerProfileId($partnerInfo['customerProfileId']);
        }
        if (!empty($partnerInfo['customerPaymentProfileId'])) {
            $this->setCustomerPaymentProfileId($partnerInfo['customerPaymentProfileId']);
        }

        if (!empty($partnerInfo['merchant_id'])) {
            $this->setMerchantId($partnerInfo['merchant_id']);
        }
        
        if (!empty($partnerInfo['marketplace_id'])) {
            $this->setMarketPlaceId($partnerInfo['marketplace_id']);
        }
        
        
        $this->setMessageTransfer ( $partnerInfo ['messageTransfer'] );
        $this->setCustomPrice( $partnerInfo ['customPrice'] );
        $traderId = $partnerInfo['traderId'];
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
}
