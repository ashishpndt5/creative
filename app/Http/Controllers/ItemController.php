<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ItemController extends Controller
{
	public $sku; //trader SKU
	public $id;
	public $partnerSku;
	public $description;
	public $lineNum;
	public $msg;
	public $qty;
	public $sdq;
	public $price;
	public $discountedPrice;
	public $total;
	public $inStock; //true if in stock, false if out of stock
	public $invalidSku; //true if
	public $manufacturer;
	public $weight;
	public $itemWarehouses = array();
	public $hasChanged;
	public $newOrderId; //foreign key of the order this item belongs to
	public $type;
	public $UPC;
	
	public function getUPC() {
		return $this->UPC;
	}
	
	public function setUPC($value) {
		$this->UPC = $value;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setType($value) {
		$this->type = $value;
	}
	
	public function getNewOrderId()
	{
		return $this->newOrderId;
	}
	
	public function setNewOrderId($value)
	{
		$this->newOrderId = $value;
	}
	 
	public function getHasChanged()
	{
		return $this->hasChanged;
	}
	
	public function setHasChanged($value)
	{
		$this->hasChanged = $value;
	}
	
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getManufacturer() {
		return $this->manufacturer;
	}
	
	public function setManufacturer($m) {
		$this->manufacturer = $m;
	}
	
	public function getPartnerSku() {
		return $this->partnerSku;
	}
	
	public function setPartnerSku($value) {
		$this->partnerSku = $value;
	}
	
	public function getInvalidSku() {
		return $this->invalidSku;
	}
	
	public function setInvalidSku($value) {
		$this->invalidSku = $value;
	}
	
	public function getInStock() {
		return $this->inStock;
	}
	
	public function setInStock($value) {
		$this->inStock = $value;
	}
	
	public function getTotal() {
		return $this->total;
	}
	
	public function setTotal($value) {
		$this->total = $value;
	}
	
	public function getPrice() {
		return $this->price;
	}
	
	public function setPrice($value) {
		$this->price = $value;
	}
	
	public function getDiscountedPrice() {
		return $this->discountedPrice;
	}
	
	public function setDiscountedPrice($value) {
		$this->discountedPrice = $value;
	
	}
	public function getQty() {
		return $this->qty;
	}
	
	public function setQty($value) {
		$this->qty = $value;
	}
	
	public function getSdq() {
		return $this->sdq;
	}
	
	public function setSdq($value) {
		$this->sdq = $value;
	}
	
	public function getMsg() {
		return $this->msg;
	}
	
	public function setMsg($value) {
		$this->msg = $value;
	}
	
	public function getLineNum() {
		return $this->lineNum;
	}
	
	public function setLineNum($value) {
		$this->lineNum = $value;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function setDescription($value) {
		$this->description = $value;
	}
	
	public function getDesc() { //backward compatibility
		return $this->getDescription();
	}
	
	public function setDesc($value) {//backward compatibility
		$this->setDescription($value);
	}
	
	public function getSku($skuChangeCase = TRUE) {
		if($skuChangeCase == TRUE) {
			return strtolower($this->sku);
		} else {
			return $this->sku;
		}
	}
	
	public function setSku($value) {
		$this->sku = $value;
	}
	
	public function getWeight() {
		return $this->weight;
	}
	
	public function setWeight($value) {
		$this->weight = $value;
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
	
	public function setItemWarehouses($itemWarehouse){
		if(!is_array($itemWarehouse)){ // must be an array
			return false;
		}
		$this->itemWarehouses = $itemWarehouse;
	}
	
	public function addItemWarehouse($itemWarehouse){
		$this->itemWarehouses[] = $itemWarehouse;
	}
	
	public function getItemWarehouses(){
		return $this->itemWarehouses;
	}
}
