<?php

namespace Phpantom;

use Respect\Validation\Validator as v;

class Item implements \Serializable
{
    protected $id;
    protected $type;
    /**
     * @return v
     * Usage:
     *  return v::attribute('name', v::string()->length(1,32))
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


    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize($this->asArray());
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
