<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RefCounter extends Model
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	
	protected $table = 'ref_counters';
	
	/**
	 * The primary key associated with the table.
	 *
	 * @var string
	 */
	
	protected $primaryKey = 'id';
	
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;
	
	public function getNewWorkFlowId() {
		return RefCounter::where('counterName','workflowId')->get()->first()->getOriginal();
	}
	
}
