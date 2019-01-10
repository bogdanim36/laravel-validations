<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class Sample2Controller extends Controller
{
	protected $service;
	private $model = "Sample2";

	public function __construct()
	{
		$this->service = new Sample2Service;
	}


	public function store(Request $request)
	{
		$response = $this->validate($this->model, $request);
		if (isset($response["status"])) return response()->json($response);
		else {
			return response()->json(array(
				"status" => true,
				"data" => $this->service->insert($request)),
				200);
		}
	}

	public function update(Request $request, $id)
	{
		$response = $this->validate($this->model, $request);
		if (isset($response["status"])) return response()->json($response);
		else {
			return response()->json(array(
				"status" => true,
				"data" => $this->service->update($request, $id)),
				200);
		}
	}
}
