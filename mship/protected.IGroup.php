<?
class mship_IGroup {
var $Title="Membership";
var $CopyrightText="(c)2006 JhAZZ Site Builder. Membership system";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(Moderator=>"Browse,DoAction");

function tab_Info(&$MemberID,&$row,$fname,$args)
  {
  $s="";
  if ($row->Subtitle) $s.="<i>$row->Subtitle</i><br/>";

  if ($row->StreetAddress) $s.="$row->StreetAddress ";
  if ($row->Phone) $s.="<b>Phone:</b> $row->Phone";
  if ($row->ContactPerson) $s.=" <b>Contact:</b> $row->ContactPerson";
  $s.="<br>";
  if ($row->Website)
    {
    $www=$row->Website;
    if (substr($www,0,7)!='http://') $www="http://$www";
    $s.="<b>Website:</b> <a href='$www' target='_blank'>$row->Website</a> ";
    }
  if ($row->Email)
    {
    $row->Email;
    $s.="<b>Email:</b> <a href='mailto:$row->Email'>$row->Email</a> ";
    }
  print $s;
  }

function tab_Catalog (&$MemberID,&$row,$fname,$args)
  {
  print langstr_get($this->qpc->Rows[$row->$fname]->Caption);
  }
function tab_EditField (&$MemberID,&$row,$fname,$args)
  {
  print "<input name='$fname"."[$MemberID]' type='text' style='font-size:10px; width:100%' size='20' maxlength='200' value='".$row->$fname."'>";
  }

function tab_ShowIt (&$MemberID,&$row,$fname,$args)
  {
  $__=&$GLOBALS[_STRINGS][_];
  $showit=$row->$fname;
  print "<input name='ShowIt[$MemberID]' type='radio' value='0' ".(($showit==0)?"checked":"").">$__[CAPTION_NO]";
  print "<input name='ShowIt[$MemberID]' type='radio' value='1' ".(($showit==1)?"checked":"").">$__[CAPTION_YES]&nbsp;";
  print "<input name='ShowIt[$MemberID]' type='radio' value='2' ".(($showit==2)?"checked":"").">Deleted";
  }

function tab_User (&$MemberID,&$row,$fname,$args)
  {
  print "<a href='javascript:;' onClick='W.openModal({url:\"".
  ActionURL("um.IUsers.Edit.b",array(UserID=>$row->$fname))."\",w:550,h:450,reloadOnOk:1});'>".
  $this->qu->Rows[$row->$fname]->Login.
  "</a>";
  }

function Explore($args)
  {
  global $cfg;
  Header("Location: ".ActionURL("jsb.ISiteExplorer.Open.n",
    array(Path=>"jsb/".$cfg['Settings']['mship']['CatalogContext']."/",
    ContextLocked=>1,
    NoLayouts=>1)));
  }

function Browse($args)
  {
  global $cfg;
  extract(param_extract(array(
    PageNo=>'int=1',
    CatalogID=>'int',
    PageMode=>'int',
    FilterVisible=>'int',
    RowsPerPage=>'int=20'
    ),$args));

  $_=&$GLOBALS[_STRINGS][mship];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;

  if ($CatalogID)
    {
    $andclause=" AND JSBPageID=$CatalogID";
    $andclause2=" CatalogID=$CatalogID";
    }
  else
    {
    print "<h2>All sections of member's catalogue</h2>";
    }


  $s="SELECT JSBPageID AS CatalogID,Caption,State FROM jsb_Pages WHERE SysContext='".$cfg['Settings']['mship']['CatalogContext']."' $andclause";
  $this->qpc=DBQuery($s,"CatalogID");

  $cname="All section";
  if (($CatalogID)&&($this->qpc))
    {
    $cname=$this->qpc->Rows[$CatalogID]->Caption;
    }
  if ($CatalogID) print "<h2>Section '".langstr_get($cname)."'</h2>";

  if ($CatalogID)
    {
    print "<table border=0 width='100%' cellpadding='10'><tr valign='top' class='bgdown'>";
    $ch=($this->qpc->Top->State!=2)?"checked":"";
    print "<td width='50%'><h3>Section parameters</h3>
    <table border=0><form method='post' onSubmit='this.target=W.openModal({reloadOnOk:1});'
      action='".ActionURL("mship.IGroup.UpdateCatalog.b",array(CatalogID=>$CatalogID))."'><tr><td align='right'>Name:</td><td><input type='text' class='inputarea' name='CatalogCaption' value='$cname'></td></tr>
    <tr><td align='right' nowrap>Visible:</td><td><input type='checkbox' name='Visible' value='1' $ch></td></tr>
    <tr><td colspan='2' align='center'>".$_ENV->PutButton(array(Caption=>'Update',Action=>'submit',ToString=>1))."</td></tr>
    <input type='hidden' name='CatalogID' value='$CatalogID'></form>
    </table>
    </td>";

    print "<td width='50%'><h3>Section image</h3>";
    $imgintf=&$_ENV->LoadInterface("img.IImage");
    $imgintf->View(array(BindTo=>"mship.Catalog/image/$CatalogID",TnFormatNo=>1,ShowCaption=>0,EditMode=>1));
    print "</td>";
    print "</tr></table><br>";
    }


  $this->qu=DBQuery ("SELECT UserID,Login FROM um_Users","UserID");
  $w=$andclause2; if ($w) $w="WHERE $w";
  $qcounts=DBQuery ("SELECT ShowIt,COUNT(*) AS RowCount FROM mship_MemberInfo $w GROUP BY ShowIt","ShowIt");

  if ($andclause2) $andclause2.=" AND ";
  $andclause2.="ShowIt=".$FilterVisible;
  $qc=DBQuery ("SELECT COUNT(*) AS RowCount FROM mship_MemberInfo WHERE $andclause2");

  $FilterVisibleCaptions=array(
    $_['MEMBERSTATE_HIDDEN'],
    $_['MEMBERSTATE_VISIBLE'],
    $_['MEMBERSTATE_SELF_DELETED']);
  $FilterVisibleList=array();

  if ($qcounts)
    {foreach($FilterVisibleCaptions as $v=>$Caption)
      {
      $FilterVisibleList[$v]="<b>[".intval($qcounts->Rows[$v]->RowCount)."]</b>$Caption";
      }
    }

  $SubactionList=array(update=>$_['IMEMBERS_UPDATE'],hide=>$_['IMEMBERS_HIDE'],show=>$_['IMEMBERS_SHOW']);
  if ($this->qpc) foreach ($this->qpc->Rows as $aCatalogID=>$row)
    {
    if (!$aCatalogID) continue;
    $SubactionList["moveto$aCatalogID"]=$_['IMEMBERS_MOVETO']." '".langstr_get($row->Caption)."'";
    }

  $s="SELECT * FROM mship_MemberInfo WHERE $andclause2 GROUP BY MemberID LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage";
  $qpi=DBQuery($s,"MemberID");

  if ($qpi)
    {
    $MemberIDs=implode (",",array_keys($qpi->Rows));
    }

  $args=array(
    Action=>ActionURL("mship.IGroup.DoAction.b"),
    Modal=>1,
    Fields=>array(
      CatalogID=>$_['MEMBER_CATALOGSECTION'],
      Name=>$_['MEMBER_NAME'],
      Info=>"Info",
      ShowIt=>$_['MEMBER_SHOW'],
      OwnerUserID=>$_['MEMBER_OWNERUSERID']
      ),
    FieldHooks=>array(
      CatalogID=>tab_Catalog,
      Info=>tab_Info,
      ShowIt=>tab_ShowIt,
      OwnerUserID=>tab_User
      ),
    FiltersAutoReload=>true,
    Filters=>array(
      array(Caption=>"Choose members to browse",
        Variable=>'FilterVisible',
        Type=>"radio",
        Values=>$FilterVisibleList,
        Value=>$FilterVisible
        ),
      ),
    ShowCheckers=>1,
    ShowDelete=>1,
    ShowOk=>1,
    TableStyle=>1,
    Width=>'100%',
    ButtonEdit=>array(ModalWindowAction=>"mship.IProfile.Edit.b",KeyName=>"MemberID",Width=>550,Height=>450),
    Pages=>array(RowCount=>$qc->Top->RowCount,RowsPerPage=>$RowsPerPage),
    SubactionList=>$SubactionList,
    HiddenFields=>array(CatalogID=>$CatalogID,MemberIDs=>$MemberIDs),
    ColAligns=>array(Edit=>'center',ShowIt=>'center',Removed=>'center'),
    ThisObject=>&$this);
  $_ENV->PrintTable($qpi,$args);
  if ($PageNo==0)
    {print "<hr><h6>All new members registering on the site are hidden until administrator set 'Show member' to 'yes'</h6>";
    }
  }

function DoAction ($args)
  {
  extract(param_extract(array(
    action=>'string',
    check=>'int_checkboxes',
    subaction=>'string',
    PageNo=>'int',
    MemberIDs=>'string',
    ),$args));
  global $cfg;

  if ($check)
    {
    $checkids=implode (",",array_keys($check));
    }

  if ($MemberIDs)
    {
    $ps=explode (",",$MemberIDs);
    foreach ($ps as $MemberID)
      {
      $ShowIt  =intval($args['ShowIt'][$MemberID]);
      $MemberID=intval($MemberID);
      $s="UPDATE mship_MemberInfo SET  ShowIt=$ShowIt  WHERE MemberID=$MemberID";
      DBExec ($s);
      }
    }

  if ($action=="delete")
    {
    DBExec ("DELETE FROM mship_MemberInfo WHERE MemberID IN ($checkids)");
    return array(ModalResult=>true);
    }

  if ($subaction=='show')
    {
    DBExec ("UPDATE mship_MemberInfo SET ShowIt=1 WHERE MemberID IN ($checkids)");
    return array(ModalResult=>true);
    }
  if ($subaction=='hide')
    {
    DBExec ("UPDATE mship_MemberInfo SET ShowIt=0 WHERE MemberID IN ($checkids)");
    return array(ModalResult=>true);
    }
  if (($action=='ok')&&(substr($subaction,0,6)=='moveto'))
    {
    $TargetCatalogID=intval(substr($subaction,6));
    if ($checkids && $TargetCatalogID)
      {
      DBExec ("UPDATE mship_MemberInfo SET CatalogID=$TargetCatalogID WHERE MemberID IN ($checkids)");
      }
    }
  return array(ModalResult=>true);
  }


function UpdateCatalog ($args)
  {
  extract(param_extract(array(
    CatalogCaption=>'string',
    CatalogID=>'int',
    Visible=>'int',
    ),$args));
  global $cfg;

  $State=($Visible)?1:2;
  DBExec ("UPDATE jsb_Pages SET Caption='$CatalogCaption',
    State=$State WHERE JSBPageID=$CatalogID AND SysContext='".$cfg['Settings']['mship']['CatalogContext']."'");

  return array(ModalResult=>true);
  }

}
