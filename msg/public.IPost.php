<?
class msg_IPost
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function Modify($args)
  {
  extract(param_extract(array(
    check=>"int_checkboxes",
    action=>"string",
    BindTo=>"string",
    Moderator=>'int'
    ),$args));

  global $_USER;
  $_USER->LoadAccessInfo();
#  $Moderator=$_USER->Roles["msg:Moderator"];

  if ($action=='delete')
    {
    $s=implode (",",array_keys($check));
    if ($s)
      {
      DBExec ("DELETE FROM msg_Posts WHERE PostID IN ($s) AND BindTo='$BindTo'");
      DBExec ("DELETE FROM msg_Posts WHERE ParentID IN ($s) AND BindTo='$BindTo'");
      }
    }

  if ($action=='approve')
    {
    $s=implode (",",array_keys($check));
    if ($s)
      {
      DBExec ("UPDATE msg_Posts SET Approved=1 WHERE PostID IN ($s) AND BindTo='$BindTo'");
      }
    }
  return array(ForwardTo=>$_SERVER['HTTP_REFERER']);
  }

function Edit($args)
  {
  global $cfg;
  $_ =&$GLOBALS['_STRINGS']['msg'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $_USER;

  extract(param_extract(array(
    PostID=>'int',
    AnswerTo=>'int',
    Moderator=>'int',
    ),$args));
  $_USER->LoadAccessInfo();
  if ($_USER->HasRole("msg:Moderator"))  { $Moderator=true; }

  if ((!$PostID)&&($AnswerTo))
    {
    $q=DBQuery ("SELECT * FROM msg_Posts WHERE PostID=$AnswerTo");
    if (!$q)
      {
      return array(Error=>"Message has been removed",Details=>"PostID=$AnswerTo");
      }
    $BindTo=$q->Top->BindTo;
    if (!$Moderator)
      {
      $info=load_document_info($BindTo);
      $IsOwner=($Control->BoundDocument->OwnerUserID == $_USER->UserID);
      if (!$IsOwner)
        {
        return array(Warning=>$_['DENIED_EDITMESSAGE'],ButtonClose=>1);
        }
      }

    # Answer to a message
    $yourname="<input type='text' class='inputarea' maxlength='80' size='20' name='Author' value='".$_USER->Login."'>";
    print "<title>$_[TITLE_ANSWERTOMESSAGE] #$PostID</title>
    <form name='form_postmessage' method='post' action='".ActionURL("msg.IPost.Post.b",array(call=>'modal'))."'>
    <input type='hidden' name='AnswerTo' value='$AnswerTo'>
    <h3>$_[TITLE_ANSWERTOMESSAGE] #$AnswerTo</h3>";
    $email=$q->Top->Email;
    print "<table cellpadding='10'><tr><td><b>".$q->Top->Author."</b><br>".$q->Top->MsgText;
    if ($email) print "<br/><input type='checkbox' name='SendAnswerViaEmail' value='1' checked>$_[SEND_ANSWER_VIA_EMAIL] 
    <input type='text' name='ToEmail' value='$email' size='30' class='inputarea'/>";
    print "</td></tr></table><hr>";
    }
  else
    {
    if ($PostID)
      {
      $q=DBQuery ("SELECT * FROM msg_Posts WHERE PostID=$PostID");
      if (!$q)
        {
        return array(Error=>$_['DENIED_EDITMESSAGE']);
        }
      $row=$q->Top;
      if (($Moderator)||( ($row->UserID !=0)&&($_USER->UserID==$row->UserID) ) || (($row->UserID==0)&&($row->IPaddr==$_SERVER[REMOTE_ADDR] )))
        {
        $yourname=($_USER->UserID)? $_USER->Login : "<input type='text' class='inputarea' maxlength='80' size='20' name='Author' value='".$row->Author."'>";
        if ($Moderator)
          {
          $yourname="<input type='text' class='inputarea' maxlength='80' size='20' name='Author' value='".$q->Top->Author."'>";
          }
        }
      $MsgText=$q->Top->MsgText;
      $BindTo=$q->Top->BindTo;
      print "<title>$_[TITLE_EDITMESSAGE] #$PostID</title>
      <form name='form_postmessage' method='post' action='".ActionURL("msg.IPost.Update.f")."'>
      <h3>$_[TITLE_EDITMESSAGE] #$PostID</h3>
      ";
      }
    else
      {
      return (array(Error=>$_['DENIED_EDITMESSAGE']));
      }
    }

  print "
  <table border='0' cellpadding='5' width='100%'>
    <tr valign='top'><td width='10%' align='right'>$_[YOURNAME]</td><td>$yourname</td></tr>
    <tr valign='top'><td align='right'>$_[YOURMESSAGE]</td>
    <td><textarea class='inputarea' type='text' name='MsgText' cols='40' rows='8' style='width:100%'>".$MsgText."</textarea></td></tr>
    <tr><td></td><td>";
   $_ENV->PutButton(array(Action=>'submit',AutoHide=>1));
   $_ENV->PutButton(array(Action=>'cancel'));
#    <input type='button' onClick='this.style.visibility=\"hidden\"; form_postmessage.submit();' class='button' value='$__[CAPTION_OK]'>
    print "</td></tr></table>
    <input type='hidden' name='BindTo' value='$BindTo'>
    <input type='hidden' name='PostID' value='$PostID'>
    </form>";
  }

function Update($args)
  {
  extract(param_extract(array(
    PostID=>'int',
    BindTo=>"string",
    Author=>"nonesc_string",
    MsgText=>"nonesc_string",
    ),$args));

  global $_USER;
  $_=&$GLOBALS['_STRINGS']['msg'];

  $MsgText=strip_tags($MsgText,"<i><b>");
  $Author =strip_tags($Author);

  $_USER->LoadAccessInfo();
  $Moderator=false;
  if ($_USER->HasRole("msg:Moderator"))  { $Moderator=true; }

  $q=DBQuery ("SELECT IPaddr,UserID FROM msg_Posts WHERE BindTo='$BindTo' AND PostID=$PostID");
  $row=$q->Top;
  if ( ($Moderator)||( ($row->UserID !=0)&&($_USER->UserID==$row->UserID) ) ||
       (($row->UserID==0)&&($row->IPaddr==$_SERVER['REMOTE_ADDR'] )))
    {
    $upauthor="";
    if ((($Moderator) && ($Author)) || ( ($row->UserID==0)&&($row->IPaddr==$_SERVER['REMOTE_ADDR'])  ))
      {
      $upauthor=",Author='$Author'";
      }

    if (!DBExec ("UPDATE msg_Posts SET MsgText='$MsgText' $upauthor WHERE PostID=$PostID"))
      {
      return array(Error=>"Cannot update message text",Details=>$PostID);
      }
    }
  else
    {
    return (array(Error=>$_['DENIED_EDITMESSAGE']));
    }
  return array(ModalResult=>true);
  }


function Post($args)
  {
  extract(param_extract(array(
    AnswerTo=>'int',
    BindTo=>"string",
    Author=>"string",
    MsgText=>"string",
    Phone=>"string",
    Email=>"string",
    IsQna=>"int",
    ForumID=>"int",
    call=>"string",
    SendAnswerViaEmail=>'string',
    ToEmail=>'string'
    ),$args));

  global $cfg,$_USER,$_LANGUAGE;
  $_=&$GLOBALS['_STRINGS']['msg'];

  $BindToInfo=BindPathInfo($BindTo);
  if (!$BindToInfo) return array(Error=>"[[BAD_BINDTO_PATH]]",$BindTo);


  $MsgText=strip_tags($MsgText,"<i><b>");
  $Author =strip_tags($Author);
  $Phone =strip_tags($Phone);
  $Email=strip_tags($Email);
  $IPaddr=$_SERVER['REMOTE_ADDR'];
  $now=time();

  $Moderator=$_USER->HasRole("msg:Moderator");
  if (!$Author)
    {
    if ($_USER->UserID)
      {
      $Author=$_USER->Login;
      $Email=$_USER->Email;
      }
    else
      {
      $Author=$_['ANONYMOUS'];
      }
    }

  $PostID=DBGetID("msg.Post");
  $_USER->LoadAccessInfo();
  $Approved=0;
  if ($Moderator)
    {
    $Approved=1;
    $ParentID=$AnswerTo;
    }
  else
    {
    $ParentID=0;
    }

  if ($AnswerTo)
  {
  	$q=DBQuery ("SELECT Email,BindTo,ThreadPostID,ForumID FROM msg_Posts WHERE PostID=$AnswerTo");
  	if (!$q) {
  		return array(Warning=>"Ошибка: Не найдено сообщение, на которое производится ответ");
  		$ForumID=$q->Top->ForumID;
  		$ThreadPostID=$q->Top->ThreadPostID;
  		$BindTo=$q->Top->BindTo;
  		$Email=$q->Top->Email;
  	}
  	/*
    DBExec ("UPDATE msg_Posts SET Approved=1,Answered=1 WHERE PostID=$AnswerTo");
    $imail=&$_ENV->LoadInterface("mail.PMailSender");
    $imail->EnqueueMessage(array(
    			Cartridge=>"um",
					TemplateName=>"AccountActivation",
					Language=>$_LANGUAGE,
					MailFrom=>$cfg['HelpEmail'],
					MailTo=>$Email,
					PushIt=>1,
					FieldValues=>array(
		        LOGIN_URL=>$URL_Login,
		        ACTIVATION_URL=>ActionURL('um.IRegistration.Activate.b',array(Login=>$Login,Lang=>$cfg['Language'],ActivationKey=>$RandomKey,ForwardTo=>$URL_Forward)),
		        LOGIN=>$Login,
		        PASS=>$Pass1))
	        );
	        */
  }	
  if (!DBExec ("INSERT INTO msg_Posts (PostID,ParentID,BindTo,Author,MsgText,IPaddr,UserID,Email,PostTime,Approved,Subject,Phone,MachineKey,IsQna)
  VALUES ($PostID,$AnswerTo,'$BindTo','$Author','$MsgText','$IPaddr',$_USER->UserID,'$Email',$now,$Approved,'$Subject','$Phone','$_SESSION->MachineKey',$IsQna)"))
  {print "Ошибка базы данных"; exit;}

  if ($call=='modal') return array(ModalResult=>true);
  return array(ForwardTo=>$_SERVER['HTTP_REFERER']);
  }
}

?>
