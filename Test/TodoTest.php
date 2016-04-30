<?php

namespace Uglybob\MrClip\Test;

class TodoTest extends EntryTest
{
    // {{{ createTestObjects
    protected function createTestObjects()
    {
        $this->default = new TodoTestClass();
        $this->parent = new TodoTestClass(41, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText');
        $this->object = new TodoTestClass(42, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', $this->parent, false);
    }
    // }}}

    // {{{ testIsDone
    public function testIsDone()
    {
        $this->assertFalse($this->object->isDone());
    }
    // }}}
    // {{{ testGetParent
    public function testGetParent()
    {
        $this->assertSame($this->parent, $this->object->getParent());
    }
    // }}}
    // {{{ testGetParentId
    public function testGetParentId()
    {
        $this->assertSame(41, $this->object->getParentId());
    }
    // }}}
    // {{{ testGetChildren
    public function testGetChildren()
    {
        $this->assertEquals([], $this->object->getChildren());
        $this->assertEquals([$this->object], $this->parent->getChildren());
    }
    // }}}

    // {{{ testIsDoneDefault
    public function testIsDoneDefault()
    {
        $this->assertFalse($this->default->isDone());
    }
    // }}}
    // {{{ testGetParentDefault
    public function testGetParentDefault()
    {
        $this->assertNull($this->default->getParent());
    }
    // }}}
    // {{{ testGetParentIdDefault
    public function testGetParentIdDefault()
    {
        $this->assertNull($this->default->getParentId());
    }
    // }}}
    // {{{ testGetChildrenDefault
    public function testGetChildrenDefault()
    {
        $this->assertEquals([], $this->default->getChildren());
    }
    // }}}

    // {{{ testSetParent
    public function testSetParent()
    {
        $this->assertNull($this->default->parent);

        $this->default->setParent($this->parent);

        $this->assertSame($this->parent, $this->default->parent);
        $this->assertEquals([$this->object, $this->default], $this->parent->children);
    }
    // }}}
}
