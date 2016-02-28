<?php

namespace Uglybob\MrClip\Lib;

class MrClip
{
    public function __construct($command, $options)
    {
        $this->prm = null;
        $options = str_replace(' : ', ':', $options); // @todo hack

        if ($command == 'comp') {
            $options = preg_replace('/^prm /', '', $options); // @todo hack
            $cm = new Command($options);
            error_log($command . '|' . $options . '|' . $cm->at() . '|' . $cm->getHint() . "\n", 3, 'debug.log');

            $at = $cm->at();

            if ($at == 'start') {
                echo " " . (new \Datetime())->format('H:i');
            } elseif ($at == 'actigory') {
                $activities = $this->getPrm()->getActivities();
                $categories = $this->getPrm()->getCategories();

                foreach($activities as $activity) {
                    foreach($categories as $category) {
                        $actigory = "$activity@$category";
                        if (preg_match("/^{$cm->getHint()}/", $actigory)) {
                            echo "$actigory ";
                        }
                    }
                }
            } elseif ($at == 'tag') {
                $tags = $this->getPrm()->getTags();

                foreach($tags as $tag) {
                    if (preg_match("/^{$cm->getHint()}/", $tag)) {
                        echo "+$tag ";
                    }
                }
            }
        } elseif ($command == 'record') {
            $cm = new Command($options);

            $this->getPrm()->editRecord(
                null,
                $cm->getStart(),
                $cm->getEnd(),
                $cm->getActivity(),
                $cm->getCategory(),
                $cm->getTags(),
                $cm->getText()
            );
        } else {

        }
    }

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
}
