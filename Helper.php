<?php

namespace App\Helpers;

use DB;
use Auth;
use Request;
use Lang;
use View;
use GuardService;

class Helper
{

	/**
	 * @param $class current class
	 * @param $function current method
	 * @return array a reposne array
	 */

	public static function responseObject($class, $function)
	{
		return array("status" => false,
			"data" => null,
			"error" => array(
				"class" => substr(strrchr($class, "\\"), 1),
				"function" => $function,
				"message" => "")
		);
	}

	public static function setTrueStatus(&$response, $data = null)
	{
		if (isset($data)) $response["data"] = $data;
		$response["status"] = true;
		unset($response["error"]);
		return $response;
	}

	public static function setValidationErrorResponse($error = null)
	{
		$response["status"] = false;
		$response["error"] = $error;
		return $response;
	}

}
