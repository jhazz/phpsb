<?
$GLOBALS['_THEME']=array(
Title=>"Pink-grey neutral system skin",
Description=>"Flegmatic pink-grey skin with orange spots",
CopyrightText=>"(c)2006. PHPSB Group",
CopyrightURL=>"www.phpsb.com",

FormStyles=>array(
'clear'=>array(Caption=>"Clear form without table"),
'normal'=>array(Caption=>"Normal form inside a table",
  FormPanel=>array(Padding=>'20',MainPanelCSS=>'td.bgup'),
  TableStyle=>1),
),

TableStyles=>array(
  array(  Caption=>'Table with head',
    Top=>'th',
    Even=>'td.ce',
    Odd =>'td.co',
    Highlight=>'td.bgtop',
    Warning=>'td.red',
    BgColor_Hovered=>'#f8f0ff',
    BgColor_Checked=>'#ffffe0',
  ),
  array(  Caption=>'Simple Table', 
    Even=>'td.ce', Odd =>'td.co',
    Highlight=>'td.bgtop',
    Warning=>'td.red',
    BgColor_Hovered=>'#f8f0ff',
    BgColor_Checked=>'#ffffe0',
    )
  ),

Topmenu=>"{Defaults:{Font:'Arial Narrow',FontSize:'14px'},
Level0:{nFontColor:'#606060',hFontColor:'#801F9D',nBgColor:false,hBgColor:'#d0d0d0'},
Level1:{nBgColor:'#d0d0d0',hBgColor:'#AB88B5',nFontColor:'#000000',hFontColor:'#ffffff'}
}",

Buttons=>array(
  'default'=>array(
    TwoPart=>"btn2",
    Width=>"50",
    ),
  'standard'=>array(CSS=>"input.button"),
  'plus'=>array(ImgSrc=>"jsb_add.gif"),
  'clear'=>array(ImgSrc=>"jsb_trash.gif"),
  ),

Spacer=>"sp.gif",

WindowStyle=>array(
  ItemBgColorHover=>'#8080a0',
  ItemFontColorHover=>'#ffffff',
  GutterClass=>'popupgutter',
  MenuClass=>'popupbg',
  ),
);
?>
