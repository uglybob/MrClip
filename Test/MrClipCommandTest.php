<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Parser;
use Uglybob\MrClip\Lib\Todo;

class MrClipCommandTest extends \PHPUnit_Framework_TestCase
{
    // {{{ assertCommandExecuted
    public function assertCommandExecuted($expected, $options)
    {
        $this->mrClip = new MrClipCommandTestClass($options);

        $this->assertSame([$expected], $this->mrClip->executed, "Failed asserting that '$expected' was executed.");
    }
    // }}}

    // {{{ testExecute
    public function testExecute()
    {
        $this->assertCommandExecuted('completion', ['completion']);
        $this->assertCommandExecuted('callRecordAdd', ['record', 'add']);
        $this->assertCommandExecuted('callRecordCurrent', ['record', 'current']);
        $this->assertCommandExecuted('callRecordStop', ['record', 'stop']);
        $this->assertCommandExecuted('callRecordContinue', ['record', 'continue']);
        $this->assertCommandExecuted('callStop', ['stop']);
        $this->assertCommandExecuted('callStatus', ['status']);

        $this->assertCommandExecuted('callTodoList', ['todo', 'list']);
        $this->assertCommandExecuted('callTodoListAll', ['todo', 'listAll']);
        $this->assertCommandExecuted('callTodoEdit', ['todo', 'edit']);
        $this->assertCommandExecuted('callTodoEditAll', ['todo', 'editAll']);
    }
    // }}}
}
