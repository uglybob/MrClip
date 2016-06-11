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
    public $match;
    public $confidence;
    // }}}
    // {{{ getFormattedTags
    public function getFormattedTags()
    {
        return parent::getFormattedTags();
    }
    // }}}

    // {{{ activityMatch
    public function activityMatch($candidate, $max)
    {
        return parent::activityMatch($candidate, $max);
    }
    // }}}
    // {{{ categoryMatch
    public function categoryMatch($candidate, $max)
    {
        return parent::categoryMatch($candidate, $max);
    }
    // }}}
    // {{{ tagsMatch
    public function tagsMatch($candidate, $max)
    {
        return parent::tagsMatch($candidate, $max);
    }
    // }}}
    // {{{ textMatch
    public function textMatch($candidate, $max)
    {
        return parent::textMatch($candidate, $max);
    }
    // }}}
}
