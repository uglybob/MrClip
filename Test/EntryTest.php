<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Entry;

class EntryTest extends \PhpUnit_Framework_TestCase
{
    // {{{ setUp
    protected function setUp()
    {
        parent::setUp();

        $this->entry = new EntryTestClass(42, 'testActivity', 'testCategory', ['testTag1', 'testTag2'], 'testText');
    }
    // }}}

    // {{{ testGetId
    public function testGetId()
    {
        $this->assertSame(42, $this->entry->getId());
    }
    // }}}
    // {{{ testGetActivity
    public function testGetActivity()
    {
        $this->assertSame('testActivity', $this->entry->getActivity());
    }
    // }}}
    // {{{ testGetCategory
    public function testGetCategory()
    {
        $this->assertSame('testCategory', $this->entry->getCategory());
    }
    // }}}
    // {{{ testGetTags
    public function testGetTags()
    {
        $this->assertSame(['testTag1', 'testTag2'], $this->entry->getTags());
    }
    // }}}
    // {{{ testGetText
    public function testGetText()
    {
        $this->assertSame('testText', $this->entry->getText());
    }
    // }}}

    // {{{ testSetId
    public function testSetId()
    {
        $this->assertSame(42, $this->entry->getId());
        $this->entry->SetId(1);
        $this->assertSame(1, $this->entry->getId());
    }
    // }}}

    // {{{ testGetDefaults
    public function testGetDefaults()
    {
        $default = new EntryTestClass();

        $this->assertNull($default->getId());
        $this->assertNull($default->getActivity());
        $this->assertNull($default->getCategory());
        $this->assertEquals([], $default->getTags());
        $this->assertNull($default->getText());
    }
    // }}}

    // {{{ testGetActigory
    public function testGetActigory()
    {
        $this->assertSame('testActivity@testCategory', $this->entry->getActigory());
    }
    // }}}
    // {{{ testFormat
    public function testFormat()
    {
        $this->assertSame('testActivity@testCategory +testTag1 +testTag2 testText', $this->entry->format());
    }
    // }}}

    // {{{ testGetFormattedTags
    public function testGetFormattedTags()
    {
        $this->assertSame('+testTag1 +testTag2', $this->entry->getFormattedTags());
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
