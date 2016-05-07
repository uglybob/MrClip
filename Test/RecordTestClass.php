<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Record;

class RecordTestClass extends Record
{
    // {{{ variables
    public $start;
    public $end;
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
