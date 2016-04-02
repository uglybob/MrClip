<?php

namespace Uglybob\MrClip\Lib;

class Parser
{
    // {{{ variables
    protected $options = [];
    protected $category = null;
    protected $activity = null;
    protected $tags = [];
    // }}}
    // {{{ constructor
    public function __construct($domain, $options, $commands)
    {
        $this->domain = $domain;
        $this->options = $options;
        $this->commands = $commands;
      }
    // }}}

    // {{{ shift
    protected function shift($regex)
    {
        $match = null;

        if (isset($this->options[0])) {
            $string = $this->options[0];

            preg_match("/^$regex$/", $string, $matches);

            if (isset($matches[0])) {
                $match = $matches[0];
                array_shift($this->options);
            }
        }

        return $match;
    }
    // }}}

    // {{{ parseDomain
    public function parseDomain()
    {
        $this->domain = $this->shift('(' . implode('|', array_keys($this->commands)) . ')');

        return $this->domain;
    }
    // }}}
    // {{{ parseCommand
    public function parseCommand()
    {
        $this->command = $this->shift('(' . implode('|', $this->commands[$this->domain]) . ')');

        return $this->command;
    }
    // }}}
    // {{{ parseTime
    public function parseTime()
    {
        return $this->shift('\d{1,2}:\d{2}');
    }
    // }}}
    // {{{ parseStart
    public function parseStart()
    {
        $this->start = $this->timeToTimestamp($this->parseTime());

        return $this->start;
    }
    // }}}
    // {{{ parseEnd
    public function parseEnd()
    {
        $this->end = $this->timeToTimestamp($this->parseTime());

        return $this->end;
    }
    // }}}
    // {{{ parseActigory
    public function parseActigory($filter = false)
    {
        if ($filter) {
            $actigory = $this->shift('[a-zA-Z0-9]*@[a-zA-Z0-9]*');
        } else {
            $actigory = $this->shift('[a-zA-Z0-9]*@[a-zA-Z0-9]+');
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
        $tag = $this->shift('\+[a-zA-Z0-9]+');

        if ($tag) {
            $this->tags[] = substr($tag, 1);
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
    }
    // }}}
    // {{{ parseText
    public function parseText()
    {
        $this->text = implode(' ', $this->options);
        $this->options = [];

        return $this->text;
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

    // {{{ timeToTimestamp
    protected function timeToTimestamp($time)
    {
        $timestamp = null;

        if ($time) {
            $split = explode(':', $time);
            $dt = new \DateTime();
            $dt->setTime($split[0], $split[1]);

            $timestamp = $dt->getTimestamp();
        }

        return $timestamp;
    }
    // }}}
}
