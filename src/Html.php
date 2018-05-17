<?php

namespace ipl\Html;

use Exception;
use InvalidArgumentException;

class Html
{
    /** @var bool */
    protected static $showTraces = true;

    /**
     * Convert special characters to HTML5 entities using the UTF-8 character set for encoding
     *
     * This method internally uses {@link htmlspecialchars} with the following flags:
     * * Single quotes are not escaped (ENT_COMPAT)
     * * Uses HTML5 entities, disallowing &#013; (ENT_HTML5)
     * * Invalid characters are replaced with � (ENT_SUBSTITUTE)
     *
     * Already existing HTML entities will be encoded as well.
     *
     * @param   string  $content        The content to encode
     *
     * @return  string  The encoded content
     */
    public static function encode($content)
    {
        return htmlspecialchars($content, ENT_COMPAT | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @deprecated Use {@link Html::encode()} instead
     */
    public static function escapeForHtml($content)
    {
        return self::encode($content);
    }

    /**
     * Create a HTML element from the given tag, attributes and content
     *
     * This method does not render the HTML element but creates a {@link HtmlElement} instance from the given tag,
     * attributes and content
     *
     * @param   string                  $tag        The tag for the element
     * @param   Attributes|array        $attributes The HTML attributes for the element
     * @param   ValidHtml|string|array  $content    The contentl of the element
     *
     * @return  HtmlElement The created element
     */
    public static function tag($tag, $attributes = null, $content = null)
    {
        return new HtmlElement($tag, $attributes, $content);
    }

    /**
     * Ensure that the given content of mixed type is converted to HTML elements that promise to render safe HTML
     *
     * The conversion procedure is as follows:
     *
     * If the content implements the {@link ValidHtml} interface, no conversion is applied. If the content is of a
     * scalar type, it will be converted to a {@link Text} element. If the content is an array, the conversion procedure
     * will be applied to every element in the array. A {@link HtmlDocument} object will be returned in this case which
     * contains the converted elements.
     *
     * @param   ValidHtml|string|array  $content
     *
     * @return  ValidHtml|Text|HtmlDocument
     *
     * @throws  InvalidArgumentException    In case the given content is of an unsupported type
     */
    public static function ensureHtml($content)
    {
        if ($content instanceof ValidHtml) {
            return $content;
        }

        if (is_scalar($content)) {
            return new Text($content);
        }

        if (is_array($content)) {
            $html = new HtmlDocument();

            if (! empty($content)) {
                foreach ($content as $element) {
                    $html->add(static::ensureHtml($element));
                }
            }

            return $html;
        }

        if (is_object($content)) {
            $type = get_class($content);
        } else {
            $type = gettype($content);
        }

        throw new InvalidArgumentException(
            "Instance of ValidHtml, array or scalar type expected. Got $type instead."
        );
    }

    /**
     * @deprecated Use {@link Html::ensureHtml()} instead
     */
    public static function wantHtml($content)
    {
        return self::ensureHtml($content);
    }

    /**
     * @param $any
     * @return string
     */
    public static function getPhpTypeName($any)
    {
        if (is_object($any)) {
            return get_class($any);
        } else {
            return gettype($any);
        }
    }
    /**
     * @param $name
     * @param $arguments
     * @return HtmlElement
     */
    public static function __callStatic($name, $arguments)
    {
        $attributes = array_shift($arguments);
        $content = null;
        if ($attributes instanceof ValidHtml || is_string($attributes)) {
            $content = $attributes;
            $attributes = null;
        } elseif (is_array($attributes)) {
            if (empty($attributes)) {
                $attributes = null;
            } elseif (is_int(key($attributes))) {
                $content = $attributes;
                $attributes = null;
            }
        }

        if (!empty($arguments)) {
            if (null === $content) {
                $content = $arguments;
            } else {
                $content = [$content, $arguments];
            }
        }

        return new HtmlElement($name, $attributes, $content);
    }

    /**
     * @param Exception|string $error
     * @return string
     */
    public static function renderError($error)
    {
        if ($error instanceof Exception) {
            $file = preg_split('/[\/\\\]/', $error->getFile(), -1, PREG_SPLIT_NO_EMPTY);
            $file = array_pop($file);
            $msg = sprintf(
                '%s (%s:%d)',
                $error->getMessage(),
                $file,
                $error->getLine()
            );
        } elseif (is_string($error)) {
            $msg = $error;
        } else {
            $msg = 'Got an invalid error'; // TODO: translate?
        }

        $output = sprintf(
            // TODO: translate? Be careful when doing so, it must be failsafe!
            "<div class=\"exception\">\n<h1><i class=\"icon-bug\">"
            . "</i>Oops, an error occurred!</h1>\n<pre>%s</pre>\n",
            static::escapeForHtml($msg)
        );

        if (static::showTraces()) {
            $output .= sprintf(
                "<pre>%s</pre>\n",
                static::escapeForHtml($error->getTraceAsString())
            );
        }
        $output .= "</div>\n";
        return $output;
    }

    /**
     * @param null $show
     * @return bool|null
     */
    public static function showTraces($show = null)
    {
        if ($show !== null) {
            self::$showTraces = $show;
        }

        return self::$showTraces;
    }
}
