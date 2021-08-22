<?php
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }
require_once ($cfg['PHPSBScriptsPath'].'/sys/inc.settings.php');

$_CACHE_PAGE=false;
if ($cfg['Settings']['jsb']['DisableCache']) $GLOBALS['DisableCache']=1;
if ((!$_DESIGN_MODE) && ($_SERVER['REQUEST_METHOD']!='POST') && (!$GLOBALS['DisableCache']) )
  {
  $_CACHE_PAGE=md5($_SERVER["PATH_INFO"]);
  $p="$cfg[TempPath]/pages";
  if (!is_dir($p)) mkdir($p,$cfg['Resources']['tmp'][1]);
  $fname="$p/$_CACHE_PAGE.php";
  if (file_exists($fname)){
    include ($fname);
    print "\n\n<!-- Cached file $fname -->\n\n";
    exit;
    }
  ob_start();
  }

  
$START_TICK=getmicrotime();
require_once ($cfg['PHPSBScriptsPath'].'/sys/inc.lang.php');
require_once ($cfg['PHPSBScriptsPath'].'/sys/inc.dbase.php');
$__=&$GLOBALS['_STRINGS']['_'];

if (!$Database->Active) {
  global $cfg;
  $f=$cfg['DataPath'].'/.mysql_errors';
  if ((file_exists($f)) && (filesize($f)>100000)) $fp=fopen($f,"w+"); else $fp=fopen($f,"a+");
 	fputs($fp,date('Y-m-d G:i:s')." [".mysql_errno()."] ".mysql_error()." - ".$_SERVER["PATH_INFO"]."\n");
 	fclose($fp);
	$GLOBALS['DisableCache']=1;
  Header ("Pragma: no-cache");
  Header ("Cache-Control: no-cache");
  Header ("Content-Type: text/html; charset=$__[META_CHARSET]");
  print ("<p>$__[ERROR_SQL_NOSERVER]</p><script>window.setTimeout('location.reload();',".rand(300,1500).");</script>");
  exit;
  }
  
require_once ($cfg['PHPSBScriptsPath'].'/sys/inc.sys.php');
$_HOMEURL=$cfg['RootURL'].$_FRONTDOOR;
$JSB_Slots=false;
$JSB_PageData=false;
$JSB_LayoutPageData=false;
$JSB_AfterInitControls=false;
$JSB_PageControlsByID=false;
$JSB_BindingControls=false;
$JSB_InitControls=false;
$JSB_ControlArgs=false;
$JSB_RenderedSlots=false; # used for detect missed controls that did not put into any slot.
$JSB_AllowedLoaderClass=false; # When contextinterface is loaded sometimes it can set allow to specific data loaders

_init_();

include ("inc.pageloader_mysql3.php");

#if ($cfg['Dbase']['Type']=='MySQL3')  
#  else include ("inc.pageloader_mysql4.php");

$_ENV->LoadTheme ('f');
$TemplateFile=$cfg['ThemesPath'].'/'.$GLOBALS['_THEME_NAME'].'/template.'.$JSB_PageData->TemplateFileName.'.php';
if (!file_exists ($TemplateFile)){
  print_error ($__['ERROR_NOTEMPLATEFOUND'],$TemplateFile);
  exit;
  }

##### LOAD PAGE CONTROLS ######################

$squery="SELECT SysContext LIKE 'layouts' AS IsLayout, SysContext,CONCAT(Slot,'|',OrderNo,'|',JSBPageControlID) AS SlotPanel, PropertiesStr,ControlClass,OrderNo
FROM jsb_PageControls WHERE (JSBPageID=$JSBPageID AND SysContext='$SysContext')"
.(($SysContext!='layouts')?" OR (JSBPageID=$JSBLayoutID AND SysContext='layouts') ":"")
." ORDER BY IsLayout DESC,OrderNo";

$qpc=DBQuery ($squery,"SlotPanel");
trace ("Register controls");
if ($qpc) array_walk ($qpc->Rows,"JSB_RegisterControls");

trace ("Init controls");
if (is_array($JSB_InitControls)) array_walk ($JSB_InitControls,"JSB_CallMethodForControl","Init");
trace ("Bind controls");
if (is_array($JSB_BindingControls)) array_walk($JSB_BindingControls,"JSB_BindControls");
trace ("Render controls");

#    R E N D E R !!!

require_once ($TemplateFile);
if ($_CACHE_PAGE)
  {
  if (!$GLOBALS['DisableCache'])
    {
    $fname="$cfg[TempPath]/pages/$_CACHE_PAGE.php";
    $fp=fopen ($fname,"w");
    fputs($fp,ob_get_contents());
    fclose ($fp);
    }
  while (@ob_end_flush());
  }


#  CHECK LOOSED CONTROLS
if ($GLOBALS['_DESIGN_MODE']){
  if ($JSB_Slots){
    foreach ($JSB_Slots as $SlotName=>$ControlsArray){
      if ((!$JSB_RenderedSlots[$SlotName])&&($SlotName!='init')){
        print "<hr><table width='100%'><tr><td><h3><font color='red'>$__[ERROR_CONTROLS_OUTOF_SLOT] $SlotName</font></h3>$__[ERROR_CONTROLS_OUTOF_SLOT_TEXT]</td></tr></table>";
        JSB_RenderSlot($SlotName);
        }
      }
    }
  trace ("Load INfoEditor");
  
  $PIE=&$_ENV->LoadInterface("jsb.IPageInfoEditor");
  $PIE->PrintEditorPanel(array(
    SomeInfo=>"Elapsed time: ".sprintf('%.3f',getmicrotime()-$START_TICK)." sec"
    ));

?>
<script>
var JSB_FloatingToolbar=P$.find('DIV_JSB_FloatingToolbar');
var JSB_DragPot=P$.find('DIV_JSB_DragPot');
document.onmousedown=JSB_ON_MouseDown;
document.onmousemove=JSB_ON_MouseOver;
document.onmouseup=  JSB_ON_MouseUp;
document.onkeypress= JSB_ON_KeyPress;
if (ie) {document.ondragstart=function() {event.returnValue = false;}}
</script>
<?
  }



####  END  ##########################################################

function JSB_BindControls(&$Control)
  {
  $Subscribers=explode (",",$Control->Component->Subscribers);
  global $JSB_PageControlsByID;
  foreach($Subscribers as $PropertyName)
    {
    $BindAddress=$Control->Properties[$PropertyName];
    if (!$BindAddress) {
      trace ("Nobind: $Control->ControlClass[$Control->JSBPageControlID]"."->$PropertyName",2);
      continue;
      }
    list($ControlID,$DataName)=explode (":",$BindAddress);
    $c=&$JSB_PageControlsByID[intval($ControlID)];
    if ($c)
      {
      trace ("Bind: $Control->ControlClass[$Control->JSBPageControlID]"
      ."->$PropertyName = &$c->ControlClass[$c->JSBPageControlID]"."->Data[$DataName]");
      $Control->$PropertyName=&$c->Data[$DataName];
      }
    else
      {
      $Control->BindError=$BindAddress;
      trace ("Bind error: $Control->ControlClass[$Control->JSBPageControlID]"
           ."->$PropertyName = &$c->ControlClass[$c->JSBPageControlID]"."->Data[$DataName]");
      }
    }
  }

function JSB_WalkOverSlotItems (&$Control,$k,$args)
  {
  global $cfg,$_DESIGN_MODE,$_SYSSKIN_NAME;
  $SysSkinURL="$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME";

  list($SlotName,$CallMethod,$SlotOrder)=$args;  # SlotOrder =0 or 1
#  print "<!-- $CallMethod  $Control->ControlClass[ $Control->JSBPageControlID ] -->";
  $Align='';
  if ($CallMethod=="Render")
    {
    $Align=$Control->Properties['Align'];
    if ($Align) {$Align=" align='$Align'";}

    global $_SYSSKIN_NAME;

    if ($_DESIGN_MODE){
      if ($SlotName=='init'){
        print "<table><tr><td>";
        }
      else {
        if ($Control->EditMode) {
          if ($Control->EditURL) {$EditURL=$Control->EditURL;} else {$EditURL="";}
          print "<table width='100%' cellpadding='0' cellspacing='1' border='0'>
          <tr><td ControlID='$Control->JSBPageControlID' id='JSB_CellHat_$Control->JSBPageControlID'><table border='0' cellpadding='0' cellspacing='1'><tr><td></td></tr></table></td></tr>
          <tr>
          <td $Align style='border:1px solid #808080;'
            ControlID='$Control->JSBPageControlID'
            id='JSB_Cell_$Control->JSBPageControlID'
            PrevControlID='$Control->PrevControlID'>
           <div width='100%' id='JSB_ControlCaption_$Control->JSBPageControlID'
          class='JSB_EditableControlCaption'>$Control->ControlClass ($Control->JSBPageControlID)</div>";
          }
        else
          {
          if ($Control->EditableContent){
            $class="JSB_EditableContentCaption";
            $block="";
          }else{
            $class="JSB_NoneditableControlCaption";
            $block=" style='background-image:url($SysSkinURL/blockbody.gif)'";
          }
          print "<table border='0' width='100%'>
            <tr><td class='$class'>$Control->ControlClass ($Control->JSBPageControlID)</td></tr>
            <tr><td $Align $block>";
          }
        }

      if ($cfg['Settings']['jsb']['ShowControlIcons'])
        {
        list($cart,$comp)=explode('.',$Control->ControlClass,2);
        $fico="$cfg[PHPSBScriptsPath]/$cart/public/$comp.gif";
        if (file_exists($fico))
          {
          $size=@getimagesize($fico);
          $Icon="<img border='0' align='left' src='$cfg[PublicURL]/$cart/$comp.gif' $size[3]>";
          }
        else
          {
          $Icon="<img border='0' align='left' src='$SysSkinURL/ico_default.gif'>";
          }
        print "<table border='0' cellspacing=0 width='100%'><tr>
        <td bgcolor='#e0e0e0' style='color:#606060; font-size:10px;'>";

        if (($Control->EditableContent)||($Control->EditMode))
          print "<a href='javascript:' onClick=\"$editscript\">$Icon <b>$Control->ControlClass</b></a><br/>#$Control->JSBPageControlID";
        else
          print "$Icon <b>$Control->ControlClass</b><br/>#$Control->JSBPageControlID";
        print "</td></tr></table>";
        }
      else
        {
        if ($SlotName=='init')print "<a href='javascript:' onClick=\"$editscript\"><b>$Control->ControlClass</b></a> #$Control->JSBPageControlID<br>";
        }
      }
    else
      {
      if ($Align) print "<table table width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td$Align>";
      }

    if ($_DESIGN_MODE && $Control->BindError)
      {
      global $__;
      print "<table><tr>
      <td bgcolor='white' style='color:red; font-size:10px;'>
      <img align='left' src='$cfg[PublicURL]/jsb/brokenbind.gif'>
      $__[ERROR_BIND_IS_BROKEN] [$Control->BindError]</td></tr></table>";
      }
    }

  JSB_CallMethodForControl ($Control,0,$CallMethod);
  if (($CallMethod=="Render")&&(($Align)||($_DESIGN_MODE)))    print "</td></tr></table>";

  global $JSB_PageData;
  if (($CallMethod=="Render")&&($SlotName=='init')&&($Control->ControlClass!=$JSB_PageData->ObjectClass))
    {
    print "<h1>Layout loader class not equal to loader control</h1>";
    print "<b>Loader class:</b>$Control->ControlClass<br><b>Page class:</b>$JSB_PageData->ObjectClass";
    exit;
    }
#  print "<!------ END OF $CallMethod ---$Control->ControlClass[ $Control->JSBPageControlID ] ----->";
  }

#   function JSB_RegisterControls(&$ControlRow,$SlotOrder)
#   PageControl has these fields
#
#   ControlClass   - class name i.e. 'um.TloginPanel'
#   SysContext     - context where it placed in
#   JSBPageID      - Page ID where it placed in
#   OrderNo        - position in slot (integer number)
#   PropertiesStr  - original data from table
#   Slot           - Page slot where placed
#
#   After registering controls obtain next data
#   --------------------------------------------
#
#   JSBPageControlID  - Page control ID
#   Properties        - Properties' values array (unserialized from PropertiesStr)
#   EditMode          - if true - it has changeable properties (i.e. layout controls are disabled on the ordinal pages)
#   DesignMode        - if true - it drawn in design mode
#   &Component        - reference to component serves this control
#
#   Also control can set
#   EditableContent   - allow web editor show any buttons or menus. And it set dark blue headline above control
#   -----
#   Bindings will be added after Init()'s

function JSB_RegisterControls(&$ControlRow)
  {
  global $JSB_Slots, $SysContext, $JSBPageID, $JSBLayoutID,
        $JSB_PageControlsByID, $JSB_AfterInitControls,
        $JSB_InitControls,
        $JSB_ControlArgs,
        $JSB_BindingControls;

  list($Slot,$OrderNo,$ID)=explode ('|',$ControlRow->SlotPanel);

  $Control=$_ENV->CreateControl(array (ControlData=>$ControlRow));
  
  $Control->JSBPageControlID=$ID;
#  $Control->SysContext=$SysContext;
  $Control->JSBPageID=$JSBPageID;
  $Control->JSBLayoutID=$JSBLayoutID;
  $Control->Slot=$Slot;
  $Control->Arguments=$JSB_ControlArgs[$ID];
  $Control->DesignMode=$GLOBALS['_DESIGN_MODE'];
	$Control->EditMode=0;
	$Control->EditableContent=0;
  
  $SlotOrder=0;

  if (($SysContext!='layouts')&&($ControlRow->SysContext=='layouts'))
    {
    # Gray controls: Non-editable and puts on the top of slot (SlotOrder=0)
    $SlotOrder=0;

    $pageControl=&$JSB_PageControlsByID[$ID];
    if ($pageControl)
      {
      print_developer_warning("Layout control see that Page control already loaded.
        Need to check out SELECT jsb_PageControls. Layout controls should goes first!");
      }
    $Control->LayoutControlPath="jsb/layouts/$JSBLayoutID!$ID";
    }
  else
    {
    	trace ("SysContext=$SysContext , ControlRow->SysContext=$ControlRow->SysContext");
    # Check existing layout control
    $layoutControl=&$JSB_PageControlsByID[$ID];
    if ($layoutControl)
      {
      # YES! Layout control exists. Overwrite properties by page-related properties
      foreach ($Control->Properties as $k=>$v) if ($v) $layoutControl->Properties[$k]=$v;
      # Overwrite Slot if not default ... it is not support by editor now but can be available soon
      if ($Control->Slot!='aslayout') $layoutControl->Slot=$Control->Slot;
      return;
      }

    # Blue controls: Editable but under gray controls (SlotOrder=1)
    if ($Control->DesignMode) {
      $Control->EditMode=2;
      }
    $SlotOrder=1;
    }
  $Control->PageControlPath="jsb/$SysContext/$JSBPageID!$ID";

  if ($SysContext=='layouts') { $Control->LayoutMode=true;}
  if (method_exists($Control->Component,"Init")) { $JSB_InitControls[]=&$Control; }
  if (method_exists($Control->Component,"AfterInit")) { $JSB_AfterInitControls[]=&$Control; }

  if ($Control->Component->Subscribers){
    $JSB_BindingControls[]=&$Control;
    }
  $JSB_Slots[$Slot][$SlotOrder][]=&$Control;
  $JSB_PageControlsByID[$ID]=&$Control;
  }

function JSB_CallMethodForControl(&$Control,$i,$CallMethod)
  {
  if (method_exists($Control->Component,$CallMethod))
    {
    eval ("\$r=&\$Control->Component->$CallMethod(\$Control);");
    if ($r)
      {
      if ($r['DisableCache']) $GLOBALS['DisableCache']=true;
      if ($r['ForwardTo']) {print "<script>location.href='$r[ForwardTo]';</script>"; exit;}
      if ($r['Error']) {print_error ($r[Error],$r[Details],0,$Control->ControlClass,$r[IntruderAlert]); }
      if ($r['Warning']) {print_error ($r[Warning],$r[Details],0); }
      if ($r['PageNotFound']) {
        PageNotFound("$Control->ControlClass"."->$CallMethod() said that page not found",$_SERVER["PATH_INFO"]);}
      if ($r['Stop']) {die();}
      }
    }
  }

function JSB_InitPage ($InitBehavior)
  {
  global $_LANGUAGE,$_ENVIRONMENT,
  $cfg,
  $JSB_AfterInitControls,
  $JSB_InitControls,
  $JSB_BindingControls,
  $JSB_PageData,
  $JSB_LayoutPageData,
  $_THEME_NAME,
  $_DESIGN_MODE, $_FRONTDOOR;
  $__=&$GLOBALS['_STRINGS']['_'];

  print "<meta http-equiv='Content-Type' content='text/html; charset=$__[META_CHARSET]'/>";
  if ($GLOBALS['SetBaseHref']){
    print "\n<base href='http://$cfg[SiteURL]$cfg[RootURL]$_FRONTDOOR/$JSB_PageData->SysContext/$JSB_PageData->JSBPageID.$cfg[VirtualExtension]'/>";
    }
  $PageBehavior=array(bgColor=>"#808080", language=>($_LANGUAGE)? $_LANGUAGE : $cfg['DefaultLanguage'], css=>false);
  extract ($InitBehavior,$PageBehavior);
  $_LANGUAGE=($PageBehavior['language']);

  if ($cfg['Settings']['jsb']['UsePNGPatchForIE']) $_ENV->PutPNGPatchForIE();
  $_ENV->InitPage(array(PutVars=>true,Environment=>$_ENVIRONMENT));

  if ($JSB_AfterInitControls){
    array_walk($JSB_AfterInitControls,"JSB_CallMethodForControl","AfterInit");
    if (is_array($JSB_BindingControls)) array_walk($JSB_BindingControls,"JSB_BindControls");
    }

  if ($_DESIGN_MODE)
    {
#    $_ENV->InitWindows();
    require_once ("inc.pageloader_editor.php");
    InitPageEditor();
    }

  global $JSB_Slots;
  $bhooks="";
  if (($JSB_PageData->Metadata)&&($GLOBALS['_KEYWORDS']))
    {
    $Metadata=$_ENV->Unserialize($JSB_PageData->Metadata);
    $s2=$GLOBALS['_KEYWORDS'];
    if ($Metadata['keywords']) $s2.=' '.$Metadata['keywords'];
    if ($s2) print "\n<meta name='keywords' content='$s2'>";
    $s2=$Metadata['description']; if ($s2) print "\n<meta name='description' content='$s2'>";
    $s2=$Metadata['other']; if ($s2) print "\n$s2";
    }
  print "\n<title>".$GLOBALS['_TITLE']." - ".langstr_get($cfg['Settings']['jsb']['WebSiteTitle'])."</title>";
#  $_ENV->InitPage();

  if (($css)&&($css!='main.css'))
    {
#    print "<link rel='stylesheet' href='$cfg[SkinsURL]/$_THEME_NAME/$css' type='text/css'>";
    }


  if ($_DESIGN_MODE)
    {
    global $JSB_BindingValues;
    if ((is_array($JSB_BindingValues))&&($_DESIGN_MODE))
      {
      $s="";
      foreach ($JSB_BindingValues as $JSBPageControlID=>$PropBind)
        {
        if ($s) {$s.=",\n";}
        $ss=implode_properties($PropBind);
        $s.="$JSBPageControlID:'$ss'";
        }
      print "\n\n\n\n<script>JSB_BindingValues={"."$s};</script>\n";
      }
    }

  if ($JSB_PageData->SysContext=='layouts')
    {
    print "<table width='100%' cellspacing='0' border='0' cellpadding='10'>
    <tr><td bgcolor='#e0e0e0'>";
    JSB_RenderSlot('init');
    print "</td><td bgcolor='#e0e0e0' align='right'><b>$__[CAPTION_THELAYOUT]</b> ".langstr_get($JSB_PageData->Caption)."</td>
    </tr></table>";
    }
  }

function get_css_elements($css)
  {
  if (!$css) {return array("p","");}
  list ($tag,$class)=explode (".",$css);
  if (!$tag) {$tag="p";}
  if ($class) {$class=" class='$class'";}
  return array($tag,$class);
  }

  function JSB_RenderSlot ($SlotName,$DontUseWalker=true){
  	global $JSB_Slots,$_DESIGN_MODE,$cfg,$JSB_RenderedSlots,$_SYSSKIN_NAME;
  	$__=&$GLOBALS['_STRINGS']['_'];
  	$SysSkinURL="$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME";
  	$DontUseWalker=false;

  	$JSB_RenderedSlots[$SlotName]=true;
  	if (($_DESIGN_MODE)&&($SlotName!='init'))
  	{
  		print "<table border='1' cellpadding='0' cellspacing='0' width='100%'><tr><td>";
  	}

  	if (is_array($JSB_Slots[$SlotName])){
  		for ($i=0;$i<=1;$i++){
  			if (is_array($JSB_Slots[$SlotName][$i])){
  				#if ($DontUseWalker) {
  					# TColumn causes deadlock on array_walk... strange and fun
  					foreach(array_keys($JSB_Slots[$SlotName][$i]) as $j) {
  						trace ("$SlotName");
  						JSB_WalkOverSlotItems($JSB_Slots[$SlotName][$i][$j],$j,array($SlotName,"Render",$i));
  					}
  				#}else{
  				#	array_walk ($JSB_Slots[$SlotName][$i],"JSB_WalkOverSlotItems",array($SlotName,"Render",$i));
  				#}
  			}
  		}
  	}
  	if (($_DESIGN_MODE)&&($SlotName!='init'))
  	print '<br/><table border="0" width="100%"><tr><td SLOTNAME="'.$SlotName.'" id="JSB_Slot_'.$SlotName.'" bgcolor="#f0f8f0"><a title="'.$__['HINT_CTRL_ADD'].'" href="javascript:JSB_CTRL_AddToSlot(\''.$SlotName.'\')"><img border=0 src="'.$SysSkinURL.'/jsb_add.gif" width="16" height="16"> '.$SlotName.'</a></td></tr></table></td></tr></table>';
  }

function _init_()
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  global $JSBPageID,$JSB_ControlArgs,$SysContext,$JSBPageType,$_DESIGN_MODE,$_USER,$cfg;

  if ($_DESIGN_MODE) {
    if (!$_USER->HasRole("jsb:Composer")) {
      $_DESIGN_MODE=0;
    }
    Header ("Pragma: no-cache");
    Header ("Cache-Control: no-cache");
    Header ("Content-Type: text/html; charset=utf-8"); # $__[META_CHARSET]
  }

  $page_path_info=BindPathInfo('jsb'.$_SERVER["PATH_INFO"]);
  $PageArgs=explode ("|",$page_path_info->ID,10);
  $JSBPageID=intval($PageArgs[0]);
  
  
  if (!$page_path_info->Context) {
  	if ($cfg['Homepage']) {
  		$page_path_info=BindPathInfo("/".$cfg['Homepage']);
  		$JSBPageID=$page_path_info->ID;
  		$SysContext=$page_path_info->Context;
  	}

  	if ((!$JSBPageID)&&(!$SysContext)) {
  		$JSBPageID=0;
  		$SysContext=$cfg['Settings']['jsb']['HomeContext'];
  		if (!$SysContext) {
  			print_error("Set 'HomeContext' in configuration to context containing homepage");
  			exit;
  		}
  	}

    $GLOBALS['SetBaseHref']=1;
    }
  else
    {
    $SysContext=$page_path_info->Context;
    $JSBPageType=$page_path_info->Type;
    for ($i=1;$i<count($PageArgs);$i++)
      {
      list ($controlid,$scontrolargs)=explode ("_",$PageArgs[$i],2);
        {
        $controlargs=explode ("_",$scontrolargs,10);
        for ($j=0;$j<count($controlargs);$j++)
          {
          list ($k,$v)=explode ("=",$controlargs[$j],2);
          if ($k) {$JSB_ControlArgs[$controlid][$k]=$v;}
          }
        }
      }
    }

  if ((!$_DESIGN_MODE)&&(($SysContext=='layouts') || ($SysContext=='backend')))
    {
    PageNotFound("Visitor tryes to load internal SysContext","$SysContext");
    }

  $GLOBALS['_TITLE']="";
  $GLOBALS['_KEYWORDS']="";
  }

function PageNotFound($ErrorText,$Details="")
  {
  global $cfg,$_FRONTDOOR;
  $httpErrorNo=404;
  print_error($ErrorText,$Details,1,false,3);
  if ($cfg['Settings']['jsb']['UseNativeHTTPError'])
    {
    if (!headers_sent()) {Header("HTTP/1.0 404 Not Found");}
    exit;
    }
  $q=DBQuery("SELECT JSBPageID FROM jsb_Pages WHERE SysContext='errors' AND JSBPageID=$httpErrorNo");
  if ($q)
    {
    $target="$cfg[RootURL]$_FRONTDOOR/errors/$httpErrorNo.html?from=$_SERVER[REQUEST_URI]";
    }
  else
    {
    $target="$cfg[RootURL]/error/$httpErrorNo.html?from=$_SERVER[REQUEST_URI]";
    }

  if (headers_sent())
    {
    print "<script>location.href='$target';</script>";
    }
  else
    {
    Header("Location: $target");
    }
  exit;
  }

function getmicrotime(){list($usec, $sec) = explode(" ",microtime()); return ((float)$usec + (float)$sec); }

?>
