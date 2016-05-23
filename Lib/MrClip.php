<?php

namespace Uglybob\MrClip\Lib;

class MrClip
{
    // {{{ variables
    protected $prm;
    protected $parser;
    // }}}
    // {{{ constructor
    public function __construct($options = [])
    {
        $this->commands = [
            'record' => [
                'add',
                'current',
                'list',
                'stop',
                'continue',
            ],
            'todo' => [
                'add',
                'list',
                'edit',
            ],
            'completion' => null,
        ];

        $this->prm = new PRM();

        $this->run($options);
    }
    // }}}
    // {{{ run
    protected function run($options = [])
    {
        $this->parser = new Parser($options, $this->commands);

        if ($domain = $this->parser->parseDomain()) {
            if ($domain == 'completion') {
                unset($this->commands['completion']);
                $this->completion($options);
            } else {
                if (
                    array_key_exists($domain, $this->commands)
                    && $this->parser->parseCommand()
                    && in_array($this->parser->getCommand(), $this->commands[$domain])
                ) {
                    $call = $domain . ucfirst($this->parser->getCommand());
                    $this->$call();
                }
            }
        }
    }
    // }}}

    // {{{ completion
    protected function completion($options)
    {
        $parser = $this->parser;
        $current = substr(end($options), 1, -1);

        if ($parser->parseDomain()) {
            if ($parser->getDomain() == 'record') {
                if ($parser->parseCommand()) {
                    if ($parser->getCommand() == 'add') {
                        if ($parser->parseStart()) {
                            $parser->parseEnd();
                            $this->completionActigoryTags($current);
                        } else {
                            $times = [date('H:i')];

                            if (
                                ($last = $this->prm->getLastRecord())
                                && ($end = $last->getEnd())
                            ) {
                                $times[] = $end->format('H:i');
                            }

                            $this->suggest($current, $times);
                        }
                    }
                } else {
                    $this->suggest($current, $this->commands[$parser->getDomain()]);
                }
            } else if ($parser->getDomain() == 'todo') {
                if ($parser->parseCommand()) {
                    if (
                        $parser->getCommand() == 'list'
                        || $parser->getCommand() == 'edit'
                    ) {
                        $this->completionActigoryTags($current, true);
                    }
                } else {
                    $this->suggest($current, $this->commands[$parser->getDomain()]);
                }
            }
        } else {
            $this->suggest($current, array_keys($this->commands));
        }
    }
    // }}}
    // {{{ completionActigoryTags
    protected function completionActigoryTags($current, $filter = false)
    {
        if ($this->parser->parseActigory($filter)) {
            $this->parser->parseTags();
            $tags = array_diff($this->prm->getTags(), $this->parser->getTags());
            $this->suggest($current, $tags, '+');
        } else {
            $this->suggest($current, $this->prm->getActigories());
        }
    }
    // }}}
    // {{{ suggest
    protected function suggest($hint, $candidates, $prefix = '')
    {
        $output = [];

        foreach($candidates as $candidate) {
            $escapedHint = preg_quote($hint);

            if (preg_match("/^$escapedHint/", $prefix . $candidate)) {
                $output[] = $prefix . $candidate;
            }
        }

        $this->output(implode(' ', $output));
    }
    // }}}

    // {{{ recordAdd
    protected function recordAdd()
    {
        $parser = $this->parser;

        $start = $parser->parseStart();
        $end = $parser->parseEnd();
        $parser->parseActigory();
        $activity = $parser->getActivity();
        $category = $parser->getCategory();
        $tags = $parser->parseTags();
        $text = $parser->parseText();

        if (empty(trim($activity)) || empty(trim($category))) {
            $this->outputNl('Activity/category missing');
        } else {
            $record = new Record(
                null,
                $activity,
                $category,
                $tags,
                $text,
                $start,
                $end
            );

            $result = $this->prm->saveRecord($record);

            if ($result) {
                $this->outputNl('(added) ' . $result->format());
            } else {
                $this->outputNl('Failed to add record');
            }
        }
    }
    // }}}
    // {{{ recordCurrent
    protected function recordCurrent()
    {
        if ($current = $this->prm->getCurrentRecord()) {
            $this->outputNl('(running) ' . $current->format());
        } else {
            if ($last = $this->prm->getLastRecord()) {
                $this->outputNl('(last) ' . $last->format());
            } else {
                $this->outputNl('Failed to fetch record');
            }
        }
    }
    // }}}
    // {{{ recordStop
    protected function recordStop()
    {
        $stopped = $this->prm->stopRecord();

        if ($stopped) {
            $this->outputNl('(stopped) ' . $stopped->format());
        } else {
            $this->outputNl('Failed to stop record');
        }
    }
    // }}}
    // {{{ recordContinue
    protected function recordContinue()
    {
        $last = $this->prm->getLastRecord();

        if ($last) {
            $last->setId(null);
            $last->setStart(new \Datetime());
            $last->setEnd(null);

            $result = $this->prm->saveRecord($last);

            if ($result) {
                $this->outputNl('(added) ' . $result->format());
            } else {
                $this->outputNl('Failed to add record');
            }
        } else {
            $this->outputNl('No previous record to continue');
        }
    }
    // }}}

    // {{{ todoList
    protected function todoList()
    {
        $this->output($this->formatTodos($this->getFilteredTodos()));
    }
    // }}}
    // {{{ todoEdit
    protected function todoEdit()
    {
        $todos = $this->getFilteredTodos();
        $todosString = $this->formatTodos($todos);
        $answer = null;

        while (
            $answer !== 'y' && $answer !== 'yes'
            && $answer !== 'c' && $answer !== 'cancel'
        ) {
            $parsed = $this->editAndParse($todosString, $todos);
            $todosString = $parsed->text;
            $answer = $this->input('accept (y/N/c)');
        }

        if ($answer === 'y' || $answer === 'yes') {
            $this->saveTodos($parsed->new);
            $this->saveTodos($parsed->moved);
            $this->saveTodos($parsed->edited);
            foreach ($parsed->delete as $todo) {
                $this->prm->deleteTodo($todo);
            }
        }
    }
    // }}}

    // {{{ editAndParse
    protected function editAndParse($string, $todos)
    {
        $newList = $this->userEditString($string);
        $newTodos = $this->parseTodoList($newList);

        $exact = new \SplObjectStorage();
        $unsure = new \SplObjectStorage();
        $guess = new \SplObjectStorage();
        $new = new \SplObjectStorage();
        $moved = new \SplObjectStorage();

        $rest = $this->matchTodos($newTodos, $todos, $exact, $unsure, 100);
        $rest = $this->matchTodos($unsure, $rest, $guess, $new, 80);

        foreach ($exact as $todo) {
            if (
                !$todo->isDone()
                && ($todo->getParentId() != $todo->getGuess()->getParentId())
            ) {
                $moved->attach($todo);
            }
        }

        $this->outputNl(count($todos) . ' old, ' . count($newTodos) . ' new');
        $this->outputNl();

        foreach ($moved as $todo) {
            $this->outputNl('(moved)   ' . $todo->format());
        }
        foreach ($guess as $todo) {
            $this->outputNl('(edited) ' . $todo->getGuess()->format() . ' -> ' . $todo->format());
        }
        foreach ($rest as $todo) {
            $this->outputNl('(deleted) ' . $todo->format());
        }
        foreach ($new as $todo) {
            $this->outputNl('(new)     ' . $todo->format());
        }

        $parsed = new \stdclass();
        $parsed->new = $new;
        $parsed->moved = $moved;
        $parsed->edited = $guess;
        $parsed->delete = $rest;
        $parsed->text = implode('', $newList);

        return $parsed;
    }
    // }}}
    // {{{ parseLevel
    protected function parseLevel($string)
    {
        preg_match('/^[ ]*/', $string, $matches);

        return strlen($matches[0]) / 4;
    }
    // }}}
    // {{{ parseTodoList
    protected function parseTodoList($list)
    {
        $newTodos = new \SplObjectStorage();
        $lastHeader = null;
        $parents = [null];
        $last = null;
        $activity = $this->parser->getActivity();
        $category = $this->parser->getCategory();

        foreach ($list as $todoString) {
            $header = $this->stringToHeader($todoString);

            if (
                !empty(trim($todoString))
                && !$header
                && $activity && $category
            ) {
                $level = $this->parseLevel($todoString);
                if (count($parents) < $level + 1) {
                    array_push($parents, $last);
                } else if (count($parents) > $level + 1) {
                    array_pop($parents);
                }

                $todo = $this->stringToTodo($activity, $category, $parents[$level], $todoString);

                $newTodos->attach($todo);
                $last = $todo;
            } else if ($lastHeader) {
                $activity = $lastHeader->getActivity();
                $category = $lastHeader->getCategory();
                $parents = [null];
                $last = null;
            }

            $lastHeader = $header;
        }

        return $newTodos;
    }
    // }}}
    // {{{ userEditString
    protected function userEditString($string)
    {
        $temp = tempnam(sys_get_temp_dir(), 'MrClip');
        file_put_contents($temp, $string);

        $pipes = array();

        $editRes = proc_open(
            Setup::get('editor') . ' ' . $temp,
            [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ],
            $pipes
        );

        proc_close($editRes);

        $newString = file($temp);
        unlink($temp);

        return $newString;
    }
    // }}}
    // {{{ stringToTodo
    protected function stringToTodo($activity, $category, $parent, $todoString)
    {
        $todoArray = explode(' ', trim($todoString));
        $parser = new Parser($todoArray);

        $done = $parser->parseDone();
        $listTags = $parser->parseTags();
        $filterTags = $this->parser->getTags();
        $tags = array_unique(array_merge($listTags, $filterTags));
        $text = trim($parser->parseText());

        $todo = new Todo(null, $activity, $category, $tags, $text, $parent, $done);

        return $todo;
    }
    // }}}
    // {{{ stringToHeader
    protected function stringToHeader($headerString)
    {
        $todoArray = explode(' ', trim($headerString));
        $parser = new Parser($todoArray);
        $todo = null;

        if ($parser->parseActigory()) {
            $todo = new Todo(null, $parser->getActivity(), $parser->getCategory());
        }

        return $todo;
    }
    // }}}

    // {{{ getFilteredTodos
    protected function getFilteredTodos()
    {
        $parser = $this->parser;

        $parser->parseActigory(true);
        $parser->parseTags();

        return $this->prm->getTodos($parser->getActivity(), $parser->getCategory(), $parser->getTags());
    }
    // }}}
    // {{{ matchTodos
    protected function matchTodos($todos, $candidates, $above, $under, $threshold)
    {
        $rest = new \SplObjectStorage();
        $rest->addAll($candidates);

        foreach ($todos as $todo) {
            foreach($rest as $candidate) {
                $todo->match($candidate);
            }

            if ($todo->getConfidence() >= $threshold) {
                $above->attach($todo);
                $todo->setId($todo->getGuess()->getId());
                $rest->detach($todo->getGuess());
            } else {
                $under->attach($todo);
            }
        }

        return $rest;
    }
    // }}}
    // {{{ saveTodos
    protected function saveTodos($todos)
    {
        foreach ($todos as $todo) {
            if ($todo->isDone()) {
                $parent = ($todo->getGuess()) ? $todo->getGuess()->getParent() : null;
                $todo->setParent($parent);
            }

            $result = $this->prm->saveTodo($todo);

            foreach($todo->getChildren() as $child) {
                $child->setParent($result);
            }
        }
    }
    // }}}

    // {{{ formatTodos
    protected function formatTodos($todos)
    {
        $tagFilter = $this->parser->getTags();
        $sorted = [];
        $list = [];

        foreach ($todos as $todo) {
            $sorted[$todo->getActigory()][] = $todo;
        }

        foreach ($sorted as $actigory => $todos) {
            $list[] = trim($actigory . ' ' . Todo::formatTags($tagFilter));
            $open = [];
            $done = [];

            foreach ($todos as $todo) {
                if ($todo->isDone()) {
                    $done[] = $todo;
                } else {
                    $open[] = $todo;
                }
            }

            if (!empty($open)) {
                $list[] = '';
            }

            foreach ($open as $todo) {
                if (is_null($todo->getParent())) {
                    $list = array_merge($list, $this->todoTree($todo, $tagFilter, 0));
                }
            }

            if (!empty($done)) {
                $list[] = '';
            }

            foreach ($done as $todo) {
                $list[] = $todo->formatTagsText($tagFilter);
            }

            $list[] = '';
        }

        return implode("\n", $list);
    }
    // }}}
    // {{{ todoTree
    protected function todoTree($todo, $tagFilter, $level)
    {
        $list = [];

        if (!$todo->isDone()) {
            $list[] = str_repeat('    ', $level) . $todo->formatTagsText($tagFilter);

            foreach ($todo->getChildren() as $child) {
                $list = array_merge($list, $this->todoTree($child, $tagFilter, $level + 1));
            }
        }

        return $list;
    }
    // }}}

    // {{{ output
    protected function output($string = '')
    {
        Cli::output($string);
    }
    // }}}
    // {{{ outputNl
    protected function outputNl($string = '')
    {
        $this->output($string . "\n");
    }
    // }}}
    // {{{ input
    protected function input($string = '')
    {
        return Cli::input($string);
    }
    // }}}
}
