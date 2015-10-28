<?php

namespace Phpantom;

use Respect\Validation\Validator as v;

class Item
{
    protected $id;
    protected $type;
    /**
     * @return v
     * Usage:
     *  return v::attribute('name', v::stringType()->length(1,32))
     *          ->attribute('birthdate', v::date()->age(18));
     */
    protected function getValidator()
    {
        return v::create();
    }

    /**
     * @param $var
     * @return mixed
     * @throws \Exception
     */
    final public function __get($var)
    {
        if (property_exists($this, $var)) {
            return $this->$var;
        }
        throw new \DomainException('Getting unknown property ' . $var);
    }

    /**
     * @param $var
     * @param $val
     * @throws \Exception
     */
    final public function __set($var, $val)
    {
        if (property_exists($this, $var)) {
            $this->$var = $val;
        } else {
            throw new \DomainException('Setting unknown property ' . $var);
        }
    }
    /**
     *
     */
    final public function validate()
    {
        $validator = $this->getValidator();
        if (!$validator || !$validator instanceof \Respect\Validation\Validator) {
            throw new \Exception(
                sprintf('Validator expected to be \Respect\Validation\Validator, %s given', gettype($validator))
            );
        }
        $validator->attribute('id', v::notEmpty()->alnum())
            ->attribute('type', v::notEmpty()->alnum());
        return $validator->assert($this);
    }


    /**
     * @return array
     */
    final public function asArray()
    {
        return get_object_vars($this);
    }
}
