<?php

namespace Uglybob\MrClip\Lib;

class Record extends Entry
{
    // {{{ variables
    protected $start;
    protected $end;
    // }}}
    // {{{ constructor
    public function __construct($id = null, $activity = null, $category = null, $tags = [], $text = null, $start = null, $end = null)
    {
        parent::__construct($id, $activity, $category, $tags, $text);

        $this->setStart($start);
        $this->setEnd($end);
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

    // {{{ setStart
    public function setStart($startString)
    {
        $this->start = $this->stringToDateTime($startString);
    }
    // }}}
    // {{{ setEnd
    public function setEnd($endString)
    {
        $this->end = $this->stringToDateTime($endString);
    }
    // }}}

    // {{{ stringToDatetime
    protected function stringToDatetime($timeString)
    {
        $datetime = null;

        if (!is_null($timeString)) {

            if (is_int($timeString)) {
                $timeInt = (int) $timeString;

                if ($timeInt >= 0) {
                    $datetime = new \DateTime();
                    $datetime->setTimestamp($timeInt);
                }
            } else {
                $datetime = new \DateTime($timeString);
            }
        }

        return $datetime;
    }
    // }}}
    // {{{ format
    public function format()
    {
        $output[] = $this->start->format('Y-m-d H:i');

        if ($this->end) {
            $output[] = '- ' . $this->end->format('Y-m-d H:i');
        }

        $output[] = parent::format();

        return implode(' ', $output);
    }
    // }}}
}
