<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ZnectPartnerShippingDetails extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
	 
    protected $table = 'znect_partner_shipping_details';
	
	/**
     * The primary key associated with the table.
     *
     * @var string
     */
	 
   // protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
}
