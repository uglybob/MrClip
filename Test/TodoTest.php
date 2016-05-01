<?php

namespace Uglybob\MrClip\Test;

class TodoTest extends EntryTest
{
    // {{{ createTestObjects
    protected function createTestObjects()
    {
        $this->parent = new TodoTestClass(41, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText');
        $this->object = new TodoTestClass(42, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', $this->parent, false);
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
}
