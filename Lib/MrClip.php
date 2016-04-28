<?php

namespace Uglybob\MrClip\Lib;

class MrClip
{
    // {{{ variables
    protected $prm = null;
    // }}}
    // {{{ constructor
    public function __construct($domain, $options)
    {
        $options = $this->cleanColons($options);
        $this->prm = new PRM();

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
        ];

        if ($domain == 'completion') {
            $this->completion($domain, $options);
        } else {
            $this->parser = new Parser($domain, $options, $this->commands);

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
    // }}}

    // {{{ cleanColons
    public static function cleanColons($options)
    {
        $newOptions = [];

        for ($i = 0; $i < count($options); $i++) {
            if (
                (
                    isset($options[$i - 1])
                    && $options[$i - 1] == ':'
                )
                || $options[$i] == ':'
            ) {
                $newOptions[count($newOptions) - 1] .= $options[$i];
            } else if ($options[$i] !== ':') {
                $newOptions[] = $options[$i];
            }
        }

        return $newOptions;
    }
    // }}}

    // {{{ completion
    protected function completion($domain, $options)
    {
        if (!empty(substr(array_shift($options), 1, -1))) {
            $current = array_pop($options);
        } else {
            $current = '';
        }

        $parser = new Parser($domain, $options, $this->commands);

        if ($parser->parseDomain()) {
            if ($parser->getDomain() == 'record') {
                if ($parser->parseCommand()) {
                    if ($parser->getCommand() == 'add') {
                        if ($parser->parseStart()) {
                            $parser->parseEnd();
                            $this->completionActigoryTags($parser, $current);
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
                        $this->completionActigoryTags($parser, $current, true);
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
    protected function completionActigoryTags($parser, $current, $filter = false)
    {
        if ($parser->parseActigory($filter)) {
            $parser->parseTags();
            $tags = array_diff($this->prm->getTags(), $parser->getTags());
            $this->suggest($current, $tags, '+');
        } else {
            $this->suggest($current, $this->prm->getActigories());
        }
    }
    // }}}

    // {{{ recordAdd
    protected function recordAdd()
    {
        $parser = $this->parser;

        if ($parser->parseStart()) {
            $parser->parseEnd();
            if ($parser->parseActigory()) {
                $parser->parseTags();
                $parser->parseText();

                $record = new Record(
                    null,
                    $parser->getActivity(),
                    $parser->getCategory(),
                    $parser->getTags(),
                    $parser->getText(),
                    $parser->getStart(),
                    $parser->getEnd()
                );

                $result = $this->prm->saveRecord($record);

                if ($result) {
                    echo '(added) ' . $result->format() . "\n";
                } else {
                    echo "Failed to add record";
                }
            }
        }
    }
    // }}}
    // {{{ recordCurrent
    protected function recordCurrent()
    {
        if ($current = $this->prm->getCurrentRecord()) {
            echo '(running) ' . $current->format() . "\n";
        } else {
            if ($last = $this->prm->getLastRecord()) {
                echo '(last) ' . $last->format() . "\n";
            } else {
                echo "Failed to fetch record\n";
            }
        }
    }
    // }}}
    // {{{ recordStop
    protected function recordStop()
    {
        $stopped = $this->prm->stopRecord();

        if ($stopped) {
            echo '(stopped) ' . $stopped->format() . "\n";
        } else {
            echo "Failed to stop record\n";
        }
    }
    // }}}
    // {{{ recordContinue
    protected function recordContinue()
    {
        $last = $this->prm->getLastRecord();

        if ($last) {
            $last->setId(null);
            $last->setStart(time());
            $last->setEnd(null);

            $result = $this->prm->saveRecord($last);

            if ($result) {
                echo '(added) ' . $result->format() . "\n";
            } else {
                echo "Failed to add record";
            }
        } else {
            echo "No previous record to continue";
        }
    }
    // }}}

    // {{{ todoList
    protected function todoList()
    {
        echo $this->formatTodos($this->getTodoList());
    }
    // }}}
    // {{{ todoEdit
    protected function todoEdit()
    {
        $todos = $this->getTodoList();
        $todosString = $this->formatTodos($todos);
        $answer = null;

        while (
            $answer !== 'y' && $answer !== 'yes'
            && $answer !== 'c' && $answer !== 'cancel'
        ) {
            $parsed = $this->editAndParse($todosString, $todos);
            $todosString = $parsed->text;
            $answer = readline('accept (y/N/c)');
        }

        if ($answer === 'y' || $answer === 'yes') {
            $this->saveTodos($parsed->new);
            $this->saveTodos($parsed->exact);
            $this->saveTodos($parsed->guess);
            foreach ($parsed->delete as $todo) {
                $this->getPrm()->deleteTodo($todo->id);
            }
        }
    }
    // }}}
    // {{{ parseLevel
    protected function parseLevel($string)
    {
        preg_match('/^[ ]*/', $string, $matches);

        return strlen($matches[0]) / 4;
    }
    // }}}
    // {{{ editAndParse
    protected function editAndParse($string, $todos)
    {
        $newList = $this->userEditString($string);
        $newTodos = new \SplObjectStorage();
        $lastHeader = null;
        $parents = [null];
        $last = null;

        foreach ($newList as $todoString) {
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

        $exact = new \SplObjectStorage();
        $unsure = new \SplObjectStorage();
        $guess = new \SplObjectStorage();
        $new = new \SplObjectStorage();
        $exactWithParent = new \SplObjectStorage();
        $exactMoved = new \SplObjectStorage();

        $rest = $this->matchTodos($newTodos, $todos, $exact, $unsure, 100);
        $rest = $this->matchTodos($unsure, $rest, $guess, $new, 80);

        foreach ($exact as $todo) {
            if ($todo->getParentId() === $todo->getGuess()->getParentId()) {
                $exactWithParent->attach($todo);
            } else {
                $exactMoved->attach($todo);
            }
        }

        echo count($todos) . ' old, ' . count($newTodos) . " new\n\n";
        foreach ($exactMoved as $todo) {
            echo '(moved)   ' . $todo->format() . "\n";
        }
        foreach ($guess as $todo) {
            echo '(edited) ' . $todo->getGuess()->format() . ' -> ' . $todo->format() . "\n";
        }
        foreach ($rest as $todo) {
            echo '(deleted) ' . $todo->format() . "\n";
        }
        foreach ($new as $todo) {
            echo '(new)     ' . $todo->format() . "\n";
        }

        $parsed = new \stdclass();
        $parsed->new = $new;
        $parsed->exact = $exact;
        $parsed->guess = $guess;
        $parsed->delete = $rest;
        $parsed->text = implode('', $newList);

        return $parsed;
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

    // {{{ getTodoList
    protected function getTodoList()
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
                $todo->setParent($todo->getGuess->getParent());
            }

            $result = $this->prm->saveTodo($todo);
        }
    }
    // }}}

    // {{{ echoComplete
    protected function echoComplete($hint, $candidate, $prefix = '')
    {
        $escapedHint = preg_quote($hint);

        if (preg_match("/^$escapedHint/", $prefix . $candidate)) {
            echo "$prefix$candidate ";
        }
    }
    // }}}
    // {{{ suggest
    protected function suggest($hint, $candidates, $prefix = '')
    {
        foreach($candidates as $candidate) {
            $this->echoComplete($hint, $candidate, $prefix);
        }
    }
    // }}}

    // {{{ formatTodos
    protected function formatTodos($todos)
    {
        $hiddenTags = $this->parser->getTags();
        $sorted = [];
        $list = '';

        foreach ($todos as $todo) {
            $sorted[$todo->getActigory()][] = $todo;
        }

        foreach ($sorted as $actigory => $todos) {
            $list .= trim($actigory . ' ' . Todo::formatTags($hiddenTags)) . "\n\n";
            $undone = [];

            foreach ($todos as $todo) {
                if ($todo->isDone()) {
                    $list .= $todo->formatFiltered($hiddenTags) . "\n";
                } else {
                    $undone[] = $todo;
                }
            }

            if (count($todos) > count($undone)) {
                $list .= "\n";
            }

            foreach ($undone as $todo) {
                if (is_null($todo->getParent())) {
                    $list .= $this->todoTree($todo, 0);
                }
            }

            $list .= "\n";
        }

        return $list;
    }
    // }}}
    // {{{ todoTree
    protected function todoTree($todo, $level)
    {
        $string = '';
        $hiddenTags = $this->parser->getTags();

        if (!$todo->isDone()) {
            $string = str_repeat('    ', $level) . $todo->formatFiltered($hiddenTags) . "\n";

            foreach ($todo->getChildren() as $child) {
                $string .= $this->todoTree($child, $level + 1);
            }
        }

        return $string;
    }
    // }}}

    // {{{ stringToTodo
    protected function stringToTodo($activity, $category, $parent, $todoString)
    {
        $todoArray = explode(' ', trim($todoString));
        $parser = new Parser('todo', $todoArray);

        $done = $parser->parseDone();
        $listTags = $parser->parseTags();
        $filterTags = $this->parser->getTags();
        $tags = array_unique(array_merge($listTags, $filterTags));
        $text = trim($parser->parseText());

        $todo = new Todo(null, $activity, $category, $tags, $text, $parent, $done);

        if ($parent) {
            $parent->addChild($todo);
        }

        return $todo;
    }
    // }}}
    // {{{ stringToHeader
    protected function stringToHeader($headerString)
    {
        $todoArray = explode(' ', trim($headerString));
        $parser = new Parser('todo', $todoArray);
        $todo = null;

        if ($parser->parseActigory()) {
            $todo = new Todo(null, $parser->getActivity(), $parser->getCategory());
        }

        return $todo;
    }
    // }}}
}
