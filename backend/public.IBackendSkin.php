<?
class backend_IBackendSkin
{
var $SysSkins;
var $CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
#var $RoleAccess=array(ChangeSettings=>"Edit,Update");

function backend_IBackendSkin()
  {
  $_=&$GLOBALS['_STRINGS']['backend'];
  $this->Title=$_['SET_BACKEND_SKIN'];
  }


function Update($args)
  {
  global $cfg;
  extract(param_extract(array(
    SetSkin=>'string',
    ),$args));
  setcookie('ActiveSysSkin',$SetSkin,time()+60*60*24*365,$cfg['Session']['CookieURL']);
  print "<script>window.top.location.reload();</script>";
  }

function Browse($args)
  {
  global $cfg,$_THEME,$_SYSSKIN_NAME;
  $_ =&$GLOBALS['_STRINGS']['backend'];
  $__=&$GLOBALS['_STRINGS']['_'];

    	
    	
  $d1="$cfg[PHPSB_PATH]/ver/$cfg[PHPSB_VERSION]/sys/public/skins";
#        $_THEME['SkinPath']="$cfg[PHPSB_PATH]/ver/$cfg[PHPSB_VERSION]/sys/public/skins/$_SYSSKIN_NAME";
#        $_THEME['SkinURL'] ="$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME";

  $d=opendir($d1);
  $text="";
  $colno=0;
  while ($themedir=readdir($d))
    {
    $d2=$d1.'/'.$themedir;

    if (($themedir=='.')||($themedir=='..')||(!is_dir($d2))) {continue;}
    $themephp=$d2.'/sys_theme.php';
    $themeimg=$d2.'/theme_155x155.gif';
    if (!file_exists ($themephp)) continue;
    if (!include ($themephp))
      {
      print "Errors detected";
      continue;
      }
    $Title=$_THEME['Title']; if (!$Title) $Title=$themedir;
    $CopyrightText=$_THEME['CopyrightText'];
    $CopyrightURL=$_THEME['CopyrightURL'];
    if ($CopyrightURL&&(substr($CopyrightURL,0,7)!='http://')) $CopyrightURL="http://".$CopyrightURL;
    if ($CopyrightURL) $CopyrightText="<a href='$CopyrightURL' target='_blank'>$CopyrightText</a>";

    $img="";
    if (file_exists($themeimg))
      {
      $size=getimagesize($themeimg);
      $img="<img src='$cfg[PublicURL]/sys/skins/$themedir/theme_155x155.gif' $size[3]/><br>";
      }
    $sel=(($_SYSSKIN_NAME==$themedir)?"checked":"");

    if (!$colno) $text.="<tr>";
    $text.="<td class='bgdown' valign='top'>$img<input type='radio' name='SetSkin' value='$themedir' $sel>
    [$themedir] $Title<p class='notice'>$_THEME[Description]</p>$CopyrightText
    </td>";
    $colno++;
    if ($colno>3) {$colno=0; $text.="</tr>";}
    }
  closedir ($d);

  if ($text)
    {
    if ($colno) $text.="</tr>";
    $_ENV->OpenForm(array(Action=>ActionURL('backend.IBackendSkin.Update.n'),Align=>'center'));
    print "<table cellpadding='10' border='0'>$text</table>";
    $_ENV->CloseForm();
    }
  else
    {
    print_developer_warning("No skins found in the skin directory '$d1'");
    }
  }
}
?>
