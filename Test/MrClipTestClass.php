<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\MrClip;

class MrClipTestClass extends MrClip
{
    // {{{ variables
    public $prm;
    public $parser;

    public $echoed = '';
    // }}}

    // {{{ output
    public function output($string)
    {
        $this->echoed .= $string;
    }
    // }}}
}
