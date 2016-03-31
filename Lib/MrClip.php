<?php

namespace Uglybob\MrClip\Lib;

class MrClip
{
    // {{{ variables
    protected $prm = null;
    protected $options = [];
    protected $category = null;
    protected $activity = null;
    protected $tags = [];
    // }}}
    // {{{ constructor
    public function __construct($domain, $options)
    {
        $this->domain = $domain;
        $this->options = $this->cleanColons($options);
        $this->commands = [
            'record' => [
                'add',
                'current',
                'list',
                'stop',
                'continue',
            ],
            'todo' => [
                'add',
                'list',
                'edit',
            ],
        ];

        if ($domain == 'completion') {
            $this->completion();
        } else if (array_key_exists($domain, $this->commands)) {
            if (
                $this->parseCommand()
                && in_array($this->command, $this->commands[$domain])
            ) {
                $call = $domain . ucfirst($this->command);
                $this->$call();
            }
        }
    }
    // }}}

    // {{{ cleanColons
    public static function cleanColons($options)
    {
        $newOptions = [];

        for ($i = 0; $i < count($options); $i++) {
            if (
                (
                    isset($options[$i - 1])
                    && $options[$i - 1] == ':'
                )
                || $options[$i] == ':'
            ) {
                $newOptions[count($newOptions) - 1] .= $options[$i];
            } else if ($options[$i] !== ':') {
                $newOptions[] = $options[$i];
            }
        }

        return $newOptions;
    }
    // }}}

    // {{{ getPrm
    protected function getPrm()
    {
        if (!$this->prm) {
            $soapOptions = [
                'location' => Setup::get('url') . '/soap.php',
                'uri' => 'http://localhost/',
            ];

            $this->prm = new \SoapClient(null, $soapOptions);
            $this->prm->login(Setup::get('user'), Setup::get('pass'));
        }

        return $this->prm;
    }
    // }}}

    // {{{ completion
    protected function completion()
    {
        if (!empty(substr(array_shift($this->options), 1, -1))) {
            $this->current = array_pop($this->options);
        } else {
            $this->current = '';
        }

        if ($this->parseDomain()) {
            if ($this->domain == 'record') {
                if ($this->parseCommand()) {
                    if ($this->command == 'add') {
                        if ($this->parseStart()) {
                            $this->parseEnd();
                            $this->completionActigoryTags();
                        } else {
                            $times = [date('H:i')];

                            if ($last = $this->getPrm()->getLastRecord()) {
                                $times[] = date('H:i', $last->end);
                            }

                            $this->suggest($this->current, $times);
                        }
                    }
                } else {
                    $this->suggest($this->current, $this->commands[$this->domain]);
                }
            } else if ($this->domain == 'todo') {
                if ($this->parseCommand()) {
                    if ($this->command == 'list') {
                            $this->completionActigoryTags(true);
                    }
                } else {
                    $this->suggest($this->current, $this->commands[$this->domain]);
                }
            }
        } else {
            $this->suggest($this->current, array_keys($this->commands));
        }
    }
    // }}}
    // {{{ completionActigoryTags
    protected function completionActigoryTags($filter = false)
    {
        if ($this->parseActigory($filter)) {
            $this->parseTags();
            $tags = array_diff($this->getPrm()->getTags(), $this->tags);
            $this->suggest($this->current, $tags, '+');
        } else {
            $activities = $this->getPrm()->getActivities();
            $activities[] = ''; // categories without activities
            $categories = $this->getPrm()->getCategories();

            foreach($activities as $activity) {
                foreach($categories as $category) {
                    $actigories[] = "$activity@$category";
                }
            }

            $this->suggest($this->current, $actigories);
        }
    }
    // }}}

    // {{{ recordAdd
    protected function recordAdd()
    {
        if ($this->parseStart()) {
            $this->parseEnd();
            if ($this->parseActigory()) {
                $this->parseTags();
                $this->parseText();

                $record = $this->getPrm()->editRecord(
                    null,
                    $this->start,
                    $this->end,
                    $this->activity,
                    $this->category,
                    $this->tags,
                    $this->text
                );

                if ($record) {
                    echo "Record added\n\n";
                    $this->echoRecord($record);
                } else {
                    echo "Failed to add record";
                }
            }
        }
    }
    // }}}
    // {{{ recordCurrent
    protected function recordCurrent()
    {
        if ($record = $this->getPrm()->getCurrentRecord()) {
            echo "Running record\n\n";
            $this->echoRecord($record);
        } else {
            if ($last = $this->getPrm()->getLastRecord()) {
                echo "Last record\n\n";
                $this->echoRecord($last);
            } else {
                echo "Failed to fetch record\n";
            }
        }
    }
    // }}}
    // {{{ recordStop
    protected function recordStop()
    {
        $stopped = $this->getPrm()->stopRecord();

        if ($stopped) {
            echo "Record stopped\n\n";
            $this->echoRecord($stopped);
        } else {
            echo "Failed to stop record\n";
        }
    }
    // }}}
    // {{{ recordContinue
    protected function recordContinue()
    {
        $last = $this->getPrm()->getLastRecord();

        if ($last) {
            $record = $this->getPrm()->editRecord(
                null,
                time(),
                null,
                $last->activity,
                $last->category,
                $last->tags,
                $last->text
            );

            if ($record) {
                echo "Record added\n\n";
                $this->echoRecord($record);
            } else {
                echo "Failed to add record";
            }
        } else {
            echo "No previous record to continue";
        }
    }
    // }}}

    // {{{ todoList
    protected function todoList()
    {
        $this->parseActigory(true);
        $this->parseTags();

        $todos = $this->getPrm()->getTodos($this->activity, $this->category, $this->tags);

        $this->echoTodos($todos);
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
    protected function parseDomain()
    {
        $this->domain = $this->shift('(' . implode('|', array_keys($this->commands)) . ')');

        return $this->domain;
    }
    // }}}
    // {{{ parseCommand
    protected function parseCommand()
    {
        $this->command = $this->shift('(' . implode('|', $this->commands[$this->domain]) . ')');

        return $this->command;
    }
    // }}}
    // {{{ parseTime
    protected function parseTime()
    {
        return $this->shift('\d{1,2}:\d{2}');
    }
    // }}}
    // {{{ parseStart
    protected function parseStart()
    {
        $this->start = $this->timeToTimestamp($this->parseTime());

        return $this->start;
    }
    // }}}
    // {{{ parseEnd
    protected function parseEnd()
    {
        $this->end = $this->timeToTimestamp($this->parseTime());

        return $this->end;
    }
    // }}}
    // {{{ parseActigory
    protected function parseActigory($filter = false)
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
    protected function parseTag()
    {
        $tag = $this->shift('\+[a-zA-Z0-9]+');

        if ($tag) {
            $this->tags[] = substr($tag, 1);
        }

        return $tag;
    }
    // }}}
    // {{{ parseTags
    protected function parseTags()
    {
        $tag = true;

        while ($tag) {
            $tag = $this->parseTag();
        }
    }
    // }}}
    // {{{ parseText
    protected function parseText()
    {
        $this->text = implode(' ', $this->options);
        $this->options = [];

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

    // {{{ echoComplete
    protected function echoComplete($hint, $candidate, $prefix = '')
    {
        $escapedHint = preg_quote($hint);

        if (preg_match("/^$escapedHint/", $prefix . $candidate)) {
            echo "$prefix$candidate ";
        }
    }
    // }}}
    // {{{ suggest
    protected function suggest($hint, $candidates, $prefix = '')
    {
        foreach($candidates as $candidate) {
            $this->echoComplete($hint, $candidate, $prefix);
        }
    }
    // }}}

    // {{{ echoRecord
    protected function echoRecord($record)
    {
        echo 'Record    ' . $record->activity . '@' . $record->category . "\n";
        echo 'Start     ' . date('Y-m-d H:i', $record->start) . "\n";
        echo 'End       ';
        if ($record->end) {
            echo date('Y-m-d H:i', $record->end);
        }
        echo "\n";
        echo 'Tags      ' .  implode(', ', $record->tags) . "\n";
        echo 'Text      ' .  $record->text . "\n";
    }
    // }}}
    // {{{ echoTodos
    protected function echoTodos($todos)
    {
        foreach($todos as $todo) {
            if (!$this->activity) {
                echo $todo->activity;
            }
            if (!$this->activity || !$this->category) {
                echo '@';
            }
            if (!$this->category) {
                echo $todo->category;
            }
            if (!$this->activity || !$this->category) {
                echo ' ';
            }

            $otherTags = array_diff($todo->tags, $this->tags);
            echo implode(' ', $this->formatTags($otherTags));

            if (!empty($otherTags)) {
                echo ' ';
            }

            echo $todo->text . "\n";
        }
    }
    // }}}
    // {{{ formatTags
    protected function formatTags($tags)
    {
        $formatted = [];

        foreach($tags as $tag) {
            $formatted[] = "+$tag";
        }

        return $formatted;
    }
    // }}}
}
