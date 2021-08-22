<?
class msg_IQna
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function msg_IQna()
  {
  $_=&$GLOBALS[_STRINGS][msg];
  }

function LoadXML($args)
  {
  extract(param_extract(array(
    BindTo=>"string",
    PageNo=>"int=1",
    ),$args));

  global $_CORE,$MachineKey;
  $IPaddr=$_SERVER[REMOTE_ADDR];
  $QuarantineTime=intval($cfg['Settings']['msg']['QuarantineTime'])*60*60;
  $RowsPerPage=intval($cfg['Settings']['msg']['QnA_RowCount']);
  $DateFormat=$cfg['Settings']['msg']['QnA_DateFormat'];
  $timeago=time()-$QuarantineTime;
  $qlimit="BindTo='$BindTo'";
  if (!$Moderate)
    {
    $qlimit.=" AND (Approved=1 OR PostTime<$timeago OR IPaddr='$IPaddr') ";
    }
  $qlimit1=$qlimit;
  if ($qlimit1) {$qlimit1.="AND ";}
  $qlimit1.="ParentID=0";

  $i1=($PageNo-1)*$RowsPerPage;

  $qc=DBQuery ("SELECT COUNT(*) AS RowCount FROM msg_Posts WHERE $qlimit1");
  $qroots=DBQuery ("SELECT * FROM msg_Posts WHERE $qlimit1 ORDER BY PostTime DESC LIMIT $i1,$RowsPerPage","PostID");
  $PageCount=ceil($qc->Top->RowCount/$RowsPerPage);


  $knownauthor="";
  $qk=DBQuery ("SELECT Author FROM msg_KnownAuthors WHERE MachineKey='$MachineKey'");
  if ($qk) {$knownauthor=$qk->Top->Author;}
  $result="<qna pagecount='$PageCount' pageno='$PageNo' bindto='$BindTo' knownauthor='$knownauthor'/>";
  if ($qroots)
    {
    $rootlist=implode(",",array_keys($qroots->Rows));
    if ($qlimit) $qlimit.=" AND ";
    $qlimit.=" ParentID IN ($rootlist)";
    $s="SELECT * FROM msg_Posts WHERE $qlimit ORDER BY PostTime";
    $qans=DBQuery ($s,"ParentID");
    foreach ($qroots->Rows as $PostID=>$row)
      {
      $str=$row->MsgText;
      $str=str_replace ("\r","",$str);
#        $str=str_replace ("\r\n","\n",$str);
      $result.="\n<question author='$row->Author' date='".format_date($DateFormat,$row->PostTime)."'>$str</question>";
      $answer=$qans->Rows[$PostID];
      if ($answer)
        {
        $str=$answer->MsgText;
        $result.="\n<answer author='$answer->Author' date='".format_date($DateFormat,$answer->PostTime)."'>$str</answer>";
        }
      }
    }

  return array(XML=>$result);
  }


function PostQuestion ($args)
  {
  extract(param_extract(array(
    BindTo=>"string",
    Question=>"string",
    Author=>"string",
    ),$args));
  global $MachineKey;
#    $Author=$_POST['Author'];
#    $Question=$_POST['Question'];
#    $BindTo=$_POST['BindTo'];

  $Question=strip_tags($Question);
  $Author=strip_tags($Author);
  $PostID=DBGetID("msg.Message");
  $now=time();
  $IPaddr=$_SERVER[REMOTE_ADDR];
  DBExec ("INSERT INTO msg_Posts (PostID,BindTo,Author,MsgText,IPaddr,PostTime,ParentID) VALUES
    ($PostID,'$BindTo','$Author','$Question','$IPaddr',$now,0)");
  $MachineKey=DBEscape($MachineKey);
  DBExec ("REPLACE INTO msg_KnownAuthors (MachineKey,Author) VALUES ('$MachineKey','$Author')");
/*    $f=fopen ("c:/zzz.txt","w");
  fputs($f,$MachineKey."\n".$Author."\n-------------\n");
  fputs($f,$Question."-----------".$BindTo);
  fclose ($f);
*/
  }

function View ($args)
  {
  $_=&$GLOBALS['_STRINGS']['msg'];

  extract(param_extract(array(
    BindTo=>"string",
    Moderator=>"int",
    PageNo=>"int",
    CSS_Question=>"string",
    ShowOnlyTotals=>"int",
    YourName=>'string='.$_['YOURNAME'],
    YourMessage=>'string='.$_['YOURMESSAGE'],
    ImageQ=>'string=qna_q.gif',
    ImageA=>'string=qna_a.gif',
    HideDate=>'int',
    DateFormat=>'string=normaldate',

    ),$args));

  global $cfg,$_THEME_NAME;
  $this->IPaddr=$_SERVER['REMOTE_ADDR'];
  $QuarantineTime=intval($cfg['Settings']['msg']['QuarantineTime'])*60*60;
  $RowsPerPage=intval($cfg['Settings']['msg']['QnA_RowCount']);
  $timeago=time()-$QuarantineTime;
  $qlimit="BindTo='$BindTo' ";

  if (!$EditMode)
    {
    $qlimit.=" AND (Approved=1 OR PostTime<$timeago OR IPaddr='$this->IPaddr')";
    }

  $qlimit1=$qlimit;
  if ($qlimit1) {$qlimit1.="AND ";}
  $qlimit1.="ParentID=0";
  if ($ShowOnlyTotals)
    {
    $Control->qlimit=&$qlimit;
    return;
    }
  $qc=DBQuery ("SELECT COUNT(*) AS RowCount FROM msg_Posts WHERE $qlimit1");
  $RowCount=$qc->Top->RowCount;
  if ($RowCount)
    {
    $PageCount=ceil($RowCount/$RowsPerPage);

    if (!$PageNo) {$PageNo=1;}
    if ($PageNo>$PageCount) $PageNo=$PageCount;
    $i1=($PageNo-1)*$RowsPerPage;

    $s="SELECT * FROM msg_Posts WHERE $qlimit1 ORDER BY PostTime DESC LIMIT $i1,$RowsPerPage";
    $this->qroots=DBQuery ($s,"PostID");
    if ($this->qroots)
      {
      $rootlist=implode(",",array_keys($this->qroots->Rows));
      if ($qlimit) $qlimit.=" AND ";
      $qlimit.=" ParentID IN ($rootlist)";
      $s="SELECT * FROM msg_Posts WHERE $qlimit ORDER BY PostTime";
      $this->qans=DBQuery ($s,"ParentID");
      }


    if ($ShowOnlyTotals)
      {
      $qcc=DBQuery ("SELECT COUNT(*) AS RowCount,SUM(Answered) AS Answered FROM msg_Posts WHERE $Control->qlimit");
      $Total=$qcc->Top->RowCount;
      $Answered=$qcc->Top->Answered;
      print "Total messages: $Total<br>";
      print "Unanswered: ".($Total-$Answered);
      $inf=BindPathInfo($URL_QnaList);
      $URL_QnaList=$GLOBALS['_HOMEURL']."/".$inf->Context."/".$inf->ID.".".$cfg['VirtualExtension'];
      print "<br><a href='$URL_QnaList?BindTo=$Control->BindTo'>Read message</a>";
      return;
      }

    print "<form name='form_edit_messages' method='post' action='".ActionURL("msg.IPost.Modify.f")."'>";
    if ($this->qroots)
      {
      print "<table>";
      foreach ($this->qroots->Rows as $PostID=>$row)
        {
        print "<tr valign='top'><td><img src='$cfg[SkinsURL]/$_THEME_NAME/$ImageQ'/>";

        if ($Moderator)
          {
          print "<br><input type='checkbox' name='check[$PostID]' value='1'>";
          }
        print "</td><td>";
        list($t,$c)=get_css_pair($CSS_Question,'p');
        $str="$Text_QuestionBy <b>$row->Author</b>";
        if (!$HideDate) $str.=" <i>(".format_date($DateFormat,$row->PostTime).")</i>";
        $str.="<br/>".nl2br($row->MsgText);
        if ((!$row->Approved)&&($Moderator)&&($row->PostTime>$timeago))
          {
          $str="<font color='#e0e0e0'><b>$_[UNDER_QUARANTINE]</b><br>$str</font>";
          }
        print "<$t$c>$str</$t>";
        if (($Moderator)||( ($row->UserID !=0)&&($_USER->UserID==$row->UserID) ) || (($row->UserID==0)&&($row->IPaddr==$this->IPaddr ))) {
          print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.f",array(PostID=>$PostID))."\",w:420,h:250,reloadOnOk:1})'>$_[EDIT_MESSAGE]</a>";
          }

        $answer=&$this->qans->Rows[$PostID];
        if ($answer)
          {
          list($t,$c)=get_css_pair($CSS_Answer,'p');

          print "</td></tr><tr valign='top'><td><img src='$cfg[SkinsURL]/$_THEME_NAME/$ImageA'/></td><td><$t$c><i>$Text_AnswerBy</i> <b>".$answer->Author.":</b> ".nl2br($answer->MsgText)."</$t>";
          if ($Moderator) {print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.f",array(PostID=>$answer->PostID))."\",w:420,h:250,reloadOnOk:1})'>$_[EDIT_MESSAGE]</a>";}
          }
        else
          {
          if ($Moderator) {print "<p><a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.f",array(AnswerTo=>$PostID))."\",w:420,h:350,reloadOnOk:1})'>$_[WRITE_ANSWER]</a></p>";}
          }

        print "</td></tr><tr><td colspan='2'><hr></td></tr>";


        }
      if ($Moderator)
        {
        print "<tr><td colspan='2'>";
        $_ENV->PutButton(array(Kind=>'delete',OnClick=>"form_edit_messages.action.value='delete';form_edit_messages.submit();"));

#        <input class='button' onClick='this.disabled=1; form_edit_messages.action.value=\"delete\";form_edit_messages.submit();' type='button' value='$__[CAPTION_DELETE]'>
#        <input class='button' onClick='this.disabled=1; form_edit_messages.action.value=\"approve\";form_edit_messages.submit();' type='button' value='$__[CAPTION_SHOW]'>
        print "</td></tr>";
        }
      print "</table>";
      }
    else
      {
      print "No messages";
      }

    }

  if ($Moderator)
    {
    print "<input type='hidden' name='action'><input type='hidden' name='BindTo' value='$Control->BindTo'></form>";
    }

  $FormTarget=ActionURL("msg.IPost.Post.f");
  $InputAuthor='';
  if ($_USER->UserID)
    {
    $InputAuthor=$_USER->Login;
    }
  else
    {
    $InputAuthor="<input type='text' class='inputarea' maxlength='80' size='20' name='Author'>";
    }

  if ($Moderator)
    {
    $InputAuthor="<input type='text' class='inputarea' maxlength='80' size='20' name='Author' value='$_USER->Login'>";
    }
  list($t,$c)=get_css_pair($CSS_Question,'p');
  print "<form name='form_postmessage' method='post' action='".$FormTarget."' onSubmit='return CheckForm();'><table border='0'>
    <tr valign='top'><td><$t$c>$YourName</$t></td><td>$InputAuthor</td></tr>
    <tr valign='top'><td><$t$c>$YourMessage</$t></td>
    <td><textarea class='inputarea' type='text' name='MsgText' cols='40' rows='8'></textarea></td></tr>
    <tr><td></td><td><input type='submit'>";
    $_ENV->PutButton(array(Action=>"submit",Name=>"submit"));
    print "</td></tr></table>
    <input type='hidden' name='BindTo' value='$BindTo'>
    <input type='hidden' name='call' value='modal'>
    </form>


    <script>
    function CheckForm()
      {
      if ((form_postmessage.Author) && (form_postmessage.Author.value==''))
        {
        alert ('Enter your name');
        return false;
        }
      if (form_postmessage.MsgText.value=='') {alert ('Type in your message'); return false;}
      form_postmessage.submit.disabled=true;
      form_postmessage.target=W.openModal({reloadOnOk:1});
      return true;
      }
    </script>
    ";




  }

}

?>
