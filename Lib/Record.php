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
        return $this->end;
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
        $output[] = $this->start->format('Y-m-d H:i');

        if ($this->end) {
            $output[] = '- ' . $this->end->format('Y-m-d H:i');
        }

        $output[] = parent::format();

        return implode(' ', $output);
    }
    // }}}
}
