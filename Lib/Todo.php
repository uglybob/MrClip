<?php

namespace Uglybob\MrClip\Lib;

class Todo extends Entry
{
    // {{{ variables
    protected $parent;
    protected $parentId;
    protected $done;
    protected $children;
    protected $match;
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
        $match = $this->lexicalMatch($candidate, 80)
            + $this->positionMatch($candidate, 19)
            + $this->doneMatch($candidate, 1);

        if ($match > $this->confidence) {
            $this->confidence = $match;
            $this->match = $candidate;
        }

        return $match;
    }
    // }}}
    // {{{ resetMatch
    public function resetMatch()
    {
        $this->confidence = 0;
        $this->match = null;
    }
    // }}}

    // {{{ positionMatch
    public function positionMatch($candidate = null, $max = 1)
    {
        $parentA = $this->parent;
        $parentB = (is_null($candidate)) ? $this->match->getParent() : $candidate->getParent();

        if ($parentA && $parentB) {
            $percent = $parentA->lexicalMatch($parentB, 99)
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
    public function lexicalMatch($candidate = null, $max = 1)
    {
        if (is_null($candidate)) {
            $candidate = $this->match;
        }

        $percent = $this->activityMatch($candidate, 10)
            + $this->categoryMatch($candidate, 10)
            + $this->tagsMatch($candidate, 30)
            + $this->textMatch($candidate, 50);

        return (int) ($percent * $max / 100);
    }
    // }}}

    // {{{ activityMatch
    protected function activityMatch($candidate, $max)
    {
        $percent = $this->compareExact($this->activity, $candidate->getActivity());

        return (int) ($percent * $max / 100);
    }
    // }}}
    // {{{ categoryMatch
    protected function categoryMatch($candidate, $max)
    {
        $percent = $this->compareExact($this->category, $candidate->getCategory());

        return (int) ($percent * $max / 100);
    }
    // }}}
    // {{{ tagsMatch
    protected function tagsMatch($candidate, $max)
    {
        $percent = $this->tagsCompare($this->tags, $candidate->getTags());

        return (int) ($percent * $max / 100);
    }
    // }}}
    // {{{ textMatch
    protected function textMatch($candidate, $max)
    {
        $percent = $this->textCompare($this->text, $candidate->getText());

        return (int) ($percent * $max / 100);
    }
    // }}}
    // {{{ doneMatch
    public function doneMatch($candidate = null, $max = 1)
    {
        if (is_null($candidate)) {
            $candidate = $this->match;
        }

        $percent = $this->compareExact($this->isDone(), $candidate->isDone());

        return (int) ($percent * $max / 100);
    }
    // }}}

    // {{{ compareExact
    protected function compareExact($a, $b)
    {
        if ($a === $b) {
            $confidence = 100;
        } else {
            $confidence = 0;
        }

        return $confidence;
    }
    // }}}
    // {{{ tagsCompare
    protected function tagsCompare($tagsA, $tagsB)
    {
        $tagWeight = 100 / 3;

        $count = count(array_diff($tagsA, $tagsB)) + count(array_diff($tagsB, $tagsA));
        $diff = 100 - ($tagWeight * $count);

        if ($diff > 0) {
            $confidence = $diff;
        } else {
            $confidence = 0;
        }

        return (int) $confidence;
    }
    // }}}
    // {{{ textCompare
    protected function textCompare($textA, $textB)
    {
        if (strcmp($textA, $textB) === 0) {
            $confidence = 100;
        } else {
            similar_text($textA, $textB, $percent);
            $confidence = (int) $percent;
        }

        return $confidence;
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
