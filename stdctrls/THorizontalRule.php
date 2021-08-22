<?php
class stdctrls_THorizontalRule
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard controls";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $Detected=0;

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];

  $this->Propdefs=array(
    SeparatorStyle =>array(Type=>"ThemeElement",Section=>"HorizontalSeparators"),
    Height         =>array(Type=>"Int",DefaultValue=>"10"),
    UseTagHR       =>array(Type=>"Boolean"),
    MarginTop      =>array(Type=>'int'),
    MarginBottom   =>array(Type=>'int'),
    );
  }

function Render(&$Control)
  {
  global $cfg,$_THEME;
  extract ($Control->Properties);

#  print

  if ($SeparatorStyle)
    {$sepdata=$_THEME['HorizontalSeparators'][$SeparatorStyle];
    list ($Separator_tag,$Separator_class)=get_css_pair ($sepdata['CSS'],"td");
    if (!$Height) $Height=$sepdata['Height'];
    } else $Separator_tag="td";

  $tdstyle="";
  if ($UseTagHR)
    {
    print "<hr>";
    }
  else
    {
    print "<table width='100%' cellspacing='0' cellpadding='0'>";
    if ($MarginTop) print "<tr><td><img src='$_THEME[SkinURL]/$_THEME[Spacer]' height='$MarginTop'></td></tr>";
    print "<tr><td $Separator_class ><img src='$_THEME[SkinURL]/$_THEME[Spacer]' height='$Height'></td></tr>";
    if ($MarginBottom) print "<tr><td><img src='$_THEME[SkinURL]/$_THEME[Spacer]' height='$MarginBottom'></td></tr>";
    print "</table>";
    }
  }
}
