<?php

namespace ipl\Html;

use InvalidArgumentException;
use UnexpectedValueException;

/**
 * HTML Attribute representation
 *
 * Every single HTML attribute should be an instance of this class. This guarantees that every attribute is safe and
 * escaped correctly.
 *
 * Usually attributes are not instantiated directly but created through an HTML element's exposed methods.
 */
class Attribute
{
    /**
     * The name of the attribute
     *
     * @var string
     */
    protected $name;

    /**
     * The value of the attribute
     *
     * @var string|bool|array|null
     */
    protected $value;

    /**
     * {@link getValue()} callback
     *
     * @var callable
     */
    protected $getter;

    /**
     * {@link setValue()} callback
     *
     * @var callable
     */
    protected $setter;

    /**
     * Create a new HTML attribute from the given name and value
     *
     * @param   string                  $name   The name of the attribute
     * @param   string|bool|array|null  $value  The value of the attribute
     *
     * @throws  InvalidArgumentException        If the name of the attribute contains special characters
     */
    public function __construct($name, $value = null)
    {
        $name = (string) $name;

        if (! preg_match('/^[a-z][a-z0-9:_.-]*$/i', $name)) {
            throw new InvalidArgumentException(
                "Can't create attribute \"{$name}\". Attribute names with special characters are not allowed."
            );
        }

        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get the name of the attribute
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of the attribute
     *
     * @return string|bool|array|null
     */
    public function getValue()
    {
        if ($this->getter !== null) {
            return call_user_func($this->getter);
        }

        return $this->value;
    }

    /**
     * Set the value of the attribute
     *
     * @param   string|bool|array|null  $value  The value of the HTML attribute
     *
     * @return  $this
     */
    public function setValue($value)
    {
        if ($this->setter !== null) {
            $value = call_user_func($this->setter, $value);
        }
        $this->value = $value;

        return $this;
    }

    /**
     * Add a value to the attribute avoiding duplicates
     *
     * @param   string|string[] $value  The value to add
     *
     * @return  $this
     */
    public function addValue($value)
    {
        if (! is_array($this->value)) {
            $this->value = [$this->value];
        }

        if (is_array($value)) {
            $this->value = array_unique(array_merge($this->value, $value));
        } else {
            $this->value[] = $value;
        }

        return $this;
    }

    /**
     * Remove a value of the attribute
     *
     * @param   string|string[] $value      The value to remove
     *
     * @return  $this
     *
     * @throws  UnexpectedValueException    If the current value is not an array
     */
    public function removeValue($value)
    {
        if (! is_array($this->value)) {
            throw new UnexpectedValueException('Can\'t remove value from non-array variable.');
        }

        if (! is_array($value)) {
            $value = [$value];
        }

        $this->value = array_diff($this->value, $value);

        return $this;
    }

    /**
     * Set {@link getValue()} or {@link setValue()} callback(s)
     *
     * @param   callable    $getter
     * @param   callable    $setter
     *
     * @return  $this
     */
    public function setCallback(callable $getter = null, callable $setter = null)
    {
        $this->getter = $getter;
        $this->setter = $setter;

        return $this;
    }

    /**
     * Render the attribute to HTML
     *
     * If the value of the attribute is of type boolean, it will be rendered as
     * {@link http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes boolean attribute}.
     * Note that null will be returned, if the value of the attribute is false.
     *
     * Also, if the value of the attribute is null, null will be returned
     *
     * HTML-encoding of the attribute's value takes place automatically using {@link Html::encode()}.
     *
     * @return  string|null
     */
    public function render()
    {
        $value = $this->getValue();

        if ($value === false || $value === null) {
            return null;
        }

        $name = $this->getName();

        if ($value === true) {
            return $name;
        }

        if (is_array($value)) {
            $value = implode($name === 'style' ? ';' : ' ', $value);
        }

        return $name . '="' . Html::encode($value) . '"';
    }
}
