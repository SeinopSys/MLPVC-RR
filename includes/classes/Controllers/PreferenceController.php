<?php

namespace App\Controllers;
use App\CoreUtils;
use App\CSRFProtection;
use App\Permission;
use App\RegExp;
use App\Response;
use App\UserPrefs;

class PreferenceController extends Controller {
	function __construct(){
		parent::__construct();

		if (!Permission::sufficient('user') || !POST_REQUEST)
			CoreUtils::notFound();
		CSRFProtection::protect();
	}

	private $_setting, $_value;
	function _getPreference($params){
		$this->_setting = $params['key'];
		$this->_value = UserPrefs::get($this->_setting);
	}

	function get($params){
		$this->_getPreference($params);

		Response::done(array('value' => $this->_value));
	}

	function set($params){
		$this->_getPreference($params);

		try {
			$newvalue = UserPrefs::process($this->_setting);
		}
		catch (\Exception $e){ Response::fail('Preference value error: '.$e->getMessage()); }

		if ($newvalue === $this->_value)
			Response::done(array('value' => $newvalue));
		if (!UserPrefs::set($this->_setting, $newvalue))
			Response::dbError();

		Response::done(array('value' => $newvalue));
	}
}
