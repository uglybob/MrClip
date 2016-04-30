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
}
