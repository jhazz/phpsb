<?php
class stdctrls_TJSMenu
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

var $PrintJSMenuLink=false;
var $LoadingContexts=false;

var $res="";
var $str=false;
var $rowno=0;
var $MenuIDs="";
var $inires="";
var $roots;
var $AfterInitComplete=false;

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];

  $this->About=$_['TMENU_ABOUT'];
  $this->Propdefs=array(
    Align=>array(Type=>"Align"),
    Style=>array(Type=>"ThemeElement",
    	Section=>"JSMenuStyles",Caption=>$_['TMENU_STYLE']),
    Root=>array(Caption=>$_['TMENU_ROOT'],
    	Type=>"InputModal",
      ModalCall=>"jsb.IPage.Select",
      ModalArgs=>array(ContextSelectable=>1),
      InitCall=>"jsb.IPage.GetPageNameByValue"),
    EmbedInPage=>array(Type=>"Boolean",Caption=>$_['TMENU_EMBEDINPAGE'],DefaultValue=>false)

    );
  }


function Init(&$Control)
  {
  if (isset($Control->Properties['Root'])){
    list ($SysContext,$RootPageID)=explode ('/',$Control->Properties['Root'],3);
    $RootPageID=intval($RootPageID);
    $this->inires.="\nvar JSMenu_$Control->JSBPageControlID=new TMainMenu('JSMenu_$Control->JSBPageControlID',JSMenu_data,'$SysContext',$RootPageID);";
    $this->MenuLoadedContexts[$SysContext]=1;
    }
  }

# This method calls after all controls are inited
# The TJSMenu need to know about all other TJSMenu loaded. They was alredy inited
# and information about loadable contexts have collected
function AfterInit(&$Control)
{
	$_=&$GLOBALS['_STRINGS']['stdctrls'];
	# call only once per page
	if ($this->AfterInitComplete) return;
	$this->AfterInitComplete=true;

	global $_THEME_NAME,$cfg,$_USER;
	$ThemeURL=$cfg['ThemesURL'].'/'.$_THEME_NAME;
	$ThemePath=$cfg['ThemesPath'].'/'.$_THEME_NAME;
	$SkinURL=$cfg['SkinsURL'].'/'.$_THEME_NAME;

	# User's resource access keys
	if ($_USER->ResourceKeys){
		$s.="";
		$ka=explode (",",$_USER->ResourceKeys);
		foreach ($ka as $k) {$s.=(($s)?",":"")."'$k':1";}
		$this->inires.="\nvar ResourceKeys={".$s."};\n";
	} else $this->inires.="\nvar ResourceKeys=false;\n";

	if ($this->MenuLoadedContexts) {
		#$sess=($_SESSION->IsNonCookie)?"~".$_SESSION->SessionKey:"";
		if (!$Control->Properties['EmbedInPage']){
			ksort($this->MenuLoadedContexts);
			$MenuID=implode('_',array_keys($this->MenuLoadedContexts));
			$MenuScriptFile="jsmenu_$MenuID.js";
			$tmppath=$cfg['TempPath'].'/stdctrls_jsmenu';
			if (!is_dir($tmppath)) {mkdir($tmppath,0777);}

			$MenuScriptFilePath=$cfg['TempPath'].'/stdctrls_jsmenu/'.$MenuScriptFile;
			if (!file_exists($MenuScriptFilePath)){
				for ($i=0;$i<10;$i++) {
					$repeatagain=false;
					foreach ($this->MenuLoadedContexts as $ctx=>$loadstate){
						if ($loadstate==1) {$repeatagain=true; $this->_loadContextIntoRes($ctx);}
					}
					if (!$repeatagain) break;
				}

				$ThemeMenuStyleScript=$ThemePath.'/MenuStyle.js';
				if (!file_exists($ThemeMenuStyleScript)) {
					return array(
					Error=>"'MenuStyle.js' not found in theme directory",
					Details=>$ThemeMenuStyleScript);
				}
				$fout=fopen ($MenuScriptFilePath,"w");
				fputs ($fout,"\n\n//---------------- MENU STYLE CODE -------------\n");
				fputs ($fout,implode ("",file ($ThemeMenuStyleScript)));  # remove cr lf ;)
				fputs ($fout,"\n\n//---------------- MAIN MENU CODE -------------\n");
				$fname=$cfg['PHPSBScriptsPath'].'/stdctrls/public/TJSMenu_script.js';
				fputs ($fout,implode ("",file ($fname)));  # remove cr lf ;)
				fputs ($fout,"\n\n//---------------- BEGIN MENU DATA -------------\n");
				#          fputs ($fout,"DesignURL='$SkinURL';\n");
				fputs ($fout,$this->res);
				fclose ($fout);
			}
			# URL pre-suffix contains session id if no cookie support by a browser
			print "\n<script src='".$cfg['TempURL'].'/stdctrls_jsmenu/'.$MenuScriptFile."'></script>";
			#       print "<script>var URLSuffix='$sess.$cfg[VirtualExtension]'; $this->inires </script>";
		}else{
			$ThemeMenuStyleScript=$ThemePath.'/MenuStyle.js';
			if (!file_exists($ThemeMenuStyleScript)) {
				return array(Error=>"'MenuStyle.js' not found in theme directory",	Details=>$ThemeMenuStyleScript);
			}
			for ($i=0;$i<10;$i++) {
				$repeatagain=false;
				foreach ($this->MenuLoadedContexts as $ctx=>$loadstate){
					if ($loadstate==1) {$repeatagain=true; $this->_loadContextIntoRes($ctx);}
				}
				if (!$repeatagain) break;
			}
			
/*
			# 1st pass - load required contexts
			foreach ($this->LoadingContexts as $ctx=>$loadstate) {
				if ($loadstate==1) $this->LoadContextIntoResource($ctx);
			}
			# 2nd pass - load attached contexts
			foreach ($this->LoadingContexts as $ctx=>$loadstate)
			{
				if ($loadstate==1) $this->LoadContextIntoResource($ctx);
			}*/
			print "\n<script src='$ThemeURL/MenuStyle.js'></script>";
			print "\n<script src='".$cfg['PublicURL']."/stdctrls/TJSMenu_script.js'></script>";
		}
		print "
<script>
var URLSuffix='$sess.$cfg[VirtualExtension]';
$this->res
$this->inires
P$.on('load',MMD_ReArrange);
P$.on('resize',MMD_ReArrange);
</script>";
	} else {
		return array(Error=>"Menu not loaded");
	}
	
}


  function Render(&$Control){
  	global $cfg,$_HOMEURL;
  	print "\n\n<script>JSMenu_$Control->JSBPageControlID.Build('".$Control->Properties['Style']."',false,false,'".$_HOMEURL."');</script>\n";
  }

  function _loadContextIntoRes($ctx) {
  	global $cfg;
  	if ($this->MenuLoadedContexts[$ctx]==2) return;

  	$this->jsb_Utils=&$_ENV->LoadInterface("jsb.Utils");
  	$this->jsb_Utils->LoadJSBPages($ctx,true,true);
  	if (!$this->res) $this->res="\nJSMenu_data=new Array();\n";

  	$this->MenuLoadedContexts[$ctx]=2;
  	$pages=&$this->jsb_Utils->PagesByContext[$ctx];
		$first=true;
  	if (is_array($pages)) {
  		foreach ($pages as $PageID=>$page) {
  			if ($PageID==0) continue;
  			if (!$this->str) {
  				$this->str='JSMenu_data['.($this->rowno++).']="';
  			}
  			if ($first){$first=false;$this->str.=$ctx;}
  			$aJSBPageID=$page['JSBPageID'];
  			$this->str.=":".intval($page['ParentID']).":".intval($aJSBPageID).":"
  			.str_replace(array('"',"'",'|','@'),array('&quot;','&#039;',' ',' '),langstr_get($page['Caption']));
  			
  			if (isset($page['_options'])) {
	  			$o=&$page['_options'];
	  			if (isset($o['virtual'])) $this->str.="@v=$o[virtual]";
	  			if (isset($o['rk'])) $this->str.="@rk=$o[rk]";
	  			if (isset($o['i'])) $this->str.="@i=o[i]";
	  			if (isset($o['hi'])) $this->str.="@hi=$o[hi]";
	  			if (isset($o['attach'])) {
	  				$this->str.="@at=$o[attach]";
	  				list ($c,$p)=explode ('/',$o['attach']);
	  				if (!isset($this->MenuLoadedContexts[$c])) $this->MenuLoadedContexts[$c]=1;  # markup other context to load
	  			}
	  			if (isset($o['u'])) {
	  				$u=$o['u'];
	  				if (substr($u,0,1)=='/') $u=$cfg['RootURL'].$u;
	  				$this->str.="@u=$u";
	  			}
  			}

  			if (strlen($this->str)>512) {
  				$this->res.=$this->str."\";\n";
  				$this->str=false;
  			} else {$this->str.="|";}
  		}
  	}
  	if ($this->str) {
  		$this->res.=$this->str."\";\n\n";
  		$this->str=false;
  	}
  }

}

?>
