<?php

namespace Uglybob\MrClip\Test;

class MrClipCommandTestClass extends MrClipTestClass
{
    public $executed = [];

    // {{{ constructor
    public function __construct($options = [])
    {
        parent::__construct($options);

        $this->run($options);
    }
    // }}}

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
    // {{{ callRecordContinue
    public function callRecordContinue()
    {
        $this->executed[] = 'callRecordContinue';
    }
    // }}}
    // {{{ callContinue
    public function callContinue()
    {
        $this->executed[] = 'callContinue';
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
