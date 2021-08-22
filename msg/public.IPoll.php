<?
class msg_IPoll
  {
  var $RowsPerPage=20;

  function msg_IPoll()
    {
    $_=&$GLOBALS[_STRINGS][msg];

    $this->CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
    $this->CopyrightURL="http://www.jhazz.com/jsb";
    $this->ComponentVersion="1.0";
    }

  function tab_ShowDate(&$RowIndex,&$row,$FieldName,&$this)
    {
    print "<center>";
    if ($row->$FieldName) print format_date("day mon year",$row->$FieldName);
    else print "-";
    print "</center>";
    }

  function tab_ShowIsHidden(&$RowIndex,&$row,$FieldName,&$this)
    {
    $_=&$GLOBALS[_STRINGS][msg];
    if ($row->$FieldName) print $_[POLL_HIDDEN];
    }

  function Browse($args)
    {
    $_=&$GLOBALS[_STRINGS][msg];
    global $cfg;
    extract(param_extract(array(
      PageNo=>"int=1",
      ),$args));

    $q1=DBQuery ("SELECT COUNT(*) AS RowCount FROM msg_Polls");
    $RowCount=$q1->Top->RowCount;
    $PageCount=ceil($RowCount/$this->RowsPerPage);

    if ($PageCount>1)
      {
      $s="";
      for ($i=1;$i<($PageCount+1);$i++)
        {
        if ($i!=$PageNo) {$s.="<a href='?PageNo=$i&EnvironmentMode=$EnvironmentMode'>$i</a> ";} else {$s.="$i ";}
        }
      print "<table width='100%'><tr><td align='right'>$s</td></tr></table>";
      }
    $s="SELECT * FROM msg_Polls ORDER BY DateBegin DESC LIMIT ".($this->RowsPerPage*($PageNo-1)).",".$this->RowsPerPage;
    $q=DBQuery ($s,"PollID");

    $args=array(
    	Action=>ActionURL("msg.IPoll.DoAction"),
      Fields=>array(Question=>$_[POLL_QUESTION],DateBegin=>$_[POLL_DATEBEGIN],DateEnd=>$_[POLL_DATEEND],Hidden=>$_[POLL_HIDDEN]),
      FieldHooks=>array(DateBegin=>tab_ShowDate, DateEnd=>tab_ShowDate,Hidden=>tab_ShowIsHidden),
      FieldHrefs=>array(Question=>ActionURL("msg.IPoll.Edit.bm")."?PollID="),
      ShowCheckers=>true,
      ShowDelete=>true,
      TableStyle=>1,
      ColWidths=>array(DateFrom=>'10%',DateTo=>'10%'),
      BgColor_Hovered=>'#fff0f0',
      BgColor_Checked=>'#fff0e0',
      ThisObject=>&$this);
    $_ENV->PrintTable($q,$args);

    print "<a href='".ActionURL("msg.IPoll.Edit.bm")."'>$_[POLL_ADDPOLL]</a>";
    }

  function Edit ($args)
    {
    $_=&$GLOBALS[_STRINGS][msg];
    $__=&$GLOBALS[_STRINGS][_];
    global $cfg;
    extract(param_extract(array(
      PollID=>"int",
      ),$args));

    print "<h1>$_[POLLS]</h1>";
    if ($PollID)
      {
      $qp=DBQuery ("SELECT * FROM msg_Polls WHERE PollID=$PollID");
      $qa=DBQuery ("SELECT * FROM msg_PollAnswers WHERE PollID=$PollID","PollAnswerID");
      $Question=$qp->Top->Question;
      $DateBegin=getdate($qp->Top->DateBegin);
      $DateEnd=getdate($qp->Top->DateEnd);
      $Hidden=$qp->Top->Hidden;
      }
    else
      {
      $Hidden=0;
      $DateBegin=getdate(time());
      $DateEnd=getdate(time()+60*60*24*30); // 30 days for one poll
      }

    print "<script>var monthNames='$__[SHORT_MONTH_NAMES]';</script>";
    print "<script src='".$cfg['PublicURL']."/sys/TDateField.js'></script>";

    $_ENV->OpenForm(array(Action=>ActionURL("msg.IPoll.Save.n")));
    $_ENV->PutFormField(array(Type=>'hidden',Name=>'PollID',Value=>$PollID));
    print "
        <tr><td>$_[POLL_QUESTION]</td><td><input class='inputarea' name='Question' size='60' maxlength='250' value='$Question'></td></tr>
        <tr><td>$_[POLL_DATEBEGIN]</td><td><script>InputDate('form1','DateBegin',$DateBegin[mday],$DateBegin[mon],$DateBegin[year],0);</script></td></tr>
        <tr><td>$_[POLL_DATEEND]</td><td><script>InputDate('form1','DateEnd',$DateEnd[mday],$DateEnd[mon],$DateEnd[year],0);</script></td></tr>
        <tr valign='top'><td>$_[POLL_ANSWERS]</td><td>";
    if ($qa)
      {
      foreach ($qa->Rows as $PollAnswerID=>$row)
        {
        print "<input type='text' class='inputarea' size='50' name='Answer[$PollAnswerID]' value='$row->AnswerText'> ($row->VoteCount)<br/>";
        }
      print "<hr>";
      }

    for ($i=0;$i<5;$i++)
      {
      print "<input type='text' size='50'  class='inputarea' name='NewAnswer[$i]' value=''><br/>";
      }
    print "<br/><input type='checkbox' name='Hidden' value='1'".(($Hidden)?"checked":"")."> $_[POLL_HIDDEN]";
    $_ENV->CloseForm();
    }

  function Save($args)
    {
    $_=&$GLOBALS[_STRINGS][msg];
    $__=&$GLOBALS[_STRINGS][_];
    global $cfg;
    extract(param_extract(array(
      PollID=>"int",
      Question=>'string',
      Answer=>"array",
      NewAnswer=>"array",
      DateBegin_day=>'string',
      DateBegin_month=>'string',
      DateBegin_year=>'string',
      DateEnd_day=>'string',
      DateEnd_month=>'string',
      DateEnd_year=>'string',
      Hidden=>'int',
      ),$args));

    $DateBegin=mktime(0,0,0,$DateBegin_month,$DateBegin_day,$DateBegin_year);
    $DateEnd  =mktime(0,0,0,$DateEnd_month,$DateEnd_day,$DateEnd_year);
    if ($PollID)
      {
      $s="UPDATE msg_Polls SET Question='$Question',DateBegin=$DateBegin,DateEnd=$DateEnd,Hidden=$Hidden WHERE PollID=$PollID";
      DBExec ($s);
      foreach ($Answer as $PollAnswerID=>$AnswerText)
        {
        $AnswerText=DBEscape($AnswerText);
        $s="UPDATE msg_PollAnswers SET AnswerText='$AnswerText' WHERE PollAnswerID=$PollAnswerID AND PollID=$PollID";
        DBExec ($s);
        }
      }
    else
      {
      $PollID=DBGetID("msg.Poll");
      $s="INSERT INTO msg_Polls (PollID,DateBegin,DateEnd,Question,Hidden) VALUES
        ($PollID,$DateBegin,$DateEnd,'$Question',0)";
      DBExec ($s);
      }

    if ($NewAnswer)
      {
      foreach($NewAnswer as $i=>$value)
        {
        if (!$value) {continue;}
        $value=DBEscape ($value);
        $PollAnswerID=DBGetID("msg.PollAnswer");
        $s="INSERT INTO msg_PollAnswers (PollAnswerID,PollID,AnswerText,VoteCount)
          VALUES ($PollAnswerID,$PollID,'$value',0)";
        DBExec ($s);
        }
      }

#    return array(ForwardTo=>ActionURL("msg.IPoll.Edit.bm",array(PollID=>$PollID)));
    return array(ForwardTo=>ActionURL("msg.IPoll.Browse.bm"));
    }

  function DoAction($args)
    {
    extract(param_extract(array(
      action=>'string',
      check=>'int_checkboxes',
      ),$args));

    if ($check)
      {
      $list=implode (",",array_keys($check));
      DBExec ("DELETE FROM msg_Polls WHERE PollID IN ($list)");
      DBExec ("DELETE FROM msg_PollAnswers WHERE PollID IN ($list)");
      }
    return array(ForwardTo=>ActionURL("msg.IPoll.Browse.bm"));
    }
  }

?>
