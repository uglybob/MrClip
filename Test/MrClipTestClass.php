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

        $this->prm = new PrmTestClass();
    }
    // }}}

    // {{{ run
    public function run($options = [])
    {
        return parent::run($options);
    }
    // }}}
    // {{{ output
    public function output($string = '')
    {
        $this->echoed .= $string;
    }
    // }}}
}
