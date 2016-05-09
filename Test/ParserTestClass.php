<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Parser;

class ParserTestClass extends Parser
{
    // {{{ variables
    public $options;
    public $commands;
    public $position;

    public $domain;
    public $command;
    public $activity;
    public $category;
    public $tags;
    public $text;
    public $start;
    public $end;
    public $done;
    // }}}

    // {{{ process
    public function process($regex)
    {
        return parent::process($regex);
    }
    // }}}
    // {{{ current
    public function current()
    {
        return parent::current();
    }
    // }}}
    // {{{ advance
    public function advance()
    {
        return parent::advance();
    }
    // }}}
}
