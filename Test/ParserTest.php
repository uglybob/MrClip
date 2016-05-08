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
    }
    // }}}

    // {{{ testParseDomain
    public function testParseDomain()
    {
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = ['domain2'];
        $this->assertSame(0, $this->parser->position);

        $this->assertSame('domain2', $this->parser->parseDomain());

        $this->assertSame(1, $this->parser->position);
    }
    // }}}
    // {{{ testParseDomainFail
    public function testParseDomainFail()
    {
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = ['noDomain'];
        $this->assertSame(0, $this->parser->position);

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
        $this->assertSame(0, $this->parser->position);

        $this->assertSame('command2', $this->parser->parseCommand());

        $this->assertSame(1, $this->parser->position);
    }
    // }}}
    // {{{ testParseCommandDomainWrong
    public function testParseCommandDomainWrong()
    {
        $this->parser->domain = 'domain2';
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = ['command2'];
        $this->assertSame(0, $this->parser->position);

        $this->assertNull($this->parser->parseCommand());

        $this->assertSame(0, $this->parser->position);
    }
    // }}}
    // {{{ testParseCommandDomainMissing
    public function testParseCommandDomainMissing()
    {
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = ['command2'];
        $this->assertSame(0, $this->parser->position);

        $this->assertNull($this->parser->parseCommand());

        $this->assertSame(0, $this->parser->position);
    }
    // }}}
}
