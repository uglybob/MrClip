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
    public $done;
    public $children;
    public $guess;
    public $confidence;
    // }}}
    // {{{ getFormattedTags
    public function getFormattedTags()
    {
        return parent::getFormattedTags();
    }
    // }}}

    // {{{ activityConfidence
    public function activityConfidence($activityA, $activityB, $max)
    {
        return parent::activityConfidence($activityA, $activityB, $max);
    }
    // }}}
    // {{{ categoryConfidence
    public function categoryConfidence($categoryA, $categoryB, $max)
    {
        return parent::categoryConfidence($categoryA, $categoryB, $max);
    }
    // }}}
    // {{{ tagsConfidence
    public function tagsConfidence($tagsA, $tagsB, $max)
    {
        return parent::tagsConfidence($tagsA, $tagsB, $max);
    }
    // }}}
    // {{{ textConfidence
    public function textConfidence($textA, $textB, $max)
    {
        return parent::textConfidence($textA, $textB, $max);
    }
    // }}}
    // {{{ doneConfidence
    public function doneConfidence($doneA, $doneB, $max)
    {
        return parent::doneConfidence($doneA, $doneB, $max);
    }
    // }}}
}
