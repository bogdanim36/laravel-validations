<?php

namespace App\Models;

class Sample1 extends BaseModel
{
	protected $table = 'sample1';
	protected $primaryKey = 'sample1_id';


	protected $validations = [
		"customer_id" => "integer",
		"project_id" => ["rules" => "integer", "messages" => ["required" => null]],
		"commodity_activity_id" => ["messages" => ["required" => null]],
		"reference_id" => ["rules" => "integer", "messages" => ["required" => null]],
		"status_id" => "required|integer",
		"internal_activity_type_id" => "integer",
		"input_effort" => ["rules"=>"required|integer|max:1440|min:1", "messages"=>["min"=>"LANG.INPUT_EFFORT_REQUIRED"]],
		"dnb_effort" => "required|integer|max:1440",
		"blb_effort" => ["rules"=>"required|integer|max:1440|min:1", "messages"=>["min"=>"LANG.BLB_EFFORT_REQUIRED"]],
		"comments" => "required|max:65535",
	];

	public function conditionalValidations($validator)
	{
		$validator->sometimes('project_id', 'required', function ($input) {
			return !$input->is_internal_hours;
		});
		$validator->sometimes('reference_id', 'required', function ($input) {
			return !$input->is_internal_hours && $input->project_id;
		});
		$validator->sometimes('practice_id', 'required', function ($input) {
			return !$input->is_internal_hours && $input->timesheetPracticeValidate;
		});
		$validator->sometimes('commodity_activity_id', 'required', function ($input) {
			return $input->commodity_activity_id && $input->selectedProjectIsCommodity;
		});
	}

}
