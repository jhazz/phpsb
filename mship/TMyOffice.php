<?php
class mship_TMyOffice
{
var $CopyrightText="(c)2006 JhAZZ Site Builder. Membership system";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS][mship];
  $this->Title=$_[TITLE_MYOFFICE];
  $this->Propdefs=array(
    CSS_Text=>array(Type=>"CSS_Class",BaseCSSClass=>"td"),
    CSS_InputArea=>array(Type=>"CSS_Class",BaseCSSClass=>"input",DefaultValue=>"input.inputarea"),
    CSS_Button=>array(Type=>"CSS_Class",BaseCSSClass=>"input",DefaultValue=>"input.button"),
    CSS_Link=>array(Type=>"CSS_Class",BaseCSSClass=>"a",DefaultValue=>"a"),
    CSS_Error=>array(Type=>"CSS_Class"),
    URL_Success=>array (Caption=>"Link to page 'Information saved'",Type=>"InputModal",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    HideInfo=>array(Type=>"Boolean",Caption=>"Hide all information from page"),
    TextInvitation=>array(Type=>"String",DefaultValue=>"You can create your representive profiles and publish on our website. Just click [Create my profile]"),
    TextAddMore=>array(Type=>"String",DefaultValue=>"You may add more profiles"),
    );
  $this->Datadefs=array(
    Qna=>array(DataType=>"socket",SocketType=>"msg.Qna",Caption=>"Question and answer of member"),
    Descriptions=>array(DataType=>"socket",SocketType=>"stdctrls.Richtext",Caption=>"Text description about member"),
    );
  }

function Init (&$Control)
  {
  global $_USER;

  $MemberID=$Control->Arguments['e'];
  if ($EditPartner)
    {
    $Control->Data['Qna']="mship.MemberInfo/qna/$MemberID";
    $Control->Data['Descriptions']="mship.MemberInfo/desc/$MemberID";
    }
  return array(DisableCache=>true);
  }

function Render(&$Control)
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  $_=&$GLOBALS['_STRINGS']['mship'];
  global $cfg;
  global $_USER;
  extract ($Control->Properties);
  $MshipContext=$cfg['Settings']['mship']['CatalogContext'];
#  $_ENV->InitWindows();

  $IImage=&$_ENV->LoadInterface ("img.IImage");
  if (!$_USER->UserID)
    {
    print $_['WARNING_REGISTER_FIRST'];
    return;
    }
  if ($HideInfo) {return;}
#  print "<h5>Your login:".$_USER->Login."</h5>";

  list($t,$text_class)  =get_css_pair($CSS_Text,"p");
  list($t,$input_class) =get_css_pair($CSS_InputArea,"input");
  list($t,$link_class)  =get_css_pair($CSS_Link,"a");

  $EditMemberID=$Control->Arguments['e'];

  $q=DBQuery ("SELECT * FROM mship_MemberInfo WHERE ShowIt<2 AND OwnerUserID=$_USER->UserID",array("CatalogID","MemberID"));

  if ($q)
    {
    $CatalogIDs=implode (',',array_keys($q->Rows));
    $qc=DBQuery("SELECT JSBPageID,Caption,State FROM jsb_Pages WHERE SysContext='$MshipContext' AND JSBPageID IN ($CatalogIDs)","JSBPageID");
/*    $qqc1=DBQuery ("SELECT p.MemberID, COUNT(m.PostID) AS Counter
      FROM msg_Posts AS m , mship_MemberInfo AS p
      WHERE (p.OwnerUserID=$_USER->UserID) AND (m.BindTo=CONCAT('mship.Member/qna/',p.MemberID))
      AND m.ParentID=0 AND p.Disabled<>2
      GROUP BY p.MemberID","MemberID");
    $qqc2=DBQuery ("SELECT p.MemberID, COUNT(m.PostID) AS Counter
      FROM msg_Posts AS m ,mship_MemberInfo AS p
      WHERE (p.OwnerUserID=$_USER->UserID) AND (m.BindTo=CONCAT('mship.Member/qna/',p.MemberID))
      AND m.Answered=1 AND m.ParentID=0 AND p.Disabled<>2
      GROUP BY p.MemberID","MemberID");
      */
    print "<table width='100%' border=0>";
    foreach ($q->Rows as $CatalogID=>$column)
      foreach($column as $aMemberID=>$row)
      {
      $Counter="";

/*      if ($qqc1)
        {
        $total=0;
        $crow=$qqc1->Rows[$aMemberID]; if ($crow) {$total=$crow->Counter;}
        $answered=0; if ($qqc2) {$crow=$qqc2->Rows[$aMemberID]; if ($crow) {$answered=$crow->Counter;}}
        $unanswered=$total-$answered;
        if ($unanswered) $Counter="<b>asked questions:$unanswered/</b>";
        if ($total) $Counter="$Counter$total";
        if ($Counter) {$Counter="<h4>[$Counter]</h4>";}
        }
*/
      print "<tr><td>";
      $IImage->View(array(BindTo=>"mship.Member/img/$aMemberID",TnFormatNo=>1,ShowCaption=>0,EditMode=>1));

      print "</td><td><h3>".langstr_get($qc->Rows[$row->CatalogID]->Caption)."</h3>";
      print "<h4>$row->Name</h4>";
      if ($row->Subtitle) {
        print "<h5>$row->Subtitle</h5>";
        }
      $status=($row->ShowIt)?$_['PROFILE_IS_PUBLISHED']:$_['PROFILE_UNDER_REVIEW'];
      print "<i>$status</i><br><br>";

      $s="";
      if ($row->StreetAddress) $s.="<b>$_[MEMBER_STREETADDRESS]:</b> $row->StreetAddress ";
      if ($row->Phone) $s.="<br><b>$_[MEMBER_PHONE]:</b> $row->Phone";
      if ($row->ContactPerson) $s.=" <b>$_[MEMBER_CONTACTPERSON]:</b> $row->ContactPerson";
      if ($row->Website)
        {
        $www=$row->Website;
        if (substr($www,0,7)!='http://') $www="http://$www";
        $s.="<br/><b>$_[MEMBER_WEBSITE]:</b> <a href='$www' target='_blank'>$row->Website</a> ";
        }
      if ($row->Email)
        {
        $row->Email;
        $s.="<br/><b>$_[MEMBER_EMAIL]:</b> <a href='mailto:$row->Email'>$row->Email</a> ";
        }

      $galbum=$_USER->GetGranted(array(ClassName=>"mship.Member",Context=>"album",ObjectID=>$aMemberID));
      $gabout=$_USER->GetGranted(array(ClassName=>"mship.Member",Context=>"about",ObjectID=>$aMemberID));
      $gqna=$_USER->GetGranted(array(ClassName=>"mship.Member",Context=>"qna",ObjectID=>$aMemberID));

# TEXT ABOUT ######################################
      $s.="<ul><li>";

      $url=ActionURL("stdctrls.IRichtext.View.f",array(
           BindTo=>"mship.Member/about/$aMemberID",
           Title=>'Page 1',EditMode=>1,CreateFirstPage=>1));

      $s.=($gabout==false)?"<a href='javascript:;' OnClick=\"W.openModal({url:'"
        .ActionURL("mship.IProfile.GrantAccess.f",
        array(ClassName=>"mship.Member",Context=>"about",ObjectID=>$aMemberID,ForwardTo=>$url))
        ."',w:650,h:500,reloadOnOk:1})\">$_[CREATE_TEXT_ABOUT]</a>"
      :"<a href='javascript:;' OnClick=\"W.openModal({url:'$url',w:650,h:500,reloadOnOk:1})\">$_[EDIT_TEXT_ABOUT]</a>";
      $s.="</li><li>";

# ALBUM ######################################
      $url=ActionURL("img.IImgIndex.View.f",
           array(BindTo=>"mship.Member/album/$aMemberID",TnFormatNo=>1 ,ShowCaptions=>1,Insertable=>1,EditMode=>1));

      $s.=($galbum==false)?"<a href='javascript:;' OnClick=\"W.openModal({url:'"
        .ActionURL("mship.IProfile.GrantAccess.f",
        array(ClassName=>"mship.Member",Context=>"album",ObjectID=>$aMemberID,ForwardTo=>$url))
        ."',w:650,h:450,reloadOnOk:1})\">$_[CREATE_PHOTOALBUM]</a>"
      :"<a href='javascript:;' OnClick=\"W.openModal({url:'$url',w:650,h:450,reloadOnOk:1})\">$_[EDIT_PHOTOALBUM]</a>";
      $s.="</li>";
# QNA ##########################################
/*      $url=ActionURL("msg.IQna.View.f",array(BindTo=>"mship.Member/qna/$aMemberID",Moderator=>1));

      $s.=($galbum==false)?"<a href='javascript:;' OnClick=\"W.openModal({url:'"
        .ActionURL("mship.IProfile.GrantAccess.f",
        array(ClassName=>"mship.Member",Context=>"qna",ObjectID=>$aMemberID,ForwardTo=>$url))
        ."',w:550,h:450,reloadOnOk:1})\">I wish to get feedback from visitors</a>"
      :"<a href='javascript:;' OnClick=\"W.openModal({url:'$url',w:550,h:450,reloadOnOk:1})\">Open feedback messages</a>";
*/
      $s.="</ul>";
      print $s."$Counter";
      print "</td></tr>";
      print "<tr><td colspan='2' class='bgdown'><table width='100%' ><tr><td>";
      $_ENV->PutButton(array(Caption=>$__['CAPTION_DELETE'],Kind=>'delete',OnClick=>"W.openModal({url:'"
      .ActionURL("mship.IProfile.Delete.f",array(MemberID=>$aMemberID))."',w:550,h:450,reloadOnOk:1})"));
      print "</td><td align='right'>";
      $_ENV->PutButton(array(Caption=>$_['EDIT_PROFILE'],Kind=>'view',OnClick=>"W.openModal({url:'"
      .ActionURL("mship.IProfile.Edit.f",array(MemberID=>$aMemberID))."',w:550,h:550,reloadOnOk:1})"));
      print "</td></tr></table></td></tr>";
      }

    if ($_USER->HasRole("mship:MultipleProfiles"))
      {
      print "<tr><td colspan='2' align='right'><br>$TextAddMore<br>";
      $_ENV->PutButton(array(Caption=>$_['ADD_PROFILE'],OnClick=>"W.openModal({url:'"
      .ActionURL("mship.IProfile.Edit.f")."',w:550,h:550,reloadOnOk:1})"));
      print "</td></tr>";
      }
    print "</table>";
    }
  else
    {
    $EditMemberID=-1;
    print $TextInvitation;
    print "<table width='100%' border=0>";
    print "<tr><td colspan='2' align='center'><br>";
      $_ENV->PutButton(array(Caption=>$_['CREATE_MYPROFILE'],OnClick=>"W.openModal({url:'"
      .ActionURL("mship.IProfile.Edit.f")."',w:550,h:500,reloadOnOk:1})"));
    print "</td></tr></table>";
    }

/*  if (!$EditMemberID)
    {
    print "<h6>You are member of groups:</h6>";
    $qg=DBQuery ("SELECT g.GroupID,g.Caption FROM um_UserGroups g,um_UserInGroups ug WHERE g.GroupID=ug.GroupID AND ug.UserID=$_USER->UserID","GroupID");
    foreach ($qg->Rows as $GroupID=>$row)
      {
      print "<li>$row->Caption</li>";
      }
    }
*/
#   $IMemberInfo=&$_ENV->LoadInterface("mship.IMembers");
#   $IMemberInfo->Edit(array(MemberID=>$EditMemberID,MemberData=>$q));
   return;
  }
}
?>
