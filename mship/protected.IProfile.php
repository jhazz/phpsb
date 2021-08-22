<?
class mship_IProfile
{
var $CopyrightText="(c)2006 JhAZZ Site Builder. Membership system";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(
  EditOwnProfile=>"Edit,Update,GrantAccess",
  MultipleProfiles=>"Add,Edit,Update,GrantAccess",
  );

function mship_IProfile()
  {
  $_=&$GLOBALS['_STRINGS']['mship'];
  $this->Title=$_[TITLE_MYOFFICE];

  }

function Add($args)
  {

  }

function Edit($args)
  {

  extract(param_extract(array(
    MemberID=>'int',
    MemberData=>'object',
    URL_Success=>'string',
    call=>'string'
    ),$args));
  global $_USER,$cfg;
  $_=&$GLOBALS['_STRINGS']['mship'];

  $input_class=" class='inputarea' ";
  if (!$MemberData)
    {
    $s="";
    if (!$_USER->HasRole("mship:Moderator")) $s="OwnerUserID=$_USER->UserID AND";
    $s="SELECT * FROM mship_MemberInfo WHERE $s MemberID=$MemberID";
    $MemberData=DBQuery ($s,"MemberID");
    $Member=$MemberData->Top;
    }
  if ($MemberData)
    {
    $Member=$MemberData->Rows[$MemberID];
    $MemberCatalogID=$Member->CatalogID;
    $orclause=" OR JSBPageID=$MemberCatalogID";
    }

  print "<table width='100%' cellpadding='10'><tr><td align=center>";
  if ($Member)
    {
    print "<h1>$_[EDIT_PROFILE]</h1>";
    if($Member->ShowIt) {print $_['PROFILE_IS_PUBLISHED'];}
    else {print $_['PROFILE_UNDER_REVIEW'];}
    }
  else
    {
    print "<h1>$_[ADD_PROFILE]</h1>";
    }


  $Context=$cfg['Settings']['mship']['CatalogContext'];
  $qc=DBQuery ("SELECT JSBPageID,Caption,State FROM jsb_Pages
  WHERE SysContext='$Context' AND (State=1 $orclause) ORDER BY OrderNo","JSBPageID");

  $cs="";
  if ($qc)
    {
    $cs="";
    foreach ($qc->Rows as $aCatalogID=>$c)
      {
      $sl=($MemberCatalogID===$aCatalogID)?" selected ":"";
      $cs.="<option value='$aCatalogID' $sl>".langstr_get($c->Caption)."</option>";
      }
    $cs="<select $input_class name='CatalogID'><optgroup label='$_[MEMBER_SELECT_YOUR_SECTION]'>$cs</optgroup></select>
    <br><br>$_[MEMBER_PREFERRED_SECTION]:
    <input $input_class name='NewCatalogName' onChange='form.CatalogID.style.visible=false; form.CatalogID.value=-1;' maxlength='30' size='30' value=''>";
    }
  else
    {
    $cs="<br>$_[MEMBER_PREFERRED_SECTION]: <input $input_class name='NewCatalogName' value=''>";
    }
  ?>
  <script>
  function checkForm(form)
    {
    if (form.Name.value=="")
      {
      alert ("<? print $_['WARNING_NOPROFILENAME']; ?>");
      form.Name.focus();
      return false;
      }

    if ((form.CatalogID.value==-1)&&(form.NewCatalogName.value==""))
      {
      alert ("<? print $_['WARNING_NOTSELECTEDCATEGORY']; ?>");
      form.CatalogID.focus();
      return false;
      }
    return true;
    }
  </script>

  <?
  print "<form method='post' onSubmit='return checkForm(this)' action='".ActionURL("mship.IProfile.Update.f",array(call=>$call))."'>
    $message
    <table >
    <tr valign='top'><td $text_class align='right'>$_[MEMBER_CATALOGSECTION]:</td><td>$cs</td></tr>
    <tr valign='top'><td></td><td $text_class >$_[MEMBER_NAME]:<br><input type='text' name='Name' $input_class maxlength='100' size='40' value='$Member->Name'></td></tr>
    <tr valign='top'><td></td><td $text_class >$_[MEMBER_SUBTITLE]:<br><input type='text' name='Subtitle' $input_class maxlength='50' size='40' value='$Member->Subtitle'></td></tr>
    <tr valign='top'><td $text_class align='right'>$_[MEMBER_STREETADDRESS]:</td><td><input type='text' name='StreetAddress' $input_class maxlength='200' size='40' value='$Member->StreetAddress'></td></tr>
    <tr valign='top'><td $text_class align='right'>$_[MEMBER_PHONE]:</td><td><input type='text' name='Phone' $input_class maxlength='200' size='40' value='$Member->Phone'></td></tr>
    <tr valign='top'><td $text_class align='right'>$_[MEMBER_WEBSITE]:</td> <td><input type='text' name='Website' $input_class maxlength='100' size='20' value='$Member->Website'></td></tr>
    <tr valign='top'><td $text_class align='right'>$_[MEMBER_EMAIL]:</td><td><input type='text' name='Email' $input_class maxlength='100' size='20' value='$Member->Email'></td></tr>
    <tr valign='top'><td></td><td $text_class >$_[MEMBER_CONTACTPERSON]:<br><input type='text' name='ContactPerson' $input_class maxlength='100' size='20' value='$Member->ContactPerson'></td></tr>
    </table>

    <table width='100%'>
    <tr valign='top'><td>";
    if ($Member)
      {
      $AURL_Success=$GLOBALS[_HOMEURL]."/".$Control->SysContext."/".$Control->JSBPageID.".".$cfg['VirtualExtension'];
      print "<br>";
     $_ENV->PutButton(array(Kind=>'delete',Caption=>$_['REMOVE_THIS_PROFILE']
       ,OnClick=>"if (confirm('Are you sure you want remove this profile?')) {location.href='"
        .ActionURL ("mship.IProfile.Delete.f",array(MemberID=>$Member->MemberID,URL_Success=>$AURL_Success))."';}"));
      }
    print "</td><td align='right'><br>";
    $_ENV->PutButton('submit'); $_ENV->PutButton('cancel');
    print "</td></tr></table>";
    if (!$Member)
      {
      print "<br><i>$_[PROFILE_UNDER_REVIEW_HINT]</i><br><br>";
      }
    print "<input type='hidden' name='MemberID' value='$Member->MemberID'>
    <input type='hidden' name='URL_Success' value='$URL_Success'>
    </form>";

  }

function Update($args)
  {
  $_=&$GLOBALS[_STRINGS][um];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg,$_USER;
  extract(param_extract(array(
    Name=>'trimstring',
    NewCatalogName=>'trimstring',
    Email=>'trimstring',
    StreetAddress=>'trimstring',
    Website=>'trimstring',
    Phone=>'trimstring',
    ContactPerson=>'trimstring',
    CatalogID=>'int',
    MemberID=>'int',
    Subtitle=>'string',
    URL_Success=>'string',
    call=>'string'
    ),$args));

  if (!$URL_Success) {$URL_Success=$_SERVER[HTTP_REFERER];}
  if (!$_USER->UserID)
    {
    return array(Alert=>"You are not authorized",Back=>1);
    }
  if (!$Name)
    {
    return array(Alert=>"You have not entered profile name",Back=>1);
    }
  if ((!$NewCatalogName)&&(!$CatalogID))
    {
    return array(Alert=>"Please select catalog section or type new one",Back=>1);
    }

  $Context=$cfg['Settings']['mship']['CatalogContext'];
  if ($NewCatalogName)
    {
    $qe=DBQuery ("SELECT JSBPageID FROM jsb_Pages WHERE SysContext='$Context' AND Caption='$NewCatalogName'");
    if ($qe)
      {
      $CatalogID=$qe->Top->JSBPageID;
      }
    else
      {
      $CatalogID=DBGetID("mship.Catalog");
      DBExec ("INSERT INTO jsb_Pages (JSBPageID,Caption,State,SysContext)
      VALUES ($CatalogID,'$NewCatalogName',2,'$Context')");
      }
    }

  if ($MemberID>=1)
    {
    DBExec ("UPDATE mship_MemberInfo
      SET Name='$Name',
        Subtitle='$Subtitle',
        Website='$Website',
        Email='$Email',
        StreetAddress='$StreetAddress',
        Phone='$Phone',
        ContactPerson='$ContactPerson',
        CatalogID=$CatalogID
      WHERE MemberID=$MemberID");
    }
  else
    {
    $MemberID=DBGetID("mship.Member");
    DBExec ("INSERT INTO
  mship_MemberInfo (MemberID,OwnerUserID,Name,Subtitle,Website,Email,StreetAddress,Phone,ContactPerson,CatalogID)
        VALUES ($MemberID,$_USER->UserID,'$Name','$Subtitle','$Website','$Email','$StreetAddress','$Phone','$ContactPerson',$CatalogID)");
    }
  if ($call=='modal') {return array(ModalResult=>true);} else {return array(ForwardTo=>$URL_Success);}
  }

function Delete($args)
  {
  extract(param_extract(array(
    MemberID=>'int',
    URL_Success=>'string',
    call=>'string'  # modal - means that we called inside modal window and should get ModalResult back
    ),$args));
  global $_USER;

  if (!$URL_Success) {$URL_Success=$_SERVER[HTTP_REFERER];}
  $s="UPDATE mship_MemberInfo SET ShowIt=2 WHERE MemberID=$MemberID AND OwnerUserID=$_USER->UserID";
#  print $s;
  DBExec ($s);
#  global $_TRACER;
#  $_TRACER->EnableOutput();
  if ($call=='modal') {return array(ModalResult=>true);} else {return array(ForwardTo=>$URL_Success);}
  }

function GrantAccess($args)
  {
  extract(param_extract(array(
    UserID=>'int',
    ClassName=>'string',
    Context=>'string',
    ObjectID=>'int',
    ForwardTo=>'string',
    ),$args));
  global $_USER;
  if (!$UserID) {$UserID=$_USER->UserID;}
  trace ("Allow access to '$ClassName/$Context/$ObjectID' to $ToUser and ForwardTo '$ForwardTo'");
  $_USER->SetGrant(array(
    ClassName=>$ClassName,
    Context=>$Context,
    ObjectID=>$ObjectID,
    UserID=>$UserID,
    AccessBits=>ACCESS_READ+ACCESS_WRITE+ACCESS_READBOUND+ACCESS_WRITEBOUND+ACCESS_ALLOW_OTHER_WRITE));
  return array(ForwardTo=>$ForwardTo);
  }
}
