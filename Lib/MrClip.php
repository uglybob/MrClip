<?php

namespace Uglybob\MrClip\Lib;

class MrClip
{
    // {{{ variables
    protected $prm = null;
    protected $options = [];
    protected $tags = [];
    // }}}
    // {{{ constructor
    public function __construct($command, $options)
    {
        $this->options = $this->cleanColons($options);
        $this->commands = ['record', 'list'];

        if (
            $command == 'completion'
            || in_array($command, $this->commands)
        ) {
            $this->$command();
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

        if ($this->parseCommand()) {
            if ($this->command == 'record') {
                if ($this->parseStart()) {
                    $this->parseEnd();
                    if ($this->parseActigory()) {
                        $this->parseTags();
                        $tags = array_diff($this->getPrm()->getTags(), $this->tags);
                        $this->echoMultiComplete($this->current, $tags, '+');
                    } else {
                        $activities = $this->getPrm()->getActivities();
                        $categories = $this->getPrm()->getCategories();

                        foreach($activities as $activity) {
                            foreach($categories as $category) {
                                $actigories[] = "$activity@$category";
                            }
                        }
                        $this->echoMultiComplete($this->current, $actigories);
                    }
                } else {
                    $this->echoMultiComplete($this->current, [(new \Datetime())->format('H:i')]);
                }
            }
        } else {
            $this->echoMultiComplete($this->current, $this->commands);
        }
    }
    // }}}
    // {{{ record
    protected function record()
    {
        if ($this->parseStart()) {
            $this->parseEnd();
            if ($this->parseActigory()) {
                $this->parseTags();
                $this->parseText();

                $success = $this->getPrm()->editRecord(
                    null,
                    $this->start,
                    $this->end,
                    $this->activity,
                    $this->category,
                    $this->tags,
                    $this->text
                );

                if ($success) {
                    echo "Record added\n\n";
                    echo 'Start     ' .  date('Y-m-d H:i', $this->start) . "\n";
                    echo 'End       ';
                    if ($this->end) {
                        echo date('Y-m-d H:i', $this->end);
                    }
                    echo "\n";
                    echo 'Activity  ' .  $this->activity . "\n";
                    echo 'Category  ' .  $this->category . "\n";
                    echo 'Tags      ' .  implode(', ', $this->tags) . "\n";
                    echo 'Text      ' .  $this->text . "\n";
                } else {
                    echo "Failed to add record";
                }
            }
        }
    }
    // }}}

    // {{{ consume
    protected function consume($regex)
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
    // {{{ parseCommand
    protected function parseCommand()
    {
        $this->command = $this->consume('(' . implode('|', $this->commands) . ')');

        return $this->command;
    }
    // }}}
    // {{{ parseTime
    protected function parseTime()
    {
        return $this->consume('\d{1,2}:\d{2}');
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
    protected function parseActigory()
    {
        $actigory = $this->consume('[a-zA-Z0-9]+@[a-zA-Z0-9]+');

        if ($actigory) {
            $actigoryArray = explode('@', $actigory);
            $this->activity = $actigoryArray[0];
            $this->category = $actigoryArray[1];
        }

        return $actigory;
    }
    // }}}
    // {{{ parseTag
    protected function parseTag()
    {
        $tag = $this->consume('\+[a-zA-Z0-9]+');

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
    // {{{ echoMultiComplete
    protected function echoMultiComplete($hint, $candidates, $prefix = '')
    {
        foreach($candidates as $candidate) {
            $this->echoComplete($hint, $candidate, $prefix);
        }
    }
    // }}}
}
