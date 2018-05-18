<?php

namespace ipl\Html;

use InvalidArgumentException;
use IteratorAggregate;

/**
 * Attributes represents HTML attributes
 */
class Attributes implements ValidHtml, IteratorAggregate
{
    /**
     * Internal storage for attributes
     *
     * @var Attribute[]
     */
    protected $attributes = [];

    /**
     * Attribute name prefix
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Create a new Attributes container from the given attribute name-value pairs
     *
     * @param   array   $attributes Attribute name-value pairs
     */
    public function __construct(array $attributes = null)
    {
        if (empty($attributes)) {
            return;
        }

        $this->add($attributes);
    }

    /**
     * Get whether the attribute with the given name exists
     *
     * @param   string  $name   The name of the attribute
     *
     * @return  bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Get the attribute with the given name
     *
     * If the attribute does not yet exist, it is automatically create and initialised with null.
     *
     * @param   string  $name   The name of the attribute
     *
     * @return  Attribute
     */
    public function get($name)
    {
        if (! array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = new Attribute($name);
        }

        return $this->attributes[$name];
    }

    /**
     * Set the attribute with the given name and value
     *
     * If the attribute with the given name already exists, it gets overridden.
     *
     * @param   string                  $name   The name of the attribute
     * @param   string|bool|array|null  $value  The value of the attribute
     *
     * @return  $this
     */
    public function set($name, $value = null)
    {
        $this->attributes[$name] = new Attribute($name, $value);

        return $this;
    }

    /**
     * Add the given attribute(s)
     *
     * If an attribute with the same name already exists, the attribute's value will be added to the current value of
     * the attribute.
     *
     * @param   array|self|string|Attribute $attribute  The attribute(s) to add
     * @param   string|bool|array|null      $value      The value of the attribute
     *
     * @return $this
     */
    public function add($attribute = null, $value = null)
    {
        if ($attribute === null) {
            return $this;
        }

        if ($attribute instanceof self) {
            foreach ($attribute as $attr) {
                $this->add($attr);
            }

            return $this;
        }

        if (is_array($attribute)) {
            foreach ($attribute as $name => $value) {
                $this->add($name, $value);
            }

            return $this;
        }

        if ($attribute instanceof Attribute) {
            $name = $attribute->getName();

            if (! array_key_exists($name, $this->attributes)) {
                $this->attributes[$name] = $attribute;
            } else {
                $this->attributes[$name]->addValue($attribute->getValue());
            }

            return $this;
        }

        if (! array_key_exists($attribute, $this->attributes)) {
            $this->attributes[$attribute] = new Attribute($attribute, $value);
        } else {
            $this->attributes[$attribute]->addValue($value);
        }

        return $this;
    }

    /**
     * Remove the attribute with the given name or remove the given value from the attribute
     *
     * @param   string                  $name   The name of the attribute
     * @param   null|string|string[]    $value  If given and not null, the value to remove
     *
     * @return  Attribute|false
     */
    public function remove($name, $value = null)
    {
        if (! array_key_exists($name, $this->attributes)) {
            return false;
        }

        $attribute = $this->attributes[$name];

        if ($value === null) {
            unset($this->attributes[$name]);
        } else {
            $attribute->removeValue($value);
        }

        return $attribute;
    }

    /**
     * Set the attributes
     *
     * @param   array|self    $attributes
     *
     * @return  $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = [];

        $this->add($attributes);

        return $this;
    }

    /**
     * Get the attribute name prefix
     *
     * @return  string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the attribute name prefix
     *
     * @param   string  $prefix
     *
     * @return  $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = (string) $prefix;

        return $this;
    }

    /**
     * Set attribute {@link Attribute::getValue()} or {@link Attribute::setValue()} callback(s)
     *
     * @param   string      $attribute  The name of the attribute
     * @param   callable    $getter
     * @param   callable    $setter
     *
     * @return  $this
     */
    public function setCallback($attribute, callable $getter = null, callable $setter = null)
    {
        $this->get($attribute)->setCallback($getter, $setter);

        return $this;
    }

    /**
     * Render attributes to HTML
     *
     * If the value of an attribute is of type boolean, it will be rendered as
     * {@link http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes boolean attribute}.
     *
     * If the value of an attribute is null, it will be skipped.
     *
     * HTML-encoding of the attributes' values takes place automatically using {@link Html::encode()}.
     *
     * @return  string
     */
    public function render()
    {
        $html = [];

        foreach ($this->attributes as $attribute) {
            $html[] = $attribute->render();
        }

        return $this->prefix . implode(' ' . $this->prefix, array_filter($html));
    }

    /**
     * @inheritdoc
     *
     * @return \Generator
     */
    public function getIterator()
    {
        foreach ($this->attributes as $attribute) {
            yield $attribute;
        }
    }

    /**
     * Ensure that the given attributes of mixed type are converted to an instance of attributes
     *
     * The conversion procedure is as follows:
     *
     * If the given attributes is an instance of Attributes, no conversion is applied. If the attributes are given as an
     * array of attribute name-value pairs, they are used to construct and return a new Attributes instance.
     * If the attributes are null, an empty new instance of Attributes is returned.
     *
     * @param   self|array|null $attributes
     *
     * @return  self
     *
     * @throws  InvalidArgumentException    In case the given attributes are of an unsupported type
     */
    public static function ensureAttributes($attributes)
    {
        if ($attributes instanceof self) {
            return $attributes;
        }

        if (is_array($attributes)) {
            return new self($attributes);
        }

        if ($attributes === null) {
            return new self();
        }

        if (is_object($attributes)) {
            $type = get_class($attributes);
        } else {
            $type = gettype($attributes);
        }

        throw new InvalidArgumentException("Attribute instance, array or null expected. Got $type instead.");
    }

    /**
     * @deprecated Use {@link ensureAttributes()} instead
     */
    public static function wantAttributes($attributes)
    {
        return self::ensureAttributes($attributes);
    }
}
