<?php

namespace Uglybob\MrClip\Test;

class MrClipCommandTestClass extends MrClipTestClass
{
    public $called = [];
    public $executed = [];

    public function __call($name, $arguments)
    {
        $this->called[] = $name;
    }

    // {{{ completion
    public function completion($options)
    {
        $this->executed[] = 'completion';
    }
    // }}}

    // {{{ callRecordAdd
    public function callRecordAdd()
    {
        $this->executed[] = 'callRecordAdd';
    }
    // }}}
    // {{{ callRecordCurrent
    public function callRecordCurrent()
    {
        $this->executed[] = 'callRecordCurrent';
    }
    // }}}
    // {{{ callRecordStop
    public function callRecordStop()
    {
        $this->executed[] = 'callRecordStop';
    }
    // }}}
    // {{{ callRecordContinue
    protected function callRecordContinue()
    {
        $this->executed[] = 'callRecordContinue';
    }
    // }}}
    // {{{ callStop
    public function callStop()
    {
        $this->executed[] = 'callStop';
    }
    // }}}
    // {{{ callStatus
    public function callStatus()
    {
        $this->executed[] = 'callStatus';
    }
    // }}}

    // {{{ callTodoList
    protected function callTodoList()
    {
        $this->executed[] = 'callTodoList';
    }
    // }}}
    // {{{ callTodoListAll
    protected function callTodoListAll()
    {
        $this->executed[] = 'callTodoListAll';
    }
    // }}}
    // {{{ callTodoEdit
    protected function callTodoEdit()
    {
        $this->executed[] = 'callTodoEdit';
    }
    // }}}
    // {{{ callTodoEditAll
    protected function callTodoEditAll()
    {
        $this->executed[] = 'callTodoEditAll';
    }
    // }}}
}
