<?
class backend_ICover
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Backend administration cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function View($args)
  {
  global $cfg;
  global $_USER;
  $_=&$GLOBALS['_STRINGS']['backend'];

  if (!$_USER->HasRole("backend:BackendAccess")) {
    print "<p>$_[BACKEND_PERMITTED_FOR_YOU]</p><input type='button' class='button' value='$_[CAPTION_ENTER]'
      onClick='location.href=\"".ActionURL("backend.ILogout.Execute.b")."\"'>";
    return;
    }

  $this->MainMenu=new AdminMenu();
  $r=$this->MainMenu->Init();
  print "
<title>{ $cfg[SiteName] }</title>
<body $bhooks leftmargin=0 topmargin=0 marginwidth=0 marginheight=0><table cellspacing=0 cellpadding=0 width='100%'><tr><td class='topmenu_bg'>
  <table cellspacing=0 cellpadding=0 height='40' width='100%'>
    <tr valign='top'></td><td align='left'>";
  $this->MainMenu->Render("HeadMenu");
  global $_THEME;
  print "</td><td>&nbsp;&nbsp;</td><td align='right'><a href='http://www.phpsb.com' target='phpsbcom'><img src='$_THEME[SkinURL]/toplogo.gif' border='0'></a></td></tr></table>
  </td></tr></table>
    <table width='100%' cellspacing='0' border='0' cellpadding='0'><tr valign='top'><td>
    <iframe style='visibility:visible' id='AdminFrame' name='AdminFrame' src='".ActionURL("backend.ICover.InfoBlocks.b")."'
       width='100' height='530'>Sorry. Your explorer does not supported IFRAME</iframe>
    </td></tr></table>
    ";
  }

function calcHash($str)
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



function DoLogin ()
  {
  global $_SESSION;
  global $_USER;

  $now=time();
  $now2=$now-60*60; # One hour is expires a key
  DBExec ("DELETE FROM um_PublishedKeys WHERE PubDate<$now2");

  $Login=strtolower(DBEscape ($_POST['login'],true));
  $ServerKey=DBEscape ($_POST['serverkey'],true);
  $ClientHash=$_POST['hash'];

  $qkey=DBQuery ("SELECT * FROM um_PublishedKeys WHERE PubKey='$ServerKey' AND IPaddr='$_SERVER[REMOTE_ADDR]'");
  if (!$qkey) {return false;}

  # YES THIS KEY IS NOT EXPIRED AND IT SENT TO HIM BEFORE

  $q1=DBQuery("SELECT PASSWORD,UserID,Visits FROM um_Users WHERE Login='$Login'");
  if (!$q1) {return false;}

  $pass=$q1->Top->PASSWORD;
  $hash=$this->calcHash($ServerKey.":".$Login.":".$pass);

  if ($hash!=$ClientHash)
    {
    return false;
    }

  DBExec ("DELETE FROM um_PublishedKeys WHERE PubKey='$ServerKey'");
  $PotentialUID=$q1->Top->UserID;

  if (!$_USER->LoadByID ($PotentialUID))
    {
    $_USER->UserID=0;
    $_SESSION->UserID=0;
    return false;
    }
  if ($_POST['keeplocked'])
    {
    $_SESSION->KeepLocked=true;
    }
  $_SESSION->UserID=$PotentialUID;
  $_SESSION->Save();
  $_ENV->ApplicationEvent("um.OnUserLogin");

  DBExec ("UPDATE um_Users SET Visits=".(intval($q1->Top->Visits)+1).",LastVisit=".time()." WHERE UserID=$PotentialUID");
  return true;
  }

function Login()
  {
  global $cfg,$_USER,$_LANGUAGE;
  $_=&$GLOBALS['_STRINGS']['backend'];
  $__=$GLOBALS['_STRINGS']['_'];
  $forget=intval($_POST['forget']);
  $login=DBEscape($_POST[login],true);
  if ($forget) {
  	
  	if (!$_ENV->IsCartridgeActive('mail')) {
  		return array(Error=>"Required cartridge 'mail' is inactive");
  	}
    $u=DBQuery("SELECT Email,Login,PASSWORD FROM um_Users WHERE Login='$login'");
    if ($u)
      {
      $to=$u->Top->Email;
      if ($to)
        {
        $password=$u->Top->PASSWORD;
	      $imail=&$_ENV->LoadInterface("backend.PMailSender");
	      if (!$imail->EnqueueMessage(array(Cartridge=>"um",
	      	TemplateName=>"PasswordReminder",
	      	Language=>$_LANGUAGE,
	      	MailFrom=>$cfg['Settings']['um']['RegistratorEmail'],
	      	MailTo=>$to,
	      	QueueName=>"BACKEND:Password reminder for [$login]",
	      	FieldValues=>array(
		        LOGIN_URL=>ActionURL("backend.ICover.Login.b"),
		        LOGIN=>$login,
		        PASS=>$password
	        )))) $Message="Error sending email";
	      else $Message=$_['REMEMBER_MAIL_SENT'];
#        mail($to,$cfg['SiteName']." - $_[REMEMBER_MAIL_SUBJECT]","$_[REMEMBER_MAIL_MESSAGE]\n\nPass-word: '$p");
        }
      }
  } elseif ($login) {
    if (!$this->DoLogin())
      {
      $Message=$_['MSG_INCORRECT_LOGIN'];
      }
    else
      {
      print "<script>location.href='".ActionURL("backend.ICover.View.b")."';</script>";
      exit;
      }
    }

  if (($Message)||(!$_USER->UserID))
    {
    $ServerKey=md5(uniqid(rand())); $now=time();
    $loginUrl=ActionURL("backend.ICover.Login.b");

    DBExec ("INSERT INTO um_PublishedKeys (PubKey,IPaddr,PubDate) VALUES ('$ServerKey','$_SERVER[REMOTE_ADDR]',$now)");
    $langopts="";
    $Languages=$GLOBALS['_LANGUAGE_DISPATCHER']->LoadLanguages();
    foreach ($Languages as $Lang=>$l){
      if ($l['Enabled'])
        {
        $sel=($_LANGUAGE==$Lang)?"selected":"";
        $langopts.="<option value='$Lang' $sel>$l[Caption]</option>";
        }
      }
    if ($langopts) $langopts="<select class='inputarea' onChange='locform.chooselanguage.value=this.value; locform.submit()'>$langopts</select>";

    $MailActive=$_ENV->IsCartridgeActive('mail');

  ?>
  <script>
  function TLoginPanel_hash(str)
    {
    var v1,v2,hv,c,sz,h=new Array(),hpos=0;
    for (var i=0;i<str.length;i++)
      {
      c=Math.sqrt(str.charCodeAt(i)*((i+1)*100));
      c=(c-Math.floor(c)).toString();
      sz=c.length;
      if (sz<4) continue;
      sz-=4; if (sz>12) {sz=12;}
      c=c.substr(2,sz);
      for (k=0;k<sz;k++)
        {
        hv=h[hpos];
        if (!hv) {hv=0;}
        h[hpos]=(hv+Number(c.charAt(k)))%24;
        hpos++; if (hpos>10) {hpos=0;}
        }
      }
    res="";
    for (i=0;i<h.length;i++)
      {
      res+=String.fromCharCode(h[i]+97);
      }
    return res;
    }

    function submitLoginForm()
      {
      form1.hash.value=TLoginPanel_hash(form1.serverkey.value+":"+form1.login.value+":"+form1.pass.value);
      form1.pass.value="";
      form1.submitter.disabled=1;
  //    event.returnValue=false;
      }
    function ShowInfo()
      {
      P$.find('info').style.display='block';
      P$.find('ainfo').style.display='none';
      }

    function FormOnKeyUp()
      {
      P$.find('hashview').innerHTML=TLoginPanel_hash(form1.serverkey.value+":"+form1.login.value+":"+form1.pass.value);
      }

    function ShowRememberer()
      {
      P$.find('Rememberer').style.display='block';
      P$.find('Pass1').style.display=P$.find('Pass2').style.display=P$.find('Pass3').style.display='none';
      P$.find('input_forget').value=1;
      }

    </script>
    <table height='100%' width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td valign="middle" align='center'>
       <table border="0" cellspacing="0" cellpadding="1"><tr><td class='bgdown'>
       <table width='100%' border="0" cellspacing="0" cellpadding="0"><tr><td class='topmenu_bg'>
       <table width='100%' cellspacing='0' cellpadding='0'>
       <tr><td align='right'><a href='http://www.phpsb.com' target='phpsbcom'><img border='0' src='<? global $_THEME; print "$_THEME[SkinURL]/toplogo.gif"; ?>'></a></td></tr>
       </table></td></tr></table>
       <table border='0'>
       <tr><td class='bgdown' align='center'>
        <h1><? print $_['TITLE_LOGINFORM']; ?></h1>

        <? print $err; ?>

        <form name="form1" style='margin:0' target='_top' onSubmit="submitLoginForm()" method="post" action="<? print $loginUrl; ?>">
          <input type='hidden' name='serverkey' value='<? print $ServerKey; ?>'>
          <input type='hidden' name='hash' value=''>
          <input type='hidden' id='input_forget' name='forget' value=''>
          <table width='500' border="0" cellspacing="0" cellpadding="3">
            <tr>
              <td width='40%' align='right'><b><? print $_['CAPTION_LANGUAGE']; ?>:</b></td>
              <td>
              <? print $langopts; ?>
              </td>
            </tr>

            <tr>
              <td align='right'><b><? print $_['CAPTION_LOGIN']; ?>:</b></td>
              <td>
                <input class='inputarea' name="login" size="10" value="" maxlength='40' onKeyUp='FormOnKeyUp()'>
              </td>
            </tr>
            <tr>
              <td align='right'><div id='Pass1'><b><? print $_['CAPTION_PASSWORD']; ?>:</b></div></td>
              <td><div id='Pass2'><input class='inputarea' maxlength='40' id='input_password' name="pass" type="password" size="10" onKeyUp='FormOnKeyUp()'><br></div></td>
            </tr>
            <? if ($Message) { print "<tr><td colspan='2' align='center'><font color='red'>$Message</font></td></tr>"; } ?>
            <tr><td></td><td>
            <div id='Pass3'>
<?  $_ENV->PutButton(array(Action=>'submit',Caption=>$_['CAPTION_ENTER'],Name=>"submitter")); ?>
                <br><input type='checkbox' name='keeplocked' value='1'/><? print $_['KEEP_SESSION_LOCKED']; ?>
                <br><br>
                <table>
                <? if ($MailActive) {?>
                <tr><td><a href="#" onClick="ShowRememberer()"><? print $_['LINK_FORGETPASSWORD'];?></a></td></tr>
<?}?>
                <tr><td id='ainfo'><a href="#" onClick="ShowInfo()"><? print $_['LINK_SHOWSECURITYINFO'];?></a></td></tr>
                </table>
                </div>
              </td>
            </tr>
          </table>
              <div id='Rememberer' style='display:none'>
         <? print $_['MSG_DO_REMEMBER']?><br>
              <br>
<?  $_ENV->PutButton(array(Action=>'submit',Caption=>$_['CAPTION_SENDPASSWORD'])); ?>
              </div>
        </form>
        <?
        print "<div id='info' style='display:none'>
        <form method='get' name='locform'><input type='hidden' name='chooselanguage'></form>
        <table width='100%' border='0'><tr><td align='center' bgcolor='#f8f8f8'>";
         if (!$_COOKIE)
          {
          print "<font color='red'>$_[ERROR_COOKIE]</font>";
          }

        print "<table><tr><td align='right'>Received server key:</td><td style='color:#202080'>$ServerKey</td></tr>
          <tr><td align='right'>Sending key:</td><td style='color:#ff0000' id='hashview'></td></tr>
          <tr><td align='right'>Language:</td><td>$_LANGUAGE</td></tr>
              <tr><td align='right'>CookiePath:</td><td>'".$cfg['Session']['CookieURL']."'</td></tr>
              <tr><td align='right'><br><b>Cookies:</b></td></tr>";
        $c=0;
        foreach ($_COOKIE as $k=>$v) {
          if ($k=="MachineKey") {$c++;}
          print "<tr><td align='right'><font color='#606060'>$k</td><td>$v</td></tr>";
          }
        if ($c>1) {print "<font color='red'>$_[ERROR_COOKIEPATH]</font>";}
        ?>
        </table></div>

        <script>form1.login.focus();</script>
        </td></tr></table>
      </td></tr></table>
      </td></tr></table>
      </td></tr></table>
     <?
    }
  } # PutLoginForm()



function InfoBlocks ($args)
{
	$cartridges=&$_ENV->LoadCartridgesList(true);
	$MenuID='top';
	$Columns=false;
	foreach ($cartridges as $c=>$IsActive)
	{
		if (!$IsActive) continue;
		$cartridge=&$_ENV->LoadCartridge($c);
		if (method_exists($cartridge,"BackendInfoBlocks"))
		{
			$ba=$cartridge->BackendInfoBlocks();
			if ($ba) foreach ($ba as $b)
			{
				$Column=$b['Column'];
				if (!$Column) $Column=2;
				$Columns[$Column][]=$b;
			}
		}
	}


	$colwidths=array('25%','75%');
	print "<table width='100%' cellpadding='10'><tr valign='top'>";
	for ($ColumnNo=1;$ColumnNo<=2;$ColumnNo++)
	{
		$width=$colwidths[$ColumnNo-1];
		print "<td width='$width' class='bgup'>";
		if (isset($Columns[$ColumnNo]))
		{
			foreach ($Columns[$ColumnNo] as $block)
			{
				list($c,$int,$m,$e)=explode(".",$block['Call']);
				$intf=&$_ENV->LoadInterface("$c.$int");
				print "<table width='100%' cellpadding='5' cellspacing='0'><tr><td class='bgdown'>$block[Caption]</td></tr><tr><td class='bgupup'>";
				if (($intf)&&(method_exists($intf,$m)))
				{
					$r=$intf->$m(array(ColumnNo=>$ColumnNo));
					if ($r['Error']) {
						print_error ($r['Error'],$r['Details'],1,"$c.$int.$m",$r['IntruderAlert']);
					}
				} else print "Unable to execute infoblock '$c.$int.$m'";
				print "</td></tr></table>";

			}
		}
		print "</td>";
	}
	print "</tr></table>";

}

} # class


class AdminMenu
  {
  var $res="\nJSMenu_data=[];\n";
  var $str=false;
  var $RowNo=0;
  var $LastID=1;
  var $CategoryIDs=false;

  function Init()
    {
    global $_USER,$_THEME,$cfg;
    $cartridges=&$_ENV->LoadCartridgesList(true);
    $MenuID='top';
    foreach ($cartridges as $c=>$IsActive)
      {
      if (!$IsActive) continue;
      $cartridge=&$_ENV->LoadCartridge($c);
      if (method_exists($cartridge,"Menu"))
        {
        $Menu=$cartridge->Menu();
        foreach($Menu as $MenuRecord)
          {
          $Block=intval($MenuRecord['Block']);
          $CreateCategory=strtolower($MenuRecord['CreateCategory']);
          $PutToCategory=strtolower($MenuRecord['PutToCategory']);
          if ($CreateCategory)
            {
            $Caption=$MenuRecord['Caption'];
            if (!$Caption) $Caption=$CreateCategory;
            $this->CategoryIDs[$CreateCategory]=$this->LastID;
#            $PutToID=$this->CategoryIDs[$CreateCategory];
            $this->CategoryEmpty[$CreateCategory]=1; # if at leas one action is allowed category will be shown
            $this->CategoryEmpty[$PutToCategory]=0;
            $Icon=$MenuRecord['Icon'];
            if ($Icon) $Icon="$cfg[PublicURL]/$c/$Icon";
            $this->Menu[$MenuID][$Block][]=array(
              PageID=>$this->LastID,
              Icon=>$Icon,
              ParentCategory=>$PutToCategory,
              Caption=>$Caption);
            $PutToCategory=$CreateCategory;
            $this->LastID++;
            }

          if (!$MenuRecord['Items']) continue;
          foreach($MenuRecord['Items'] as $j=>$Item)
            {
            if (!$_USER->IsActionAllowed($Item['Call'])) { continue;}
            $this->CategoryEmpty[$PutToCategory]=0;
            $Caption=$Item['Caption'];
            $Block=intval($Item['Block']);
            if (!$Caption) $Caption=$Item['Call'];
            $Icon=$Item['Icon'];
            if ($Icon) $Icon=$cfg['PublicURL'].'/'.$c.'/'.$Icon;
            $this->Menu[$MenuID][$Block][]=array(
              PageID=>$this->LastID,
              ParentCategory=>$PutToCategory,
              Icon=>$Icon,
              Call=>$Item['Call'],
              Caption=>$Caption);
            $this->LastID++;
            }
          }
        }
      }

    ksort($this->Menu);
    foreach ($this->Menu as $MenuID=>$Block)
      {
      if (!is_array($Block)) continue;
      ksort($Block);
      $NeedSep=0;
      foreach ($Block as $i=>$SubBlock)
        {
        foreach ($SubBlock as $j=>$MenuRecord)
          {
          $ParentID=intval($this->CategoryIDs[strtolower($MenuRecord['ParentCategory'])]);
          if (!$this->str){
            $this->str='JSMenu_data['.$this->RowNo.']="'; $this->RowNo++;
            }
          if (($NeedSep)&&($ParentID)) $this->str.="m:$ParentID:-1:-|";

          $this->str.="m:$ParentID:$MenuRecord[PageID]:$MenuRecord[Caption]";
          if ($MenuRecord['Call'])
            {
            list ($c,$int,$m,$e)=explode (".",$MenuRecord['Call']);
            if (!$e) $e='bm';
            $this->str.="@u=$c.$int.$m.$e";
            }
          if ($MenuRecord['Icon'])
            {
            $this->str.="@i=".$MenuRecord['Icon'];
            }


          if (strlen($this->str)>2500)
            {
            $this->res.=$this->str."\";\n";
            $this->str="";
            } else {$this->str.="|";}
          $NeedSep=0;
          }
        $NeedSep=1;
        }
      }

    if ($this->str)
      {
      $this->res.=$this->str."\";\n\n";
      $this->str=false;
      }


    $skindefs="SkinDefs=$_THEME[Topmenu];";
    if ($this->res)
      {
      print"\n<script src='".$cfg['PublicURL']."/backend/adminmenu.js'></script>";
?>
<script>
<? print $this->res;?>
var ActionURL='<? print $cfg['ActionURL']; ?>';
<? print $skindefs; ?>
function resetCover(){MMD_ReArrange();var a=P$.find('AdminFrame'),b=document.body;a.height=b.clientHeight-50;a.width=b.clientWidth-5;a.style.visibility='visible';}
var topmenu=new TMainMenu('topmenu',JSMenu_data,'m',0);

P$.on('load',resetCover,window);
P$.on('resize',resetCover,window);

</script>
<?
      }
    else
      {
      return array(Error=>"Menu not loaded");
      }

    return $result;
    }
  function Render($Style)
    {
    global $cfg,$_LANGUAGE;
    print "\n\n<script>topmenu.Build('$Style',false,false,'".$cfg['RootURL']."/edit');</script>\n";
    }

  }