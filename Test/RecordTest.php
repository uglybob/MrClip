<?php

namespace Uglybob\MrClip\Test;

class RecordTest extends EntryTest
{
    // {{{ createTestObjects
    protected function createTestObjects()
    {
        $start = new \Datetime('2015-10-21 16:29');
        $this->object = new RecordTestClass(42, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', $start, null);
    }
    // }}}

    // {{{ testGetStart
    public function testGetStart()
    {
        $this->assertSame($this->object->start, $this->object->getStart());
    }
    // }}}
    // {{{ testGetEnd
    public function testGetEnd()
    {
        $now = date('Y-m-d H:i');
        $this->assertSame($now, $this->object->getEnd()->format('Y-m-d H:i'));

        $this->object->end = new \Datetime('2015-10-21 16:29');
        $this->assertSame($this->object->end, $this->object->getEnd());
    }
    // }}}

    // {{{ testSetStart
    public function testSetStart()
    {
        $start = new \Datetime();
        $this->object->setStart($start);

        $this->assertSame($start, $this->object->start);
    }
    // }}}
    // {{{ testSetStartNull
    public function testSetStartNull()
    {
        $this->object->setStart(null);

        $this->assertSame(date('Y-m-d H:i'), $this->object->start->format('Y-m-d H:i'));
    }
    // }}}
    // {{{ testSetEnd
    public function testSetEnd()
    {
        $end = new \Datetime();
        $this->object->setEnd($end);

        $this->assertSame($end, $this->object->end);
    }
    // }}}

    // {{{ testFormat
    public function testFormat()
    {
        $now = date('Y-m-d H:i');
        $this->assertSame("2015-10-21 16:29 - $now testActivity@testCategory +testTag1 +testTag2 testText", $this->object->format());

        $this->object->end = new \Datetime('2015-10-21 16:30');
        $this->assertSame('2015-10-21 16:29 - 2015-10-21 16:30 testActivity@testCategory +testTag1 +testTag2 testText', $this->object->format());

        $this->object->activity = 'test2Activity';
        $this->assertSame('2015-10-21 16:29 - 2015-10-21 16:30 test2Activity@testCategory +testTag1 +testTag2 testText', $this->object->format());

        $this->object->category = 'test2Category';
        $this->assertSame('2015-10-21 16:29 - 2015-10-21 16:30 test2Activity@test2Category +testTag1 +testTag2 testText', $this->object->format());

        $this->object->tags = ['testTag3'];
        $this->assertSame('2015-10-21 16:29 - 2015-10-21 16:30 test2Activity@test2Category +testTag3 testText', $this->object->format());

        $this->object->text = 'test2Text';
        $this->assertSame('2015-10-21 16:29 - 2015-10-21 16:30 test2Activity@test2Category +testTag3 test2Text', $this->object->format());

        $this->object->tags = [];
        $this->assertSame('2015-10-21 16:29 - 2015-10-21 16:30 test2Activity@test2Category test2Text', $this->object->format());

        $this->object->text = null;
        $this->assertSame('2015-10-21 16:29 - 2015-10-21 16:30 test2Activity@test2Category', $this->object->format());

        $this->object->end = null;
        $this->assertSame("2015-10-21 16:29 - $now test2Activity@test2Category", $this->object->format());
    }
    // }}}
}
