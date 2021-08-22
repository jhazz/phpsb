<?
class backend_ISettings
{
var $CopyrightText="(c)2007 PHP Systems builder. Backend";
var $CopyrightURL="http://www.phpsb.com/backend";
var $ComponentVersion="1.0";
var $RoleAccess=array(ChangeSettings=>"Edit,Update");

var $Cartridges;
/*function backend_ISettings()
  {
  $_=&$GLOBALS['_STRINGS']['backend'];
  $this->Title=$_['TITLE_SETTINGS'];
  }

*/
function Update($args)
  {
  global $_SETTINGS;
  extract(param_extract(array(
    CartridgeName=>'string',
    ),$args));
  $_=&$GLOBALS['_STRINGS']['backend'];

  if (!$CartridgeName)
    {
    return array(Error=>"No cartridge to update");
    }

  $Cartridge=&$_ENV->LoadCartridge($CartridgeName);
  $SettingsDesc=&call_user_method("Settings",$Cartridge);
  $Settings=false;
  foreach ($SettingsDesc as $Setting=>$r)
    {
    $v=$_POST[$Setting];

    switch ($r['Type'])
      {
      case 'dim':
        $v=intval($v['w']).'x'.intval($v['h']);
        break;
      case 'int':
        $v=intval($v);
        break;
      }
    $_SETTINGS->Data[$CartridgeName][$Setting]=$v;
    }
  $_SETTINGS->Save();
  return array (ModalResult=>true);
  }

function Edit($args)
  {
  global $cfg,$_SYSSKIN_NAME;
  $_= &$GLOBALS['_STRINGS']['backend'];
  $__=&$GLOBALS['_STRINGS']['_'];
  $DateFormatList=array(
    "shortdate",
    "normaldate",
    "day month",
    "day mon year",
    "day month year",
    "day MONTH year",
    "Mon, day year",
    "Month, day year",
    "MONTH, day year",
    "day Month",
    "Month day",
    "day Mon",
    "Mon day",
    "day MON",
    "MON day",
    "day month year hh:mm",
    "day mon year hh:mm",
    "mn/day/year hh:mm",
    "day-mn-year hh:mm",
    );

  extract(param_extract(array(
    CartridgeName=>'string=jsb',
    ),$args));

  $Cartridges=&$_ENV->LoadCartridgesList(true);
  $s="";
  $links2=array(Category=>"list1",Caption=>$_['TITLE_SETTINGS'],Items=>array());
  foreach ($Cartridges as $aCartridgeName=>$Active)
    {
    $aCartridge=&$_ENV->LoadCartridge($aCartridgeName);
    if (!method_exists($aCartridge,'Settings'))
      {
      continue;
      }
    if ($aCartridge) {$title=$aCartridge->Title;}
    if (!$title) $title=$aCartridgeName;
    $Active=($aCartridgeName == $CartridgeName);
    $links2['Items'][]=array(
      Caption=>$title,
      Icon=>"$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME/tree_page.gif",
      Active=>$Active,
      URL=>ActionURL ("backend.ISettings.Edit.bm",array(CartridgeName=>$aCartridgeName)));
    }

  $_ENV->PutRelatedLinks("links2",$links2);
  $Cartridge=&$_ENV->LoadCartridge($CartridgeName);
  print "<h2>$Cartridge->Title</h2>";
  $_ENV->OpenForm(array(Name=>"form1",Modal=>1,Action=>ActionURL("backend.ISettings.Update.b"),Align=>"center"));
  $_ENV->PutFormField(array(Type=>'hidden',Name=>'CartridgeName',Value=>$CartridgeName));
  if (!method_exists($Cartridge,'Settings'))
    {
    return array(Error=>"This cartridge has no settings",Details=>$CartridgeName);
    }

  $SettingsDesc=&call_user_method("Settings",$Cartridge);
  global $_SETTINGS;
  $_SETTINGS->Load();

  $now=time();
  foreach($DateFormatList as $f) $ValuesArray[$f]=format_date($f,$now);
  
  $_ENV->PutValueSet(array(ValueSetName=>'dateformats',Values=>$ValuesArray));
  
  $qctx=DBQuery ("SELECT SysContext,Caption FROM sys_Contexts ORDER BY OrderNo","SysContext");
  $_ENV->PutValueSet(array(ValueSetName=>"syscontexts", Recordset=>$qctx,CaptionField=>"Caption"));

  foreach ($SettingsDesc as $Setting=>$data)
    {
    $Value=$_SETTINGS->Data[$CartridgeName][$Setting];
    $Caption=$Setting;
    $Notice=$data['Caption'];
    $DefaultValue=$data['DefaultValue'];
    $Required=$data['Required'];
    if ($Required) if (!$Value) $Value=$DefaultValue;

    $ValuesArray=false;

    switch (strtolower($data['Type']))
      {
      case "langstring":
        $_ENV->PutFormField(array(Type=>'langstring', Name=>"$Setting",Value=>$Value,Caption=>$Caption,Required=>$Required,Notice=>$Notice,MaxLength=>50,Size=>50));
        break;
      case 'langtext':
        $_ENV->PutFormField(array(Type=>'langtext', Name=>"$Setting",Value=>$Value,Caption=>$Caption,Required=>$Required,Notice=>$Notice,Size=>60,Rows=>5));
      	break;
      case "syscontext":
        $_ENV->PutFormField(array(Type=>'droplist',Caption=>$Caption,DefaultValue=>$DefaultValue,Size=>40,ValueSetName=>"syscontexts", Notice=>$Notice, Required=>$Required,Name=>$Setting,Value=>$Value));
        break;
      case "dateformat":
        if ($Notice) $Notice.="<br>";
        $Notice.=format_date($Value,$now);
        $_ENV->PutFormField(array(Type=>'droplist',DefaultValue=>$DefaultValue,
          Editable=>1,DoEditValue=>1,Caption=>$Caption,Size=>40,ValueSetName=>"dateformats",
          Name=>$Setting,Notice=>$Notice, Value=>$Value));
        break;
      case 'inputmodal':
        $_ENV->PutFormField(array(Type=>"inputmodal",
          ModalCall=>$data['ModalCall'],
          ModalArgs=>array(SettingName=>$Setting),
          InitCall=>$data['InitCall'],
          Editable=>$data['Editable'],
          Size=>40,Name=>$Setting,
          Caption=>$Caption,
          Notice=>$data['Caption'],
          Hint=>$data['Hint'],Value=>$Value));
      	break;
      case "boolean":
        $_ENV->PutFormField(array(Type=>'checkbox', Name=>$Setting,Value=>$Value,Caption=>$Caption,Notice=>$Notice));
        break;
      case "int": case "float": case "string": case "dim":
        $_ENV->PutFormField(array(Type=>$data['Type'], Name=>$Setting,DefaultValue=>$DefaultValue,Value=>$Value,Caption=>$Caption,Required=>$Required,Notice=>$Notice));
        break;
      case "call":
        $_ENV->PutFormField(array(Type=>'call', Action=>$data['Action'], Name=>$Setting,DefaultValue=>$DefaultValue,Value=>$Value,Caption=>$Caption,Required=>$Required,Notice=>$Notice));
        break;
      }
    }
#  print "</table><br>";
#  $_ENV->PutButton('submit');
  $_ENV->CloseForm();
  print "</td></tr></table>";
  }
}
?>
