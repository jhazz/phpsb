<?
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }
set_error_handler("_coreErrorHandler");
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

if (defined('__SYS_CORE__'))  {  return;  }
define ('__SYS_CORE__',1);
define ('ACCESS_READ',1);       # user can read object
define ('ACCESS_WRITE',2);      # user can edit/delete object
define ('ACCESS_READBOUND',4);  # user can read objects that BindTo this
define ('ACCESS_WRITEBOUND',8); # user can edit/delete/add objects that BindTo this
define ('ACCESS_ALLOW_OTHER_WRITE',16); # user can allow other users to write to this object
define ('ACCESS_ALLOW_OTHER_BIND',32); # user can allow other users to write to this object
mb_internal_encoding("UTF-8");

## Module definitions:
##  class: sys_User
##  class: sys_Session
##
##  global var: $_USER
##  global var: $_SESSION
##  global var: $_CORE

$JSB_Component=false;
$JSB_Interface=false;

class jsb_Control {
	var $JSBPageControlID;
	var $SysContext;
	var $JSBPageID;
	var $Slot;
	var $Arguments;
	var $DesignMode=3;
	var $EditMode=0;
	var $EditableContent=0;
}

class sys_Core
  {
  var $Cartridges;
  var $Components;
  var $Interfaces;
  var $AppEvents;
  var $Settings;
  var $WindowsInited;
  var $Grants,$GrantArrays;
  var $Languages;
  var $Mailer;
  var $Data;

  var $pack_from=array('~' ,':' ,'|' ,'/' ,'\\' ,'.'  ,'%' ,"'" ,'"');
  var $pack_to  =array('~A','~B','~C','~D','~E' ,'~F' ,'~G','~1','~2');
  var $packkeys_from=array('BindTo','EditMode','Insertable','ArgsStr','PageTab','ExpandedItems','JSBPageID','SysContext');
  var $packkeys_to  =array('~H',    '~I',      '~J',        '~K',     '~L',     '~M',           '~N',       '~O');
  var $CartridgesActive; # accessible after LoadCartridgesList()
  var $CartridgesStatus;

  function InitMailer() {
  	if (!isset($this->Mailer)) {
	    include_once ("inc.mailer.php");
	    $this->Mailer=new Mailer();
  	}
  }

  function PutPNGPatchForIE()
    {
    global $cfg;
    print "<style>img{behavior:url('$cfg[PublicURL]/sys/png.htc');}</style>";
    ?><script>
    if (navigator.platform == "Win32" && navigator.appName == "Microsoft Internet Explorer" && window.attachEvent)
    {window.attachEvent("onload", alphaBackgrounds);}
function alphaBackgrounds(){
	var bg,itsAllGood,mypng,rslt = navigator.appVersion.match(/MSIE (\d+\.\d+)/, '');
	itsAllGood=(rslt != null && Number(rslt[1]) >= 5.5);
	for (i=0;i<document.all.length;i++){
		bg = document.all[i].currentStyle.backgroundImage;
		if (itsAllGood && bg){
			if (bg.match(/\.png/i) != null){
				mypng = bg.substring(5,bg.length-2);
				document.all[i].style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+mypng+"', sizingMethod='scale')";
				document.all[i].style.backgroundImage = "url('<? print $cfg['PublicURL'];?>/sys/sp.gif')";
			}
		}
	}
}
</script>
    <?
    }
  function PutButton($args) {
    include_once ("inc.PutButton.php");
    return core_PutButton($args);
  }
  function PutSwf($args) {
    include_once ("inc.PutSwf.php");
    return core_PutSwf($args);
  }
  function PutDropDown($args){
    include_once ("inc.PutDropDown.php");
    return core_PutDropDown($args);
  }
  function PutPages($args){
    include_once ("inc.PutPages.php");
    return core_PutPages($args);
  }
  function PutValueSet($args){
    include_once ("inc.PutValueSet.php");
    return core_PutValueSet($args);
  }
  function PutFormField($args){
    include_once ("inc.PutForm.php");
    return core_PutFormField($args);
  }
  function PutFormOpenGroup($args){
    return core_PutFormOpenGroup($args);
  }
  function PutFormCloseGroup($args){
    return core_PutFormCloseGroup($args);
  }
  function OpenForm ($args){
    include_once ("inc.PutForm.php");
    return core_PutFormOpen($args);
  }
  function CloseForm ($args=false){
    return core_PutFormClose($args);
  }
  function PutLanguageSelector($args){
    include_once ("inc.PutForm.php");
    return core_PutLanguageSelector($args);
  }
  function SetWindowOptions($Options){
    $s="";
    if (($Options['Width'])&&($Options['Height'])){
    	$s.="W.setSize(".intval($Options['Width']).",".intval($Options['Height']).");";
    }
    if ($Options['Title']){
      $s.="W.setTitle('".addslashes($Options['Title'])."');";
    }
    if ($s) print "<script>$s</script>";
  }
  function PutVars(){
    if ($this->hasPutVars) return;
    global $cfg,$_SYSSKIN_NAME,$_ENVIRONMENT,$_THEME_NAME;
    if (!$_ENVIRONMENT){
      print_error("Unknown environment","",1,"sys:PutVars");
      return;
    }
    switch($_ENVIRONMENT){
      case 'f': $SkinURL=$cfg['SkinsURL'].'/'.$_THEME_NAME; break;
      default:  $SkinURL=$cfg['PublicURL'].'/sys/skins/'.$_SYSSKIN_NAME;
    }
    $this->hasPutVars=1;
    print "<script>var PublicURL='$cfg[PublicURL]',SkinURL='$SkinURL',sysPubURL='$cfg[PublicURL]/sys';</script>\n";
  }
/*
  function &LoadLanguages()
    {
    if ($this->Languages) return &$this->Languages;
    global $cfg;
    $f=$cfg['DataPath'].'/.languages';
    if (is_file($f))
      {
      $Lines=file($f);
      foreach ($Lines as $line)
        {
        $line=trim($line);
        if ((!$line)||(substr($line,0,1)=='#')) continue;
        list($LangID,$Enabled,$Caption)=explode (":",$line,4);
        if ($LangID) $this->Languages[$LangID]=array(Enabled=>$Enabled,Caption=>$Caption);
        }
      }
    return &$this->Languages;
    }

  # return array(LangID(tinyint), LanguageCaption(string))
  function GetLanguageByName($Language)
    {
    if (!$this->Languages) $this->LoadLanguages();
    return $this->Languages[$Language];
    }
*/
  function IsIpBanned($ip)
    {
    global $cfg;
    $f=$cfg['DataPath'].'/.banips';
    $fp=fopen ($f,"r"); $s=fread($fp,10000); fclose($fp);
    if (preg_match("/".$ip."/",$s)) {
      # it is banned IP but we should check out white list
      $f=$cfg['DataPath'].'/.nobanips';
      if (is_file($f)) {
        $fp=fopen ($f,"r"); $s=fread($fp,10000); fclose($fp);
        if (preg_match("/".$ip."/",$s)) return false;
        }
      return true;
      }
    return false;
    }

  function PutRelatedLinks ($category,$links,$defaultIconURL=false,$cartridgeName=false)
    {
    if (!is_array($links['Items'])) return;

    global $cfg;
    if (!$this->ContextmenuJSIncluded)
      {
      $this->ContextmenuJSIncluded=true;
      print "<script src='$cfg[PublicURL]/backend/contextmenu.js'></script>\n";
      }
    $s="";
    foreach ($links['Items'] as $l)
      {
      if ($s) $s.=",";
      $s1="c:'".addslashes($l['Caption'])."',u:'".addslashes($l['URL'])."'";
      if ($l['Icon']) $s1.=",i:'".addslashes($l['Icon'])."'";
        else if ((!$l['Bullet'])&&($defaultIconURL)) $s1.=",i:'$defaultIconURL'";
      if ($l['Caption2']) $s1.=",c2:'".addslashes($l['Caption2'])."'";
      if ($l['Caption3']) $s1.=",c3:'".addslashes($l['Caption3'])."'";
      if ($l['Bullet']) $s1.=",b:'".addslashes($l['Bullet'])."'";
      if ($l['Active']) $s1.=",a:1";
      $s1.="\n";
      $s.="{".$s1."}";
      }
    print "\n\n<script>PutRelatedLinks('$category','$links[Caption]',[$s]);</script>\n\n";
    }

  function BanIP($ip,$reason)
    {
    global $cfg;
    # check if he is in the 'white list'
    $f=$cfg['DataPath'].'/.nobanips';
    if (is_file($f))
      {
      $fp=fopen ($f,"r"); $s=fread($fp,100000); fclose($fp);
      if (preg_match("/$ip/i",$s)) return false;
      }
    # yes ban him
    $fp=fopen ($cfg['DataPath'].'/.banips',"a+");
    fputs ($fp,"[$ip] $reason\n");
    fclose ($fp);
    print "You have been banned";
    }

  function PrintTable(&$queryResult,$params)
    {
    require_once ("inc.PrintTable.php");
    PrintTableEditor($queryResult,$params);
    }

  function InitWindows(){
    global $_THEME,$cfg;
    $__=&$GLOBALS['_STRINGS']['_'];
    $this->PutVars();
    if ($this->WindowsInited) return; $this->WindowsInited=1;
    if ($_THEME['WindowStyle']){
      $WindowStyle="";
      foreach($_THEME['WindowStyle'] as $k=>$v){$WindowStyle.=(($WindowStyle)?",":"")."'$k':'$v'";}
      }
   	if ($WindowStyle) print "\n<script>\$STYLE={"."$WindowStyle};</script>";
#    print "</script>\n<script src='$cfg[PublicURL]/sys/windows.js'></script>
#     <script>W.strWait='$__[MSG_PLEASE_WAIT]'; W.style={"."$WindowStyle}; W.defaultTitle='$cfg[SiteName]'</script>";
    }

  function PutBody($Options=false)
    {
    global $cfg,$_THEME_NAME,$_SYSSKIN_NAME;
    $__=&$GLOBALS['_STRINGS']['_'];
    if ($this->hasBodyPut) return;
    if ($Options['PutVars']) $this->PutVars();
    $this->hasBodyPut=true;
    print "<script src='$cfg[PublicURL]/sys/disp.js'></script>\n";
    if ($Options['Environment'])
      {
      global $_ENVIRONMENT;
      $_ENVIRONMENT=$Options['Environment'];
      switch($_ENVIRONMENT) {
        case 'b': case 'bm':
          print "<link rel='stylesheet' href='$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME/backend.css' type='text/css'>";
          break;
        case 'f': print "<link rel='stylesheet' href='$cfg[ThemesURL]/$_THEME_NAME/main.css' type='text/css'>\n";break;
        }
      }
    }


  function LoadTheme($Environment='f')
    {
    global $_THEME,$_THEME_NAME,$cfg,$_SYSSKIN_NAME;
    $__=&$GLOBALS['_STRINGS']['_'];
    

    switch ($Environment)
      {
      case 'f':
		    if (isset($_THEME) && $_THEME['Environment']=='f') return true;
      	if (isset($GLOBALS['ThemesCache']['f'])) {
      		$_THEME=$GLOBALS['ThemesCache']['f'];
      		$_THEME_NAME=$_THEME['Name'];
      		return true;
      	}
        if (!$_THEME_NAME) $_THEME_NAME=$cfg['Settings']['jsb']['ActiveTheme'];
        if (!$_THEME_NAME)
          {
          print_error("Unable to find active theme in settings");
          exit;
          }
        $JSB_ThemeFile="$cfg[ThemesPath]/$_THEME_NAME/theme.php";
        break;
      case 'b': case 'bm':
		    if (isset($_THEME) && $_THEME['Environment']=='b') return true;
      	if (isset($GLOBALS['ThemesCache']['b'])) {
      		$_THEME=$GLOBALS['ThemesCache']['b'];
      		$_THEME_NAME=$_THEME['Name'];
      		return true;
      	}
        $JSB_ThemeFile="$cfg[PHPSB_PATH]/ver/$cfg[PHPSB_VERSION]/sys/public/skins/$_SYSSKIN_NAME/sys_theme.php";
        break;
      }

    if (!is_file($JSB_ThemeFile))
      {
      print_error ($__['ERROR_NO_THEME_FILE'],$JSB_ThemeFile);
      exit;
      }
    else
      {
      include ($JSB_ThemeFile);
      if (!$_THEME)
        {
        print_error ("Theme variable did not declared in themefile",$JSB_ThemeFile);
        exit;
        }
      }

    $_THEME['Name']=$_THEME_NAME;
    $_THEME['Uptime']=filemtime($JSB_ThemeFile);
    $_THEME['ThemePath']="$cfg[ThemesPath]/$_THEME_NAME";
    switch ($Environment)
      {
      case 'f':
        $_THEME['SkinPath']="$cfg[SkinsPath]/$_THEME_NAME";
        $_THEME['SkinURL'] ="$cfg[SkinsURL]/$_THEME_NAME";
        $_THEME['Environment']="f";
      	$GLOBALS['ThemesCache']['f']=$_THEME;
        break;
      case 'b': case 'bm':
        $_THEME['Environment']="b";
        $_THEME['SkinPath']="$cfg[PHPSB_PATH]/ver/$cfg[PHPSB_VERSION]/sys/public/skins/$_SYSSKIN_NAME";
        $_THEME['SkinURL'] ="$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME";
      	$GLOBALS['ThemesCache']['b']=$_THEME;
        break;
      }
    return true;
    }

  function PutProgress($id,$info,$width=200)
    {
    global $cfg,$_SYSSKIN_NAME;
    $this->PutVars();
    if (!$this->ProgressJSIncluded)
      {
      $this->ProgressJSIncluded=1;
      print "\n<script src='$cfg[PublicURL]/sys/progress.js'></script>";
      }
    print "\n\n<script>PutProgress('$id','$info',$width);</script>";
    }

  function ApplicationEvent($EventName,$args=false)
    {
    global $cfg;
    if (!$this->AppEvents)
      {
      $AppEventsFile=file($cfg['DataPath'].'/.app_events');
      foreach ($AppEventsFile as $row)
        {
        $row=trim($row);
        if ((!$row)||(substr($row,0,1)=='#')) continue;
        $a=explode (":",$row,2);
        if ($a[1]) $this->AppEvents[$a[0]]=explode (",",$a[1]);
        }
      }
    $calls=$this->AppEvents[$EventName];
    if ($calls)
      {
      foreach($calls as $call)
        {
        $call=trim($call);
        list ($cartridge,$intfname,$method,$env)=explode (".",$call);
        $interface=&$this->LoadInterface("$cartridge.$intfname");
        if (is_object($interface))
          {
          $interface->$method($args);
          }
        }
      }
    }
/*
  function LoadLanguage($CartridgeName="")
    {

    global $cfg,$_LANGUAGE;

    if (!$_LANGUAGE) {
      $_LANGUAGE=$cfg['Language'];
      if ($_COOKIE['DefaultLanguage']) $_LANGUAGE=$_COOKIE['DefaultLanguage'];
      }
    if ($CartridgeName)
      {
      $dir="$cfg[ScriptsPath]/$CartridgeName";
      if (!is_dir($dir)) $dir="$cfg[PHPSBScriptsPath]/$CartridgeName";
      $langfile1="$dir/$CartridgeName/lang.$_LANGUAGE.php";
      $langfile2="$dir/$CartridgeName/lang.en.php";
      }
    else
      {
      $langfile1="$cfg[ScriptsPath]/lang.$_LANGUAGE.php";
      if (!file_exists($langfile1)) $langfile1="$cfg[PHPSBScriptsPath]/lang.$_LANGUAGE.php";
      $langfile2="$cfg[PHPSBScriptsPath]/lang.en.php";
      }

    if (!file_exists($langfile1))
      {
      if (!file_exists($langfile2))
        {
        print "<h1>Language '$_LANGUAGE' file not found</h1>Required file: $langfile<br>English language file not found too: '$langfile2'";
        return false;
        }
      $langfile1=$langfile2;
      }
    require_once ($langfile1);
    return true;
    }
*/
  function &LoadCartridge($CartridgeName)
    {
    global $_LANGUAGE,$cfg;
    $CartridgeName=strtolower($CartridgeName);
    if (!$this->IsCartridgeActive($CartridgeName)) {return false;}
    if (isset($this->Cartridges[$CartridgeName]))
      {
      $r=&$this->Cartridges[$CartridgeName]->Data;
      if ($r) {return $r;}
      }
    $language=($_LANGUAGE)?$_LANGUAGE:$cfg['DefaultLanguage'];
    if (!$language) {$language="en";}
    $dir="$cfg[ScriptsPath]/$CartridgeName";
    if (!is_dir($dir)) $dir="$cfg[PHPSBScriptsPath]/$CartridgeName";
    $langfile="$dir/lang.$language.php";
    if (file_exists($langfile)) {
      include_once ($langfile);
      }
    else
      {
      $langfile="$dir/lang.en.php";
      if (file_exists($langfile)) {include_once ($langfile);}
      }

    $cartridgephp="$dir/cartridge.php";
    if (file_exists($cartridgephp)) {
      include_once  ($cartridgephp);
      $r=&new $CartridgeName();
      $this->Cartridges[$CartridgeName]->Data=&$r;
      return $r;
      }
    return false;
    }

  function &LoadCartridgesList($andLoadCartridges=false)
    {
    if (($this->CartridgesActive && (!$andLoadCartridges))||$this->CartridgesLoaded) return $this->CartridgesActive;
    $crc="";
    $mustcrc="";
    # todo: CRC+SERVER_IP+HSP_PASSWORD protection
#    if ($this->CartridgesActive) return;

    global $cfg;
    $lines=file($cfg['DataPath'].'/.cartridges');
    if (!$lines) return false;
    foreach ($lines as $row)
      {
      $row=trim($row);
      if (substr($row,0,1)=='#')
        {
#        print $row;
        if (substr($row,0,5)=='#CRC:') $mustcrc=substr($row,5);
        if (substr($row,0,5)=='#PAC:') $this->CartridgePackage=substr($row,5);
        continue;
        }
      $crc=md5($crc.$row);
      $r=explode (":",$row,3);
      $Name=strtolower(trim($r[0]));
      $Active=intval($r[1]);
      parse_str($r[2],$info);
      $this->CartridgesInfo[$Name]=$info;
      if ($info['till'] && ($info['till']<time())) $Active=false;
      $this->CartridgesActive[$Name]=$Active;
      if ($andLoadCartridges && $Active) $this->LoadCartridge($Name);
      }
    return $this->CartridgesActive;
    }

  function IsCartridgeActive($CartridgeName)
    {
    # todo: analyze license time expiration
    if (!isset($this->CartridgesActive))  $this->LoadCartridgesList();
    return ($this->CartridgesActive[strtolower($CartridgeName)]==1);
    }

  function &LoadInterface($InterfaceName)
    {
    global $cfg;
    $InterfaceName=basename($InterfaceName);
    $r=&$this->LoadedInterfaces[$InterfaceName];
    if ($r) {return $r;}
    list ($cartridge,$interface_Class)=explode ('.',$InterfaceName,2);
#    if (!$this->IsCartridgeActive($cartridge)) {return false;}
    if (!$this->LoadCartridge($cartridge)) return;

    $dir="$cfg[ScriptsPath]/$cartridge";
    if (!is_dir($dir)) $dir="$cfg[PHPSBScriptsPath]/$cartridge";
    $f="$dir/protected.$interface_Class.php";
    if (!is_file($f)) { $f="$dir/public.$interface_Class.php"; }
    if (!is_file($f))
      {
      print_error ("Interface file not found",$f,0,"Load interface");
      return false;
      }
    require_once ($f);
    $interface_class=$cartridge.'_'.$interface_Class;
    if (!class_exists ($interface_class))
      {
      print_developer_warning("Class declaration not found in interface","class $interface_class{} in '".basename($f)."'");
      return false;
      }
    $r=&new $interface_class();
    $this->LoadedInterfaces[$InterfaceName]=&$r;
    return $r;
    }

  function &CreateControl($args,$ApplyDefaults=false,$EditMode=false)
    {
    # args['ControlData']         - from pagecontrol database
    # args['ControlClass'] - name of conrol class (could be declared in ControlRow)
    # args['ControlData']->PropertiesStr | args['PropStr']:str | args['Properties']:array
    # args['BindStr']
    # args['Language']

    if (is_string($args)) {$ControlClass=$args;}
    if (is_array($args)) {extract ($args);}

    
    $newControl=($ControlData) ? $ControlData : new jsb_Control();

    
    if ($ControlData) {$ControlClass=$ControlData->ControlClass;}

    if ($ControlClass)
      {
      $Component=&$this->LoadComponent($ControlClass,$Language); #($ApplyDefaults || $EditMode));
      }
    else
      {
      print_error ("Cannot create control for unknown class");
      return false;
      }

    if (!is_object($Component))
      {
      print_developer_warning("Cannot acquire Component for Control '$ControlClass'");
      return false;
      }
    $newControl->Component=&$Component;

    if (($EditMode || $ApplyDefaults) && (method_exists($Component,"InitComponent"))) $Component->InitComponent();
    if ($ApplyDefaults)
      {
      foreach ($Component->Propdefs as $PropName=>$d)
        {
        if (isset($d['DefaultValue'])) $newControl->Properties[$PropName]=$d['DefaultValue'];
        }
      }

    if ($ControlData)
      {
      if (($ControlData->PropertiesStr)&&(!$PropStr))
        {
        $PropStr=$ControlData->PropertiesStr;
        }
      }

    $PropertiesArray=false;
    if ($PropStr)
      {
      $PropertiesArray=&explode_properties($PropStr);
      }
    else
      {
      if (is_array($Properties)) $PropertiesArray=$Properties;
      }

    if ($PropertiesArray)
      {
      foreach ($PropertiesArray as $PropName=>$v)
        $newControl->Properties[$PropName]=$v;
      }
    if ($BindStr)
      {
      $newControl->Bindings=&explode_properties($BindStr);
      }
      
    return $newControl;
    }

  #
  # $ControlClass is a component name that renders controls. e.g. 'site.TText'
  function &LoadComponent($ControlClass,$language=false,$initComponent=false)
    {
    global $cfg;

    $c=&$this->Components[$ControlClass];
    if ($c) {return $c; }
    list ($CartridgeName,$class_name)=explode ('.',$ControlClass,2);
    if ((!$this->IsCartridgeActive($CartridgeName)) && ($CartridgeName!="core")) {
      print_developer_warning("Inactive cartridge",$CartridgeName);
      return false;
      }

    if (!$language)
      {
      global $_LANGUAGE;
      $language=($_LANGUAGE)?$_LANGUAGE:$cfg['DefaultLanguage'];
      if (!$language) {$language="en";}
      }
    $dir="$cfg[ScriptsPath]/$CartridgeName";
    if (!is_dir($dir)) $dir="$cfg[PHPSBScriptsPath]/$CartridgeName";
    $langfile="$dir/lang.$language.php";
    if (file_exists($langfile)) {include_once ($langfile);}
    else {
      $langfile="$dir/lang.en.php";
      if (file_exists($langfile)) {include_once ($langfile);}
      }

    $f="$dir/$class_name.php";
    if (!file_exists($f))
      {
      return false;
      }
    require_once ($f);

    $comp_class=$CartridgeName.'_'.$class_name;
    if (!class_exists ($comp_class))
      {
      return false;
      }
    eval ("\$r=&new $comp_class();");
    if ($initComponent) { if (method_exists($r,"InitComponent")) $r->InitComponent(); }
    $r->CartridgeName=$CartridgeName;
    $this->Components[$ControlClass]=&$r;
    return $r;
    }

  # Prevent double user posting blockage
  function UnlockTwicePost()
    {
    $this->DONOTLOCKPOST=true;
    }

  function Serialize(&$Properties,$level=0,$packKeys=false)
    {
    if (!is_array($Properties))
      {
      $s=str_replace('%7E','~',urlencode(str_replace($this->pack_from,$this->pack_to,$Properties)));
      return $s;
      }
    $s="";
    foreach ($Properties as $k=>$v)
      {
      $k=urlencode(str_replace($this->pack_from,$this->pack_to,$k));
      if ($v)
        {
        if (is_array($v))
          {
          if ($level<10)
            {
            $s.=$k.'::'.$this->Serialize($v,$level+1).'|';
            }
          }
        else
          {
          if ($v)
            {
            if ($packKeys) $k=str_replace ($this->packkeys_from,$this->packkeys_to,$k);
            $s.=$k.':'.str_replace('%7E','~',urlencode(str_replace($this->pack_from,$this->pack_to,$v))).'|';
            }
          }
        }
      }
    return $s;
    }

  function Unserialize(&$PropertiesStr,$resetPos=true)
    {
    $result=false;
    static $p;
    if ($resetPos) {$p=0;}
    if (!$PropertiesStr) return false;
    while ($p<strlen($PropertiesStr))
      {
      if (substr($PropertiesStr,$p,1)=='|') break;
      $p1=strpos($PropertiesStr,':',$p);
      $p2=strpos($PropertiesStr,':',$p1+1);
      if ($p1===false) {
        # SCALAR UNSERIALIZE!!!
        return ($PropertiesStr)?str_replace($this->pack_to,$this->pack_from,urldecode($PropertiesStr)):"";
        break;
        }
      $name=substr($PropertiesStr,$p,$p1-$p);
			$name=str_replace($this->pack_to,$this->pack_from,urldecode($name));
      $name=str_replace($this->packkeys_to,$this->packkeys_from,$name);
      if ($p2==($p1+1))
        {
        # array declaration starts from ::
        $p=$p2+1;
        $result[$name]=$this->Unserialize($PropertiesStr,false);
        $p++;
        }
      else
        {
        # simple var
        $p2=strpos($PropertiesStr,'|',$p1+1);
        if ($p2===false)
          {
          $value=substr($PropertiesStr,$p1+1);
          $p=strlen($PropertiesStr)+1;
          }
        else
          {
          $value=substr($PropertiesStr,$p1+1,$p2-$p1-1);
          $p=$p2+1;
          }
        $result[$name]=str_replace($this->pack_to,$this->pack_from,urldecode($value));
        }
      }
    return $result;
    }

    function DropCache($CacheDir=false,$NextDir=false)
    {
    	global $cfg;
    	if ($CacheDir)
    	{
    		if ($CacheDir=='*')
    		{
    			$tdir=$cfg['TempPath'];
    		}
    		else
    		{
    			$tdir="$cfg[TempPath]/$CacheDir";
    		}
    	}
    	else
    	{
    		$tdir="$cfg[TempPath]/pages";
    	}

    	if ($NextDir) $tdir.="/$NextDir";
    	if (!is_dir($tdir)) return;
    	$d=opendir($tdir);
    	while (($fname=readdir($d))!==false)
    	{
    		if (($fname=='.')||($fname=='..')) continue;
    		$fullname="$tdir/$fname";
    		if (is_file($fullname)) {
    			unlink($fullname);
    		}
    		if (is_dir($fullname))
    		{
    			$this->DropCache($CacheDir,$fname);
    			rmdir($fullname);
    		}
    	}
    	closedir($d);
    }

		function &ParseTableStyle($TableStyle=0) {
			global $_THEME;
			if (!$_THEME) {
				print_developer_warning("Undefined Theme");
				return false;
			}
			$ts=&$_THEME['TableStyles'][$TableStyle];
			
	   	list($ts['te'],$ts['ce'])=get_css_pair($ts['Even'],"td");
			list($ts['to'],$ts['co'])=get_css_pair($ts['Odd'],"td");
			if ($ts['Top']) {
				list($ts['tt'],$ts['ct'])=get_css_pair($ts['Top'],"td");
			} else {$ts['tt']=$ts['to']; $ts['ct']=$ts['ce']; }
			
			if ($ts['Bottom']) {
				list($ts['tb'],$ts['cb'])=get_css_pair($ts['Bottom'],"td");
			} else {$ts['tb']=$ts['te']; $ts['cb']=$ts['ce']; }
			
			list($ts['th'],$ts['ch'])=get_css_pair($ts['Top'],"th");
			if ($ts['LeftEven']) {
				list($ts['tle'],$ts['cle'])=get_css_pair($ts['LeftEven'],"th");
				list($ts['tlo'],$ts['clo'])=get_css_pair($ts['LeftOdd'],"th");
			} else {$ts['tle']=$ts['te']; $ts['cle']=$ts['ce']; $ts['tlo']=$ts['to']; $ts['clo']=$ts['co']; }
			if ($ts['RightEven']) {
				list($ts['tre'],$ts['cre'])=get_css_pair($ts['RightEven'],"td");
				list($ts['tro'],$ts['cro'])=get_css_pair($ts['RightOdd'],"td");
			} else {$ts['tre']=$ts['te']; $ts['cre']=$ts['ce']; $ts['tro']=$ts['to']; $ts['cro']=$ts['co']; }
			if ($ts['Group1']) {
				list($ts['tg1'],$ts['cg1'])=get_css_pair($ts['Group1'],"td");
			} else {$ts['tg1']=$ts['th']; $ts['cg1']=$ts['ch'];}
			if ($ts['Group2']) {
				list($ts['tg2'],$ts['cg2'])=get_css_pair($ts['Group2'],"td");
			} else {$ts['tg2']=$ts['th']; $ts['cg2']=$ts['ch'];}
			if ($ts['HiEven']) {
				list($ts['the'],$ts['che'])=get_css_pair($ts['HiEven'],"td");
				list($ts['tho'],$ts['cho'])=get_css_pair($ts['HiOdd'],"td");
			} else {
				$ts['the']=$ts['te']; $ts['che']=$ts['ce'];
				$ts['tho']=$ts['to']; $ts['cho']=$ts['co'];
			}
    return $ts;
		}
    
  } # end of class #sys_Core

class sys_User
  {
  var $UserID;
  var $Groups;  # array of GroupID which the User member of
  var $Roles=false;
  var $AllowedMethods=false;
  var $ProtectedInterfaces=false;
  var $AccessInfoLoaded=false;


  function sys_User ()
    {
    $this->UserID=0;
#    $this->Groups=array(-2=>"guest");
#    $this->GroupsByName=array("guest"=>-1);
    }

  # Format: "$Cartridge:$RoleName"
  function HasRole($RoleName)
    {
    if (!$this->Groups) $this->LoadGroups();
    if ($this->Groups)
      {
      if (array_search(-3,$this->Groups)!==false) return true;
      }

    if (!$this->Roles) $this->LoadAccessInfo();
    return isset($this->Roles[$RoleName]);
    }

  # Call has format : "$Cartridge.$Interface.$Method"
  function IsActionAllowed($Action)
    {
    if (!$this->Groups) $this->LoadGroups();
    list($c,$i,$m,$e)=explode(".",$Action);
    if ($this->ProtectedInterfaces ===false)
      {
      $this->LoadInterfaceProtectionInfo();
      }
    if (!isset($this->ProtectedInterfaces["$c.$i"]))
      {
      return true;
      }

    if (!$this->Roles) $this->LoadAccessInfo();

    $allowed=isset($this->AllowedMethods["$c.$i.$m"]);
    if (!$allowed) {
      trace ("You are not allowed to execute '$c.$i.$m'",2);
      }
    # if admin ... he has access to all
    if ($this->Groups)
      {
      if (array_search(-3,$this->Groups)!==false) return true;
      }
    return $allowed;
    }

  function LoadInterfaceProtectionInfo()
    {
    $qprotected=DBQuery ("SELECT CONCAT(Cartridge,'.',InterfaceName) AS CartInterface FROM um_ProtectIntf","CartInterface");
    $this->ProtectedInterfaces=&$qprotected->Rows;
    }

  function LoadGroups()
    {
    if (!$this->UserID) {return;}
    if (!$this->Groups)
      {
      $qg=DBQuery ("SELECT GroupID FROM um_UserInGroups WHERE UserID = $this->UserID","GroupID");
      $this->Groups=array_keys($qg->Rows);
      }
    }
  function LoadAccessInfo()
    {
    if ($this->AccessInfoLoaded) {return;}

    $this->AccessInfoLoaded=true;
    $this->AllowedMethods=$this->Roles=false;
    if (!$this->UserID) {return;}
    $qug=DBQuery ("
      SELECT CONCAT(r.Cartridge,':',r.Role) AS Access,r.AllowMode, r.AllowedMethods
      FROM um_GroupRoles r
      LEFT JOIN um_UserGroups   g  ON  r.GroupID = g.GroupID
      LEFT JOIN um_UserInGroups ug ON  g.GroupID = ug.GroupID
      WHERE ug.UserID = $this->UserID
      GROUP BY r.AllowMode, ug.UserID, Access",array("AllowMode","Access"));
    if (!$qug)
      {
      return;
      }
    $Allows=&$qug->Rows["1"];
    $Denies=&$qug->Rows["2"];

    foreach ($Allows as $Access=>$row)
      {
      list ($Cartridge,$Role)=explode(":",$Access);
      if (isset($Denies[$Access])) {continue;}
      $this->Roles[$Access]=true;
      $IMethods=$row->AllowedMethods;
      if (!$IMethods) {continue;}
      $IMethods=explode ("|",$IMethods);
      foreach ($IMethods as $IMethod)
        {
        list ($Interface,$MethodsStr)=explode (":",$IMethod);
        $Methods=explode(",",$MethodsStr);
        foreach($Methods as $Method)
          {
          $this->AllowedMethods["$Cartridge.$Interface.$Method"]=true;
          }
        }
      }
    }

  function LoadByID ($UserID)
    {
    $UserID=intval($UserID);
    $q1=DBQuery("SELECT * FROM um_Users WHERE UserID=$UserID");
    $r=$q1->Top;
    if (!$r) {return false;}
    while (list ($k,$v)=each($r))
      {
      if ((!is_int ($k))&&(strtolower($k)!='password'))  { $this->$k=$v; }
      }
    $this->UserID=$UserID;
    return true;
    }

  function AuthorizeSessionToUserID($UserID)
    {
    global $_SESSION;
    $_SESSION->UserID=$UserID;
    $_SESSION->Save();
    $_ENV->ApplicationEvent("OnUserLogin");
    DBExec("UPDATE um_Users SET Visits=Visits+1,LastVisit=".time()." WHERE UserID=$UserID");
    }

  function SetGrant($args)
    {
    extract(param_extract(array(
      UserID=>'int',
      GroupID=>'int',
      ClassName=>'string',
      Context=>'string',
      ObjectID=>'int',
      AccessBits=>'int',
      ForwardTo=>'string',
      call=>'string',
      ),$args));

    if ((!$UserID)&&(!$GroupID))
      {
      print_developer_warning("No group or user defined to set grant to");
      return false;
      }
    if ($GroupID) $Keys=array(GroupID=>$GroupID);
    else $Keys=array(UserID=>$UserID);
    $Grantor=$this->UserID;
    $Keys+=array(ClassName=>$ClassName,Context=>$Context,ObjectID=>$ObjectID);
    DBReplace(array(Table=>"sys_Grants",
      Values=>array(AccessBits=>$AccessBits,Grantor=>$Grantor),
      Keys=>$Keys));
    if ($ForwardTo) return array(ForwardTo=>$ForwardTo);
    if ($call==modal) return array(ModalResult=>true);
    return true;
    }

  function &ReadGrants ($UserID=0)
    {
    # todo check grants to user group
    if (!$UserID) $UserID=$this->UserID;
    $r=&$this->UserGrants[$UserID];
    if ($r) return $r;
    $r=&DBQuery("SELECT * FROM sys_Grants WHERE UserID=$UserID",array("ClassName","Context","ObjectID"));
    if ($r)
      {
      $this->UserGrants[$UserID]=&$r->Rows;
      return $r->Rows;
      }
    return false;
    }

  function GetGranted($args)
    {
    extract(param_extract(array(
      UserID=>'int',
      GroupID=>'int',
      ClassName=>'string',
      Context=>'string',
      ObjectID=>'int',
      AccessBits=>'int',
      ),$args));

    if (!$ClassName)
      {
      print_developer_warning("GetGranted() gets no ClassName in arguments","$ClassName/$Context/$ObjectID");
      return false;
      }

    if (!$UserID) $UserID=$this->UserID;
    $r=&$this->UserGrants[$UserID];
    if (!$r) $r=&$this->ReadGrants($UserID);
    if (!$r) return false;
    $result=false;
    $a1=array_keys($r);
    foreach ($a1 as $aClassName)
      {
      if ($aClassName!=$ClassName) continue;
      $a2=array_keys($r[$ClassName]);
      foreach ($a2 as $aContext)
        {
        if ($aContext!=$Context) continue;
        $a3=array_keys($r[$ClassName][$Context]);
        foreach ($a3 as $aObjectID)
          {
          $row=&$r[$aClassName][$aContext][$aObjectID];
          if ((!$aObjectID)||(!$ObjectID)||($aObjectID==$ObjectID))
            {
            if ((!$AccessBits) || (($row->AccessBits & $AccessBits)==$AccessBits))
              $result[]=$row;
            }
          }
        }
      }
    return $result;
    }
  } # end class sys_User


##########################################
##
## class: sys_Session
## ----------
##
## One $_SESSION var defining in this php module
##
##   ->Vars[] - variables
##   ->Save() - Save session variables
##

class sys_Session
  {
  var $Vars;
  var $SessionKey;
  var $MachineKey;
  var $IsNonCookie=false;
  var $RemoteHost;
  var $FrontDoorSessionKey;
  var $AddCount_Visits;
  var $AddCount_Unique;
  var $AddCount_Errors;

  function sys_Session()
    {
    global $cfg;
    global $_USER;

    $this->SessionKey=DBEscape($_COOKIE['SessionKey']);
    $this->MachineKey=DBEscape($_COOKIE['MachineKey']);
    $this->RemoteHost=DBEscape($_SERVER['REMOTE_ADDR']);

    $pinfo=BindPathInfo("jsb".$_SERVER["PATH_INFO"]);
    if ($pinfo->SessionKey)
      {
      $this->FrontDoorSessionKey=$pinfo->SessionKey;
      }

    $this->AddCount_Visits=0;
    $this->AddCount_Unique=0;
    $this->AddCount_Errors=0;

    $now=time();
    $date=getdate ($now);
    if (!$this->MachineKey)
      {
      $this->IsNonCookie=true;
      $MachineKey=md5(uniqid("41341"));
      SetCookie("MachineKey",$MachineKey,$now+10*12*30*24*60*60,$cfg['Session']['CookieURL']); # 10 years
      $this->AddCount_Unique=1;
      # DON'T SET UP $this->MachineKey IF CLIENT IsNonCookie but try to set this to a client
      }


    if (($this->FrontDoorSessionKey)&&(!$this->SessionKey))
      {
      list ($SessionKeyPart,$IP)=explode ("_",$this->FrontDoorSessionKey);
      if ($this->RemoteHost!=$IP)
        {
        $qsession=false;
        }
      else
        {
        $qsession=DBQuery("SELECT * FROM um_Sessions
          WHERE SessionKey='$this->FrontDoorSessionKey' AND Closed<>1 AND ((Locked=1) OR (UpdateTime>=".($now-$cfg['Session']['Timeout'])."))");
        }

      if ($qsession)
        {
        # Client seems dont support cookies. Use session via URL and
        $this->SessionKey=$this->FrontDoorSessionKey;
        SetCookie ("SessionKey",$this->SessionKey,$now+$cfg['Session']['CookieLife'],$cfg['Session']['CookieURL']);
        $_ENV->ApplicationEvent("OnSessionOpen");
        }
      else
        {
        # Client comes from FrontDoor with other SessionKey. Lets think that key he give is a key of Referree
        $qfd=DBQuery("SELECT OpenCount FROM stat_Referrees WHERE RefSessionKey='$this->FrontDoorSessionKey'");
        if ($qfd)
          {
          DBExec ("UPDATE stat_Referrees SET OpenCount=OpenCount+1 WHERE RefSessionKey='$this->FrontDoorSessionKey'");
          }
        else
          {
          # try to find referree in old Sessions (it could be purged of course)
          $q=DBQuery ("SELECT UserID,Browser,Host,IPaddr FROM um_Sessions WHERE SessionKey='$this->FrontDoorSessionKey'");
          $Caption=$q->Top->Browser;
          $UserID=intval($q->Top->UserID);
          $IPaddr=$q->Top->IPaddr;
          DBExec ("INSERT INTO stat_Referrees (OpenCount,RefSessionKey,Caption,UserID,IPaddr)
            VALUES (1,'$this->FrontDoorSessionKey','$Caption',$UserID,'$IPaddr')");
          }
        # after this we opening new session
        }
      }


    # Load session vars if sessionkey has in the URL or in the COOKIE
    if ($this->SessionKey)
      {
      if (!$qsession)
        {
        # It could be loaded at the FrontDoor and don't need to reload again
        $qsession=DBQuery("SELECT * FROM um_Sessions
        WHERE SessionKey='$this->SessionKey' AND Closed<>1 AND ((Locked=1) OR (UpdateTime>=".($now-$cfg[Session][Timeout])."))");
        }

      if ($qsession)
        {
        $r=$qsession->Top;
        $vars=&$_ENV->Unserialize($r->VarsData);
        if ($vars) foreach ($vars as $k=>$v) {$this->$k=$v;}
        foreach ($r as $k=>$v) {if ((!is_int ($k))&&($k!='VarsData')&&($k!='MachineKey')) {$this->$k=$v;}}
#        DBExec ("UPDATE um_Sessions SET UpdateCount=UpdateCount+1,UpdateTime=".time()." WHERE SessionKey='$this->SessionKey'");
        }
      else
        {
        $this->SessionKey="";
        # if session key expired or wrong. forget it
        }
      }

    if (!$this->SessionKey)
      {
      # Try to setup Session key to client

      # Clear too old sessions
      DBExec ("DELETE FROM um_Sessions WHERE UpdateTime<".($now-$cfg['Session']['KeepStatisticTimeout']));
      $this->SessionKey=substr(md5(uniqid("z121")),0,5).'_'.$this->RemoteHost;
      SetCookie ("SessionKey",$this->SessionKey,$now+$cfg['Session']['CookieLife'],$cfg['Session']['CookieURL']);

      # register sessionkey
      $this->AddCount_Visits=1;
#      $remotehostname=DBEscape(gethostbyaddr($_SERVER['REMOTE_ADDR']));
      if ($_SERVER['HTTP_VIA']) $remotehostname.="; proxy `$_SERVER[HTTP_VIA]`";
      if ($_SERVER['HTTP_X_FORWARDED_FOR']) $remotehostname.="; xforwardfor:`$_SERVER[HTTP_X_FORWARDED_FOR]`";
#      $remotehostname=DBEscape($remotehostname);
      $s="INSERT INTO um_Sessions (SessionKey,UserID,LoginTime,
         UpdateTime,IPaddr,MachineKey,Language,Browser,Closed,UpdateCount)
         VALUES ('$this->SessionKey',0,$now,$now,'".$this->RemoteHost."','$this->MachineKey','".$_SERVER[HTTP_ACCEPT_LANGUAGE]."','".DBEscape($_SERVER[HTTP_USER_AGENT])."',0,0)";
      DBExec ($s);
      $this->LoginTime=$now;
      $this->UserID=0;
      }

    if ($this->UserID)
      {
      $_USER->LoadByID ($this->UserID);
      }
    return $this;
    }

  function Save()
    {
    if (!$this->SessionKey)
      {
      return;
      }
    $arr=get_object_vars($this);
    $Reserved=explode (',','RemoteHost,VarsData,SessionKey,Language,Browser,AddCount_Visits,'
    .'AddCount_Unique,AddCount_Errors,UpdateCount,'
    .'Closed,MachineKey,LoginTime,IPaddr,Locked,'
    .'Host,UpdateTime,UserID,KeepLocked,IsNonCookie');

    foreach ($Reserved as $k) {unset($arr[$k]);}
    $props=$_ENV->Serialize($arr);
    $sl=($this->KeepLocked)?"Locked=1,":"";
    DBExec ("UPDATE um_Sessions SET $sl Closed=".intval($this->Closed)."
      ,UserID=".intval($this->UserID)."
      ,UpdateTime=".time()."
      ,VarsData='$props'
      ,UpdateCount=UpdateCount+1
      WHERE SessionKey='".$this->SessionKey."'");
    }

  function Close()
    {
    $this->Closed=true;
    $_ENV->ApplicationEvent("OnSessionClose");
    }
  } # sys_Session


#####
function concat_url_args($url,$argstr)
  {
  if ($url)
    {
    $p=strpos($url,'?');
    if (!is_integer($p)) {$url.="?";}
    else
      {
      if ($p!=(strlen($url)-1)) {$url.="&";}
      }
    $url.=$argstr;
    }
    return $url;
  }

function param_extract($descriptions,&$src)
  {
  if (!function_exists("preserve_int_checkboxes"))
    {
    function preserve_int_checkboxes(&$v,$def)  { $v=($v)?intval($v):$def; }
    }
  foreach($descriptions as $param=>$type)
    {
    $required=false;
    list($type,$default)=explode ("=",$type,2);
    list($type,$subtype)=explode (":",$type,2);
    if (substr($type,0,1)=='*') {$type=substr($type,1); $required=1;}

    if (substr($type,0,1)=='&') {$type=substr($type,1); $v=&$src[$param];
    } else {
	    if (is_array($src)) {if (isset($src[$param])) $v=&$src[$param]; else $v=&$default;}
	    elseif (is_object($src)) {if (isset($src->$param)) $v=&$src->$param; else $v=&$default;}
	    else {$v='';}
    }

    switch ($type)
      {
      case "size":
        list($w,$h)=explode ("x",$v);
        $w=intval($w); $h=intval($h);
        $result[$param]=($w && $h)?($w.'x'.$h):"";
        break;
      case "int":
        $result[$param]=intval($v);
        break;
      case "float":
        $result[$param]=str_to_float($v);
        break;
      case "nonesc_string":
        $result[$param]=(get_magic_quotes_gpc())?stripslashes($v):$v;
        break;
      case "nonesc_langstring":
        $result[$param]=langstr_get((get_magic_quotes_gpc())?stripslashes($v):$v);
        break;

      case "trimstring":
        $v=trim($v);
      case "string":
        $result[$param]=DBEscape($v,true);
        break;
      case 'langstring':
      	$result[$param]=DBEscape(langstr_get($v),true);
      	break;
      case "int_checkboxes":
        if (($v)&&(is_array($v)))
          {
          array_walk($v,"preserve_int_checkboxes",intval($default));
          $result[$param]=&$v;
          } else $result[$param]=false;
        break;
      case "object":
      	$result[$param]=&$v;
      	break;
      case "array":
        if (is_array($v))
          {
          $kk=array_keys($v);
          switch($subtype)
            {
            case 'string': foreach ($kk as $k2) $v[$k2]=DBEscape($v[$k2]);break;
            case 'int':foreach ($kk as $k2) $v[$k2]=intval($v[$k2]);break;
            case 'float':foreach ($kk as $k2) $v[$k2]=str_to_float($v[$k2]);break;
            }
          $result[$param]=$v;
          }
          break;
      default:
        $result[$param]=$v;
      }
      if (($required) && (!$result[$param])) {
      	$s="";
      	if (function_exists("debug_backtrace")) {
      		$a=debug_backtrace ();
      		if ($a) {
      			$s="param_extract called from ".$a[1]['file']." line:".$a[1]['line']." function ".$a[1]['function']."()";
      		}
      	}
      	print_developer_warning("Required parameter '$param' is NULL",$s);
      	exit;
      }
    }
  return $result;
  }

function ActionURL($action,$Args=false)
  {
  global $cfg,$_USER,$_SESSION,$_ENVIRONMENT;
  list($c,$i,$m,$e)=explode(".",$action);
  if (!$e) $e=$_ENVIRONMENT;
  $action=implode (".",array($c,$i,$m,$e));
  if (!$_USER->IsActionAllowed($action)) {return "#";}
  $s=$cfg['ActionURL']."/$action";
  unset($Args['ArgsStr']);
  if ($Args) $s.="?ArgsStr=".$_ENV->Serialize($Args,0,true);
  return $s;
  }

function implode_properties(&$Properties,$level=0)
  {
  return $_ENV->Serialize($Properties);
  }

function &explode_properties(&$PropertiesStr,$resetp=true)
  {
  return $_ENV->Unserialize($PropertiesStr,$resetp);
  }

function print_developer_warning ($Description,$Details="")
  {
  global $cfg;
  trace ($Description." ".$Details,2);
  if (!$cfg['SilentMode'])
    {
    print_warning ($Description,$Details,2);
    }
  }

function print_warning ($Description,$Details="",$type=0,$Module="",$AlertStr="")
  {
  global $cfg,$_MODULENAME,$_SYSSKIN_NAME,$_STRINGS;
  if (substr($Description,0,1)=='[')
    {
    $s=substr($Description,1,-1);
    foreach ($_STRINGS as $languageSet=>$set)
      {
      if (isset($set[$s])) {$Description=$set[$s]; break;}
      }
    }
  if (!$Module) $Module=$_MODULENAME;
  $js1=str_replace (array("'","\\","\n","\r","<",">"),array('"',"/"," "," ","&lt;","&gt;"),$Description);
  $js2=str_replace (array("'","\\","\n","\r","<",">"),array('"',"/"," "," ","&lt;","&gt;"),$Details);
  print "<script>try{raiseError('$js1','$js2');}catch(e){}</script>";
  print "<table width='100%' border=0><tr>";
  switch ($type)
    {
    case 1: $ico='ico_error.gif'; break;
    case 2: $ico='ico_deverror.gif'; break;
    default: $ico='ico_warning.gif';
    }
  print "<td bgcolor='#f8f0f0'><table width='100%' border=0 cellpadding=2>
   <tr valign='top'><td width='30' style='font-family: Verdana,Arial; font-size:10px; color:#cc0000' align='center'>
   <img src='$cfg[PublicURL]/sys/$ico'><br>$AlertStr</td>
   <td width='50' style='font-family: Verdana,Arial; font-size:10px; color:#666666'><b>$Module</td>
   <td width='99%' style='font-family: Verdana,Arial; font-size:10px;'><font color='#000000'><b>$Description</b>
   </font><br/><font color='808080'>".(($Details)?"$Details":"")."</font></td>
   </tr></table></td></tr></table>";
  }

function print_error ($Description,$Details="",$type=0,$Module=false,$IntruderAlert=0)
  {
  # $type=0 - warning
  # $type=1 - critical error
  # $type=2 - developer warnin

  # $IntruderAlert=0 - no alert
  # $IntruderAlert=1 - just count alert per ip
  # $IntruderAlert=2 - need to ban ip after 10 tries
  # $IntruderAlert=3 - page not found - Do not print error. Record it and ban if too much missing queries from one IP


  global $cfg,$_MODULENAME,$_SESSION;
  $_SESSION->AddCount_Errors++;

  $RecordErrors=$cfg['RecordErrors'];
  $cfg['RecordErrors']=0;
  $__=$GLOBALS['_STRINGS']['_'];
  if (!$Module) $Module=$_MODULENAME;
  if ($IntruderAlert>0)
    {
    $ip=$_SERVER[REMOTE_ADDR];
    $q=DBQuery ("SELECT IPaddr FROM sys_Intruders WHERE IPaddr='$ip'");
    $a=array("CounterLow","CounterMedium","CounterHigh");
    $CounterName=$a[$IntruderAlert]; if (!$CounterName) $CounterName='CounterLow';
    if ($q)
      {
      DBExec ("UPDATE sys_Intruders SET DetectTime=".time().", $CounterName=$CounterName+1 WHERE IPaddr='$ip'");
      }
    else
      {
      DBExec ("INSERT INTO sys_Intruders (IPaddr,DetectTime,$CounterName) VALUES ('$ip',".time().",1)");
      }
    $maxcount=intval($cfg['AutoBanIPAfterHighAlerts']);
    if (($IntruderAlert>=2)&&($maxcount))
      {
      $q=DBQuery ("SELECT CounterHigh FROM sys_Intruders WHERE IPaddr='$ip'");
      $CounterHigh=$q->Top->CounterHigh;
      if ($CounterHigh>$maxcount)
        {
        # Ban him !
        $_ENV->BanIP($ip,"More than $CounterHigh high alerted errors");
        }
      }
    }

  if ((!$cfg['SilentMode'])&&($IntruderAlert!=3))
    {
    if ($IntruderAlert) $IntruderAlertStr="<br><font color='red'>intruder level:$IntruderAlert</font>";
    print_warning($Description,$Details,$type,$Module,$IntruderAlertStr);
    }

  if ($RecordErrors)
    {
    global $cfg,$_USER;
    $IntruderAlert=intval($IntruderAlert);
    $fp=fopen ($cfg['DataPath'].'/.errors','a+');
    fputs ($fp,$_SERVER["REQUEST_URI"]."\n");


    $s=array(
      IP=>$_SERVER["REMOTE_ADDR"],
      'time'=>date("Y-m-d H:m:s"),
      'type'=>$type,
      'intrud'=>$IntruderAlert,
      'module'=>$Module,
      'userid'=>$_USER->UserID,
      'ref'=>$_SERVER['HTTP_REFERER']);
    $s=$_ENV->Serialize($s);

    fputs ($fp,"- $s\n");
    fputs ($fp,"- ".nl2br($Description)."\n");
    fputs ($fp,"- ".nl2br($Details)."\n");
    fputs ($fp,"\n");
    fclose($fp);
    }
  $cfg['RecordErrors']=$RecordErrors;
  }

function get_css_pair($css,$default_tag=false)
  {
  list($t,$c)=explode ('.',$css);
  if (!$t) {$t=$default_tag;}
  if ($c) {$cc=" class='$c'";}
  return array($t,$cc,$c);
  }

if (!function_exists('mb_ucfirst') && function_exists('mb_substr')) {
  function mb_ucfirst($string) {
    $string = mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    return $string;
  }
}

function format_date ($format,$date)
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  if (!$date) return;
  $mm_names=explode (',',$__['MONTH_NAMES']);
  $m_names=explode (',',$__['SHORT_MONTH_NAMES']);
  $swd_names=explode (',',$__['SHORT_WDAY_NAMES']);
  $format=str_replace(array('shortdate','normaldate','numericdate'),array($__['SHORTDATE'],$__['NORMALDATE'],$__['NUMERICDATE']),$format);
  $d=getdate($date);
  $s="";
  for ($i=0;$i<strlen($format);$i++)
    {
    $c5=substr($format,$i,5);
    $c4=substr($format,$i,4);
    $c3=substr($format,$i,3);
    $c2=substr($format,$i,2);
    $c1=substr($format,$i,1);

    if ($c5=="month") {$s.=$mm_names[$d[mon]-1]; $i+=4; continue;}
    if ($c5=="Month") {$s.=mb_ucfirst($mm_names[$d[mon]-1]); $i+=4; continue;}
    if ($c5=="MONTH") {$s.=mb_strtoupper($mm_names[$d[mon]-1]); $i+=4; continue;}

    if (strtolower($c4)=="year") {$s.=$d['year']; $i+=3; continue;}
    if (strtolower($c4)=="wday") {$s.= $swd_names[$d['wday']]; $i+=3; continue;}

    if ($c3=="mon") {$s.=$m_names[$d[mon]-1]; $i+=2; continue;}
    if ($c3=="mnn") {$s.=sprintf('%02d',$d[mon]); $i+=2; continue;}
    if ($c3=="Mon") {$s.=mb_ucfirst($m_names[$d[mon]-1]); $i+=2; continue;}
    if ($c3=="MON") {$s.=mb_strtoupper($m_names[$d[mon]-1]); $i+=2; continue;}
    if (strtolower($c3)=="day") {$s.=$d[mday]; $i+=2; continue;}

    if (strtolower($c2)=="mn") {$s.=$d['mon']; $i++; continue;}
    if (strtolower($c2)=="hh") {$s.=$d['hours']; $i++; continue;}
    if (strtolower($c2)=="mm") {$s.=$d['minutes']; $i++; continue;}
    if (strtolower($c2)=="ss") {$s.=$d['seconds']; $i++; continue;}
    if (strtolower($c2)=="yy") {$s.=substr($d['year'],-2); $i++; continue;}

    $s.=$c1;
    }
  return $s;
  
  # preg_replace much fun
  }
function format_bytes ($size) {
	$__=&$GLOBALS['_STRINGS']['_'];
  $bnames=explode(",",$__['BYTES']);
  $t=intval(log($size,1024));
  
  if ($t) $v=round($size/pow(1024,$t),2); else $v=$size;
  return $v.$bnames[$t];
}
function get_post_max_size () {
    $val = trim(ini_get('post_max_size'));
    $last = strtolower($val{strlen($val)-1});
    if (!is_integer($last)) {
    	$val=intval(substr($val,0,-1));
	    switch($last) {
	        case 'g': $val *= 1024;
	        case 'm': $val *= 1024;
	        case 'k': $val *= 1024;
	    }
    }
    return format_bytes($val);
}

function includeTracer()
  {
  global $_TRACER;
  if (!is_object($_TRACER))
    {
    include_once ("inc.Tracer.php");
    $_TRACER=new sys_Tracer();
    }
  }

function trace ($v,$type=0)
  {
  # 0 - ordinal
  # 1 - head
  # 2 - warning
  # 3 - error
  # 4 - fatal
  global $_TRACER;
  if ($GLOBALS['cfg']['DeveloperMode'])
    {
    if (!is_object($_TRACER)) includeTracer();
    $_TRACER->Trace($v,$type);
    }
  }

function _coreErrorHandler ($errno, $errmsg, $filename, $linenum, $vars)
  {
  if ($errno & (E_NOTICE | 2048| 1024| 16384 | 8192)) return; # fuck em
  $errortype = array (
    E_ERROR           => "Error",
    E_WARNING         => "Warning",
    E_PARSE           => "Parsing Error",
    E_NOTICE          => "Notice",
    E_CORE_ERROR      => "Core Error",
    E_CORE_WARNING    => "Core Warning",
    E_COMPILE_ERROR   => "Compile Error",
    E_COMPILE_WARNING => "Compile Warning",
    E_USER_ERROR      => "User Error",
    E_USER_WARNING    => "User Warning",
    E_USER_NOTICE     => "User Notice",
#    E_STRICT          => "Runtime Notice"
    );
  $type=1; if ($errno==E_NOTICE) $type=0;
  print_error($errortype[$errno],$errmsg." in ".$linenum,$type,basename($filename));
  }

###############################################
#
# ABOUT OBJECT PATH
# -----------------
#
# object path could be represented in this cases
# fullly qualified path string:
#    [access_class@]
#      class[.subclass]
#      [/context/subcontext/subsubcontext/...]
#      /object_id
#      [~SessionKey][.Type]
#      [!sub_id][?query_args]
#
# Examples:
# class/id
#    this is a required path elements
#
# class/context/id.html
#    very useful object path with object described as .html type
#    in the JSB .html - is clean abstraction
#
# class/context/id.type?args
#    if needed object request with arguments use this syntax
#    where args is a url-style query (in exmaple:  arg1=x&arg2=y&arg3=z)
#
# class.subclass/context/id.type?args
#    subclass is recomended style to declare multiple objects provided by cartridge
#    in example cartridge 'jsb' provides classes: 'jsb.page' and 'jsb.pagecontrol'
#
# class.subclass/id.type
# class.subclass/context/subcontext/subcontext/id.type?args
# class.subclass/context/subcontext/subcontext/id~sessionkey.type?args
# access_class@class.subclass/context/subcontext/subcontext/id.type?args
# class.subclass/context/id.type!sub_id?args    (in example id is $PageID , sub_id is $PageControlID)


function BindPathInfo($object_path)
  {
  if (strpos($object_path,'@')!==false)
    {
    list ($result->Access,$object_path)=explode ('@',$object_path,2);
    }

  if (strpos($object_path,'/')===false) return false;
  list ($result->Class,$p1)=explode('/',$object_path,2);
  list ($p2,$result->ArgsStr)=explode('?',$p1,2);

  if ($result->ArgsStr) {parse_str ($result->ArgsStr,$result->Args);}

  if (strpos($p2,'!')!==false)
    {
    list($p2,$result->SubID)=explode ('!',$p2);
    }
  $lastdot=strrpos ($p2,'.');
  if ($lastdot===false) {$result->Type=false;}
  else
    {
    $result->Type=DBEscape(substr ($p2,$lastdot+1));
    $p2=substr ($p2,0,$lastdot);
    }

  $lastroof=strrpos ($p2,'~');
  if ($lastroof===false) {$result->SessionKey=false;}
  else
    {
    $result->SessionKey=DBEscape(substr ($p2,$lastroof+1));
    $p2=substr ($p2,0,$lastroof);
    }

  $endslash=strrpos ($p2,'/');

  if ($endslash===false)
    {
    $result->Context=false;
    $result->ID=$p2;
    }
  else
    {
    $result->Context=substr ($p2,0,$endslash);
    $result->ID=substr ($p2,$endslash+1);
    }
  $result->Context=strtolower($result->Context);
  $result->Folder=$result->Class.(($result->Context)?"/$result->Context":"")."/";
  return $result;
  }


function OnShutdown()
  {
  $AddCount_Visits=intval($_SESSION->AddCount_Visits);
  $AddCount_Unique=intval($_SESSION->AddCount_Unique);
  $AddCount_Errors=intval($_SESSION->AddCount_Errors);

  global $_SESSION,$allposts,$qsysposts,$_TRACER;
  flush(); # it does not work but we try...
  $_SESSION->Save();
  if ($_TRACER) $_TRACER->EnableOutput();

  $date=getdate(time());
  $s="SELECT * FROM stat_ByDate WHERE Year=$date[year] AND Month=$date[mon] AND Day=$date[mday] AND Hours=$date[hours]";
  $q=DBQuery ($s);
  if ($q)
    {
    DBExec ("UPDATE LOW_PRIORITY stat_ByDate
      SET Hits=Hits+1, Visits=Visits+$AddCount_Visits, Uniq=Uniq+$AddCount_Unique, Errors=Errors+$AddCount_Errors
      WHERE Year=$date[year] AND Month=$date[mon] AND Day=$date[mday] AND Hours=$date[hours]");
    }
  else
    {
    $s="INSERT INTO stat_ByDate
      SET Hits=1, Visits=$AddCount_Visits, Uniq=$AddCount_Unique, Errors=$AddCount_Errors
      ,Year=$date[year], Month=$date[mon], Day=$date[mday], Hours=$date[hours]";
    DBExec ($s);
    }

  $ip=$_SERVER["REMOTE_ADDR"];
#  print "</td></tr></table></td></tr></table></body><!--- Normal shutdown. Session saved ---></html>";
  if (!$_ENV->DONOTLOCKPOST)
    {
    if ($_SERVER['REQUEST_METHOD']=='POST')
      {
      $time=time();
      if ($qsysposts)
        {
        DBExec ("UPDATE LOW_PRIORITY sys_Posts SET LastPost=$time, AllPosts='".implode (",",$allposts)."' WHERE IPaddr='$ip' ");
        }
      else
        {
        DBExec ("INSERT LOW_PRIORITY INTO sys_Posts (IPaddr,LastPost,AllPosts) VALUES ('$ip',$time,'$time')");
        }
      }
    }
  }



#######
# i.e. $Path='custom.PartnerInfo/qna/306'
# returns array of document description
# can read also document data or document owner only
# return document object(IndexID,Caption,Table, .. and document fields)
#

function &get_document_class ($Class)
  {
  global $JSBDocClasses;
  if (!$JSBDocClasses)
    {
    $qdoc=DBQuery ("SELECT * FROM doc_Classes","ClassName");
    if ($qdoc)
      {
      $JSBDocClasses=&$qdoc->Rows;
      } else { return array(Error=>"Table doc_Classes absent");}
    }
  return $JSBDocClasses[$Class];
  }
function load_document_info($Path)
  {
  $inf=BindPathInfo($Path);
  if (!$inf) {return false;}
  $Class=get_document_class($inf->Class);
  $IndexFieldName=$Class->IndexFieldName;
  $DocTable=$Class->DocTable;
  $s="$IndexFieldName AS IndexID";
  $Class->ClassCaption=$Class->Caption;
  $CaptionField=$Class->CaptionField;
  $s.=",".(($CaptionField=='Caption')?"Caption":"$CaptionField AS Caption");
  $OwnerFieldName=$Class->OwnerFieldName;
  if ($OwnerFieldName) {$s.=",".(($OwnerFieldName=='OwnerUserID')?'OwnerUserID':"$OwnerFieldName AS OwnerUserID");}
  $s="SELECT $s FROM $DocTable WHERE $IndexFieldName=$inf->ID";
  $qobj=DBQuery ($s);
  if ($qobj)
    {
    $qobj->Top->_Class=&$Class;
    return $qobj->Top;
    }
  return false;
  }

function langstr_get($s,$LangID=false)
  {
  if (mb_substr($s,0,1)=="¨")
    {
    global $_LANGUAGE;
    if (!$LangID) $LangID=$_LANGUAGE;
    $l=($LangID=='default')?"¨¦":"¨".$LangID."¦";
    $s2=$s;
    if (substr($s2,-2)!='¨') $s2.='¨';
    if (preg_match("/$l(.*?)¨/s",$s2,$m)) {return $m[1];}
    elseif (preg_match("/¨¦(.*?)¨/s",$s2,$m)) return $m[1];
    return $s;
    }
  else
    {
    return $s;
    }
  }

function str_to_float($s)
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  return floatval(str_replace(array($__['THOUSAND_SEPARATOR'],$__['DECIMAL_SEPARATOR']),array("","."),$s));
  }
function float_to_str($f,$Decimals=2)
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  return number_format(floatval($f),$Decimals,"$__[DECIMAL_SEPARATOR]","$__[THOUSAND_SEPARATOR]");
  }
  
function mkdir_recursive($path,$mode=0777)
  {
  $path=str_replace("\\",'/',$path);
  if (substr($path,-1)=='/') $path=substr($path,0,strlen($path)-1);
  $elements=explode ('/',$path);

  $dirstr="";
  if (($elements[0])&&(strpos($path,':')===false)) $dirstr=getcwd().'/';

  if (count($elements)>30) {
    print_error("Strange path","$path",1,false,2);
    return false;
    }

  foreach ($elements as $i=>$subdir)
    {
    if ($subdir)
      {
      $dirstr.=$subdir;
      if (substr($subdir,-1)==':') {$dirstr.='/'; continue;}
      if (is_file($dirstr))
        {
        print_error("Bad mkdir path. File '$subdir' found inside a path","$path",1,false,2);
        return false;
        }
      if (!file_exists($dirstr)) {
        mkdir($dirstr,$mode);}
      }
    elseif ($i) {
      print_error("Bad mkdir path. Empty subdit found inside a path","$path",1,false,2);
      return false;
      }
    $dirstr.='/';
    }
  }

function strip_slashes_deep($value)
{
    $value = is_array($value) ? array_map('strip_slashes_deep', $value) : stripslashes($value);
    return $value;
}
function strip_tags_and_slashes_deep($value)
{
    $value = is_array($value) ? array_map('strip_tags_and_slashes_deep', $value) : strip_tags(stripslashes($value));
    return $value;
}
function strip_tags_deep($value)
{
    $value = is_array($value) ? array_map('strip_tags_deep', $value) : strip_tags($value);
    return $value;
}
function time_sum($t1,$t2) {
	$a=explode (":",$t1); 
	$b=explode (":",$t2);
	if ((!$t1)&&($t2)) return $t2;
	if ((!$t2)&&($t1)) return $t1;
	if ((count($a)!=3)||(count($b)!=3)) return "Time syntax error [$t1,$t2]";
	
	$s=intval($a[2],10)+intval($b[2],10);
	$m=intval($a[1],10)+intval($b[1],10);
	$h=intval($a[0],10)+intval($b[0],10);
	if ($s>59) {$s-=60;$m++;}
	if ($m>59) {$m-=60;$h++;}
	return "$h:$m:$s";
}

###########################################
##
##   M A I N
##
##


$__=&$GLOBALS['STRINGS']['_'];
$_ENV=new sys_Core();
$_CORE=&$_ENV;
if ($_ENV->IsIpBanned($_SERVER["REMOTE_ADDR"])) { exit; }
if (!$Database->Active) {print_error ($__['ERROR_NOSQLSERVER']); exit; }
$_USER=new sys_User();
$_SESSION=new sys_Session();

$_SYSSKIN_NAME=$_COOKIE['ActiveSysSkin'];
if (!$_SYSSKIN_NAME) $_SYSSKIN_NAME=$cfg['DefaultSysSkin'];
register_shutdown_function(OnShutdown);
if ($_SERVER['REQUEST_METHOD']=='POST')
  {
  $_ENV->DropCache();
  $time=time();
  $ip=$_SERVER["REMOTE_ADDR"];
  $qsysposts=DBQuery ("SELECT * FROM sys_Posts WHERE IPaddr='$ip'");
  if ($qsysposts)
    {
    $lastpost=intval($qsysposts->Top->LastPost);
    $allposts=explode (",",$qsysposts->Top->AllPosts);
    if ($lastpost>($time-3))
      {
      # Block the secondary post during 3 seconds
      $blockage=$__['ERROR_TWICEPOST'];
      }
    else
      {
      $allposts[]=$time;
      if (count($allposts)>10)
        {
        $tenth_post=intval(array_shift($allposts));
        # block 10th post if it made during 60 seconds
        if ($tenth_post>($time-60))
          {
          $blockage=$__['ERROR_TOOMUCHPOSTS'];
          array_unshift($allposts,$time+1*60);
          }
        }
      }
    if ($blockage)
      {
      print "<script>history.back();alert ('$blockage');</script>";
      exit;
      }
    }
  }
?>
