<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Parser;
use Uglybob\MrClip\Lib\Todo;

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
        $this->assertSame('', $this->mrClip->echoed);
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

    // {{{ testRecordAddEmpty
    public function testRecordAddEmpty()
    {
        $this->mrClip->recordAdd();
        $this->assertSame("Activity/category missing\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testRecordAdd
    public function testRecordAdd()
    {
        $options = 'testActivity@testCategory +testTag1 +testTag2 testText';
        $now = date('Y-m-d H:i');

        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));
        $this->mrClip->recordAdd();
        $this->assertSame("(added) $now - $now testActivity@testCategory +testTag1 +testTag2 testText\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testRecordAddNoActivity
    public function testRecordAddNoActivity()
    {
        $options = '@testCategory +testTag1 +testTag2 testText';

        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));
        $this->mrClip->recordAdd();
        $this->assertSame("Activity/category missing\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testRecordAddNoCategory
    public function testRecordAddNoCategory()
    {
        $options = 'testActivity@ +testTag1 +testTag2 testText';
        $now = date('Y-m-d H:i');

        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));
        $this->mrClip->recordAdd();
        $this->assertSame("Activity/category missing\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testRecordAddNoTag
    public function testRecordAddNoTag()
    {
        $options = 'testActivity@testCategory testText';
        $now = date('Y-m-d H:i');

        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));
        $this->mrClip->recordAdd();
        $this->assertSame("(added) $now - $now testActivity@testCategory testText\n", $this->mrClip->echoed);
    }
    // }}}
    // {{{ testRecordAddNoText
    public function testRecordAddNoText()
    {
        $options = 'testActivity@testCategory +testTag1 +testTag2';
        $now = date('Y-m-d H:i');

        $this->mrClip->parser = new ParserTestClass(explode(' ', $options));
        $this->mrClip->recordAdd();
        $this->assertSame("(added) $now - $now testActivity@testCategory +testTag1 +testTag2\n", $this->mrClip->echoed);
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
        $this->assertSame(1, $todo->getParentId());
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

    // {{{ testFormatTodos
    public function testFormatTodos()
    {
        $todo = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);

        $expected = "testActivity@testCategory\n\n" .
        "+testTag1 +testTag2 testText\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo]));
    }
    // }}}
    // {{{ testFormatTodosEmpty
    public function testFormatTodosEmpty()
    {
        $this->assertSame('', $this->mrClip->formatTodos([]));
    }
    // }}}
    // {{{ testFormatTodosTwo
    public function testFormatTodosTwo()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', null, false);

        $expected = "testActivity@testCategory\n\n" .
        "+testTag1 +testTag2 testText\n" .
        "+testTag1 +testTag2 testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2]));
    }
    // }}}
    // {{{ testFormatTodosTwoDifferentTags
    public function testFormatTodosTwoDifferentTags()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag3', 'testTag4'], 'testText2', null, false);

        $expected = "testActivity@testCategory\n\n" .
        "+testTag1 +testTag2 testText\n" .
        "+testTag3 +testTag4 testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2]));
    }
    // }}}
    // {{{ testFormatTodosTwoDifferentTagsIntersect
    public function testFormatTodosTwoDifferentTagsIntersect()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag2', 'testTag3'], 'testText2', null, false);

        $expected = "testActivity@testCategory\n\n" .
        "+testTag1 +testTag2 testText\n" .
        "+testTag2 +testTag3 testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2]));
    }
    // }}}
    // {{{ testFormatTodosChild
    public function testFormatTodosChild()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', $todo1, false);

        $expected = "testActivity@testCategory\n\n" .
        "+testTag1 +testTag2 testText\n" .
        "    +testTag1 +testTag2 testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2]));
    }
    // }}}
    // {{{ testFormatTodosChildren
    public function testFormatTodosChildren()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', $todo1, false);
        $todo3 = new Todo(3, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText3', $todo1, false);

        $expected = "testActivity@testCategory\n\n" .
        "+testTag1 +testTag2 testText\n" .
        "    +testTag1 +testTag2 testText2\n" .
        "    +testTag1 +testTag2 testText3\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2, $todo3]));
    }
    // }}}
    // {{{ testFormatTodosChildChild
    public function testFormatTodosChildChild()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', $todo1, false);
        $todo3 = new Todo(3, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText3', $todo2, false);

        $expected = "testActivity@testCategory\n\n" .
        "+testTag1 +testTag2 testText\n" .
        "    +testTag1 +testTag2 testText2\n" .
        "        +testTag1 +testTag2 testText3\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2, $todo3]));
    }
    // }}}
    // {{{ testFormatTodosMultipleActigories
    public function testFormatTodosMultipleActigories()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);
        $todo2 = new Todo(2, 'testActivity2', 'testCategory2', ['testTag1', 'testTag2'], 'testText2', null, false);

        $expected = "testActivity@testCategory\n\n" .
        "+testTag1 +testTag2 testText\n\n" .
        $expected = "testActivity2@testCategory2\n\n" .
        "+testTag1 +testTag2 testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2]));
    }
    // }}}
    // {{{ testFormatTodosMultipleActivities
    public function testFormatTodosMultipleActivities()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);
        $todo2 = new Todo(2, 'testActivity2', 'testCategory', ['testTag1', 'testTag2'], 'testText2', null, false);

        $expected = "testActivity@testCategory\n\n" .
        "+testTag1 +testTag2 testText\n\n" .
        $expected = "testActivity2@testCategory\n\n" .
        "+testTag1 +testTag2 testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2]));
    }
    // }}}
    // {{{ testFormatTodosMultipleCategories
    public function testFormatTodosMultipleCategories()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory2', ['testTag1', 'testTag2'], 'testText2', null, false);

        $expected = "testActivity@testCategory\n\n" .
        "+testTag1 +testTag2 testText\n\n" .
        $expected = "testActivity@testCategory2\n\n" .
        "+testTag1 +testTag2 testText2\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2]));
    }
    // }}}
    // {{{ testFormatTodosTagFilter
    public function testFormatTodosTagFilter()
    {
        $this->mrClip->parser = new ParserTestClass(explode(' ', '+testTag1'));
        $this->mrClip->parser->parseTags();

        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, false);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag3'], 'testText2', null, false);
        $todo3 = new Todo(3, 'testActivity', 'testCategory', ['testTag1'], 'testText3', null, false);

        $expected = "testActivity@testCategory +testTag1\n\n" .
        "+testTag2 testText\n" .
        "+testTag3 testText2\n" .
        "testText3\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2, $todo3]));
    }
    // }}}
    // {{{ testFormatTodosDone
    public function testFormatTodosDone()
    {
        $todo = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', null, true);

        $expected = "testActivity@testCategory\n\n" .
        "# +testTag1 +testTag2 testText\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo]));
    }
    // }}}
    // {{{ testFormatTodosDoneMixed
    public function testFormatTodosDoneMixed()
    {
        $todo1 = new Todo(1, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText1', null, true);
        $todo2 = new Todo(2, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText2', null, false);

        $expected = "testActivity@testCategory\n" .
        "\n" .
        "+testTag1 +testTag2 testText2\n" .
        "\n" .
        "# +testTag1 +testTag2 testText1\n";

        $this->assertSame($expected, $this->mrClip->formatTodos([$todo1, $todo2]));
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

    // {{{ testMatchTodos
    public function testMatchTodos()
    {
        $todos = $this->mrClip->prm->getTodos();

        $candidates = new \SplObjectStorage();
        $above = new \SplObjectStorage();
        $under = new \SplObjectStorage();
        $threshold = 70;

        $candidate1 = new Todo(null, 'activity1', 'category1', ['tag1', 'tag2'], 'text', null, false);
        $candidate2 = new Todo(null, 'activity1', 'category1', ['tag1', 'tag2'], 'text2', $candidate1, false);
        $candidate3 = new Todo(null, 'noActivity', 'noCategory', [], 'idontknow', $candidate1, false);

        $candidates->attach($candidate1);
        $candidates->attach($candidate2);
        $candidates->attach($candidate3);

        $rest = $this->mrClip->matchTodos($todos, $candidates, $above, $under, $threshold);

        $this->assertSame(1, $rest->count());
        $this->assertSame(2, $above->count());
        $this->assertSame(8, $under->count());

        $this->assertTrue($rest->contains($candidate3));
    }
    // }}}
}
