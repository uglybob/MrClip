<?php

namespace Uglybob\MrClip\Test;

class RecordTest extends EntryTest
{
    // {{{ createTestObjects
    protected function createTestObjects()
    {
        $this->object = new RecordTestClass(42, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText', '10:10', null);
    }
    // }}}

    // {{{ testRecordDefaults
    public function testRecordDefaults()
    {
        $default = new RecordTestClass();

        $this->assertNull($default->getStart());
        $this->assertNull($default->getEnd());
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
        $this->assertSame($this->object->end, $this->object->getEnd());
    }
    // }}}

    // {{{ testSetStart
    public function testSetStart()
    {
        $this->object->setStart('10:20');
        $date = date('Y-m-d');

        $this->assertSame("$date 10:20", $this->object->getStart()->format('Y-m-d H:i'));
    }
    // }}}
    // {{{ testSetEnd
    public function testSetEnd()
    {
        $this->object->setEnd('10:20');
        $date = date('Y-m-d');

        $this->assertSame("$date 10:20", $this->object->getEnd()->format('Y-m-d H:i'));
    }
    // }}}

    // {{{ testStringToDatetime
    public function testStringToDatetime()
    {
        $this->assertNull($this->object->stringToDatetime(null));
        $this->assertEquals(date('Y-m-d') . ' 10:10', $this->object->stringToDatetime('10:10')->format('Y-m-d H:i'));
        $this->assertEquals('2015-10-21 16:29', $this->object->stringToDatetime('2015-10-21 16:29')->format('Y-m-d H:i'));
        $this->assertEquals('2015-10-21 16:29', $this->object->stringToDatetime(1445444940)->format('Y-m-d H:i'));
    }
    // }}}

    // {{{ testFormat
    public function testFormat()
    {
        $date = date('Y-m-d');

        $this->assertSame("$date 10:10 testActivity@testCategory +testTag1 +testTag2 testText", $this->object->format());
    }
    // }}}
}
