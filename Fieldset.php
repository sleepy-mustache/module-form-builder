<?php
namespace Module\FormBuilder;

use Module\FormBuilder\Field;

/**
 * Creates a Fieldset
 * @internal
 */
class Fieldset {
	/**
	 * Class to apply to the field
	 * @var string
	 */
	public $class;

	/**
	 * The legend of the fieldset 
	 * @var string
	 */
	public $legend;

	/**
	 * an array of Module\FormBulder\Field
	 * @var [type]
	 */
	private $fields;

	/**
	 * Constructor for the fieldset
	 * @param object $object
	 */
	public function __construct($object) {
		if (isset($object->class)) {
			$this->class = $object->class;
		}

		if (isset($object->legend)) {
			$this->legend = $object->legend;
		}


		foreach ($object->fields as $field) {
			$this->fields[] = new Field($field);
		}
	}

	/**
	 * Renders a complete fieldset with fields
	 * @param  boolean $validate
	 * @return string
	 */
	public function render($validate) {
		$buffer = array();
		$buffer[] = "<fieldset class=\"{$this->class}\">";

		if (isset($this->legend)) {
			$buffer[] = "<legend>{$this->legend}</legend>";
		}

		$buffer[] = "<ul>";

		foreach($this->fields as $field) {
			$buffer[] = "<li>" . $field->render($validate) . "</li>";
		}

		$buffer[] = "</ul>";
		$buffer[] = "</fieldset>";

		return implode(" ", $buffer);
	}

	/**
	 * Validates all the fields in a fieldset
	 * @return array
	 */
	public function validate() {
		$errors = array();

		foreach($this->fields as $field) {
			$error = $field->validate();
			if (is_array($error)) {
				$errors = array_merge($errors, $error);
			}
		}

		return $errors;
	}

	/**
	 * Gets the datamap for all fields in a fieldset
	 * @return [type]
	 */
	public function getDataMap() {
		$fieldsetData = array();

		foreach ($this->fields as $field) {
			if (is_array($field->getDataMap())) {
				$fieldsetData = array_merge($fieldsetData, $field->getDataMap());
			}
		}

		return $fieldsetData;
	}
}