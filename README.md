# Complete Php Laravel with AngularJS Validations

01. Change <a href='https://github.com/bogdanim36/laravel-validations/blob/master/src/BaseModel.php' target='_blank'>BaseModel</a>
02. Change <a href='https://github.com/bogdanim36/laravel-validations/blob/master/src/Controller.php' target='_blank'>Controller.php</a>
03. Add in client side <a href='https://github.com/bogdanim36/laravel-validations/blob/master/src/validator.service.js' target='_blank'>validator.service.js</a>. Change how to highlight controls with error as you need.
04. Add in client side <a href='https://github.com/bogdanim36/laravel-validations/blob/master/src/validator-errors.component.html' target='_blank'>validator-errors.html</a>, and change css classes as you need. Your view controller must be names with alias vm (ng-controller = 'SampleCtrl as vm'.
05. Add in client side <a href='https://github.com/bogdanim36/laravel-validations/blob/master/src/validator-errors.component.js' target='_blank'>validator-errors.js</a>

<br>In Model.php setup validations rules (must delete rules for required field which are compelted in service or repository insert or update):

    protected $validations = [
		"user_id" => array("rules" => "required|integer"),
		"tenant_id" => "required|integer",
		"type_id" => "required|integer",
		"date_start" => "required|date",
		"date_end" => ["rules" => "required|date|after:date_start",
			"messages" => ["after" => 'LANG.END_DATE_MUST_BE_AFTER_START_DATE'],
			"title" => "LANG.DATE_START"],
		"comment" => "required|max:65535",
		"created_by" => "integer",
		"modified_by" => "integer",
	];
	
	public $relatedModels=["phone", "emails", "address"] 
	
Where messages contains an array of custom mesages (if the automatic generated messages are not good enought).

Conditional vaalidations: In Model must create method conditionalValidations, and must be added messages for rules in $validations

    protected $validations = [
		"project_id" => ["messages" => ["required" => null]],
		"practice_id" => ["messages" => ["required" => 'LANG.CUSTOM_MESSAGE'], //ID for translated custom message
		"commodity_activity_id" => ["messages" => ["required" => null]], // null for automatic generated message
		"reference_id" => ["messages" => ["required" => null]]

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
        
In controller.php:

	public function update(Request $request, $id)
	{
		$response = $this->validate("UserVacation", $request);
		if (isset($response["status"])) return response()->json($response);
		else return response()->json(array(
			"status" => true,
			"data" => $this->service->update($request, $id)),
			200);
	}

	public function store(Request $request, $id)
	{
		$response = $this->validate("UserVacation", $request);
		if (isset($response["status"])) return response()->json($response);
		else return response()->json(array(
			"status" => true,
			"data" => $this->service->insert($request)),
			200));
	}

In form blade must be insterted element for show error messages:
    
    <div class="form-group mb-3 row" id="error_messages">
        <validator-errors ></validator-errors>
    </div>
    
for validation of children (in ngrepaet must have same name as property of model)
    
    <div ng-repeat= "entityName in vm.data.entityName" > 
        <input ng-model="entityName.field1>
        <datetime ng-model="entityName.field2>
    </div>
    
In save callback method from angular must have:

			if (response.status) {
				if (this.saveCallback) this.saveCallback(response);
				else window.location = "/" + this.entity + "/" + params.data[this.primaryKey];
			} else {
				$injector("validator").markErrors(response.error, this.element);
				let messages = {};
				Object.keys(response.error).forEach(field => {
					let errors = response.error[field];
					if (angular.isString(errors[0])) messages[field] = errors;
					else messages[field] = [trans('LANG.' + field + 'S_INVALID')];
				});
				this.errors = messages;
				console.warn(this.errors);
			}

For reports.
 
	 <select ng-model="vm.filter.user_id" required></lookup>
	 <datetimepicker ng-model="vm.filter.date_to" aftre=date_from"></datetimepicker>



Automatic generated messages are creeated in BaseModel.getStandardValidationError:
file: src/BaseModel.php

    private function getStandardValidationError($rule, $fieldName)
	{
		$ruleName = explode(":", $rule)[0];
		$ruleValues = strpos(":", $rule) > -1 ? explode(":", $rule)[1] : null;
		switch ($ruleName) {
			case "required":
				return trans(upper('LANG'.$fieldName)) . trans('LANG.IS_REQUIRED');
			default:
				return trans(upper('LANG'.$fieldName)) . ' ' . trans(upper('LANG'.$ruleName)); //trans($fieldName . " 	must " . $ruleName);
		}
	}
