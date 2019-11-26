<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class EdiStatusNew extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
	 
    protected $table = 'edi_status_new';
	
	/**
     * The primary key associated with the table.
     *
     * @var string
     */
	 
    protected $primaryKey = 'status_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    
    public function isEligibleToProcess($traderId, $customerId=NULL, $customerPO, $status = NULL) {
    	$customerPO = 123;
    	$results = DB::table('edi_status_news');
    	//App\Flight
    	
    	if($customerId){
    		$matchThese = ['customer_id' => $customerId, 'trader_id' => $traderId, 'customer_po' => $customerPO];
    		$results->where($matchThese);
    	} else {
    		$results->where(['customer_po' => $customerPO,'trader_id' => $traderId]);
    	}
    	
    	$results->orderByRaw('status_id DESC');
    	$results->take(1);
    	$results = $results->get()->first();
    	//$r = $results->status;
    	$r = $results['status'];
    	if($results['status']) {
    		$res['flag'] = true;
    		$res['status'] = $results['status'];
    	} else {
    		$res['flag'] = false;
    	}
    	
    	return $res;
    }
}
