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
    Style=>array(Type=>"List",Caption=>$_['TMENU_STYLE'],
      Values=>array(
        LeftMenu=>$_['STYLE_LEFTMENU'],
        HeadMenu=>$_['STYLE_HEADMENU'],
        TopMenu=>$_['STYLE_TOPMENU'])
      ),
    Root=>array(Caption=>$_['TMENU_ROOT'],
    	Type=>"InputModal",
      ModalCall=>"jsb.IPage.Select",
      ModalArgs=>array(ContextSelectable=>1),
      InitCall=>"jsb.IPage.GetPageNameByValue"),
    EmbedInPage=>array(Type=>"Boolean",Caption=>$_['TMENU_EMBEDINPAGE'],DefaultValue=>false)

    );
  }

function LoadContextIntoResource($ctx)
  {
  if (!$this->res)
    {
    $this->res="\nJSMenu_data=new Array();\n";
    }

  if ($this->LoadingContexts[$ctx]==2)
    {
    return;
    }
  $this->LoadingContexts[$ctx]=2;

  $qpages=DBQuery ("SELECT CONCAT(SysContext,'_',JSBPageID) AS ContextPageID,
    Caption,ParentID,JSBLayoutID,Options
    FROM jsb_Pages
    WHERE SysContext='$ctx' AND State=1
    ORDER BY OrderNo","ContextPageID");

  $PrevContext="";
  if ($qpages)
    {
    foreach ($qpages->Rows as $Context_PageID=>$pagedata)
      {
      if (!$this->str)
        {
        $this->str='JSMenu_data['.$this->rowno.']="'; $this->rowno++;
        }
      list ($aJSBContext,$aJSBPageID)=split ("_",$Context_PageID);

      if ($aJSBContext!=$PrevContext) {$this->str.=$aJSBContext;}
      $this->str.=":".intval($pagedata->ParentID).":".intval($aJSBPageID).":"
       .str_replace(array('"',"'",'|','@'),array('&quot;','&#039;',' ',' '),langstr_get($pagedata->Caption));
      $PrevContext=$aJSBContext;

      parse_str ($pagedata->Options,$Options);

      $v=$Options['virtual'];
      if ($v)
        {
        $this->str.="@v=$v";
        }

      if ($Options['rk'])
        {
        $this->str.='@rk='.$Options['rk'];
        }

      if ($Options['i'])
        {
        $this->str.='@i='.$Options['i'];
        }
      if ($Options['hi'])
        {
        $this->str.='@hi='.$Options['hi'];
        }

      if ($Options['attach'])
        {
        $this->str.='@at='.$Options['attach'];
        list ($ctx,$pg)=explode ('/',$Options['attach']);
        if (!$this->LoadingContexts[$ctx])
          {$this->LoadingContexts[$ctx]=1;}     # markup other context to load
        }

      if ($Options['u'])
        {
        $u=$Options['u'];
        global $cfg;
        if (substr($u,0,1)=='/') $u=$cfg['RootURL'].$u;
        $this->str.="@u=$u";
        }

      if (strlen($this->str)>10000)
        {
        $this->res.=$this->str."\";\n";
        $this->str=false;
        } else {$this->str.="|";}
      }
    }
  if ($this->str) {$this->res.=$this->str."\";\n\n";
    $this->str=false;
    }
  }

function Init(&$Control)
  {
  $Root=$Control->Properties['Root'];
  if ($Root)
    {
    list ($SysContext,$RootPageID)=explode ('/',$Root,3);
    $RootPageID=intval($RootPageID);
    $this->inires.="\nvar JSMenu_$Control->JSBPageControlID=new TMainMenu('JSMenu_$Control->JSBPageControlID',JSMenu_data,'$SysContext',$RootPageID);";
    $this->LoadingContexts[$SysContext]=1;
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

  global $_THEME_NAME,$cfg;
  global $cfg,$_SESSION,$_USER;
  $ThemeURL=$cfg['ThemesURL'].'/'.$_THEME_NAME;
  $ThemePath=$cfg['ThemesPath'].'/'.$_THEME_NAME;
  $SkinURL=$cfg['SkinsURL'].'/'.$_THEME_NAME;

  global $_USER;
  # User's resource access keys
  if ($_USER->ResourceKeys)
    {
    $s.="";
    $ka=explode (",",$_USER->ResourceKeys);
    foreach ($ka as $k) {$s.=(($s)?",":"")."$k:1";}
    $this->inires.="\nvar ResourceKeys={".$s."};\n";
    } else $this->inires.="\nvar ResourceKeys=false;\n";

  if ($this->LoadingContexts)
    {
    #$sess=($_SESSION->IsNonCookie)?"~".$_SESSION->SessionKey:"";
    if (!$Control->Properties['EmbedInPage'])
      {
      ksort($this->LoadingContexts);
      $MenuID=implode('_',array_keys($this->LoadingContexts));
      $MenuScriptFile="jsmenu_$MenuID.js";
      $tmppath=$cfg['TempPath'].'/stdctrls_jsmenu';
      if (!is_dir($tmppath)) {mkdir($tmppath,0777);}

      $MenuScriptFilePath=$cfg['TempPath'].'/stdctrls_jsmenu/'.$MenuScriptFile;
      if (!file_exists($MenuScriptFilePath))
        {
        # 1st pass - load required contexts
        foreach ($this->LoadingContexts as $ctx=>$loadstate)
          {
          if ($loadstate==1) $this->LoadContextIntoResource($ctx);
          }
        # 2nd pass - load attached contexts
        foreach ($this->LoadingContexts as $ctx=>$loadstate)
          {
          if ($loadstate==1) $this->LoadContextIntoResource($ctx);
          }

        $ThemeMenuStyleScript=$ThemePath.'/MenuStyle.js';
        if (!file_exists($ThemeMenuStyleScript)) {return array(
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
      }
    else
      {
      $ThemeMenuStyleScript=$ThemePath.'/MenuStyle.js';
      if (!file_exists($ThemeMenuStyleScript))
        {return array(
        Error=>"'MenuStyle.js' not found in theme directory",
        Details=>$ThemeMenuStyleScript);
        }

      # 1st pass - load required contexts
      foreach ($this->LoadingContexts as $ctx=>$loadstate)
        {
        if ($loadstate==1) $this->LoadContextIntoResource($ctx);
        }
      # 2nd pass - load attached contexts
      foreach ($this->LoadingContexts as $ctx=>$loadstate)
        {
        if ($loadstate==1) $this->LoadContextIntoResource($ctx);
        }
      print "\n<script src='$ThemeURL/MenuStyle.js'></script>";
      print "\n<script src='".$cfg['PublicURL']."/stdctrls/TJSMenu_script.js'></script>";
      }
    print "
<script>
var URLSuffix='$sess.$cfg[VirtualExtension]';
$this->res
$this->inires
setBodyEvent('onLoad','MMD_ReArrange()');
setBodyEvent('onResize','MMD_ReArrange()');
</script>";
    }
  else
    {
    return array(Error=>"Menu not loaded");
    }
  return $result;
  }


function AfterLoad(&$Control)
  {

  }

function Render(&$Control)
  {
  global $cfg,$_HOMEURL;
  print "\n\n<script>JSMenu_$Control->JSBPageControlID.Build('".$Control->Properties[Style]."',false,false,'".$_HOMEURL."');</script>\n";
  }

}

?>
