<?
class mailbulk_ISubscribers
{
var $CopyrightText="(c)2006 Mail bulk cartridge";
var $CopyrightURL="http://www.jhazz.com/mailbulk";
var $ComponentVersion="1.0";
var $RoleAccess=array(
  EditSubscriberList=>"Browse,EditSubjects,UpdateSubjects"
  );
var $SubjColors=array("#ffa0a0","#ffffa0","#a0ffa0","#a0ffff","#a0a0ff");

function mailbulk_ISubscribers()
  {
  $_=&$GLOBALS['_STRINGS']['mailbulk'];
  $this->Title=$_['IMANAGER_TITLE'];
  }

function tab_Subscriber (&$RcptID,&$row,$fname,$args)
  {
  print "$row->FName $row->LName<br/>$row->Country, $row->State, $Row->City";
  }
function tab_Email(&$RcptID,&$row,$fname,$args)
  {
  print $row->Email;
  }
function tab_Date (&$RcptID,&$row,$fname,$args)
  {
  $s=format_date("shortdate",$row->$fname);
  print $s;
  }
function tab_Subject(&$RcptID,&$row,$fname,$args)
  {
  print ($row->$fname)?"Y":"";
  }
function tab_OnGetCellStyle(&$RcptID,&$row,$fname,$args)
  {
  if (substr($fname,0,7)=='Subject')
    {
    $id=intval(substr($fname,7));
    $bgcolor=$this->SubjColors[$id-1];
    if (($bgcolor)&&($row->$fname)) return " style='background-color:$bgcolor' ";
    }
  }
function tab_Disable(&$RcptID,&$row,$fname,$args)
  {
  if ($row->$fname) print "Disabled";
  }


function Update ($args)
  {
  extract(param_extract(array(
    action=>'string',
    check=>'int_checkboxes',
    subaction=>'string',
    ),$args));
  global $cfg;
  if($check)
    {
    $checks=implode(",",array_keys($check));

    if ($action=='delete')
      {
      DBExec ("DELETE FROM mbulk_Subscribers WHERE RcptID IN ($checks)");
      }

    if (substr($subaction,0,9)=='Subscribe')
      {
      $to=intval(substr($subaction,9));
      DBExec ("UPDATE mbulk_Subscribers SET Subject$to=1 WHERE RcptID IN ($checks)");
      }

    if (substr($subaction,0,11)=='Unsubscribe')
      {
      $to=intval(substr($subaction,11));
      DBExec ("UPDATE mbulk_Subscribers SET Subject$to=0 WHERE RcptID IN ($checks)");
      }

    if ($subaction=='Disable')
      {
      DBExec ("UPDATE mbulk_Subscribers SET Disabled=1 WHERE RcptID IN ($checks)");
      }

    if ($subaction=='Enable')
      {
      DBExec ("UPDATE mbulk_Subscribers SET Disabled=0 WHERE RcptID IN ($checks)");
      }
    }
  return array(ModalResult=>true);
  }

function Browse ($args)
  {
  extract(param_extract(array(
    PageNo=>'int=1',
    RowsPerPage=>'int=20',
    ShowDisabled=>'int',
    vs=>'int_checkboxes',
    vd=>'int',
    ),$args));
  $_=&$GLOBALS[_STRINGS][mbulk];
  global $cfg;

  if (!$PageNo) $PageNo=1;
  $clause=($vd)?"Disabled=1":" Disabled=0";
  if ($vs)
    {
    foreach($vs as $SubjectID=>$x)
      {
      $clause.=(($clause)?" AND ":"")."Subject$SubjectID=1";
      }
    }
  if ($clause) $clause="WHERE $clause ";

  $this->qsubjects=DBQuery("SELECT SubjectID,Caption FROM mbulk_Subjects ORDER BY SubjectID","SubjectID");
  $this->qsc=DBQuery("SELECT COUNT(RcptID) AS SCount FROM mbulk_Subscribers $clause");
  $Count=$this->qsc->Top->SCount;
  $PageCount=ceil($Count/$RowsPerPage);
  if ($PageNo>$PageCount) $PageNo=$PageCount;

  $sal=array(Disable=>"Disable selected",Enable=>"Enable selected");
  $hf=array(PageNo=>$PageNo,vd=>$vd);
  foreach($this->qsubjects->Rows as $SubjectID=>$row)
    {
    $sal["Subscribe$SubjectID"]="Subscribe for '".$row->Caption."'";
    $sal2["Unsubscribe$SubjectID"]="Unsubscribe from '".$row->Caption."'";
    $hf["vs[$SubjectID]"]=$vs[$SubjectID];
    $check=($vs[$SubjectID])?"checked":"";
    $views.="<tr><td bgcolor='".$this->SubjColors[$SubjectID-1]."'>
      <input type='checkbox' name='vs[$SubjectID]' value='1' $check>$SubjectID ".$this->qsubjects->Rows[$SubjectID]->Caption."</td></tr>";
    }
  $sal+=$sal2;


  if ($Count)
    {
    $this->qs=DBQuery("SELECT * FROM mbulk_Subscribers $clause
      ORDER BY RcptID
      LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage","RcptID");
    }

  print "<form name='subscribers' method='get'><table width='100%' border=0><tr valign='bottom'><td>";
  if ($views)
    {
    $check=($vd)?"checked":"";
    $views.="<tr><td><input type='checkbox' name='vd' value='1' $check>$_[SHOW_DISABLED_SUBSCRIBERS]</td></tr>";
    print "Show subscribed for<br><table>$views<tr><td><a href='javascript:subscribers.submit();'>$_[RELOAD_SUBSCRIBERS]</a></td></tr></table>";
    }
  print "</td><td>";
  $_ENV->PutPages(array(PageCount=>$PageCount,PageNo=>$PageNo,ToForm=>'subscribers'));
  print "</td></tr></table></form>";

  if ($Count==0)
    {
    print $_[NO_SUBSCRIBERS_FOUND];
    return;
    }

  $_ENV->PrintTable($this->qs,array(
  ModalWindowURL=>ActionURL("mbulk.ISubscribers.Update.b"),
  HiddenFields=>$hf,
  Fields=>array(
    Email=>"Email",
    Subscriber=>"Subscriber",
    Subscribed=>"Subscribed",
    Unsubscribed=>"Unsubscribed",
    Subject1=>"1",
    Subject2=>"2",
    Subject3=>"3",
    Subject4=>"4",
    Subject5=>"5",
    Disabled=>"D",
    ),
  ShowCheckers=>1,
  FieldHooks=>array(
    Email=>tab_Email,
    Subscriber=>tab_Subscriber,
    Subscribed=>tab_Date,
    Unsubscribed=>tab_Date,
    Subject1=>tab_Subject,
    Subject2=>tab_Subject,
    Subject3=>tab_Subject,
    Subject4=>tab_Subject,
    Subject5=>tab_Subject,
    Disabled=>tab_Disable,
    ),
  ShowDelete=>1,
  TableStyle=>1,
  Width=>'600',
  OnGetCellStyle=>tab_OnGetCellStyle,
  ColAligns=>array(Subscribed=>'center',Unsubscribed=>'center'),
  BgColor_Hovered=>'#fff0f0',
  BgColor_Checked=>'#fff0e0',
  ShowOk=>1,
  SubactionList=>$sal,
  ThisObject=>&$this));


  }

function UpdateSubjects($args)
  {
  foreach ($_POST['Caption'] as $SubjectID=>$NewCaption)
    {
    $SubjectID=intval($SubjectID);
    $NewCaption=DBEscape($NewCaption);
    $Hidden=intval($_POST['Hidden'][$SubjectID]);
    $Recommend=intval($_POST['Recommend'][$SubjectID]);

    $s="UPDATE mbulk_Subjects SET Caption='$NewCaption',Hidden=$Hidden,Recommend=$Recommend WHERE SubjectID=$SubjectID";
    DBExec ($s);
    }

  return array(ModalResult=>true);
  }

function tab_EditField (&$id,&$row,$fname,$args)
  {
  print "<input name='$fname"."[$id]' type='text' style='font-size:10px; width:100%' size='20' maxlength='200' value='".$row->$fname."'/>";
  }
function tab_CheckField (&$id,&$row,$fname,$args)
  {
  print "<input type='checkbox' name='$fname"."[$id]' value=1 ".(($row->$fname)?'checked':'')."/>";
  }
function tab_OnGetCellStyle2(&$id,&$row,$fname,$args)
  {
  $bgcolor=$this->SubjColors[$id-1];
  if ($bgcolor) return " style='background-color:$bgcolor' ";
  }

function EditSubjects($args)
  {
  $this->qs=DBQuery ("SELECT * FROM mbulk_Subjects ORDER BY SubjectID","SubjectID");
  $_ENV->PrintTable($this->qs,array(
    ModalWindowURL=>ActionURL("mbulk.ISubscribers.UpdateSubjects.b"),
    Fields=>array(
      Caption=>"Subscribing subject",
      Recommend=>"Recommend",
      Hidden=>"Hidden from subscribers",
      LastBulkDate=>"Last bulk date"),
    FieldHooks=>array(
      Caption=>tab_EditField,
      Hidden=>tab_CheckField,
      Recommend=>tab_CheckField,
      LastBulkDate=>tab_Date,
      ),
    OnGetCellStyle=>tab_OnGetCellStyle2,
    ColAligns=>array(Hidden=>"center",Recommend=>"center"),
    ShowOk=>true,
    ThisObject=>&$this));

  }

}

