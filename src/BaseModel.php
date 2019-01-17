<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator;

class BaseModel extends Model
{
	public $relatedModels = [];
	protected $validations = [];

	public function getValidations()
	{
		if (!property_exists($this, "validations")) {
			return [];
		}
		$validator = ["rules" => [], "messages" => []];
		foreach ($this->validations as $key => $validation) {
			$rules = isset($validation["rules"]) ? $validation["rules"] : $validation;
			$messages = isset($validation["messages"]) ? $validation["messages"] : [];
			if (is_string($rules)) {
				$validator["rules"][$key] = $rules;
				$rulesList = explode("|", $rules);
				// generez mesaje pt. regulile existente in $validations
				foreach ($rulesList as $rule) {
					$ruleName = explode(':', $rule)[0];
					$messageKey = $key . "." . $ruleName;
					if (!isset($messages[$ruleName])) $message = $this->getStandardValidationError($rule, $key);
					else $message = trans("LANG." . strtoupper($messages[$ruleName]));
					$validator["messages"][$messageKey] = $message;
				}
			}
			// generez mesaje pt. regulile conditionale (care nu sunt definite in $validations ci in conditionalValidations)
			foreach ($messages as $ruleName => $message) {
				$messageKey = $key . "." . $ruleName;
				if (isset($validator["messages"][$messageKey] )) continue;
				$message = isset($messages[$ruleName]) ?
					trans("LANG." . strtoupper($messages[$ruleName])) :
					$this->getStandardValidationError($ruleName, $key);

				$validator["messages"][$messageKey] = $message;
			}
		}
		return $validator;
	}

	public function getRelatedModels()
	{
		if (!property_exists($this, "relatedModels")) {
			return [];
		}
		return $this->relatedModels;
	}

	private function getStandardValidationError($rule, $fieldName)
	{
		if (!isset($rule) || $rule=="") throw new \Exception("No rule set for field " . $fieldName . " in model " . $this->table);
		$ruleName = explode(":", $rule)[0];
		$ruleValues = strpos(":", $rule) > -1 ? explode(":", $rule)[1] : null;
		switch ($ruleName) {
			case "required":
				return trans(strtoupper('LANG.' . $fieldName)) . ' ' . trans('LANG.IS_REQUIRED');
			default:
				return trans(strtoupper('LANG.' . $fieldName)) . ' ' . trans(strtoupper('LANG.' . $ruleName)); //trans($fieldName . " must " . $ruleName);
		}
	}

	public function validate(array $input, $catchResponse = false)
	{
		if (!property_exists($this, "validations")) {
			return $this;
		}

		$validator = Validator::make($input, $this->validations);
		if ($validator->fails()) {
			if ($catchResponse) {
				return $validator->errors();
			} else {
				throw new \Exception($validator->errors(), 1);
			}
		}

		return $this;
	}
}
