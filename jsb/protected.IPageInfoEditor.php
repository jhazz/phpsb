<?
class jsb_IPageInfoEditor
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Web page editor";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(
  MainDesigner=>"PrintEditorPanel,UpdatePageData,PrintAdminPageEditorPanel",
  Composer=>"PrintEditorPanel,UpdatePageData,PrintAdminPageEditorPanel",
  );


function jsb_IPageInfoEditor()
  {
  $_=&$GLOBALS[_STRINGS][jsb];
  $this->Title=$_[IPAGEINFOEDITOR];
  }


function PrintEditorPanel ($args)
  {
  global $JSB_PageData,$JSB_LayoutPageData, $cfg;
#  $_ENV->InitWindows();
  $_=&$GLOBALS['_STRINGS']['jsb'];
  extract(param_extract(array(
    SomeInfo=>'nonesc_string',
    ),$args));

  if ($JSB_PageData->Metadata)
    {
    $Metadata=$_ENV->Unserialize($JSB_PageData->Metadata);
    $Keywords=$Metadata['keywords'];
    $Description=$Metadata['description'];
    }
  parse_str($JSB_PageData->Options,$opt);
  $img=$opt['i'];
  $img_hover=$opt['hi'];
  $InfoTitle="[$JSB_PageData->Literal] $JSB_PageData->SysContext/$JSB_PageData->JSBPageID.".$cfg['VirtualExtension'].' ';
  if ($JSB_PageData->SysContext=='layouts') {$InfoTitle.="<i>Default parameters for layout </i> ";}
  if ($JSB_PageData->Title) {$InfoTitle.=langstr_get($JSB_PageData->Title);}
   else $InfoTitle.="[".langstr_get($JSB_PageData->PCaption)."]";

  $PageInfo="<b>Context:</b> $JSB_PageData->SysContext<br/>
    <b>PageID:</b> $JSB_PageData->JSBPageID<br/>
    <b>PHTML File:</b> $JSB_PageData->TemplateFileName<br/>";

  if ($JSB_LayoutPageData) {$PageInfo.="<b>Layout:</b> ".langstr_get($JSB_LayoutPageData->LCaption);}
  if ($SomeInfo) {$PageInfo.="<br><br>".$SomeInfo;}

  $_ENV->OpenForm(array(Align=>'center',
    Name=>'jsbPageInfoEditor',
    Action=>ActionURL("jsb.IPageInfoEditor.UpdatePageData.b"),
    ShowCancel=>0,
    HideSubmit=>1,
    ReloadOnOk=>1,
    Style=>"clear"));

  print "<style>
  .jsbPE {font-family:verdana; font-size:10px; font-weight: normal; color:#000000; background-color:#cccccc;}
  .jsbPET{font-family:verdana; font-size:10px; font-weight: bold  ; color:#ffffff; background-color:#888888;}
  .jsbPEI{font-family:verdana; font-size:10px; font-weight: normal; color:#000000; background-color:#e0e8e8;}
  .jsbPEB{font-family:verdana; font-size:10px; font-weight: bold  ; color:#000000; background-color:#bbbbbb;}
  </style>
  <table bordercolorlight='#333333' bordercolordark='#FFFFFF' width='100%' border='1' cellpadding='2' cellspacing='0'>
  <tr><td colspan='3' class='jsbPET'>$InfoTitle</td></tr>
  <tr valign='top'>
  <td class='jsbPE'>
  <table>
  <tr><td class='jsbPE' align='right'>$_[CAPTION_PAGE_MENUCAPTION]:</td><td class='jsbPE'>";
  $_ENV->PutFormField(array(Type=>'langstring',Style=>'clear',Required=>1,Caption=>$_['CAPTION_PAGE_MENUCAPTION'],Name=>'NewPageCaption',MaxLength=>30,Value=>$JSB_PageData->Caption));
  print "</td></tr><tr><td class='jsbPE' align='right'>$_[CAPTION_PAGE_TITLE]:</td><td>";
  $_ENV->PutFormField(array(Type=>'langstring',Style=>'clear',Required=>1,Caption=>$_['CAPTION_PAGE_TITLE'],Name=>'NewPageTitle',MaxLength=>100,Value=>$JSB_PageData->Title));
  print "</td></tr>
     <tr><td class='jsbPE' align='right'>$_[CAPTION_PAGE_KEYWORDS]:</td><td><input size='40' maxlength='250' class='jsbPEI' type='text' name='NewPageKeywords' value='".$Keywords."'></td></tr>
     <tr><td class='jsbPE' align='right'>$_[CAPTION_PAGE_DESCRIPTION]:</td><td><textarea cols='40' rows='3' class='jsbPEI' name='NewPageDescription'>".$Description."</textarea></td></tr>
     </table>
  </td>
  <td class='jsbPE'>
    Menu item image:<br>";
    $_ENV->PutFormField(array(Type=>'inputmodal',Name=>'img',Value=>$img,Style=>'clear',Editable=>1,ModalCall=>ActionURL('jsb.IThemeReader.SelectSkinImage')));
    print "<br>Menu item image onMouseOver<br/>";
    $_ENV->PutFormField(array(Type=>'inputmodal',Name=>'img_hover',Value=>$img_hover,Style=>'clear',Editable=>1,ModalCall=>ActionURL('jsb.IThemeReader.SelectSkinImage')));
    print "</td><td class='jsbPE'>
    <br>$PageInfo
    <input type='hidden' name='OldOptions' value='$JSB_PageData->Options'>
    <input type='hidden' name='EditSysContext' value='$JSB_PageData->SysContext'>
    <input type='hidden' name='EditJSBPageID' value='$JSB_PageData->JSBPageID'>
    <br/><br/>
    <input type='submit' class='jsbPEB' id='jsbPageInfoEditor_submitbtn' value='Update'>
    </td></tr></table>";
  $_ENV->CloseForm();
  }


  function UpdatePageData($args)
    {
    global $cfg;
    extract(param_extract(array(
      EditJSBPageID=>'int',
      EditSysContext=>'string',
      NewPageCaption=>'string',
      NewPageTitle=>'string',
      NewPageKeywords=>'nonesc_string',
      NewPageDescription=>'nonesc_string',
      OtherMeta=>'nonesc_string',
      img=>'string',
      img_hover=>'string',
      OldOptions=>'string',
      ),$args));

    $result="";
    if ($NewPageCaption=='') {return array(Message=>"Caption cannot be empty!");}
    if ((!$EditSysContext)||(!$EditJSBPageID)) {return array(Message=>"Page address is absent!");}

    parse_str($OldOptions,$opt);
    $opt['i']=$img;
    $opt['hi']=$img_hover;
    $Options="";
    if ($NewPageKeywords)    $Metadata['keywords']=$NewPageKeywords;
    if ($NewPageDescription) $Metadata['description']=$NewPageDescription;
    if ($OtherMeta)          $Metadata['other']=$OtherMeta;
    $MetadataStr=$_ENV->Serialize($Metadata);

    foreach($opt as $k=>$v) if ($v) {if ($Options) {$Options.="&";} $Options.=$k.'='.urlencode($v);}
    $s="UPDATE jsb_Pages SET
      Caption='$NewPageCaption',Title='$NewPageTitle',Options='$Options',MetaData='$MetadataStr'
      WHERE SysContext='$EditSysContext' AND JSBPageID=$EditJSBPageID";
    if (!DBExec ($s)) {return array(Error=>"Cannot update page info");}

    $cleaner=&$_ENV->LoadInterface("jsb.IClearTmp");
    $cleaner->NavigationMenu();
    return array(ModalResult=>true);
    }

function PrintAdminPageEditorPanel($args)
  {
  global $cfg,$JSBPageID,$SysContext;
  $__=&$GLOBALS[_STRINGS][_];
  $_=&$GLOBALS[_STRINGS][jsb];

#  $_ENV->InitWindows();
  $squery="SELECT P.JSBPageID, P.Caption, P.Options AS POptions FROM jsb_Pages P
   WHERE SysContext='$SysContext' AND JSBPageID=$JSBPageID";
  $q=DBQuery ($squery);

  if ($q)
    {
    parse_str($q->Top->POptions,$opt);
    $SelectedCall=$opt['intf'];
    }
  print "<table width='100%' height='100%' cellspacing='0' border='0' cellpadding='0'><tr><td align='center' valign='middle'>";

  print "<h1>$_[CAPTION_SETADMINACTION]</h1>
    <form name='jsbPageInfoEditor' onSubmit='target=W.openModal({reloadOnOk:1})' method='post' action='"
    .ActionURL("jsb.IPageInfoEditor.UpdateAdminPageData.b")
    ."'><table cellpadding='5'>
    <tr><td>$_[CAPTION_PAGE_MENUCAPTION]</td>
    <td><input class='inputarea' size='40' maxlength='50' name='NewPageCaption' value='".$q->Top->Caption."'></td></tr>
    <tr><td>$_[CAPTION_CALLINTERFACEMETHOD]</td><td><input class='inputarea' size='40' name='InterfaceMethod' value='".$opt['intf']."'></td></tr>
    <tr><td></td><td>";
    $_ENV->PutButton('submit');
     print "<input type='hidden' name='EditSysContext' value='admin'>
      <input type='hidden' name='EditJSBPageID' value='".$q->Top->JSBPageID."'>
      </td></tr>
    </table>

    </form></td></tr></table>";
  }

}

?>
