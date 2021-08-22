<?
class doc_TDisplayForm
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Documents";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

var $Subscribers="Document";


function InitComponent()
  {
  global $cfg;
  $_= &$GLOBALS['_STRINGS']['doc'];
  $__=&$GLOBALS['_STRINGS']['_'];
  $qi=DBQuery ("SELECT FormID,Caption FROM doc_Forms WHERE FormType='display' ORDER BY FormID","FormID");
  $this->Propdefs=array(
			Document=>array(Type=>"Binding",DataType=>"doc.Document",Caption=>"Документ и описание",Required=>1),
      FormID=>array(Type=>"list",Caption=>"Форма для отображения документа",Recordset=>&$qi,CaptionField=>"Caption",Required=>1),
    );
  
  }


function AfterInit (&$Control)
  {
  	if ($Control->Properties) extract ($Control->Properties);
  	if ($Control->DesignMode)
  	{
  		return;
  	}
		$Control->qform=DBQuery("SELECT * FROM doc_Forms WHERE FormID=$FormID");
		$Control->qff=DBQuery("SELECT * FROM doc_FormFields WHERE FormID=$FormID GROUP BY GroupID,FormFieldID ORDER BY Seq ","FormFieldID");
  }
  
function Render(&$Control) {
  	if ($Control->DesignMode)
  	{
  		print "Режим разработки";
  		return;
  	}

  $qdf=&$Control->Document['qdf'];
  $qdata=&$Control->Document['qdata'];
  $qv=&$Control->Document['qv'];
  #$qdoc=&$Control->Document['qdoc'];
  if (!$qdata) {
		print "Документ  не загружен";
		return;
	}
	$print="";
	#$Control->qff->Dump();
	#$qdoc->Dump();
	foreach ($Control->qff->Rows as $FormFieldID=>$FormField) {
  	$DocFieldID=$FormField->DocFieldID;
  	$DocField=&$qdf->Rows[$DocFieldID];
  	$FieldName=$DocField->FieldName;
  	$v=$qdata->Top->$FieldName;
  	#list ($FormFieldType,$FormFieldSubType)=explode(".",$FormField->RepresentType);
  	
  	switch ($FormField->RepresentType) {
  		case 'enum': $qvr=$qv[$DocFieldID]; if ($qvr) $v=langstr_get($qvr->Top->Caption); break;
  	}
  	$print.="<tr valign='top'><td class='bgdowndown' align='right' width='25%'><b>".langstr_get($FormField->Caption)."</b></td><td class='bgdown'>$v</td></tr>";
  }
  if ($print) {
  	print "<table cellpadding='5' cellspacing='1'>$print</table>";
  }
#	$qdata->Dump();
	
}

}

?>
