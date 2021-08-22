<?
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }

class jsb_PChangeStyle
  {
  function next()
    {
    global $_SESSION;
    $bgno=intval($_SESSION->BackgroundNo);
    $bgno++;
    $_SESSION->BackgroundNo=$bgno;
    return array(ForwardTo=>$_SERVER[HTTP_REFERER]);
    }
  }

