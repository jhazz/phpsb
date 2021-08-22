<?php
class doc_TSearchResult
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Documents";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['doc'];
  $__=&$GLOBALS['_STRINGS']['_'];
  $q=DBQuery ("SELECT FormID,Caption FROM doc_Forms WHERE FormType='list' ORDER BY FormID","FormID");
  $qi=DBQuery ("SELECT FormID,Caption FROM doc_Forms WHERE FormType='display' ORDER BY FormID","FormID");
  $qbt=DBQuery ("SELECT BriefcaseTypeID,Caption FROM doc_BriefcaseTypes ORDER BY BriefcaseTypeID","BriefcaseTypeID");

  $this->Propdefs=array(
    ShowSearchForm=>array(Type=>'boolean',Caption=>"Отображать параметры запроса, сформировавшего этот результат"),
    SearchFormWidth=>array(Type=>'int',Caption=>"Ширина формы поиска"),
#    SearchFormID=>array(Type=>"list",Caption=>"Форма ПОИСК, которая выполняет запрос и чьи результаты выводятся",Recordset=>&$q,CaptionField=>"Caption",Required=>1),
#    FormID=>array(Type=>"list",Caption=>"Форма СПИСОК",Recordset=>&$q,CaptionField=>"Caption",Required=>1),
    OpenDocumentFormID=>array(Type=>"list",Caption=>"Форма для открытия документа",NullCaption=>"[Использовать форму ДОКУМЕНТ по-умолчанию]",Recordset=>&$qi,CaptionField=>"Caption"),
    OpenContext=>array(Type=>"SysContext",Caption=>"Раздел сайта для открывания документа (если не указана форма)"),
    NoDocumentsFound=>array(Type=>"String",Caption=>"Отсутствуют документы",DefaultValue=>"Отсутствуют документы списка"),
    ShowNumbers=>array(Type=>"Boolean",Caption=>"Показывать порядковые номера"),
    CSS_Href=>array(Type=>"CSS_Class",BaseCSSClass=>"a"),
    Caption_RowCountText=>array(Type=>'Caption',Caption=>"Общее число документов:"),
    Caption_PutToBriefcase=>array(Type=>'Caption',Caption=>"Положить в портфель"),
    URL_BriefcaseView=>array(Type=>"InputModal",Caption=>"Ссылка на страницу, которая отображает портфель данного типа",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    BriefcaseTypeID=>array(Type=>"list",Caption=>"В какой портфель складывать документы",NullCaption=>"[--Не использовать портфель--]",
      Recordset=>&$qbt,CaptionField=>"Caption"),
    );
	$this->Datadefs=array(
		FormCaption=>array(DataType=>"String",Caption=>"Заголовок формы отображения результатов поиска"),
		Pages =>array(DataType=>"Pages",Caption=>"Страницы "),
		RowCountText=>array(DataType=>"String",Caption=>"Общее количество документов"),
		);
  }
  
function Init(&$Control) {
  $__=&$GLOBALS['_STRINGS']['_'];
  $_=&$GLOBALS['_STRINGS']['doc'];
  global $_SESSION;
  extract($Control->Properties);
  $Control->SearchID=$_SESSION->doc_Search['SearchID'];
  if (!$Control->SearchID) { 	return; }
  
  $isearch=&$_ENV->LoadInterface("doc.PSearch");
	$Control->searchresult=$isearch->PrepareSearchResult(array(SearchID=>$Control->SearchID));

  $RowsPerPage=20;
	$Control->PageNo=$Control->Arguments['p'];
	if (!$Control->PageNo) {$Control->PageNo=1;}
#  $Control->RowCount=$Control->form->GetCount(array(SearchID=>$Control->SearchID));
  $PageCount=ceil($Control->searchresult['DocCount']/$RowsPerPage);
  
  $Control->tags=$isearch->LoadDocumentsBySearchResult(array(SearchID=>$Control->SearchID,PageNo=>$Control->PageNo,RowsPerPage=>$RowsPerPage));
  $Control->Data['Pages']=array(PageCount=>$PageCount,PageNo=>$Control->PageNo,JSBPageControlID=>$Control->JSBPageControlID);
  $Control->Data['RowCountText']=$Caption_RowCountText." ".$Control->searchresult['DocCount'];
  include_once ("inc.DocumentForm.php");
  

  /*return;
  
  
  
  $Control->form=new DocumentForm();
  $r=$Control->form->Load(array(FormID=>$FormID));
  if ($r['Error']) return $r;
		  include_once ("inc.DocumentForm.php");
		  $form=new DocumentForm();
		  $r=$form->Load(array(DocClassID=>$DocClassID,FormType=>"list.preview"));
  
  $RowsPerPage=20;
  $Control->RowCount=$Control->form->GetCount(array(SearchID=>$Control->SearchID));
  $PageCount=ceil($Control->RowCount/$RowsPerPage);
  
  $Control->Data['FormCaption']=langstr_get($Control->form->qform->Top->Caption);
  $Control->form->LoadDocumentsBySearchResult(array(
    SearchID=>$Control->SearchID,
    PageNo=>$Control->PageNo,
    RowsPerPage=>$RowsPerPage));
  
  $Control->Data['Pages']=array(PageCount=>$PageCount,PageNo=>$Control->PageNo,JSBPageControlID=>$Control->JSBPageControlID);
  $Control->Data['RowCountText']=$Caption_RowCountText." ".$Control->RowCount;
  */
}

function Render(&$Control)
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  $_=&$GLOBALS['_STRINGS']['doc'];
  extract ($Control->Properties);

  if (!$Control->SearchID) {
  	print $NoDocumentsFound;
  	return;
  }
  
  if ($ShowSearchForm) {
  	if (!$Control->searchresult['SearchFormID']) {return array(Error=>'Отсутствует форма поиска в запросе');}
	  $Control->form=new DocumentForm();
	  $r=$Control->form->Load(array(FormID=>$Control->searchresult['SearchFormID']));
	  if ($r['Error']) return $r;
	  $Control->form->DisplaySearchForm(array(
	    SearchID=>$Control->SearchID,
	    SubmitType=>'inframe',
	    TargetURL=>$_SERVER['REQUEST_URI'],
	    SubmitCaption=>$SubmitCaption,
	    Text_ListIsEmpty=>$Text_ListIsEmpty,
	    Width=>$SearchFormWidth,
	    AutoHide=>1
	  ));
	  
  }
  
  print "<form method='post' action='".ActionURL("doc.PBriefcase.Put")."'>";
  print "<input type='hidden' name='BriefcaseTypeID' value='$BriefcaseTypeID'/>";
  print "<input type='hidden' name='FormID' value='$FormID'/>";
  
    
  $BriefcaseID=$_SESSION->doc_Briefcase[$BriefcaseTypeID];
  $AlreadySelected=false;
  if ($BriefcaseID) {
  	$q=DBQuery("SELECT ParamValues FROM doc_Briefcases WHERE BriefcaseID=$BriefcaseID");
  	if ($q) {
  		$Parameters=$_ENV->Unserialize($q->Top->ParamValues);
  		if ($Parameters['_collections_']) foreach ($Parameters['_collections_'] as $afield=>$adocs) {
  			list ($FieldName,$DocClassID)=explode (":",$afield);
  			if ($adocs) foreach ($adocs as $id=>$data) {
  				$AlreadySelected["$DocClassID:$id"]=1;
  			}
  		}
  	}
  	if ($AlreadySelected) $args2['AlreadySelected']=&$AlreadySelected;
  }

  
  # output list of documents
  $displayargs=array(AlreadySelected=>&$AlreadySelected,SubmitType=>'totargeturl',Taggable=>($BriefcaseTypeID)?1:0);
  if (!$Control->tags) {return array(Error=>"Поиск не успешен");}
  $DocClassCount=count($Control->tags);
  foreach ($Control->tags as $DocClassID=>$DocumentTags) {
  	if ($DocClassCount>1) {
  		print "Класс документа: $DocClassID<br>";
  	}
  	$form=new DocumentForm();
	  $r=$form->Load(array(DocClassID=>$DocClassID,FormType=>"list"));
	  if ($r['Error']) return $r;
	  print "<h3>".langstr_get($form->qform->Top->Caption)."</h3>";
	  $form->LoadDocumentList(array(WhereKeyIn=>implode(",",array_keys($DocumentTags))));
	  $form->DisplayList($displayargs);
		if ($result['Error']) return $result;
  }  
  
  print "<table width='100%'><tr><td></td><td align='right'>";
  if ($URL_BriefcaseView)	$_ENV->PutButton(array(Action=>"button",Kind=>"view",Href=>$URL_BriefcaseView,Caption=>"Посмотреть свой портфель"));
  $_ENV->PutButton(array(Action=>"submit",Caption=>$Caption_PutToBriefcase));
  print "</td></tr></table>";
  print "</form>";
  }
}
?>
