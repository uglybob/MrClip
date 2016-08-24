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
    // {{{ fsUnlink
    protected function fsUnlink($path)
    {
        unset($this->fs[$path]);
    }
    // }}}

    // {{{ recordAdd
    public function recordAdd()
    {
        return parent::recordAdd();
    }
    // }}}
    // {{{ recordCurrent
    public function recordCurrent()
    {
        return parent::recordCurrent();
    }
    // }}}
    // {{{ recordStop
    public function recordStop()
    {
        return parent::recordStop();
    }
    // }}}
    // {{{ stop
    public function stop()
    {
        return parent::stop();
    }
    // }}}
    // {{{ status
    public function status()
    {
        return parent::status();
    }
    // }}}

    // {{{ todoList
    public function todoList()
    {
        return parent::todoList();
    }
    // }}}
    // {{{ todoEdit
    public function todoEdit()
    {
        return parent::todoEdit();
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
    public function getFilteredTodos($includeDone = true)
    {
        return parent::getFilteredTodos($includeDone);
    }
    // }}}

    // {{{ editAndParse
    public function editAndParse($string, $todos)
    {
        return parent::editAndParse($string, $todos);
    }
    // }}}

    // {{{ procRun
    public function procRun($executable, $arguments)
    {
        $this->fsWrite($arguments, $this->userEditString);
    }
    // }}}
    // {{{ input
    public function input($string = '')
    {
        return 'y';
    }
    // }}}
}
