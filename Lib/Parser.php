<?php

namespace Uglybob\MrClip\Lib;

class Parser
{
    // {{{ variables
    protected $options;
    protected $commands;
    protected $position;

    protected $domain;
    protected $command;
    protected $activity;
    protected $category;
    protected $tags;
    protected $text;
    protected $start;
    protected $end;
    protected $done;
    // }}}
    // {{{ constructor
    public function __construct($domain = null, $options = [], $commands = [])
    {
        $this->domain = $domain;
        $this->options = $options;
        $this->commands = $commands;
        $this->position = 0;

        $this->command = null;
        $this->category = null;
        $this->activity = null;
        $this->tags = [];
        $this->text = null;
        $this->start = null;
        $this->end = null;
        $this->done = null;
    }
    // }}}

    // {{{ process
    protected function process($regex)
    {
        $match = null;
        $current = $this->current();

        if ($current) {
            preg_match("/^$regex$/", $current, $matches);

            if (isset($matches[0])) {
                $match = $matches[0];
                $this->advance();
            }
        }

        return $match;
    }
    // }}}
    // {{{ current
    protected function current()
    {
        $result = null;

        if (isset($this->options[$this->position])) {
            $result = $this->options[$this->position];
        }

        return $result;
    }
    // }}}
    // {{{ advance
    protected function advance()
    {
        return $this->position++;
    }
    // }}}

    // {{{ parseDomain
    public function parseDomain()
    {
        $this->domain = $this->process('(' . implode('|', array_keys($this->commands)) . ')');

        return $this->domain;
    }
    // }}}
    // {{{ parseCommand
    public function parseCommand()
    {
        $this->command = null;

        if (isset($this->commands[$this->domain])) {
            $this->command = $this->process('(' . implode('|', $this->commands[$this->domain]) . ')');
        }

        return $this->command;
    }
    // }}}
    // {{{ parseActigory
    public function parseActigory($filter = false)
    {
        if ($filter) {
            $actigory = $this->process('[a-zA-Z0-9]*@[a-zA-Z0-9]*');
        } else {
            $actigory = $this->process('[a-zA-Z0-9]*@[a-zA-Z0-9]+');
        }

        if ($actigory) {
            $actigoryArray = explode('@', $actigory);
            $this->activity = ($actigoryArray[0]) ? ($actigoryArray[0]) : null;
            $this->category = ($actigoryArray[1]) ? ($actigoryArray[1]) : null;
        }

        return $actigory;
    }
    // }}}
    // {{{ parseTag
    public function parseTag()
    {
        $tag = null;
        $match = $this->process('\+[a-zA-Z0-9]+');

        if ($match) {
            $tag = substr($match, 1);

            if (!in_array($tag, $this->tags)) {
                $this->tags[] = $tag;
            }
        }

        return $tag;
    }
    // }}}
    // {{{ parseTags
    public function parseTags()
    {
        $tag = true;

        while ($tag) {
            $tag = $this->parseTag();
        }

        return $this->tags;
    }
    // }}}
    // {{{ parseText
    public function parseText()
    {
        $text = null;

        if (!empty($this->options)) {
            $text = implode(' ', $this->options);
            $this->options = [];
        }

        $this->text = $text;

        return $this->text;
    }
    // }}}

    // {{{ parseTime
    protected function parseTime()
    {
        // @todo catch 99:99
        return $this->stringToDatetime($this->process('\d{1,2}:\d{2}'));
    }
    // }}}
    // {{{ stringToDatetime
    protected function stringToDatetime($timeString)
    {
        $datetime = null;

        if (!is_null($timeString)) {
            $datetime = new \DateTime($timeString);
        }

        return $datetime;
    }
    // }}}
    // {{{ parseStart
    public function parseStart()
    {
        $this->start = $this->parseTime();

        return $this->start;
    }
    // }}}
    // {{{ parseEnd
    public function parseEnd()
    {
        $this->end = $this->parseTime();

        return $this->end;
    }
    // }}}

    // {{{ parseDone
    public function parseDone()
    {
        $done = false;
        $checkedString = '#';
        $current = $this->current();

        if ($current) {
            if ($current == $checkedString) {
                $done = true;
                $this->advance();
            }

            preg_match("/^$checkedString/", $current, $matches);

            if (isset($matches[0])) {
                $done = true;
                $this->options[$this->position] = substr($current, count($checkedString));
            }
        }

        $this->done = $done;

        return $done;
    }
    // }}}

    // {{{ getDomain
    public function getDomain()
    {
        return $this->domain;
    }
    // }}}
    // {{{ getCommand
    public function getCommand()
    {
        return $this->command;
    }
    // }}}
    // {{{ getActivity
    public function getActivity()
    {
        return $this->activity;
    }
    // }}}
    // {{{ getCategory
    public function getCategory()
    {
        return $this->category;
    }
    // }}}
    // {{{ getTags
    public function getTags()
    {
        return $this->tags;
    }
    // }}}
    // {{{ getText
    public function getText()
    {
        return $this->text;
    }
    // }}}

    // {{{ getStart
    public function getStart()
    {
        return $this->start;
    }
    // }}}
    // {{{ getEnd
    public function getEnd()
    {
        return $this->end;
    }
    // }}}
}
