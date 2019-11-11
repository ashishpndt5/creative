<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShippingInfoController extends Controller
{
    private $shippingType;
    private $defaultPriority;
    private $carrier;
    private $shipComplete;
    private $fromAddress;
    private $packaging; //Multiple / Individual
    private $shippingCarrier;
    private $refCodeValuePairs;
        
    public function getRefCodeValuePairs(){
      return $this->refCodeValuePairs;
    }
    
    public function setRefCodeValuePairs($value){
      $this->refCodeValuePairs = $value;
    }
        
    public function getShippingCarrier() 
    {
      return $this->shippingCarrier;
    }
    
    public function setShippingCarrier($value) 
    {
      $this->shippingCarrier = $value;
    }

        
    public function getPackaging() 
    {
      return $this->packaging;
    }
    
    public function setPackaging($value) 
    {
      $this->packaging = $value;
    }
	    
	public function getFromAddress() 
	{
	  return $this->fromAddress;
	}
	
	public function setFromAddress($value) 
	{
	  $this->fromAddress = $value;
	}
    
	public function getShipComplete() 
	{
	  return $this->shipComplete;
	}
	
	public function setShipComplete($value) 
	{
	  $this->shipComplete = $value;
	}    
    
	public function getCarrier() 
	{
	  return $this->carrier;
	}
	
	public function setCarrier($value) 
	{
	  $this->carrier = $value;
	}

        
    public function getShippingType() 
    {
      return $this->shippingType;
    }
    
    public function setShippingType($value) 
    {
      $this->shippingType = $value;
    }
    
	public function getDefaultPriority() {
		return $this->defaultPriority;
	}
	public function setDefaultPriority($value) {
		$this->defaultPriority = $value;
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
