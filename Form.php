<?php
namespace Module\FormBuilder;

use Module\FormBuilder\Fieldset;

/**
 * Builds and valids forms
 *
 * This class allows for building forms using JSON. Fields are automatically
 * validated based on Rules allowing for easy server-side validation. The markup
 * closely resembles jQuery validation plugin so you can use one stylesheet for
 * both client- and server-side validation.
 *
 * ### Usage
 *
 * <code>
 *   use Module\FormBuilder\Form;
 * 
 *   $UserEdit = new Form('{
 *     "id": "user",
 *     "action": "#",
 *     "method": "POST",
 *     "fieldsets": [
 *       {
 *         "legend": "Update your user information:",
 *         "fields": [
 *           {
 *             "name": "txtName",
 *             "label": "Name",
 *             "dataMap": "name",
 *             "type": "text",
 *             "value": "Jaime Rodriguez",
 *             "rules": {
 *               "required": true,
 *               "lengthMax": 20
 *             }
 *           }, {
 *             "name": "txtEmail",
 *             "label": "Email",
 *             "dataMap": "email",
 *             "type": "text",
 *             "value": "hi.i.am.jaime@gmail.com",
 *             "rules": {
 *               "required": true,
 *               "email": true
 *             }
 *           }, {
 *             "name": "txtDate",
 *             "label": "Date",
 *             "dataMap": "date",
 *             "type": "text",
 *             "value": "04/11/1984",
 *             "rules": {
 *               "required": true,
 *               "date": true
 *             }
 *           }, {
 *             "name": "ddlRole",
 *             "label": "Role",
 *             "dataMap": "role",
 *             "type": "select",
 *             "values": [
 *               {
 *                 "name":  "Administrator",
 *                 "value": "admin"
 *               }, {
 *                 "name":  "Subscriber",
 *                 "value": "subscriber"
 *               }, {
 *                 "name":  "User",
 *                 "value": "user",
 *                 "selected": true
 *               }
 *             ]
 *           }
 *         ]
 *       }, {
 *         "class": "submit",
 *         "fields": [
 *           {
 *             "name": "btnSubmit",
 *             "label": "",
 *             "value": "Submit",
 *             "type": "submit"
 *           }
 *         ]
 *       }
 *     ]
 *   }');
 *
 *   // Simulate a record Object
 *   $u = new \stdClass();
 *   $u->columns = array();
 *
 *   // Has the form been submitted?
 *   if ($UserEdit->submitted()) {
 *     // Validate the form
 *     $passed = $UserEdit->validate();
 *
 *     if ($passed === true) {
 *       // put the values into the record Object
 *       $u->columns = array_merge($u->columns, $UserEdit->getDataMap());
 *     }
 *   }
 *
 *   // if Form::validate() fails, it will display errors and
 *   // updated values, otherwise it'll render normally
 *   echo $UserEdit->render();
 * </code>
 *
 * ### Changelog
 * 
 * ## Version 2.0
 * * Made 2.x compatible
 *
 * ## Version 1.6
 * * Added track attribute for google data tracking
 *
 * ## Version 1.5
 * * Throws an exception when you make a JSON error
 *
 * ## Version 1.4
 * * Added ability to overwrite errors
 * * Fixed equalTo rule validation bug
 *
 * ## Version 1.2
 * * Added placeholder for inputs
 * * Added namespacing
 * * Fixed error class bug
 *
 * ## Version 1.1
 * * Added the date and changelog sections to documentation
 *
 * @date July 30, 2020
 * @author Jaime A. Rodriguez <hi.i.am.jaime@gmail.com>
 * @version 2.0
 * @license  http://opensource.org/licenses/MIT
 */
class Form {
	/**
	 * The ID of the form
	 * @var string
	 */
	public $id;

	/**
	 * Class to apply to the field
	 * @var string
	 */
	public $class;

	/**
	 * The action of the form
	 * @var string
	 */
	public $action;

	/**
	 * The method of the form
	 * @var string
	 */
	public $method;

	/**
	 * Should the form be validated?
	 * @var boolean
	 */
	public $validate = true;

	/**
	 * An array of fieldsets
	 * @var array of Module\FormBuilder\Fieldset
	 */
	private $fieldsets;

	/**
	 * Creates a Form based on JSON
	 * @param string $json
	 */
	public function __construct($json) {
		$data = \json_decode(str_replace('\\', '\\\\', $json));

		if (!is_object($data)) {
			var_dump($json);
			throw new \Exception('There is an error in your JSON. Cannot continue.');
		}

		if (!isset($data->action)) {
			$data->action = "#";
		}

		if (!isset($data->method)) {
			$data->method = "POST";
		}

		$this->action = $data->action;
		$this->method = $data->method;

		if (isset($data->id)) {
			$this->id = $data->id;
		}

		if (isset($data->class)) {
			$this->class = $data->class;
		}

		if (isset($data->validate)) {
			$this->validate = $data->validate;
		}

		foreach ($data->fieldsets as $fieldset) {
			$this->fieldsets[] = new Fieldset($fieldset);
		}
	}

	/**
	 * Checks if the form has been submitted?
	 * @return boolean
	 */
	public function submitted() {
		if ($_SERVER['REQUEST_METHOD'] == $this->method) {
			if (strtoupper($this->method) === "POST") {
				$id = $_POST['frmID'];
			} else {
				$id = $_GET['frmID'];
			}

			if ($id == $this->id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Validates the form
	 * @return array
	 */
	public function validate() {
		$errors = array();

		foreach($this->fieldsets as $fieldset) {
			$error = $fieldset->validate();
			if (is_array($error)) {
				$errors = array_merge($errors, $error);
			}
		}

		return (count($errors) == 0) ? true : $errors;
	}

	/**
	 * Renders a form
	 * @return string
	 */
	public function render() {
		$validate = (!$this->validate) ? "novalidate" : "";
		$buffer = array();
		$buffer[] = "<form id=\"{$this->id}\" class=\"{$this->class}\" action=\"{$this->action}\" method=\"{$this->method}\" {$validate}>";
		$buffer[] = "<input type=\"hidden\" name=\"frmID\" id=\"frmID\" value=\"{$this->id}\">";

		foreach($this->fieldsets as $fieldset) {
			$buffer[] = $fieldset->render($this->validate && $this->submitted());
		}

		$buffer[] = "</form>";

		return implode(" ", $buffer);
	}

	/**
	 * Get the datamap for the form
	 * @return array
	 */
	public function getDataMap() {
		$formData = array();

		foreach ($this->fieldsets as $fieldset) {
			if (is_array($fieldset->getDataMap())) {
				$formData = array_merge($formData, $fieldset->getDataMap());
			}
		}

		return $formData;
	}
}





