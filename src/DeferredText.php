<?php

namespace ipl\Html;

/**
 * Generate content only when rendering
 *
 * This class allows to generate content via a callback. It is called when the element is going to be rendered and
 * escaped to HTML:
 *
 *     $myVar = 'Some value';
 *     $text = new DeferredText(function () use ($myVar) {
 *         return $myVar;
 *     });
 *     $myVar = 'Changed idea';
 *     echo $text->render();
 *
 * The content of the element is HTML-encoded using {@link Html::encode()} if necessary.
 */
class DeferredText implements ValidHtml
{
    /**
     * Callback which returns the content to render
     *
     * @var callable
     */
    protected $callback;

    /**
     * Whether the callback's content is already HTML-encoded
     *
     * @var bool
     */
    protected $encoded;

    /**
     * DeferredText constructor
     *
     * @param   callable    $callback   Callback which returns the content to render
     * @param   bool        $encoded    Whether the callback's content is already HTML-encoded
     */
    public function __construct(callable $callback, $encoded = false)
    {
        $this->callback = $callback;
        $this->encoded = (bool) $encoded;
    }

    public function render()
    {
        $content = call_user_func($this->callback, $this);

        if (! $this->encoded) {
            return Html::encode($content);
        }

        return $content;
    }
}
