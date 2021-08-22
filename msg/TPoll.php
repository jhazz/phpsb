<?php
class msg_TPoll
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Message cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  $this->About=$_['RATEIT_ABOUT'];
  $this->Propdefs=array(
    CSS_Text=>array(Type=>"CSS_Class",Caption=>$_['CSSTEXT'],BaseCSSClass=>"td",DefaultValue=>"td"),
    Align=>array(Type=>"Align"),
    SendVote=>array(Type=>"Caption",DefaultValue=>$_['POLL_SENDVOTE']),
    );
  }

function Render(&$Control)
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $_USER,$_SESSION;
  extract ($Control->Properties);

  $qa=DBQuery("SELECT PollID FROM msg_PollAnswerHosts WHERE MachineKey='$_SESSION->MachineKey'","PollID");
  if ($qa)
    {
    $exc=" AND PollID NOT IN (".implode(",",array_keys($qa->Rows)).")";
    }
  $now=time();
  $qc=DBQuery ("SELECT PollID,Question FROM msg_Polls WHERE Hidden=0 AND DateBegin<$now AND DateEnd>$now $exc");
  list($t1,$c1)=get_css_pair($CSS_Text,'td');

  if (!$qc) {return;}
  $PollCount=$qc->RowCount;
  $random=mt_rand(0,$PollCount-1);
  $poll=$qc->Rows[$random];

  $qpa=DBQuery ("SELECT * FROM msg_PollAnswers WHERE PollID=$poll->PollID","PollAnswerID");


  ?>
  <script>
  function votepoll()
    {
    var d=new Date, w=300, h=450;
    var wndname=String(d.getHours())+String(d.getMinutes())+String(d.getSeconds());
    var tit="<? print $_['PLEASE_WAIT_VOTING'] ?>";
    OpenedWnd=window.open("",wndname,"dependent=yes,status=no,resizable=no,scrollbars=no,width="+w+",height="+h+",left="+((screen.width-w)/2)+",top="+((screen.height-h)/2));
    d=OpenedWnd.document;
    d.open ();
    d.write ("<body bgcolor='#b0b0b0'><meta name='Content-Type' content='text/html; charset=utf-8'><title>"+tit+"</title><center><br>"+tit);
    d.close();
    poll_form.target=wndname;
    poll_form_div.style.visibility='hidden';
    return true;
    }
  </script>
  <?
  print "<div id='poll_form_div'>
  <form method='post' name='poll_form' action='".ActionURL("msg.PPoll.Vote.f")."' onSubmit='return votepoll();'><table cellspacing='0' cellpadding='0'><tr><$t1$c1 colspan='2'><b>$poll->Question</b></t1></tr>";
  foreach ($qpa->Rows as $PollAnswerID=>$answer) {
    print "<tr valign='top'><td valign='top' align='right'><input onClick='poll_form.bsubmit.disabled=false' name='PollAnswerID' type='radio' value='$PollAnswerID'/></td><$t1$c1>$answer->AnswerText</$t1></tr>";
    }
  print "<tr><td colspan='2' align='center'><input type='submit' name='bsubmit' disabled='true' class='button' value='$SendVote'></td></tr></table>
  <input type='hidden' name='PollID' value='$poll->PollID'>
  </form></div>";
  return array(DisableCache=>true);
  }

}
?>
