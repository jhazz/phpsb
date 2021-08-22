<?
class jsb_IControl
{
var $CopyrightText="(c)2006 JhAZZ Site Builder. jsb management";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(
    MainDesigner=>"Add,Edit,Post,Move,Remove,UpdateFromPageEditor",
    Composer=>"Add,Edit,Post,Move,Remove,UpdateFromPageEditor");

function Add($args)
  {
  extract(param_extract(array(
    Slot=>'string',
    EditSysContext=>'string',
    EditJSBPageID=>'string',
    BaseControlID=>'int',
    subaction=>'string',   # insbefore/addtoslot
    ),$args));

  global $cfg,$_SYSSKIN_NAME;
  $_ =&$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];
  $SysSkinURL="$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME";
  $_ENV->SetWindowOptions(array(Title=>$_['CAPTION_ADD_CONTROL'],Width=>500,Height=>500));
?>
<script>function set_NewControlClass(className) {form1.ControlClass.value=className; form1.submit(); }</script>
<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>
  <tr valign='top'>
  <td width='150' rowspan='2' background='<? print $SysSkinURL; ?>/jsb_bg.gif'>
  <img src='<? print $SysSkinURL; ?>/jsb_add_new_control.gif'>
    <form name='form1' method='post' action='<? print ActionURL("jsb.IControl.Edit.b"); ?>'>
    <input type='hidden' name='ControlClass' value=''>
<?
  print "<input type='hidden' name='subaction' value='$subaction'>
  <input type='hidden' name='EditSysContext' value='$EditSysContext'>
  <input type='hidden' name='EditJSBPageID' value='$EditJSBPageID'>
  <input type='hidden' name='Slot' value='$Slot'>
  ";
  if ($BaseControlID)
    {
    print "<input type='hidden' name='BaseControlID' value='$BaseControlID'>";
    }
  $c1=""; $c2=""; $hints="";
  $FirstCartridge=false;
  $_ENV->LoadCartridgesList(true);
  foreach ($_ENV->Cartridges as $CartridgeName=>$Active)
    {
    if (!$Active) continue;
    $Cartridge=&$_ENV->LoadCartridge($CartridgeName);
    if (!$Cartridge) continue;
    $title=$Cartridge->Title;
    if (!$title) $title=$CartridgeName;
    if (method_exists($Cartridge,"Controls"))
      {
      $Controls=&$Cartridge->Controls();
      }
    else continue;
    if (!$FirstCartridge) {$FirstCartridge=$CartridgeName;}

    $url="show_cartridge(\"$CartridgeName\")";
    $c1.="<tr><td><img border='0' src='$SysSkinURL/sq1.gif' width='12' height='9'></td><td height='40' align='left' id='TAB_$CartridgeName'><a class='p' href='javascript:$url' onMouseOver='$url'>$title</a></td></tr>";
    $c3=""; $colno=0;
    foreach ($Controls as $ControlClassName=>$ControlInfo)
      {
      if (!$colno) {$c3.="<tr valign='top'>";}
      $img="";
      $fico="$cfg[PHPSBScriptsPath]/$CartridgeName/public/$ControlClassName.gif";
      if (file_exists($fico))
        {
        $size=@getimagesize($fico);
        $Icon="<img border='0' src='$cfg[PublicURL]/$CartridgeName/$ControlClassName.gif' $size[3]>";
        }
      else
        {
        $Icon="<img border='0' src='$SysSkinURL/ico_default.gif'>";
        }

      $s=$ControlInfo['Description'];
      $ControlClassFullName="$CartridgeName.$ControlClassName";
      if ($s)
        {
        $hints.="<div id='HINT_$ControlClassFullName' style='display:none'><h5>$ControlInfo[Caption]</h5>$s</div>";
        }

      $s=$ControlInfo['Caption'];
      if ($s) {$s="<br><span class='jsb_tiny'>$s</span>";}
      $c3.="<td align='center'><a class='info' href='javascript:set_NewControlClass(\"$ControlClassFullName\")' onMouseOver='show_hint(\"$ControlClassFullName\");'>$Icon<br>$ControlClassName</a>$s</td>";
      $colno++;
      if ($colno>2) {$colno=0;$c3.="</tr><tr><td>&nbsp;</td></tr>";}
      }
    if (!$colno) {$c3.="</tr>";}

    $c2.="<div style='height:300;background-color:#e0e0e0;display:none;' id='DIV_$CartridgeName'><table width='100%' cellspacing='0' border='0' cellpadding='10'><tr><td><h2>$title</h2>
      <table width='100%'>$c3</table>
      </td></tr></table></div>";
    }
  $hints.="<div id='HINT_Start' style='display:block'>$_[CHOOSE_CARTRIDGE_AT_LEFT]</div>";
  print "<table border='0' cellpadding='5' cellspacing='0'>$c1</table></td>
  <td height='300' valign='top'>$c2</td></tr><tr valign='top'>
  <td><table border='0' cellpadding='10' cellspacing='0'><tr><td valign='top'>$hints</td></tr></table>";
  ?>
  </form></td></tr></table></body>
  <script>
  var PageLoaded=false;
  var ShownCart=false,ShownCartButton,ShownHint=false;
  function show_cartridge(id)
    {
    if (!PageLoaded) {return;}
    if (ShownCart) {ShownCart.style.display='none'; }
    if (ShownCartButton) {ShownCartButton.style.backgroundColor='';}
    ShownCart=P$.find("DIV_"+id);
    ShownCart.style.display='block';
    ShownCartButton=P$.find("TAB_"+id);
    ShownCartButton.style.backgroundColor='#ffffff';
    if (ShownHint) {ShownHint.style.display='none'; ShownHint=false;}
    }

  function show_hint(str)
    {
    if (!PageLoaded) {return;}
    if (ShownHint) {ShownHint.style.display='none'; }
    ShownHint=P$.find("HINT_"+str);
    if (ShownHint) {ShownHint.style.display='block';}
    }

  ShownCart=P$.find("HINT_Start");
  PageLoaded=true;
  <? print "show_cartridge('$FirstCartridge'); "; ?>
  </script>
  <?
  }

function Edit ($args)
  {
  extract(param_extract(array(
    Slot=>'string',
    EditSysContext=>'string',
    EditJSBPageID=>'int',
    BaseControlID=>'int',
    EditControlID=>'int',
    ControlClass=>'string',
    subaction=>'string',
    ),$args));

  global $cfg,$IThemeReader,$_THEME;
  $_ENV->UnlockTwicePost();
  $_= &$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];
  $LayoutControls=false;
  $JSBLayoutID=false;
  $PageControls=false;
  $_ENV->LoadTheme('f');
  $_THEME_FRONT=$_THEME;
  $Component=false;


  # LOAD CONTROLS FOR FUTURE BINDING

#  if (!$Value) {$Value="-1:ThePage";}

  $LOptions=false; #layout options array. used to detect ObjectClass loader for layout

  $qpc=DBQuery ("SELECT JSBPageControlID,Slot,ControlClass,PropertiesStr
    FROM jsb_PageControls WHERE SysContext='$EditSysContext' AND JSBPageID=$EditJSBPageID","JSBPageControlID");
  if ($qpc)
    {
    $s=$qp->Top->Options;
    if ($s) parse_str($s,$LOptions);

    foreach (array_keys($qpc->Rows) as $JSBPageControlID)
      {
      $c=$_ENV->CreateControl(array(ControlData=>&$qpc->Rows[$JSBPageControlID]),false,true);
      $c->SysContext=$EditSysContext;
      $c->JSBPageID=$EditJSBPageID;
      $c->DesignMode=1;
      if (method_exists($c->Component,"Init")) {
        $c->Component->Init($c);
        }
      $PageControls[intval($JSBPageControlID)]=$c;
      }
    }


  if ($EditSysContext!='layouts')
    {
    $qp=DBQuery ("SELECT JSBLayoutID,Options FROM jsb_Pages WHERE SysContext='$EditSysContext' AND JSBPageID=$EditJSBPageID");

    if (!$qp)
      {
      $qp=DBQuery ("SELECT JSBLayoutID,Options FROM jsb_Pages WHERE SysContext='$EditSysContext' AND JSBPageID=0");
      }

    $JSBLayoutID=$qp->Top->JSBLayoutID;
    $qlp=DBQuery ("SELECT Options FROM jsb_Pages WHERE SysContext='layouts' AND JSBPageID=$JSBLayoutID");
    $s=$qlp->Top->Options;
    if ($s) parse_str($s,$LOptions);
#    print
    if ($qp)
      {
      $qlc=DBQuery ("SELECT JSBPageControlID,Slot,ControlClass,PropertiesStr
      FROM jsb_PageControls WHERE SysContext='layouts' AND JSBPageID=$JSBLayoutID","JSBPageControlID");
      if ($qlc) foreach ($qlc->Rows as $JSBPageControlID=>$row)
        {
        $c=$_ENV->CreateControl(array(ControlData=>$row),false,true);
        $c->SysContext=$EditSysContext;
        $c->JSBPageID=$EditJSBPageID;
        $c->ControlEditor=1;

        $c->DesignMode=1;
        if (method_exists($c->Component,"Init"))  $c->Component->Init($c);
        $PageControls[intval($JSBPageControlID)]=$c;
        }
      }
    }
  else
    {
    # this is layout page
    $qp=DBQuery ("SELECT JSBLayoutID,Options FROM jsb_Pages WHERE SysContext='layouts' AND JSBPageID=$EditJSBPageID");
    if (!$qp)
      {
      return array(Error=>"Layout page is absent",Details=>"JSBPageID=$EditJSBPageID");
      }
    $s=$qp->Top->Options;
    if ($s) parse_str($s,$LOptions);
    }


  if ((!$LOptions)||(!$LOptions['obj']))
    {
    return array(Error=>"Undefined layout object loader");
    }
/*
  $c=$_ENV->CreateControl($LOptions['obj'],false,true);
  $c->SysContext=$EditSysContext;
  $c->JSBPageID=$EditJSBPageID;
  $c->ControlClass=$LOptions['obj'];
  $c->Component->Init($c);
  $PageControls[-1]=$c;
*/

  if ($subaction=="edit")
    {
    $EditControlID=intval($EditControlID);
    $qc=DBQuery ("SELECT * FROM jsb_PageControls WHERE JSBPageControlID=$EditControlID AND JSBPageID=$EditJSBPageID AND SysContext='$EditSysContext'");
    if (!$qc)
      {
      return array(Error=>$_['ERROR_BAD_CONTROLID'],Details=>$EditControlID);
      }
    $Control=$_ENV->CreateControl(array(ControlData=>$qc->Top),false,true);
    $Component=$Control->Component;
    $ControlClass=$Control->ControlClass;
    } # if not 'edit' the $Control leave false !!

  if (!$ControlClass)
    {
    print_error ("Missed control class!");
    exit;
    }

  if (!$Component)
    {
    # INSERT NEW CONTROL TO THE PAGE
    $Control=$_ENV->CreateControl($ControlClass,true,true);
    $Component=$Control->Component;
    if (!$Component)
      {
      return array(Error=>"Could not load component",Details=>$ControlClass);
      }
    }


  if (method_exists($Component,"Edit")) {$Component->Edit($Control);}
  if ($EditSysContext)
    {
    $Control->JSBPageID=$EditJSBPageID;
    $Control->SysContext=$EditSysContext;
    }
  print "<table width='100%' cellpadding='10' border=0><tr><td>";
  if ($Control->JSBPageControlID)
    {
    $Title="$ControlClass ($Control->JSBPageControlID)";
    }
  else
   {
   $Title="$ControlClass (new)";
   }
  $_ENV->SetWindowOptions(array(Title=>$Title));
  list ($CartridgeName,$ControlName)=explode (".",$ControlClass);
  $Cartridge=&$_ENV->LoadCartridge($CartridgeName);
  if (method_exists($Cartridge,"Controls"))
    {$ComponentControls=&$Cartridge->Controls();
     print "<h1>".$ComponentControls[$ControlName]['Caption']."</h1><h2>"
     .nl2br($ComponentControls[$ControlName]['Description'])."</h2>";
    }

  $_ENV->LoadTheme('b');
  $_ENV->OpenForm(array(
    Name=>"ModalForm",
    ModalOkOnOk=>1,
    Width=>'100%',
    ShowOk=>1,
    ShowCancel=>1,
    Action=>ActionURL("jsb.IControl.Post.b"),
    Align=>"center"));
  print "
  <input type='hidden' name='EditSysContext' value='$EditSysContext'>
  <input type='hidden' name='EditJSBPageID' value='$EditJSBPageID'>
  <input type='hidden' name='EditControlID' value='$EditControlID'>
  <input type='hidden' name='ControlClass' value='$ControlClass'>
  <input type='hidden' name='subaction' value='$subaction'>
  <input type='hidden' name='Slot' value='$Slot'>";
  if ($BaseControlID)
    {
    print "<input type='hidden' name='BaseControlID' value='$BaseControlID'>";
    }
################################## BINDING #####################################

  # Do Binding
  if ($Component->Propdefs) foreach ($Component->Propdefs as $PropName=>$PropDetails)
    {
    if (strtolower($PropDetails['Type'])=="binding")
      {
      $BindAddress=$Control->Properties[$PropName];
      if ($BindAddress=='self')
        {
        $Control->Bindings[$PropName]="jsb.ctrls/$Control->JSBPageControlID";
        continue;
        }

      if ($BindAddress=='layout')
        {
#        $Control->Bindings[$PropName]="jsb.ctrls/$Control->JSBPageControlID";
        continue;
        }

      if ($BindAddress)
        {
        list($ControlID,$DataName)=explode (":",$BindAddress);
        $c=$PageControls[intval($ControlID)];
        if ($c)
          {
          $Control->Bindings[$PropName]=$c->Data[$DataName];
          }
        else
          {
          $Control->Properties[$PropName]="[broken]|$BindAddress";
#          print_developer_warning("Incorrect binding. Possibly target binding control has been removed",$BindAddress);
          }
        }
      }
    } # BINDING COMPLETE


###################################
  $qcontexts=DBQuery ("SELECT * FROM sys_Contexts ORDER BY OrderNo","SysContext");
  $DrawingFontsSet=false;
  $DateFormatList=array(
    "shortdate",
    "normaldate",
    "numericdate",
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
    "mn/day/year hh:mm",
    "day-mn-year hh:mm",
    );
  $now=time();
  foreach($DateFormatList as $f) $ValuesArray[$f]=format_date($f,$now);
    $_ENV->PutValueSet(array(ValueSetName=>'DateFormats',Values=>$ValuesArray));

  $_ENV->PutValueSet(array(ValueSetName=>'Alignments',Values=>array(
    'left'=>$__['ALIGNLEFT'],
    'right'=>$__['ALIGNRIGHT'],
    'center'=>$__['ALIGNCENTER'],
    'justify'=>$__['ALIGNJUSTIFY'])));

  if (is_array($Component->Propdefs))
    {
    foreach ($Component->Propdefs as $PropName=>$PropDetails)
      {
      $Type=strtolower($PropDetails['Type']);
      $Required=$PropDetails['Required'];
      $Caption=$PropDetails['Caption'];
      $DefaultValue=$PropDetails['DefaultValue'];
      $NullCaption=$PropDetails['NullCaption'];

      $Value=false;
      if (is_array($Control->Properties))
        {
        $Value=$Control->Properties[$PropName];
        }
      if (!$EditControlID) $Value=$DefaultValue;

      $ValueStr=""; $s="";

      $Hint="$PropName:$Type";

      switch ($Type)
        {
        case "alignment": case 'align':
          $_ENV->PutFormField(array(Type=>'droplist',
            Caption=>$PropName,Notice=>$Caption,
            Size=>20,
            Name=>$PropName,ValueSetName=>'Alignments',
            Value=>$Value,Required=>$Required));
          break;

        case "dateformat":
          $Notice=format_date($Value,time());
          $_ENV->PutFormField(array(Type=>'droplist',Caption=>$PropName,Size=>'48',
             Notice=>$Notice,Name=>$PropName,Editable=>1,DoEditValue=>1,ValueSetName=>'DateFormats',Value=>$Value,Required=>$Required));
          break;

        case "themeelement":
          $Elements=false;
          if (!isset($_THEME_FRONT[$PropDetails['Section']]))
            {
            $_ENV->PutFormField(array(Type=>'label',
              Caption=>$PropName,
              Value=>"Section '$PropDetails[Section]' not found in the theme"));
            }
          else
            {
            foreach ($_THEME_FRONT[$PropDetails['Section']] as $aID=>$desc)
              {
              $sc=$desc['Caption'];
              if (!$sc) $sc=$aID; else $sc="$aID: $sc";
              $Elements[$aID]=$sc;
              }
            $_ENV->PutValueSet(array(ValueSetName=>$PropName,Values=>$Elements));
            if (!$Required) $NullCaption="----";
            $_ENV->PutFormField(array(Type=>'droplist',
               Caption=>$PropName,
               Size=>'48',
               Notice=>$Caption,Name=>$PropName,
               DefaultValue=>$DefaultValue,
               NullCaption=>$NullCaption,
               ValueSetName=>$PropName,Value=>$Value,Required=>$Required));
            }
          break;

        case "drawingfont":
          if (!$DrawingFontsSet)
            {
            $DrawingFonts=&$_THEME_FRONT['FontStyles'];
            if (!$DrawingFonts) $DrawingFonts=&$_THEME_FRONT['DrawingFonts'];
            foreach ($DrawingFonts as $Style=>$desc)
              {
              $DrawingFontsSet[$Style]=$desc['Caption'];
              if (!$DefaultValue) $DefaultValue=$Style;
              }
            $_ENV->PutValueSet(array(ValueSetName=>'DrawingFontsSet',Values=>$DrawingFontsSet));
            }
          if (!$Value) $Value=$DefaultValue;
          $_ENV->PutFormField(array(Type=>'droplist',Caption=>$PropName,Size=>'48',
             Notice=>$Caption,Name=>$PropName,
             ValueSetName=>'DrawingFontsSet',
             DefaultValue=>$DefaultValue,
             Value=>$Value,
             Required=>$Required));
          break;

        case "syscontext":
          if ($qcontexts)
            {
            $s="";
            $ValueSet=false;
            foreach ($qcontexts->Rows as $SysContext=>$ctx)
              {
              if (($PropDetails['ObjectClass'])&&($PropDetails['ObjectClass']!=$ctx->ObjectClass)) continue;
              $ValueSet[$SysContext]=langstr_get($ctx->Caption);
              }
            $_ENV->PutValueSet(array(ValueSetName=>"vs_$PropName",Values=>$ValueSet));
            $_ENV->PutFormField(array(Type=>'droplist',Caption=>$PropName,Size=>'48',
               Notice=>$Caption,Name=>$PropName,Editable=>0,ValueSetName=>"vs_$PropName",
               Value=>$Value,DefaultValue=>$DefaultValue,Required=>$Required));
            }
          else
            {
            $_ENV->PutFormField(array(Type=>"string",Value=>$Value,Caption=>$PropName,Notice=>$Caption, Name=>$PropName,Required=>$Required));
            }
          break;

        case "binding":
          $DataType=strtolower($PropDetails['DataType']);
          $s="";
          $ValueSet=false;
          if ($PageControls)
            {
            foreach ($PageControls as $JSBPageControlID=>$BindableControl)
              {
              $Datadefs=$BindableControl->Component->Datadefs;
              if (!$Datadefs) {continue;}
              foreach ($Datadefs as $DataName=>$DataDef)
                {
                if (($DataType==strtolower($DataDef['DataType'])) ||
                    (($DataType=='object')&&(strpos($DataDef['DataType'],'.')!==false))
                   )
                  {
                  $cap=$DataDef['Caption']; if (!$cap) {$cap=$DataName;}
                  $cap.=' ['.$BindableControl->ControlClass."($JSBPageControlID)]";
                  $BindName=$JSBPageControlID.':'.$DataName;
                  $ValueSet[$BindName]=$cap;
                  }
                }
              }
            }
          if ($ValueSet)
            {
            $_ENV->PutValueSet(array(ValueSetName=>"vs_$PropName",Values=>$ValueSet));
            $_ENV->PutFormField(array(Type=>'droplist',Caption=>$PropName,Size=>'48',
               Notice=>$Caption,Name=>$PropName,Editable=>0,ValueSetName=>"vs_$PropName",
               Value=>$Value,NullCaption=>"- - - - - - -",Required=>$Required));

            }
          else
            {
            $_ENV->PutFormField(array(Type=>'label',Caption=>$PropName,
              Notice=>$Caption,Value=>"<i>No controls to bind to</i><br/>$DataType"));
            }

          if (substr($Value,0,8)=='[broken]')
            {
            list($t,$s)=explode('|',$Value);
            $_ENV->PutFormField(array(Type=>'label',Caption=>$PropName,
              Notice=>$Caption,Value=>"<font color='red'>$_[ERROR_BIND_IS_BROKEN] $s</font>"));
            }

          break;
        case "css_class":
          $baseclass=$PropDetails['BaseCSSClass'];
          $ValueSet=false;

          if ($PropDetails['AddDrawingFonts'])
            {
            $DrawingFonts=&$_THEME_FRONT['DrawingFonts'];
            if ($DrawingFonts) foreach ($DrawingFonts as $Style=>$desc)
              {
              $Caption=$desc['Caption']; if (!$Caption) $Caption=$Style;
              $ValueSet["$Style:draw"]="{".$Caption."}";
              }
            }


          if ($_THEME_FRONT['Styles'])
            {
            $s="";
            $inlist=false;
            foreach($_THEME_FRONT['Styles'] as $CSSStyle=>$CSSName)
              {
              list ($base,$class)=explode (".",$CSSStyle);
              if ($base!='')
                {
                if ($baseclass)
                  {
                  #
                  if ($baseclass=='p')
                    {
                    if (!$base) {continue;}
                    if (($base=='input')||($base=='td')||($base=='th')||($base=='a')) {continue;}
                    }
                  else
                    {
                    if ($baseclass!=$base) {continue;}
                    }
                  }
                else
                  {
                  if (($base=='input')||($base=='img')||($base=='a')) {continue;}
                  }
                }
              $ValueSet[$CSSStyle]=$CSSName;
              }
            }
            $_ENV->PutValueSet(array(ValueSetName=>"vs_$PropName",Values=>$ValueSet));
            $_ENV->PutFormField(array(Type=>'droplist',Caption=>$PropName,Size=>'48',
               Notice=>$Caption,Name=>$PropName,Editable=>1,DoEditValue=>1,ValueSetName=>"vs_$PropName",
               Value=>$Value,Required=>$Required));
          break;

        case "list":
          if (!$NullCaption) $NullCaption="--------";
          $Values="";
          if (isset($PropDetails['Values']))
            {
            $Values=&$PropDetails['Values'];
            }
          else
            {
            $s=$PropDetails['GetListValuesFrom'];
            if ($s)
              {
              list ($cart,$mod,$met)=explode (".",$s,3);
              $intf=&$_ENV->LoadInterface("$cart.$mod");
              if (!is_object($intf))
                {
                print_developer_warning("Interface is not loaded","$cart.$mod");
                break;
                }

              $DependProperties=false;
              $s2=$PropDetails['DependOn'];
              if ($s2)
                {
                $s2=explode (",",$s2);
                foreach ($s2 as $name)
                  {
                  $DependProperties[$name]=$Control->Properties[$name];
                  }
                }
              if (!method_exists($intf,$met))
                {
                print_developer_warning("Method not found in the interface class","$cart.$mod.$met");
                break;
                }
              $result=$intf->$met(array(
                ControlID=>$BaseControlID,
                PageControls=>&$PageControls,
                DependProperties=>$DependProperties));

              if (is_array($result['ListValues']))
                {
                $Values=&$result['ListValues'];
                }
              }
            }

          if ($Values || $PropDetails['Recordset'])
            {
            $_ENV->PutValueSet(array(ValueSetName=>"vs_$PropName",Values=>$Values,Recordset=>$PropDetails['Recordset'],CaptionField=>$PropDetails['CaptionField']));
            $_ENV->PutFormField(array(Type=>'droplist',
              Caption=>$PropName,
               Size=>'48',
               DefaultValue=>$DefaultValue,
               Notice=>$Caption,
               Name=>$PropName,
               ValueSetName=>"vs_$PropName",
               Value=>$Value,
               NullCaption=>$NullCaption,
               Required=>$Required));
            }
          else
            {
            $_ENV->PutFormField(array(Type=>'label',Caption=>$PropName,
              Notice=>$Caption,Value=>"<i>No values</i>"));
            }
          break;
        case "boolean":
          $ch="";
          $_ENV->PutFormField(array(Type=>"checkbox",Name=>$PropName,Caption=>$PropName,Notice=>$Caption,Hint=>$Hint,Value=>$Value));
          break;
        case "int": case "integer":
          $Value=intval($Value);
          $_ENV->PutFormField(array(Type=>"int",Size=>11,MaxLength=>11,Name=>$PropName,Caption=>$PropName,Notice=>$Caption,Hint=>$Hint,Value=>$Value));
          break;
        case "dim":
          list($w,$h)=explode ("x",$Value);
          $_ENV->PutFormField(array(Type=>"dim",
            Name=>$PropName,Caption=>$PropName,Notice=>$Caption,Hint=>$Hint,Value=>$Value,DefaultValue=>$DefaultValue));
          break;
        case "langcaption": case "caption":
          if ((!$Caption)&&($DefaultValue)) $Caption=$PropDetails['DefaultValue'];
          $Caption=langstr_get($Caption);
          $Hint.='"'.htmlspecialchars($Caption).'"';
          if (strlen($Caption)>70) {$Caption=substr($Caption,0,70)."...";}
          $Caption=$_['CAPTION_CAPTION']." '".$Caption."'";
          $t="string"; if ($Type=="langcaption") $t="langstring";
          $_ENV->PutFormField(array(Type=>$t,Size=>50,Name=>$PropName,Caption=>$PropName,Notice=>$Caption,Hint=>$Hint,Value=>$Value));
          break;
        case 'langstring':
          $_ENV->PutFormField(array(Type=>"langstring",Size=>50,Value=>$Value,Caption=>$PropName,Notice=>$Caption, Name=>$PropName,Required=>$Required));;
          break;
        case "socket":
          print "<input type='hidden' name='$PropName' value='$Value'>(socket '$Value')";
          break;
        case "inputmodal":
        	$args=$PropDetails['ModalArgs'];
        	$args['property']=$PropName;
          $_ENV->PutFormField(array(Type=>"inputmodal",
            ModalCall=>$PropDetails['ModalCall'],
            ModalArgs=>$args,
            InitCall=>$PropDetails['InitCall'],
            Editable=>$PropDetails['Editable'],
            Size=>40,Name=>$PropName,Caption=>$PropName,
            Notice=>$Caption,Hint=>$Hint,Value=>$Value));
          break;
        default:
          $_ENV->PutFormField(array(Type=>"string",Size=>50,Value=>$Value,Caption=>$PropName,Notice=>$Caption, Name=>$PropName,Required=>$Required));;
          break;
        }

      if ($PropDetails['GetValueFrom'])
        {
        print "Property attribute 'GetValueFrom' is deprecated";
        }
      }
    }
  else
    {
    print "This control has no properties";
    }
   $_ENV->CloseForm();
   ?>
<script>
var ResultTargetField;
function GetValueFrom(targetfield,CallURL)
  {
  ResultTargetField=targetfield;
  W.openModal({url:CallURL,w:400,h:500,callback:"callback_SetFieldValue"});
  }
function callback_SetFieldValue(result)
  {
  ResultTargetField.value=result;
  }
function type_size_onChange(propname)
  {
  var w=parseInt(ModalForm.elements["w"+propname].value) || 0;
  var h=parseInt(ModalForm.elements["h"+propname].value) || 0;
  var s=(w && h)?(w+"x"+h):"";
  ModalForm.elements[propname].value=s;
  }
var SelectPageURL="<? print ActionURL("jsb.IPage.SelectPageOrURL.b"); ?>";
function SelectURL(targetfield)
  {
  ResultTargetField=targetfield;
  W.openModal ({url:SelectPageURL+"?Path="+targetfield.value,callback:"callback_SetFieldURL"});
  }
function callback_SetFieldURL(result,param)
  {
  ResultTargetField.value=result; //"jsb/"+result['SelectedContext']+"/"+result['SelectedPageID'];;
  }
function doSubmit()
  {
  return true;
  }
</script>
<?
  $s=$Component->CopyrightText;
  if ($s)
    {
    if ($Component->CopyrightURL)
      {
      $s="<a href='$Component->CopyrightURL' target='_blank'>$s</a>";
      }
    }
  else
    {
    $s="No copyrights have been reserved";
    }
  if ($Component->ComponentVersion)
    {
    $s.="<br>Component version: ".$Component->ComponentVersion;
    }
  print "<p class='copyrights'>$s</p>";
  print " </td></tr></table>";
  return;
  }


function Post ($args)
  {
  global $cfg;
  global $_ENV;
  extract(param_extract(array(
    Slot=>'string',
    EditSysContext=>'string',
    EditJSBPageID=>'string',
    BaseControlID=>'int',
    EditControlID=>'int',
    ControlClass=>'string',
    subaction=>'string=edit',
    ),$args));

  if (!$ControlClass)
    {
    return array(Error=>"Control class indentificator not presents in arguments");
    }
  $Control=$_ENV->CreateControl($ControlClass,false,true); #EditMode=true
  if (!$Control) {exit;}
  $Component=$Control->Component;
  foreach ($Component->Propdefs as $Propname=>$PropDesc)
    {
    $v=$_POST[$Propname];
    $Control->Properties[$Propname]=$v;
    }
  $JSB_PropertiesStr=implode_properties ($Control->Properties);

  $OrderNo=0;
  if ($subaction=='addtoslot')
    {
    $qo=DBQuery("SELECT MAX(OrderNo) AS MaxOrderNo
    FROM jsb_PageControls
    WHERE JSBPageID=$EditJSBPageID AND
    SysContext='$EditSysContext'
    AND Slot='$Slot'");

    $MaxOrderNo=1;
    if ($qo) { $MaxOrderNo=$qo->Top->MaxOrderNo; }
    $OrderNo=$MaxOrderNo+100000;
    }
  if ($subaction=='insbefore')
    {
    $BaseControlID=intval($BaseControlID);
    $s="SELECT OrderNo,Slot
    FROM jsb_PageControls
    WHERE JSBPageID=$EditJSBPageID AND
    SysContext='$EditSysContext' AND
    JSBPageControlID=$BaseControlID";
    $qc=DBQuery ($s);
    if ($qc)
      {
      $Slot=$qc->Top->Slot;
      $MaxOrderNo=$qc->Top->OrderNo;
      $qo=DBQuery ("SELECT JSBPageControlID
      FROM jsb_PageControls
      WHERE JSBPageID=$EditJSBPageID AND SysContext='$EditSysContext'
      AND Slot='$Slot' AND OrderNo<$MaxOrderNo");
      if ($qo)
        {
        $qo=DBQuery ("SELECT MAX(OrderNo) AS MaxOrderNo
        FROM jsb_PageControls
        WHERE JSBPageID=$EditJSBPageID AND SysContext='$EditSysContext'
        AND Slot='$Slot' AND OrderNo<$MaxOrderNo");
        $OrderNo=intval(($qo->Top->MaxOrderNo + $MaxOrderNo) /2);
        }
      else
        {
        $OrderNo=$MaxOrderNo-100000;
        }
      }
    }

  if ($subaction!='edit')
    {
    $Control->JSBPageControlID=DBGetID("jsb.PageControl");
    }


  ########## CALL 'ACCEPTEDIT' method and then collect
  ########## changed Control->Properties back to JSB_PropertiesStr

  if (($Component)&&(method_exists ($Component,"Regenerate")))
    {
    eval ("\$r=\$Component->Regenerate(\$Control);");
    # update JSB_PropertiesStr
    $JSB_PropertiesStr=implode_properties ($Control->Properties);
    if ($r['Error'])
      {
      return array(Error=>$r['Error']);
      }
    }

  if ($subaction!='edit')
    {
    $s="INSERT INTO jsb_PageControls (JSBPageControlID,ControlClass,OrderNo,SysContext,JSBPageID,Slot,PropertiesStr)
    VALUES ($Control->JSBPageControlID,'$ControlClass',$OrderNo,'$EditSysContext',$EditJSBPageID,'$Slot','$JSB_PropertiesStr')";
    }

  if (($EditControlID)&&($subaction=='edit'))
    {
    $s="UPDATE jsb_PageControls
          SET PropertiesStr='$JSB_PropertiesStr'
          WHERE JSBPageID=$EditJSBPageID AND SysContext='$EditSysContext'
          AND JSBPageControlID='$EditControlID'";
    }

  if (!DBExec ($s))
    {
    return;
    }
  return array(ModalResult=>true);
  }

function Move ($args)
  {
  extract(param_extract(array(
    TargetSlot=>'string',
    TargetControlID=>'int',
    EditSysContext=>'string',
    EditJSBPageID=>'int',
    ControlID=>'int',
    ),$args));

  print "<style>body{font-family:verdana; font-size:10px; background-color:#b0b0b0;}</style>";

  if ($TargetSlot)
    {
    print "Move control to the bottom of slot '$TargetSlot'<br>";
    $MaxNo=0;
    $q=DBQuery ("SELECT MAX(OrderNo) AS MaxNo FROM jsb_PageControls WHERE Slot='$TargetSlot' AND SysContext='$EditSysContext' AND JSBPageID=$EditJSBPageID");
    if ($q) {$MaxNo=$q->Top->MaxNo;}
    print "<b>Found MaxOrderNo:</b> $MaxNo<br>";
    $MaxNo+=100000;
    print "<b>Set OrderNo:</b> $MaxNo<br>";

    $s="UPDATE jsb_PageControls SET Slot='$TargetSlot', OrderNo=$MaxNo
      WHERE SysContext='$EditSysContext' AND JSBPageID=$EditJSBPageID AND JSBPageControlID=$ControlID";
    DBExec ($s);
    }

  if ($TargetControlID)
    {
    if ($TargetControlID==$ControlID) {print "Skip move. Control jump to itself"; return;}

    $TargetOrderNo=0;
    $q1=DBQuery ("SELECT OrderNo,Slot FROM jsb_PageControls WHERE SysContext='$EditSysContext' AND JSBPageID=$EditJSBPageID AND JSBPageControlID=$TargetControlID");
    if ($q1)
      {
      $TargetSlot=$q1->Top->Slot;
      $MaxNo=$q1->Top->OrderNo;
      print "Found target control ($TargetControlID):[$MaxNo]'<br>";
      $s="SELECT MAX(OrderNo) AS LowOrderNo FROM jsb_PageControls
        WHERE OrderNo<$MaxNo AND SysContext='$EditSysContext' AND JSBPageID=$EditJSBPageID AND Slot='$TargetSlot'";
      $q2=DBQuery ($s);
      if (($q2)&&($q2->Top->LowOrderNo !==NULL))
        {
        $LowNo=$q2->Top->LowOrderNo;
        print "Found upper control with [$LowNo]'<br>";
        $TargetOrderNo=($LowNo+$MaxNo)/2;
        }
      else
        {
        print "No control upper than target<br>";
        $TargetOrderNo=$MaxNo-100000;
        }
      print "<b>Set new OrderNo:</b> [$TargetOrderNo]'<br>";

      $s="UPDATE jsb_PageControls
        SET Slot='$TargetSlot', OrderNo=$TargetOrderNo
        WHERE SysContext='$EditSysContext' AND JSBPageID=$EditJSBPageID AND JSBPageControlID=$ControlID";
      DBExec ($s);
      }
    }
#    $_ENV->InitWindows();
    print "<script>if (window.parent.frames['viewer']) window.parent.frames['viewer'].location.reload();
    else { W.modalResult('ok');} </script>";
  }

function Remove ($args)
  {
  extract(param_extract(array(
    EditSysContext=>'string',
    EditJSBPageID=>'int',
    ControlID=>'int',
    ),$args));

  $s="DELETE FROM jsb_PageControls
  WHERE SysContext='$EditSysContext' AND JSBPageID=$EditJSBPageID AND JSBPageControlID=$ControlID";
  DBExec ($s);
#  $_ENV->InitWindows();
  print "<script>if (window.parent.frames['viewer']) window.parent.frames['viewer'].location.reload();
  else { W.modalResult('ok');} </script>";
  }

function UpdateFromPageEditor($args)
  {
  extract(param_extract(array(
    SysContext=>'string',
    JSBPageID=>'int',
    JSBPageControlID=>'int',
    Properties=>'array',
    ),$args));

  $s="SELECT * FROM jsb_PageControls
  WHERE JSBPageControlID=$JSBPageControlID AND ((SysContext='$SysContext' AND JSBPageID=$JSBPageID) OR (SysContext='layouts'))";

  $q=DBQuery ($s,"SysContext");
  if (!$q)
    {
    return array(ModalResult=>true);
    }


  if ($q->Rows[$SysContext])
    {
    $ControlClass=$q->Rows[$SysContext]->ControlClass;
    $Properties  =$q->Rows[$SysContext]->PropertiesStr;
    $OrderNo     =$q->Rows[$SysContext]->OrderNo;
    }
  else
    {
    if ($q->Rows['layouts'])
      {
      $ControlClass=$q->Rows['layouts']->ControlClass;
      $OrderNo     =$q->Rows['layouts']->OrderNo;
      }
    }
  $Properties=$_ENV->Unserialize($Properties);

  foreach ($args as $k=>$v)
    {
    if (substr($k,0,2)=='p_')
      {
      $Name=substr($k,2);
      $Properties[$Name]=$v;
      }
    }
  $PropertiesStr=$_ENV->Serialize($Properties);
  $exists=false;
  if ($q) foreach ($q->Rows as $aSysContext=>$row)
    {
    if (($aSysContext==$SysContext) && ($row->JSBPageID==$JSBPageID))
      {
      $exists=true;
      break;
      }
    }

  if ($exists)
    {
    $s="UPDATE jsb_PageControls SET PropertiesStr='$PropertiesStr'
    WHERE SysContext='$SysContext' AND JSBPageID=$JSBPageID AND JSBPageControlID=$JSBPageControlID";
    }
  else
    {
    $s="INSERT INTO jsb_PageControls (JSBPageControlID,JSBPageID,SysContext,Slot,PropertiesStr,OrderNo,ControlClass)
     VALUES ($JSBPageControlID,$JSBPageID,'$SysContext','aslayout','$PropertiesStr',$OrderNo,'$ControlClass')";
    }
    DBExec ($s);
  return array(ModalResult=>true);
  }
}

?>
