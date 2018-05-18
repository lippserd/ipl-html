<?php

namespace ipl\Html;

/**
 * Render formatted text
 */
class FormattedText implements ValidHtml
{
    /**
     * The format string
     *
     * @var ValidHtml
     */
    protected $format;

    /**
     * The format string arguments
     *
     * @var ValidHtml[]
     */
    protected $args;

    /**
     * FormattedText constructor
     *
     * @param   string  $format     The format string. See {@link sprintf()} for a description of format
     * @param   array   ...$args    The format string arguments
     */
    public function __construct($format, ...$args)
    {
        $this->format = Html::ensureHtml($format);
        $this->args = array_walk($args, 'Html::ensureHtml');
    }

    public function render()
    {
        return vsprintf(
            $this->format,
            $this->args
        );
    }
}
