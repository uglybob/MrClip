<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Record;

class RecordTestClass extends Record
{
    // {{{ variables
    public $start;
    public $end;
    public $activity;
    public $category;
    public $tags;
    public $text;
    // }}}
    // {{{ getFormattedTags
    public function getFormattedTags()
    {
        return parent::getFormattedTags();
    }
    // }}}

    // {{{ stringToDatetime
    public function stringToDatetime($timeString)
    {
        return parent::stringToDatetime($timeString);
    }
    // }}}
}
