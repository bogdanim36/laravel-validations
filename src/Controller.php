<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Validator;

class Controller extends BaseController
{

	public function __construct()
	{
	}

	function snakeToCamel($str)
	{
		// Remove underscores, capitalize words, squash, lowercase first.
		return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
	}
	public function validate($modelName, $request)
	{
		$camelCase = $this->snakeToCamel($modelName);
		$model = \App::make('\App\Models\\' . $camelCase);
		$validators = $model->getValidations();
		$input = is_array($request) ? $request : $request->all();
		$validation = \Validator::make($input, $validators["rules"], $validators["messages"]);
		if (method_exists($model, "conditionalValidations")) $model->conditionalValidations($validation);
		$errors = $validation->fails() ? $validation->errors()->messages() : [];
		foreach ($model->relatedModels as $relatedModelName) {
			if (!isset($input[$relatedModelName])) continue;
			if (isset($input[$relatedModelName][0]))
			foreach ($input[$relatedModelName] as $index => $item) {
				$result = $this->validate($relatedModelName, $item);
				if (isset($result["status"])) $errors[$relatedModelName][] = array("index" => $index, "errors" => $result['error']);
			}
			else {
				$result = $this->validate($relatedModelName, $input[$relatedModelName]);
				if (isset($result["status"])) {
					foreach ($result['error'] as $field=>$error){
						$relatedModelField = $relatedModelName .'.'.$field;
						$errors[$relatedModelField]=$error;
					}
				}
			}
		}
		if (count($errors)) return Helper::setValidationErrorResponse($errors);
		else return true;
	}
}
