<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Traits\AppTrait;

class EdiStatusNew extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
	 
    protected $table = 'edi_status_news';
	
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
    use AppTrait;
    
    public function isEligibleToProcess($traderId, $customerId=NULL, $customerPO, $status = NULL) {
    	//$customerPO = 123;
    	$at = $this->getData();
    	$query = DB::table('edi_status_news');
    	//App\Flight
    	
    	if($customerId) {
    		$matchThese = ['customer_id' => $customerId, 'trader_id' => $traderId, 'customer_po' => $customerPO];
    		$query->where($matchThese);
    	} else {
    		$query->where(['customer_po' => $customerPO,'trader_id' => $traderId]);
    	}
    	
    	$query->orderByRaw('status_id DESC');
    	//$query->take(1);
    	$results = $query->get();
    	//$results = $results->get();
    	//$resultss = $results->get()->first()->toArray();
    	//var_dump($results);
    	if($results && isset($results[0])) {
    		$data = $results[0];
    		$r = $data->status;
	    	//if($data->status) {
	    	if ($data->status == 'RR'  || ($data->status == 'RC' && $data->errored == 'N')) {
	    		$res['flag'] = true;
	    		$res['status'] = $data->status;
	    	} else {
	    		$res['flag'] = false;
	    	}
    	} else {
    		$res['flag'] = true;
    	}
    	
    	return $res;
    }
    
    public function logOrderaa($traderId, $partnerId, $PoNumber, $soNumber, $status, $comment=null, $errorDescription=NULL) {
    	$e = $traderId;
    
    }
}
