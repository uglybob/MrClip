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
    public function setStart($start)
    {
        if (is_null($start)) {
            $this->start = new \Datetime();
        } else {
            $this->start = $start;
        }

        return $this->start;
    }
    // }}}
    // {{{ setEnd
    public function setEnd($end)
    {
        $this->end = $end;
    }
    // }}}

    // {{{ isRunning
    public function isRunning()
    {
        return (!$this->end);
    }
    // }}}
    // {{{ format
    public function format()
    {
        $output[] = $this->getStart()->format('Y-m-d H:i');

        if (!$this->isRunning()) {
            $output[] = '-';
            $output[] = $this->getEnd()->format('Y-m-d H:i');
        }

        $output[] = parent::format();

        return implode(' ', $output);
    }
    // }}}
}
