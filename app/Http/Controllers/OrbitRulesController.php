<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Log;

class OrbitRulesController extends Controller
{
	private $rules = array();
	private $rulesToApply = array();
	public $partner;
	public $trader;
	private $order;
	private $product;
	private $rulesOutput = array();
	private $db;
	private $lastSql = array();
	private $lastResultSet = array();
	
    public function __construct($trader, $partner) {
    	$dd;
    	$this->partner = $partner;
    	$this->trader = $trader;
    	$rulesXML = simplexml_load_file("../config/zNectRules.xml");
    	foreach ($rulesXML->Rule as $rule) {
    		$this->rules[intval($rule->RuleNumber)] = $rule;
    	}
    	$partnerRuleIds = $partner->getRuleIDs();
    	$this->rulesToApply = array_intersect_key($this->rules, $partnerRuleIds);
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
	
	public function applyRules($object) {
		$parentType = get_parent_class($object);
		if ($parentType) {
			$objectType = $parentType;
			$objectBaseClass = class_basename($object);
		} else {
			$objectType = get_class($object);
			$objectBaseClass = class_basename($object);
		}
		switch ($objectBaseClass) {
			case 'NewOrderController':
	
				try {
					$this->order = $object;
					//loop through each rule and apply to newOrder
	
					foreach ($this->rulesToApply as $rule) {
						echo "Rule " . $rule->RuleName . " is to be applied.\n";
						if ((string) $rule->Class != 'newOrder')
							continue;
							switch ($rule->RuleType) {
								case "VALIDATION":
									$this->validateOrder($rule);
									break;
								case "UPDATE":
									$this->updateOrder($rule);
									break;
								case "ADD":
									$this->addToOrder($rule);
									break;
								default;
								echo "$rule->RuleNumber is unclassified rule\n";
							}
					}
				} catch (Exception $exc) {
					echo $exc->getTraceAsString();
					throw $exc;
				}
				//echo "PRINTING ORDER WITHIN APPLY RULES\n";
				//print_r($this->order);
				return $this->order;
			case 'product':
				$this->product = $object;
	
				if(empty($this->rulesToApply)){
					return isset($this->product) ? $this->product : null;
				}
				try {
	
					foreach ($this->rulesToApply as $rule) {
						if ((string) $rule->Class != 'product')
							continue;
							//echo "Rule " . $rule->RuleName . " ".$this->product->getSku(). " is to be applied.\n";
							switch ($rule->RuleType) {
								case "VALIDATION":
									//$this->validateProduct($rule);
									break;
								case "UPDATE":
									if($this->product) {
										$this->updateProduct($rule);
										break;
									} else {
										break;
									}
								case "ADD":
									echo "ADD operation not available.";
									//$this->addToProduct($rule);
									break;
								case "DELETE":
									$this->deleteProduct($rule);
									break;
								default;
								echo "$rule->RuleNumber is unclassified rule\n";
							}
					}
				} catch (Exception $exc) {
					echo $exc->getTraceAsString();
					throw $exc;
				}
				return isset($this->product) ? $this->product : null;
		}
	}
	
	private function validateOrder($rule) {
		
		$isArray = strcasecmp($rule->isArray, 'TRUE') == 0 ? true : false;
		$sql = (string) $rule->SQL;
		$arrayName = $rule->ArrayName;
		$varName = $rule->VariableName;
		$operator = $rule->Operator;
		//$evalexp = (string)$rule->EvalExpression;
		$setObjectName = (string) $rule->Class;
		$setFunctionName = (string) $rule->FunctionName;
		//check if sql needs trader id or partner id
		$traderID = (string) $this->order->getTrader()->getId();
		if (strtolower($rule->PartnerType != "seller")) {
			$partnerID = (string) $this->order->getPartner()->getId();
		} else {
			$partnerID = (string) $this->order->getSellerPartner()->getId();
		}
		//replacre trader id and partner id in sql for $trader_id and $partner_id
		$sql = str_replace("\$trader_id", $traderID, $sql);
		$sql = str_replace("\$partner_id", $partnerID, $sql);
		$sql = str_replace("\$msgType", $setObjectName, $sql);
	
		if ($isArray) {
			if (strcasecmp($arrayName, 'items') == 0) {
				foreach ($this->order->getItems() as $item) {
					$qty = $item->getQty();
					switch ($varName) {
						case "sku":
							$itemVal = $item->getSku();
							break;
						case "qty":
							$itemVal = $item->getQty();
							break;
						case "price":
							$itemVal = $item->getPrice();
							break;
						default:
							//TODO: Raise an exception
							echo "unknow value: " . $varName;
							break;
					}
					$rightExp = $this->db->getRulePackageDivisibleFromDB($sql);
	
					$leftExpValue = $itemVal;
					$result = $this->checkExpression($leftExpValue, $operator, $rightExp);
					if ($setObjectName == 'newOrder') {
						if (!$result) {
							$itemReject = new Item ();
							$itemReject->setLineNum ( $item->getLineNum() );
							$itemReject->setQty ( $item->getQty() );
							$itemReject->setPrice ( $item->getPrice() );
							$itemReject->setSku ( trim ( $item->getSku() ) );
							$itemsRejectArray [] =  $itemReject;
							//unset($this->getItems()[$item]);
							//$item->setQty(0);
						} else {
							$itemnew = new Item ();
							$itemnew->setLineNum ( $item->getLineNum() );
							$itemnew->setQty ( $item->getQty() );
							$itemnew->setPrice ( $item->getPrice() );
							$itemnew->setSku ( trim ( $item->getSku() ) );
							$itemsArray [] =  $itemnew;
						}
					}
				}
				 
			}
			$this->order->setItems ( $itemsArray );
			if(!empty($itemsRejectArray)){
				$this->order->setRejectedItems ( $itemsRejectArray );
			}
		}
	}
	
	private function updateOrder($rule) {
		$source = $rule->UpdateSource;
		if ($source == 'Class') {
			$this->updateOrderFromClass($rule);
		} else if ($source == 'DB') {
			$this->updateOrderFromDB($rule);
		} else if ($source == 'Expression') {
			//$this->updateObjectFromeExp($rule);
		}
		//return $newOrder;
	}
	
	private function updateOrderFromClass($rule) {
		$getObjectName = (string) $rule->UpdateSourceClassName;
		$getFunctionName = (string) $rule->UpdateSourceFunction;
		$setObjectName = (string) $rule->ObjectName;
		$setFunctionName = (string) $rule->FunctionName;
		$value = '';
		if ($getObjectName == 'PARTNER') {
			if (method_exists($this->partner, $getFunctionName)) {
				$value = $this->partner->$getFunctionName();
			} else {
				throw new Exception("Update Order rule failed. Rule# " . $rule->RuleNumber +
						" Function " . $getFunctionName . "does not exist in " . $getObjectName +
						" Order # " . $this->order->getPoNumber() +
						" Trader id is " . $this->order->getTrader()->getId() . " and " +
						" Partner id is " . $this->order->getPartner()->getId() . "\n");
			}
		}
	
		if ($setObjectName == 'newOrder') {
			if (method_exists($this->order, $setFunctionName)) {
				$this->order->$setFunctionName($value);
			} else {
				throw new Exception("Update Order rule failed. Rule# " . $rule->RuleNumber +
						" Function " . $setFunctionName . "does not exist in " . $setObjectName +
						" Order # " . $this->order->getPoNumber() +
						" Trader id is " . $this->order->getTrader()->getId() . " and " +
						" Partner id is " . $this->order->getPartner()->getId() . "\n");
			}
		}
	
		//return $newOrder;
	}
	
	private function updateOrderFromDB($rule) {
		$isArray = strcasecmp($rule->isArray, 'TRUE') == 0 ? true : false;
		$sql = (string) $rule->SQL;
		$setObjectName = (string) $rule->Class;
		$setFunctionName = (string) $rule->FunctionName;
		//check if sql needs trader id or partner id
		$traderID = (string) $this->order->getTrader()->getId();
		if (strtolower($rule->PartnerType != "seller")) {
			$partnerID = (string) $this->order->getPartner()->getPartnerId();
		} else {
			$partnerID = (string) $this->order->getSellerPartner()->getId();
		}
		//replacre trader id and partner id in sql for $trader_id and $partner_id
		$sql = str_replace("\$trader_id", $traderID, $sql);
		$sql = str_replace("\$partner_id", $partnerID, $sql);
		if ($isArray) {
			$arrayName = $rule->ArrayName;
			$varName = $rule->VariableName;
			if (strcasecmp($arrayName, 'items') == 0) {
				$param = "";
				foreach ($this->order->getItems() as $item) {
					if (!empty($param)) {
						$param .= ",";
					}
					switch ($varName) {
						case "sku":
							$itemVal = $item->getSku($this->order->getPartner()->getSkuChangeCase ());
							break;
						case "qty":
							$itemVal = $itemQty();
							break;
						case "price":
							$itemVal = $itemPrice();
							break;
						default:
							//TODO: Raise an exception
							echo "unknow value: " . $varName;
							break;
					}
					$param .= "'" . $itemVal . "'";
				}
				$sql = str_replace("\$SKU", $param, $sql);
				$dbresult = $this->getMappingFromDB($sql);
				if ($setObjectName == 'newOrder') {
					if (method_exists($this->order, $setFunctionName)) {
						$this->order->$setFunctionName($dbresult);
					} else {
						echo "\n $setFunctionName in $setObjectName does not exist\n";
					}
				}
			}
		}
	}
}
