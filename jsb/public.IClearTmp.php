<?
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }

class jsb_IClearTmp
  {
  function NavigationMenu()
    {
    $_ENV->DropCache('stdctrls_jsmenu');
    }

  function All()
    {
    $_ENV->DropCache('*');
    }

  }

?>
