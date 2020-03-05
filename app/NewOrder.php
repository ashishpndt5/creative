<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewOrder extends Model
{
    /**
	 * The table associated with the model.
	 *
	 * @var string
	 */
    protected $table = 'new_orders';
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    
    protected $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','traderId','partnerId','workflowId','poNumber','soNumber','status','sent_to_seller','poDate'];
}
