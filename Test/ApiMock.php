<?php

namespace Uglybob\MrClip\Test;

class ApiMock
{
    // {{{ variables
    public $records;
    // }}}
    // {{{ constructor
    public function __construct()
    {
        $this->records = [];

        $record1 = new \StdClass();
        $record1->id = 1;
        $record1->activity = 'activity1';
        $record1->category = 'category1';
        $record1->tags = ['tag1', 'tag2'];
        $record1->text = null;
        $record1->start = strtotime('2015-10-21 16:29');
        $record1->end = strtotime('2015-10-21 16:34');

        $record2 = new \StdClass();
        $record2->id = 2;
        $record2->activity = 'activity2';
        $record2->category = 'category1';
        $record2->tags = ['tag2'];
        $record2->text = 'someMemo';
        $record2->start = strtotime('2015-10-21 17:00');
        $record2->end = strtotime('2015-10-21 18:00');

        $record3 = new \StdClass();
        $record3->id = 3;
        $record3->activity = 'activity1';
        $record3->category = 'category2';
        $record3->tags = [];
        $record3->text = null;
        $record3->start = strtotime('2015-10-21 19:00');
        $record3->end = strtotime('2015-10-21 20:00');

        $record4 = new \StdClass();
        $record4->id = 4;
        $record4->activity = 'activity2';
        $record4->category = 'category1';
        $record4->tags = ['tag2'];
        $record4->text = null;
        $record4->start = strtotime('2015-10-21 21:00');
        $record4->end = strtotime('2015-10-21 22:00');

        $this->records = [$record1, $record2, $record3, $record4];
    }
    // }}}

    // {{{ getActivities
    public function getActivities()
    {
        $activities = [];

        foreach($this->records as $record) {
            if (!in_array($record->activity, $activities)) {
                $activities[] = $record->activity;
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
            if (!in_array($record->category, $categories)) {
                $categories[] = $record->category;
            }
        }

        return $categories;
    }
    // }}}
    // {{{ getTags
    public function getTags()
    {
        $tags = [];

        foreach($this->records as $record) {
            foreach($record->tags as $tag) {
                if (!in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }

        return $tags;
    }
    // }}}

    // {{{ getLastRecord
    public function getLastRecord()
    {
        $last = null;

        foreach ($this->records as $record) {
            if (
                is_null($last)
                || $last->end < $record->end
            ) {
                $last = $record;
            }
        }

        return $last;
    }
    // }}}
}
