<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Parser;

class ParserTest extends \PhpUnit_Framework_TestCase
{
    // {{{ setUp
    protected function setUp()
    {
        parent::setUp();

        $this->parser = new ParserTestClass();
        $this->assertEquals([], $this->parser->options);
        $this->assertEquals([], $this->parser->commands);
        $this->assertSame(0, $this->parser->position);

        $this->assertNull($this->parser->domain);
        $this->assertNull($this->parser->command);
        $this->assertNull($this->parser->activity);
        $this->assertNull($this->parser->category);
        $this->assertEquals([], $this->parser->tags);
        $this->assertNull($this->parser->text);
        $this->assertNull($this->parser->start);
        $this->assertNull($this->parser->end);
        $this->assertNull($this->parser->done);
    }
    // }}}

    // {{{ testParseDomain
    public function testParseDomain()
    {
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = ['domain2'];

        $this->assertSame('domain2', $this->parser->parseDomain());

        $this->assertSame(1, $this->parser->position);
        $this->assertSame('domain2', $this->parser->domain);
    }
    // }}}
    // {{{ testParseDomainFail
    public function testParseDomainFail()
    {
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = ['noDomain'];

        $this->assertNull($this->parser->parseDomain());

        $this->assertSame(0, $this->parser->position);
    }
    // }}}

    // {{{ testParseCommand
    public function testParseCommand()
    {
        $this->parser->domain = 'domain1';
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = ['command2'];

        $this->assertSame('command2', $this->parser->parseCommand());

        $this->assertSame(1, $this->parser->position);
    }
    // }}}
    // {{{ testParseCommandFail
    public function testParseCommandFail()
    {
        $this->parser->domain = 'domain2';
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = ['noCommand'];

        $this->assertNull($this->parser->parseCommand());

        $this->assertSame(0, $this->parser->position);
    }
    // }}}
    // {{{ testParseCommandDomainWrong
    public function testParseCommandDomainWrong()
    {
        $this->parser->domain = 'domain2';
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = ['command2'];

        $this->assertNull($this->parser->parseCommand());

        $this->assertSame(0, $this->parser->position);
    }
    // }}}
    // {{{ testParseCommandDomainMissing
    public function testParseCommandDomainMissing()
    {
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = ['command2'];

        $this->assertNull($this->parser->parseCommand());

        $this->assertSame(0, $this->parser->position);
    }
    // }}}

    // {{{ testParseActigory
    public function testParseActigory()
    {
        $this->parser->options = ['activity@category'];

        $this->assertSame('activity@category', $this->parser->parseActigory());

        $this->assertSame(1, $this->parser->position);
        $this->assertSame('activity', $this->parser->activity);
        $this->assertSame('category', $this->parser->category);
    }
    // }}}
    // {{{ testParseActigoryCategory
    public function testParseActigoryCategory()
    {
        $this->parser->options = ['@category'];

        $this->assertSame('@category', $this->parser->parseActigory());

        $this->assertSame(1, $this->parser->position);
        $this->assertNull($this->parser->activity);
        $this->assertSame('category', $this->parser->category);
    }
    // }}}
    // {{{ testParseActigoryFail
    public function testParseActigoryFail()
    {
        $this->parser->options = ['activity'];
        $this->assertNull($this->parser->parseActigory());
        $this->assertNull($this->parser->activity);
        $this->assertNull($this->parser->category);
        $this->assertSame(0, $this->parser->position);

        $this->parser->options = ['activity@'];
        $this->assertNull($this->parser->parseActigory());
        $this->assertNull($this->parser->activity);
        $this->assertNull($this->parser->category);
        $this->assertSame(0, $this->parser->position);
    }
    // }}}

    // {{{ testParseTag
    public function testParseTag()
    {
        $this->parser->options = ['+tag'];

        $this->assertSame('tag', $this->parser->parseTag());

        $this->assertSame(['tag'], $this->parser->tags);
        $this->assertSame(1, $this->parser->position);
    }
    // }}}
}
