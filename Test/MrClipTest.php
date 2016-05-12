<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Parser;

class MrClipTest extends \PhpUnit_Framework_TestCase
{
    // {{{ setUp
    protected function setUp()
    {
        parent::setUp();

        $this->mrClip = new MrClipTestClass();
    }
    // }}}

    // {{{ comp
    protected function comp($string)
    {
        $options = explode(' ', $string);
        array_unshift($options, 'completion');

        if (substr($string, -1) == ' ') {
            $options[] = "''";
        } else {
            end($options);
            $key = key($options);
            reset($options);

            $options[$key] = "'" . $options[$key] . "'";
        }

        $this->mrClip->run($options);
    }
    // }}}

    // {{{ testSuggest
    public function testSuggest()
    {
        $candidates = ['test', 'test2', 'anotherTest', 'oneMoreTest'];
        $this->mrClip->suggest('', $candidates);
        $this->assertSame('test test2 anotherTest oneMoreTest', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testSuggestStart
    public function testSuggestStart()
    {
        $candidates = ['test', 'test2', 'anotherTest', 'oneMoreTest'];
        $this->mrClip->suggest('t', $candidates);
        $this->assertSame('test test2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testSuggestTwo
    public function testSuggestTwo()
    {
        $candidates = ['test', 'test2', 'anotherTest', 'oneMoreTest'];
        $this->mrClip->suggest('test', $candidates);
        $this->assertSame('test test2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testSuggestOne
    public function testSuggestOne()
    {
        $candidates = ['test', 'test2', 'anotherTest', 'oneMoreTest'];
        $this->mrClip->suggest('test2', $candidates);
        $this->assertSame('test2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testSuggestFail
    public function testSuggestFail()
    {
        $candidates = ['test', 'test2', 'anotherTest', 'oneMoreTest'];
        $this->mrClip->suggest('x', $candidates);
        $this->assertSame('', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testSuggestEmpty
    public function testSuggestEmpty()
    {
        $candidates = [];
        $this->mrClip->suggest('', $candidates);
        $this->assertSame('', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testSuggestEmptyStart
    public function testSuggestEmptyStart()
    {
        $candidates = [];
        $this->mrClip->suggest('t', $candidates);
        $this->assertSame('', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testSuggestPrefix
    public function testSuggestPrefix()
    {
        $candidates = ['test', 'test2', 'anotherTest', 'oneMoreTest'];
        $this->mrClip->suggest('', $candidates, 'testPrefix');
        $this->assertSame('testPrefixtest testPrefixtest2 testPrefixanotherTest testPrefixoneMoreTest', $this->mrClip->echoed);
    }
    // }}}

    // {{{ testEmpty
    public function testEmpty()
    {
        $this->assertSame('', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionEmpty
    public function testCompletionEmpty()
    {
        $this->comp('');
        $this->assertSame('record todo', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionR
    public function testCompletionR()
    {
        $this->comp('r');
        $this->assertSame('record', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecord
    public function testCompletionRecord()
    {
        $this->comp('record');
        $this->assertSame('record', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecord_
    public function testCompletionRecord_()
    {
        $this->comp('record ');
        $this->assertSame('add current list stop continue', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordA
    public function testCompletionRecordA()
    {
        $this->comp('record a');
        $this->assertSame('add', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAdd
    public function testCompletionRecordAdd()
    {
        $this->comp('record add ');

        $this->assertSame(date('H:i') . ' 22:00', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTime_
    public function testCompletionRecordAddTime_()
    {
        $this->comp('record add 22:00 ');
        $this->assertSame('activity1@category1 activity1@category2 activity2@category1 activity2@category2 @category1 @category2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeA
    public function testCompletionRecordAddTimeA()
    {
        $this->comp('record add 22:00 a');
        $this->assertSame('activity1@category1 activity1@category2 activity2@category1 activity2@category2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivity
    public function testCompletionRecordAddTimeActivity()
    {
        $this->comp('record add 22:00 activity1');
        $this->assertSame('activity1@category1 activity1@category2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAt
    public function testCompletionRecordAddTimeActivityAt()
    {
        $this->comp('record add 22:00 activity1@');
        $this->assertSame('activity1@category1 activity1@category2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtC
    public function testCompletionRecordAddTimeActivityAtC()
    {
        $this->comp('record add 22:00 activity1@c');
        $this->assertSame('activity1@category1 activity1@category2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtCategory
    public function testCompletionRecordAddTimeActivityAtCategory()
    {
        $this->comp('record add 22:00 activity1@category1');
        $this->assertSame('activity1@category1', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtCategory_
    public function testCompletionRecordAddTimeActivityAtCategory_()
    {
        $this->comp('record add 22:00 activity1@category1 ');
        $this->assertSame('+tag1 +tag2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtCategoryT
    public function testCompletionRecordAddTimeActivityAtCategoryT()
    {
        $this->comp('record add 22:00 activity1@category1 +');
        $this->assertSame('+tag1 +tag2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtCategoryT2
    public function testCompletionRecordAddTimeActivityAtCategoryT2()
    {
        $this->comp('record add 22:00 activity1@category1 +t');
        $this->assertSame('+tag1 +tag2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtCategoryTag
    public function testCompletionRecordAddTimeActivityAtCategoryTag()
    {
        $this->comp('record add 22:00 activity1@category1 +tag1');
        $this->assertSame('+tag1', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtCategoryTag_
    public function testCompletionRecordAddTimeActivityAtCategoryTag_()
    {
        $this->comp('record add 22:00 activity1@category1 +tag1 ');
        $this->assertSame('+tag2', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionTodo_
    public function testCompletionTodo_()
    {
        $this->comp('todo ');
        $this->assertSame('add list edit', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionTodoL
    public function testCompletionTodoL()
    {
        $this->comp('todo l');
        $this->assertSame('list', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionTodoList
    public function testCompletionTodoList()
    {
        $this->comp('todo list');
        $this->assertSame('list', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionTodoList_
    public function testCompletionTodoList_()
    {
        $this->comp('todo list ');
        $this->assertSame('activity1@category1 activity1@category2 activity2@category1 activity2@category2 @category1 @category2', $this->mrClip->echoed);
    }
    // }}}

    // {{{ testRecordAdd
    public function testRecordAdd()
    {
        $this->mrClip->recordAdd();
        $this->assertSame('', $this->mrClip->echoed);
    }
    // }}}
}
