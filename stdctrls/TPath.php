<?php
class stdctrls_TPath
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS][stdctrls];
  $this->About=$_[SITEPATH_ABOUT];
  $this->Propdefs=array(
    CSS_Text=>array(Type=>"CSS_Class",Caption=>$_[SITEHEADLINE_P_CSSTEXT],BaseCSSClass=>"p"),
    ShowRoot=>array(Type=>"Boolean",DefaultValue=>"1"),
    Delimiter=>array(Type=>"String",DefaultValue=>" : "),
    AttachRoot=>array(Type=>"InputModal",Caption=>$_['SITEPATH_ATTACHROOT'],Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    UsePageTitles=>array(Type=>"Boolean"),
    Align=>array(Type=>"Align"));
  }

function Render(&$Control)
  {
  global $cfg;
  $ext=$cfg['VirtualExtension'];
  extract ($Control->Properties);
  $SysContext=$Control->SysContext;
  $JSBPageID=$Control->JSBPageID;

  global $_TITLE;
  $Text=$_TITLE;
  for ($i=0;$i<10;$i++)
    {
    $vJSBContext=$vJSBPageID=false;
    $q=DBQuery ("SELECT ParentID,Caption,Title,Options FROM jsb_Pages WHERE SysContext='$SysContext' AND JSBPageID='$JSBPageID'");
    if ($q)
      {
      parse_str($q->Top->Options,$opt);
      $virtual=$opt['virtual'];
      $ParentID=$q->Top->ParentID;

      if (($ParentID==0)&&(!$AttachRoot)&&(!$ShowRoot)) {break;}

      if ($virtual)
        {
        list($vJSBContext,$vJSBPageID)=explode ("/",$virtual);
        $vJSBPageID=intval($vJSBPageID);
        $q=DBQuery ("SELECT ParentID,Caption,Title,Options FROM jsb_Pages WHERE SysContext='$vJSBContext' AND JSBPageID='$vJSBPageID'");
        }


      if (($i!=0) || (!$_TITLE))
        {
        	if ($UsePageTitles) {
        		$Caption=langstr_get($q->Top->Title);
        	} else {$Caption=langstr_get($q->Top->Caption);}
        
        if ($Text) {$Text=$Delimiter.$Text;}

        if ($Control->JSBPageID==$JSBPageID)
          {
          $Text=$Caption.$Text;
          }
        else
          {
          if ($virtual)
            {
            $Text="<a href='../$vJSBContext/$vJSBPageID.$ext'>$Caption</a>".$Text;
            }
          else
            {
            $Text="<a href='../$SysContext/$JSBPageID.$ext'>$Caption</a>".$Text;
            }
          }
        }
      }
    if (!$ParentID)
      {
      if (!$AttachRoot)
        {
        break;
        }
      else
        {
        list ($tmp,$SysContext,$ParentID)=explode ("/",$AttachRoot);
        $AttachRoot=false;
        }
      }
    $JSBPageID=$ParentID;
    }
  if ($Align) {$Align=" align='$Align'";}
  list($t,$c)=get_css_pair($CSS_Text,'p');
  print "<$t$c$Align>$Text</$t>";
  }
}
?>
