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

    // {{{ testFormat
    public function testFormat()
    {

        $this->assertSame('2016-05-07 10:10 testActivity@testCategory +testTag1 +testTag2 testText', $this->object->format());
    }
    // }}}
}
