<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\MrClip;

class MrClipTestClass extends MrClip
{
    // {{{ variables
    public $prm;
    public $parser;

    public $echoed = '';
    public $userEditString = [];
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
    // {{{ suggest
    public function suggest($hint, $candidates, $prefix = '')
    {
        return parent::suggest($hint, $candidates, $prefix);
    }
    // }}}
    // {{{ output
    public function output($string = '')
    {
        $this->echoed .= $string;
    }
    // }}}

    // {{{ recordAdd
    public function recordAdd()
    {
        return parent::recordAdd();
    }
    // }}}

    // {{{ formatTodos
    public function formatTodos($todos)
    {
        return parent::formatTodos($todos);
    }
    // }}}
    // {{{ parseTodoList
    public function parseTodoList($list)
    {
        return parent::parseTodoList($list);
    }
    // }}}
    // {{{ getFilteredTodos
    public function getFilteredTodos()
    {
        return parent::getFilteredTodos();
    }
    // }}}
    // {{{ matchTodos
    public function matchTodos($todos, $candidates, $above, $under, $threshold)
    {
        return parent::matchTodos($todos, $candidates, $above, $under, $threshold);
    }
    // }}}

    // {{{ editAndParse
    public function editAndParse($string, $todos)
    {
        return parent::editAndParse($string, $todos);
    }
    // }}}
    // {{{ userEditString
    public function userEditString($string)
    {
        return $this->userEditString;
    }
    // }}}
}
