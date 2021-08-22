<?
class msg_IForum
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function msg_IForum()
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  }

function Select ($args)
  {
  global $cfg;
  $IPage=$_ENV->LoadInterface("jsb.IPage");
  $ForumContext=$cfg['Settings']['msg']['ForumContext'];
  $IPage->Select(array(SysContext=>$ForumContext));
  }
function GetForumsList($args)
  {
  global $cfg;
  $ForumContext=$cfg['Settings']['msg']['ForumContext'];
  $q=DBQuery("SELECT JSBPageID as ForumID,Caption FROM jsb_Pages WHERE State=1 AND SysContext='$ForumContext'","ForumID");
  
  if ($q)
    {
    $result=false;
    foreach ($q->Rows as $ForumID=>$r)
      {
      $result[$ForumID]=langstr_get($r->Caption);
      }
    return array(ListValues=>$result);
    }
  }

}

?>
