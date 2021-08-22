<?
class backend_IFrontdoors
{
var $CopyrightText="(c)2007 PHP Systems builder. Backend";
var $CopyrightURL="http://www.phpsb.com/backend";
var $ComponentVersion="1.0";
var $RoleAccess=array(ChangeConfig=>"AddDoorKeeper,UpdateDoorKeepers,AddFrontdoor,UpdateFrontdoors,Browse");

function AddDoorKeeper($args)
  {
  global $_SETTINGS;
  global $altcfg;
  extract(param_extract(array(
    BrowserLang=>'string',
    OpeningDoor=>'string',
    ),$args));
  $cond="HTTP_ACCEPT_LANGUAGE=$BrowserLang";
  $altcfg[$cond]['OpeningDoor']=$OpeningDoor;
  $_SETTINGS->_updateConfigAndHtaccess();
  return array (ModalResult=>true);
  }

function UpdateDoorKeepers($args)
  {
  global $_SETTINGS;
  global $altcfg;
  $_=&$GLOBALS['_STRINGS']['backend'];
  extract(param_extract(array(
    Keys=>'string',
    BrowserLang=>'array:string',
    OpeningDoor=>'array:string',
    action=>'string',
    check=>'int_checkboxes',
    DefaultOpeningDoor=>'string',
    ),$args));

  if ($DefaultOpeningDoor)
    {
    $altcfg['main']['OpeningDoor']=$DefaultOpeningDoor;
    }
  else
    {
    $doorkeepers=explode(",",$Keys);
    foreach ($doorkeepers as $doorkeeper)
      {
      $lang=$BrowserLang[$doorkeeper];
      if (($lang!=$doorkeeper)||(($action=='delete')&&($check[$doorkeeper]) ))
        {
        unset($altcfg["HTTP_ACCEPT_LANGUAGE=$doorkeeper"]);
        }
      if ($action=='delete') continue;

      $cond="HTTP_ACCEPT_LANGUAGE=$lang";
      $altcfg[$cond]['OpeningDoor']=$OpeningDoor[$doorkeeper];
      }
    }

  $_SETTINGS->_updateConfigAndHtaccess();
  return array (ModalResult=>true);
  }

function AddFrontdoor($args)
  {
  global $altcfg,$_SETTINGS;
  $_=&$GLOBALS['_STRINGS']['backend'];
  extract(param_extract(array(
    Frontdoor=>'string',
    Language=>'string',
    Theme=>'string',
    Homepage=>'string',
    VisitorType=>'string',
    ),$args));
  if (substr($Frontdoor,0,1)=='/')
    {
    $DataPath=$altcfg['main']['RootPath'].'/data';
    $FdoorsFile=$DataPath.'/.frontdoors';
    $fp=fopen($FdoorsFile,"a");
    fputs($fp,"\n".substr($Frontdoor,1));
    fclose ($fp);
    $cfgcond="FRONTDOOR=$Frontdoor";
    }
  else
    $cfgcond="HTTP_HOST=$Frontdoor";
  $altcfg[$cfgcond]['Language']=$Language;
  $altcfg[$cfgcond]['Theme']=$Theme;
  $altcfg[$cfgcond]['Homepage']=$Homepage;
  $altcfg[$cfgcond]['VisitorType']=$VisitorType;
  $_SETTINGS->_updateConfigAndHtaccess();
  return array (ModalResult=>true);
  }

function UpdateFrontdoors($args)
  {
  global $altcfg;
  $_=&$GLOBALS['_STRINGS']['backend'];
  extract(param_extract(array(
    Keys=>'string',
    Language=>'array:string',
    Theme=>'array:string',
    Homepage=>'array:string',
    VisitorType=>'array:string',
    DisableFlash=>'array:int',
    action=>'string',
    check=>'int_checkboxes',
    ),$args));

  $DataPath=$altcfg['main']['RootPath'].'/data';
  $FdoorsFile=$DataPath.'/.frontdoors';

  $fdoorfilelines=false;
  $fdoors=explode(",",$Keys);
  foreach($fdoors as $fdoorid)
    {
    $fdoorpart="";
    if (substr($fdoorid,0,1)=='/')
      {
      $fdoorpart=substr($fdoorid,1);
      $fdoorfilelines[$fdoorpart]=1;
      $cfgcond="FRONTDOOR=$fdoorid";
      }
    else
      $cfgcond="HTTP_HOST=$fdoorid";

    if ($action=='delete')
      {
      if ($check[$fdoorid])
        {
        unset($altcfg[$cfgcond]);
        if ($fdoorpart) unset($fdoorfilelines[$fdoorpart]);
        }
      continue;
      }

    $altcfg[$cfgcond]['Language']=$Language[$fdoorid];
    $altcfg[$cfgcond]['Theme']=$Theme[$fdoorid];
    $altcfg[$cfgcond]['Homepage']=$Homepage[$fdoorid];
    $altcfg[$cfgcond]['VisitorType']=$VisitorType[$fdoorid];
    $altcfg[$cfgcond]['DisableFlash']=$DisableFlash[$fdoorid];
    }
  global $_SETTINGS;
  if (!$fdoorfilelines)
    {if (file_exists($FdoorsFile)) unlink($FdoorsFile);}
  else
    {
    $fp=fopen($FdoorsFile,"w");
    fputs($fp,implode("\n",array_keys($fdoorfilelines)));
    fclose($fp);
    }
  $_SETTINGS->_updateConfigAndHtaccess();
  return array (ModalResult=>true);
  }

function Browse($args)
  {
  global $cfg,$altcfg;
  $_= &$GLOBALS['_STRINGS']['backend'];
  $__=&$GLOBALS['_STRINGS']['_'];

  if (!$altcfg['main']) return array(Error=>"No main section in altcfg");
  $DataPath=$altcfg['main']['RootPath'].'/data';
  $FdoorsFile=$DataPath.'/.frontdoors';
  if (file_exists($FdoorsFile)) $fdoorlines=file($FdoorsFile);

  $doorkeepers=new TRecordset;
  $doorkeepers->Fields=array("BrowserLang","Language","OpeningDoor");
  $doorkeepers->KeyFields=array("BrowserLang");

  $doorsdata=new TRecordset;
  $doorsdata->Fields=array("Frontdoor","Language","DisableFlash","Theme","Homepage","VisitorType");
  $doorsdata->KeyFields=array("Frontdoor");

  $frontdoors=false;
  $possibledoor="";
  if ($fdoorlines)
    {
    foreach ($fdoorlines as $fdoorid)
      {
      $fdoorid=trim($fdoorid); if (!$fdoorid) continue;
      $doorcfg=$altcfg["FRONTDOOR=/$fdoorid"];
      $frontdoors["/$fdoorid"]=1;
  #    $redirs["/$fdoorid"]="/$fdoorid";
      $d=new stdclass;
      $d->Frontdoor="/$fdoorid";
      if (!$possibledoor) $possibledoor="/$fdoorid";
      $d->Language=$doorcfg['Language'];
      $d->DisableFlash=$doorcfg['DisableFlash'];
      $d->Theme=$doorcfg['Theme'];
      $d->Homepage=$doorcfg['Homepage'];
      $d->VisitorType=$doorcfg['VisitorType'];
      $doorsdata->Rows["/$fdoorid"]=$d;
      }
    }
  else
    {
    $d=new stdclass;
    $d->Frontdoor="/en";
    $doorsdata->Rows["/en"]=$d;
    $frontdoors["/en"]=1;
    }
  if (!$possibledoor) $possibledoor="/en";

  # collect subdomains and domain detectors
  $hosts=array("");
  foreach ($altcfg as $cond=>$doorcfg)
    {
    list($param,$val)=explode("=",$cond,2);
    if ($param=='HTTP_ACCEPT_LANGUAGE')
      {
      $d=new stdclass;
      $d->BrowserLang=$val;
      $d->OpeningDoor=$doorcfg['OpeningDoor'];
      $doorkeepers->Rows[$val]=$d;
      }
    if ($param=="HTTP_HOST")
      {
      $d=new stdclass;
      $hosts[]=$val;
#      $redirs[$val]=$val;
      $d->Frontdoor="$val";
      $d->Language=$doorcfg['Language'];
      $d->DisableFlash=$doorcfg['DisableFlash'];
      $d->Theme=$doorcfg['Theme'];
      $d->Homepage=$doorcfg['Homepage'];
      $d->VisitorType=$doorcfg['VisitorType'];
      $doorsdata->Rows["$val"]=$d;
      }
    }

  foreach ($hosts as $host)
    {
    if ($frontdoors) foreach ($frontdoors as $frontdoor=>$tmp)
      {
      $redirs["$host$frontdoor"]="$host$frontdoor";
      }
    }
  $languages=file($DataPath.'/.languages');
  $langhash=false;
  foreach ($languages as $row)
    {
    $row=trim($row);
    if (!$row) continue;
    list ($LangID,$Active,$Caption)=explode (":",$row);
    if (!$Active) continue;
    $langhash[$LangID]=$Caption;
    }


  $d1=$cfg['ThemesPath'];
  $d=opendir($d1);
  $themes=false;
  while ($themedir=readdir($d)) {
    if (($themedir=='.')||($themedir=='..')) continue;
    if (!is_file("$d1/$themedir/theme.php")) continue;
    $themes[$themedir]=$themedir;
    }
  closedir($d);

  $_ENV->PutValueSet(array(ValueSetName=>"redirs",Values=>$redirs));
  $_ENV->PutValueSet(array(ValueSetName=>"langs",Values=>$langhash));
  $_ENV->PutValueSet(array(ValueSetName=>"themes",Values=>$themes));


  print "<h2>$_[DOORKEEPERS]</h2>";

  $_ENV->OpenForm(array(Modal=>1,Action=>ActionURL("backend.IFrontdoors.UpdateDoorKeepers"),Align=>"center", Width=>650));
  $defdoor=$altcfg['main']['OpeningDoor'];  if (!$defdoor) $defdoor=$possibledoor;
  $_ENV->PutFormField(array(Type=>'droplist',
    Name=>'DefaultOpeningDoor',
    Caption=>"Default frontdoor",
    Notice=>'Which frontdoor should be opened to the visitor of any language',
    Size=>30,
    Required=>1,
    Value=>$defdoor,
    ValueSetName=>'redirs'));
  $_ENV->CloseForm();

  if ($doorkeepers->Rows)
    {$_ENV->PrintTable($doorkeepers,array(
    Action=>ActionURL("backend.IFrontdoors.UpdateDoorKeepers.b"),
    ReloadOnOk=>1,
    Fields=>array(
      BrowserLang=>$_['BROWSER_LANGUAGES'],
      OpeningDoor=>$_['OPENING_DOOR'],
      ),
    FieldTypes=>array(
      BrowserLang=>array(
        Type=>'inputstring',
        Required=>1,
        ),
      OpeningDoor=>array(
        Required=>1,
        Type=>'droplist',
        ValueSetName=>'redirs',
        Size=>30,
        ),
      ),
    ShowCheckers=>true,
    ShowDelete=>true,
    ShowOk=>true,
    TableStyle=>1,
    PutKeyFieldsList=>true,
    Width=>'650',
    ThisObject=>&$this));
    print "<a href='javascript:;' id='add1' onClick='document.getElementById(\"adddoorkeeper\").style.display=\"block\"; document.getElementById(\"add1\").style.display=\"none\";' >$_[ADD_NEW_DOORKEEPER]</a>
    <br><div id='adddoorkeeper' style='display:none;'>";
    } else print "<div>";
  $_ENV->OpenForm(array(Title=>$_['ADD_NEW_DOORKEEPER'],Modal=>1,Action=>ActionURL("backend.IFrontdoors.AddDoorKeeper.b"),Align=>"center", Width=>650));
  $_ENV->PutFormField(array(Type=>'string',Required=>1,Name=>'BrowserLang',Caption=>$_['BROWSER_LANGUAGES'],Notice=>"If you wish the website homepage to autodetect the visitor language you can put here language detecting string (i.e. 'de', 'en|en-us')"));
  $_ENV->PutFormField(array(Type=>'droplist',Required=>1,Name=>'OpeningDoor',Caption=>$_['OPENING_DOOR'],Notice=>'Which frontdoor should be opened to the visitor of this language',Size=>30,ValueSetName=>'redirs'));
  $_ENV->CloseForm();
  print "<table width='100%'><tr><td class='notice'><b>Notice:</b><br>Your browser language string: '$_SERVER[HTTP_ACCEPT_LANGUAGE]'</td></tr></table>";
  print "</div><hr>";

  print "<h2>$_[FRONTDOORS]</h2>";
  $_ENV->PrintTable($doorsdata,array(
    Action=>ActionURL("backend.IFrontdoors.UpdateFrontdoors.b"),
    ReloadOnOk=>1,
    Fields=>array(
      Frontdoor=>"Frontdoor",
      Language=>"Language",
      DisableFlash=>"DisableFlash",
      Theme=>"Theme",
      Homepage=>"Homepage",
      VisitorType=>"Visitor type",
      ),
    FieldTypes=>array(
      DisableFlash=>'checkbox',
      VisitorType=>'inputstring:15',
      Language=>array(
        Type=>'droplist',
        ValueSetName=>'langs',
        ),
      Theme=>array(
        Type=>'droplist',
        ValueSetName=>'themes',
        ),
      Homepage=>array(
        Type=>'inputmodal',
        InitCall=>"jsb.IPage.GetPageNameByValue",
        ModalCall=>"jsb.IPage.Select.b",
        CaptionField=>"Homepage",
        Width=>700,
        Height=>500
        ),
      ),
    ShowCheckers=>true,
    ShowDelete=>true,
    ShowOk=>true,
    TableStyle=>1,
    PutKeyFieldsList=>true,
    Width=>'650',
    ThisObject=>&$this));
    print "<a href='javascript:;' id='add2' onClick='document.getElementById(\"addfrontdoor\").style.display=\"block\"; document.getElementById(\"add2\").style.display=\"none\";'>$_[ADD_NEW_FRONTDOOR]</a>
    <br><div id='addfrontdoor' style='display:none;'>";
  $_ENV->OpenForm(array(Title=>"$_[ADD_NEW_FRONTDOOR]",Modal=>1,Action=>ActionURL("backend.IFrontdoors.AddFrontdoor.b"),Align=>"center", Width=>650));
  $_ENV->PutFormField(array(Type=>'string',Value=>'/',Required=>1,Name=>'Frontdoor',Caption=>"Frontdoor",Notice=>"Front door could be subdomain or first path element (i.e. 'fr.mydomain.com' or '/fr'). Path front door should start from '/'"));
  $_ENV->PutFormField(array(Type=>'droplist',Name=>'Language',Caption=>"Language",Notice=>'When visitor comes from this frontdoor interface language will switched to this. You may leave this option empty and default language will be used instead.',Size=>30,ValueSetName=>'langs'));
  $_ENV->PutFormField(array(Type=>'droplist',Name=>'Theme',Caption=>'Theme',Notice=>'You may set theme visible to visitors',Size=>30,ValueSetName=>'themes'));
  $_ENV->PutFormField(array(Type=>'identifier',Name=>'VisitorType',Caption=>"VisitorType",Notice=>"Any surname for all visitors who are enters this door"));
  $_ENV->PutFormField(array(Type=>'inputmodal',Name=>'Homepage',Caption=>'Homepage',Size=>30,Call=>"jsb.IPage.Select.b"));
  $_ENV->CloseForm();
  print "</div>";
  }
}
?>
