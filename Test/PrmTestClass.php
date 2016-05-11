<?php

namespace Uglybob\MrClip\Test;

use Uglybob\MrClip\Lib\PRM;

class PrmTestClass extends PRM
{
    // {{{ variables
    public $connection;
    // }}}
    // {{{ constructor
    public function __construct()
    {
        $this->connection = new ApiMock();
    }
    // }}}

    // {{{ getConnection
    public function getConnection()
    {
        return $this->connection;
    }
    // }}}
}
