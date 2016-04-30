<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Entry;

class EntryTest extends \PhpUnit_Framework_TestCase
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

    // {{{ testGetIdDefault
    public function testGetIdDefault()
    {
        $this->assertNull($this->default->getId());
    }
    // }}}
    // {{{ testGetActivityDefault
    public function testGetActivityDefault()
    {
        $this->assertNull($this->default->getActivity());
    }
    // }}}
    // {{{ testGetCategoryDefault
    public function testGetCategoryDefault()
    {
        $this->assertNull($this->default->getCategory());
    }
    // }}}
    // {{{ testGetTagsDefault
    public function testGetTagsDefault()
    {
        $this->assertEquals([], $this->default->getTags());
    }
    // }}}
    // {{{ testGetTextDefault
    public function testGetTextDefault()
    {
        $this->assertSame('testText', $this->object->getText());
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
