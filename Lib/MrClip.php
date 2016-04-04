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
                echo '(added) ' . $this->formatRecord($stopped) . "\n";
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
        $todos = $this->getTodoList();
        echo $this->formatTodos($todos);
    }
    // }}}
    // {{{ todoEdit
    protected function todoEdit()
    {
        $temp = tempnam(sys_get_temp_dir(), 'MrClip');
        $list = $this->getTodoList();
        $formatted = $this->formatTodos($list);
        file_put_contents($temp, $formatted);

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

        $newList = file($temp);
        unlink($temp);

        $parents = [];

        foreach($newList as $todoString) {
            preg_match('/^[ ]*/', $todoString, $matches);
            $level = strlen($matches[0]) / 4;
            $todoArray = explode(' ', ltrim($todoString));
            $parser = new Parser('todo', $todoArray);

            if ($this->parser->getActivity()) {
                $activity = $this->parser->getActivity();
            } else {
                $parser->parseActigory();
                $activity = $parser->getActivity();
            }

            if ($this->parser->getCategory()) {
                $category = $this->parser->getCategory();
            } else if ($parser->getCategory()) {
                $category = $parser->getCategory();
            } else {
                $activity = $parser->parseActigory();
                $category = $parser->getCategory();
            }

            $parser->parseTags();
            $listTags = $parser->getTags();

            $optionTags = $this->parser->getTags();
            $tags = array_unique(array_merge($listTags, $optionTags));
            $text = trim($parser->parseText());

            $matches = $this->matchTodo($activity, $category, $tags, $text, $level, $list);


            echo "$activity@$category " . implode(' ', $this->formatTags($tags)) . " $text\n";

            foreach ($matches as $match) {
                echo $match[0] . ': ' . $match[1]->activity . 
                    '@' . $match[1]->category . ' ' . 
                    implode(' ', $this->formatTags($match[1]->tags)) . ' ' . 
                    $match[1]->text . "\n";
            }
            /*

            if (empty($parents)) {
                $parents[] = $
            }
            var_dump($level, $activity, $category, $tags, $text);

            //$this->getPrm()->editTodo();
            */
        }
    }
    // }}}

    // {{{ getTodoList
    protected function getTodoList()
    {
        $parser = $this->parser;

        $parser->parseActigory(true);
        $parser->parseTags();

        $todos = $this->getPrm()->getTodos($parser->getActivity(), $parser->getCategory(), $parser->getTags());

        return $todos;
    }
    // }}}
    // {{{ matchTodo
    protected function matchTodo($activity, $category, $tags, $text, $level, $todos)
    {
        $confidences = [];

        foreach($todos as $todo) {
            $confidence = 0;

            if ($activity == $todo->activity) $confidence += 10;
            if ($category == $todo->category) $confidence += 20;

            $diffs = count(array_diff($tags, $todo->tags)) + count(array_diff($todo->tags, $tags));
            $tagConfidence = 30 - 10 * $diffs;
            if ($tagConfidence > 0) $confidence += $tagConfidence;

            $textConfidence = 40 - abs(strcmp($text, $todo->text));
            $confidence += $textConfidence;

            $confidences[] = $confidence;
        }

        array_multisort($confidences, SORT_DESC, $todos);

        $result = [];
        foreach ($todos as $key => $todo) {
            $result[] = [$confidences[$key], $todo];
        }

        return $result;
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
    protected function formatTodo($todo)
    {
        $parser = $this->parser;

        $activity = ($parser->getActivity()) ? '' : $todo->activity;
        $category = ($parser->getCategory()) ? '' : $todo->category;
        $tags = array_diff($todo->tags, $parser->getTags());
        $text = $todo->text;

        return $this->formatAttributes($activity, $category, $tags, $text);
    }
    // }}}
    // {{{ formatTodos
    protected function formatTodos($todos)
    {
        foreach ($todos as $todo) {
            $todo->children = [];
            $numbered[$todo->id] = $todo;
        }

        foreach ($numbered as $todo) {
            if (!is_null($todo->parent) && array_key_exists($todo->parent, $numbered)) {
                $numbered[$todo->parent]->children[] = $todo;
            }
        }

        $list = '';
        foreach ($numbered as $todo) {
            if (is_null($todo->parent)) {
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
        $string = str_repeat('    ', $level) . $this->formatTodo($todo) . "\n";

        foreach ($todo->children as $child) {
            $string .= $this->todoTree($child, $level + 1);
        }

        return $string;
    }
    // }}}
}
