<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Todo;

class TodoTestClass extends Todo
{
    // {{{ variables
    public $activity;
    public $category;
    public $tags;
    public $text;

    public $parent;
    public $parentId;
    public $position;
    public $done;
    public $children;
    public $match;
    public $confidence;
    // }}}
    // {{{ getFormattedTags
    public function getFormattedTags()
    {
        return parent::getFormattedTags();
    }
    // }}}

    // {{{ compareExact
    public function compareExact($a, $b)
    {
        return parent::compareExact($a, $b);
    }
    // }}}
    // {{{ tagsCompare
    public function tagsCompare($tagsA, $tagsB)
    {
        return parent::tagsCompare($tagsA, $tagsB);
    }
    // }}}
    // {{{ textCompare
    public function textCompare($textA, $textB)
    {
        return parent::textCompare($textA, $textB);
    }
    // }}}
}
