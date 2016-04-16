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
        $formatted = $this->formatTodos($todos);
        $newList = $this->userEditString($formatted);

        $newTodos = new \SplObjectStorage();
        $rest = new \SplObjectStorage();
        $rest->addAll($todos);
        $exact = new \SplObjectStorage();

        $parents = [null];
        $last = null;

        foreach($newList as $todoString) {
            $newTodo = $this->stringToTodo($todoString);
            $level = $this->parseLevel($todoString);

            if (count($parents) < $level + 1) {
                array_push($parents, $last);
            } else if (count($parents) > $level + 1) {
                array_pop($parents);
            }

            $last = $newTodo;
            $newTodo->parent = $parents[$level];
            $candidates = $this->matchTodo($newTodo, $rest);

            if (
                isset($candidates[0][0])
                && $candidates[0][0] == 100
            ) {
                $exact->attach($newTodo);
                $newTodo->guess = $candidates[0][1];
                $rest->detach($candidates[0][1]);
                $newTodo->id = $candidates[0][1]->id;
            } else {
                $newTodos->attach($newTodo);
            }
        }

        $guess = new \SplObjectStorage();
        $new = new \SplObjectStorage();

        foreach($newTodos as $newTodo) {
            $candidates = $this->matchTodo($newTodo, $rest);

            if (
                isset($candidates[0][0])
                && $candidates[0][0] > 80
            ) {
                $guess->attach($newTodo);
                $newTodo->guess = $candidates[0][1];
                $rest->detach($candidates[0][1]);
                $newTodo->id = $candidates[0][1]->id;
            } else {
                $new->attach($newTodo);
            }
        }

        $exactWithParent = new \SplObjectStorage();
        $exactMoved = new \SplObjectStorage();

        foreach($exact as $todo) {
            if (
                (is_null($todo->guess->parentId) && is_null($todo->parent))
                || ($todo->parent->id === $todo->guess->parentId)
            ) {
                $exactWithParent->attach($todo);
            } else {
                $exactMoved->attach($todo);
            }
        }

        echo count($todos) . ' old, ' . count($newList) . " new\n\n";
        foreach ($exactMoved as $todo) {
            echo '(moved)   ' . $this->formatTodo($todo) . "\n";
        }
        foreach ($guess as $todo) {
            echo '(edited) ' . $this->formatTodo($todo->guess) . ' -> ' . $this->formatTodo($todo) . "\n";
        }
        foreach ($rest as $todo) {
            echo '(deleted) ' . $this->formatTodo($todo) . "\n";
        }
        foreach ($new as $todo) {
            echo '(new)     ' . $this->formatTodo($todo) . "\n";
        }

        $answer = readline('accept (y/N)');
        if ($answer === 'y') {
            $this->saveTodos($new);
            $this->saveTodos($exact);
            $this->saveTodos($guess);
            foreach ($rest as $todo) {
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
            $todos->attach($todo);
        }

        return $todos;
    }
    // }}}
    // {{{ matchTodo
    protected function matchTodo($needle, $haystack)
    {
        $confidences = [];
        $candidates = [];

        foreach($haystack as $candidate) {
            $confidence = 0;

            if ($needle->activity == $candidate->activity) $confidence += 10;
            if ($needle->category == $candidate->category) $confidence += 10;

            $diffs = count(array_diff($needle->tags, $candidate->tags)) + count(array_diff($candidate->tags, $needle->tags));
            $tagConfidence = 30 - 10 * $diffs;
            if ($tagConfidence > 0) $confidence += $tagConfidence;

            $textConfidence = 50 - abs(strcmp($needle->text, $candidate->text));
            $confidence += $textConfidence;

            $confidences[] = $confidence;
            $candidates[] = $candidate;
        }

        array_multisort($confidences, SORT_DESC, $candidates);

        $result = [];
        foreach ($candidates as $key => $candidate) {
            $result[] = [$confidences[$key], $candidate];
        }

        return $result;
    }
    // }}}
    // {{{ saveTodos
    protected function saveTodos($todos)
    {
        foreach ($todos as $todo) {
            $parentId = ($todo->parent) ? $todo->parent->id : null;

            $result = $this->getPrm()->editTodo(
                $todo->id,
                $todo->activity,
                $todo->category,
                $todo->tags,
                $todo->text,
                $parentId
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
    // {{{ formatTodo
    protected function formatTodo($todo, $hideActivity = false, $hideCategory = false, $hiddenTags = [])
    {
        $activity = ($hideActivity) ? '' : $todo->activity;
        $category = ($hideCategory) ? '' : $todo->category;
        $tags = array_diff($todo->tags, $hiddenTags);
        $text = $todo->text;

        return $this->formatAttributes($activity, $category, $tags, $text);
    }
    // }}}
    // {{{ formatTodos
    protected function formatTodos($todos)
    {
        $numbered = [];

        foreach ($todos as $todo) {
            $todo->children = [];
            $numbered[$todo->id] = $todo;
        }

        foreach ($numbered as $todo) {
            if (!is_null($todo->parentId) && array_key_exists($todo->parentId, $numbered)) {
                $numbered[$todo->parentId]->children[] = $todo;
            }
        }

        $list = '';
        foreach ($numbered as $todo) {
            if (is_null($todo->parentId)) {
                $list .= $this->todoTree($todo, 0);
            }
        }

        return $list;
    }
    // }}}
    // {{{ formatAttributes
    protected function formatAttributes($activity, $category, $tags, $text)
    {
        $actigory = '';

        if ($activity)              $actigory .= $activity;
        if ($activity || $category) $actigory .= '@';
        if ($category)              $actigory .= $category;

        if ($actigory)      $output[] = $actigory;
        if (!empty($tags))  $output[] = implode(' ', $this->formatTags($tags));
        if ($text)          $output[] = $text;

        return implode(' ', $output);
    }
    // }}}
    // {{{ formatTags
    protected function formatTags($tags)
    {
        $formatted = [];

        foreach($tags as $tag) {
            $formatted[] = "+$tag";
        }

        return $formatted;
    }
    // }}}
    // {{{ todoTree
    protected function todoTree($todo, $level)
    {
        $parser = $this->parser;
        $hideActivity = (bool) $parser->getActivity();
        $hideCategory = (bool) $parser->getCategory();
        $hiddenTags = $parser->getTags();

        $string = str_repeat('    ', $level) . $this->formatTodo($todo, $hideActivity, $hideCategory, $hiddenTags) . "\n";

        foreach ($todo->children as $child) {
            $string .= $this->todoTree($child, $level + 1);
        }

        return $string;
    }
    // }}}

    // {{{ stringToTodo
    protected function stringToTodo($todoString)
    {
        $todoArray = explode(' ', trim($todoString));
        $parser = new Parser('todo', $todoArray);
        $todo = new \stdclass();

        if ($this->parser->getActivity()) {
            $todo->activity = $this->parser->getActivity();
        } else {
            $parser->parseActigory();
            $todo->activity = $parser->getActivity();
        }

        if ($this->parser->getCategory()) {
            $todo->category = $this->parser->getCategory();
        } else if ($parser->getCategory()) {
            $todo->category = $parser->getCategory();
        } else {
            $todo->activity = $parser->parseActigory();
            $todo->category = $parser->getCategory();
        }

        $parser->parseTags();
        $listTags = $parser->getTags();

        $optionTags = $this->parser->getTags();
        $todo->tags = array_unique(array_merge($listTags, $optionTags));
        $todo->text = trim($parser->parseText());
        $todo->id = null;

        return $todo;
    }
    // }}}
}
