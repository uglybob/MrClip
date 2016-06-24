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
    public $fs = [];
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

    // {{{ fsWrite
    protected function fsWrite($name, $data)
    {
        $this->fs[$name] = $data;
    }
    // }}}
    // {{{ fsRead
    protected function fsRead($name, $ttl = null)
    {
        return (isset($this->fs[$name])) ? $this->fs[$name] : false;
    }
    // }}}

    // {{{ recordAdd
    public function recordAdd()
    {
        return parent::recordAdd();
    }
    // }}}

    // {{{ formatTodos
    public function formatTodos($todos, $hideDone)
    {
        return parent::formatTodos($todos, $hideDone);
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
