<?php

namespace Uglybob\MrClip\Test;

class TodoTest extends EntryTest
{
    // {{{ createTestObjects
    protected function createTestObjects()
    {
        $this->parent = new TodoTestClass(41, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testParentText');
        $this->object = new TodoTestClass(42, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', $this->parent, false);
        $this->todo = $this->object;
    }
    // }}}

    // {{{ testTodoDefaults
    public function testTodoDefaults()
    {
        $default = new TodoTestClass();

        $this->assertFalse($default->isDone());
        $this->assertNull($default->getParent());
        $this->assertNull($default->getParentId());
        $this->assertEquals([], $default->getChildren());
    }
    // }}}

    // {{{ testIsDone
    public function testIsDone()
    {
        $this->object->done = null;
        $this->assertFalse($this->object->isDone());

        $this->object->done = true;
        $this->assertTrue($this->object->isDone());

        $this->object->done = false;
        $this->assertFalse($this->object->isDone());
    }
    // }}}
    // {{{ testGetParent
    public function testGetParent()
    {
        $this->assertSame($this->parent, $this->object->getParent());

        $otherParent = new TodoTestClass();

        $this->object->parent = $otherParent;
        $this->assertSame($otherParent, $this->object->getParent());
    }
    // }}}
    // {{{ testGetParentId
    public function testGetParentId()
    {
        $this->assertSame(41, $this->object->getParentId());

        $this->object->parentId = 10;
        $this->assertSame(41, $this->object->getParentId());

        $this->object->parent = null;
        $this->assertSame(10, $this->object->getParentId());
    }
    // }}}
    // {{{ testGetChildren
    public function testGetChildren()
    {
        $this->assertEquals([], $this->object->getChildren());
        $this->assertEquals([$this->object], $this->parent->getChildren());
    }
    // }}}

    // {{{ testSetParent
    public function testSetParent()
    {
        $this->assertSame($this->parent, $this->object->parent);
        $this->object->parentId = 10;

        $otherParent = new TodoTestClass();

        $this->object->setParent($otherParent);

        $this->assertNull($this->object->parentId);
        $this->assertSame($otherParent, $this->object->parent);
        $this->assertEquals([$this->object], $otherParent->children);
    }
    // }}}
    // {{{ testSetParentId
    public function testSetParentId()
    {
        $this->assertNull($this->object->parentId);

        $this->object->setParentId(10);
        $this->assertSame(10, $this->object->parentId);
    }
    // }}}
    // {{{ testAddChild
    public function testAddChild()
    {
        $this->assertEquals([], $this->object->children);

        $child1 = new TodoTestClass();
        $child2 = new TodoTestClass();

        $this->object->addChild($child1);
        $this->assertEquals([$child1], $this->object->children);

        $this->object->addChild($child2);
        $this->assertEquals([$child1, $child2], $this->object->children);
    }
    // }}}

    // {{{ testFormat
    public function testFormat()
    {
        $this->assertSame('testActivity@testCategory +testTag1 +testTag2 testText', $this->object->format());

        $this->object->done = true;
        $this->assertSame('# testActivity@testCategory +testTag1 +testTag2 testText', $this->object->format());
    }
    // }}}
    // {{{ testFormatFiltered
    public function testFormatFiltered()
    {
        $filter = [];
        $this->assertSame('+testTag1 +testTag2 testText', $this->object->formatTagsText($filter));

        $filter = ['testTag3'];
        $this->assertSame('+testTag1 +testTag2 testText', $this->object->formatTagsText($filter));

        $filter = ['testTag2'];
        $this->assertSame('+testTag1 testText', $this->object->formatTagsText($filter));

        $filter = ['testTag1', 'testTag2'];
        $this->assertSame('testText', $this->object->formatTagsText($filter));

        $filter = ['testTag2', 'testTag1'];
        $this->assertSame('testText', $this->object->formatTagsText($filter));

        $filter = ['testTag1', 'testTag2', 'testTag3'];
        $this->assertSame('testText', $this->object->formatTagsText($filter));
    }
    // }}}

    // {{{ testMatch
    public function testMatch()
    {
        $this->assertSame(86, $this->object->match($this->parent));
        $this->assertSame(86, $this->object->confidence);
        $this->assertSame($this->parent, $this->object->guess);

        $this->assertSame(100, $this->object->match($this->object));
        $this->assertSame(100, $this->object->confidence);
        $this->assertSame($this->object, $this->object->guess);

    }
    // }}}
    // {{{ testMatchSame
    public function testMatchSame()
    {
        $this->todo1 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag'], 'testText', null, false);
        $this->todo2 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag'], 'testText', null, false);

        $this->assertSame(100, $this->todo1->match($this->todo2));
        $this->assertSame(100, $this->todo1->confidence);
        $this->assertSame($this->todo2, $this->todo1->guess);
    }
    // }}}
    // {{{ testMatchEditActivity
    public function testMatchEditActivity()
    {
        $this->todo1 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag'], 'testText', null, false);
        $this->todo2 = new TodoTestClass(null, 'testActivity2', 'testCategory', ['testTag'], 'testText', null, false);

        $this->assertSame(90, $this->todo1->match($this->todo2));
        $this->assertSame(90, $this->todo1->confidence);
        $this->assertSame($this->todo2, $this->todo1->guess);
    }
    // }}}
    // {{{ testMatchEditCategory
    public function testMatchEditCategory()
    {
        $this->todo1 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag'], 'testText', null, false);
        $this->todo2 = new TodoTestClass(null, 'testActivity', 'testCategory2', ['testTag'], 'testText', null, false);

        $this->assertSame(90, $this->todo1->match($this->todo2));
        $this->assertSame(90, $this->todo1->confidence);
        $this->assertSame($this->todo2, $this->todo1->guess);
    }
    // }}}
    // {{{ testMatchEditTagsMinus1
    public function testMatchEditTagsMinus1()
    {
        $this->todo1 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag'], 'testText', null, false);
        $this->todo2 = new TodoTestClass(null, 'testActivity', 'testCategory', [], 'testText', null, false);

        $this->assertSame(90, $this->todo1->match($this->todo2));
        $this->assertSame(90, $this->todo1->confidence);
        $this->assertSame($this->todo2, $this->todo1->guess);
    }
    // }}}
    // {{{ testMatchEditTagsPlus1
    public function testMatchEditTagsPlus1()
    {
        $this->todo1 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag'], 'testText', null, false);
        $this->todo2 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag', 'testTag2'], 'testText', null, false);

        $this->assertSame(90, $this->todo1->match($this->todo2));
        $this->assertSame(90, $this->todo1->confidence);
        $this->assertSame($this->todo2, $this->todo1->guess);
    }
    // }}}
    // {{{ testMatchEditTagsEdit
    public function testMatchEditTagsEdit()
    {
        $this->todo1 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag'], 'testText', null, false);
        $this->todo2 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag2'], 'testText', null, false);

        $this->assertSame(80, $this->todo1->match($this->todo2));
        $this->assertSame(80, $this->todo1->confidence);
        $this->assertSame($this->todo2, $this->todo1->guess);
    }
    // }}}
    // {{{ testMatchEditText
    public function testMatchEditText()
    {
        $this->todo1 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag'], 'testText', null, false);
        $this->todo2 = new TodoTestClass(null, 'testActivity', 'testCategory', ['testTag'], 'testText2', null, false);

        $this->assertTrue(100 > $this->todo1->match($this->todo2));
    }
    // }}}

    // {{{ testActivityConfidence
    public function testActivityConfidence()
    {
        $this->assertSame(100, $this->todo->activityConfidence('testActivity', 'testActivity', 100));
        $this->assertSame(0, $this->todo->activityConfidence('testActivity', 'testActivity2', 100));
        $this->assertSame(0, $this->todo->activityConfidence(null, 'testActivity', 100));
        $this->assertSame(0, $this->todo->activityConfidence(null, false, 100));
        $this->assertSame(0, $this->todo->activityConfidence(true, 'testActivity', 100));
        $this->assertSame(0, $this->todo->activityConfidence(1234, '1234', 100));

        $this->assertSame(10, $this->todo->activityConfidence('testActivity', 'testActivity', 10));
    }
    // }}}
    // {{{ testCategoryConfidence
    public function testCategoryConfidence()
    {
        $this->assertSame(100, $this->todo->categoryConfidence('testCategory', 'testCategory', 100));
        $this->assertSame(0, $this->todo->categoryConfidence('testCategory', 'testCategory2', 100));
        $this->assertSame(0, $this->todo->categoryConfidence(null, 'testCategory', 100));
        $this->assertSame(0, $this->todo->categoryConfidence(null, false, 100));
        $this->assertSame(0, $this->todo->categoryConfidence(true, 'testCategory', 100));
        $this->assertSame(0, $this->todo->categoryConfidence(1234, '1234', 100));

        $this->assertSame(10, $this->todo->categoryConfidence('testCategory', 'testCategory', 10));
    }
    // }}}
    // {{{ testTagsConfidence
    public function testTagsConfidence()
    {
        $this->assertSame(100, $this->todo->tagsConfidence([], [], 100));
        $this->assertSame(100, $this->todo->tagsConfidence(['testTag'], ['testTag'], 100));
        $this->assertSame(100, $this->todo->tagsConfidence(['testTag', 'testTag2'], ['testTag', 'testTag2'], 100));

        $this->assertSame(67, $this->todo->tagsConfidence(['testTag', 'testTag2'], ['testTag2'], 100));
        $this->assertSame(67, $this->todo->tagsConfidence(['testTag'], ['testTag', 'testTag2'], 100));

        $this->assertSame(67, $this->todo->tagsConfidence(['testTag'], [], 100));
        $this->assertSame(67, $this->todo->tagsConfidence([], ['testTag'], 100));

        $this->assertSame(34, $this->todo->tagsConfidence([true], ['1234'], 100));

        // @todo
        // $this->assertSame(33, $this->todo->tagsConfidence([1234], ['1234'], 100));
        // $this->assertSame(34, $this->todo->tagsConfidence([true], [1], 100));
        // $this->assertSame(34, $this->todo->tagsConfidence([false], [null], 100));
    }
    // }}}

    // {{{ testGetGuess
    public function testGetGuess()
    {
        $this->assertNull($this->object->getGuess());

        $this->assertSame(86, $this->object->match($this->parent));
        $this->assertSame($this->parent, $this->object->getGuess());
    }
    // }}}
    // {{{ testGetConfidence
    public function testGetConfidence()
    {
        $this->assertNull($this->object->getConfidence());

        $this->assertSame(86, $this->object->match($this->parent));
        $this->assertSame(86, $this->object->getConfidence());
    }
    // }}}
}
