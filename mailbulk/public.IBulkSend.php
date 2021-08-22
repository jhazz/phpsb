<?
class mailbulk_IBulkSend
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Image storage cartridge";
var $CopyrightURL="www.phpsb.com/img";
var $ComponentVersion="1.0";
var $RoleAccess=array(
  BulkOperator=>"Prepare,Sending,SendingFrame,GetAddresses"
  );

function Prepare ($args)
  {
  global $cfg;
  $this->qsubjects=DBQuery("SELECT SubjectID,Caption FROM mbulk_Subjects ORDER BY SubjectID","SubjectID");
  print "<form method='post' action='".ActionURL('mbulk.IBulkSend.GetAddresses.bm')."'><h2>Select subjects that visitors subscribed for</h2><table>";
  foreach ($this->qsubjects->Rows as $SubjectID=>$Subject)
    {
    print "<tr><td><input type='checkbox' name='Subjects[$SubjectID]' value='1'>$Subject->Caption</td></tr>";
    }
  print "<tr><td><input type='checkbox' name='Unsubscribed' value='1'>Include unsubscribed</td></tr>";
  print "</table>";
  print "<h2>Select user groups</h2><table>";
  $this->qu=DBQuery ("SELECT GroupID,Caption FROM um_UserGroups","GroupID");
  foreach ($this->qu->Rows as $GroupID=>$Group)
    {
    print "<tr><td><input type='checkbox' name='UserGroups[$GroupID]' value='1'>$Group->Caption</td></tr>";
    }
  print "</table>";
  $_ENV->PutButton (array(Action=>'submit',Caption=>'Get addresses list'));

/*  print "<form>";
  print "<hr><table><tr><td><h2>Bulk send</h2>
    Message subject:<br><input type='text' disabled name='Subject'><br>
    Email address of sender:<br><input type='text' disabled name='Sender' value=''><br>
    Message text:<br>
    <textarea cols='80' rows='10' disabled>This is a sample</textarea></td></tr></table>";
  $_ENV->PutButton (array(Action=>'submit',Caption=>'Send',Disabled=>1));
  */
  }

function GetAddresses($args)
  {
  extract(param_extract(array(
    Subjects=>"int_checkboxes",
    UserGroups=>"int_checkboxes",
    Unsubscribed=>'int',   # include unsubscribed too
    ),$args));
  if ($Subjects)
    {
    foreach($Subjects as $SubjectID=>$x)
      {
      $clause.=(($clause)?" OR ":"")." Subject$SubjectID=1";
      }
    $clause="($clause)";
    if (!$Unsubscribed) {$clause.=" AND Unsubscribed=0 ";}
    $s="SELECT RcptID,Email,FName,LName FROM mbulk_Subscribers WHERE $clause AND Disabled=0 ORDER BY RcptID";
    $q1=DBQuery ($s,"RcptID");
    if ($q1)
      {
      print "<table border='1'><tr><td>";
      foreach ($q1->Rows as $RcptID=>$row)
        {
        $Name=$row->FName;
        if ($row->LName) $Name.=" ".$row->LName;
        $Email=$row->Email;
        if ($Name) $Email="$Name &lt;$Email&gt;";
        print "$Email<br>";
        }
      print "</td></tr></table>";
      }
    }

  if ($UserGroups)
    {
    $ug=implode (",",array_keys($UserGroups));
    $s="SELECT u.UserID,u.Login,u.Email
        FROM um_UserInGroups AS ug INNER JOIN um_Users AS u ON ug.UserID = u.UserID
        WHERE ug.GroupID IN ($ug) ORDER BY UserID";
    $q2=DBQuery ($s,"UserID");
    if ($q2)
      {
      print "<table border='1'><tr><td>";
      foreach ($q2->Rows as $UserID=>$row)
        {
        $Email=$row->Email;
        if ($Email) print "$Email<br>";
        }
      print "</td></tr></table>";
      }
    }
  }

function SendingFrame($args)
  {
  extract(param_extract(array(
    BindTo=>"string",  # regenerates all images bound via BindTo
    ImgID=>"int",      # if ImgID defined: regenerate only one image that bound via BindTo
    FormatID=>"int",   # if FormatID defined: regenerate all images of this format
    OnlyTnFormatNo=>"int", # shrink regeneration of FormatID to only one TnFormatNo
    InFrame=>"int",
    Offset=>"int",
    ),$args));


  print "<font style='font-size:10px'>";
  $EndingTime=time()+3;
  if (!$ImgID)
    {
    if ($Offset<$Total)
      {
      $url=ActionURL("mbulk.IBulkSend.SendingFrame.b",
        array(BindTo=>$BindTo,ImgID=>$ImgID,FormatID=>$FormatID,OnlyTnFormatNo=>$OnlyTnFormatNo,
        InFrame=>1,Offset=>$Offset));
      print "<hr><a href='$url'>Continue</a><script>parent.updateInfo($Offset,$Total,0,'$url');</script>";
      return;
      }
    else
      {
      print "<script>parent.updateInfo ($Total,$Total,1); </script>";
      }
    }
  }


function Sending($args)
  {
  extract(param_extract(array(
    BindTo=>"string",  # regenerates all images bound via BindTo
    ImgID=>"int",      # if ImgID defined: regenerate only one image that bound via BindTo
    FormatID=>"int",   # if FormatID defined: regenerate all images of this format
    OnlyTnFormatNo=>"int", # shrink regeneration of FormatID to only one TnFormatNo
    InFrame=>"int",
    Offset=>"int",
    ),$args));

  global $cfg,$_CORE,$_THEME_NAME;
  $filemode=$cfg['Resources']['files'][2];if (!$filemode)$filemode=0777;
  $_ENV->UnlockTwicePost();
  $_ENV->DropCache();

  ?>
  <center>
  <br>
  <? $_ENV->PutProgress("p1","Images are regenerating"); ?>
  <div id='msg' align='center'></div>
  <script>
  var ErrorsDetected=false;
  Progress_Start("p1");
  W.setErrorHandler(onError);
  function onError(error,details)
    {
    showFrame();
    ErrorsDetected=true;
    document.getElementById('msg').innerHTML="<font color='red'>Error: <b>"+error+"</b></font><br>"+details;
    }
  function showFrame()
    {
    document.getElementById('framecontainer').style.display='block';
    W.setSize(500,460);
    }
  function continueIteration(jump)
    {
    Progress_Continue("p1");
    ErrorsDetected=false;
    document.getElementById('f1').src=jump;
    document.getElementById('msg').innerHTML='';
    }
  function updateInfo (offset,total,complete,jump)
    {
    if ((complete)&&(!ErrorsDetected)) {W.modalResult("ok");return;}
    var s=offset+"/"+total+" images regenerated";
    Progress_NewPos('p1',Math.round(offset/total*100),s);

    if (ErrorsDetected)
      {
      Progress_Pause("p1");
      showFrame();
      s="<br><font color='red'>Errors found!";
      if (!complete) s+="<br><input type='button' class='button' value='Continue' onClick='continueIteration(\""+jump+"\")'></font>";
      document.getElementById('msg').innerHTML=s;
      }
    if (!ErrorsDetected) {continueIteration(jump);}
  //     document.getElementById('msg').innerHTML+="<br>I wish jump to<br>"+jump;
      }
  </script>
  <?
/*    print "<br>
      <div id='framecontainer' style='displ ay:none; text-align:center; width:100%'>
      <iframe id='f1' width='450' height='250' src='".ActionURL("mbulk.IBulkSend.SendingFrame.b",
      array(BindTo=>$BindTo,
      ImgID=>$ImgID,
      FormatID=>$FormatID,
      OnlyTnFormatNo=>$OnlyTnFormatNo,
      InFrame=>1,
      Offset=>$Offset))."'></iframe></div>";
      */
    return;
  }
}
?>
