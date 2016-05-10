<?php

namespace Uglybob\MrClip\Lib;

class Record extends Entry
{
    // {{{ variables
    protected $start;
    protected $end;
    // }}}
    // {{{ constructor
    public function __construct($id = null, $activity = null, $category = null, $tags = [], $text = null, \Datetime $start, $end = null)
    {
        parent::__construct($id, $activity, $category, $tags, $text);

        $this->start = $start;
        $this->end = $end;
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
        if (is_null($this->end)) {
            $end = new \Datetime();
        } else {
            $end = $this->end;
        }

        return $end;
    }
    // }}}

    // {{{ setStart
    public function setStart(\Datetime $start)
    {
        $this->start = $start;
    }
    // }}}
    // {{{ setEnd
    public function setEnd($end)
    {
        $this->end = $end;
    }
    // }}}

    // {{{ format
    public function format()
    {
        $output[] = $this->getStart()->format('Y-m-d H:i');
        $output[] = '-';
        $output[] = $this->getEnd()->format('Y-m-d H:i');
        $output[] = parent::format();

        return implode(' ', $output);
    }
    // }}}
}
