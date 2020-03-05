<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class OrbitDBAdapterController extends Controller
{
    public function __construct() {
    	
    }
	public function putOrder($order) {
		
		$partner = $order->getPartner();
		$orderTrader = $order->getTrader();
		$checkSku = $orderTrader->getCheckSku();
		//$checkSku = $orderTrader['checkSku'];
		//$checkSku = $order->getTrader()->getCheckSku();
	
		//if($orderTrader['id'] == "14" || $orderTrader['id'] == "15"){
		if($orderTrader->getId() == "14" || $orderTrader->getId() == "15") {
			$checkSku =  false;
		}
	
		if($checkSku && !$order->getReRun()){
			$resultArr = $this->isSkuAvailable($order);
		}else{
			$resultArr = $this->setSkuAvailable($order);
		}
		$arrProducts = array();
	
		if (count($resultArr['inStockItem']) > 0) {
			$arrProducts = $resultArr['inStockItem'];
		}
		$resultArr;
		//if (((integer) $partner->getShipComplete()) && !($resultArr['is_in_stock'])) {
		//if (((integer) $partner['shipComplete']) && !($resultArr['is_in_stock'])) {
		if (!($resultArr['is_in_stock'])) {
				
			if (isset($resultArr['exceptionMessage'])) {//count of out of stock ,,,, count of invalid
				$resp["errorMessage"] = $resultArr['exceptionMessage'];
			} else {
				$resp["errorMessage"] = '';
				if (isset($resultArr['outOfStockSKU'])) {
					$resp["errorMessage"] = "Received order for out of stock sku " . implode(',', $resultArr['outOfStockSKU']);
				}
				if (isset($resultArr['wrongSKU'])) {
					$resp["errorMessage"] .= " Received order for incorrect sku " . implode(',', $resultArr['wrongSKU']);
				}
			}
			$resp["isError"] = true;
			//$this->logger->LogWarn("znectDBAdapter:putOrder: " . $resp["errorMessage"]);
			Log::warning("znectDBAdapter:putOrder: " . $resp["errorMessage"]);
			
			return $resp;
		} //elseif (!((integer) $partner->getShipComplete())) {
			else {
			if(isset($resultArr['outOfStockSKU'])){
				$resp['outOfStock'] = $resultArr['outOfStockSKU'];
			}elseif(isset($resultArr['wrongSKU'])){
				$resp['wrongSKU'] = $resultArr['wrongSKU'];
			}
		}
		if (count($arrProducts) > 0) {
			$resp["errorMessage"] = "";
			$resp['isError'] = false;
			return $resp;	
		} else {
			$resp["isError"] = true;
			$resp["errorMessage"] = "No ordered products in stock";
			//$this->logger->LogWarn("znectDBAdapter:putOrder: " . $resp["errorMessage"]);
			Log::warning("znectDBAdapter:putOrder: " . $resp["errorMessage"]);
		}
		return $resp;
	}
	
	public function isSkuAvailable($order) {
		
		$passedItems = $order->getItems(); //items are updated in this method
		foreach ($passedItems as $oi) {
			$skuChangeCase = $order->getPartner()->getSkuChangeCase();
			//$skuChangeCase = $order->getPartner ()['skuChangeCase'];
			$passedSku[] = $oi->getSku($skuChangeCase);
		}
		try {
			//$tr = $order->getTrader()['id'];
			$inventory = $this->getInventory($order->getTrader()->getId(), 0 /*partner 0*/, $passedSku);
			//foreach($inventory as $p){
			if($inventory && count($inventory) > 0) {
				$pItem['sku'] = strtolower($inventory['sku']); //strtolower($p->getSku());
				$pItem['product_id'] = $inventory['id'];
				$pItem['qty'] = intval ($inventory['inventory']);
				$pItem['qty'] > 0 ? $pItem['is_in_stock'] = true :$pItem['is_in_stock'] = false;
				$returnSku[] = $pItem;
			}
				
			$retArr = array(); //
			$retArr["is_in_stock"] = true; //
			foreach ($passedItems as $passedItem) {
				$found = false;
				foreach ($returnSku as $ak => $returnItem) {
					$passedItemSku = strtolower($passedItem->getSku());
					$index = array_search($passedItemSku, $returnItem);
					if ($index == 'sku') {
						if (($returnItem['qty']) > 0 &&
								($returnItem['is_in_stock'] == true) &&
								intval($returnItem['qty']) >= intval($passedItem->getQty())) {
									$passedItem->setInStock(true);
									$passedItem->setHasChanged(true);
									$order->setHasInStockItems(true);
									if($order->getPartner()->getCustomPrice() == "yes") {
									//if($order->getPartner()->getCustomPrice() == "yes") {
										$retArr["inStockItem"][] = array(
												'product_id' => $returnItem['product_id'],
												'qty' => intval($passedItem->getQty()),
												'custom_price' => $passedItem->getPrice()
										);
									} else {
										$retArr["inStockItem"][] = array(
												'product_id' => $returnItem['product_id'],
												'qty' => intval($passedItem->getQty())
										);
									}
									$returnItem['qty'] = intval ($returnItem['qty']) - intval($passedItem->getQty());
								} else {
									$passedItem->setInStock(false);
									$passedItem->setType('denySku');
									$retArr["is_in_stock"] = false;
									$order->setHasOutofStockItems(true);
									$passedItem->setHasChanged(true);
									$retArr["outOfStockSKU"][] = $passedItem->getSku($order->getPartner ()->getSkuChangeCase ());
								}
								$found = true;
					}
				}
				if (!$found) {
					$passedItem->setInStock(false);
					$order->setHasOutofStockItems(true);
					$passedItem->setType('denySku');
					$passedItem->setInvalidSku(true);
					$passedItem->setHasChanged(true);
					$retArr["is_in_stock"] = false;
					$retArr["wrongSKU"][] = $passedItem->getSku();
				}
			}
		} catch (Exception $e) {
			$retArr["is_in_stock"] = false;
			$order->setHasOutofStockItems(true);
			$retArr["exceptionMessage"] = $e->getMessage();
			//$this->logger->LogWarn("znectDBAdapter:isSkuAvailable: ". $retArr["exceptionMessage"]);
			Log::warning("znectDBAdapter:isSkuAvailable: ". $retArr["exceptionMessage"]);
			return false;
		}
	
		return $retArr;
	}
	
	public function setSkuAvailable($order) {
		$passedItems = $order->getItems(); //items are updated in this method
		foreach ($passedItems as $oi) {
			$passedSku[] = $oi->getSku($order->getPartner ()->getSkuChangeCase ());
		}
		$retArr = array(); //
		$retArr["is_in_stock"] = true; //
		foreach ($passedItems as $passedItem) {
			$passedItem->setInStock(true);
			$passedItem->setHasChanged(true);
			$retArr["inStockItem"][] = array(
					'product_id' => $passedItem->getSku(),//$returnItem['product_id'],
					'qty' => intval($passedItem->getQty())
			);
		}
	
		return $retArr;
	}
	
	public function invoiceOrder($order){
		return true;
	}
}
