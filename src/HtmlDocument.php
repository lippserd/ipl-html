<?php

namespace ipl\Html;

use Countable;

/**
 * Representation of HTML documents
 */
class HtmlDocument implements Countable, ValidHtml
{
    /**
     * Whether the content has been assembled via {@link assemble()}
     *
     * @var bool
     */
    protected $assembled = false;

    /**
     * The content separator for rendering
     *
     * @var string
     */
    protected $separator = "\n";

    /**
     * The content of the element
     *
     * @var ValidHtml[]
     */
    private $content = [];

    /**
     * @var array
     */
    private $contentIndex = [];

    /**
     * Append content to the element
     *
     * @param   ValidHtml|array|string  $content    The content to append
     *
     * @return  $this
     */
    public function add($content)
    {
        $this->ensureAssembled();

        if (is_array($content)) {
            foreach ($content as $c) {
                $this->add($c);
            }
        } else {
            $this->addContentIndexed(Html::ensureHtml($content));
        }

        return $this;
    }

    /**
     * Prepend content to the element
     *
     * @param   ValidHtml|array|string  $content    The content to prepend
     *
     * @return  $this
     */
    public function prepend($content)
    {
        $this->ensureAssembled();

        if (is_array($content)) {
            foreach (array_reverse($content) as $part) {
                $this->prepend($part);
            }
        } else {
            $pos = 0;
            $html = Html::ensureHtml($content);
            array_unshift($this->content, $html);
            $this->incrementIndexKeys();
            $this->addContentByPos($html, $pos);
        }

        return $this;
    }

    /**
     * Remove content from the element
     *
     * @param   ValidHtml   $html   The content to remove
     *
     * @return  $this
     */
    public function remove(ValidHtml $html)
    {
        $key = spl_object_hash($html);

        if (array_key_exists($key, $this->contentIndex)) {
            foreach ($this->contentIndex[$key] as $pos) {
                unset($this->content[$pos]);
            }
        }

        $this->reIndexContent();

        return $this;
    }

    /**
     * Set the content of the element
     *
     * @param   ValidHtml|array|string  $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = array();

        $this->add($content);

        return $this;
    }

    public function count()
    {
        return count($this->content);
    }

    /**
     * Create the initial content for the element
     *
     * Override this method in order to provide the initial content for the element.
     */
    protected function assemble()
    {
    }

    /**
     * Ensure that the initial content for the element is created
     *
     * This method calls {@link assemble()}.
     */
    protected function ensureAssembled()
    {
        if (! $this->assembled) {
            $this->assembled = true;
            $this->assemble();
        }
    }

    public function render()
    {
        $this->ensureAssembled();

        $html = [];

        foreach ($this->content as $content) {
            $html[] = $content->render();
        }

        return implode($this->separator, $html);
    }

    private function reIndexContent()
    {
        $this->contentIndex = [];

        foreach ($this->content as $pos => $html) {
            $this->addContentByPos($html, $pos);
        }
    }

    private function addContentByPos(ValidHtml $html, $pos)
    {
        $key = spl_object_hash($html);

        if (array_key_exists($key, $this->contentIndex)) {
            $this->contentIndex[$key][] = $pos;
        } else {
            $this->contentIndex[$key] = [$pos];
        }
    }

    private function addContentIndexed(ValidHtml $html)
    {
        $pos = count($this->content);
        $this->content[$pos] = $html;
        $this->addContentByPos($html, $pos);
    }

    private function incrementIndexKeys()
    {
        foreach ($this->contentIndex as &$index) {
            foreach ($index as &$pos) {
                ++$pos;
            }
        }
    }
}
