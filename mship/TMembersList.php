<?php
class mship_TMembersList
{
var $CopyrightText="(c)2006 JhAZZ Site Builder. Membership system";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

var $Subscribers="MemberGroup";

function InitComponent()
  {
  	global $cfg;
  $_=&$GLOBALS['_STRINGS']['mship'];
  $s="SELECT JSBPageID AS GroupID,Caption FROM jsb_Pages
  	 WHERE SysContext='".$cfg['Settings']['mship']['CatalogContext']."' AND State=1";
  $qg=DBQuery($s,"GroupID");

  $this->Propdefs=array(
    BindToMemberGroup=>array(Type=>"Binding",DataType=>"mship.Group",Caption=>"Member group"),
    ShowOnlyGroup=>array(Type=>"list",Recordset=>&$qg,CaptionField=>"Caption"),
    ShowWWWAndEmailButton=>array(Type=>"Boolean",Caption=>"Link to web site could be viewed in the page of member and here"),
    TextIfEmpty=>array(Type=>"String",DefaultValue=>"This section is empty"),
    TnFormatNo=>array(Type=>"int",DefaultValue=>1),
    DetailsContext=>array(Type=>"SysContext",ObjectClass=>"mship.Member"),
    );
  }

function Render(&$Control)
  {
  $__=&$GLOBALS[_STRINGS][_];
  $_=&$GLOBALS[_STRINGS][mship];
  global $cfg,$_USER;
  extract ($Control->Properties);

  if ($Control->SysContext=='layouts')
    {
    print "[Here will be printed members list of corresponding catalogue section]";
    return;
    }
  if ($ShowOnlyGroup) {
  	$CatalogID=$ShowOnlyGroup;
  } else {
    $info=BindPathInfo($Control->BindToMemberGroup);
	  $CatalogID=$info->ID;
  }

  
  $MshipContext=$cfg['Settings']['mship']['CatalogContext'];
  $s="SELECT * FROM mship_MemberInfo WHERE CatalogID=$CatalogID AND (ShowIt=1 OR (OwnerUserID=$_USER->UserID AND ShowIt<2))";
  $qm=DBQuery ($s,array("MemberID"));

  if ($qm)
    {
    $imgkeys=""; $keys=array_keys($qm->Rows);
    foreach ($keys as $k)
      {
      $imgkeys.=(($imgkeys)?",":"")."'mship.Member/img/$k'";
      }
    if ($imgkeys)
      {
      $s="SELECT BindTo,ImgID,Filenames FROM img_Documents WHERE BindTo IN ($imgkeys) ORDER BY OrderNo";
      $qimg=DBQuery($s,"BindTo"); # MAY BE OVERLOAD ONE IMAGE BY OTHER
      }

    $text="";
    foreach ($qm->Rows as $MemberID=>$Member)
      {
      $info="<h4>$Member->Name</h4>";
      if ($Member->Subtitle) $info.="<h5>$Member->Subtitle</h5>";
      if ($Member->StreetAddress) $info.="$Member->StreetAddress<br>";
      if ($Member->Phone) $info.="<b>Phone:</b> $Member->Phone<br>";
      if ($Member->ContactPerson) $info.="$Member->ContactPerson<br>";
      $img="";
      $imgDoc=$qimg->Rows["mship.Member/img/$MemberID"];
      if ($imgDoc)
        {
        $tns=$_ENV->Unserialize($imgDoc->Filenames);
        $img="/img/mship.Member/img/$tns[1]";
        $imgPath="$cfg[FilesPath]$img";
        $imgURL="$cfg[FilesURL]$img";
        list($w,$h,$type,$size)=getimagesize($imgPath);
        $img="<a href='../$DetailsContext/$MemberID.$cfg[VirtualExtension]'><img src='$imgURL' $size border=0/></a>";
        }
      if ($text) $text.="<tr><td colspan=2><hr></td></tr>";
      $info.="<br><br>".$_ENV->PutButton(array(Caption=>$_['VIEW_DETAILS'],Kind=>'view',Href=>"../$DetailsContext/$MemberID.$cfg[VirtualExtension]",ToString=>1));
      if ($ShowWWWAndEmailButton)
        {
        if ($Member->Website)
          {
          $www=$Member->Website;
          if (substr($www,0,7)!='http://') $www="http://$www";
          $info.=$_ENV->PutButton(array(Caption=>'Website',Kind=>'home',Href=>$www,NewWindow=>1,ToString=>1));
          }
        if ($Member->Email)
          {
          $Member->Email;
          $info.=$_ENV->PutButton(array(Caption=>'Email',Kind=>'email',Href=>"mailto:".$Member->Email,ToString=>1));
          }
        }

      $text.="<tr><td colspan=2><h3>$Member->Company</h3></td><tr valign='top'><td width='3%'>".$img."</td><td>$info</td></tr>";

      }

    if ($text) $text="<table width='100%' cellpadding=5>$text</table>";

    print $text;
    }
  else
    {
    print "<p>$TextIfEmpty</p>";
    }
  }

}
?>
