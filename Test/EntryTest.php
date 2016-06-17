<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Entry;

class EntryTest extends \PHPUnit_Framework_TestCase
{
    // {{{ setUp
    protected function setUp()
    {
        parent::setUp();

        $this->createTestObjects();
    }
    // }}}

    // {{{ createTestObjects
    protected function createTestObjects()
    {
        $this->default = new EntryTestClass();
        $this->object = new EntryTestClass(42, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText');
    }
    // }}}

    // {{{ testEntryDefaults
    public function testEntryDefaults()
    {
        $default = new EntryTestClass();

        $this->assertNull($default->getId());
        $this->assertNull($default->getActivity());
        $this->assertNull($default->getCategory());
        $this->assertEquals([], $default->getTags());
        $this->assertNull($default->getText());
    }
    // }}}

    // {{{ testGetId
    public function testGetId()
    {
        $this->assertSame(42, $this->object->getId());
    }
    // }}}
    // {{{ testGetActivity
    public function testGetActivity()
    {
        $this->assertSame('testActivity', $this->object->getActivity());
    }
    // }}}
    // {{{ testGetCategory
    public function testGetCategory()
    {
        $this->assertSame('testCategory', $this->object->getCategory());
    }
    // }}}
    // {{{ testGetTags
    public function testGetTags()
    {
        $this->assertSame(['testTag1', 'testTag2'], $this->object->getTags());
    }
    // }}}
    // {{{ testGetText
    public function testGetText()
    {
        $this->assertSame('testText', $this->object->getText());
    }
    // }}}

    // {{{ testSetId
    public function testSetId()
    {
        $this->assertSame(42, $this->object->getId());
        $this->object->SetId(1);
        $this->assertSame(1, $this->object->getId());
    }
    // }}}

    // {{{ testGetActigory
    public function testGetActigory()
    {
        $this->assertSame('testActivity@testCategory', $this->object->getActigory());
    }
    // }}}
    // {{{ testFormat
    public function testFormat()
    {
        $this->assertSame('testActivity@testCategory +testTag1 +testTag2 testText', $this->object->format());
    }
    // }}}

    // {{{ testGetFormattedTags
    public function testGetFormattedTags()
    {
        $this->assertSame('+testTag1 +testTag2', $this->object->getFormattedTags());
    }
    // }}}

    // {{{ testFormatTags
    public function testFormatTags()
    {
        $this->assertSame('+testTag1', Entry::formatTags(['testTag1']));
        $this->assertSame('+testTag1 +testTag2', Entry::formatTags(['testTag1', 'testTag2']));
        $this->assertSame('', Entry::formatTags(null));
    }
    // }}}
}
