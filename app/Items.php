<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'items';
	
	public $timestamps = false;
}
