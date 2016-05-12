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

        $this->records[1] = $this->createRecord(1, 'activity1', 'category1', ['tag1', 'tag2'],  null,       strtotime('2015-10-21 16:29'), strtotime('2015-10-21 16:34'));
        $this->records[2] = $this->createRecord(2, 'activity2', 'category1', ['tag2'],          'someMemo', strtotime('2015-10-21 17:00'), strtotime('2015-10-21 18:00'));
        $this->records[3] = $this->createRecord(3, 'activity1', 'category2', [],                null,       strtotime('2015-10-21 19:00'), strtotime('2015-10-21 20:00'));
        $this->records[4] = $this->createRecord(4, 'activity2', 'category1', ['tag2'],          null,       strtotime('2015-10-21 21:00'), strtotime('2015-10-21 22:00'));
    }
    // }}}
    // {{{ createRecord
    public function createRecord($id, $activity, $category, $tags, $text, $start, $end)
    {
        $array = [
            'id' => $id,
            'activity' => $activity,
            'category' => $category,
            'tags' => $tags,
            'text' => $text,
            'start' => $start,
            'end' => $end,
        ];

        return (object) $array;
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
    // {{{ editRecord
    public function editRecord($id, $activity, $category, $tags, $text, $start, $end)
    {
        $record = $this->createRecord($id, $activity, $category, $tags, $text, $start, $end);

        if (is_null($record->id)) {
            $record->id = count($this->records);
        }

        $this->records[$record->id] = $record;

        return $record;
    }
    // }}}
}
