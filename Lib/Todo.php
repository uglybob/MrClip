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
    public function __construct($id = null, $activity = null, $category = null, $tags = [], $text = null, $parent = null, $done = null)
    {
        parent::__construct($id, $activity, $category, $tags, $text);

        $this->setParent($parent);
        $this->done = $done;
        $this->children = [];
        $this->confidence = null;
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
        $parentId = $this->parentId;

        if ($this->parent) {
            $parentId = $this->parent->getId();
        }

        return $parentId;
    }
    // }}}
    // {{{ getChildren
    public function getChildren()
    {
        return $this->children;
    }
    // }}}

    // {{{ setParent
    public function setParent($parent)
    {
        $this->parentId = null;
        $this->parent = $parent;

        if ($parent) {
            $parent->addChild($this);
        }
    }
    // }}}
    // {{{ setParentId
    public function setParentId($id)
    {
        $this->parentId = $id;
    }
    // }}}
    // {{{ addChild
    public function addChild(Todo $child)
    {
        $this->children[] = $child;
    }
    // }}}

    // {{{ format
    public function format()
    {
        if ($this->isDone()) $output[] = '#';
        $output[] = parent::format();

        return implode(' ', $output);
    }
    // }}}
    // {{{ formatTagsText
    public function formatTagsText($tagFilter = [])
    {
        $output = [];

        if ($this->isDone()) $output[] = '#';
        $tags = array_diff($this->tags, $tagFilter);
        if (!empty($tags)) $output[] = Todo::formatTags($tags);
        if (!empty($this->text)) $output[] = $this->text;

        return implode(' ', $output);
    }
    // }}}

    // {{{ match
    public function match(Todo $candidate)
    {
        $confidence = $this->activityConfidence($this->activity, $candidate->getActivity(), 10)
            + $this->categoryConfidence($this->category, $candidate->getCategory(), 10)
            + $this->tagsConfidence($this->tags, $candidate->getTags(), 30)
            + $this->textConfidence($this->text, $candidate->getText(), 49)
            + $this->doneConfidence($this->isDone(), $candidate->isDone(), 1);

        if ($confidence > $this->confidence) {
            $this->confidence = $confidence;
            $this->guess = $candidate;
        }

        return $confidence;
    }
    // }}}
    // {{{ activityConfidence
    protected function activityConfidence($activityA, $activityB, $max)
    {
        if ($activityA == $activityB) {
            $confidence = $max;
        } else {
            $confidence = 0;
        }

        return $confidence;
    }
    // }}}
    // {{{ categoryConfidence
    protected function categoryConfidence($categoryA, $categoryB, $max)
    {
        if ($categoryA == $categoryB) {
            $confidence = $max;
        } else {
            $confidence = 0;
        }

        return $confidence;
    }
    // }}}
    // {{{ tagsConfidence
    protected function tagsConfidence($tagsA, $tagsB, $max)
    {
        $count = count(array_diff($tagsA, $tagsB)) + count(array_diff($tagsB, $tagsA));
        $diff = $max - 10 * $count;

        if ($diff > 0) {
            $confidence = $diff;
        } else {
            $confidence = 0;
        }

        return $confidence;
    }
    // }}}
    // {{{ textConfidence
    protected function textConfidence($textA, $textB, $max)
    {
        if (strcmp($textA, $textB) === 0) {
            $confidence = $max;
        } else {
            similar_text($textA, $textB, $percent);
            $confidence = (int) ($percent * $max / 100);
        }

        return $confidence;
    }
    // }}}
    // {{{ doneConfidence
    protected function doneConfidence($doneA, $doneB, $max)
    {
        if ($doneA === $doneB) {
            $confidence = $max;
        } else {
            $confidence = 0;
        }

        return $confidence;
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
}
