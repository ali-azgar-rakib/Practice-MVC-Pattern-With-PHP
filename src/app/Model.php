<?php

namespace App;

abstract class Model
{
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';

    public array $errors = [];
    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }


    public abstract function rules(): array;
    public function validate()
    {
        foreach ($this->rules() as $attribute => $rules) {
            $value = $this->{$attribute};
            foreach ($rules as $rule) {
                $ruleName = $rule;
                if (is_array($ruleName)) {
                    $ruleName = $ruleName[0];
                }

                if ($ruleName === self::RULE_REQUIRED && !$value) {
                    $this->addError($attribute, $ruleName);
                }
                if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_SANITIZE_EMAIL)) {
                    $this->addError($attribute, $ruleName);
                }
                if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
                    $this->addError($attribute, $ruleName, $rule);
                }
                if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
                    $this->addError($attribute, $ruleName, $rule);
                }
                if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule[self::RULE_MATCH]}) {
                    $this->addError($attribute, $ruleName, $rule);
                }
            }
        }

        return empty($this->errors);
    }

    public function addError($attribute, $ruleName, $params = [])
    {
        $message = $this->getErrorMessage()[$ruleName] ?? '';
        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        $this->errors[$attribute][] = $message;
    }

    public function getErrorMessage()
    {
        return [
            self::RULE_REQUIRED => "This field is required",
            self::RULE_EMAIL => "This field must be valid email address",
            self::RULE_MIN => "Minimum length of this field must be {min}",
            self::RULE_MAX => "Maximum length of this field must be {max}",
            self::RULE_MATCH => "This field must be same as {match}",
        ];
    }
}
