<?php

namespace Uglybob\MrClip\Lib;

class Todo extends Entry
{
    // {{{ variables
    protected $parent;
    protected $parentId;
    protected $done;
    protected $children;
    protected $guess;
    protected $confidence;
    // }}}
    // {{{ constructor
    public function __construct($id = null, $activity = null, $category = null, $tags = [], $text = null, $parentId = null, $done = null)
    {
        parent::__construct($id, $activity, $category, $tags, $text);

        $this->parent = null;
        $this->parentId = $parentId;
        $this->done = $done;
        $this->children = [];
        $this->confidence = null;
    }
    // }}}

    // {{{ getDone
    public function getDone()
    {
        return $this->done;
    }
    // }}}
    // {{{ isDone
    public function isDone()
    {
        return (bool) $this->done;
    }
    // }}}
    // {{{ getParent
    public function getParent()
    {
        return $this->parent;
    }
    // }}}
    // {{{ getParentId
    public function getParentId()
    {
        return $this->parentId;
    }
    // }}}
    // {{{ getChildren
    public function getChildren()
    {
        return $this->children;
    }
    // }}}
    // {{{ getGuess
    public function getGuess()
    {
        return $this->guess;
    }
    // }}}
    // {{{ getConfidence
    public function getConfidence()
    {
        return $this->confidence;
    }
    // }}}

    // {{{ setParent
    public function setParent(Todo $parent)
    {
        $this->parent = $parent;
    }
    // }}}

    // {{{ format
    public function format()
    {
        if ($this->isDone()) $outout[] = '#';
        $output[] = parent::format();

        return implode(' ', $output);
    }
    // }}}
    // {{{ formatFiltered
    public function formatFiltered($tagFilter = [])
    {
        $output = [];

        if ($this->isDone()) $output[] = '#';
        $tags = array_diff($this->tags, $tagFilter);
        if (!empty($tags)) $output[] = $this->getFormattedTags();
        if (!empty($this->text)) $output[] = $this->text;

        return implode(' ', $output);
    }
    // }}}

    // {{{ addChild
    public function addChild(Todo $child)
    {
        $this->children[] = $child;
    }
    // }}}

    // {{{ match
    public function match(Todo $candidate)
    {
        $confidence = 0;

        if ($this->activity == $candidate->getActivity()) $confidence += 10;
        if ($this->category == $candidate->getCategory()) $confidence += 10;

        $diffs = count(array_diff($this->tags, $candidate->getTags())) + count(array_diff($candidate->getTags(), $this->tags));
        $tagConfidence = 30 - 10 * $diffs;
        if ($tagConfidence > 0) $confidence += $tagConfidence;

        $textConfidence = 49 - abs(strcmp($this->text, $candidate->getText()));
        $confidence += $textConfidence;

        if ($this->isDone() == $candidate->isDone()) $confidence += 1;

        if ($confidence > $this->confidence) {
            $this->confidence = $confidence;
            $this->guess = $candidate;
        }

        return $confidence;
    }
    // }}}
}