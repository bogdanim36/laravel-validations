# Complete Php Laravel with AngularJS Validations

01. Change <a href='https://github.com/bogdanim36/laravel-validations/blob/master/src/BaseModel.php' target='_blank'>BaseModel</a>
02. Change <a href='https://github.com/bogdanim36/laravel-validations/blob/master/src/Controller.php' target='_blank'>Controller.php</a>
03. Add in client side <a href='https://github.com/bogdanim36/laravel-validations/blob/master/src/validator.service.js' target='_blank'>validator.service.js</a>. Change how to highlight controls with error as you need.
04. Add in client side <a href='https://github.com/bogdanim36/laravel-validations/blob/master/src/validator-errors.component.html' target='_blank'>validator-errors.html</a>, and change css classes as you need. Your view controller must be names with alias vm (ng-controller = 'SampleCtrl as vm'.
05. Add in client side <a href='https://github.com/bogdanim36/laravel-validations/blob/master/src/validator-errors.component.js' target='_blank'>validator-errors.js</a>

<br>in Model.php se pun regulile de validare:
(!!! se vor sterge validarile pt. campurile ce se completeaza in metodele update  or insert din repository ca: tenanant_id, create, update, etc)

    protected $validations = [
		"user_id" => array("rules" => "required|integer"),
		"tenant_id" => "required|integer",
		"type_id" => "required|integer",
		"date_start" => "required|date",
		"date_end" => ["rules" => "required|date|after:date_start",
			"messages" => ["after" => 'END_DATE_MUST_BE_AFTER_START_DATE'],
			"title" => "DATE_START"],
		"comment" => "required|max:65535",
		"created_by" => "integer",
		"modified_by" => "integer",
	];
	
	//atentie trebuie sa fie public
	public $relatedModels=["phone", "emails", "address"] // numele claselor ce se vor regasi ca membrii un obiectul model, si in ng-repeat pe copii respectivi
	
Unde:
	- messages contine un array de  cu mesajele custom (necesare daca cele generate automat nu sunt ok)

Validari conditionale: In Model se creaza metoda conditionalValidations, si se adauga mesajele pt. reguli in $validations

    protected $validations = [
		"project_id" => ["messages" => ["required" => null]],
		"practice_id" => ["messages" => ["required" => 'LANG.CUSTOM_MESSAGE'],
		"commodity_activity_id" => ["messages" => ["required" => null]],
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

In form blade trebuie pus elementul pt. afisarea mesajelor de la server:
    
    <div class="form-group mb-3 row" id="error_messages">
        <validator-errors ></validator-errors>
    </div>
    
pt. copii
    
    <div ng-repeat= "entityName in vm.data.entityName" > 
        <input ng-model="entityName.field1>
        <datetime ng-model="entityName.field2>
    </div>

pt. rapoarte
 
 <lookup ng-model="vm.filter.user_id" required></lookup>
 <datetimepicker ng-model="vm.filter.date_to" aftre=date_from"></datetimepicker>
 
 
In metoda de raspuns server in client side (este implementat in baseCtrl.save):

        if (response.status) {
            saveCallback(response);
            vm.closeDialog();
        } else {
            this.validator.markErrors(response.error, this.element);
            let messages = {};
            Object.keys(response.error).forEach(field => {
                let errors = response.error[field];
                if (angular.isString(errors[0])) messages[field] = errors;
                else messages[field] = [trans('LANG.' + field + 'S_INVALID')];
            });
            this.errors = messages;
        }

Informatii aditionale:

Mesajele generate automate se creeaza in BaseModel.getStandardValidationError:
file: app/Models/BaseModel.php

    private function getStandardValidationError($rule, $fieldName)
	{
		$ruleName = explode(":", $rule)[0];
		$ruleValues = strpos(":", $rule) > -1 ? explode(":", $rule)[1] : null;
		switch ($ruleName) {
			case "required":
				return trans(upper('LANG'.$fieldName)) . trans('LANG.IS_REQUIRED');
			default:
				return trans(upper('LANG'.$fieldName)) . ' ' . trans(upper('LANG'.$ruleName)); //trans($fieldName . " must " . $ruleName);
		}
	}

Template-ul html de modificat este:
	/assets/custom/js/directives/validator-errors/validator-errors.component.html

BaseModel.php contine metodele:

	public function getValidations()
	{
		if (!property_exists($this, "validations")) {
			return [];
		}
		$validator = ["rules" => [], "messages" => []];
		foreach ($this->validations as $key => $validation) {
			$rules = isset($validation["rules"]) ? $validation["rules"] : $validation;
			$validator["rules"][$key] = $rules;
			$rulesList = explode("|", $rules);
			$messages = isset($validation["messages"]) ? $validation["messages"] : [];
			foreach ($rulesList as $rule) {
				$ruleName = explode(':', $rule)[0];
				$messageKey = $key . "." . $ruleName;
				if (!isset($messages[$ruleName])) $message = $this->getStandardValidationError($rule, $key);
				else $message = trans("LANG." . strtoupper($messages[$ruleName]));
				$validator["messages"][$messageKey] = $message;
			}
		}
		return $validator;
	}

	public function getRelatedModels()
	{
		if (!property_exists($this, "relatedModels") ) {
			return [];
		}
		return $this->relatedModels;
	}

	private function getStandardValidationError($rule, $fieldName)
	{
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


Controller.php contine:

	public function validate($modelName, $request)
	{
		$model = \App::make('\App\Models\\' . $modelName);
		$validators = $model->getValidations();
		$input = is_array($request) ? $request : $request->all();
		$validation = \Validator::make($input, $validators["rules"], $validators["messages"]);
		$errors = $validation->fails() ? $validation->errors()->messages(): [];
		foreach ($model->relatedModels as $relatedModelName) {
			if (!isset($input[$relatedModelName])) continue;
			foreach ($input[$relatedModelName] as $index => $item) {
				$result = $this->validate($relatedModelName, $item);
				if (isset($result["status"])) $errors[$relatedModelName][] = array("index" => $index, "errors" => $result['error']);
			}
		}
		if (count($errors)) return Helper::setValidationErrorResponse($errors);
		else return true;
	}
