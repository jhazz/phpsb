<?php
class mship_TMember
{
var $CopyrightText="(c)2006 JhAZZ Site Builder. Membership system";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['mship'];
  $this->About="Member data";
  $this->Propdefs=array(
    HideAllText=>array(Type=>"Boolean",Caption=>"Hides description information"),
    DateFormat=>array(Type=>"Dateformat",Required=>true,DefaultValue=>"normaldate",Caption=>$_['NEWSDATEFORMAT']),
    );

  $this->Datadefs=array(
    Title      =>array(DataType=>"String",Caption=>"Member title"),
    Subtitle   =>array(DataType=>"String",Caption=>"Member subtitle"),
    MemberImage=>array(DataType=>"img.Image",Caption=>"Member image"),
    MemberAlbum=>array(DataType=>"img.Image",Caption=>"Member photoalbum"),
    MemberAbout=>array(DataType=>"stdctrls.Richtext",Caption=>"News text"),
    );
  }


function Init (&$Control)
  {
  $_=&$GLOBALS[_STRINGS][mship];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg,$_USER;

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
  $q=DBQuery ("SELECT * FROM mship_MemberInfo WHERE MemberID=$MemberID AND (ShowIt<2 OR (OwnerUserID=$_USER->UserID))");
  if ($q)
    {
    $Control->Member=$q->Top;

    $GLOBALS['_TITLE']=$Control->Data['Title']=$Control->Member->Name.(($Control->Member->Subtitle)?" - ".$Control->Member->Subtitle:"");
    $Control->Data['Subtitle']=$Control->Member->Subtitle;
    $Control->Data['MemberImage']="mship.Member/img/$MemberID";
    $Control->Data['MemberAlbum']="mship.Member/album/$MemberID";
    $Control->Data['MemberAbout']="mship.Member/about/$MemberID";
    }
  }

function Render(&$Control)
  {
  $_=&$GLOBALS['_STRINGS']['mship'];
  global $cfg,$SysContext;
  extract($Control->Properties);
  if ($SysContext=='layouts')
    {
    print "[Here will be printed information about one member]";
    return;
    }

  $Member=&$Control->Member;
  if ($Member)
    {
    if (!$HideAllText)
      {
      $text="";
      $info="";
      if ($Member->StreetAddress) $info.="$Member->StreetAddress<br>";
      if ($Member->Phone) $info.="<b>Phone:</b> $Member->Phone<br>";
      if ($Member->ContactPerson) $info.="$Member->ContactPerson<br>";

/*      if ($Member->Website)
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
  */
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


      $img="";
      $imgDoc=$qimg->Rows["mship.Member/img/$MemberID"];
      if ($imgDoc)
        {
        $tns=$_ENV->Unserialize($imgDoc->Filenames);
        $img="/img/mship.Member/img/$tns[1]";
        $imgPath="$cfg[FilesPath]$img";
        $imgURL="$cfg[FilesURL]$img";
        list($w,$h,$type,$size)=getimagesize($imgPath);
        $img="<img src='$imgURL' $size border=0/>";
        }
      if ($text) $text.="<tr><td colspan=2><hr></td></tr>";
      $ss=$Member->Subtitle; if ($ss) $ss="<h5>$ss</h5>";
      $text.="<tr><td colspan=2><h4>$Member->Name</h4>$ss</td><tr valign='top'><td width='3%'>".$img."</td><td>$info</td></tr>";
      if ($text)
        {
        print "<table width='100%'>$text</table>";
        }

      }

    }
  }
} # end of class


?>
