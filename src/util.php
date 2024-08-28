<?php

namespace App;

class Util
{
    public static function logmsg($msg, $newLine=true)
    {
        $journal = fopen("logbook.txt", "a");
        if ($newLine)
         fwrite($journal, $msg . "\n");
       else
        fwrite($journal, $msg);
    }
}

