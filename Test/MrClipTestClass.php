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
    // {{{ constructor
    public function __construct($options = [])
    {
        parent::__construct($options);

        $this->prm = new PrmMock();
    }
    // }}}

    // {{{ output
    public function output($string = '')
    {
        $this->echoed .= $string;
    }
    // }}}
}
