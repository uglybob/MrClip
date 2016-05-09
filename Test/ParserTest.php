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

    // {{{ testProcess
    public function testProcess()
    {
        $this->parser->options = ['item'];

        $this->assertSame('item', $this->parser->process('.*'));
        $this->assertSame(1, $this->parser->position);
    }
    // }}}
    // {{{ testProcessSpecific
    public function testProcessSpecific()
    {
        $this->parser->options = ['item'];

        $this->assertSame('item', $this->parser->process('item'));
        $this->assertSame(1, $this->parser->position);
    }
    // }}}
    // {{{ testProcessEmpty
    public function testProcessEmpty()
    {
        $this->parser->options = [];

        $this->assertNull($this->parser->process('.*'));
        $this->assertSame(0, $this->parser->position);
    }
    // }}}
    // {{{ testProcessFail
    public function testProcessFail()
    {
        $this->parser->options = ['item'];

        $this->assertNull($this->parser->process('I.*'));
        $this->assertSame(0, $this->parser->position);
    }
    // }}}

    // {{{ testCurrent
    public function testCurrent()
    {
        $this->assertNull($this->parser->current());

        $this->parser->options = ['item1', 'item2', 'item3'];
        $this->assertSame('item1', $this->parser->current());

        $this->parser->position = 1;
        $this->assertSame('item2', $this->parser->current());

        $this->parser->position = 10;
        $this->assertNull($this->parser->current());
    }
    // }}}
    // {{{ testAdvance
    public function testAdvance()
    {
        $this->assertSame(1, $this->parser->advance());
        $this->assertSame(1, $this->parser->position);
        $this->assertSame(2, $this->parser->advance());
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
    // {{{ testParseDomainEmpty
    public function testParseDomainEmpty()
    {
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = [];

        $this->assertNull($this->parser->parseDomain());

        $this->assertSame(0, $this->parser->position);
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
    // {{{ testParseCommandEmpty
    public function testParseCommandEmpty()
    {
        $this->parser->domain = 'domain2';
        $this->parser->commands = ['domain1' => ['command1', 'command2'], 'domain2' => ['command3']];
        $this->parser->options = [];

        $this->assertNull($this->parser->parseCommand());

        $this->assertSame(0, $this->parser->position);
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
    // {{{ testParseActigoryNoActivity
    public function testParseActigoryNoActivity()
    {
        $this->parser->options = ['@category'];

        $this->assertSame('@category', $this->parser->parseActigory());

        $this->assertSame(1, $this->parser->position);
        $this->assertNull($this->parser->activity);
        $this->assertSame('category', $this->parser->category);
    }
    // }}}
    // {{{ testParseActigoryEmpty
    public function testParseActigoryEmpty()
    {
        $this->parser->options = [];
        $this->assertNull($this->parser->parseActigory());
        $this->assertNull($this->parser->activity);
        $this->assertNull($this->parser->category);
        $this->assertSame(0, $this->parser->position);
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
    // {{{ testParseActigoryCategoryOptional
    public function testParseActigoryCategoryOptional()
    {
        $this->parser->options = ['activity@category'];

        $this->assertSame('activity@category', $this->parser->parseActigory(true));

        $this->assertSame(1, $this->parser->position);
        $this->assertSame('activity', $this->parser->activity);
        $this->assertSame('category', $this->parser->category);
    }
    // }}}
    // {{{ testParseActigoryCategoryOptionalNoCategory
    public function testParseActigoryCategoryOptionalNoCategory()
    {
        $this->parser->options = ['activity@'];

        $this->assertSame('activity@', $this->parser->parseActigory(true));

        $this->assertSame(1, $this->parser->position);
        $this->assertSame('activity', $this->parser->activity);
        $this->assertNull($this->parser->category);
    }
    // }}}
    // {{{ testParseActigoryCategoryOptionalNoActivity
    public function testParseActigoryCategoryOptionalNoActivity()
    {
        $this->parser->options = ['@category'];

        $this->assertSame('@category', $this->parser->parseActigory(true));

        $this->assertSame(1, $this->parser->position);
        $this->assertNull($this->parser->activity);
        $this->assertSame('category', $this->parser->category);
    }
    // }}}
    // {{{ testParseActigoryCategoryOptionalNoActivityNoCategory
    public function testParseActigoryCategoryOptionalNoActivityNoCategory()
    {
        $this->parser->options = ['@'];

        $this->assertSame('@', $this->parser->parseActigory(true));

        $this->assertSame(1, $this->parser->position);
        $this->assertNull($this->parser->activity);
        $this->assertNull($this->parser->category);
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
    // {{{ testParseTagEmpty
    public function testParseTagEmpty()
    {
        $this->parser->options = [];

        $this->assertNull($this->parser->parseTag());

        $this->assertSame([], $this->parser->tags);
        $this->assertSame(0, $this->parser->position);
    }
    // }}}
    // {{{ testParseTagFail
    public function testParseTagFail()
    {
        $this->parser->options = ['tag'];
        $this->assertNull($this->parser->parseTag());
        $this->assertSame([], $this->parser->tags);
        $this->assertSame(0, $this->parser->position);

        $this->parser->options = ['+'];
        $this->assertNull($this->parser->parseTag());
        $this->assertSame([], $this->parser->tags);
        $this->assertSame(0, $this->parser->position);
    }
    // }}}
    // {{{ testParseTagMultiple
    public function testParseTagMultiple()
    {
        $this->parser->options = ['+tag1', '+tag2'];

        $this->assertSame('tag1', $this->parser->parseTag());
        $this->assertSame('tag2', $this->parser->parseTag());

        $this->assertSame(['tag1', 'tag2'], $this->parser->tags);
        $this->assertSame(2, $this->parser->position);
    }
    // }}}
    // {{{ testParseTagDuplicate
    public function testParseTagDuplicate()
    {
        $this->parser->options = ['+tag1', '+tag1'];

        $this->assertSame('tag1', $this->parser->parseTag());
        $this->assertSame('tag1', $this->parser->parseTag());

        $this->assertSame(['tag1'], $this->parser->tags);
        $this->assertSame(2, $this->parser->position);
    }
    // }}}

    // {{{ testParseTags
    public function testParseTags()
    {
        $this->parser->options = ['+tag1', '+tag2'];

        $this->assertSame(['tag1', 'tag2'], $this->parser->parseTags());

        $this->assertSame(['tag1', 'tag2'], $this->parser->tags);
        $this->assertSame(2, $this->parser->position);
    }
    // }}}
    // {{{ testParseTagsEmpty
    public function testParseTagsEmpty()
    {
        $this->parser->options = [];

        $this->assertSame([], $this->parser->parseTags());

        $this->assertSame([], $this->parser->tags);
        $this->assertSame(0, $this->parser->position);
    }
    // }}}
    // {{{ testParseTagsFail
    public function testParseTagsFail()
    {
        $this->parser->options = ['+'];

        $this->assertSame([], $this->parser->parseTags());

        $this->assertSame([], $this->parser->tags);
        $this->assertSame(0, $this->parser->position);
    }
    // }}}
    // {{{ testParseTagsDuplicate
    public function testParseTagsDuplicate()
    {
        $this->parser->options = ['+tag1', '+tag1'];

        $this->assertSame(['tag1'], $this->parser->parseTags());

        $this->assertSame(['tag1'], $this->parser->tags);
        $this->assertSame(2, $this->parser->position);
    }
    // }}}
    // {{{ testParseTagsText
    public function testParseTagsText()
    {
        $this->parser->options = ['+tag1', '+tag2', 'text'];

        $this->assertSame(['tag1', 'tag2'], $this->parser->parseTags());

        $this->assertSame(['tag1', 'tag2'], $this->parser->tags);
        $this->assertSame(2, $this->parser->position);
    }
    // }}}

    // {{{ testParseText
    public function testParseText()
    {
        $this->parser->options = ['text'];

        $this->assertSame('text', $this->parser->parseText());

        $this->assertSame('text', $this->parser->text);
    }
    // }}}
    // {{{ testParseTextEmpty
    public function testParseTextEmpty()
    {
        $this->parser->options = [];

        $this->assertNull($this->parser->parseText());

        $this->assertNull($this->parser->text);
    }
    // }}}
    // {{{ testParseTextMultiple
    public function testParseTextMultiple()
    {
        $this->parser->options = ['text1', 'text2'];

        $this->assertSame('text1 text2', $this->parser->parseText());

        $this->assertSame('text1 text2', $this->parser->text);
    }
    // }}}

    // {{{ testParseStart
    public function testParseStart()
    {
        $this->parser->options = ['10:10'];
        $start = date('Y-m-d') . ' 10:10';

        $this->assertSame($start, $this->parser->parseStart()->format('Y-m-d H:i'));

        $this->assertSame($start, $this->parser->start->format('Y-m-d H:i'));
    }
    // }}}
    // {{{ testParseStartEmpty
    public function testParseStartEmpty()
    {
        $this->parser->options = [];

        $this->assertNull($this->parser->parseStart());
        $this->assertNull($this->parser->start);
    }
    // }}}
    // {{{ testParseStartFail
    public function testParseStartFail()
    {
        $this->parser->options = ['nope'];

        $this->assertNull($this->parser->parseStart());
        $this->assertNull($this->parser->start);
    }
    // }}}

    // {{{ testParseEnd
    public function testParseEnd()
    {
        $this->parser->options = ['10:10'];
        $end = date('Y-m-d') . ' 10:10';

        $this->assertSame($end, $this->parser->parseEnd()->format('Y-m-d H:i'));

        $this->assertSame($end, $this->parser->end->format('Y-m-d H:i'));
    }
    // }}}
    // {{{ testParseEndEmpty
    public function testParseEndEmpty()
    {
        $this->parser->options = [];

        $this->assertNull($this->parser->parseEnd());
        $this->assertNull($this->parser->end);
    }
    // }}}
    // {{{ testParseEndFail
    public function testParseEndFail()
    {
        $this->parser->options = ['nope'];

        $this->assertNull($this->parser->parseEnd());
        $this->assertNull($this->parser->end);
    }
    // }}}

    // {{{ testParseDone
    public function testParseDone()
    {
        $this->parser->options = ['#'];

        $this->assertTrue($this->parser->parseDone());

        $this->assertTrue($this->parser->done);
        $this->assertSame(1, $this->parser->position);
    }
    // }}}
    // {{{ testParseDoneEmpty
    public function testParseDoneEmpty()
    {
        $this->parser->options = [];

        $this->assertFalse($this->parser->parseDone());

        $this->assertFalse($this->parser->done);
        $this->assertSame(0, $this->parser->position);
    }
    // }}}
    // {{{ testParseDoneFail
    public function testParseDoneFail()
    {
        $this->parser->options = ['+'];

        $this->assertFalse($this->parser->parseDone());

        $this->assertFalse($this->parser->done);
        $this->assertSame(0, $this->parser->position);
    }
    // }}}

    // {{{ testParseMultiple
    public function testParseMultiple()
    {
        $this->parser->options = ['activity@category', '+tag1', '+tag2', 'text'];

        $this->assertFalse($this->parser->parseDone());
        $this->assertSame(0, $this->parser->position);
        $this->assertSame('activity@category', $this->parser->parseActigory());
        $this->assertSame(1, $this->parser->position);
        $this->assertSame(['tag1', 'tag2'], $this->parser->parseTags());
        $this->assertSame(3, $this->parser->position);
        $this->assertSame('text', $this->parser->parseText());

        $this->assertFalse($this->parser->done);
        $this->assertSame('activity', $this->parser->activity);
        $this->assertSame('category', $this->parser->category);
        $this->assertSame(['tag1', 'tag2'], $this->parser->tags);
        $this->assertSame('text', $this->parser->text);
    }
    // }}}

    // {{{ testGetDomain
    public function testGetDomain()
    {
        $this->assertNull($this->parser->getDomain());

        $this->parser->domain = 'testDomain';
        $this->assertSame('testDomain', $this->parser->getDomain());
    }
    // }}}
    // {{{ testGetCommand
    public function testGetCommand()
    {
        $this->assertNull($this->parser->getCommand());

        $this->parser->command = 'testCommand';
        $this->assertSame('testCommand', $this->parser->getCommand());
    }
    // }}}
    // {{{ testGetActivity
    public function testGetActivity()
    {
        $this->assertNull($this->parser->getActivity());

        $this->parser->activity = 'testActivity';
        $this->assertSame('testActivity', $this->parser->getActivity());
    }
    // }}}
    // {{{ testGetCategory
    public function testGetCategory()
    {
        $this->assertNull($this->parser->getCategory());

        $this->parser->category = 'testCategory';
        $this->assertSame('testCategory', $this->parser->getCategory());
    }
    // }}}
    // {{{ testGetTags
    public function testGetTags()
    {
        $this->assertSame([], $this->parser->getTags());

        $this->parser->tags = ['testTag'];
        $this->assertSame(['testTag'], $this->parser->getTags());
    }
    // }}}
    // {{{ testGetText
    public function testGetText()
    {
        $this->assertNull($this->parser->getText());

        $this->parser->text = 'testText';
        $this->assertSame('testText', $this->parser->getText());
    }
    // }}}

    // {{{ testGetStart
    public function testGetStart()
    {
        $this->assertNull($this->parser->getStart());

        $now = new \Datetime();

        $this->parser->start = $now;
        $this->assertSame($now, $this->parser->getStart());
    }
    // }}}
    // {{{ testGetEnd
    public function testGetEnd()
    {
        $this->assertNull($this->parser->getEnd());

        $now = new \Datetime();

        $this->parser->end = $now;
        $this->assertSame($now, $this->parser->getEnd());
    }
    // }}}
}
