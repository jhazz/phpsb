<?
class backend_ILogout
  {
  function Execute($args)
    {
    global $_USER,$_SESSION;

    if ($_USER->UserID)
      {
      $_SESSION->Close();
      }
    print "<script>window.top.location.href='../../admin';</script>";
    exit;
    }
  }
?>
