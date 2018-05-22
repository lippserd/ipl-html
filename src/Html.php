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
     * Create a HTML element
     *
     * This method supports the following signatures:
     *
     * * Html::tag($tag, $content) and
     * * Html::tag($tag, $attributes, $content)
     *
     * with the following parameter specification:
     *
     * * ValidHtml|string|array **$content**
     * * Attributes|array       **$attributes**
     *
     * If you want to omit the attributes of the element, just specify content as the second parameter. Note that if you
     * specify attributes and content for the element, attributes must be the 2nd and content must be the 3rd parameter
     * to this method.
     *
     * @param   string  $tag                                    The tag for the element
     * @param   mixed                   $attributesOrContent    Either the content of the element if you want to omit
     *                                                          the attributes or the attributes of the element either
     *                                                          as {@link Attributes} instance or array of attribute
     *                                                          name-value pairs
     * @param   ValidHtml|string|array  $content                The content of the element if attributes are present
     *
     * @return  HtmlElement The created element
     */
    public static function tag($tag, $attributesOrContent = null, $content = null)
    {
        if ($content === null) {
            // Detect whether attributes is content
            $attributesIsContent = false;
            if (is_array($attributesOrContent)) {
                reset($attributesOrContent);
                $first = key($attributesOrContent);

                if (is_int($first) && ! $attributesOrContent[$first] instanceof Attribute) {
                    $attributesIsContent = true;
                }
            } elseif (! $attributesOrContent instanceof Attributes) {
                $attributesIsContent = true;
            }

            if ($attributesIsContent) {
                $content = $attributesOrContent;
                $attributesOrContent = null;
            }
        }

        return new HtmlElement($tag, $attributesOrContent, $content);
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
