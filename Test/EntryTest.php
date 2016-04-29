<?php

namespace Uglybob\MrClip\Test;

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
}
