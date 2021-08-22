<?php
class doc_TSearchForm
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Documents";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['doc'];
  $__=&$GLOBALS['_STRINGS']['_'];
  
  $q=DBQuery ("SELECT FormID,Caption FROM doc_Forms WHERE FormType='search' ORDER BY FormID","FormID");
  
  $this->Propdefs=array(
    FormID=>array(Type=>"list",Caption=>"Форма поиска, которая формирует запрос. Тип формы: ПОИСК",Recordset=>&$q,CaptionField=>"Caption",Required=>1),
    TargetPage=>array (Caption=>"Какую страницу выводить после отправки данных",
      Editable=>0,Required=>1,Type=>"InputModal",ModalCall=>"jsb.IPage.Select",InitCall=>"jsb.IPage.GetPageNameByValue"
    ),
    Text_ListIsEmpty=>array(Type=>"String",Caption=>"Текст, выводимый, когда документ пуст",DefaultValue=>"Записей нет"),
#    ShowPageTabs=>array(Type=>"Boolean",Caption=>"Выводить страницы как закладки"),
    Width=>array(Type=>"String",DefaultValue=>'100%'),
#    TableStyle=>array(Type=>"inputmodal",Caption=>"Стиль оформления формы",ModalCall=>"jsb.IThemeReader.SelectTableStyle.f",InitCall=>"jsb.IThemeReader.GetTableStyleByValue"),
    SubmitType=>array(Type=>'list',Values=>array(inframe=>"Общий результат поиска показывать в окошке рядом",totargeturl=>"Перейти на страницу результатов")),
    AutoHide=>array(Type=>"Boolean",Caption=>"Автоматически сворачивать при появлении"),
  );
  $this->Datadefs=array(
		FormCaption=>array(DataType=>"String",Caption=>$_[NEWSGROUP_CAPTION]),
#		Pages =>array(DataType=>"Pages",Caption=>$_[NEWSBROWSER_PAGES]),
		);
  
  }

function Init (&$Control) {
  $__=&$GLOBALS['_STRINGS']['_'];
  $_ =&$GLOBALS['_STRINGS']['doc'];
  extract ($Control->Properties);
  
  $Control->SearchID=$_SESSION->doc_Search['SearchID'];
  include_once ("inc.DocumentForm.php");
  $Control->form=new DocumentForm();
  $r=$Control->form->Load(array(FormID=>$FormID));
  if ($r['Error']) return $r;
  $Control->Data['FormCaption']=$Control->form->Caption;
}

function Render(&$Control) {
  $__=&$GLOBALS['_STRINGS']['_'];
  $_ =&$GLOBALS['_STRINGS']['doc'];
  extract ($Control->Properties);
	global $cfg,$_HOMEURL;
  if (!$SubmitCaption) $SubmitCaption=$_['CAPTION_SEARCH'];
  $TargetURL=($TargetPage)?"$_HOMEURL/$TargetPage.$cfg[VirtualExtension]":"";
  $Control->form->DisplaySearchForm(array(
    SearchID=>$Control->SearchID,
    SubmitType=>$SubmitType,
    TargetURL=>$TargetURL,
    SubmitCaption=>$SubmitCaption,
    Text_ListIsEmpty=>$Text_ListIsEmpty,
    Width=>$Width,
    AutoHide=>$AutoHide
  ));
}

}
?>
