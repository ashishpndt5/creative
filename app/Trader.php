<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trader extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
	 
    

	 protected $table = 'traders';
	 
	 /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
	
	 /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
	
	/**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','name','email_address','erp_adapter','erp_domain','erp_path','erp_user','erp_password','edi_interchange_id','edi_interchange_qualifier','duns_number','q_qrder_database_url','q_qrder_database_user_name','q_qrder_database_password','q_qrder_database_name','phone_number','order_email_address','street1','street2','street3','city','state','zip','country','defaultWarehouseID','packingSlipGenTiming','clientId','clientSecret','redirectUri','accessToken','folderId','erpAdapterVersion','vatCheckRequired','addressCheckRequired','AS2Id','venderIdOrderFile','checkSku'];

    /**
     * Get the comments for the blog post.
     */
    public function trader_partners () {
        //return $this->hasMany('App\TraderPartner');
        return $this->hasMany('App\TraderPartner', 'trader_id');
       // return $this->hasMany(TraderPartner::class);
    }

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

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

    public function initializeByXML($passedXMLFileName, $passedID, $source = "XML") {
        $IDtype = "id";
        $this->initializeFromDB($passedID);
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
}
