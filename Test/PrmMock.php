<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\Record;

class PrmMock
{
    // {{{ variables
    public $records;
    // }}}
    // {{{ constructor
    public function __construct()
    {
        $this->records = [];

        $start1 = new \Datetime('2015-10-21 16:29');
        $end1 = new \Datetime('2015-10-21 16:34');
        $this->records[] = new Record(1, 'activity1', 'category1', ['tag1', 'tag2'], null, $start1, $end1);

        $start2 = new \Datetime('2015-10-21 17:00');
        $end2 = new \Datetime('2015-10-21 18:00');
        $this->records[] = new Record(2, 'activity2', 'category1', ['tag2'], 'someMemo', $start2, $end2);

        $start3 = new \Datetime('2015-10-21 19:00');
        $end3 = new \Datetime('2015-10-21 20:00');
        $this->records[] = new Record(3, 'activity1', 'category2', [], null, $start3, $end3);

        $start4 = new \Datetime('2015-10-21 21:00');
        $end4 = new \Datetime('2015-10-21 22:00');
        $this->records[] = new Record(4, 'activity2', 'category1', ['tag2'], null, $start4, $end4);
    }
    // }}}

    // {{{ getLastRecord
    public function getLastRecord()
    {
        $last = null;

        foreach ($this->records as $record) {
            if (
                is_null($last)
                || $last->getEnd()->getTimestamp() < $record->getEnd()->getTimestamp()
            ) {
                $last = $record;
            }
        }

        return $last;
    }
    // }}}
    // {{{ getActivities
    public function getActivities()
    {
        $activities = [];

        foreach($this->records as $record) {
            if (!in_array($record->getActivity(), $activities)) {
                $activities[] = $record->getActivity();
            }
        }

        return $activities;
    }
    // }}}
    // {{{ getCategories
    public function getCategories()
    {
        $categories = [];

        foreach($this->records as $record) {
            if (!in_array($record->getCategory(), $categories)) {
                $categories[] = $record->getCategory();
            }
        }

        return $categories;
    }
    // }}}
    // {{{ getActigories
    public function getActigories()
    {
        $activities = $this->getActivities();
        $activities[] = ''; // categories without activities
        $categories = $this->getCategories();

        foreach($activities as $activity) {
            foreach($categories as $category) {
                $actigories[] = "$activity@$category";
            }
        }

        return $actigories;
    }
    // }}}
}
