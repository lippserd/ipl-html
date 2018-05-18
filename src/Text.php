<?php

namespace ipl\Html;

/**
 * A HTML text element
 *
 * The content of the text element is HTML-encoded using {@link Html::encode()} if necessary.
 */
class Text implements ValidHtml
{
    /**
     * The content to render
     *
     * @var string
     */
    protected $content;

    /**
     * Whether the content is already HTML-encoded
     *
     * @var bool
     */
    protected $encoded;

    /**
     * Create a text element
     *
     * @param   string  $content    The content to render
     * @param   bool    $encoded    Whether the content is already HTML-encoded
     */
    public function __construct($content, $encoded = false)
    {
        $this->content = (string) $content;
        $this->encoded = (bool) $encoded;
    }

    public function render()
    {
        if ($this->encoded) {
            return $this->content;
        }

        return Html::encode($this->content);
    }
}
