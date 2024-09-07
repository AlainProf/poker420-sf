<?php

class Util
{
	//-----------------------------------
	//
	//-----------------------------------
    public static function logmsg($msg="", $traceUp=false, $ligne=true, $afficheDate=true)
    {
	   //return;	
	   if ($traceUp)
	   {
          $journal = fopen("logbook.txt", "a");
          $d = "";
          if ($afficheDate)
            $d = date('Y-m-d H:i:s');
  
          if ($ligne)
           fwrite($journal, "$d: $msg\n");
          else
	       fwrite($journal, "$d: $msg");
	  
	      fclose($journal);	
	   }
    }
 

}