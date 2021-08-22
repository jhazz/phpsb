<?
class Briefcase {
	
#	var $qbc;  # Список всех портфелей данного посетителя
#	var $qbct; # Данные о типе портфеля BriefcaseTypeID и о маршруте документа, который выйдет при окончании заполнения портфеля
	var $BriefcaseTypeID; # текущий тип портфеля
	var $BriefcaseID; # текущий потфель
	var $DocClassID; # ID Класса документа, являющегося тикетом маршрута
	var $qdocclasses; # классы тикета и его связанных документов второго уровня
	var $vars;
	
	
function ShowDisplayFilters($resetToDefauls=false) {
	  /*extract(param_extract(array(
	    BriefcaseTypeID=>'*int',
	  ),$args));*/
		$DisplayFields=$this->vars['BriefcaseType']['DisplayFields'];

		print "<table cellpadding='10' width='100%'><tr><td class='bgdown'><div id='div1'><a href='javascript:;'
		  	onClick='document.getElementById(\"div1\").style.display=\"none\"; document.getElementById(\"div2\").style.display=\"block\";'>[+] Выбрать параметры произведений для отображения</a></div>
		  	<div id='div2' style='display:none'><h2>Выберите поля, которые будут отражены</h2>";
		print "<form method='post' action='".ActionURL("doc.PBriefcase.SetDisplayFilters.n")."'>";

		$fs=explode("\n",$DisplayFields);
		$DisplayingColumns=false;
		$colNo=0;
		foreach ($fs as $s) {
			$s=trim($s);
			if (!$s) continue;
			list ($FieldName,$FieldCaption)=explode(":",$s,2);
			list ($FieldName,$CheckedOff)=explode ("=",$FieldName,2);
			$FieldName=trim($FieldName);
			if (!$FieldName) {
				$colNo++;
				$DisplayingColumns[$colNo]['Caption']=$FieldCaption;
				continue;
			}
			if ($resetToDefauls) {
				$_SESSION->doc_BriefcaseFieldFilters[$FieldName]=$CheckedOff;
			}
			$DisplayingColumns[$colNo]['Items'][$FieldName]=$FieldCaption;
		}
		if ($DisplayingColumns) {
			print "<table><tr valign='top'>";
			for ($i=1;$i<=$colNo;$i++) {
				print "<td class='bgup'><b>".$DisplayingColumns[$i]['Caption']."</b><br>";
				foreach ($DisplayingColumns[$i]['Items'] as $FieldName=>$FieldCaption) {
					if (!$_SESSION->doc_BriefcaseFieldFilters[$FieldName]) $ch="checked"; else $ch="";
					print "<input type='checkbox' name='dff[$FieldName]' value='1' $ch id='dff_$FieldName'/><label style='cursor:hand' for='dff_$FieldName'>".langstr_get($FieldCaption)."</label><br>";
					print "<input type='hidden' name='dffall[]' value='$FieldName'/>";
				}
				print "</td>";
			}
			print "</tr></table>";
		}

		print "<input type='submit' class='button' value='Обновить'>";
		print "</form></div></td></tr></table>";
	
}
function LoadBriefcase ($args) {
	  extract(param_extract(array(
#	  	UserID=>'int',
	  	BriefcaseID=>'int',
	    BriefcaseTypeID=>'*int',
	    LoadOtherTypes=>'int',
	    LoadAllBriefcases=>'int',
	  ),$args));
  global $_SESSION,$_USER;
 	$this->BriefcaseTypeID=$BriefcaseTypeID;
 	
 	
 	if ($_USER->HasRole("doc:BriefcaseManager")) {
 		$wh="";
 	} else {
	  $wh="SessionKey='$_SESSION->SessionKey'";
	  if ($_USER->UserID)	$wh.=" OR UserID=$_USER->UserID"; 
	  $wh="($wh)";
 	}
 	
	if (!$LoadOtherTypes) $wh=(($wh)?"$wh AND ":"")."BriefcaseTypeID=$BriefcaseTypeID ";
	if (!$LoadAllBriefcases) $wh=(($wh)?"$wh AND ":"")."BriefcaseID=$BriefcaseID";

  DBQuery2Array("SELECT bct.RouteID, bct.SingleForUser, r.TicketDocClassID, bct.AskUser, bct.AskGuest,bct.DisplayFields
FROM doc_BriefcaseTypes AS bct INNER JOIN doc_FlowRoutes AS r ON bct.RouteID = r.RouteID
WHERE bct.BriefcaseTypeID=$this->BriefcaseTypeID",$this->vars['BriefcaseType']);
	DBQuery2Array ("SELECT * FROM doc_Briefcases WHERE $wh",$this->vars['Briefcases'],"BriefcaseID");
  
  if (!isset($this->vars['Briefcases'][$BriefcaseID])) return;
	$this->vars['BriefcaseID']=$BriefcaseID;
	$this->vars['DocClassID']=$DocClassID=$this->vars['BriefcaseType']['TicketDocClassID'];
	
	DBQuery2Array("SELECT DocClassID,DocFieldID,Size,Caption,FieldName,FieldType,TargetDocClass,AutoCalc FROM
		   doc_Fields WHERE DocClassID=$DocClassID ORDER BY Seq",$this->vars['DocClassFields'][$DocClassID],"DocFieldID:FieldName");
	
	$df=&$this->vars['DocClassFields'][$DocClassID]['ByID'];
	if ($df) {
		$targetClasses=false;
		foreach ($df as $fname=>$f) {
			if (preg_match('/collection|document/',$f['FieldType'])) $targetClasses[$f['TargetDocClass']]=1;
		}
	}
	
	if ($targetClasses) {
		
		$lc=implode(",",array_keys($targetClasses));
		DBQuery2Array("SELECT TargetDocClass FROM doc_Fields
		 WHERE (FieldType='document') AND (DocClassID IN ($lc)) AND TargetDocClass<>$DocClassID",
		  $targets,"TargetDocClass");
		
		if ($targets) {
			$lc.=",".implode(",",array_keys($targets));
			DBQuery2Array("SELECT DocClassID,DocFieldID,Size,Caption,FieldName,FieldType,TargetDocClass,AutoCalc FROM
				   doc_Fields WHERE DocClassID IN ($lc) ORDER BY Seq",$this->vars['DocClassFields'],array("DocClassID","DocFieldID:FieldName"));
		}
		$lc.=",$DocClassID";
	} else $lc=$DocClassID;
	DBQuery2Array("SELECT * FROM doc_Classes WHERE DocClassID IN ($lc)",$this->vars['DocClasses'],"DocClassID:ClassName");
	
	
	$this->vars['BriefcaseValues']=$_ENV->Unserialize($this->vars['Briefcases'][$BriefcaseID]['ParamValues']);
	#print_r($this->vars['BriefcaseValues']);
#	$this->vars['Data']=false;
	$autoCalcValues=false;

	
	# Предварительная загрузка документов, на которые ссылаются элементы внутри коллекции (в данном случае из gtrf_Data)
	$listOfLinkedDocumentIDs=false;
	
	foreach ($df as $fieldID=>$field) {
		if ($field['FieldType']=='collection') {
			$collectionItemClassID=$field['TargetDocClass']; # (11) gtrf.RequestProducts
			$itemFields=&$this->vars['DocClassFields'][$collectionItemClassID]['ByName'];
			foreach ($itemFields as $itemFieldName=>$itemField) { #gtrf_RequestDetails
				if (($itemField['FieldType']=='document')&&($itemField['TargetDocClass']!=$DocClassID)) {
					$data=&$this->vars["BriefcaseValues"]['_collections_']["$field[FieldName]:$itemField[TargetDocClass]"];
					if ($data) foreach ($data as $id=>$tmp) {
						$listOfLinkedDocumentIDs[$itemField['TargetDocClass']][]=$id;
					}
					break;
				}
			}
			
		}
	}
	
	
	if ($listOfLinkedDocumentIDs) foreach ($listOfLinkedDocumentIDs as $aDocClassID=>$arrayOfIDs) {
		$aDocTable=$this->vars['DocClasses']['ByID'][$aDocClassID]['DocTable'];
		$aIDField=$this->vars['DocClasses']['ByID'][$aDocClassID]['IDField'];
		DBQuery2Array("SELECT * FROM $aDocTable
		   WHERE $aIDField IN (".implode(",",$arrayOfIDs).")",&$linkedDocumentsData[$aDocClassID],$aIDField);
	}
	# Теперь сборка
	$requiredLists=false;
	
	foreach ($df as $fieldID=>$field) {
		$fname=$field['FieldName'];
		
		
		$field['FormFieldName']="briefcase[$fname]";
		$field['FieldPath']="$fname";
		list ($type,$subtype)=explode (".",$field['FieldType']);
		switch ($type) {
			case 'collection': 
				$collectionItemClassID=$field['TargetDocClass'];
				$itemFields=&$this->vars['DocClassFields'][$collectionItemClassID]['ByName'];
				$this->vars['Fields'][$fname]=$field;
				
				foreach ($this->vars["BriefcaseValues"]['_collections_'] as $classificator=>$collectionData) {
					list ($aFieldName,$itemLinkedClassID)=explode (":",$classificator);
					if ($aFieldName != $fname) {continue;}
					
					if ($collectionData) foreach ($collectionData as $id=>$values) {
						$item=false;
#						$collectionData[$id][$IDField]=$id;
						foreach ($itemFields as $itemFieldName=>$itemField) { #gtrf_RequestDetails
							if ($itemField['TargetDocClass']==$DocClassID) {continue;} # exclude back reference to the parent class
							#$collectionData[$id]['FormFieldName']="_collections_[$classificator][$id][$itemFieldName]";
							
							$this->vars['Fields'][$fname]['Items'][$id][$itemFieldName]=$itemField;
							if (($itemField['FieldType']=='document')&& $itemField['TargetDocClass']) {
								$subitemFields=&$this->vars['DocClassFields'][$itemField['TargetDocClass']]['ByName'];
								foreach ($subitemFields as $subitemFieldName=>$subitemField) {
									#$this->vars['Fields'][$fname]['Items'][$id][$itemFieldName][$subitemFieldName]=$subitemField;
									$resultSubItemField=&$this->vars['Fields'][$fname]['Items'][$id][$itemFieldName]['Fields'][$subitemFieldName];
									$resultSubItemField=$subitemField;
									$resultSubItemField['Value']=&$linkedDocumentsData[$itemField['TargetDocClass']][$id][$subitemFieldName];
#									$resultSubItemField['FormFieldName']="_collections_[$itemFieldName:$itemField[TargetDocClass]][$id][$subitemFieldName]";
									if (($subitemField['FieldType']=='enum')||($subitemField['FieldType']=='set')) {
#										$resultSubItemField['EnumDocFieldID']=$subitemField['DocFieldID'];
										$requiredValueLists[$subitemField['DocFieldID']][]=&$resultSubItemField;
									}
								}
								continue;
							}
							$this->vars['Fields'][$fname]['Items'][$id][$itemFieldName]['Value']=$values[$itemFieldName];
							$this->vars['Fields'][$fname]['Items'][$id][$itemFieldName]['FormFieldName']="briefcase[_collections_][$classificator][$id][$itemFieldName]";
							if ($itemField['AutoCalc']) {
								$autoCalcValues[]=array(
								'variable'=>&$this->vars['Fields'][$fname]['Items'][$id][$itemFieldName]['Value'],
								'expr'=>$itemField['AutoCalc'],
								'this'=>&$this->vars['Fields'][$fname]['Items'][$id],
								'parent'=>&$this->vars['Fields'][$fname],
								'parent.parent'=>&$this->vars['Fields']);
							}
						}
					}
				}
				break;
			default:
				
				$this->vars['Fields'][$fname]=$field;
				$this->vars['Fields'][$fname]['Value']=&$this->vars["BriefcaseValues"][$fname];
				
		}
	}
	if ($requiredValueLists) {
		DBQuery2Array("SELECT DocFieldID,Value,Caption FROM doc_ListValues WHERE DocFieldID IN ("
		  .implode (",",array_keys($requiredValueLists)).")",$this->ValuesData,array("DocFieldID","Value"));
		foreach ($requiredValueLists as $DocFieldID=>$arrayOfReferences) {
			foreach (array_keys($arrayOfReferences) as $i) {
				$ref=&$arrayOfReferences[$i];
				$ref['Text']=langstr_get($this->ValuesData[$DocFieldID][$ref['Value']]['Caption']);
			}
		}
		
	}
	if ($autoCalcValues) foreach ($autoCalcValues as $ac) {
		$this->currentAutoCalc=$ac;
		$s=preg_replace_callback("|\{([^}]*?)}|",array($this,replaceVars),$this->currentAutoCalc['expr']);
		eval ("\$r=$s;");
		$ac['variable']=$r;
	}
	}
	
#{this.ProductID.Runtime}." сек"; {parent.TOTAL}[4]=time_sum({parent.TOTAL}[4],{ProductID.Runtime})
#{this.ProductID.Fields.Runtime.Value}." сек"; {parent.TOTALTIME}=time_sum({parent.TOTALTIME}[4],{this.ProductID.Fields.Runtime.Value}
	
function replaceVars($matches){
	$s=$matches[1];
	if (substr($s,0,5)=='this.') {
		$part=str_replace(".","][",substr($s,5));
		$s="\$ac['this'][$part]";
	} elseif (substr($s,0,14)=='parent.parent.') {
		$part=str_replace(".","][",substr($s,14));
		$s="\$ac['parent.parent'][$part]";
	} elseif (substr($s,0,7)=='parent.') {
		$part=str_replace(".","][",substr($s,7));
		$s="\$ac['parent'][$part]";
	}  else {
		$s="\$s";
	}
	return $s;
	
}
	
	function DisplayBriefcases($args) {
	  extract(param_extract(array(
	  	UserID=>'int',
	  	BriefcaseID=>'int', # to show as current
	    BriefcaseTypeID=>'*int',
	    LoadOtherTypes=>'int',
	    LoadAllBriefcases=>'int',
	    TableStyle=>'string',
	    Closeable=>'int',
	    PageNo=>'int',
	    RowsPerPage=>'int=20',
	    
	  ),$args));
	  global $_SESSION,$_USER;
	  
	  
	 	$this->BriefcaseTypeID=$BriefcaseTypeID;
	 	$IsManager=($_USER->HasRole("doc:BriefcaseManager"));
	  
	 	if (!$IsManager) {
		 	if (!$UserID) {
			  $wh="SessionKey='$_SESSION->SessionKey'";
			  if ($_USER->UserID) {	$wh.=" OR UserID=$_USER->UserID"; }
		  } else $wh="UserID=$UserID";
	 	} else {$wh='DateClose<>0';}
		if (!$LoadOtherTypes) $wh="BriefcaseTypeID=$BriefcaseTypeID AND ($wh)";
		if (!$LoadAllBriefcases) $wh="BriefcaseID=$BriefcaseID AND $wh";
		$qc=DBQuery("SELECT COUNT(*) AS RowCount FROM doc_Briefcases WHERE $wh");
		$RowCount=0;
		if ($qc) $RowCount=$qc->Top->RowCount;
		if ($RowCount==0) {
			$Closeable=0;
			print "Заявок нет. Сделайте поиск по фондам и выберите произведения";
		}
		if ($Closeable) {
	  	print "<table cellpadding='10' width='100%' border='0'><tr><td class='bgdown'>";
			print "<div id='bcaselist1'><a href='javascript:;' 
			    onClick='document.getElementById(\"bcaselist2\").style.display=\"block\"; 
			    document.getElementById(\"bcaselist1\").style.display=\"none\";'>[+] Показать все мои заявки</a></div>
			  <div id='bcaselist2' style='display:none;'><h2>Заявки</h2>";
	  }
		
		if (!$PageNo) $PageNo=1;
		$q=DBQuery("SELECT * FROM doc_Briefcases WHERE $wh ORDER BY DateOpen LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage","BriefcaseID");
		
	  $_ENV->PrintTable($q,array(
	    Action=>ActionURL("doc.PBriefcase.ListAction.n"),
	    HiddenFields=>array(BriefcaseTypeID=>$BriefcaseTypeID),
	    Fields=>$fields,
	    Width=>'100%',
	    Fields=>array(
	    	Active=>"Состояние",
	      Caption=>"Название заявки",
	      Information=>"Содержание",
	      DateOpen=>"Дата создания",
	#      DateClose=>"Дата закрытия",
	      ),
	    FieldTypes=>array(DateOpen=>"shortdate",DateClose=>"shortdate"),
	    FieldHooks=>array(Caption=>"tab_Caption",Active=>"tab_Current",Information=>"tab_DocInformation"),
	    FieldHookArgs=>array(IsManager=>$IsManager),
	    ShowDelete=>true,
	    HideSubmit=>1,
	    ShowCheckers=>true,
	    TableStyle=>$TableStyle,
			Pages=>array(RowCount=>$qc->Top->RowCount,RowsPerPage=>$RowsPerPage),	    
	    Buttons=>array(array(Kind=>'add',FormAction=>'add',Caption=>'Открыть новую заявку')),
	    #ButtonEdit=>array(KeyName=>$IDField,ModalWindowAction=>"doc.IDocData.Edit.b",Width=>680,Height=>500),
	    ThisObject=>&$this));
	    
		if ($Closeable) {
			print "</div></td></tr></table>";
		}
	}
	function tab_DocInformation($BriefcaseID,&$data,$f,$a) {
		$params=$_ENV->Unserialize($data->ParamValues);
		if (!$params) {print "<font color='red'>$data->ParamValues</font>"; return;}
		$s="";
		$who=$params['ContactName'];
		if ($who) {print "<b>$who</b><br>";}
		/*
		foreach ($params as $p=>$v) {if ($p!="_collections_") $s.="<tr><td align='right'>$p</td><td>:$v</td></tr>";}
		if ($s) print "<table>$s</table>";*/
		$collections=$params["_collections_"];
		if ($collections) {
			foreach ($collections as $c=>$a) {
				print "Выбрано: ".count($a)." ед; ";
			}
		}
	}
	
	function tab_Caption($BriefcaseID,&$data,$f,$a) {
		global $_SESSION;
		
		$s=$data->$f;
		if ($this->vars['BriefcaseID']==$BriefcaseID) $s="<b>$s</b>"; else 	$s="<a href='?bcaseid=$BriefcaseID'>$s</a>";
		print $s;
	}
	function tab_Current($BriefcaseID,&$data,$f,$a) {
		global $_SESSION;
		if ($a['IsManager']) {
			# this is manager
			if ($data->DateClose) $s="Отправлена ".format_date('shortdate',$data->DateClose); else $s="Еще составляется";
			if ($_SESSION->doc_Briefcase[$data->BriefcaseTypeID]==$BriefcaseID) print "Редактируется";  else {
				print "<a href='?bcaseid=$BriefcaseID&setactive=1'>Редактировать заявку</a>";
			}
			print "<br><span style='font-weight:normal; color:#606060'>$s</span>";
			
		} else {
			# this is user or guest
			if ($_SESSION->doc_Briefcase[$data->BriefcaseTypeID]==$BriefcaseID) print "Формируется";  
			else  {
				if ($data->DateClose) print "<span style='color:#808080'>Отправлена на рассмотрение</font>"; else print "<a href='?bcaseid=$BriefcaseID&setactive=1'>Открыть текущей</a>";
			}
		}
	}
		
	function eval_variables($matches) {
		$s=$matches[1];
		$p=explode (".",$s);
		$sa="\$this->_VARIABLES";
		foreach ($p as $pp) {
			$sa.="['$pp']";
		}
		return $sa;
		
	}

}
	

?>