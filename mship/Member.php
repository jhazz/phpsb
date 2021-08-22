<?php
class mship_Member
{
var $CopyrightText="(c)2006 JhAZZ Site Builder. Membership system";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['mship'];
  $this->Propdefs=array(
    DateFormat=>array(Type=>"Dateformat",Required=>true,DefaultValue=>"normaldate",Caption=>$_[NEWSDATEFORMAT]),
    UseButtons=>array(Type=>"Boolean"),
    );

  $this->Datadefs=array(
    Title      =>array(DataType=>"String",Caption=>"Member title"),
    Subtitle   =>array(DataType=>"String",Caption=>"Member subtitle"),
    Member     =>array(DataType=>"mship.Member",Caption=>"Member ref"),
    MemberCatalogImage=>array(DataType=>"img.Image",Caption=>"Member catalog image"),
    MemberImage=>array(DataType=>"img.Image",Caption=>"Member image"),
    MemberAlbum=>array(DataType=>"img.Image",Caption=>"Member photoalbum"),
    MemberAbout=>array(DataType=>"stdctrls.Richtext",Caption=>"Member details text"),
    MemberInfoAndLinks=>array(DataType=>"String",Caption=>"Member information including links or buttons"),
    );
  }

function Init (&$Control)
  {
  $_=&$GLOBALS['_STRINGS']['mship'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg,$_USER,$SysContext;

  if ($Control->Properties)
    {
    extract ($Control->Properties);
    }

  if ($Control->DesignMode)
    {
    $Control->Data['Title']="[Here will be member's title]";
    $Control->Data['Subtitle']="[Here will be member's subtitle]";
    $Control->Data['MemberImage']="mship.Member/img/";
    $Control->Data['MemberAlbum']="mship.Member/album/";
    $Control->Data['MemberAbout']="mship.Member/about/";
    $Control->Data['MemberCatalogImage']="mship.Catalog/image/";

    if ($SysContext!='layouts')
      {
      print "<font color='red'>This control should be placed only onto <b>layout<b> page</font>";
      }
    }

  if ($SysContext=='layouts')
    {
    return;
    }


  $MemberID=$Control->JSBPageID;
  $Control->Data['Member']="mship.Member/$MemberID";
  $q=DBQuery ("SELECT * FROM mship_MemberInfo WHERE MemberID=$MemberID AND (ShowIt<2 OR (OwnerUserID=$_USER->UserID))");
  if ($q)
    {
    $Member=$q->Top;
    $Control->Data['Title']=$Member->Name;
    $GLOBALS['_TITLE']=$Member->Name.(($Member->Subtitle)?" - ".$Member->Subtitle:"");
    $Control->Data['Subtitle']=$Member->Subtitle;
    $Control->Data['MemberImage']="mship.Member/img/$MemberID";
    $Control->Data['MemberAlbum']="mship.Member/album/$MemberID";
    $Control->Data['MemberAbout']="mship.Member/about/$MemberID";
    $Control->Data['MemberCatalogImage']="mship.Catalog/image/".$q->Top->CatalogID;

    $info="";
    if ($Member->StreetAddress) $info.="$Member->StreetAddress<br>";
    if ($Member->Phone) $info.="<b>Phone:</b> $Member->Phone<br>";
    if ($Member->ContactPerson) $info.="$Member->ContactPerson<br>";

    if ($UseButtons)
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
        $info.=$_ENV->PutButton(array(Caption=>'Email',Kind=>'email',NewWindow=>1,Href=>"mailto:".$Member->Email,ToString=>1));
        }
      }
    else
      {
      if ($Member->Website)
        {
        $www=$Member->Website;
        if (substr($www,7)!='http://') $www="http://$www";
        $info.="<br/><b>Website:</b> <a href='$www' target='_blank'>$Member->Website</a> ";
        }
      if ($Member->Email)
        {
        $Member->Email;
        $info.="<br/><b>Email:</b> <a href='mailto:$Member->Email'>$Member->Email</a> ";
        }
      }

    $Control->Data['MemberInfoAndLinks']=$info;
    }
  else
    {
    return array(PageNotFound=>1);
    }
  }

} # end of class


?>
