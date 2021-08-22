<?php
class msg_TRateit
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Message cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

var $Subscribers="BindTo";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS][msg];
#  $this->About=$_[RATEIT_ABOUT];
  $this->Propdefs=array(
    BindTo=>array(Type=>"Binding",DataType=>"Object",Caption=>$_['MESSAGES_BINDTO']),
    CSS_Text=>array(Type=>"CSS_Class",Caption=>$_['CSSTEXT'],BaseCSSClass=>"p",DefaultValue=>"p"),
    CSS_Messages=>array(Type=>"CSS_Class",Caption=>$_['CSSTEXT'],BaseCSSClass=>"p",DefaultValue=>"p"),
    InputRate=>array(Type=>"Boolean",Caption=>$_['RATEIT_SHOWINPUTRATE'],DefaultValue=>'true'),
    YourName=>array(Type=>"Caption",DefaultValue=>$_['YOURNAME']),
    YourMessage=>array(Type=>"Caption",DefaultValue=>$_['YOURMESSAGE']),
    YouHaveVoted=>array(Type=>"Caption",DefaultValue=>$_['RATE_YOUHAVEVOTED']),
    Align=>array(Type=>"Align"),

    OpenLastPage=>array(Type=>"Boolean"),
    );
  $this->Datadefs=array(
    Pages =>array(DataType=>"Pages",Caption=>"RateIt Pages"),
    RateTotal=>array(DataType=>"String",Caption=>"RateIt total info"),
    );
  }

function AfterInit (&$Control)
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $_USER,$_SESSION,$cfg;
  $Control->RowsPerPage=intval($cfg['Settings']['msg']['MessagesRowCount']);
  $Control->DateFormat=$cfg['Settings']['msg']['DateFormat'];
  if (!$Control->RowsPerPage) $Control->RowsPerPage=20;
  $Control->PageNo=intval($Control->Arguments['p']);

  extract ($Control->Properties);

  if ($Control->SysContext=='layouts') return;
  if (!$Control->BindTo) return array(Error=>"Bind error. I see no object to bind to");

  if ($_USER->HasRole("msg:Moderator"))
    {
    $Control->Moderator=true;
    }
  $Control->IPaddr=$_SERVER['REMOTE_ADDR'];
  $Control->timeago=time()-intval($cfg['Settings']['msg']['QuarantineTime'])*60*60;
  $qlimit="BindTo='$Control->BindTo'";
  if (!$Control->Moderator) { $qlimit.=" AND (Approved=1 OR PostTime<$Control->timeago OR IPaddr='$Control->IPaddr')";   }

  $Control->RateCounter=0;
  $Control->RateAvg=0;

  # acquire rate of object (Rate>0)
  $qc=DBQuery ("SELECT AVG(Rate) as avg,COUNT(*) as counter FROM msg_Rateit WHERE $qlimit AND Rate>0");
  if ($qc)
    {
    $Control->RateAvg=$qc->Top->avg;
    $Control->RateCounter=$qc->Top->counter;
    if ($Control->RateCounter>0)
      {
      if ($Control->RateCounter>5)
        {
        $s=sprintf ($_['RATE_INFO'],$Control->RateCounter, $Control->RateAvg);
        }
      else
        {
        $s=sprintf ($_['RATE_TOO_SMALL'],$Control->RateCounter);
        }
      $Control->Data['RateTotal']=$s;
      }
    }

  # does visitor rated this object
  $s="SELECT COUNT(IPaddr) AS Counter FROM msg_Rateit
  WHERE BindTo='$Control->BindTo' AND IPaddr='$Control->IPaddr' AND Rate>0";
  $qv=DBQuery ($s);

  $qa=DBQuery ("SELECT Author FROM msg_KnownAuthors WHERE MachineKey='$_SESSION->MachineKey'");
  if (($qv)&&($qv->Top->Counter!=0))
    {
    $Control->UserHasVoted=1;
    }
  if ($qa) $Control->PrefferedName=$qa->Top->Author;

  # acquire count of all messages either rated or not
  $s="SELECT COUNT(*) AS RowCount FROM msg_Rateit WHERE $qlimit";
  $qc=DBQuery ($s);
  if (($qc)&&($qc->Top->RowCount))
    {
    $Control->RowCount=$qc->Top->RowCount;
    $Control->PageCount=ceil($Control->RowCount / $Control->RowsPerPage);
    if (!$Control->PageNo) {if ($OpenLastPage) $Control->PageNo=$Control->PageCount;}
    if ($Control->PageNo>$Control->PageCount) $Control->PageNo=$Control->PageCount;
    $Control->Data['Pages']=array(PageCount=>$Control->PageCount,PageNo=>$Control->PageNo,JSBPageControlID=>$Control->JSBPageControlID);
    }
  if (!$Control->PageNo) $Control->PageNo=1;

  $i1=$Control->RowsPerPage*($Control->PageNo-1);
  $Control->q=DBQuery ("SELECT * FROM msg_Rateit WHERE $qlimit ORDER BY PostTime LIMIT $i1,$Control->RowsPerPage","RateMsgID");
  }

function Render(&$Control)
  {
  $_ =&$GLOBALS['_STRINGS']['msg'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $_USER,$cfg;
  print $_LANGUAGE;
  extract ($Control->Properties);

  if ($Align) {$Align=" align='$Align'";}
  list($t,$c)=get_css_pair($CSS_Text,'span');
  list($tm,$cm)=get_css_pair($CSS_Messages,'span');

  if ($Control->DesignMode)
    {
    if (!$BindTo)
      {
      print "No object that bound to Rateit";
      return;
      }
    }
  else
    {
    if (!$Control->BindTo) return;
    }

  $FormTarget="";
  if ($Control->UserHasVoted)
    {
    $InputRate=false;
    print $YouHaveVoted;
    }


  if ($Control->Moderator)
    {
    print "<h3>$_[MESSAGE_MODERATOR]</h3>";
    }
  if (!$Control->DesignMode)
    {
    if ($Control->Moderator)
      {
      print "<form name='form_edit_rates' method='post' action='".ActionURL("msg.IRateit.Modify.f")."'>";
      }

    # acquire all messages of current page

    $DateFormat=$cfg['Settings']['msg']['DateFormat'];

    if ($Control->q)
      {
      print "<table>";
      foreach ($Control->q->Rows as $RateMsgID=>$row)
        {
        print "<tr valign='top'><td>";
        $Rate=$row->Rate;

        if ($Rate)
          {
          $sr="";
          for ($i=0;$i<$Rate;$i++) {$sr.="�";}
          $sr="<font color='red' face='wingdings'>$sr</font>";
          if ($i<5) {
            $sr.="<font color='#8f8f8f' face='wingdings'>";
            for (;$i<5;$i++) {$sr.="�";}
            $sr.="</font>";
            }
          print $sr."<br>";
          }

        if ($Control->Moderator)
          {
          print "<input type='checkbox' name='check[$RateMsgID]' value='1'></td><td>";
          }


        if ((!$row->Approved)&&($Control->Moderator)&&($row->PostTime > $Control->timeago))
          {
          print "<font color='red'>";
          }

        print "<$tm$cm><b>$row->Author</b> <i>(".format_date($DateFormat,$row->PostTime).")</i><br>$row->MsgText</$tm>";
        print "</td></tr>";
        }
      if ($Control->Moderator)
        {
        print "<tr><td colspan='2'>
        <input class='button' onClick='this.disabled=1; form_edit_rates.action.value=\"delete\";form_edit_rates.submit();' type='button' value='$__[CAPTION_DELETE]'>
        <input class='button' onClick='this.disabled=1; form_edit_rates.action.value=\"approve\";form_edit_rates.submit();' type='button' value='$__[CAPTION_SHOW]'> </td></tr>";
        }
      print "</table>";
      }
    if ($Control->Moderator)
      {
      print "<input type='hidden' name='action'><input type='hidden' name='BindTo' value='$Control->BindTo'></form>";
      }

    $FormTarget=ActionURL("msg.IRateit.Post.f");
    } # if ! $Control->DesignMode


  $rates=explode ("|",$_['RATEIT_RATES']);
  $rs="";
  foreach ($rates as $i=>$s)
    {
    $j=$i+1;
    $rs.="<option value='$j'>$j - $s</option>";
    }

  $yourname='';
  if (($_USER->UserID)&&(!$Control->Moderator))
    {
    $yourname=$_USER->Login;
    }
  else
    {
    $yourname="<input type='text' class='inputarea' maxlength='30' size='20' name='Author' value='$Control->PrefferedName'>";
    }

  print "<script>
  function checkmsg(f)
  {
  form_postrate.submitbtn.disabled=(f.value=='') ;
  }</script><form name='form_postrate' method='post' action='".$FormTarget."'>";
  print "<table border='0'><tr><td align='right'><$t$c>$YourName</$t></td><td><$t$c>$yourname</$t></td></tr>";
  if ($InputRate)
    {
    $rs="<select class='inputarea' name='Rate'><option value='0'>$_[RATEIT_YOURRATE]</option>$rs</select>";
    print "<tr><td></td><td>$rs</td></tr>";
    }
  print "<tr valign='top'><td align='right'><$t$c>$YourMessage</$t></td>
  <td><textarea onKeyUp='checkmsg(this)' onChange='checkmsg(this)' class='inputarea' type='text' name='MsgText' cols='40' rows='5'></textarea></td></tr>
    <tr><td></td><td>";
  $_ENV->PutButton(array(Disabled=>1,AutoHide=>1,Name=>'submitbtn',Action=>'submit'));
  print "</td></tr></table><input type='hidden' name='BindTo' value='$Control->BindTo'></form>";

  return array(DisableCache=>true);
  }
}
?>
