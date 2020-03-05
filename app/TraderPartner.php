<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TraderPartner extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
	 protected $guarded = [];

	 protected $table = 'trader_partners';
	 
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['traderId','partnerId','partnerName','emailAddress','getOrderAdapter','ediInterchangeID','ediQualifier','traderInterchangeID','traderIDQualifier','ediX12Version','ediGSIndustryIdentifierCode','fieldSeperator','ediSegmentSeperator'];

    /**
	 * Get the user that owns the phone.
	 */
	public function trader()
	{
	   // return $this->belongsTo('App\Trader','traderId');
	    return $this->belongsTo('App\Trader');
	    //return $this->belongsTo(Trader::class);
	}
}
