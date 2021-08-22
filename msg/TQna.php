<?php
class msg_TQna
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Message cartridge";
var $CopyrightURL="http://www.jhazz.com/msg";
var $ComponentVersion="1.0";

var $Subscribers="BindTo";

function InitComponent()
  {
  global $cfg;
  $_=&$GLOBALS['_STRINGS']['msg'];
  $this->About=$_['QNA_ABOUT'];
  $this->Propdefs=array(
    BindToForum=>array(Type=>"InputModal",
      ModalCall=>"jsb.IPage.Select",
      ModalArgs=>array(ContextSelectable=>0,SysContext=>$cfg['Settings']['msg']['ForumContext'],ContextLocked=>1),
      InitCall=>"jsb.IPage.GetPageNameByValue"),
    BindTo=>array(Type=>"Binding",DataType=>"Object",Caption=>$_['MESSAGES_BINDTO']),
    CSS_Question=>array(Type=>"CSS_Class",Caption=>$_['CSSTEXT'],BaseCSSClass=>"p",DefaultValue=>"p"),
    CSS_Answer=>array(Type=>"CSS_Class",BaseCSSClass=>"p",DefaultValue=>"p.small"),
    YourName=>array(Type=>"Caption",DefaultValue=>$_['YOURNAME']),
    YourMessage=>array(Type=>"Caption",DefaultValue=>$_['YOURMESSAGE']),
    YourPhone=>array(Type=>"Caption",DefaultValue=>$_['YOURPHONE']),
    YourEmail=>array(Type=>"Caption",DefaultValue=>$_['YOUREMAIL']),
    NoMessages=>array(Type=>"Caption",DefaultValue=>$_['NO_MESSAGES']),
    Text_QuestionBy=>array(Type=>"Caption",DefaultValue=>$_['QNA_QUESTIONBY']),
    Text_AnswerBy=>array(Type=>"Caption",DefaultValue=>$_['QNA_ANSWERBY']),
    AskGuestPhone=>array(Type=>"Boolean"),
    AskGuestEmail=>array(Type=>"Boolean"),
    HideDate=>array(Type=>"Boolean",Caption=>"Hide date from post title"),
    ImageQ=>array(DefaultValue=>"qna_q.gif",Type=>"InputModal",Editable=>1,InitCall=>"jsb.IThemeReader.GetSkinImageByValue",ModalCall=>"jsb.IThemeReader.SelectSkinImage"),
    ImageA=>array(DefaultValue=>"qna_a.gif",Type=>"InputModal",Editable=>1,InitCall=>"jsb.IThemeReader.GetSkinImageByValue",ModalCall=>"jsb.IThemeReader.SelectSkinImage"),
    ShowOnlyTotals=>array(Type=>"Boolean",Caption=>"Show only totals"),
    
    URL_QnaList=>array(Caption=>"Page that contains qna detail list if only totals shown",Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    Align=>array(Type=>"Align"),
    );
  $this->Datadefs=array(
    Pages =>array(DataType=>"Pages",Caption=>"QnA Pages"),
    );
  }


function AfterInit (&$Control)
  {
  global $_USER,$cfg;
  extract ($Control->Properties);
#  $_ENV->InitWindows();

  if (!$Control->BindTo) {
  	list ($tmp,$Control->BindTo)=explode ("/",$BindToForum);
  	$Control->BindTo="msg.Forum/$Control->BindTo";
  }
  $Control->ForumID=0;
  if ($BindToForum) {
  	$p=BindPathInfo($BindToForum);
  	$Control->ForumID=$p->ID;
  }
	
	if (!$PageNo) {$PageNo=1;}
  $PageNo=$Control->Arguments['p'];
  $Control->IsOwner=false;
  $Control->IsModerator=$_USER->HasRole("msg:Moderator");
  
  if (!$Control->DesignMode)
    {
    $this->IPaddr=$_SERVER['REMOTE_ADDR'];
    $QuarantineTime=intval($cfg['Settings']['msg']['QuarantineTime'])*60*60;
    $RowsPerPage=intval($cfg['Settings']['msg']['MessagesRowCount']);
    $this->timeago=time()-$QuarantineTime;
    $qlimit="BindTo='$Control->BindTo' ";

    if (!$Control->IsModerator)
      {
      $qlimit.=" AND (Approved=1 OR PostTime<$this->timeago OR IPaddr='$this->IPaddr')";
      }
    $qlimit1=$qlimit;
    if ($qlimit1) {$qlimit1.="AND ";}
    $qlimit1.=" ThreadPostID=0 AND IsQna=1";
    
    if ($ShowOnlyTotals)
      {
      $Control->qlimit=&$qlimit;
      return;
      }
    
    $qc=DBQuery ("SELECT COUNT(*) AS RowCount FROM msg_Posts WHERE $qlimit1");
    $RowCount=$qc->Top->RowCount;
    if (!$RowCount) return;

    $PageCount=ceil($RowCount/$RowsPerPage);
    $PageNo=intval($Control->Arguments['p']);

    if (!$PageNo) {$PageNo=1;}
    if ($PageNo>$PageCount) $PageNo=$PageCount;
    $i1=($PageNo-1)*$RowsPerPage;

    $s="SELECT * FROM msg_Posts WHERE $qlimit1 ORDER BY PostID LIMIT $i1,$RowsPerPage";
    $Control->qroots=DBQuery ($s,"PostID");
    if ($Control->qroots)
      {
      $rootlist=implode(",",array_keys($Control->qroots->Rows));
      if ($qlimit) $qlimit.=" AND ";
      $qlimit.=" ParentID IN ($rootlist)";
      $s="SELECT * FROM msg_Posts WHERE $qlimit ORDER BY PostID";
      $Control->qans=DBQuery ($s,"ParentID");
      }
    }
  $Control->Data['Pages']=array(PageCount=>$PageCount,PageNo=>$PageNo,JSBPageControlID=>$Control->JSBPageControlID);

  }

function Render(&$Control)
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $_USER;
  global $cfg;
  global $_THEME_NAME;
  global $_SESSION;
  
  extract ($Control->Properties);

  $DateFormat=$cfg['Settings']['msg']['DateFormat'];
  if ($Align) {$Align=" align='$Align'";}
  if ($Control->DesignMode)
    {
    print "Question&Answers BindTo '$Control->BindTo'";
    }

  if (!$Control->BindTo)
    {
    return;
    }

  if (!$Control->DesignMode)
    {
    if ($ShowOnlyTotals)
      {
      $qcc=DBQuery ("SELECT COUNT(*) AS RowCount,SUM(Answered) AS Answered FROM msg_Posts WHERE $Control->qlimit");
      $Total=$qcc->Top->RowCount;
      $Answered=$qcc->Top->Answered;
      print "Total messages: $Total<br>";
      print "Unanswered: ".($Total-$Answered);
      $inf=BindPathInfo($URL_QnaList);
      $URL_QnaList="$GLOBALS[_HOMEURL]/$inf->Context/$inf->ID.$cfg[VirtualExtension]";
      print "<br><a href='$URL_QnaList?BindTo=$Control->BindTo'>Read message</a>";
      return;
      }

    if ($Control->IsModerator)
      {
      print "<form name='form_edit_messages' method='post' action='".ActionURL("msg.IPost.Modify.f")."'>";
      }

    # acquire all messages of current page
    if ($Control->qroots)
      {
      print "<table>";
      foreach ($Control->qroots->Rows as $PostID=>$row)
        {
        print "<tr valign='top'><td><img src='$cfg[SkinsURL]/$_THEME_NAME/$ImageQ'/>";

        if ($Control->IsModerator)
          {
          print "<br><input type='checkbox' name='check[$PostID]' value='1'>";
          }
        print "</td><td>";
        list($t,$c)=get_css_pair($CSS_Question,'p');
				
        $s=$row->Author;
        if ($Control->IsModerator) {
        	if ($row->Email) $s.="&nbsp;<a href='mailto:$row->Email'>$row->Email</a>";
        	if ($row->Phone) $s.="&nbsp;,tel.:$row->Phone&nbsp;";
        }
        $str="$Text_QuestionBy <b>$s</b>";
        
        
        if (!$HideDate) $str.=" <i>(".format_date($DateFormat,$row->PostTime).")</i>";
        $str.="<br/>".nl2br($row->MsgText);
        
        if ((!$row->Approved)&&($Control->IsModerator)&&($row->PostTime>$this->timeago))
          {
          $str="<font color='#a02020'><b>$_[UNDER_QUARANTINE]</b><br>$str</font>";
          }
        print "<$t$c>$str</$t>";
        if (($Control->IsModerator)||( ($row->UserID !=0)&&($_USER->UserID==$row->UserID) ) || (($row->UserID==0)&&($row->MachineKey==$_SESSION->MachineKey)&&(!$row->Answered))) {
          print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.f",array(PostID=>$PostID))."\",w:720,h:350,reloadOnOk:1})'>$_[EDIT_MESSAGE]</a>";
          }

        $answer=&$Control->qans->Rows[$PostID];
        if ($answer)
          {
          list($t,$c)=get_css_pair($CSS_Answer,'p');

          print "</td></tr><tr valign='top'><td><img src='$cfg[SkinsURL]/$_THEME_NAME/$ImageA'/></td><td><$t$c><i>$Text_AnswerBy</i> <b>".$answer->Author.":</b> ".nl2br($answer->MsgText)."</$t>";
          if ($Control->IsModerator) {print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.f",array(PostID=>$answer->PostID))."\",w:720,h:350,reloadOnOk:1})'>$_[EDIT_MESSAGE]</a>";}
          }
        else
          {
          if ($Control->IsModerator) {print "<p><a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.f",array(AnswerTo=>$PostID))."\",w:720,h:550,reloadOnOk:1})'>$_[WRITE_ANSWER]</a></p>";}
          }

        print "</td></tr><tr><td colspan='2'><hr></td></tr>";


        }
      if ($Control->IsModerator)
        {
        print "<tr><td colspan='2'>
        <input class='button' onClick='this.disabled=1; form_edit_messages.action.value=\"delete\";form_edit_messages.submit();' type='button' value='$__[CAPTION_DELETE]'>
        <input class='button' onClick='this.disabled=1; form_edit_messages.action.value=\"approve\";form_edit_messages.submit();' type='button' value='$__[CAPTION_SHOW]'> </td></tr>";
        }
      print "</table>";
      }
    else
      {print $NoMessages."<hr>";}
    if ($Control->IsModerator)
      {
      print "<input type='hidden' name='action'><input type='hidden' name='BindTo' value='$Control->BindTo'>
      </form>";
      }

    } # if ! $Control->DesignMode

  $_ENV->OpenForm(array(Action=>ActionURL("msg.IPost.Post.f"),Width=>700));
  $_ENV->PutFormField(array(Type=>'hidden',Name=>'IsQna',Value=>1));
  $_ENV->PutFormField(array(Type=>'hidden',Name=>'BindTo',Value=>$Control->BindTo));
  $_ENV->PutFormField(array(Type=>'hidden',Name=>'ForumID',Value=>$Control->ForumID));
  
  if ($_USER->UserID) {
	  if ($Control->IsModerator) {
		  $_ENV->PutFormField(array(Type=>'string',Name=>'Author',Caption=>$YourName,Required=>1));
	  } else {
	  	print $_USER->Login;
	  }
  	
  } else {
	  $_ENV->PutFormField(array(Type=>'string',Name=>'Author',Caption=>$YourName,Required=>1));
	  if ($AskGuestPhone) {$_ENV->PutFormField(array(Type=>'string',Name=>'Phone',Caption=>$YourPhone));}
	  if ($AskGuestEmail) $_ENV->PutFormField(array(Type=>'email',Name=>'Email',Caption=>$YourEmail,Notice=>"Этот адрес будет использован только для связи с Вами и публиковаться на сайте не будет"));
  }
  $_ENV->PutFormField(array(Type=>'text',Size=>70,Rows=>10,Name=>'MsgText',Caption=>$YourMessage,Required=>1));
  $_ENV->CloseForm();	
  
  
/*  
  print "<form name='form_postmessage' method='post' action='".$FormTarget."' onSubmit='return checkpostform();'>
  print "<input type='hidden' name='IsQna' value='1'/>";
  <table border='0'>
    <tr><td><$t$c>$YourName</$t></td><td>$yourname</td></tr>
    <tr valign='top'><td><$t$c>$YourMessage</$t></td>
    <td><textarea class='inputarea' type='text' name='MsgText' cols='40' rows='8'></textarea></td></tr>
    <tr><td></td><td>";

    print "</td></tr></table>
    <input type='hidden' name='BindTo' value='$Control->BindTo'>
    <input type='hidden' name='ForumID' value='$Control->ForumID'>
    </form>
    <script>
    function checkpostform()
      {
      if ((form_postmessage.Author) && (form_postmessage.Author.value==''))
        {
        alert ('Введите имя');
        return false;
        }
      if (form_postmessage.MsgText.value=='') {alert ('Введите сообщение'); return false;}
      form_postmessage.submit.disabled=true;
      return true;
      }
    </script>
    ";
*/
  return array(DisableCache=>true);
  }

}
?>
