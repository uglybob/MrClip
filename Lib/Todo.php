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
        $match = $this->lexicalMatch($candidate, 70)
            + $this->positionMatch($candidate, 29)
            + $this->doneMatch($candidate, 1);

        if ($match > $this->match) {
            $this->confidence = $match;
            $this->match = $candidate;
        }

        return $match;
    }
    // }}}

    // {{{ positionMatch
    public function positionMatch($candidate, $max)
    {
        $parentA = $this->parent;
        $parentB = $candidate->getParent();

        if ($parentA && $parentB) {
            $percent = $parentA->lexicalMatch($parentB, 99);
                + $parentA->doneMatch($parentB, 1);

            $positionMatch = (int) ($percent * $max / 100);
        } else if ($parentA == $parentB) {
            $positionMatch = $max;
        } else {
            $positionMatch = 0;
        }

        return $positionMatch;
    }
    // }}}
    // {{{ lexicalMatch
    public function lexicalMatch($candidate, $max)
    {
        $percent = $this->activityMatch($this->activity, $candidate->getActivity(), 10)
            + $this->categoryMatch($this->category, $candidate->getCategory(), 10)
            + $this->tagsMatch($this->tags, $candidate->getTags(), 30)
            + $this->textMatch($this->text, $candidate->getText(), 60);

        return = (int) ($percent * $max / 100);
    }
    // }}}

    // {{{ activityMatch
    protected function activityMatch($candidate, $max)
    {
        if ($this->activity === $candidate->getActivity()) {
            $match = $max;
        } else {
            $match = 0;
        }

        return $match;
    }
    // }}}
    // {{{ categoryMatch
    protected function categoryMatch($candidate, $max)
    {
        if ($this->category === $candidate->getCategory()) {
            $match = $max;
        } else {
            $match = 0;
        }

        return $match;
    }
    // }}}
    // {{{ tagsMatch
    protected function tagsMatch($candidate, $max)
    {
        $tagsA = $this->tags;
        $tagsB = $candidate->getTags();

        $tagWeight = (int) round($max / 3);
        $count = count(array_diff($tagsA, $tagsB)) + count(array_diff($tagsB, $tagsA));
        $diff = $max - ($tagWeight * $count);

        if ($diff > 0) {
            $match = $diff;
        } else {
            $match = 0;
        }

        return $match;
    }
    // }}}
    // {{{ textMatch
    protected function textMatch($candidate, $max)
    {
        $textA = $this->text;
        $textB = $candidate->getText();

        if (strcmp($textA, $textB) === 0) {
            $match = $max;
        } else {
            similar_text($textA, $textB, $percent);
            $match = (int) ($percent * $max / 100);
        }

        return $match;
    }
    // }}}
    // {{{ doneMatch
    protected function doneMatch($candidate, $max)
    {
        if ($this->isDone() === $candidate->isDone()) {
            $match = $max;
        } else {
            $match = 0;
        }

        return $match;
    }
    // }}}

    // {{{ getMatch
    public function getMatch()
    {
        return $this->match;
    }
    // }}}
    // {{{ getConfidence
    public function getConfidence()
    {
        return $this->confidence;
    }
    // }}}
}
