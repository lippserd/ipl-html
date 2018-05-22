<?php

namespace ipl\Html;

/**
 * Base class for HTML elements
 */
abstract class BaseHtmlElement extends HtmlDocument
{
    /**
     * List of void elements which do not contain closing tags or content
     *
     * This property should be used to decide whether the content and closing tag has to be rendered.
     *
     * @var array
     *
     * @see https://www.w3.org/TR/html5/syntax.html#void-elements
     */
    public static $voidElements = [
        'area'      => 1,
        'base'      => 1,
        'br'        => 1,
        'col'       => 1,
        'embed'     => 1,
        'hr'        => 1,
        'img'       => 1,
        'input'     => 1,
        'keygen'    => 1,
        'link'      => 1,
        'meta'      => 1,
        'param'     => 1,
        'source'    => 1,
        'track'     => 1,
        'wbr'       => 1
    ];

    /**
     * The default HTML attributes of the element if no attributes were given
     *
     * @var Attributes
     */
    protected $defaultAttributes;

    /**
     * The tag of the HTML element
     *
     * @var string
     */
    protected $tag;

    /**
     * The HTML attributes of the element
     *
     * @var Attributes
     */
    private $attributes;

    /**
     * Get the HTML attributes of the element
     *
     * @return  Attributes
     */
    public function getAttributes()
    {
        if ($this->attributes === null) {
            if (! empty($this->defaultAttributes)) {
                $this->attributes = Attributes::ensureAttributes($this->defaultAttributes);
            } else {
                $this->attributes = new Attributes();
            }
        }

        return $this->attributes;
    }

    /**
     * Set the HTML attributes of the element
     *
     * @param   Attributes|array|null   $attributes
     *
     * @return  $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = Attributes::ensureAttributes($attributes);

        return $this;
    }

    /**
     * Get the HTML tag of the element
     *
     * @return  string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Get whether the element should render the closing tag
     *
     * @return  bool
     */
    public function wantsClosingTag()
    {
        $tag = $this->getTag();

        return ! isset(self::$voidElements[$tag]);
    }

    /**
     * Render the content of the element
     *
     * @return  string
     */
    protected function renderContent()
    {
        return parent::render();
    }

    public function render()
    {
        $tag = $this->getTag();

        $this->ensureAssembled();

        if (empty($tag)) {
            $html = $this->renderContent();
        } else {
            $html = [
                // rtrim because attributes may be empty
                rtrim("<$tag " . $this->getAttributes()->render())
                . '>'
            ];

            if ($this->wantsClosingTag()) {
                $html[] = $this->renderContent();
                $html[] = "</$tag>";
            }

            $html = implode('', $html);
        }

        return $html;
    }
}
