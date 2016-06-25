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
                'stop',
                'continue',
            ],
            'todo' => [
                'list',
                'listAll',
                'edit',
                'editAll',
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
        $this->cacheAttributes();

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

                            if ($end = $this->getLastRecordEnd()) {
                                $times[] = $end;
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
        $parser = $this->parser;

        if (
            !$parser->parseActigory($filter)
            || ($parser->isLast() && !empty($current))
        ) {
            $this->suggest($current, $this->getActigories());
        } else {
            $parser->parseTags();
            $tags = array_diff($this->getTags(), $parser->getTags());
            $this->suggest($current, $tags, '+');
        }
    }
    // }}}
    // {{{ suggest
    protected function suggest($hint, $candidates, $prefix = '')
    {
        $output = [];
        $escapedHint = preg_quote($hint);

        foreach($candidates as $candidate) {
            if (preg_match("/^$escapedHint/", $prefix . $candidate)) {
                $output[] = $prefix . $candidate;
            }
        }

        $this->output(implode(' ', $output));
    }
    // }}}

    // {{{ getLastRecordEnd
    protected function getLastRecordEnd()
    {
        $end = $this->cacheRead('end');

        if ($end === false) {
            $last = $this->prm->getLastRecord();

            if ($last) {
                $end = $last->getEnd()->format('H:i');
            }

            $this->cacheWrite('end', [$end]);
        } else {
            $end = $end[0];
        }

        return $end;
    }
    // }}}
    // {{{ getActigories
    protected function getActigories()
    {
        $actigories = $this->cacheRead('actigories');

        if ($actigories === false) {
            $actigories = $this->prm->getActigories();
            $this->cacheWrite('actigories', $actigories);
        }

        return $actigories;
    }
    // }}}
    // {{{ getTags
    protected function getTags()
    {
        $tags = $this->cacheRead('tags');

        if ($tags === false) {
            $tags = $this->prm->getTags();
            $this->cacheWrite('tags', $tags);
        }

        return $tags;
    }
    // }}}

    // {{{ cacheAttributes
    protected function cacheAttributes()
    {
        $pid = pcntl_fork();

        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
        } else {
            $this->getLastRecordEnd();
            $this->getActigories();
            $this->getTags();
            exit;
        }
    }
    // }}}
    // {{{ cacheWrite
    protected function cacheWrite($name, $data)
    {
        $file = Setup::get('storage') . '/' . $name;
        $this->fsWrite($file, implode("\n", $data));
    }
    // }}}
    // {{{ cacheRead
    protected function cacheRead($name)
    {
        $file = Setup::get('storage') . '/' . $name;

        return $this->fsRead($file, 15);
    }
    // }}}
    // {{{ fsWrite
    protected function fsWrite($path, $data)
    {
        // thanks, https://php.net/manual/en/function.file-put-contents.php#84180

        $parts = explode('/', $path);
        $file = array_pop($parts);
        $path = '';

        foreach($parts as $part) {
            if (!is_dir($path .= "/$part")) {
                mkdir($path);
            }
        }

        file_put_contents("$path/$file", $data);
    }
    // }}}
    // {{{ fsRead
    protected function fsRead($path, $ttl = null)
    {
        $result = false;

        if (
            file_exists($path)
            && (
                is_null($ttl)
                || ((time() - filemtime($path)) < $ttl)
            )
        ) {
            $result = file($path, FILE_IGNORE_NEW_LINES);
        }

        return $result;
    }
    // }}}

    // {{{ recordAdd
    protected function recordAdd()
    {
        $parser = $this->parser;
        $result = null;

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

        return $result;
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
        $this->output($this->formatTodos($this->getFilteredTodos(), true));
    }
    // }}}
    // {{{ todoListAll
    protected function todoListAll()
    {
        $this->output($this->formatTodos($this->getFilteredTodos(), false));
    }
    // }}}
    // {{{ todoEdit
    protected function todoEdit()
    {
        $this->editTodos(true);
    }
    // }}}
    // {{{ todoEditAll
    protected function todoEditAll()
    {
        $this->editTodos(false);
    }
    // }}}

    // {{{ editTodos
    protected function editTodos($hideDone)
    {
        $todos = $this->getFilteredTodos();
        $filtered = [];

        foreach ($todos as $todo) {
            if (!($hideDone && $todo->isDone())) {
                $filtered[] = $todo;
            }
        }

        $todosString = $this->formatTodos($filtered);
        $answer = null;
        $parsed = null;

        while (
            $answer !== 'y' && $answer !== 'yes'
            && $answer !== 'c' && $answer !== 'cancel'
        ) {
            $parsed = $this->editAndParse($todosString, $todos);

            if (
                !$parsed->new->count()
                && !$parsed->moved->count()
                && !$parsed->edited->count()
                && !$parsed->deleted->count()
            ) {
                $this->outputNl('no change');
                break;
            }
            $todosString = $parsed->text;
            $answer = $this->input('accept (y/N/c)');
        }

        if ($answer === 'y' || $answer === 'yes') {
            $this->saveTodos($parsed->new);
            $this->saveTodos($parsed->moved);
            $this->saveTodos($parsed->edited);
            foreach ($parsed->deleted as $todo) {
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
        $toMatch = $newTodos->count();

        $rest = new \SplObjectStorage();
        $rest->addAll($todos);

        $matched = new \stdClass();
        $matched->new = new \SplObjectStorage();
        $matched->total = new \SplObjectStorage();
        $matched->edited = new \SplObjectStorage();
        $matched->unchanged = new \SplObjectStorage();
        $matched->exact = new \SplObjectStorage();
        $matched->moved = new \SplObjectStorage();
        $matched->deleted = new \SplObjectStorage();

        while (
            ($rest->count() > 0)
            && ($matched->total->count() < $toMatch)
        ) {
            $confidence = 0;

            foreach ($newTodos as $new) {
                if (
                    $new->getMatch()
                    && ($new->getConfidence() > $confidence)
                    && $rest->contains($new->getMatch())
                ) {
                    $confidence = $new->getConfidence();
                    $bestMatch = $new;

                    if ($confidence === 100) {
                        break;
                    }
                } else {
                    $new->resetMatch();

                    foreach ($rest as $old) {
                        $new->match($old);

                        if ($new->getConfidence() > $confidence) {
                            $confidence = $new->getConfidence();
                            $bestMatch = $new;

                            if ($confidence == 100) {
                                break;
                            }
                        }
                    }
                }
            }

            $newTodos->detach($bestMatch);
            $matched->total->attach($bestMatch);

            if ($bestMatch->getConfidence() >= 80) {
                $bestMatch->setId($bestMatch->getMatch()->getId());
                $rest->detach($bestMatch->getMatch());

                if ($bestMatch->lexicalMatch() && $bestMatch->doneMatch()) {
                    $matched->unchanged->attach($bestMatch);
                } else {
                    $matched->edited->attach($bestMatch);
                }
            } else {
                $matched->new->attach($bestMatch);
            }
        }

        foreach ($matched->unchanged as $unchanged) {
            if (
                $unchanged->isDone()
                || (
                    $unchanged->getParentId() == $unchanged->getMatch()->getParentId()
                    && $unchanged->getPosition() == $unchanged->getMatch()->getPosition()
                )
            ) {
                $matched->exact->attach($unchanged);
            } else {
                $matched->moved->attach($unchanged);
            }
        }

        $matched->deleted->addAll($rest);
        $matched->new->addAll($newTodos);
        $matched->text = implode('', $newList);

        $this->outputNl(count($todos) . ' old, ' . $toMatch . ' new');
        $this->outputNl();

        foreach ($matched->moved as $todo) {
            $this->outputNl('(moved)   ' . $todo->format());
        }
        foreach ($matched->edited as $todo) {
            $this->outputNl('(edited)  ' . $todo->getMatch()->format() . ' -> ' . $todo->format());
        }
        foreach ($matched->deleted as $todo) {
            $this->outputNl('(deleted) ' . $todo->format());
        }
        foreach ($matched->new as $todo) {
            $this->outputNl('(new)     ' . $todo->format());
        }

        return $matched;
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
        $positions = [0];
        $last = null;
        $activity = $this->parser->getActivity();
        $category = $this->parser->getCategory();
        $tags = $this->parser->getTags();

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
                    $positions[$level] = 0;
                } else if (count($parents) > $level + 1) {
                    $parents = array_slice($parents, 0, $level + 1);
                }

                $todo = $this->stringToTodo($activity, $category, $tags, $parents[$level], $positions[$level], $todoString);

                if (!$todo->isDone()) {
                    $positions[$level]++;
                }

                $newTodos->attach($todo);
                $last = $todo;
            } else if ($lastHeader) {
                $activity = $lastHeader->getActivity();
                $category = $lastHeader->getCategory();
                $tags = $lastHeader->getTags();
                $parents = [null];
                $positions = [0];
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
        $temp = tempnam(Setup::get('storage') . '/', 'MrClip');
        $this->fsWrite($temp, $string);

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
    protected function stringToTodo($activity, $category, $tags, $parent, $position, $todoString)
    {
        $todoArray = explode(' ', trim($todoString));
        $parser = new Parser($todoArray);

        $done = $parser->parseDone();
        $filterTags = $this->parser->getTags();
        $tags = array_unique(array_merge($tags, $filterTags));
        $text = trim($parser->parseText());

        $todo = new Todo(null, $activity, $category, $tags, $text, $parent, $position, $done);

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
            $todo = new Todo(null, $parser->getActivity(), $parser->getCategory(), $parser->parseTags());
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
    // {{{ saveTodos
    protected function saveTodos($todos)
    {
        foreach ($todos as $todo) {
            if ($todo->isDone()) {
                $parent = ($todo->getMatch()) ? $todo->getMatch()->getParent() : null;
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
        $top = [];
        $list = [];

        foreach ($todos as $todo) {
            if (is_null($todo->getParent())) {
                if (!isset($top[$todo->formatBase()])) {
                    $top[$todo->formatBase()] = new Todo(null, $todo->getActivity(), $todo->getCategory(), $todo->getTags());
                }

                $top[$todo->formatBase()]->addChild($todo);
            }
        }

        foreach ($top as $group => $head) {
           $list = array_merge($list, $this->todoTree($head, -1));
        }

        return implode("\n", $list);
    }
    // }}}
    // {{{ posSort
    protected function posSort($todos)
    {
        usort(
            $todos,
            function($a, $b) {
                return $a->getPosition() - $b->getPosition();
            }
        );

        return $todos;
    }
    // }}}
    // {{{ todoTree
    protected function todoTree($todo, $level)
    {
        if ($level < 0) {
            $list[] = $todo->formatBase();
            $list[] = '';
        } else {
            $list[] = str_repeat('    ', $level) . $todo->formatText();
        }

        $children = $this->posSort($todo->getChildren());

        foreach ($children as $child) {
            $list = array_merge($list, $this->todoTree($child, $level + 1));
        }

        if ($level < 0) {
            $list[] = '';
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
