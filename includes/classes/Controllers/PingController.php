<?php

namespace App\Controllers;
use App\Response;

class PingController extends Controller {
	public $do = 'ping';

	public function ping(){
		Response::done();
	}
}
