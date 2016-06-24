<?php

namespace Uglybob\MrClip\Lib;

abstract class Entry
{
    // {{{ variables
    protected $id;
    protected $category;
    protected $activity;
    protected $tags;
    protected $text;
    // }}}
    // {{{ constructor
    public function __construct($id = null, $activity = null, $category = null, $tags = [], $text = null)
    {
        $this->id = $id;
        $this->activity = $activity;
        $this->category = $category;
        $this->setTags($tags);
        $this->text = $text;
    }
    // }}}

    // {{{ getId
    public function getId()
    {
        return $this->id;
    }
    // }}}
    // {{{ getActivity
    public function getActivity()
    {
        return $this->activity;
    }
    // }}}
    // {{{ getCategory
    public function getCategory()
    {
        return $this->category;
    }
    // }}}
    // {{{ getTags
    public function getTags()
    {
        return $this->tags;
    }
    // }}}
    // {{{ getText
    public function getText()
    {
        return $this->text;
    }
    // }}}

    // {{{ setId
    public function setId($id)
    {
        $this->id = $id;
    }
    // }}}
    // {{{ setTags
    public function setTags($tags)
    {
        if (!is_null($tags)) {
            sort($tags);
        }

        $this->tags = $tags;
    }
    // }}}

    // {{{ getActigory
    public function getActigory()
    {
        return $this->activity . '@' . $this->category;
    }
    // }}}
    // {{{ formatBase
    public function formatBase()
    {
        $output[] = $this->getActigory();
        if (!empty($this->tags)) $output[] = $this->getFormattedTags();

        return implode(' ', $output);
    }
    // }}}
    // {{{ format
    public function format()
    {
        $output[] = $this->formatBase();
        if ($this->text) $output[] = $this->text;

        return implode(' ', $output);
    }
    // }}}

    // {{{ getFormattedTags
    protected function getFormattedTags()
    {
        return $this->formatTags($this->tags);
    }
    // }}}
    // {{{ formatTags
    public static function formatTags($tags = [])
    {
        $formatted = '';

        if (!empty($tags)) {
            $formatted = '+' . implode(' +', $tags);
        }

        return $formatted;
    }
    // }}}
}
