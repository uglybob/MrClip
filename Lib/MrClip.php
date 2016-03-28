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

        if (!empty(substr(array_shift($this->options), 1, -1))) {
            $this->current = array_pop($this->options);
        } else {
            $this->current = '';
        }

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
        /*
        $cm = new Command($options);

        $this->getPrm()->editRecord(
            null,
            $cm->getStart(),
            $cm->getEnd(),
            $cm->getActivity(),
            $cm->getCategory(),
            $cm->getTags(),
            $cm->getText()
        );
        */
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
    protected function parseTime($time)
    {
        $this->$time = $this->consume('\d{1,2}:\d{2}');

        return $this->$time;
    }
    // }}}
    // {{{ parseStart
    protected function parseStart()
    {
        return $this->parseTime('start');
    }
    // }}}
    // {{{ parseEnd
    protected function parseEnd()
    {
        return $this->parseTime('end');
    }
    // }}}
    // {{{ parseActigory
    protected function parseActigory()
    {
        $this->actigory = $this->consume('[a-zA-Z0-9]+@[a-zA-Z0-9]+');

        return $this->actigory;
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
