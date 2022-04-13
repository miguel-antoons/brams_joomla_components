<?php

App::uses('Model', 'Model');

class AppModel extends Model {
    public $recursive = -1;

    public function identicalFieldValues($field = array(), $compareField = null, $compareFunction = null) {
        foreach ($field as $key => $value) {
            $v1 = $value;
            if ($compareFunction === null) {
                $v2 = $this->data[$this->name][$compareField];
            } else {
                $v2 = call_user_func(array($this, $compareFunction), $this->data[$this->name][$compareField]);
            }
            if ($v1 !== $v2) {
                return false;
            }
        }
        return true;
    }

    public function greaterThan($field = array(), $compareField = null) {
        $v2 = $this->data[$this->name][$compareField];
        foreach ($field as $key => $v1) {
            if ($v1 <= $v2) {
                return false;
            }
        }
        return true;
    }

    public function lessThan($field = array(), $compareField = null) {
        $v2 = $this->data[$this->name][$compareField];
        if (!is_numeric($v2)) {
            return false;
        }
        foreach ($field as $key => $v1) {
            if ($v1 >= $v2) {
                return false;
            }
        }
        return true;
    }

    public function alphaNumericDashUnderscore($field) {
        /* The current field $field to be validated is passed into the function
         * as first parameter as an associated array with field name as key
         * and posted data as value. */
        $value = array_values($field);
        $value = $value[0];

        return preg_match('|^[0-9a-zA-Z_-]*$|', $value);
    }

    public function beforeSave($options = array()) {
        /**
         * Checks table metadata for fields which allows NULL and set the value
         * to NULL when they are empty
         *
         * TODO: probably make this a behavior?
         */
        $tableInfo = $this->schema();
        foreach ($tableInfo as $name => $field) {
            if ($field['null']) {
                if (isset($this->data[$this->name][$name]) && $this->data[$this->name][$name] === '') {
                    $this->data[$this->name][$name] = null;
                }
            }
        }

        return true;
    }
}