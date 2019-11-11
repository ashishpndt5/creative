<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AddressController extends Controller
{
    private $firstName;
    private $lastName;
    private $company;
    private $phone;
    private $street1;
    private $street2;
    private $city;
    private $stateAbbrev;
    private $zip;
    private $country;
    private $addressType;
    private $isDefaultShipping;
    private $isDefaultBilling;
	private $warehouseID;
	private $newOrderId; // foreign key for the order this address belongs to
	private $id;

	public function getId() {
	  return $this->id;
	}
	
	public function setId($value) {
	  $this->id = $value;
	}
	    
	public function getNewOrderId() 
	{
	  return $this->newOrderId;
	}
	
	public function setNewOrderId($value) 
	{
	  $this->newOrderId = $value;
	}
	    
	
    function __construct() {
        $this->setCountry("");
    }

    public function getName() {
    	return $this->getFirstName() . " " . $this->getLastName();
    }
    
    function initialize() {
        $this->firstName = trim($passedArray['firstName']);
        $this->lastName = trim($passedArray['lastName']);
        $this->company = $passedArray['company'];
        $this->phone = $passedArray['telephoneNumber'];
        $this->street1 = trim($passedArray['street1']);
        $this->street2 = trim($passedArray['street2']);
        $this->city = $passedArray['city'];
        $this->stateAbbrev = $passedArray['stateAbbrev'];
        $this->zip = $passedArray['zip'];
        $this->country = $passedArray['country'];
        $this->addressType = $passedArray['addressType'];
        $this->isDefaultShipping = $passedArray['isDefaultShipping'];
        $this->isDefaultBilling = $passedArray['isDefaultBilling'];

    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    public function getCompany() {
        return $this->company;
    }

    public function setCompany($company) {
        $this->company = $company;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function setPhone($telephoneNumber) {
        $this->phone = $telephoneNumber;
    }

    public function getStreet1() {
        return $this->street1;
    }

    public function setStreet1($street1) {
        $this->street1 = $street1;
    }

    public function getStreet2() {
        return $this->street2;
    }

    public function setStreet2($street2) {
        $this->street2 = $street2;
    }

    public function getCity() {
        return $this->city;
    }

    public function setCity($city) {
        $this->city = $city;
    }

    public function getStateAbbrev() {
        return $this->stateAbbrev;
    }

    public function setStateAbbrev($stateAbbrev) {
        $this->stateAbbrev = $stateAbbrev;
    }

    public function getZip() {
        return $this->zip;
    }

    public function setZip($zip) {
        $this->zip = $zip;
    }

    public function getCountry() {
        return $this->country;
    }

    public function setCountry($country) {
        if(empty($country) || !isset($country) || strtoupper($country)=="USA"){
           $this->country = "US";
           return;
        }
        if($country=="UK"){
            $country = "GB";
        }
        $this->country = $country;
    }

    public function getAddressType() {
        return $this->addressType;
    }

    public function setAddressType($addressType) {
        $this->addressType = $addressType;
    }

    public function getIsDefaultShipping() {
        return $this->isDefaultShipping;
    }

    public function setIsDefaultShipping($isDefaultShipping) {
        $this->isDefaultShipping = $isDefaultShipping;
    }

    public function getIsDefaultBilling() {
        return $this->isDefaultBilling;
    }

    public function setIsDefaultBilling($isDefaultBilling) {
        $this->isDefaultBilling = $isDefaultBilling;
    }

    public function getWarehouseID()
    {
    	return $this->warehouseID;
    }
    
    public function setWarehouseID($value)
    {
    	$this->warehouseID = $value;
    }
    
    public function get($property) {
    	if($property == "name"){
    		return ( $this->firstName . $this->lastName);
    	}
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
