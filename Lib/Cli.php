<?php

namespace Uglybob\MrClip\Lib;

class Cli
{
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

    // {{{ output
    public static function output($string = '')
    {
        fwrite(STDOUT, $string);
    }
    // }}}
    // {{{ input
    public static function input($string = '')
    {
        return readline($string);
    }
    // }}}
}
