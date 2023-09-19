<?php
namespace Module\FormBuilder;

/**
 * Creates a Field
 * @internal
 */
class Field {
	// Manditory properties

	/**
	 * The name of the field
	 * @var string
	 */
	public $name;

	/**
	 * The label for the field
	 * @var string
	 */
	public $label;

	/**
	 * The type of input for the field
	 * @var string
	 */
	public $type;

	// Optional properties

	/**
	 * The rules for validation
	 * @var object
	 */
	public $rules;

	/**
	 * The value of the field
	 * @var array
	 */
	public $values = array();

	/**
	 * The placeholder for the field
	 * @var string
	 */
	public $placeholder;

	/**
	 * The google event tracking for the field
	 * @var string
	 */
	public $track;

	/**
	 * Should we autofocus on this field?
	 * @var boolean
	 */
	public $autofocus;

	/**
	 * The data mapping for this field
	 * @var string
	 */
	public $dataMap;

	/**
	 * Class to apply to the field
	 * @var string
	 */
	public $class;

	/**
	 * Is this field disabled?
	 * @var boolean
	 */
	public $disabled = false;

	/**
	 * Constructor for the field
	 * @param object $object
	 */
	public function __construct($object) {
		if (!isset($object->type)) {
			$object->type = "text";
		}

		if (!isset($object->name)) {
			throw new \Exception('Module\Formbuilder\Field: Name is manditory.');
		}

		$this->name = $object->name;
		$this->track = @$object->track;
		$this->label = @$object->label;
		$this->type = $object->type;

		if (isset($object->label)) {
			$this->label = $object->label;
		}

		if (isset($object->dataMap)) {
			$this->dataMap = $object->dataMap;
		}

		if (isset($object->class)) {
			$this->class = $object->class;
		}

		if (isset($object->disabled)) {
			$this->disabled = $object->disabled;
		}

		if (isset($object->placeholder)) {
			$this->placeholder = $object->placeholder;
		}

		if (isset($object->autofocus)) {
			$this->autofocus = $object->autofocus;
		}

		if (isset($object->rules)) {
			$this->rules = $object->rules;
		}

		if (isset($object->errors)) {
			$this->errors = $object->errors;
		}

		// These two have to be redone
		if (isset($object->values)) {
			$this->values = $object->values;
		} elseif (isset($object->value)) {
			$this->values[] = $object->value;
		}
	}

	/**
	 * Validates a date
	 * @param  string $date
	 * @param  string $format
	 * @return boolean
	 * @private
	 */
	private function validateDate($date, $format = 'Y-m-d H:i:s') {
		$d = \DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}

	/**
	 * Renders the field
	 * @return string
	 */
	public function render($validate) {
		// Get any errors
		$errors = ($validate) ? $this->validate() : "";

		$disabled = ($this->disabled) ? "disabled " : "";
		$track = (@isset($this->track)) ? "data-track=\"{$this->track}\" " : "";
		$autofocus = ($this->autofocus) ? "autofocus " : "";
		$placeholder = ($this->placeholder) ? "placeholder='{$this->placeholder}' " : "";

		// Setup rules for client-side processing
		$required =  (@$this->rules->required) ? "required " : "";
		$equalTo =   (@isset($this->rules->equalTo)) ? "equalTo='#{$this->rules->equalTo}' " : "";
		$minLength = (@isset($this->rules->minLength)) ? "minlength='{$this->rules->minLength}'' " : "";
		$maxLength = (@isset($this->rules->maxLength)) ? "maxlength='{$this->rules->maxLength}'' " : "";
		$digits =    (@$this->rules->digits) ? "digits " : "";
		$email =     (@$this->rules->email) ? "email " : "";
		$date =      (@$this->rules->date) ? "date " : "";

		// Add all the rules to one string for brevity
		$rules = "{$track}{$required}{$minLength}{$maxLength}{$equalTo}{$disabled}{$autofocus}{$placeholder}{$digits}{$email}{$date}";

		if (is_array($errors)) {
			$this->class = $this->class . " error";
		}

		if (count($this->values) == 0) {
			$this->values[0]= "";
		}

		$buffer = array();

		switch ($this->type) {
		case 'copy':
			if (isset($this->label)) {
				$buffer[] = "<label for=\"{$this->name}\">{$this->label}</label>";
			}

			$buffer[] = $this->values[0];
			break;
		case 'textbox':
			if (isset($this->label)) {
				$buffer[] = "<label for=\"{$this->name}\">{$this->label}</label>";
			}

			$buffer[] = "<textbox {$rules} id=\"{$this->name}\" name=\"{$this->name}\" class=\"{$this->class}\">";
			$buffer[] = "{$this->values[0]}";
			$buffer[] = "</textbox>";
			break;
		case 'select':
			if (isset($this->label)) {
				$buffer[] = "<label for=\"{$this->name}\">{$this->label}</label>";
			}

			$buffer[] = "<select {$rules} id=\"{$this->name}\" name=\"{$this->name}\" class=\"{$this->class}\">";

			foreach($this->values as $option) {
				$disabledField = "";
				$selected = "";

				if (isset($option->disabled)) {
					$disabledField = ($option->disabled) ? "disabled" : "";
				}

				if (isset($option->selected)) {
					$selected = ($option->selected) ? "selected" : "";
				}

				$buffer[] = "<option {$disabledField} {$selected} value=\"{$option->value}\">{$option->name}</option>";
			}

			$buffer[] = "</select>";
			break;
		case 'radio':
			if (isset($this->label)) {
				$buffer[] = "<label for=\"{$this->name}\">{$this->label}</label>";
			}

			$buffer[] = "<ul class=\"radios {$this->class}\">";

			foreach($this->values as $option) {
				$track = (@isset($option->track)) ? "data-track=\"{$option->track}\" " : "";
				$disabledField = "";
				$selected = "";

				if (isset($option->disabled)) {
					$disabledField = ($option->disabled) ? "disabled " : "";
				}

				if (isset($option->selected)) {
					$selected = ($option->selected) ? "checked " : "";
				}

				$buffer[] = "<li>";
				$buffer[] = "<input {$track} {$rules} {$selected} type=\"radio\" id=\"{$option->id}\" name=\"{$this->name}\" class=\"{$this->class}\" value=\"{$option->value}\">";

				if (isset($option->label)) {
					$buffer[] = "<label for='{$option->id}'>{$option->label}</label>";
				}

				$buffer[] = "</li>";
			}
			$buffer[] = "</ul>";

			break;
		case 'checkbox':
			$selected = "";

			if (isset($this->selected)) {
				$selected = ($this->selected) ? "checked " : "";
			}

			$buffer[] = "<input {$rules} {$selected} type=\"{$this->type}\" id=\"{$this->name}\" name=\"{$this->name}\" class=\"{$this->class}\" value=\"{$this->values[0]}\">";

			if (isset($this->label)) {
				$buffer[] = "<label for=\"{$this->name}\">{$this->label}</label>";
			}
			break;
		default:
			if (isset($this->label)) {
				$buffer[] = "<label for=\"{$this->name}\">{$this->label}</label>";
			}
			$buffer[] = "<input {$rules} type=\"{$this->type}\" id=\"{$this->name}\" name=\"{$this->name}\" class=\"{$this->class}\" value=\"{$this->values[0]}\">";
		}

		if (is_array($errors)) {
			foreach($errors as $error) {
				$buffer[] = "<label class=\"error\" for=\"{$this->name}\">{$error}</label>";
			}
		}

		return implode(" ", $buffer);
	}

	/**
	 * Validates the field
	 * @return array Errors
	 */
	public function validate() {
		$errors = array();

		// assign the new values
		switch ($this->type) {
			case 'submit':
				break;
			case 'select':
			case 'radio':
				if (!empty($_POST[$this->name])) {
					foreach($this->values as $key => $object) {
						$this->values[$key]->selected = ($_POST[$this->name] == $object->value) ? true : false;
					}
				}
				break;
			case 'checkbox':
				if (!empty($_POST[$this->name])) {
					$this->selected = ($_POST[$this->name] == $this->values[0]) ? true : false;
				}
				break;
			default:
				if (!empty($_POST[$this->name])) {
					$this->values[0] = $_POST[$this->name];
				} else {
					unset($this->values[0]);
				}
		}

		if (is_object($this->rules)) {
			foreach ($this->rules as $rule => $value) {
				switch($rule) {
				case 'required':
					if ($value != false) {
						if (count($this->values) == 0) {
							if (isset($this->errors->$rule)) {
								$errors[] = $this->errors->$rule;
							} else {
								$errors[] = "'{$this->label}' is a required field.";
							}
						}
					}
					break;
				case 'lengthMax':
					if ($value != false) {
						if (isset($this->values[0])) {
							if (strlen($this->values[0]) >= $value) {
								if (isset($this->errors->$rule)) {
									$errors[] = $this->errors->$rule;
								} else {
									$errors[] = "'{$this->label}' should be a maximum of {$value} characters.";
								}
							}
						}
					}
					break;
				case 'lengthMin':
					if ($value != false) {
						if (strlen($this->values[0]) <= $value) {
							if (isset($this->errors->$rule)) {
								$errors[] = $this->errors->$rule;
							} else {
								$errors[] = "'{$this->label}' should be a minimum of {$value} characters.";
							}
						}
					}
					break;
				case 'digits':
					if ($value != false) {
						if (!filter_var($this->values[0], FILTER_VALIDATE_FLOAT)) {
							if (isset($this->errors->$rule)) {
								$errors[] = $this->errors->$rule;
							} else {
								$errors[] = "'{$this->label}' is not a valid number.";
							}
						}
					}
					break;
				case 'email':
					if ($value != false) {
						if (count($this->values) == 0) {
							$this->values[0] = NULL;
						} else {
							if (!filter_var($this->values[0], FILTER_VALIDATE_EMAIL)) {
								if (isset($this->errors->$rule)) {
									$errors[] = $this->errors->$rule;
								} else {
									$errors[] = "'{$this->label}' is not a valid email address.";
								}
							}
						}
					}
					break;
				case 'date':
					if (count($this->values) == 0) {
						$this->values[0] = NULL;
					}

					if (!$this->validateDate($this->values[0], 'm/d/Y')) {
						if (isset($this->errors->$rule)) {
								$errors[] = $this->errors->$rule;
						} else {
							$errors[] = "'{$this->label}' is not a valid date (mm/dd/yyyy).";
						}
					}
					break;
				case 'equal':
				case 'equalTo':
					if ($value != false) {
						if (count($this->values) == 0) {
							$this->values[0] = NULL;
						}

						if ($this->values[0] != $_POST[$value]) {
							if (isset($this->errors->$rule)) {
								$errors[] = $this->errors->$rule;
							} else {
								$errors[] = "'{$this->label}' does not match '{$value}'.";
							}
						}
					}
					break;
				}
			}
		}

		return $errors;
	}

	/**
	 * Returns an array with mapping => value
	 * @return array
	 */
	public function getDataMap() {
		if (isset($this->dataMap)) {
			switch($this->type) {
			case 'select':
				foreach ($this->values as $option) {
					if ($option->selected) {
						return array(
							$this->dataMap => $option->value
						);
					}
				}
				break;
			case 'checkbox':
				if (count($this->values) == 0) {
					$this->values[0] = NULL;
				}

				if (isset($this->selected)) {
					return array(
						$this->dataMap => $this->values[0]
					);
				}

				return array(
					$this->dataMap => null
				);

				break;
			default:
				if (count($this->values) == 0) {
					$this->values[0] = NULL;
				}

				return array(
					$this->dataMap => $this->values[0]
				);
			}
		}
	}
}