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
        $this->parser = new ParserTestClass($options, $this->commands);
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

    // {{{ cacheAttributes
    protected function cacheAttributes()
    {
        // no caching here
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

    // {{{ callRecordAdd
    public function callRecordAdd()
    {
        return parent::callRecordAdd();
    }
    // }}}
    // {{{ callRecordCurrent
    public function callRecordCurrent()
    {
        return parent::callRecordCurrent();
    }
    // }}}
    // {{{ callRecordStop
    public function callRecordStop()
    {
        return parent::callRecordStop();
    }
    // }}}
    // {{{ callStop
    public function callStop()
    {
        return parent::callStop();
    }
    // }}}
    // {{{ callStatus
    public function callStatus()
    {
        return parent::callStatus();
    }
    // }}}
    // {{{ callRecordContinue
    public function callRecordContinue()
    {
        return parent::callRecordContinue();
    }
    // }}}
    // {{{ callContinue
    public function callContinue()
    {
        return parent::callContinue();
    }
    // }}}

    // {{{ callTodoList
    public function callTodoList()
    {
        return parent::callTodoList();
    }
    // }}}
    // {{{ callTodoEdit
    public function callTodoEdit()
    {
        return parent::callTodoEdit();
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
