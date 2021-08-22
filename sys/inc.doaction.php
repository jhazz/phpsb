<?php
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
#define ('ACCEPT_INCLUDE',1);
$START_TICK=getmicrotime();
Header ("Pragma: no-cache");
Header ("Cache-Control: no-cache");
Header ("Content-type: text/html; charset=utf-8");

if (isset($_GET['chooselanguage'])) {$_LANGUAGE=$_GET['chooselanguage']; setcookie('DefaultLanguage',$_LANGUAGE,time()+60*60*24*365,$cfg['Session']['CookieURL']);}
else {$_LANGUAGE=$_COOKIE['DefaultLanguage'];}
if (!$_LANGUAGE){$_LANGUAGE=$cfg['Language'];}
if (!$_LANGUAGE){$_LANGUAGE='en';}

require_once ("inc.settings.php");
require_once ('inc.lang.php');
require_once ('inc.dbase.php');
require_once ('inc.sys.php');

$__=&$GLOBALS['_STRINGS']['_'];

function extract_call_string($s)  {
  $i=strpos($s,'?');
  if ($i!==false) $s=substr($s,0,$i);
  $i=strpos($s,'scripts/do/');
  if ($i!==false) $s=substr($s,$i+11);
  return $s;
  }

$_CALL=extract_call_string($_SERVER["REQUEST_URI"]);
$Args=$_ENV->Unserialize($_GET['ArgsStr']);
if ($_GET) {$Args=($Args)? $Args+$_GET : $_GET;}
if ($_POST) {$Args=($Args)? $Args+$_POST : $_POST;}
if (isset($Args['ArgsStr'])) {unset($Args['ArgsStr']);}

$_THEME_NAME=$cfg['Settings']['jsb']['ActiveTheme']; # frontend theme name
list($_CARTRIDGENAME,$_INTERFACE,$_METHOD,$_ENVIRONMENT)=explode (".",$_CALL,4);
if (substr($_METHOD,0,1)=='_') exit; # do not call private methods

trace ("$_CALL [$_ENVIRONMENT]",1);
trace ($Args,0);

if (($_ENVIRONMENT!='n')&&($_ENVIRONMENT!='f')&&($_ENVIRONMENT!='b')&&($_ENVIRONMENT!='bm'))
  {
  print_error ("Calling URL does not contain correct elements ",$_CALL,1,"doaction",2);  # maximum intruder alert
  exit;
  }

if ((!$_USER->UserID)&&(($_ENVIRONMENT=='bm')||($_ENVIRONMENT=='b')))
  {
  $_ENV->LoadTheme($_ENVIRONMENT);
  $_ENV->InitPage(array(Environment=>$_ENVIRONMENT,PutVars=>true));
  $dname="$cfg[ScriptsPath]/backend";
  if (!is_dir($dname)) $dname="$cfg[PHPSBScriptsPath]/backend";
  $langfile="$dname/lang.$_LANGUAGE.php";
  if (file_exists($langfile)) {require_once ($langfile);}
  $intf=&$_ENV->LoadInterface("backend.ICover");
  $intf->Login();
  exit;
  }

#
# - Init environment
#


if ($_ENVIRONMENT!='n') {
  $_ENV->LoadTheme($_ENVIRONMENT);
  $_ENV->InitPage(array(Environment=>$_ENVIRONMENT,PutVars=>true));
#  $_ENV->InitWindows();
  }

#
# - Check user permission to execute this Call
#
if (!$_USER->IsActionAllowed($_CALL))
  {
  print_error ("You have no rights to execute this Call",$_CALL,1,"execute",1);  # medium intruder alert
  exit;
  }

$intf=&$_ENV->LoadInterface("$_CARTRIDGENAME.$_INTERFACE");

if (!is_object($intf))
  {
  # all errors already printed
  exit;
  }

if ($_ENVIRONMENT=='bm')
  {
  execute_PutContextMenu($_CARTRIDGENAME,$_CALL);
  }

execute_CallMethod ($intf,$_METHOD);
if ($_ENVIRONMENT=='b') { print "</td></tr></table></body>"; }
print "</html>";

#######################################

function execute_CallMethod(&$interface,$method)
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  global $_ENVIRONMENT,$Args;
  if (method_exists($interface,$method))
    {
    $Title=$interface->Title ;
    $r=call_user_func(array(&$interface,$method),$Args);
    if ($r)
      {
      if ($r['Alert']) print "<script>window.alert(\"$r[Alert]\");</script>";
      if ($r['Back']) {
      	if ($_SERVER['HTTP_REFERER'] && (!headers_sent())) header ("Location: $_SERVER[HTTP_REFERER]");
      	else print "<script>history.back();</script>";
      	exit;
      }
      if ($r['Previous']) {
      	if ($_SERVER['HTTP_REFERER']) print "<script>location.href='".concat_url_args($_SERVER['HTTP_REFERER'],"rnd=".rand())."';</script>";
      	else print "<script>history.back();location.reload();</script>";
      	exit;
      }
      if ($r['ForwardTo']) {
      	if (!headers_sent()) header ("Location: $r[ForwardTo]");
      	else print "<script>location.href='".$r['ForwardTo']."';</script>";
      	exit;
      }

      if ($r['Error'])
        {
        print_error ($r['Error'],$r['Details'],1,false,$r['IntruderAlert']);
        }
        
      if ($r['Message']) {
        print "<center><table width='390' cellpadding='10'><tr><td align='center' class='bgdown'><p>".$r['Message']."</p>";
        if ($r['Details']) {print "<p><i>".$r['Details']."</i></p>";}
        print "</td></tr></table></center>";
        }
      if ($r['ButtonOk']||$r['ButtonClose']||($r['ButtonBack'])) {
      	print "<table width='100%'><tr><td align='center'>";
	      if ($r['ButtonOk']) $_ENV->PutButton('ok');
	      if ($r['ButtonClose']) $_ENV->PutButton(array(Action=>'cancel',Caption=>$__['CAPTION_CLOSE']));
	      if ($r['ButtonBack']) $_ENV->PutButton(array(Action=>'back',Caption=>$__['CAPTION_BACK']));
      	print "</td></tr></table>";
      }
      if ($r['Close']) {print "<script>window.close();</script>";}
      if ($r['TimeoutForwardTo']) {
      	print "<script>window.setTimeout(\"document.getElementById('continueclicker').style.display='none'; location.href='$r[TimeoutForwardTo]';\",5000);</script>
      	<br><center><a id='continueclicker' href='$r[TimeoutForwardTo]'>$__[CLICK_HERE_IF_NOT_WAIT]</a></center>";
      	exit;
      }
      
      if ($r['ModalResult'])
        {
        $z=$r['ModalResult'];
        if (is_array($z))
          {
          $s="";
          foreach ($z as $k=>$v)
            {
            if ($v)
              {
              if ($s) {$s.=",";}
              if (is_string($v)) {$v="'$v'";}
              $s.="$k:$v";
              }
            }
          $s='{'.$s.'}';
          } else {
            $s=$z;
            if ($z===true) $s="ok";
            if (is_string($s)) {$s="'$s'";}
            }
        print "<script>W.modalResult($s);</script>";
        }
      }
    }
  else
    {
    print_developer_warning("Interface loaded but method not found","$method");
    }
  }


function execute_PutContextMenu($cartridgeName,$currentCall=false)
  {
  global $_USER,$cfg,$ContextMenuHasPut,$_SYSSKIN_NAME;
  $SysSkinURL="$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME";
  if (!$ContextMenuHasPut)
    {
    $ContextMenuHasPut=1;
    $cartridge=&$_ENV->LoadCartridge($cartridgeName);
    if (!method_exists($cartridge,"Menu")) return;
    print "<td class='lmenu_panel'>";
    $menu=$cartridge->Menu();

    $ThisTitle="";
    if ($menu)
      {
      if (!is_array($menu))
        {
        print_error("Bad menu declaration","Returning variable of Menu() is an array of menu blocks. Each block has Category, Caption and Items(array)",1,"execute");
        return;
        }
      list ($c,$intf,$m,$e)=explode (".",$currentCall);
      $currentCall="$c.$intf.$m";

      foreach($menu as $i=>$block)
        {
        if (is_array($block['Items']))
          {foreach ($block['Items'] as $j=>$alink)
          {
          if ($alink['Icon']) {
          	$block['Items'][$j]['Icon']="$cfg[PublicURL]/$cartridgeName/$alink[Icon]";
          }
          if ($alink['Call'])
            {
            $url=ActionURL($alink['Call'],$alink['Args']);
            if (!$_USER->IsActionAllowed($alink['Call'])) {$url="";}
            $block['Items'][$j]['URL']=$url;
            list ($c,$intf,$m,$e)=explode (".",$alink['Call']);
            $call="$c.$intf.$m";
            if ($call==$currentCall)
              {
              $block['Items'][$j]['Active']=true;
              $ThisTitle=$alink[Caption];
              }
            
            }
          }
          $_ENV->PutRelatedLinks("main$i",$block,"$SysSkinURL/tree_f.gif",$cartridgeName);
          }
        }
      }
    print "<table width='100%' border='0'><tr><td align='center'>";
    if ($ThisTitle) {print "<h1>$ThisTitle</h1>";}
    }
  }


function getmicrotime(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
  }

?>
