<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Todo;

class TodoTestClass extends Todo
{
    // {{{ variables
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
}
