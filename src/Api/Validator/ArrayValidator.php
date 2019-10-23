<?php

namespace Yoga\Api\Validator;

class ArrayValidator extends \Yoga\Api\Validator {

    /**
     * @var \Yoga\Api\Validator
     */
    private $itemValidator;

    public function handle($rawValue) {
        $rawValue = parent::handle($rawValue);
        if (!is_array($rawValue)) {
            return null;
        }
        $itemValidator = $this->getItemValidator();
        $itemValidator->setParameterName($this->getParameterName());
        foreach ($rawValue as &$item) {
            $item = $itemValidator->handle($item);
        }
        return $rawValue;
    }

    /**
     * @return \Yoga\Api\Validator
     */
    public function getItemValidator() {
        return $this->itemValidator;
    }

    /**
     * @param \Yoga\Api\Validator $itemValidator
     * @return ArrayValidator
     */
    public function setItemValidator($itemValidator) {
        $this->itemValidator = $itemValidator;
        return $this;
    }

}