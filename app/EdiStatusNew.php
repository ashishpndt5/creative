<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
