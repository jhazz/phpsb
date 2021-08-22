<?php
class stdctrls_TVerticalMenu
{
	var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
	var $CopyrightURL="http://www.jhazz.com/jsb";
	var $ComponentVersion="1.0";
	var $lastMenuItemID=0,$CompiledStyles,$JStyle;

	
	function InitComponent()
	{
		global $cfg;
		$_=&$GLOBALS['_STRINGS']['stdctrls'];
		$this->Propdefs=array(
		Style=>array(Type=>"ThemeElement",Required=>1,Section=>"VerticalMenuStyles",Caption=>$_['LINKLIST_STYLE']),
		Root=>array(Type=>"InputModal",Caption=>$_['TMENU_ROOT'],InitCall=>"jsb.IPage.GetPageNameByValue",ModalCall=>"jsb.IPage.Select",ModalArgs=>array(ContextSelectable=>1)),
		HideFirstLevel=>array(Type=>"Boolean",Caption=>"Hides menu captions of linked to the root"),
		HideImages=>array(Type=>"Boolean",Caption=>"Do not show menu images"),
		ExpandAll=>array(Type=>"Boolean",Caption=>"View all menu"),
		UsePageTitles=>array(Type=>"Boolean"),
		OpenModalWindow=>array(Type=>"Boolean"),
		Align=>array(Type=>"Align"),
		);
	}

	
	function Init(&$Control) {
		global $_THEME;
		
		if (!isset($this->jsb_Utils)) $this->jsb_Utils=&$_ENV->LoadInterface("jsb.Utils");
		$StyleName=$Control->Properties['Style'];
		if (isset($this->CompiledStyles[$StyleName])) {
			$Control->MenuStyle=&$this->CompiledStyles[$StyleName];
			
			return;
		}
		$this->CompiledStyles[$StyleName]=array();
		$Control->MenuStyle=&$this->CompiledStyles[$StyleName];
		
		$SourceStyle=&$_THEME['VerticalMenuStyles'][$StyleName];
		
		$kk=array_keys($SourceStyle);
		foreach($kk as $k) {if($k!='Levels')$LevelStyle[$k]=$SourceStyle[$k];}
#		$HoverImagesArray=false;

		if (isset($SourceStyle['Levels'])) {
			for ($Level=0;$Level<5;$Level++) {
				if (!isset($SourceStyle['Levels'][$Level])) {break;}
				if (!$SourceStyle['Levels'][$Level]['Inherited']) $LevelStyle=false;
				$kk=array_keys($SourceStyle['Levels'][$Level]);
				foreach($kk as $k) {if($k!='Levels')$LevelStyle[$k]=$SourceStyle['Levels'][$Level][$k];}

				$LevelStyle['HAlignHTML']=isset($LevelStyle['HAlign'])?" align='$LevelStyle[HAlign]'":"";
				list($LevelStyle['at'],$LevelStyle['ac'])=get_css_pair($LevelStyle['CSS_Href'],'a');
				list($LevelStyle['ct'],$LevelStyle['cc'])=get_css_pair($LevelStyle['CSS_Cell'],'td');
				list($LevelStyle['mt'],$LevelStyle['mc'])=get_css_pair($LevelStyle['CSS_MenuCell'],'td');
				$LevelStyle['CellPadding']=$LevelStyle['CellPadding'];
				$LevelStyle['CellSpacing']=$LevelStyle['CellSpacing'];

			  $a=array('hBgColorOpen','hBgColorClose','BgColorOpen','BgColorClose');
				foreach ($a as $k) if ($LevelStyle[$k]) {$this->JStyle[$StyleName][$Level][$k]=$LevelStyle[$k];}
				$a=array("DecorOpenBg","DecorOpenTL","DecorOpenT","DecorOpenTR","DecorOpenL","DecorOpenR","DecorOpenBL","DecorOpenB","DecorOpenBR",
				"DecorCloseBg","DecorCloseTL","DecorCloseT","DecorCloseTR","DecorCloseL","DecorCloseR","DecorCloseBL","DecorCloseB","DecorCloseBR","NosubDecorCloseTR");
				foreach ($a as $k) {
					if ($LevelStyle[$k]) {
						list ($src,$w,$h)=explode (",",$LevelStyle[$k]);
						if (!$w) {$f="$_THEME[SkinPath]/$src";$img=@getimagesize($f);list($w,$h,$t,$imgsz)=$img;}else{$imgsz="width='$w' height='$h'";}
						$LevelStyle["src$k"]=$_THEME['SkinURL'].'/'.$src;
						$LevelStyle["size$k"]=$imgsz;
						$LevelStyle["width$k"]=$w;
						$LevelStyle["height$k"]=$h;
						if ($LevelStyle["h$k"]) {$this->JStyle[$StyleName][$Level]["h$k"]=$LevelStyle["h$k"];}
						$this->JStyle[$StyleName][$Level][$k]=$LevelStyle[$k];
					}
				}

				$LevelStyle['SeparatorHTML']="";
				if ($LevelStyle['SeparatorStyle']){
					$SeparatorStyleParams=&$_THEME['HorizontalSeparators'][$LevelStyle['SeparatorStyle']];
					$SepHeight=$SeparatorStyleParams['Height'];
					if (!$SepHeight) $SepHeight=1;

					list($t,$c)=get_css_pair($SeparatorStyleParams['CSS'],'td');
					if ($SeparatorStyleParams['BgImage']) {
						$LevelStyle['SeparatorHTML']="<tr><td colspan='2'><table width='100%' cellpadding='0' cellspacing='0'><tr>
			         <$t$c colspan='2' background='$_THEME[SkinURL]/$SeparatorStyleParams[BgImage]'><img src='$_THEME[SkinURL]/$_THEME[Spacer]' width='1' height='$SepHeight'/></$t></tr></table></td></tr>";
					} elseif ($SeparatorStyleParams['CSS']){
						$LevelStyle['SeparatorHTML']="<tr><td colspan='2'><table width='100%' cellpadding='0' cellspacing='0'><tr><$t$c colspan='2' ><img src='$_THEME[SkinURL]/$_THEME[Spacer]' width='1' height='$SepHeight'></$t></tr></table></td></tr>";
					}
				}
				$Control->MenuStyle['Levels'][$Level]=$LevelStyle;
			}
		}
				
	}

	function AfterInit(&$Control) {
		global $JSB_PageData,$_THEME,$SysContext;

		$Root=$Control->Properties['Root'];
		if ($Root){list($Control->RootContext,$Control->RootPageID)=explode ("/",$Root);
		}else{
			$Control->RootContext=$SysContext;
			$Control->RootPageID=($JSB_PageData->ParentID) ? $JSB_PageData->ParentID : 0;
		}
		$this->jsb_Utils->LoadJSBPages($Control->RootContext,true,true);

		if (!$this->Inited) {
			$this->Inited=1;
			$sss="z:{"."z:12}";
			?><script><?
	  	if (is_array($this->JStyle)){
	  		
	  		foreach ($this->JStyle as $sName=>$Style){
	  			$ss="";
		  		foreach ($Style as $Level=>$images){
		  			$s="";
		  			foreach ($images as $k=>$v){$s.=(($s)?",":"")."$k:'".$v."'";}
		  			$ss.=(($ss)?',':'').'{'.$s.'}';
		  		}
		  		$sss.=(($sss)?",":"")."'$sName':[$ss]";
	  		}
	  		
	  	}
	  	print "\nvar stdctrls_vmenu_jstyle={".$sss."};\n";
	  	
	  #### JAVASCRIPT FUNCTION ####
  	?>
function dg(s){return document.getElementById(s);}
function stdctrls_vmenu_MO(mid,h){
	var e=document.getElementById("stdctrls_vm"+mid);
	if (!e) return;if (e.h==h)return false;e.h=h;
	var img,sName=e.attributes['s'].nodeValue,hh=(h)?"h":"",st=false,level=parseInt(e.attributes['level'].nodeValue),state=e.attributes['mState'].nodeValue;
	try {st=stdctrls_vmenu_jstyle[sName][level];}catch(zz){}
	img=document.getElementById("stdctrls_vm_i"+mid);
	if (img){var hsrc=img.attributes['hsrc'].nodeValue;if(hsrc){if(h){img.oldSrc=img.src;img.src=hsrc;}else{img.src=img.oldSrc;}}}
	if (!st){return;}
	var bgc,s,i,c=['TL','TR','BL','BR'],b=['T','L','R','B','Bg'];
	bgc=st[hh+'BgColor'+state];if (!bgc) bgc="";
	e.style.backgroundColor=bgc;
	for(i in c){e=document.getElementById("stdctrl_vm_"+c[i]+mid);if (e){if(e.attributes['nosub']==undefined){s=st[hh+'Decor'+state+c[i]];if ((s!="")&&(s!=undefined)) {e.src=SkinURL+"/"+s;}}else{s=st[hh+'NosubDecor'+state+c[i]];if ((s!="")&&(s!=undefined)) {e.src=SkinURL+"/"+s;}}}}
	for(i in b){e=document.getElementById("stdctrl_vm_"+b[i]+mid);if (e){s=st[hh+'Decor'+state+b[i]];if ((s!="")&&(s!=undefined)) {e.style.backgroundImage="url("+SkinURL+"/"+s+")";}}}
}
</script>
  	<?
		}
	}


	function Render(&$Control) {
		$_=&$GLOBALS['_STRINGS']['stdctrls'];
		extract ($Control->Properties);
		global $cfg,$_THEME;
		$w=$MenuStyle['Width'];
		if (!$w) $w='100%';
		$w=" width='$w'";
		print "<table$w border='0' cellpadding='0' cellspacing='0'>";
		$this->_getgrouptext($Control->RootContext,$Control->RootPageID,&$Control,0);
		print "</table>";
	}

	function _getgrouptext($aSysContext,$ParentID,&$Control,$Level)
	{
		global $cfg,$_THEME,$_THEME_NAME,$_HOMEURL;
		if ($Level>10) {return;}

		$pages=&$this->jsb_Utils->PagesByContext[$aSysContext][$ParentID]['_childs'];
		if (!is_array($pages)) return;

		# FUCKING PHP. THIS DOESNT WORK RECURSIVELY
		#array_walk(&$pages,array(&$this,"_walk_over_menu_items"),array(&$MenuStyle,&$Control,$ParentContext,$Level));
		foreach ($pages as $n=>$page) {
			$this->_walk_over_menu_items($page,$n,$Control,$aSysContext,$Level);
		}
		return;
	}


	function _walk_over_menu_items(&$page,$n,&$Control,$aSysContext,$Level) { # n - 0,1,2,3...
		global $_HOMEURL,$_THEME,$cfg,$SysContext;
		$l=$Level;
		if ($Control->Properties['HideFirstLevel']){$l--;}
		if ($l<0) $l=0;
		$ml=count($Control->MenuStyle['Levels']);
		if ($l>=$ml) $l=$ml-1;
		
		$StyleName=$Control->Properties['Style'];
		$MenuStyle=&$Control->MenuStyle['Levels'][$l];
		$thisJSBContext=$page['SysContext'];
		$thisJSBPageID=$JSBPageID=intval($page['JSBPageID']);
		
		if (!(($Control->HideFirstLevel) && ($Level==0) ) && ($n))
		print $MenuStyle['SeparatorHTML'];

		
		$Caption=($Control->Properties['UsePageTitles'])?langstr_get($page['Title']):langstr_get($page['Caption']);
		if ($page['_virtual_pageid']){
			$thisJSBContext=$page['_virtual_context'];
			$thisJSBPageID =$page['_virtual_pageid'];
		}

		$url="$_HOMEURL/$thisJSBContext/$thisJSBPageID.$cfg[VirtualExtension]";
		$this->lastMenuItemID++; # will changed inside _getgrouptext
		$thisMenuItemID=$this->lastMenuItemID;
		$isin=$this->_is_child_opened($page,$SysContext,$Control->JSBPageID,$aSysContext,0);

		if (isset($page['_options']['url'])) $url=$page['_options']['url'];
		$img=$page['_options']['i'];
		$himg=$page['_options']['hi'];
		$imghtml="";
		if (($img)&&(!$Control->Properties['HideImages'])){
			$imgPath="$_THEME[SkinPath]/$img";
			$imgURL= "$_THEME[SkinURL]/$img";
			if (file_exists($imgPath)){
				$imginfo=getimagesize($imgPath);
				$imghtml="<img id='stdctrls_vm_i$thisMenuItemID' src='$imgURL'".(($himg)?" hsrc='$_THEME[SkinURL]/$himg'":"")." border='0' $imginfo[3]/>";
			}
		}


		$onClick=($Control->Properties['OpenModalWindow'])
		? "onClick='W.openModal({url:\"$url\",w:900,h:240,x:50,y:400})'"
		: "onClick='location.href=\"$url\"'";


		if ($Control->Properties['OpenModalWindow']) {$url="javascript:;";}

		$CellPadding=$MenuStyle['CellPadding'];
		
		$Width=$MenuStyle['Width'];
		$Width=" width='".(($Width)?$Width:"100%")."'";
		if ($isin || $Control->Properties['ExpandAll']) {

			# open
			if ((!$Control->Properties['HideFirstLevel']) || ($Level!=0) )
			print "<tr><td style='cursor:hand;".(isset($MenuStyle["BgColorOpen"])?"background-color:$MenuStyle[BgColorOpen]":"")."' id='stdctrls_vm$thisMenuItemID' s='$StyleName' level='$l' mState='Open'>"
			."<table border='0' cellpadding='0' cellspacing='0' width='100%'><tr>"
			.((isset($MenuStyle['DecorOpenTL']))?"<td><img id='stdctrl_vm_TL$thisMenuItemID' src='$MenuStyle[srcDecorOpenTL]' $MenuStyle[sizeDecorOpenTL]/></td>":"")
			."<td>$imghtml</td><td width='100%' id='stdctrl_vm_T$thisMenuItemID' $MenuStyle[cc]$MenuStyle[HAlignHTML] style='padding:$CellPadding".(isset($MenuStyle['DecorOpenT'])?";background-image:url($MenuStyle[srcDecorOpenT])'":"")."' $onClick onMouseOut='stdctrls_vmenu_MO($thisMenuItemID,0)' onMouseOver='stdctrls_vmenu_MO($thisMenuItemID,1)'>$Caption</td>"
			.((isset($MenuStyle['DecorOpenTR']))?"<td align='right'><img id='stdctrl_vm_TR$thisMenuItemID' src='$MenuStyle[srcDecorOpenTR]' $MenuStyle[sizeDecorOpenTR]/></td>":"")."</tr></table>
  			     <table border='0' cellpadding='0' cellspacing='0' width='100%'><tr><td id='stdctrl_vm_L$thisMenuItemID'".(isset($MenuStyle['DecorOpenL'])?" style='background-image:url($MenuStyle[srcDecorOpenL])'":"")."><img src='$_THEME[SkinURL]/$_THEME[Spacer]' width='$MenuStyle[widthDecorOpenL]'></td>"
			."<td id='stdctrl_vm_Bg$thisMenuItemID' width='100%'".(isset($MenuStyle['DecorOpenBg'])?" style='background-repeat:repeat-x;background-image:url($MenuStyle[srcDecorOpenBg])'":"")."><table border='0' $Width cellspacing=0 cellpadding=0>";

			$this->_getgrouptext($aSysContext,$JSBPageID,$Control,$Level+1);

			if ((!$Control->Properties['HideFirstLevel']) || ($Level!=0) )
			{
				print "</table></td><td id='stdctrl_vm_R$thisMenuItemID'".(isset($MenuStyle['DecorOpenR'])?" style='background-image:url($MenuStyle[srcDecorOpenR])'":"")."><img src='$_THEME[SkinURL]/$_THEME[Spacer]' width='$MenuStyle[widthDecorOpenR]'></td></tr></table>";
				if (isset($MenuStyle['DecorOpenB'])) {
					print "<table border='0' cellpadding='0' cellspacing='0' width='100%'><tr>"
					.((isset($MenuStyle['DecorOpenBL']))?"<td><img id='stdctrl_vm_BL$thisMenuItemID' src='$MenuStyle[srcDecorOpenBL]'$MenuStyle[sizeDecorOpenBL]/></td>":"")
					."<td width='100%' id='stdctrl_vm_B$thisMenuItemID'".(isset($MenuStyle['DecorOpenB'])?" style='background-image:url($MenuStyle[srcDecorOpenB])'":"")."><img src='$_THEME[SkinURL]/$_THEME[Spacer]' height='$MenuStyle[heightDecorOpenB]'></td>"
					.((isset($MenuStyle['DecorOpenBR']))?"<td align='right'><img id='stdctrl_vm_BR$thisMenuItemID' src='$MenuStyle[srcDecorOpenBR]'$MenuStyle[sizeDecorOpenBR]/></td>":"")
					."</tr></table>";
				}
				print "</td></tr>";
			}
		} else {
			# close
			$tr=$MenuStyle['srcDecorCloseTR'];
			$hold="";
			if((!is_array($page['_childs']))&&($MenuStyle['srcNosubDecorCloseTR'])) {$tr=$MenuStyle['srcNosubDecorCloseTR'];$nosub=" nosub='1' "; }
			
			
			if ((!$Control->Properties['HideFirstLevel']) || ($Level!=0) )
			print "<tr><td style='cursor:hand;".(isset($MenuStyle["BgColorOpen"])?"background-color:$MenuStyle[BgColorClose]":"")."' id='stdctrls_vm$thisMenuItemID' s='$StyleName' level='$l' mState='Close' $onClick onMouseOut='stdctrls_vmenu_MO($thisMenuItemID,0);event.returnValue = false;' onMouseOver='stdctrls_vmenu_MO($thisMenuItemID,1);event.returnValue = false;'><table border='0' cellpadding='0' cellspacing='0' width='100%'><tr>"
			.((isset($MenuStyle['DecorCloseTL']))?"<td><img id='stdctrl_vm_TL$thisMenuItemID' src='$MenuStyle[srcDecorCloseTL]'$MenuStyle[sizeDecorCloseTL]></td>":"")
			."<td>$imghtml</td><td width='100%' $MenuStyle[cc]$MenuStyle[HAlignHTML] id='stdctrl_vm_T$thisMenuItemID' style='padding:$CellPadding".(isset($MenuStyle['DecorCloseT'])?";background-image:url($MenuStyle[srcDecorCloseT])":"")."'><a $MenuStyle[ac] href='$url'>$Caption</a></td>"
			.((isset($MenuStyle['DecorCloseTR']))?"<td align='right'><img id='stdctrl_vm_TR$thisMenuItemID' $nosub src='$tr'$MenuStyle[sizeDecorCloseTR]/></td>":"")
			."</tr></table></td></tr>";
		}
	}

	function _is_child_opened(&$page,$OpenedContext,$OpenedPageID,$CurrentContext,$Level=0){
		if ($Level>16) return;
		if ((($page['JSBPageID']==$OpenedPageID) && ($CurrentContext == $OpenedContext)) ||
		(($page['_virtual_pageid']==$OpenedPageID) && ($page['_virtual_context'] == $OpenedContext))) {
			return true;
		}
		if (is_array($page['_childs'])) {
			foreach ($page['_childs'] as $i=>$apage) {
				$c=$CurrentContext;
				if ($this->_is_child_opened($apage,$OpenedContext,$OpenedPageID,$c,$Level+1)) return true;
			}
		}
		return false;
	}

}
?>
