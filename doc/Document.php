<?
class doc_Document
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Documents";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";


function InitComponent()
  {
  global $cfg;
  $_ =&$GLOBALS['_STRINGS']['doc'];
  $__=&$GLOBALS['_STRINGS']['_'];
  $qdoc=DBQuery ("SELECT DocClassID,Caption FROM doc_Classes","DocClassID");
  $this->Propdefs=array(
    DocClassID=>array(Type=>"list",NullCaption=>"[Определять автоматически]",Caption=>"Класс документа, который будет загружаться",Recordset=>&$qdoc,CaptionField=>"Caption"),
    );

	$this->Datadefs=array(
#		DocumentID=>array(DataType=>"doc.ID",Caption=>"Ключевой идентификатор документа"),
		Document=>array(DataType=>"doc.Document",Caption=>"Документ загружаемой страницы"),
		);
  }


function Init (&$Control)
  {
  if ($Control->Properties) extract ($Control->Properties);
  if ($DocClassID) {
  	$Control->qdoc=DBQuery("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
  	$Control->qdf=DBQuery("SELECT * FROM doc_Fields WHERE DocClassID=$DocClassID","DocFieldID");
  	if (!$Control->qdf) return;
  	if ($Control->DesignMode) {
	  	foreach ($Control->qdf->Rows as $DocFieldID=>$field) {
	  		$this->Datadefs[$field->FieldName]=array(DataType=>"string",Caption=>langstr_get($field->Caption));
	  	}
  	}
  	
  }

  if ($Control->DesignMode)
    {
    $Control->Data['DocumentID']=0;
    return;
    }
    
  $ID=$Control->Data['DocumentID']=intval($Control->JSBPageID);
  if ($Control->qdoc) {
  	$DocTable=$Control->qdoc->Top->DocTable;
  	$IDField =$Control->qdoc->Top->IDField;

  	$Control->qdata=DBQuery("SELECT * FROM $DocTable WHERE $IDField=$ID");
  	if ($Control->qdata) {
  		foreach ($Control->qdf->Rows as $DocFieldID=>$field) {
  			$FieldName=$field->FieldName;
  			$v=$Control->qdata->Top->$FieldName;
  			switch ($field->FieldType) {
  				case 'enum': 
  					$v=intval($v);
  					if ($v) {
  						$qv=DBQuery ("SELECT Caption FROM doc_ListValues WHERE Value=$v AND DocFieldID=$DocFieldID");
  						$Control->qv[$DocFieldID]=&$qv;
  						$v=langstr_get($qv->Top->Caption);
  					}
  					
  				break;
  			}
  			$Control->Data["$FieldName"]=$v;
  		}
#  		print "!!!!!";
  		$Control->Data['Document']=array(
  		  qdoc=>&$Control->qdoc,
  		  qdf=>&$Control->qdf,
  			qdata=>&$Control->qdata,
  			qv=>&$Control->qv,
  		  DocTable=>$DocTable,
  		  IDField=>$IDField,
  		  );
  		 
  	}
  }
  }



}

?>
