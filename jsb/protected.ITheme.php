<?
class jsb_ITheme
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(MainDesigner=>"Edit,Update");

function jsb_ITheme()
  {
  $_=&$GLOBALS['_STRINGS']['jsb'];
  $this->Title=$_['TITLE_EDIT_THEME'];
  }

function Update($args)
  {
  global $cfg;
  $_ =&$GLOBALS['_STRINGS']['jsb'];
  $newtheme=DBEscape($args['selected_theme'],true);
  $cfg['Settings']['jsb']['ActiveTheme']=$newtheme;
  global $_SETTINGS;
  $_SETTINGS->Save();
  print "<h3>$_[THEME_CHANGED]</h3>";
  }

function Edit($args)
  {
  $_ =&$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $_THEME,$cfg;
  $ActiveTheme=$cfg['Settings']['jsb']['ActiveTheme'];

  $colno=0;
  print "<form method='post' action='".ActionURL("jsb.ITheme.Update.bm")."'>
  <table cellspacing=10 cellpadding=20 border='1'>";
  $d1=$cfg['ThemesPath'];
  $d=opendir($d1);
  while ($themedir=readdir($d))
    {
    $d2=$d1.'/'.$themedir;

    if (($themedir=='.')||($themedir=='..')||(!is_dir($d2))) {continue;}
    $themephp=$d2.'/theme.php';
    if (file_exists ($themephp))
      {
      if (!include_once ($themephp))
        {
        print "Errors detected";
        continue;
        }

      $Title=$_THEME['Title']; if (!$Title) $Title=$themedir;
      $Description=$_THEME['Description'];
      $CopyrightText=$_THEME['CopyrightText'];
      $CopyrightURL=$_THEME['CopyrightURL'];
      if ($CopyrightURL) $CopyrightText="<br><a href='$CopyrightURL' target='_blank'>$CopyrightText</a>";
      $img=$cfg['SkinsURL']."/$themedir/theme_155x155.gif";
      $imgfile=$cfg['SkinsPath']."/$themedir/theme_155x155.gif";
      $sel="";
      if ($themedir==$ActiveTheme) {$sel=' CHECKED';}
      $templates="";

      $dt=opendir($d2);
      while ($fname=readdir($dt))
        {
        $afname=$d2.'/'.$fname;
        if (is_file($afname)&&(substr($fname,-4)=='.php')&&(substr($fname,0,9)=='template.'))
          {
          $tname=substr($fname,9,strlen($fname)-(4+9));
          if ($templates) $templates.=',';
          $templates.=$tname;
          }
        }
      closedir ($dt);
      if ($templates) {$templates=$_[THEME_TEMPLATES].':<br>'.$templates.'<br>';}
      else {$templates="<font color='red'>$_[THEME_NO_TEMPLATES]</font><br/>";}

      if (!$colno) {print "<tr valign='top'>";}
      print "<td width='50%' class='tab'>";
      if (file_exists($imgfile))
        {
        print "<img align='right' src='$img' width='155' height='155'>";
        }

      print "<h4>[$themedir] $Title</h4><p class='notice'>$Description$CopyrightText</p>$templates
        <input type='radio' name='selected_theme' value='$themedir'$sel> $_[ACTIVATE_THIS_THEME]
        </td>";
      $colno++;
      if ($colno>1) {$colno=0; print "</tr>";}
      }
    }
  if ($colno) {print "</tr>";}
  closedir ($d);
  print "</table><input type='hidden' name='action' value='updatetheme'>";
  $_ENV->PutButton('submit');
  print "</form>";
  }

function SettingGetTheme($args)
  {
  $_ =&$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $_THEME,$cfg;
  $ActiveTheme=$cfg['Settings']['jsb']['ActiveTheme'];
  extract ($args);

  $d1=$cfg['ThemesPath'];
  $d=opendir($d1);
  $result="";
  while ($themedir=readdir($d))
    {
    $d2=$d1.'/'.$themedir;

    if (($themedir=='.')||($themedir=='..')||(!is_dir($d2))) {continue;}
    $themephp=$d2.'/theme.php';
    if (file_exists ($themephp))
      {
      if (!include_once ($themephp))
        {
        print "Errors detected";
        continue;
        }
      }
    $Title=$_THEME['Title']; if (!$Title) $Title=$themedir;

    $sel=(($Value==$themedir)?"checked":"");
    $result.="<tr class='bgdown' valign='top'><td><input type='radio' name='$Name' value='$themedir' $sel></td>
    <td>[$themedir] $Title<p class='notice'>$Description</p>
    </td></tr>";

    }
  closedir ($d);
  if ($result) $result="<table border='0'>$result</table>";

  # restore system theme back ;)
  $_ENV->LoadTheme('b');
  return $result;
  }
}


?>
