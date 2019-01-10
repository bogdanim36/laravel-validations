<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sample1Service;

class Sample1Controller extends Controller
{
	protected $service = null;
	private $model = "Sample1";

	public function __construct()
	{
		$this->service = new Sample1Service();
	}



	public function store(Request $request)
	{
		$response = $this->validate($this->model, $request);
		if (isset($response["status"])) return response()->json($response);
		else return response()->json(array(
			"status" => true,
			"data" => $this->service->insert($request),
			200));
	}


	public function update(Request $request, $id)
	{
		$response = $this->validate($this->model, $request);
		if (isset($response["status"])) return response()->json($response);
		else return response()->json(array(
			"status" => true,
			"data" => $this->service->update($request, $id)),
			200);
	}
}
