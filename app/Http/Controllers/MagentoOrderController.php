<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MagentoOrderController extends Controller
{
	private $magentoOrderArray = array();
	
	public function __construct($logger) {
		parent::__construct($logger);
	}
}
