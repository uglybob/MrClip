<?php

namespace Uglybob\MrClip\Test;

class ApiMock
{
    // {{{ variables
    public $records;
    public $todos;
    // }}}
    // {{{ constructor
    public function __construct()
    {
        $this->records = [];

        $this->records[1] = $this->createRecord(1, 'activity1', 'category1', ['tag1', 'tag2'],  null,       strtotime('2015-10-21 16:29'), strtotime('2015-10-21 16:34'));
        $this->records[2] = $this->createRecord(2, 'activity2', 'category1', ['tag2'],          'someMemo', strtotime('2015-10-21 17:00'), strtotime('2015-10-21 18:00'));
        $this->records[3] = $this->createRecord(3, 'activity1', 'category2', [],                null,       strtotime('2015-10-21 19:00'), strtotime('2015-10-21 20:00'));
        $this->records[4] = $this->createRecord(4, 'activity2', 'category1', ['tag2'],          null,       strtotime('2015-10-21 21:00'), strtotime('2015-10-21 22:00'));

        $this->todos = [];

        $this->todos[1]     = $this->createTodo(1,  'activity1', 'category1', ['tag1', 'tag2'],         'parent1',      null,   0,  false);
        $this->todos[2]     = $this->createTodo(2,  'activity1', 'category1', ['tag1', 'tag2'],         'child1',       1,      0,  false);
        $this->todos[3]     = $this->createTodo(3,  'activity1', 'category1', ['tag1', 'tag2'],         'child2',       1,      1,  false);
        $this->todos[4]     = $this->createTodo(4,  'activity1', 'category1', ['tag1', 'tag2'],         'subchild1',    2,      0,  false);
        $this->todos[5]     = $this->createTodo(5,  'activity1', 'category1', ['tag1', 'tag2', 'tag3'], 'extra tag',    1,      2,  false);
        $this->todos[6]     = $this->createTodo(6,  'activity1', 'category1', ['tag1', 'tag2'],         'parent2',      null,   1,  false);
        $this->todos[7]     = $this->createTodo(7,  'activity1', 'category1', ['tag1', 'tag2'],         'child3',       6,      0,  false);
        $this->todos[8]     = $this->createTodo(8,  'activity1', 'category1', ['tag1', 'tag2'],         'done',         6,      1,  true);
        $this->todos[9]     = $this->createTodo(9,  'activity2', 'category1', ['tag2'],                 'other tags',   null,   2,  false);
        $this->todos[10]    = $this->createTodo(10, 'activity2', 'category1', ['tag2'],                 'other tags',   9,      0,  false);
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
    // {{{ createTodo
    public function createTodo($id, $activity, $category, $tags, $text, $parent, $position, $done)
    {
        $array = [
            'id' => $id,
            'activity' => $activity,
            'category' => $category,
            'tags' => $tags,
            'text' => $text,
            'parentId' => $parent,
            'position' => $position,
            'done' => $done,
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

        foreach($this->todos as $todo) {
            if (!in_array($todo->activity, $activities)) {
                $activities[] = $todo->activity;
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

        foreach($this->todos as $todo) {
            if (!in_array($todo->category, $categories)) {
                $categories[] = $todo->category;
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

        foreach($this->todos as $todo) {
            foreach($todo->tags as $tag) {
                if (!in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }

        return $tags;
    }
    // }}}

    // {{{ getCurrentRecord
    public function getCurrentRecord()
    {
        $current = null;

        foreach ($this->records as $record) {
            if (is_null($record->end)) {
                $current = $record;
            }
        }

        return $current;
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
    // {{{ stopRecord
    public function stopRecord()
    {
        $stopped = null;

        foreach ($this->records as $record) {
            if (is_null($record->end)) {
                $record->end = time();
                $stopped = $record;
            }
        }

        return $stopped;
    }
    // }}}

    // {{{ getTodos
    public function getTodos($activity = null, $category = null, $tags = [], $includeDone = true)
    {
        $filtered = [];

        foreach ($this->todos as $todo) {
            if (
                (is_null($activity) || $todo->activity == $activity)
                && (is_null($category) || $todo->category == $category)
                && (empty($tags) || empty(array_diff($tags, $todo->tags)))
                && (
                    ($includeDone == $todo->done)
                    || !$todo->done
                )
            ) {
                $filtered[$todo->id] = $todo;
            }
        }

        return $filtered;
    }
    // }}}
}
