<?php
class stdctrls_TLinkList
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $jsb_Utils;

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  $this->About=$_['TLINKLIST_ABOUT'];
  $this->Propdefs=array(
    Style=>array(Type=>"List",Caption=>$_['LINKLIST_STYLE'],
      DefaultValue=>'simple',
      Values=>array(
        'simple'  =>$_['LINKLIST_STYLE_SIMPLE'],
        'horizontal'=>$_['LINKLIST_STYLE_HORIZONTAL'],
        'wideline'=>$_['LINKLIST_STYLE_WIDELINE'],
        'tabs'=>'Tabs',
        )
      ),
    Root=>array(Type=>"InputModal",InitCall=>"jsb.IPage.GetPageNameByValue",ModalCall=>"jsb.IPage.Select",Caption=>$_['TMENU_ROOT']),
    CSS_Cell=>array(Type=>"CSS_Class",Caption=>$_['SITETEXT_P_CSSCELL'],BaseCSSClass=>"td"),
    CSS_Text=>array(Type=>"CSS_Class",Caption=>$_['SITETEXT_P_CSSTEXT'],BaseCSSClass=>"p"),
    CSS_Href=>array(Type=>"CSS_Class",Caption=>$_['LINKLIST_P_CSSHREF'],BaseCSSClass=>"a"),
    Align=>array(Type=>"Align"),
    Before=>array(Type=>"String",Caption=>$_['LINKLIST_BEFORE']),
    After=>array(Type=>"String",Caption=>$_['LINKLIST_AFTER']),
    ImgClosed=>array(Type=>"InputModal",Editable=>1,InitCall=>"jsb.IThemeReader.GetSkinImageByValue",ModalCall=>"jsb.IThemeReader.SelectSkinImage"),
    ImgOpened=>array(Type=>"InputModal",Editable=>1,InitCall=>"jsb.IThemeReader.GetSkinImageByValue",ModalCall=>"jsb.IThemeReader.SelectSkinImage"),
    ActivateLinkRecursive=>array(Type=>"Boolean"),
    Between=>array(Type=>"String",Caption=>$_['LINKLIST_BETWEEN']),
    Padding=>array(Type=>"Integer",Caption=>$_['TLINKLIST_PADDING'],DefaultValue=>3),
    HorizontalSeparator=>array(Type=>"ThemeElement",Section=>"HorizontalSeparators"),
    HideImages=>array(Type=>"Boolean",Caption=>"Do not show menu images"),
    );
  }

  function Init(&$Control) {
		global $JSB_PageData,$SysContext;
  	$Root=$Control->Properties['Root'];
  	if ($Root){list($Control->RootContext,$Control->RootPageID)=explode ("/",$Root);
  	}else{
  		$Control->RootContext=$SysContext; 
  		$Control->RootPageID=($JSB_PageData->ParentID) ? $JSB_PageData->ParentID : 0;
  	}
  	$this->jsb_Utils=&$_ENV->LoadInterface("jsb.Utils");
  	$this->jsb_Utils->LoadJSBPages($Control->RootContext,true,true);
  }
  

  function _find_opened_page(&$page,$n,$args) {
  	# args[0] - current walk SysContext, changes after jump to other tree
  	# args[1] - context of the opening page
  	# args[2] - pageid of the opening page

  	# args[3] - level
  	# args[4] - pageID of level0 
  	# args[5] - HighlightedID
  	
		if ($args[3]==0) $args[4]=$page['JSBPageID'];
		
  	if ( (($args[0]==$args[1]) && ($page['JSBPageID']==$args[2])) || 
  	   (($args[1]==$page['_virtual_context']) && ($page['_virtual_pageid']==$args[2]))) {
  		$args[5]=$args[4];
  		return;
  	}
  	if (is_array($page['_childs'])) {
  		if ($page['_attached_context']) $args[0]=$page['_attached_context'];
  		$args[3]++;
  		array_walk($page['_childs'],array(&$this,"_find_opened_page"),$args);
  	}
  }

  function Render(&$Control) {
  extract ($Control->Properties);
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  global $_LANGUAGE,$cfg,$_THEME_NAME,$JSB_PageData,$_THEME,$SysContext;
	
  $rows=&$this->jsb_Utils->PagesByContext[$Control->RootContext];
  $HighlightID=0;

	if (!$rows){
		if ($SysContext=='layouts'){
			$test=new stdClass;
			$test->Caption="Link example";
			$test->Options=array('url'=>'#');
			$rows=array("1"=>$test,"2"=>$test,"3"=>$test);
			$RowCount=3;
		}else{return true;}
	} else {
	  $rows=&$rows[$Control->RootPageID]['_childs'];
		$RowCount=count($rows);
  	array_walk($rows,array(&$this,"_find_opened_page"),array($Control->RootContext,$SysContext,$Control->JSBPageID,0,0,&$HighlightID));
	}

  if ($Align) {$Align=" align='$Align'";}
  list($at,$ac)=get_css_pair($CSS_Href,'a');
  list($ct,$cc)=get_css_pair($CSS_Cell,'td');
  list($st,$sc)=get_css_pair($CSS_Text,'span');

  if ($ImgLeft)
    {
    $imgPath="$cfg[SkinsPath]/$_THEME_NAME/$ImgLeft";
    if (file_exists($imgPath))
      {
      $imginfo=getimagesize($imgPath);
      $ImgLeft="<img src='$cfg[SkinsURL]/$_THEME_NAME/$ImgLeft' border='0' align='absmiddle' $imginfo[3]/>";
      }
    else $ImgLeft="";
    }


  $str="";
  $RowNo=-1;
  $prevTab=-2;
 
  # Двигаемся от корня $SysContext/$JSBRootPageID перебирая всех _childs[]
  foreach($rows as $i=>$row) {
    
		$RowNo++;
    $thisContext=$SysContext;
    $JSBPageID=$thisJSBPageID=$row['JSBPageID'];
    $Caption=langstr_get($row['Caption']);
    parse_str ($row['Options'],$Options);
		$v=$row['_virtual_pageid'];
    if ($v) {$thisContext=$row['_virtual_context'];$thisJSBPageID=$row['_virtual_pageid'];}
    $url="$GLOBALS[_HOMEURL]/$thisContext/$thisJSBPageID.".$cfg['VirtualExtension'];
    $img=$Options['i'];
    $imghtml="";
    if (($img)&&(!$HideImages))
      {
      $imgPath="$cfg[SkinsPath]/$_THEME_NAME/$img";
      $imgURL= "$cfg[SkinsURL]/$_THEME_NAME/$img";
      if (file_exists($imgPath))
        {
        $imginfo=getimagesize($imgPath);
        $imghtml="<img src='$imgURL' border='0' align='absmiddle' $imginfo[3]/>";
        }
      }
    if ($Options['url']) $url=$Options['url'];
		$isActive=(($HighlightID==$JSBPageID)||($HighlightID==$thisJSBPageID));
		
    switch ($Style) {
      case 'tabs':
      	if ($prevTab===-2) {
      		# First tab
      		$imgname="/tabs/tab-start-".(($isActive)?"a":"i").".gif";
      		$img=getimagesize("$_THEME[SkinPath]$imgname");
      		$str.="<td><img src='$_THEME[SkinURL]$imgname' $img[3]></td>";
      	}else{
      		# one of tab
      		$imgname="/tabs/tab-mid-".(($prevTab)?"ai":(($isActive)?"ia":"ii")).".gif";
      		$img=getimagesize("$_THEME[SkinPath]$imgname");
      		$str.="<td><img src='$_THEME[SkinURL]$imgname' $img[3]'></td>";
      	}
      	if ($isActive) $str.="<td $cc background='$_THEME[SkinURL]/tabs/tab-a.gif' nowrap>$Caption</td>";
      	else $str.="<td $cc background='$_THEME[SkinURL]/tabs/tab-i.gif' nowrap><a$ac href='$url'>$Caption</a></td>";
      	$prevTab=$isActive;
      	break;
      case 'horizontal':
      case 'wideline':
        $color="";
        if ($ColorRampFrom) {
          $color=sprintf ("#%02x%02x%02x",
            floor(($r2-$r1)/$RowCount*$RowNo+$r1),
            floor(($g2-$g1)/$RowCount*$RowNo+$g1),
            floor(($b2-$b1)/$RowCount*$RowNo+$b1));
          $color=" style='background-color:$color'";
          }
        if ($str) {$str.="<$ct$cc$Align nowrap width='1'><$st$sc>$Between</$st></$ct>";}
        if ($isActive)
          {
          $str.="<$ct$cc$Align><$st$sc>$Before$imghtml$Caption$After</$st></$ct>";
          }
        else
          {
          $str.="<$ct$cc$Align><$st$sc>$Before<$at$ac href='$url'>$imghtml$Caption</a>$After</$st></$ct>";
          }
        break;

      case 'simple':

      default:
        if ($str) {$str.=$Between;}
        if ($isActive)
        $str.="$Before<font color='$SelectedTextColor'>$imghtml$Caption</font>$After";
          else
        $str.="$Before<$at $ac href='$url'>$imghtml$Caption</a>$After";
      }
    }

  switch ($Style)
    {
    case'tabs':
  		$imgname="/tabs/tab-end-".(($isActive)?"a":"i").".gif";
  		$img=getimagesize("$_THEME[SkinPath]$imgname");
    	$str.="<td><img src='$_THEME[SkinURL]$imgname' $img[3]></td>";
      print "<table width='100%' cellpadding='0' cellspacing='0'><tr>
        <td valign='bottom'><div><table border='0' cellpadding='0' cellspacing='0'><tr>$str</tr></table></div></td></tr></table>";
    	break;
    case 'wideline':
      if ($str){
        print "<table width='100%' border='0' cellpadding='".intval($Padding)."' cellspacing='0'><tr>$str</tr></table>";
        }
      break;
    case 'horizontal':
      print "<table border='0' cellpadding='".intval($Padding)."' cellspacing='0'><tr>$str</tr></table>";
      break;

    default:
    case 'simple':
      print "<table cellpadding='0' cellspacing='0'><tr><$ct$cs$Align><$st$sc>$str</$st></$ct></table>";
      break;
    }

  }
}
?>
