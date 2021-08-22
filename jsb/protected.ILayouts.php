<?
class jsb_ILayouts
  {
var $CopyrightText="(c)2005 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(MainDesigner=>"Browse,Update,AddLayout,PreviewTemplate");
  function jsb_ILayouts()
    {
    $_=&$GLOBALS['_STRINGS']['jsb'];
    $this->Title=$_['TITLE_THESE_ARE_LAYOUTS'];
    }

  function AddLayout($args)
    {
    extract(param_extract(array(
      LayoutCaption =>'string',
      Literal =>'string',
      LayoutTemplate=>'string',
      LayoutObjectClass=>'string',
      ),$args));

    $_ =&$GLOBALS['_STRINGS']['jsb'];
    $__=&$GLOBALS['_STRINGS']['_'];
    global $cfg;
    $Literal=substr($Literal,0,2);
    $NewPageID=DBGetID("jsb.Page","layouts");
    $s="INSERT INTO jsb_Pages (SysContext,JSBPageID,Caption,Title,State,Options,UpdatedAt)
            VALUES ('layouts',$NewPageID,'$LayoutCaption','$LayoutCaption',1,'phtmpl=$LayoutTemplate&lit=$Literal&obj=$LayoutObjectClass',".time().")";
    DBExec ($s);
    $Control=$_ENV->CreateControl($LayoutObjectClass,false,true);
    $Control->JSBPageControlID=DBGetID("jsb.PageControl");
    $props=$_ENV->Serialize($Control->Properties);
    $s="INSERT INTO jsb_PageControls (JSBPageControlID,ControlClass,OrderNo,SysContext,JSBPageID,Slot,PropertiesStr)
      VALUES ($Control->JSBPageControlID,'$LayoutObjectClass',0,'layouts',$NewPageID,'init','$props')";
    DBExec ($s);
    return array(ModalResult=>true);
    }

  function Update($args)
    {
    if ($args['template'])
      {
      $qpc=DBQuery("SELECT JSBPageControlID,ControlClass,JSBPageID FROM jsb_PageControls WHERE SysContext='layouts' AND Slot='init'","JSBPageID");
      foreach ($args['template'] as $JSBPageID=>$Template)
        {
        $Literal=$args['literal'][$JSBPageID];
        $ObjectClass=$args['objclass'][$JSBPageID];
        if ($Template)
          {
          $s="UPDATE jsb_Pages SET Options='phtmpl=$Template&lit=$Literal&obj=$ObjectClass',UpdatedAt=".time()."
            WHERE SysContext='layouts' AND JSBPageID=$JSBPageID";
#               print $s."<br>";
          DBExec ($s);

          $r=$qpc->Rows[$JSBPageID];
          if (!$r)
            {
            $Control=$_ENV->CreateControl($ObjectClass,false,true);
            $Control->JSBPageControlID=DBGetID("jsb.PageControl");
            $props=$_ENV->Serialize($Control->Properties);
            $s="INSERT INTO jsb_PageControls (JSBPageControlID,ControlClass,OrderNo,SysContext,JSBPageID,Slot,PropertiesStr)
              VALUES ($Control->JSBPageControlID,'$ObjectClass',0,'layouts',$JSBPageID,'init','$props')";
#               print $s."<br>";
            DBExec ($s);
            }
          else
            {
            if ($r->ControlClass!=$ObjectClass)
              {
              $Control=$_ENV->CreateControl($ObjectClass,false,true);
              $Control->JSBPageControlID=$r->JSBPageControlID;
              $props=$_ENV->Serialize($Control->Properties);
              $s="UPDATE jsb_PageControls SET ControlClass='$ObjectClass',PropertiesStr='$props'
                WHERE JSBPageControlID=$Control->JSBPageControlID";
#               print $s."<br>";
              DBExec ($s);
              }
            }
#          print "<br>";
          }
        }
      }

    $subaction=$args['subaction'];
    if ($subaction)
      {
      list($sub,$to)=explode ("_",$subaction);
      if ($sub=='removeto')
        {
        $error=false;
        $to=intval ($to);
        foreach ($_POST['check'] as $RemLayoutID=>$i)
          {
          if ($to==$RemLayoutID)
            {
            print "<p>$_[ERROR_REPLACE_TO_DEADSTYLE]</p>";
            $error=true;
            break;
            }
          }
        if (!$error)
          {
          $remlist=implode (",",array_keys($_POST['check']));
          $s="UPDATE jsb_Pages SET JSBLayoutID=$to WHERE JSBLayoutID IN ($remlist)";
          DBExec ($s);
          DBExec ("DELETE FROM jsb_Pages WHERE SysContext='layouts' AND JSBPageID IN ($remlist)");
          DBExec ("DELETE FROM jsb_PageControls WHERE SysContext='layouts' AND JSBPageID IN ($remlist)");
          }
        }
      }
    return array(ModalResult=>true);
    }


  function tab_PHTMLTemplate($JSBPageID,$row,$fname)
    {
    parse_str($row->Options,$opt);
    $phtmpl=$opt['phtmpl'];
    $_ENV->PutFormField(array(
      Type=>'droplist',
      Style=>'clear',
      ValueSetName=>'templates',
      Value=>$phtmpl,
      Required=>1,
      Size=>30,
      Name=>"template[$JSBPageID]"));
    }

  function tab_ObjectClass($JSBPageID,$row,$fname)
    {
    parse_str($row->Options,$opt);
    $obj=$opt['obj'];
    if (!$obj) $obj='jsb.Page';
    $_ENV->PutFormField(array(
      Type=>'droplist',
      Style=>'clear',
      ValueSetName=>'obj',
      Value=>$obj,
      Size=>30,
      Required=>1,
      Name=>"objclass[$JSBPageID]"));
    }

  function tab_Literal($JSBPageID,$row,$fname)
    {
    parse_str($row->Options,$opt);
    global $UsedLiterals;
    $literal=$opt['lit'];
    $UsedLiterals[$literal]=1;
    print "<center><input size='1' maxlength='2' class='inputarea' style='border:solid 1; text-align:center;' type='text' name='literal[$JSBPageID]' value='$literal'></center>";
    }

  function tab_MenuCaption ($JSBPageID,$row)
    {
    print "<a href='".ActionURL("jsb.ISiteExplorer.Open.n",
      array(Path=>"jsb/layouts/$JSBPageID",
      ContextLocked=>1,
      EditMode=>1,
      NoLayouts=>1))."'>".langstr_get($row->Caption)."</a>";
    }


  function Browse($args)
    {
    $_ =&$GLOBALS['_STRINGS']['jsb'];
    $__=&$GLOBALS['_STRINGS']['_'];
    extract(param_extract(array(
      ThemeName=>'string',
      ),$args));


    function printit(&$str) {print $str;}
    global $cfg,$_THEME_NAME,$_THEME;


    if ($ThemeName)
      {
      $_THEME_NAME=$ThemeName;
      }
    else
      {
      $_THEME_NAME=$cfg['Settings']['jsb']['ActiveTheme']; # frontend theme name
      }

    $ThemeFile=$cfg['ThemesPath']."/$_THEME_NAME/theme.php";
    if (file_exists($ThemeFile))
      {
      include_once ($ThemeFile);
      }
    else
      {
      print "Theme file not found: $ThemeFile";
      }

    global $Templates; # For ILayouts internal functions only!
    $Templates=false; #$GLOBALS['_THEME']['Templates']; # DECLARED IN 'theme.php' file

    global $UsedLiterals;
    global $_THEME_NAME;
    $UsedLiterals=false;

    $TemplatesDir=$cfg['ThemesPath'].'/'.$_THEME_NAME;
    $dh=opendir ($TemplatesDir);
    while ($fname=readdir($dh))
      {
      $afname=$TemplatesDir.'/'.$fname;
      if (is_file($afname)&&(substr($fname,-4)=='.php')&&(substr($fname,0,9)=='template.')&&(substr($fname,0,1)!='~'))
        {
        $tname=substr($fname,9,strlen($fname)-(4+9));
        $desc=$_THEME['Templates'][$tname];
        $Templates[$tname]=($desc)?$desc:$tname;
        }
      }
    closedir ($dh);

    print "<table cellpadding='5' width='100%' ><tr valign='top'><td align='center'>";
    $ql=DBQuery ("SELECT JSBPageID,Caption,Options
    FROM jsb_Pages WHERE SysContext='layouts' AND State=1
    AND JSBPageID<>0","JSBPageID");

    $SubactionList["none"]=$_['CAPTION_MODIFY'];
    if ($ql) foreach ($ql->Rows as $JSPPageID=>$row)
      {
      $SubactionList["removeto_$JSPPageID"]=$_['CAPTION_REMOVETO']." '".langstr_get($row->Caption)."'";
      }

    $cartridges=&$_ENV->LoadCartridgesList(true);
    $ObjectClasses=false;
    foreach ($cartridges as $c=>$IsActive)
      {
      if (!$IsActive) continue;
      $cartridge=&$_ENV->LoadCartridge($c);
      if (method_exists($cartridge,"ObjectClasses"))
        {
        $info=$cartridge->ObjectClasses();
        # check interface availability
        foreach($info as $aObjectClass=>$info)
          {
          $ObjectClasses[$aObjectClass]="$info[Caption] [$aObjectClass]";
          }
        }
      }

    $_ENV->PutValueSet(array(ValueSetName=>'templates',Values=>$Templates));
    $_ENV->PutValueSet(array(ValueSetName=>'obj',Values=>$ObjectClasses));

    # return back current sys theme
    $_ENV->LoadTheme('bm');
    $_ENV->PrintTable($ql,array(
      Action=>ActionURL("jsb.ILayouts.Update.b"),
      ReloadOnOk=>1,
      Fields=>array(Literal=>$_['CAPTION_LITERAL'],
        Caption=>$_['CAPTION_LAYOUT'],
        PHTMLTemplate=>$_['CAPTION_TEMPLATE'],
        ObjectClass=>"Class"),
      ShowCheckers=>true,
      FieldHooks=>array(
        PHTMLTemplate=>tab_PHTMLTemplate,
        Literal=>tab_Literal,
        Caption=>tab_MenuCaption,
        ObjectClass=>tab_ObjectClass),
      ShowDelete=>false,
      ShowOk=>true,
      SubactionList=>$SubactionList,
      TableStyle=>0,
      ThisObject=>&$this
      ));

    $GoodLiteral="A";
    for ($i=0;$i<22;$i++)
      {
      $c=Chr($i+65);
      if (!$UsedLiterals[$c]) {$GoodLiteral=$c;break;}
      }

    print "<script>
    function ShowHiddenDiv(){P$.find(\"insert_layout\").style.display=\"block\";}
    </script>
    <a href='javascript:ShowHiddenDiv();'>$_[CAPTION_ADD_LAYOUT]</a>
      <div style='display:none' id='insert_layout'>";
    $_ENV->OpenForm(array(Modal=>1,Action=>ActionURL("jsb.ILayouts.AddLayout.b"),Align=>"center"));
    $_ENV->PutFormField(array(Type=>'hidden',Name=>'action',Value=>'addlayout'));
    print "<tr><td width='30%' align='right'>$_[CAPTION_LAYOUT_NAME]</td><td>";
    $_ENV->PutFormField(array(Type=>'string',Required=>1,Style=>'clear',MaxLength=>2,Size=>2,Name=>'Literal',Value=>$GoodLiteral));
    print "-";
    $_ENV->PutFormField(array(Type=>'langstring',Required=>1,Style=>'clear',Name=>'LayoutCaption'));
    print "</td></tr><tr><td align='right'>$_[CAPTION_TEMPLATE]</td><td>";
    $_ENV->PutFormField(array(Type=>'droplist',Name=>'LayoutTemplate',Required=>1,Size=>30,Style=>'clear',ValueSetName=>'templates'));
    print "</td></tr><tr><td align='right'>$_[LAYOUT_OBJECTCLASS]</td><td>";
    $_ENV->PutFormField(array(Type=>'droplist',Name=>'LayoutObjectClass',Required=>1,Size=>30,Style=>'clear',Value=>'jsb.Page',DefaultValue=>'jsb.Page',ValueSetName=>'obj'));
    print "</td></tr>";
    $_ENV->CloseForm();
    print "</div></td><td><h3>$_[TITLE_THESE_ARE_TEMPLATES]</h3>";
    foreach ($Templates as $tname=>$desc)
      {
      $flink="";
      print "<b>$tname</b><br>";
      if ($desc!=$tname) print "$desc<br/>";
      print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("jsb.ILayouts.PreviewTemplate.f",array(ThemeName=>$_THEME_NAME,Template=>$tname))."\",w:800,h:550,Title:\"Template [$tname]\"})'>$_[PREVIEW_TEMPLATE]</a><br><br>";
      }
    print "</td></tr></table>";

    if (file_exists($ThemeAbout))
      {
      $f=file($ThemeAbout);
      print "<h2>$f[0]</h2>";
      for ($i=1;$i<count($f);$i++)
        {
        print $f[$i];
        }
      }
    }

  function PreviewTemplate($args)
    {
    extract(param_extract(array(
      ThemeName=>'string',
      Template=>'string'
      ),$args));

    global $cfg;
    $ThemeDir=$cfg['ThemesPath'].'/'.$ThemeName;
    $f=$ThemeDir."/template.$Template.php";
    if (!file_exists($f))
      {
      return array(Error=>"Template not found",Details=>$f);
      }
    function JSB_InitPage ($InitBehavior)
      {
      print_r($InitBehavior);
      }
    function JSB_RenderSlot ($SlotName)
      {
      print "<table width='100%'><tr><td bgcolor='#ff6633' style='font-family:verdana,arial,sans; font-size:12px; color:#ffffff'>$SlotName</td></tr></table>";
      }
    print "Opening file: $f<br>";
    include($f);
    }
  }

?>
