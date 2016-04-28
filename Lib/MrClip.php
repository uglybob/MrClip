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

    // {{{ getPrm
    protected function getPrm()
    {
        if (!$this->prm) {
            $soapOptions = [
                'location' => Setup::get('url') . '/soap.php',
                'uri' => 'http://localhost/',
            ];

            $this->prm = new \SoapClient(null, $soapOptions);
            $this->prm->login(Setup::get('user'), Setup::get('pass'));
        }

        return $this->prm;
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

                            if ($last = $this->getPrm()->getLastRecord()) {
                                $times[] = date('H:i', $last->end);
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
            $tags = array_diff($this->getPrm()->getTags(), $parser->getTags());
            $this->suggest($current, $tags, '+');
        } else {
            $activities = $this->getPrm()->getActivities();
            $activities[] = ''; // categories without activities
            $categories = $this->getPrm()->getCategories();

            foreach($activities as $activity) {
                foreach($categories as $category) {
                    $actigories[] = "$activity@$category";
                }
            }

            $this->suggest($current, $actigories);
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

                $record = $this->getPrm()->editRecord(
                    null,
                    $parser->getStart(),
                    $parser->getEnd(),
                    $parser->getActivity(),
                    $parser->getCategory(),
                    $parser->getTags(),
                    $parser->getText()
                );

                if ($record) {
                    echo '(added) ' . $this->formatRecord($record) . "\n";
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
        if ($record = $this->getPrm()->getCurrentRecord()) {
            echo '(running) ' . $this->formatRecord($record) . "\n";
        } else {
            if ($last = $this->getPrm()->getLastRecord()) {
                echo '(last) ' . $this->formatRecord($last) . "\n";
            } else {
                echo "Failed to fetch record\n";
            }
        }
    }
    // }}}
    // {{{ recordStop
    protected function recordStop()
    {
        $stopped = $this->getPrm()->stopRecord();

        if ($stopped) {
            echo '(stopped) ' . $this->formatRecord($stopped) . "\n";
        } else {
            echo "Failed to stop record\n";
        }
    }
    // }}}
    // {{{ recordContinue
    protected function recordContinue()
    {
        $last = $this->getPrm()->getLastRecord();

        if ($last) {
            $record = $this->getPrm()->editRecord(
                null,
                time(),
                null,
                $last->activity,
                $last->category,
                $last->tags,
                $last->text
            );

            if ($record) {
                echo '(added) ' . $this->formatRecord($record) . "\n";
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
            if (
                ($todo->isDone())
                || (is_null($todo->getGuess()->getParentId()) && is_null($todo->getParent()))
                || ($todo->getParent()->getId() === $todo->getGuess()->getParentId())
            ) {
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

        $todoArray = $this->getPrm()->getTodos($parser->getActivity(), $parser->getCategory(), $parser->getTags());

        $todos = new \SplObjectStorage();

        foreach($todoArray as $todo) {
            $newTodo = new Todo(
                $todo->id,
                $todo->activity,
                $todo->category,
                $todo->tags,
                $todo->text,
                $todo->parentId,
                $todo->done
            );

            $numbered[$todo->id] = $newTodo;
            $todos->attach($newTodo);
        }

        foreach($todos as $todo) {
            $id = $todo->getParentId();

            if ($id) {
                $todo->setParent($numbered[$id]);
                $numbered[$id]->addChild($todo);
            }
        }

        return $todos;
    }
    // }}}
    // {{{ matchTodos
    protected function matchTodos($todos, $candidates, &$above, &$under, $threshold)
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
            if ($todo->done) {
                $parentId = ($todo->guess->parentId);
            } else {
                $parentId = ($todo->parent) ? $todo->parent->id : null;
            }

            $result = $this->getPrm()->editTodo(
                $todo->id,
                $todo->activity,
                $todo->category,
                $todo->tags,
                $todo->text,
                $parentId,
                $todo->done
            );
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

    // {{{ formatRecord
    protected function formatRecord($record)
    {
        $output[] = date('Y-m-d H:i', $record->start);

        if ($record->end) {
            $output[] = '- ' . date('Y-m-d H:i', $record->end);
        }

        $output[] = $this->formatAttributes($record->activity, $record->category, $record->tags, $record->text);

        return implode(' ', $output);
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
            $headerTodo = new Todo(null, reset($todos)->getActivity(), reset($todos)->getCategory(), $hiddenTags);
            $list .= $headerTodo->format() . "\n\n";

            $numbered = [];
            $doneBreak = false;

            foreach ($todos as $todo) {
                if ($todo->isDone()) {
                    $list .= $todo->formatFiltered($hiddenTags) . "\n";
                    $doneBreak = true;
                } else {
                    $undone[] = $todo;
                }
            }

            if ($doneBreak) {
                $list .= "\n";
            }

            foreach ($undone as $todo) {
                if (is_null($todo->getParentId())) {
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

        if (!$todo->isDone()) {
            $hiddenTags = $this->parser->getTags();

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

        $todo = new Todo(null, $activity, $category, $tags, $text, null, $done);

        if ($parent) {
            $todo->setParent($parent);
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
