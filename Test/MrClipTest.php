<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Parser;

class MrClipTest extends \PhpUnit_Framework_TestCase
{
    // {{{ setUp
    protected function setUp()
    {
        parent::setUp();

        $this->mrClip = new MrClipTestClass();
    }
    // }}}

    // {{{ comp
    protected function comp($string)
    {
        $options = explode(' ', $string);
        array_unshift($options, 'completion');

        if (substr($string, -1) == ' ') {
            $options[] = "''";
        } else {
            end($options);
            $key = key($options);
            reset($options);

            $options[$key] = "'" . $options[$key] . "'";
        }

        $this->mrClip->run($options);
    }
    // }}}

    // {{{ testEmpty
    public function testEmpty()
    {
        $this->assertSame('', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionEmpty
    public function testCompletionEmpty()
    {
        $this->comp('');
        $this->assertSame('record todo', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionR
    public function testCompletionR()
    {
        $this->comp('r');
        $this->assertSame('record', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecord
    public function testCompletionRecord()
    {
        $this->comp('record');
        $this->assertSame('record', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecord_
    public function testCompletionRecord_()
    {
        $this->comp('record ');
        $this->assertSame('add current list stop continue', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordA
    public function testCompletionRecordA()
    {
        $this->comp('record a');
        $this->assertSame('add', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAdd
    public function testCompletionRecordAdd()
    {
        $this->comp('record add ');

        $this->assertSame(date('H:i') . ' 22:00', $this->mrClip->echoed);
    }
    // }}}
    // {{{ testCompletionRecordAddTime_
    public function testCompletionRecordAddTime_()
    {
        $this->comp('record add 22:00 ');
        $this->assertSame('activity1@category1 activity1@category2 activity2@category1 activity2@category2 @category1 @category2', $this->mrClip->echoed);
    }
    // }}}
}
