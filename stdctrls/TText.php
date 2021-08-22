<?php
class stdctrls_TText
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $Subscribers="Bind";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  $this->About=$_['SITETEXT_ABOUT'];
  $this->Propdefs=array(
    Bind=>array(Type=>"Binding",DataType=>"String",Caption=>$_['SITETEXT_BINDTOTEXT']),
    Text=>array(Type=>"LangString",Caption=>$_['SITETEXT_TEXT']),
    CSS_Text=>array(Type=>"CSS_Class",Caption=>$_['SITETEXT_P_CSSTEXT'],BaseCSSClass=>"p",AddDrawingFonts=>1),
    Cropping=>array(Type=>"Boolean",Caption=>$_['SITETEXT_CROPPING'],DefaultValue=>'1'),
    Align=>array(Type=>"Align")
    );
  }

function Render(&$Control)
  {
  trace ("Start text");
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  if (is_array($Control->Properties)) extract ($Control->Properties);
  if ($Control->Bind) $Text=$Control->Bind;

  if ((!$Text)&&($Control->DesignMode)) {$Text='{'.$Bind.'}'; }
  $Text=langstr_get($Text);
  trace ("Empty text $Text");

  
  if (!$Text) return;
  trace ("Not Empty text $Text");
	if ($Align) {$Align=" align='$Align'";}
  if (strtolower(substr($CSS_Text,-5))==':draw')
    {
    $DrawingFont=substr($CSS_Text,0,-5);
    trace ("Load interface");
    $IDrawText=&$_ENV->LoadInterface("stdctrls.PDrawText");
    trace ("IDRAW loaded interface");
    $r=$IDrawText->GetDrawText(array(
      RenderAsText=>$RenderAsText,
      DrawingFont=>$DrawingFont,
      Cropping=>$Cropping,
      Text=>$Text
      ));
    if ($r['Error']) return $r;
    print $r['Text'];
    return;
    }
  list($t,$c)=get_css_pair($CSS_Text,'span');
  print "<$t$c$Align>$Text</$t>";
  trace ("End text");
  
  }
}
?>
