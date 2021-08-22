<?php
class store_TPriceAttrSelector
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  global $cfg;
  $_=&$GLOBALS[_STRINGS][store];
  $this->About=$_[TPRICEATTRSELECTOR];
  $this->Propdefs=array(
    BindTo_DetectedOptions=>array(Type=>"Binding",DataType=>"store.DetectedOptions",Caption=>$_[DATADEF_DETECTED_OPTIONS],Required=>true),
    ButtonText=>array(Type=>"Caption",Caption=>$_[TPRICEATTRSELECTOR_BUTTON],DefaultValue=>$_[TPRICEATTRSELECTOR_BUTTON]),
    TitleText=>array(Type=>"Caption",Caption=>$_[TPRICEATTRSELECTOR_TITLE]),
    TitleCSS=>array(Type=>"CSS_Class",Caption=>$_[TPRICEATTRSELECTOR_TITLE_CSS],DefaultValue=>"p"),
    OptionsCSS=>array(Type=>"CSS_Class",Caption=>$_[TPRICEATTRSELECTOR_OPTIONS_CSS],BaseCSSClass=>"td"),
    );
  $this->Datadefs=array(
    HighlightedOptions=>array(DataType=>"store.HighlightedOptions",Caption=>$_[DATADEF_HIGHLIGHTED_OPTIONS]),
    );
  }


function Init(&$Control)
  {
  $attrschecked=$_POST['attrschecked'];
  if ($attrschecked)
    {
    $Control->Data['SelectedAttrs']=$attrschecked;
    }
  }

function Render(&$Control)
  {
  extract ($Control->Properties);

  print "›“Œ“  ŒÃœŒÕ≈Õ“ —≈…◊¿— Õ≈ –¿¡Œ“¿≈“. ”ƒ¿À»“≈ ≈√Œ";
  $attrschecked=$_POST['attrschecked'];
  $UsedOptions=$Control->Bindings['BindTo_DetectedOptions'];

  if (($Control->DesignMode) && (!$UsedAttrs))
    {
    $UsedAttrs="Optional attr 1^Optional attr 2^Optional attr 3^Optional attr 4";
    }
  if ($UsedOptions)
    {

    print "¬—≈ “”“ Õ¿ƒŒ –¿¡Œ“¿“‹";


    list($ot,$oc)=get_css_pair($OptionsCSS,"td");
    $s="";
    $a=explode ("^",$UsedAttrs);
    foreach($a as $Attr)
      {
      $ch="";
      if ($attrschecked) if (array_search($Attr,$attrschecked)!==false) {$ch=' checked';}
      $s.="<tr><td width='5'><input $ch type='checkbox' name='attrschecked[]' value='$Attr'/></td><$ot$oc>$Attr</$ot></tr>";
      }
    list($tt,$tc)=get_css_pair($TitleCSS,'p');
    $s.="<tr valign='top'><td></td><td><input type='submit' class='button' value='$ButtonText'/></td></tr>";

    $s="<table border='0' cellpadding='1' cellspacing='1'>$s</table>";
    $s="<$tt$cc>$TitleText</$tt>$s<input type='hidden' name='action' value='selattr'>";
    if (!$Control->DesignMode) {$s="<form method='post' action=''>$s</form>";}
    print $s;
    }
  }

}
?>
