<?php

namespace Uglybob\MrClip\Lib;

class PRM
{
    // {{{ variables
    protected $connection = null;
    // }}}

    // {{{ getConnection
    protected function getConnection()
    {
        if (!$this->connection) {
            $soapOptions = [
                'location' => Setup::get('url') . '/soap.php',
                'uri' => 'http://localhost/',
            ];

            $this->connection = new \SoapClient(null, $soapOptions);
            $this->connection->login(Setup::get('user'), Setup::get('pass'));
        }

        return $this->connection;
    }
    // }}}

    // {{{ getActigories
    public function getActigories()
    {
        $activities = $this->getConnection()->getActivities();
        $activities[] = ''; // categories without activities
        $categories = $this->getConnection()->getCategories();

        foreach($activities as $activity) {
            foreach($categories as $category) {
                $actigories[] = "$activity@$category";
            }
        }

        return $actigories;
    }
    // }}}
    // {{{ getTags
    public function getTags()
    {
        return $this->getConnection()->getTags();
    }
    // }}}
    // {{{ getLastRecord
    public function getLastRecord()
    {
        return $this->castRecord($this->getConnection()->getLastRecord());
    }
    // }}}
    // {{{ getCurrentRecord
    public function getCurrentRecord()
    {
        return $this->castRecord($this->getConnection()->getCurrentRecord());
    }
    // }}}
    // {{{ getTodos
    public function getTodos($activity, $category, $tags)
    {
        $objArray = $this->getConnection()->getTodos($activity, $category, $tags);
        $todos = new \SplObjectStorage();
        $numbered = [];

        foreach($objArray as $obj) {
            $todo = $this->castTodo($obj);

            $numbered[$todo->getId()] = $todo;
            $todos->attach($todo);
        }

        foreach($todos as $todo) {
            $id = $todo->getParentId();

            if ($id && array_key_exists($id, $numbered)) {
                $todo->setParent($numbered[$id]);
                $numbered[$id]->addChild($todo);
            }
        }

        return $todos;
    }
    // }}}

    // {{{ deleteTodo
    public function deleteTodo(Todo $todo)
    {
        return $this->getConnection()->deleteTodo($todo->getId());
    }
    // }}}
    // {{{ saveTodo
    public function saveTodo(Todo $todo)
    {
        $result = $this->getConnection()->editTodo(
            $todo->getId(),
            $todo->getActivity(),
            $todo->getCategory(),
            $todo->getTags(),
            $todo->getText(),
            $todo->getParentId(),
            $todo->isDone()
        );

        return $this->castTodo($result);
    }
    // }}}
    // {{{ saveRecord
    public function saveRecord(Record $record)
    {
        $end = ($record->getEnd()) ? $record->getEnd()->getTimestamp() : null;

        $result = $this->getConnection()->editRecord(
            $record->getId(),
            $record->getActivity(),
            $record->getCategory(),
            $record->getTags(),
            $record->getText(),
            $record->getStart()->getTimestamp(),
            $end
        );

        return $this->castRecord($result);
    }
    // }}}
    // {{{ stopRecord
    public function stopRecord()
    {
        $result = $this->getConnection()->stopRecord();

        return $this->castRecord($result);
    }
    // }}}

    // {{{ castRecord
    protected function castRecord($obj)
    {
        $record = null;

        if ($obj) {
            $record = new Record(
                $obj->id,
                $obj->activity,
                $obj->category,
                $obj->tags,
                $obj->text,
                $obj->start,
                $obj->end
            );
        }

        return $record;
    }
    // }}}
    // {{{ castTodo
    protected function castTodo($obj)
    {
        $todo = null;

        if ($obj) {
            $todo = new Todo(
                $obj->id,
                $obj->activity,
                $obj->category,
                $obj->tags,
                $obj->text,
                null,
                $obj->done
            );
            $todo->setParentId($obj->parentId);
        }

        return $todo;
    }
    // }}}
}
