<?
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }

class msg_PPoll
  {
  function msg_PPoll()
    {
    $_=&$GLOBALS[_STRINGS][msg];

    $this->CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
    $this->CopyrightURL="http://www.jhazz.com/jsb";
    $this->ComponentVersion="1.0";
    }

  function Vote($args)
    {
    extract(param_extract(array(
      PollID=>"int",
      PollAnswerID=>"int",
      ),$args));

    global $_SESSION;
    $this->makeVote($PollID, $PollAnswerID, $_SESSION->MachineKey);

    $qp=DBQuery ("SELECT * FROM msg_Polls WHERE PollID=$PollID");
    $q=DBQuery ("SELECT * FROM msg_PollAnswers WHERE PollID=$PollID ORDER BY PollAnswerID","PollAnswerID");

    print "<meta name='Content-Type' content='text/html; charset=utf-8'><title>Результаты голосования</title><table cellpadding='20'><tr><td><h3>".$qp->Top->Question."</h3><br/><table width='100%'>";
    $total=0;
    $max=0;
    foreach ($q->Rows as $answerid=>$row)
      {
      $c=intval($row->VoteCount);
      $total+=$c;
      if ($c>$max) {$max=$c;}
      }
    foreach ($q->Rows as $answerid=>$row)
      {
      $c=intval($row->VoteCount);
      print "<tr valign='top'><td align='right' width='1%'>".round(($c/$total)*100)."%</td><td><table width='".round(($c/$max)*100)."%'><tr><td bgcolor='#ff8000'>&nbsp;</td></tr></table>$row->AnswerText<br></td></tr>";
      }
    print "</table><br><a href='javascript:window.close()'>Закрыть окно</a></td></tr></table>";
    }

  function XML_LoadRandomPoll($args)
    {
    global $MachineKey;
    $qdone=DBQuery ("SELECT PollID FROM msg_PollAnswerHosts WHERE MachineKey='$MachineKey'","PollID");
    $s="SELECT PollID FROM msg_Polls";
    if ($qdone)
      {
      $s.=" WHERE PollID NOT IN (".implode (",",array_keys($qdone->Rows)).")";
      }
    $qpossible=DBQuery ($s);
    if (!$qpossible) {return;}
    $i=mt_rand(0,$qpossible->RowCount-1);
    $row=$qpossible->Rows[$i];
    $PollID=$row->PollID;

    $qp=DBQuery ("SELECT * FROM msg_Polls WHERE PollID=$PollID");
    $qa=DBQuery ("SELECT PollAnswerID,AnswerText,VoteCount FROM msg_PollAnswers WHERE PollID=$PollID","PollAnswerID");
    if ((!$qp)||(!$qa)) {return array (XML=>"<error>No poll found</error>");}
    $question=$qp->Top->Question;
    $result="<question PollID='$PollID'>$question</question>";
    foreach ($qa->Rows as $PollAnswerID=>$row)
      {
      $result.="<answer PollAnswerID='$PollAnswerID' count='$row->VoteCount'>$row->AnswerText</answer>";
      }
    return array (XML=>$result);
    }

  function makeVote($PollID, $PollAnswerID, $MachineKey)
    {
    $qhas=DBQuery ("SELECT PollID FROM msg_PollAnswerHosts WHERE MachineKey='$MachineKey' AND PollID=$PollID");
    if ($qhas) return false;

    if ($PollID && $PollAnswerID) {
      DBExec ("UPDATE msg_PollAnswers SET VoteCount=VoteCount+1 WHERE PollAnswerID=$PollAnswerID AND PollID=$PollID");
      DBExec ("REPLACE INTO msg_PollAnswerHosts (PollID,MachineKey) VALUES ($PollID,'$MachineKey')");
      }
    }

  function XML_PostVote ($args)
    {
    extract(param_extract(array(
      PollID=>"int",
      PollAnswerID=>"int",
      ),$args));

    global $MachineKey;
    $this->makeVote($PollID, $PollAnswerID, $MachineKey);
    $f=fopen ("c:/z1.txt","w");
    fputs($f,"$PollID, PollAnswerID, $MachineKey");
    fclose ($f);
    }
  }

?>
