<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Parser;
use Uglybob\MrClip\Lib\Todo;

class MrClipTest extends \PHPUnit_Framework_TestCase
{
    // {{{ setUp
    protected function setUp()
    {
        parent::setUp();

        $this->mrClip = new MrClipTestClass();
        $this->dateFormat = 'Y-m-d H:i';
        $this->prm = $this->mrClip->prm;
        $this->api = $this->prm->connection;
    }
    // }}}

    // {{{ comp
    protected function comp($string)
    {
        $options = explode(' ', $string);
        array_unshift($options, 'completion');

        end($options);
        $key = key($options);
        reset($options);

        if (substr($string, -1) == ' ') {
            $options[$key] = "''";
        } else {
            $options[] = "'" . $options[$key] . "'";
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
        $this->assertSame('continue record status stop todo', $this->mrClip->echoed);
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
        $this->assertSame('', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecord_
    public function testCompletionRecord_()
    {
        $this->comp('record ');
        $this->assertSame('add continue current stop', $this->mrClip->echoed);
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
        $this->assertSame('+tag1 +tag2 +tag3', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtCategoryT
    public function testCompletionRecordAddTimeActivityAtCategoryT()
    {
        $this->comp('record add 22:00 activity1@category1 +');
        $this->assertSame('+tag1 +tag2 +tag3', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtCategoryT2
    public function testCompletionRecordAddTimeActivityAtCategoryT2()
    {
        $this->comp('record add 22:00 activity1@category1 +t');
        $this->assertSame('+tag1 +tag2 +tag3', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtCategoryTag
    public function testCompletionRecordAddTimeActivityAtCategoryTag()
    {
        $this->comp('record add 22:00 activity1@category1 +tag1');
        $this->assertSame('', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTimeActivityAtCategoryTag_
    public function testCompletionRecordAddTimeActivityAtCategoryTag_()
    {
        $this->comp('record add 22:00 activity1@category1 +tag1 ');
        $this->assertSame('+tag2 +tag3', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionTodo_
    public function testCompletionTodo_()
    {
        $this->comp('todo ');
        $this->assertSame('edit editAll list listAll', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionTodoL
    public function testCompletionTodoL()
    {
        $this->comp('todo l');
        $this->assertSame('list listAll', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionTodoList
    public function testCompletionTodoList()
    {
        $this->comp('todo list');
        $this->assertSame('', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionTodoList_
    public function testCompletionTodoList_()
    {
        $this->comp('todo list ');
        $this->assertSame('activity1@category1 activity1@category2 activity2@category1 activity2@category2 @category1 @category2', $this->mrClip->echoed);
    }
    // }}}

    // {{{ testCallRecordAddEmpty
    public function testCallRecordAddEmpty()
    {
        $record = $this->mrClip->callRecordAdd();

        $this->assertSame("Activity/category missing\n", $this->mrClip->echoed);
        $this->assertNull($record);
    }
    // }}}
    // {{{ testCallRecordAdd
    public function testCallRecordAdd()
    {
        $options = 'testActivity@testCategory +testTag1 +testTag2 testText';
        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));

        $now = date($this->dateFormat);

        $record = $this->mrClip->callRecordAdd();

        $this->assertSame("(added) $now testActivity@testCategory +testTag1 +testTag2 testText\n", $this->mrClip->echoed);

        $this->assertSame(4, $record->getId());
        $this->assertSame($now, $record->getStart()->format($this->dateFormat));
        $this->assertNull($record->getEnd());
        $this->assertSame('testActivity', $record->getActivity());
        $this->assertSame('testCategory', $record->getCategory());
        $this->assertSame(['testTag1', 'testTag2'], $record->getTags());
        $this->assertSame('testText', $record->getText());
        $this->assertTrue($record->isRunning());
    }
    // }}}
    // {{{ testCallRecordAddNoActivity
    public function testCallRecordAddNoActivity()
    {
        $options = '@testCategory +testTag1 +testTag2 testText';
        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));

        $record = $this->mrClip->callRecordAdd();

        $this->assertSame("Activity/category missing\n", $this->mrClip->echoed);
        $this->assertNull($record);
    }
    // }}}
    // {{{ testCallRecordAddNoCategory
    public function testCallRecordAddNoCategory()
    {
        $options = 'testActivity@ +testTag1 +testTag2 testText';
        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));

        $record = $this->mrClip->callRecordAdd();

        $this->assertSame("Activity/category missing\n", $this->mrClip->echoed);
        $this->assertNull($record);
    }
    // }}}
    // {{{ testCallRecordAddNoTag
    public function testCallRecordAddNoTag()
    {
        $options = 'testActivity@testCategory testText';
        $now = date($this->dateFormat);
        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));

        $record = $this->mrClip->callRecordAdd();

        $this->assertSame("(added) $now testActivity@testCategory testText\n", $this->mrClip->echoed);

        $this->assertSame(4, $record->getId());
        $this->assertSame($now, $record->getStart()->format($this->dateFormat));
        $this->assertNull($record->getEnd());
        $this->assertSame('testActivity', $record->getActivity());
        $this->assertSame('testCategory', $record->getCategory());
        $this->assertSame([], $record->getTags());
        $this->assertSame('testText', $record->getText());
        $this->assertTrue($record->isRunning());
    }
    // }}}
    // {{{ testCallRecordAddNoText
    public function testCallRecordAddNoText()
    {
        $options = 'testActivity@testCategory +testTag1 +testTag2';
        $now = date($this->dateFormat);
        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));

        $record = $this->mrClip->callRecordAdd();

        $this->assertSame("(added) $now testActivity@testCategory +testTag1 +testTag2\n", $this->mrClip->echoed);

        $this->assertSame(4, $record->getId());
        $this->assertSame($now, $record->getStart()->format($this->dateFormat));
        $this->assertNull($record->getEnd());
        $this->assertSame('testActivity', $record->getActivity());
        $this->assertSame('testCategory', $record->getCategory());
        $this->assertSame(['testTag1', 'testTag2'], $record->getTags());
        $this->assertNull($record->getText());
        $this->assertTrue($record->isRunning());
    }
    // }}}

    // {{{ testCallRecordCurrent
    public function testCallRecordCurrent()
    {
        $this->assertNull($this->mrClip->callRecordCurrent());
        $this->assertSame("(last) 2015-10-21 21:00 - 2015-10-21 22:00 activity2@category1 +tag2\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCallRecordCurrentRunning
    public function testCallRecordCurrentRunning()
    {
        $this->prm->getConnection()->records[3]->end = null;
        $this->assertNull($this->mrClip->callRecordCurrent());
        $this->assertSame("(running) 2015-10-21 19:00 activity1@category2\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCallStatus
    public function testCallStatus()
    {
        $this->assertNull($this->mrClip->callStatus());
        $this->assertSame("(last) 2015-10-21 21:00 - 2015-10-21 22:00 activity2@category1 +tag2\n", $this->mrClip->echoed);
    }
    // }}}

    // {{{ testCallRecordStop
    public function testCallRecordStop()
    {
        $this->prm->getConnection()->records[3]->end = null;
        $this->assertNull($this->mrClip->callRecordStop());

        $timestamp = $this->prm->getConnection()->records[3]->end;
        $date = date('Y-m-d H:i', $timestamp);

        $this->assertSame("(stopped) 2015-10-21 19:00 - $date activity1@category2\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCallStop
    public function testCallStop()
    {
        $this->prm->getConnection()->records[3]->end = null;
        $this->assertNull($this->mrClip->callStop());

        $timestamp = $this->prm->getConnection()->records[3]->end;
        $date = date('Y-m-d H:i', $timestamp);

        $this->assertSame("(stopped) 2015-10-21 19:00 - $date activity1@category2\n", $this->mrClip->echoed);
    }
    // }}}

    // {{{ testCallRecordContinue
    public function testCallRecordContinue()
    {
        $now = date('Y-m-d H:i');

        $this->assertNull($this->mrClip->callRecordContinue());
        $this->assertSame("(added) $now activity2@category1 +tag2\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCallContinue
    public function testCallContinue()
    {
        $now = date('Y-m-d H:i');

        $this->assertNull($this->mrClip->callContinue());
        $this->assertSame("(added) $now activity2@category1 +tag2\n", $this->mrClip->echoed);
    }
    // }}}

    // {{{ testGetFilteredTodos
    public function testGetFilteredTodos()
    {
        $options = 'activity1@category1 +tag3';
        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));

        $todos = $this->mrClip->getFilteredTodos();
        $this->assertSame(1, count($todos));

        $todos->rewind();
        $todo = $todos->current();
        $this->assertSame(5, $todo->getId());
        $this->assertSame('activity1', $todo->getActivity());
        $this->assertSame('category1', $todo->getCategory());
        $this->assertSame(['tag1', 'tag2', 'tag3'], $todo->getTags());
        $this->assertSame('extra tag', $todo->getText());
        $this->assertSame(null, $todo->getParentId());
        $this->assertSame(0, $todo->getPosition());
        $this->assertFalse($todo->isDone());
    }
    // }}}
    // {{{ testGetFilteredTodosMultiple
    public function testGetFilteredTodosMultiple()
    {
        $options = 'activity1@category1 +tag1';

        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));

        $this->assertSame(8, count($this->mrClip->getFilteredTodos()));
    }
    // }}}
    // {{{ testGetFilteredTodosEmpty
    public function testGetFilteredTodosEmpty()
    {
        $options = 'unknown@unknown +unknown';
        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));

        $todos = $this->mrClip->getFilteredTodos();
        $this->assertSame(0, count($todos));
    }
    // }}}
    // {{{ testGetFilteredTodosNotDone
    public function testGetFilteredTodosNotDone()
    {
        $options = 'activity1@category1 +tag1';

        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));

        $this->assertSame(7, count($this->mrClip->getFilteredTodos(false)));
    }
    // }}}

    // {{{ testFormatTodos
    public function testFormatTodos()
    {
        $todo = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo], true));
    }
    // }}}
    // {{{ testFormatTodosEmpty
    public function testFormatTodosEmpty()
    {
        $this->assertSame('', $this->mrClip->formatTodos([], true));
    }
    // }}}
    // {{{ testFormatTodosTwo
    public function testFormatTodosTwo()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', null, 1, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n" .
        "testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2], true));
    }
    // }}}
    // {{{ testFormatTodosTwoDifferentTags
    public function testFormatTodosTwoDifferentTags()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag3', 'testTag4'], 'testText2', null, 1, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n\n" .
        "testActivity@testCategory +testTag3 +testTag4\n\n" .
        "testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2], true));
    }
    // }}}
    // {{{ testFormatTodosTwoDifferentTagsIntersect
    public function testFormatTodosTwoDifferentTagsIntersect()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag2', 'testTag3'], 'testText2', null, 1, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n\n" .
        $expected = "testActivity@testCategory +testTag2 +testTag3\n\n" .
        "testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2], true));
    }
    // }}}
    // {{{ testFormatTodosChild
    public function testFormatTodosChild()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', $todo1, 0, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n" .
        "    testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2], true));
    }
    // }}}
    // {{{ testFormatTodosChildren
    public function testFormatTodosChildren()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', $todo1, 0, false);
        $todo3 = new Todo(3, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText3', $todo1, 1, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n" .
        "    testText2\n" .
        "    testText3\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2, $todo3], true));
    }
    // }}}
    // {{{ testFormatTodosChildChild
    public function testFormatTodosChildChild()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', $todo1, 0, false);
        $todo3 = new Todo(3, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText3', $todo2, 0, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n" .
        "    testText2\n" .
        "        testText3\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2, $todo3], true));
    }
    // }}}
    // {{{ testFormatTodosChildChildGap
    public function testFormatTodosChildChildGap()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', $todo1, 0, false);
        $todo3 = new Todo(3, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText3', $todo2, 0, false);
        $todo4 = new Todo(4, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText4', null, 1, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n" .
        "    testText2\n" .
        "        testText3\n" .
        "testText4\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2, $todo3, $todo4], true));
    }
    // }}}
    // {{{ testFormatTodosMultipleActigories
    public function testFormatTodosMultipleActigories()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity2', 'testCategory2', ['testTag1', 'testTag2'], 'testText2', null, 0, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n\n" .
        $expected = "testActivity2@testCategory2 +testTag1 +testTag2\n\n" .
        "testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2], true));
    }
    // }}}
    // {{{ testFormatTodosMultipleActivities
    public function testFormatTodosMultipleActivities()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity2', 'testCategory', ['testTag1', 'testTag2'], 'testText2', null, 0, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n\n" .
        $expected = "testActivity2@testCategory +testTag1 +testTag2\n\n" .
        "testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2], true));
    }
    // }}}
    // {{{ testFormatTodosMultipleCategories
    public function testFormatTodosMultipleCategories()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory2', ['testTag1', 'testTag2'], 'testText2', null, 0, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n\n" .
        $expected = "testActivity@testCategory2 +testTag1 +testTag2\n\n" .
        "testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2], true));
    }
    // }}}
    // {{{ testFormatTodosTagFilter
    public function testFormatTodosTagFilter()
    {
        $this->mrClip->parser = new ParserTestClass(explode(' ', '+testTag1'));
        $this->mrClip->parser->parseTags();

        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag3'], 'testText2', null, 1, false);
        $todo3 = new Todo(3, 'testActivity', 'testCategory', ['testTag1'], 'testText3', null, 2, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "testText\n\n" .
        "testActivity@testCategory +testTag1 +testTag3\n\n" .
        "testText2\n\n" .
        "testActivity@testCategory +testTag1\n\n" .
        "testText3\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2, $todo3], true));
    }
    // }}}
    // {{{ testFormatTodosDone
    public function testFormatTodosDone()
    {
        $todo = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, 0, true);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n\n" .
        "# testText\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo], false));
    }
    // }}}
    // {{{ testFormatTodosDoneMixed
    public function testFormatTodosDoneMixed()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText1', null, 0, true);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', null, 1, false);

        $expected = "testActivity@testCategory +testTag1 +testTag2\n" .
        "\n" .
        "# testText1\n" .
        "testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2], false));
    }
    // }}}
    // {{{ testFormatTodosPosition
    public function testFormatTodosPosition()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', [], 'testText', null, 1, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', [], 'testText2', null, 2, false);
        $todo3 = new Todo(3, 'testActivity', 'testCategory', [], 'testText3', null, 0, false);

        $expected = "testActivity@testCategory\n\n" .
        "testText3\n" .
        "testText\n" .
        "testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2, $todo3], true));
    }
    // }}}

    // {{{ testParseTodoList
    public function testParseTodoList()
    {
        $list = ['testActivity@testCategory',
        '',
        'testText',
        ];

        $todos = $this->mrClip->parseTodoList($list);
        $this->assertSame(1, count($todos));

        $todos->rewind();
        $todo = $todos->current();

        $this->assertFalse($todo->isDone());
        $this->assertNull($todo->getParent());
        $this->assertSame('testActivity', $todo->getActivity());
        $this->assertSame('testCategory', $todo->getCategory());
        $this->assertSame('testText', $todo->getText());
        $this->assertSame([], $todo->getTags());
    }
    // }}}
    // {{{ testParseTodoListTwo
    public function testParseTodoListTwo()
    {
        $list = ['testActivity@testCategory',
        '',
        'testText1',
        'testText2',
        ];

        $todos = $this->mrClip->parseTodoList($list);
        $this->assertSame(2, count($todos));

        $todos->rewind();
        $todo1 = $todos->current();
        $todos->next();
        $todo2 = $todos->current();

        $this->assertFalse($todo1->isDone());
        $this->assertNull($todo1->getParent());
        $this->assertSame('testActivity', $todo1->getActivity());
        $this->assertSame('testCategory', $todo1->getCategory());
        $this->assertSame('testText1', $todo1->getText());
        $this->assertSame([], $todo1->getTags());

        $this->assertFalse($todo2->isDone());
        $this->assertNull($todo2->getParent());
        $this->assertSame('testActivity', $todo2->getActivity());
        $this->assertSame('testCategory', $todo2->getCategory());
        $this->assertSame('testText2', $todo2->getText());
        $this->assertSame([], $todo2->getTags());
    }
    // }}}
    // {{{ testParseTodoListChild
    public function testParseTodoListChild()
    {
        $list = ['testActivity@testCategory',
        '',
        'testText1',
        '    testText2',
        ];

        $todos = $this->mrClip->parseTodoList($list);
        $this->assertSame(2, count($todos));

        $todos->rewind();
        $todo1 = $todos->current();
        $todos->next();
        $todo2 = $todos->current();

        $this->assertFalse($todo1->isDone());
        $this->assertNull($todo1->getParent());
        $this->assertSame('testActivity', $todo1->getActivity());
        $this->assertSame('testCategory', $todo1->getCategory());
        $this->assertSame('testText1', $todo1->getText());
        $this->assertSame([], $todo1->getTags());

        $this->assertFalse($todo2->isDone());
        $this->assertSame($todo1, $todo2->getParent());
        $this->assertSame('testActivity', $todo2->getActivity());
        $this->assertSame('testCategory', $todo2->getCategory());
        $this->assertSame('testText2', $todo2->getText());
        $this->assertSame([], $todo2->getTags());
    }
    // }}}
    // {{{ testParseTodoListChildren
    public function testParseTodoListChildren()
    {
        $list = ['testActivity@testCategory',
        '',
        'testText1',
        '    testText2',
        '    testText3',
        ];

        $todos = $this->mrClip->parseTodoList($list);
        $this->assertSame(3, count($todos));

        $todos->rewind();
        $todo1 = $todos->current();
        $todos->next();
        $todo2 = $todos->current();
        $todos->next();
        $todo3 = $todos->current();

        $this->assertFalse($todo1->isDone());
        $this->assertNull($todo1->getParent());
        $this->assertSame('testActivity', $todo1->getActivity());
        $this->assertSame('testCategory', $todo1->getCategory());
        $this->assertSame('testText1', $todo1->getText());
        $this->assertSame([], $todo1->getTags());

        $this->assertFalse($todo2->isDone());
        $this->assertSame($todo1, $todo2->getParent());
        $this->assertSame('testActivity', $todo2->getActivity());
        $this->assertSame('testCategory', $todo2->getCategory());
        $this->assertSame('testText2', $todo2->getText());
        $this->assertSame([], $todo2->getTags());

        $this->assertFalse($todo3->isDone());
        $this->assertSame($todo1, $todo3->getParent());
        $this->assertSame('testActivity', $todo3->getActivity());
        $this->assertSame('testCategory', $todo3->getCategory());
        $this->assertSame('testText3', $todo3->getText());
        $this->assertSame([], $todo3->getTags());
    }
    // }}}
    // {{{ testParseTodoListChildChild
    public function testParseTodoListChildChild()
    {
        $list = ['testActivity@testCategory',
        '',
        'testText1',
        '    testText2',
        '        testText3',
        ];

        $todos = $this->mrClip->parseTodoList($list);
        $this->assertSame(3, count($todos));

        $todos->rewind();
        $todo1 = $todos->current();
        $todos->next();
        $todo2 = $todos->current();
        $todos->next();
        $todo3 = $todos->current();

        $this->assertFalse($todo1->isDone());
        $this->assertNull($todo1->getParent());
        $this->assertSame('testActivity', $todo1->getActivity());
        $this->assertSame('testCategory', $todo1->getCategory());
        $this->assertSame('testText1', $todo1->getText());
        $this->assertSame([], $todo1->getTags());

        $this->assertFalse($todo2->isDone());
        $this->assertSame($todo1, $todo2->getParent());
        $this->assertSame('testActivity', $todo2->getActivity());
        $this->assertSame('testCategory', $todo2->getCategory());
        $this->assertSame('testText2', $todo2->getText());
        $this->assertSame([], $todo2->getTags());

        $this->assertFalse($todo3->isDone());
        $this->assertSame($todo2, $todo3->getParent());
        $this->assertSame('testActivity', $todo3->getActivity());
        $this->assertSame('testCategory', $todo3->getCategory());
        $this->assertSame('testText3', $todo3->getText());
        $this->assertSame([], $todo3->getTags());
    }
    // }}}
    // {{{ testParseTodoListChildChildGap
    public function testParseTodoListChildChildGap()
    {
        $list = ['testActivity@testCategory',
        '',
        'testText1',
        '    testText2',
        '        testText3',
        '            testText4',
        '    testText5',
        '        testText6',
        ];

        $todos = $this->mrClip->parseTodoList($list);
        $this->assertSame(6, count($todos));

        $todos->rewind();
        $todo1 = $todos->current();
        $todos->next();
        $todo2 = $todos->current();
        $todos->next();
        $todo3 = $todos->current();
        $todos->next();
        $todo4 = $todos->current();
        $todos->next();
        $todo5 = $todos->current();
        $todos->next();
        $todo6 = $todos->current();

        $this->assertFalse($todo1->isDone());
        $this->assertNull($todo1->getParent());
        $this->assertSame('testActivity', $todo1->getActivity());
        $this->assertSame('testCategory', $todo1->getCategory());
        $this->assertSame('testText1', $todo1->getText());
        $this->assertSame([], $todo1->getTags());

        $this->assertFalse($todo2->isDone());
        $this->assertSame($todo1, $todo2->getParent());
        $this->assertSame('testActivity', $todo2->getActivity());
        $this->assertSame('testCategory', $todo2->getCategory());
        $this->assertSame('testText2', $todo2->getText());
        $this->assertSame([], $todo2->getTags());

        $this->assertFalse($todo3->isDone());
        $this->assertSame($todo2, $todo3->getParent());
        $this->assertSame('testActivity', $todo3->getActivity());
        $this->assertSame('testCategory', $todo3->getCategory());
        $this->assertSame('testText3', $todo3->getText());
        $this->assertSame([], $todo3->getTags());

        $this->assertFalse($todo4->isDone());
        $this->assertSame($todo3, $todo4->getParent());
        $this->assertSame('testActivity', $todo4->getActivity());
        $this->assertSame('testCategory', $todo4->getCategory());
        $this->assertSame('testText4', $todo4->getText());
        $this->assertSame([], $todo4->getTags());

        $this->assertFalse($todo5->isDone());
        $this->assertSame($todo1, $todo5->getParent());
        $this->assertSame('testActivity', $todo5->getActivity());
        $this->assertSame('testCategory', $todo5->getCategory());
        $this->assertSame('testText5', $todo5->getText());
        $this->assertSame([], $todo5->getTags());

        $this->assertFalse($todo6->isDone());
        $this->assertSame($todo5, $todo6->getParent());
        $this->assertSame('testActivity', $todo6->getActivity());
        $this->assertSame('testCategory', $todo6->getCategory());
        $this->assertSame('testText6', $todo6->getText());
        $this->assertSame([], $todo6->getTags());
    }
    // }}}
    // {{{ testParseTodoListDone
    public function testParseTodoListDone()
    {
        $list = ['testActivity@testCategory',
        '',
        '# testText',
        ];

        $todos = $this->mrClip->parseTodoList($list);
        $this->assertSame(1, count($todos));

        $todos->rewind();
        $todo = $todos->current();

        $this->assertTrue($todo->isDone());
        $this->assertNull($todo->getParent());
        $this->assertSame('testActivity', $todo->getActivity());
        $this->assertSame('testCategory', $todo->getCategory());
        $this->assertSame('testText', $todo->getText());
        $this->assertSame([], $todo->getTags());
    }
    // }}}

    // {{{ testParsingDeleted
    public function testParsingDeleted()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo1, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    text3',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(1, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(1, $parsed->deleted->count());

        $this->assertSame("3 old, 2 new\n\n(moved)   activity1@category1 +tag1 +tag2 text3\n(deleted) activity1@category1 +tag1 +tag2 text2\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingUnchanged
    public function testParsingUnchanged()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo1, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    text2',
            '    text3',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("3 old, 3 new\n\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingUnchangedDone
    public function testParsingUnchangedDone()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo1, 1, true);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    text2',
            '',
            '# text3',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("3 old, 3 new\n\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingEdit
    public function testParsingEdit()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo1, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    text4',
            '    text3',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(1, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("3 old, 3 new\n\n(edited)  activity1@category1 +tag1 +tag2 text2 -> activity1@category1 +tag1 +tag2 text4\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingEditGap
    public function testParsingEditGap()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo2, 0, false);
        $todo4 = new Todo(4, 'activity1', 'category1', ['tag1', 'tag2'], 'text4', null, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);
        $todos->attach($todo4);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    text2',
            '        text3',
            'text5',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(1, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("4 old, 4 new\n\n(edited)  activity1@category1 +tag1 +tag2 text4 -> activity1@category1 +tag1 +tag2 text5\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingEditParent
    public function testParsingEditParent()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', null, 1, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo2, 0, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            'text4',
            '    text3',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(1, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("3 old, 3 new\n\n(edited)  activity1@category1 +tag1 +tag2 text2 -> activity1@category1 +tag1 +tag2 text4\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingEditDuplicate
    public function testParsingEditDuplicate()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text', $todo1, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text3',
            '    text2',
            '    text',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(1, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("3 old, 3 new\n\n(edited)  activity1@category1 +tag1 +tag2 text -> activity1@category1 +tag1 +tag2 text3\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingMoveParent
    public function testParsingMoveParent()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo1, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    text2',
            'text3',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(1, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("3 old, 3 new\n\n(moved)   activity1@category1 +tag1 +tag2 text3\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingMovePosition
    public function testParsingMovePosition()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo1, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    text3',
            '    text2',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(2, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $parsed->moved->rewind();
        $this->assertSame('text3', $parsed->moved->current()->getText());
        $this->assertSame(0, $parsed->moved->current()->getPosition());
        $parsed->moved->next();
        $this->assertSame('text2', $parsed->moved->current()->getText());
        $this->assertSame(1, $parsed->moved->current()->getPosition());

        $this->assertSame("3 old, 3 new\n\n(moved)   activity1@category1 +tag1 +tag2 text3\n(moved)   activity1@category1 +tag1 +tag2 text2\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingMovePositionAddChild
    public function testParsingMovePositionAddChild()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo1, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    text2',
            '        text4',
            '    text3',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(1, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $parsed->new->rewind();
        $this->assertSame('text4', $parsed->new->current()->getText());
        $this->assertSame(0, $parsed->new->current()->getPosition());

        $this->assertSame("3 old, 4 new\n\n(new)     activity1@category1 +tag1 +tag2 text4\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingMovePositionCheckPredecessor
    public function testParsingMovePositionCheckPredecessor()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo1, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    # text2',
            '    text3',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(1, $parsed->moved->count());
        $this->assertSame(1, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $parsed->moved->rewind();
        $this->assertSame('text3', $parsed->moved->current()->getText());
        $this->assertSame(0, $parsed->moved->current()->getPosition());

        $this->assertSame("3 old, 3 new\n\n(moved)   activity1@category1 +tag1 +tag2 text3\n(edited)  activity1@category1 +tag1 +tag2 text2 -> # activity1@category1 +tag1 +tag2 text2\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingNew
    public function testParsingNew()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo1, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    text2',
            '    text3',
            '    text4',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(1, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("3 old, 4 new\n\n(new)     activity1@category1 +tag1 +tag2 text4\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingNewParent
    public function testParsingNewParent()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $todo1, 0, false);
        $todo3 = new Todo(3, 'activity1', 'category1', ['tag1', 'tag2'], 'text3', $todo1, 1, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);
        $todos->attach($todo3);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '    text2',
            'text4',
            '    text3',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(1, $parsed->new->count());
        $this->assertSame(1, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("3 old, 4 new\n\n(moved)   activity1@category1 +tag1 +tag2 text3\n(new)     activity1@category1 +tag1 +tag2 text4\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingMultipleActivities
    public function testParsingMultipleActivities()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity2', 'category1', ['tag1', 'tag2'], 'text2', null, 0, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '',
            'activity2@category1 +tag1 +tag2',
            '',
            'text2',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("2 old, 2 new\n\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingMultipleCategories
    public function testParsingMultipleCategories()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category2', ['tag1', 'tag2'], 'text2', null, 0, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '',
            'activity1@category2 +tag1 +tag2',
            '',
            'text2',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("2 old, 2 new\n\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testParsingMultipleTagGroups
    public function testParsingMultipleTagGroups()
    {
        $todo1 = new Todo(1, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, 0, false);
        $todo2 = new Todo(2, 'activity1', 'category1', ['tag2'], 'text2', null, 0, false);

        $todos = new \SplObjectStorage();
        $todos->attach($todo1);
        $todos->attach($todo2);

        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'text',
            '',
            'activity1@category1 +tag2',
            '',
            'text2',
        ];

        $parsed = $this->mrClip->editAndParse('', $todos);

        $this->assertSame(0, $parsed->new->count());
        $this->assertSame(0, $parsed->moved->count());
        $this->assertSame(0, $parsed->edited->count());
        $this->assertSame(0, $parsed->deleted->count());

        $this->assertSame("2 old, 2 new\n\n", $this->mrClip->echoed);
    }
    // }}}

    // {{{ testTodoEdit
    public function testTodoEdit()
    {
        $this->mrClip->userEditString = [
            'activity1@category1 +tag1 +tag2',
            '',
            'parent1',
            '    child1',
            '        subchild1',
            '    child2',
            'parent2',
            '    child3',
            '',
            'activity1@category1 +tag1 +tag2 +tag3',
            '',
            'extra tag',
            '',
            'activity2@category1 +tag2',
            '',
            'other tags',
            '    other tags',
        ];

        $this->assertNull($this->mrClip->callTodoEdit());
        $this->assertSame("9 old, 9 new\n\nno change\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testTodoList
    public function testTodoList()
    {
        $this->assertNull($this->mrClip->callTodoList());
        $this->assertSame(
            "activity1@category1 +tag1 +tag2\n" .
            "\n" .
            "parent1\n" .
            "    child1\n" .
            "        subchild1\n" .
            "    child2\n" .
            "parent2\n" .
            "    child3\n" .
            "\n" .
            "activity1@category1 +tag1 +tag2 +tag3\n" .
            "\n" .
            "extra tag\n" .
            "\n" .
            "activity2@category1 +tag2\n" .
            "\n" .
            "other tags\n" .
            "    other tags\n",
            $this->mrClip->echoed
        );
    }
    // }}}
    // {{{ testTodoStorageNaming
    public function testTodoStorageNaming()
    {
    }
    // }}}
}
