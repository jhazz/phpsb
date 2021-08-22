<?
class msg_IRateit
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/msg";
var $ComponentVersion="1.0";

function Modify($args)
  {
  extract(param_extract(array(
    check=>"int_checkboxes",
    action=>"string",
    BindTo=>"string"
    ),$args));

  global $_USER;
  $_USER->LoadAccessInfo();
  $Moderate=$_USER->Roles["msg:Moderator"];

  if ($Moderate)
    {
    if ($action=='delete')
      {
      $s=implode (",",array_keys($check));
      if ($s)
        {
        DBExec ("DELETE FROM msg_Rateit WHERE RateMsgID IN ($s) AND BindTo='$BindTo'");
        }
      }

    if ($action=='approve')
      {
      $s=implode (",",array_keys($check));
      if ($s)
        {
        DBExec ("UPDATE msg_Rateit SET Approved=1 WHERE RateMsgID IN ($s) AND BindTo='$BindTo'");
        }
      }
    }
  print "<script>history.back();location.reload();</script>";
#  Header("Location: ".$_SERVER[HTTP_REFERER]);
  }
  
function Edit($args) {
  $_ =&$GLOBALS['_STRINGS']['msg'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg,$_USER;

  extract(param_extract(array(
		RateMsgID=>'*int',
  ),$args));

  $_USER->LoadAccessInfo();
  $Moderator=false;
  if ($_USER->HasRole("msg:Moderator"))  { $Moderator=true; }
  $q=DBQuery ("SELECT * FROM msg_Rateit WHERE RateMsgID=$RateMsgID");
  if (!$q)
    {
    return array(Error=>$_['DENIED_EDITMESSAGE']);
    }
  $_ENV->SetWindowOptions(array(Width=>600,Height=>400));
  if ($q) extract(param_extract(array(
		MsgText=>'nonesc_string',
		Author=>'string',
		BindTo=>'string',
		Rate=>'int',
		Approved=>'int',
  ),$q->Top));
  
	print "<h3>$_[TITLE_EDITMESSAGE] #$RateMsgID</h3>";
  $_ENV->OpenForm(array(
  	Action=>ActionURL('msg.IRateit.Update.b'),
  	ShowCancel=>1,
  	ModalOkOnOk=>1,
  	Width=>'100%'));
  if (($Moderator)||( ($row->UserID !=0)&&($_USER->UserID==$row->UserID) ) || (($row->UserID==0)&&($row->IPaddr==$_SERVER['REMOTE_ADDR'] ))) {
    if (($Moderator)||(!$_USER->UserID)) {
    	$_ENV->PutFormField(array(Type=>'string',Caption=>$_['YOURNAME'],Name=>'Author',Required=>1,Value=>$Author));
    } else {
    	print "<tr><td>$_[YOURNAME]</td><td>$_USER->Login</td></tr>";
    }
  }
	$_ENV->PutValueSet(array(ValueSetName=>'rates', Values=>array(1=>'+',2=>'++',3=>'+++',4=>'++++',5=>'+++++')));
	$_ENV->PutFormField(array(Type=>'droplist',ValueSetName=>'rates',Caption=>$_['RATEIT_YOURRATE'],Name=>'Rate',Value=>$Rate));

	if ($Moderator) {
		$_ENV->PutFormField(array(Type=>'checkbox',Caption=>$_['PUBLISH'],Name=>'Approved',Value=>$Approved));
	}
  print "<tr><td colspan='2'>";
  $_ENV->PutFormField(array(Type=>'text',Style=>'vertical',Required=>1,Value=>$MsgText,Name=>'MsgText',Size=>60,Width=>'100%', Rows=>10,Caption=>$_['YOURMESSAGE']));
  print "</td></tr></td></tr></table>
    <input type='hidden' name='RateMsgID' value='$RateMsgID'>
    ";
  $_ENV->CloseForm();
  }
function Update($args) {
  $_ =&$GLOBALS['_STRINGS']['msg'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg,$_USER;

  extract(param_extract(array(
		RateMsgID=>'*int',
    Author=>"*string",
    MsgText=>"*string",
    Rate=>"integer=0",
    Approved=>'int'
    ),$args));	
  $q=DBQuery ("SELECT * FROM msg_Rateit WHERE RateMsgID=$RateMsgID");
  if (!$q) return array(Error=>$_['DENIED_EDITMESSAGE']);

  $_USER->LoadAccessInfo();
  $Moderator=false;
  if ($_USER->HasRole("msg:Moderator"))  { $Moderator=true; }
  
  $values=array(MsgText=>$MsgText, Author=>$Author,	Rate=>$Rate);
 	if ($Moderator) $values['Approved']=$Approved;
  		
  DBUpdate(array(Table=>'msg_Rateit',	Keys=>array(RateMsgID=>$RateMsgID),Values=>$values));
  return array(ModalResult=>true);
}

function Post($args)
  {
  extract(param_extract(array(
    BindTo=>"string",
    Author=>"string",
    MsgText=>"string",
    Rate=>"integer=0"
    ),$args));

  global $cfg;
  $_=&$GLOBALS['_STRINGS']['msg'];
  global $_USER,$_SESSION;

  $MsgText=strip_tags($MsgText);
  $Author =strip_tags($Author);
  $IPaddr=$_SERVER['REMOTE_ADDR'];
  $now=time();

  if ($MsgText)
    {
    if (!$Author)
      {
      if ($_USER->UserID)
        {
        $Author=$_USER->Login;
        }
      else
        {
        $Author=$_['ANONYMOUS'];
        }
      }

    $RateMsgID=DBGetID("msg.Rateit");
    $Approved=0;
    if ($_USER->UserID)
      {
      $Approved=1;
      }
    DBExec ("INSERT INTO msg_Rateit (RateMsgID,BindTo,Author,MsgText,IPaddr,UserID,Email,Rate,PostTime,Approved)
    VALUES ($RateMsgID,'$BindTo','$Author','$MsgText','$IPaddr',$_USER->UserID,'$Email',$Rate,$now,$Approved)");

    DBReplace(array(
      Table=>'msg_KnownAuthors',
      Values=>array(Author=>$Author,LastAccessTime=>time()),
      Keys=>array(MachineKey=>$_SESSION->MachineKey)
      ));
    DBExec ("DELETE FROM msg_KnownAuthors WHERE LastAccessTime<".(time()-24*60*60*90));
    }
  print "<script>history.back();location.reload();</script>";
#  Header("Location: ".$_SERVER['HTTP_REFERER']);
  }
}

?>
