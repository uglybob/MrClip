<?php

namespace Uglybob\MrClip\Lib;

class Command
{
    // {{{ constructor
    public function __construct($string)
    {
        $this->parsed = false;
        $this->string = $string;

        $this->rTime = '\d{1,2}:\d{2}';
        $this->rTimes = "(?:{$this->rTime}\s+){0,2}";
        $this->rString = '[a-zA-Z0-9 ]+';
        $this->rTags = "(?:\s+\+{$this->rString})*";
        $this->rText = "\s+:({$this->rString})";
        $this->rFormat = "({$this->rTimes})({$this->rString})@({$this->rString})({$this->rTags})(?:{$this->rText})?";
        $this->rRegex = "/^\s*{$this->rFormat}\s*$/";
    }
    // }}}

    // {{{ at
    public function at()
    {
        $at = false;
        $this->hint = '';

        if (preg_match("/^\s*record\s*$/", $this->string)) {
            $at = 'start';
        } elseif (preg_match("/^\s*record\s*{$this->rTime}$/", $this->string)) {
            $at = 'actigory';
        } elseif (preg_match("/^\s*record\s*{$this->rTimes}({$this->rString})?$/", $this->string, $matches)) {
            $at = 'actigory';
            $this->hint = (isset($matches[1])) ? $matches[1] : '';
        } elseif (preg_match("/^\s*record\s*{$this->rTimes}({$this->rString}@{$this->rString})?$/", $this->string, $matches)) {
            $at = 'actigory';
            $this->hint = (isset($matches[1])) ? $matches[1] : '';
        } elseif (preg_match("/^\s*record\s*{$this->rTimes}{$this->rString}@{$this->rString}{$this->rTags}\s+\+({$this->rString})?$/", $this->string, $matches)) {
            $at = 'tag';
            $this->hint = (isset($matches[1])) ? $matches[1] : '';
        }

        return $at;
    }
    // }}}
    // {{{ getHint
    public function getHint()
    {
        return $this->hint;
    }
    // }}}

    // {{{ parse
    protected function parse()
    {
        if (!$this->parsed) {
            $this->parsed = true;

            $commands = $this->split($this->string);

            $this->activity = $commands['activity'];
            $this->category = $commands['category'];
            $this->times = $this->parseTimes($commands['times']);
            $this->tags = $this->parseTags($commands['tags']);
            $this->text = $commands['text'];
        }
    }
    // }}}
    // {{{ split
    protected function split($commandString)
    {
        preg_match($this->rRegex, $commandString, $matches);

        $result = [];
        $result['times'] = isset($matches[1]) ? $matches[1] : null;
        $result['activity'] = $matches[2];
        $result['category'] = $matches[3];
        $result['tags'] = isset($matches[4]) ? $matches[4] : null;
        $result['text'] = isset($matches[5]) ? $matches[5] : null;

        return $result;
    }
    // }}}
    // {{{ parseTimes
    protected function parseTimes($timesString)
    {
        preg_match_all("/{$this->rTime}/", $timesString, $matches);
        $times = (isset($matches[0])) ? $matches[0] : null;

        return $times;
    }
    // }}}
    // {{{ parseTags
    protected function parseTags($tagsString)
    {
        preg_match_all("/\+({$this->rString})/", $tagsString, $matches);
        $tags = $matches[1];
        $tags = array_map(
            function($value)
            {
                return trim($value);
            },
            $tags
        );

        return $tags;
    }
    // }}}

    // {{{ getStart
    public function getStart()
    {
        $this->parse();

        $dt = new \DateTime();

        if (isset($this->times[0])) {
            $split = explode(':', $this->times[0]);
            $dt->setTime($split[0], $split[1]);
        }

        return $dt->getTimestamp();
    }
    // }}}
    // {{{ getEnd
    public function getEnd()
    {
        $this->parse();

        $end = null;

        if (isset($this->times[1])) {
            $split = explode(':', $this->times[1]);
            $dt = new \DateTime();
            $dt->setTime($split[0], $split[1]);
            $end = $dt->getTimestamp();
        }

        return $end;
    }
    // }}}
    // {{{ getActivity
    public function getActivity()
    {
        $this->parse();
        return $this->activity;
    }
    // }}}
    // {{{ getCategory
    public function getCategory()
    {
        $this->parse();
        return $this->category;
    }
    // }}}
    // {{{ getTags
    public function getTags()
    {
        $this->parse();
        return $this->tags;
    }
    // }}}
    // {{{ getText
    public function getText()
    {
        $this->parse();
        return $this->text;
    }
    // }}}
}
