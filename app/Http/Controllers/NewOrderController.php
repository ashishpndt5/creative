<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewOrderController extends Controller
{
	public $trader;
	public $partner; //buyer
	public $sellerPartner; //seller
	public $couponCode;
	public $backOrderType;
	public $shipDate;
	public $cancelDate;
	public $saAddress;
	public $baAddress;
	public $paymentType;
	public $poNumber;
	public $dcNumber;
	public $poDate;
	public $soNumber;
	public $items = array();
	//---- added 22-03-2016 ----
	public $rejectedItems = array();
	public $regenrateLabels;
	//------ added ------
	public $incompleteOrder;
	public $shipType;
	public $shipmentCarrier;
	public $shipmentPriority;
	public $orderComments;
	public $accountType;
	public $subTotal;
	public $taxAmount;
	public $shippingAmount;
	public $grandTotal;
	public $createdDate;
	public $ediTrasactionSetControlNumber;
	public $ediTransactionSetIdentifierCode = 1;
	public $ediTransactionType;
	public $statusHistory = array();
	public $shipment;
	public $rawOrder;
	public $hasOutofStockItems;
	public $shippingLabelFileName;
	public $packingSlipFileName;
	public $vatNumber;
	public $orderOutData;
	protected $logger;
	public $shippingLabel;
	public $packingSlip;
	public $id;
	public $hasChanged;
	public $generateShippingLabel; // renamed genShippingLabel for database persisence purposes.
	public $warehouseId; //"single or multiple"
	public $sellerParnterId;
	public $genCartonLabel = false;
	public $erpCustomerGroupId;
	public $erpCustomerEmail;
	public $erpDataObject;  // for magento2 object
	public $traderId;
	public $partnerId;
	public $status;
	public $invoiceNumber;
	public $hasInStockItems = false;
	public $shipByDate;
	public $reRun = false;
	
	function __construct() {
		//$this->logger = $logger;
	}
	
	public function getReRun() {
		return $this->reRun;
	}
	
	public function setReRun($value) {
		$this->reRun = $value;
	}
	 
	public function getHasInStockItems() {
		return $this->hasInStockItems;
	}
	
	public function setHasInStockItems($value) {
		$this->hasInStockItems = $value;
	}
	 
	public function getShipByDate() {
		return $this->shipByDate;
	}
	
	public function setShipByDate($value) {
		$this->shipByDate = $value;
	}
	
	public function getStatus()
	{
		return $this->status;
	}
	
	public function setStatus($value)
	{
		$this->status = $value;
	}
	 
	public function getPartnerId() {
		return $this->partnerId;
	}
	
	public function setPartnerId($value) {
		$this->partnerId = $value;
	}
	
	public function getTraderId() {
		return $this->traderId;
	}
	
	public function setTraderId($value) {
		$this->traderId = $value;
	}
	
	public function getErpDataObject(){
		return $this->erpDataObject;
	}
	
	public function setErpDataObject($erpDataObject){
		$this->erpDataObject = $erpDataObject;
	}
	
	public function getErpCustomerGroupId(){
		return $this->erpCustomerGroupId;
	}
	
	public function setErpCustomerGroupId($erpCustomerGroupId){
		$this->erpCustomerGroupId = $erpCustomerGroupId;
	}
	
	public function getErpCustomerEmail(){
		return $this->erpCustomerEmail;
	}
	
	public function setErpCustomerEmail($erpCustomerEmail){
		$this->erpCustomerEmail = $erpCustomerEmail;
	}
	
	public function getGenCartonLabel() {
		return $this->genCartonLabel;
	}
	
	public function setGenCartonLabel($value) {
		$this->genCartonLabel = $value;
	}
	public function getSellerParnterId()
	{
		return $this->sellerParnterId;
	}
	
	public function setSellerParnterId($value)
	{
		$this->sellerParnterId= $value;
	}
	
	
	public function getWarehouseId()
	{
		return $this->warehouseId;
	}
	
	public function setWarehouseId($value)
	{
		$this->warehouseId = $value;
	}
	 
	public function getGenerateShippingLabel()
	{
		return $this->generateShippingLabel;
	}
	
	public function setGenerateShippingLabel($value)
	{
		$this->generateShippingLabel = $value;
	}
	 
	public function getHasChanged()
	{
		return $this->hasChanged;
	}
	
	public function setHasChanged($value)
	{
		$this->hasChanged = $value;
	}
	 
	public function getId()
	{
		return $this->id;
	}
	 
	public function setId($value)
	{
		$this->id = $value;
	}
	
	
	public function getVatNumber() {
		return $this->vatNumber;
	}
	
	public function setVatNumber($vatNumber) {
		$this->vatNumber = $vatNumber;
	}
	
	
	public function getPackingSlipFileName()
	{
		return $this->packingSlipFileName;
	}
	
	public function setPackingSlipFileName($value)
	{
		$this->packingSlipFileName = $value;
	}
	
	
	public function getShippingLabelFileName(){
		return $this->shippingLabelFileName;
	}
	
	public function setShippingLabelFileName($value){
		$this->shippingLabelFileName = $value;
	}
	
	//not a getter. calculates/sets/returns generateShippingLabel propery
	public function getGenShippingLabel(){
		if(null !== $this->getGenerateShippingLabel()){ //run only once
			return $this->getGenerateShippingLabel();
		}  //TODO: Review - should function calculate the flag whenever it is invoked?
		if(!isset($this->sellerPartner)){
			$errMsg = "SellerPartner is not set for order:  " . $this->poNumber;
			$this->logger->LogError("NewOrder:getGenShippingLabel: " . $errMsg);
			throw new Exception($errMsg);
		}
		$db = new ediSqlNew($this->trader);
		$scacArr = $db->getCarrierShippingCodeInfo ( $this->getTrader()->getId (), $this->getShipmentCarrier () );
		$carrier = strtoupper($scacArr['partner_id']);
		if(strtoupper($this->sellerPartner->getGenShippingLabel()) == 'NO'){
			$this->setGenerateShippingLabel(false);
		}elseif ($carrier == "FEDEX" || $carrier == "UPS") {
			$this->setGenerateShippingLabel(true);
		}else{
			$this->setGenerateShippingLabel(false);
		}
		return $this->getGenerateShippingLabel();
	}
	
	public function setGenShippingLabel($value){
		return $this->setGenerateShippingLabel($value);
	}
	
	public function getHasOutofStockItems()
	{
		return $this->hasOutofStockItems;
	}
	
	public function setHasOutofStockItems($value)
	{
		$this->hasOutofStockItems = $value;
	}
	
	
	
	
	public function getStatusHistory() {
		return $this->statusHistory;
	}
	
	public function setStatusHistory($statusHistory) {
		$this->statusHistory = $statusHistory;
	}
	
	public function getShipment() {
		return $this->shipment;
	}
	
	public function setShipment(shipment $shipment) {
		$this->shipment = $shipment;
	}
	
	public function getRawOrder() {
		return $this->rawOrder;
	}
	
	public function setRawOrder($rawOrder) {
		$this->rawOrder = $rawOrder;
	}
	
	public function getEdiTrasactionSetControlNumber() {
		return $this->ediTrasactionSetControlNumber;
	}
	
	public function setEdiTrasactionSetControlNumber($ediTrasactionSetControlNumber) {
		$this->ediTrasactionSetControlNumber = $ediTrasactionSetControlNumber;
	}
	
	public function getEdiTransactionSetIdentifierCode() {
		return $this->ediTransactionSetIdentifierCode;
	}
	
	public function setEdiTransactionSetIdentifierCode($ediTransactionSetIdentifierCode) {
		$this->ediTransactionSetIdentifierCode = $ediTransactionSetIdentifierCode;
	}
	
	public function getEdiTransactionType() {
		return $this->ediTransactionType;
	}
	
	public function setEdiTransactionType($ediTransactionType) {
		$this->ediTransactionType = $ediTransactionType;
	}
	
	public function getPartner() {
		return $this->partner;
	}
	
	public function setPartner($partner) {
		$this->partner = $partner;
	}
	
	public function getSellerPartner() {
		return $this->sellerPartner;
	}
	
	public function setSellerPartner($partner) {
		$this->sellerPartner = $partner;
	}
	
	public function getCompanyName() {
		return $this->companyName;
	}
	
	public function setCompanyName($companyName) {
		$this->companyName = $companyName;
	}
	
	public function getTrader() {
		return $this->trader;
	}
	
	public function setTrader($trader) {
		$this->trader = $trader;
	}
	
	public function getCouponCode() {
		return $this->couponCode;
	}
	
	public function setCouponCode($couponCode) {
		$this->couponCode = $couponCode;
	}
	
	public function getBackOrderType() {
		return $this->backOrderType;
	}
	
	public function setBackOrderType($backOrderType) {
		$this->backOrderType = $backOrderType;
	}
	
	public function getShipDate() {
		return $this->shipDate;
	}
	
	public function setShipDate($shipDate) {
		$this->shipDate = $shipDate;
	}
	
	public function getCancelDate() {
		return $this->cancelDate;
	}
	
	public function setCancelDate($cancelDate) {
		$this->cancelDate = $cancelDate;
	}
	
	public function getSaAddress() {
		return $this->saAddress;
	}
	
	public function setSaAddress($saAddress) {
		$this->saAddress = $saAddress;
	}
	
	public function getBaAddress() {
		return $this->baAddress;
	}
	
	public function setBaAddress($baAddress) {
		$this->baAddress = $baAddress;
	}
	
	public function getPaymentType() {
		return $this->paymentType;
	}
	
	public function setPaymentType($paymentType) {
		$this->paymentType = $paymentType;
	}
	
	public function getPoNumber() {
		return $this->poNumber;
	}
	
	public function setPoNumber($poNumber) {
		$this->poNumber = $poNumber;
	}
	
	public function getDCNumber() {
		return $this->dcNumber;
	}
	
	public function setDCNumber($dcNumber) {
		$this->dcNumber = $dcNumber;
	}
	
	
	public function getPoDate() {
		return $this->poDate;
	}
	
	public function setPoDate($poDate) {
		$this->poDate = $poDate;
	}
	
	public function getSoNumber() {
		return $this->soNumber;
	}
	
	public function setSoNumber($soNumber) {
		$this->soNumber = $soNumber;
	}
	
	public function getItems() {
		return $this->items;
	}
	
	public function setItems($items) {
		$this->items = $items;
	}
	
	//---added 22-03-2016 ---
	public function getRejectedItems() {
		return $this->rejectedItems;
	}
	
	public function setRejectedItems($rejectedItems) {
		$this->rejectedItems = $rejectedItems;
	}
	
	public function getRegenerateLabels() {
		return $this->regenrateLabels;
	}
	
	public function setRegenerateLabels($regenrateLabels) {
		$this->regenrateLabels = $regenrateLabels;
	}
	//---- added 22-03-2016
	
	public function getIncompleteOrder() {
		return $this->incompleteOrder;
	}
	
	public function setIncompleteOrder($incompleteOrder) {
		$this->incompleteOrder = $incompleteOrder;
	}
	
	public function getShipType() {
		return $this->shipType;
	}
	
	public function setShipType($shipType) {
		$this->shipType = $shipType;
	}
	
	public function getShipmentCarrier() {
		return $this->shipmentCarrier;
	}
	
	public function setShipmentCarrier($shipmentCarrier) {
		$this->shipmentCarrier = $shipmentCarrier;
	}
	
	public function getShipmentPriority() {
		return $this->shipmentPriority;
	}
	
	public function setShipmentPriority($shipmentPriority) {
		$this->shipmentPriority = $shipmentPriority;
	}
	
	public function getOrderComments() {
		return $this->orderComments;
	}
	
	public function setOrderComments($orderComments) {
		$this->orderComments = $orderComments;
	}
	
	public function getAccountType() {
		return $this->accountType;
	}
	
	public function setAccountType($accountType) {
		$this->accountType = $accountType;
	}
	
	public function getSubTotal() {
		return $this->subTotal;
	}
	
	public function setSubTotal($subTotal) {
		$this->subTotal = $subTotal;
	}
	
	public function getTaxAmount() {
		return $this->taxAmount;
	}
	
	public function setTaxAmount($taxAmount) {
		$this->taxAmount = $taxAmount;
	}
	
	public function getShippingAmount() {
		return $this->shippingAmount;
	}
	
	public function setShippingAmount($shippingAmount) {
		$this->shippingAmount = $shippingAmount;
	}
	
	public function getGrandTotal() {
		return $this->grandTotal;
	}
	
	public function setGrandTotal($grandTotal) {
		$this->grandTotal = $grandTotal;
	}
	
	public function getCreatedDate() {
		return $this->createdDate;
	}
	
	public function setCreatedDate($createdDate) {
		$this->createdDate = $createdDate;
	}
	
	public function addItem($upc, $qty, $price, $itemDesc) {
	
		$item = new Item();
		$item->setSku($upc);
		$item->setQty($qty);
		$item->setPrice($price);
		$item->setDesc($itemDesc);
		 
		$this->items[count($this->items)] = $item;
	}
	
	public function addItemObject($item) {
		$this->items[] = $item;
	}
	
	public function updateSKUs($skuMapping) {
		foreach($this->getItems() as $item){
			$sku = $item->getSku($this->getPartner()->getSkuChangeCase ());
			if (array_key_exists($sku, $skuMapping)) {
				$item->setSku($skuMapping[$sku]);
			}
		}
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
	public function getOrderOutData() {
		return $this->orderOutData;
	}
	public function setOrderOutData($orderOutData) {
		$this->orderOutData = $orderOutData;
		return $this;
	}
	public function getShippingLabel() {
		return $this->shippingLabel;
	}
	public function setShippingLabel($shippingLabel) {
		$this->shippingLabel = $shippingLabel;
		return $this;
	}
	public function getPackingSlip() {
		return $this->packingSlip;
	}
	public function setPackingSlip($packingSlip) {
		$this->packingSlip = $packingSlip;
		return $this;
	}
	public function getInvoiceAmount() {
		return $this->invoiceAmount;
	}
	public function setInvoiceAmount($invoiceAmount) {
		$this->invoiceAmount = $invoiceAmount;
		return $this;
	}
	 
	public static function splitOrdersByWarehouse($orders){
		$splitOrders = array(); //post split orders
		foreach($orders as $order){
			if(strtolower($order->getWarehouseId()) != "multiple"){ //all items belong to one warehouse.. nothing to split
				$splitOrders[] = $order;
				continue;
			}
			$warhouseOrderItems = array(); // map wareshouse:orderItem
			$warehouseOrders = array(); // map warehouse:order
			foreach($order->getItems() as $item){
				foreach($item->getItemWarehouses() as $itemWarehouse){
					if(!empty($warehouseOrders) && is_object($newOrder = $warehouseOrders[$itemWarehouse->getWarehouseId()])){//an existing post split order found for the warehouse
						$newItem = clone $item;//shallow copy
						$newItem->setItemWarehouses(array($itemWarehouse));
						$newOrder->addItemObject($newItem);//add item to the pre-split order items
					}else{//no order for the specific warehouse found before
						$newOrder = clone $order;//shallow copy
						$newItem = clone $item;//shallow copy
	
						$newOrder->setItems(array($newItem));//set new item array with the pre-split order item
						$newItem->setItemWarehouses(array($itemWarehouse));//set new itemWarehouse array with the pre-split order itemWarehouse
	
						$warehouseOrders[$itemWarehouse->getWarehouseId()] = $newOrder;
						$splitOrders[] = $newOrder;
					}
				}
			}
		}
		return $splitOrders;
	}
	 
	public function getItemLineNumberFromPo($orderItem,$item,$orderId,$partner) {
		$db = new ediSqlNew($this->getTrader());
		if(!empty($partner)) {
			$itemDetailsFrom850 = $db->getItemInformationFrom850(
					$this->getTrader()->getId(),
					$partner->getId(),
					$orderId);
			if($itemDetailsFrom850 != false) {
				$buyerLineNumber = $itemDetailsFrom850[$orderItem['sku']]['buyer_line_number'];
				$item->setLineNum($buyerLineNumber);
			}
		}
	}
	
	public function setInvoiceNumber($value) {
		$this->invoiceNumber = $value;
	}
	
	public function getInvoiceNumber() {
		if(empty($this->invoiceNumber)) {
			return  $this->getSoNumber();
		}
		return $this->invoiceNumber;
	}
	
	public static function splitOrder($order, $itemMfMap){
		foreach($itemMfMap as $mfr => $items){
			$newOrder = clone $order;//shallow copy
			$newOrder->setItems($items);
			$splitOrders[] = $newOrder;
			$newOrder->setSellerPartner($order->getTrader()->getPartnerByName($mfr, 'seller'));
		}
		 
		return $splitOrders;
	}
	 
	public static function splitOrdersByManufacturer($orders, $db){
		$splitOrders = array(); //post split orders
		foreach($orders as $order){
			$items = $order->getItems();
			$itemMfMap = array();
			foreach($items as $item){
				if(!isset($skMfMap[$item->getSku()])){ //build sku mfr map cache
					$product = $db->getProductDetails(null, $item->getSku(), $bySku = true);
					if($product == false){
						$skMfMap[$item->getSku()] = $order->getTrader()->getPartner(0)->getName(); //default partner 0
					}else{
						$m = $product[0]->getManufacturer();
						if(empty($m)){
							$skMfMap[$item->getSku()] = $order->getTrader()->getPartner(0)->getName(); //default partner 0
						}else{
							$skMfMap[$item->getSku()] = $product[0]->getManufacturer();}
					}
				}
				$itemMfMap[$skMfMap[$item->getSku()]][] = $item;
				 
			}
			if(count($itemMfMap) > 1){ //multiple manufacturers.. need to split orders
				$clonedOrders = newOrder::splitOrder($order, $itemMfMap);
				$splitOrders = array_merge($splitOrders, $clonedOrders);
			}else{
				$splitOrders[] = $order; //no splitting required
				$mfr = key($itemMfMap);
				$order->setSellerPartner($order->getTrader()->getPartnerByName($mfr, 'seller'));
			}
			unset($itemMfMap);
		}
		return $splitOrders;
	}
	
	public function getTransactionSetIdentifierCode() {
		return $this->ediTransactionSetIdentifierCode;
	}
	
	public function setTransactionSetIdentifierCode($transactionSetIdentifierCode) {
		$this->ediTransactionSetIdentifierCode = $transactionSetIdentifierCode;
	}
	
	public function setOrderArray($order) {
		$this->orderArray[] = $order;
	}
	public function getOrderArray() {
		return $this->orderArray;
	}
}
