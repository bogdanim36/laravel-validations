(function () {
	'use strict';

	angular.module('app')
		.service('validator', validator);

	function validator($timeout) {
		this.markErrors = function (errors, $element) {
			$timeout(() => {
				var mandatory = [];
				var valid = true;
				var field = '';
				var root = $element ? $element : $('body');
				for (let field in errors) {
					let fieldError = errors[field];
					if (angular.isString(fieldError[0])) {
						let ngModel = (this.prefix ? this.prefix : 'vm.data') + '.' + field;
						this.markField(root, mandatory, ngModel);
					} else {
						errors[field].forEach(item => {
							Object.keys(item.errors).forEach(childField => {
								let ngModel = field + "." + childField;
								this.markField(root, mandatory, ngModel, item.index);
							});
						});
					}
				}
				this.invalidMarkup(mandatory);
			});
		};
		this.markField = function (root, mandatory, result, index) {
			let selector = '[ng-model="' + result + '"], [ng-model="' + result + '.DateFormat"], [ng-model="' + result + '.DatetimeFormat"]';
			if (root.find(selector).length)
				mandatory.push(
					root.find(selector)
						.filter((i) => {
							return index === undefined ? true : i === index;
						}));
		};
		this.setInvalid = function (element) {
			if (this.focus) return;
			element.focus();
			this.focus = true;
		};
		this.invalidMarkup = function (htmlElements) {
			this.focus = false;
			for (var field = 0; field < htmlElements.length; field++) {
				var element = htmlElements[field];
				console.log("field", field, element);
				switch (htmlElements[field][0].nodeName) {
					case 'DATETIMEPICKER':
						if (element.find('input')) {
							element.find('input, span button ').css('border', '1px solid red');
							element.find('span button').css('border-left', '1px solid #ccc');
							this.setInvalid(element);
						}

						element.find('input, span button').change(function () {
							$(this).css('border', '1px solid #ccc');
							$($(this)[0].nextElementSibling).find('button').css('border', '1px solid #ccc');
						});


						break;
					case 'LOOKUP':
						let selector = '.select2-selection.select2-selection--single, .select2-selection.select2-selection--multiple';
						if (element.find('select')) {
							element.find(selector).css('border', '1px solid red');
							this.setInvalid(element.find(selector));

						}

						element.find('select').change(function (event) {
							$($(this)[0].nextSibling).find(selector).css('border', '1px solid #ccc');
						});

						break;

					case 'TEXTAREA':
						if (element) {
							element.css('border', '1px solid red');
							this.setInvalid(element);
						}

						element.keypress(function () {
							$(this).css('border', '1px solid #ccc');
						});
						break;

					case 'INPUT':
						if (element) {
							element.css('border', '1px solid red');
							this.setInvalid(element);

						}

						element.keypress(function () {
							$(this).css('border', '1px solid #ccc');
						});
						break;
					case 'SELECT':
						if (element) {
							$(element[0].nextSibling).find('span .select2-selection.select2-selection--single').css('border', '1px solid red');
							this.setInvalid($(element[0].nextSibling).find('span .select2-selection.select2-selection--single'));
						}

						element.change(function (event) {
							$($(this)[0].nextSibling).find('.select2-selection.select2-selection--single').css('border', '1px solid #ccc');
						});
						break;
					default:
						throw "Unknown element in invalid-markup.service.js: " + htmlElements[field][0].nodeName;

				}
			}
		};

	}
}());