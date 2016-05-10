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

    // {{{ testEmpty
    public function testEmpty()
    {
        $this->assertSame('', $this->mrClip->echoed);
    }
    // }}}
}
