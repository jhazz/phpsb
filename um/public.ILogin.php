<?
class um_ILogin
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. User management cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
function um_ILogin()
  {
  $_=&$GLOBALS['_STRINGS']['um'];
  $this->Title=$_['TITLE_USER_MANAGEMENT_SYSTEM'];
  }
function TLoginPanel_phphash($str)
  {
  $hpos=0;
  for ($i=0;$i<strlen($str);$i++)
    {
    $c=sqrt(ord(substr($str,$i,1))*(($i+1)*100));
    $c=sprintf("%0.17f",$c-floor($c));
    for ($j=strlen($c)-1;$j>=0;$j--)
      {
      if (substr($c,$j,1)!='0') {break;}
      }
    if ($j>0) {$c=substr($c,0,$j);}
    $sz=strlen($c);
    if ($sz<4) continue;
    $sz-=4; if ($sz>12) {$sz=12;}
    $c=substr($c,2,$sz);
    for ($k=0;$k<$sz;$k++)
      {
      $hv=$h[$hpos];
      if (!hv) {$hv=0;}
      $h[$hpos]=($hv+intval(substr($c,$k,1)))%24;
      $hpos++; if ($hpos>10) {$hpos=0;}
      }
    }
  $res="";
  for ($i=0;$i<count($h);$i++)
    {
    $res.=chr($h[$i]+97);
    }
  return $res;
  }

function Forget($args) {
  extract(param_extract(array(
  login=>'string',
  URL_Reminded=>'string',
  ),$args));
  global $cfg,$_LANGUAGE;
  
  $u=DBQuery("SELECT Email,PASSWORD FROM um_Users WHERE Login='$login'");
  if ($u)
    {
    $to=$u->Top->Email;
    if ($to)
      {
      $password=$u->Top->PASSWORD;
      # SITE_NAME,SITE_URL,LOGIN_URL,HELP_EMAIL,LOGIN,PASS
      $imail=&$_ENV->LoadInterface("mail.PMailSender");
      if (!$imail) {
      	return array(Error=>"Required cartridge 'mail' inactive",Details=>'um.ILogin.Forget');
      }
      if ($imail->EnqueueMessage(array(
      	Cartridge=>"um",
      	TemplateName=>"PasswordReminder",
      	Language=>$_LANGUAGE,
      	MailFrom=>$cfg['Settings']['um']['RegistratorEmail'],
      	MailTo=>$to,
      	QueueName=>"UM:Password reminder for [$login]",
      	FieldValues=>array(
	        LOGIN_URL=>$_SERVER['HTTP_REFERER'],
	        LOGIN=>$login,
	        PASS=>$password
        )))) return array(ForwardTo=>$URL_Reminded);
      }
    }
}
function Enter($args)
  {
  global $cfg;
  extract(param_extract(array(
    login=>'string',
    pass=>'string',
    serverkey=>'string',
    keeplocked=>'int',
    hash=>'string',
    URL_Success=>'string',
    URL_Error=>'string',
    ),$args));

  $Error=0;
  $now2=$now-60*60; # One hour is expires a key
  DBExec ("DELETE FROM um_PublishedKeys WHERE PubDate<$now2");
  $now2=$now-60*60; # one hour is expires an inactive user
  DBExec ("DELETE FROM um_Users WHERE Activated=0 AND DateCreate<$now2");

  $login=strtolower(DBEscape ($login,true));
  $serverkey=DBEscape($serverkey,true);
  $ClientHash=$hash;

  $qkey=DBQuery ("SELECT * FROM um_PublishedKeys WHERE PubKey='$serverkey' AND IPaddr='$_SERVER[REMOTE_ADDR]'");
  $quser=DBQuery("SELECT PASSWORD,UserID,Visits,Activated FROM um_Users WHERE Login='$login'");

  if ((!$quser)||(!$qkey)||(!$quser->Top->Activated))
    {
    if ($URL_Error) return array(ForwardTo=>$URL_Error); else return false;
    }

  # YES THIS KEY IS NOT EXPIRED AND IT SENT TO HIM BEFORE
  $pass=$quser->Top->PASSWORD;
  $hash=$this->TLoginPanel_phphash($serverkey.":".$login.":".$pass);

  if ($hash!=$ClientHash)
    {
    if ($URL_Error) return array(ForwardTo=>$URL_Error); else return false;
    }

  DBExec ("DELETE FROM um_PublishedKeys WHERE PubKey='$ServerKey'");
  $PotentialUID=$quser->Top->UserID;

  global $_USER,$_SESSION;
  if (!$_USER->LoadByID ($PotentialUID))
    {
    $_USER->UserID=0;
    $_SESSION->UserID=0;
    return array(ForwardTo=>$URL_Error);
    }
  if ($keeplocked)
    {
    $_SESSION->KeepLocked=true;
    }
  $_USER->AuthorizeSessionToUserID($PotentialUID);
  return array(ForwardTo=>$URL_Success);
  
  }

function Logout($args)
  {
  global $_USER,$_SESSION;
  extract(param_extract(array(
    URL_ForwardTo=>'string',
    ),$args));

  if ($_USER->UserID) $_SESSION->Close();
  if (!$URL_ForwardTo) $URL_ForwardTo=$_SERVER['HTTP_REFERER'];
  return array(ForwardTo=>$URL_ForwardTo);
  }
}
