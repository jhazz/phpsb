<?
class doc_PSearch {
	var $CopyrightText="(c)2007 PHP Systems builder. Documents";
	var $CopyrightURL="http://www.phpsb.com/doc";
	var $ComponentVersion="1.0";
	
	
	
	function &LoadDocumentsBySearchResult($args) {
	  extract(param_extract(array(
	  	SearchID=>'*int',
	    PageNo=>'int=1',
	    RowsPerPage=>'int=20',
	  ),$args));
	  

	  $s="SELECT DocClassID,DocumentID FROM doc_SearchTags 
	    WHERE SearchID=$SearchID ORDER BY DocClassID,DocumentID LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage";
	  
	  $ok=DBQuery2Array($s,$Documents,array("DocClassID","DocumentID"));
	  return $Documents;
	  
/*	  
	  $fnames="tt.".implode(",tt.",array_keys($this->TableFields));
		$IDField=$this->qdoc->Top->IDField;
		if ($SearchID) {
			$s="SELECT st.DocumentID,st.DocClassID,$fnames 
			FROM ".$this->qdoc->Top->DocTable." AS tt LEFT JOIN doc_SearchTags AS st ON tt.$IDField=st.DocumentID";
			$s.=" WHERE st.SearchID=$SearchID";
		}
	  if ($this->GroupByFields) $s.=" GROUP BY ".implode (",",array_keys($this->GroupByFields));
	  $s.=" ORDER BY ";
	  if ($this->OrderByFields) {	
	  	$s.=",".implode (",",array_keys($this->OrderByFields));	
	  	if ($this->OrderByDescending==2) $s.=" DESC"; 
	  }
		$s.=" LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage";
		return DBQuery($s,$IDField);	
		*/
	}
	
	function PrepareSearchResult($args) {
 	  extract(param_extract(array(
	    SearchID=>'int', # If search was made... just set SearchID
	    
	    FormID=>'int', # The search form ID that sent data
	    TargetURL=>'string', # open full list after preview
	    Text_ListIsEmpty=>'string',
	    SubmitType=>'string', #inframe/totargetpage
	    BooleanMode=>'int',
	  ),$args));
	  		
		global $_SESSION,$_USER,$cfg;
	  $_ =&$GLOBALS['_STRINGS']['doc'];
		$QueryParameters=false;
		$time=time();
		$lifetime=$cfg['Settings']['doc']['SearchCacheLifetimeInMinutes'];
		if (!$lifetime) $lifetime=30; # 30 minutes to live if not specified
		$lifetime*=60;
		if ($SearchID) {
			$qs=DBQuery("SELECT * FROM doc_Searches WHERE SearchID=$SearchID");
#			$qs->Dump();
			if (!$qs) {
				return array(Error=>'Search cache has been dropped. Please re-query search',Deatils=>" SearchID=$SearchID");
			}
			$result['SearchFormID']=$qs->Top->SearchFormID;
			$QueryParameters=unserialize($qs->Top->QueryParameters);
			if (!$qs->Top->Dropped) {
				# Search result still in cache... just return it and update droptime
				DBUpdate(array(Table=>'doc_Searches',Keys=>array(SearchID=>$SearchID),Values=>array(DropTime=>$time+$lifetime)));
				$result['DocCount']=$qs->Top->DocCount;
				$result['Title']=$qs->Top->Title;
				return $result;
			} # DROPPED... re request the query
		} else {$result['SearchFormID']=$FormID;}

		
		# cleanup search cache
		$qclean=DBQuery("SELECT SearchID FROM doc_Searches WHERE DropTime<$time AND Dropped=0 LIMIT 0,500","SearchID");
		if ($qclean) {
			$ids=implode (",",array_keys($qclean->Rows));
			DBexec ("DELETE FROM doc_SearchTags WHERE SearchID IN($ids)");
			DBexec ("UPDATE doc_Searches SET Dropped=1 WHERE SearchID IN($ids)");
		#	print "Из кэша уалено ".count($qclean->Rows)." запросов<br>";
		#	$qclean->Dump();
		}
		
		$qform=DBQuery("SELECT * FROM doc_Forms WHERE FormID=$FormID");
	  if (!$qform) return array (Error=>"Form not found",Details=>"FormID=$FormID");
	  
		extract(param_extract(array(
		  DocClassID=>'int',
		  FormType=>'string',
		  Caption=>'langstring',
		  ),$qform->Top));
	  $qdoc=DBQuery ("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
	  if (!$qdoc) {
	  	return array (Error=>"Document class not found for '$Caption'",Details=>"DocClassID=$DocClassID");
	  }
	  $DocTable=$qdoc->Top->DocTable;
	  $IDField=$qdoc->Top->IDField;
	  
		$qf=DBQuery ("SELECT ff.FormFieldID, ff.RepresentType, df.FieldName,
		df.FieldType, df.Size, df.Decimals, df.Required, df.IsProperty, df.TargetDocClass
	  FROM doc_FormFields AS ff LEFT JOIN doc_Fields AS df ON ff.DocFieldID = df.DocFieldID
		WHERE FormID=$FormID ORDER BY ff.Seq","FormFieldID");
		$result['Title']="";
		$result['DocCount']=0;
		$searchExpr="";
		
		foreach ($qf->Rows as $FormFieldID=>$field) {
			if ($SearchID) {
				$value=$QueryParameters[$FormFieldID];
			} else {
				$value=$args["Field$FormFieldID"];
				if (!$value) continue;
				if ($field->IsProperty) {
					return array(Error=>"Я еще не умею работать c поиском по свойствам");
					continue;
				}
				$QueryParameters[$FormFieldID]=$value;
			}
			
			# пропускаем вспомогательные поля
			if (!$field->FieldName) continue;
			$stext="";
			switch ($field->FieldType) {
				case 'enum':
					switch ($field->RepresentType) {
						case 'enum.checks': $stext="$field->FieldName IN ($value)"; break;
					}
					break;
				case 'string': case 'text':
					$value=strip_tags($value);
					$words=explode (" ",$value);
					$words=implode ("* ",$words)."*";
					$stext="MATCH ($field->FieldName) AGAINST ('$words'".(($BooleanMode)?" IN BOOLEAN MODE":"").")";
					$title.=(($title)?",":"")."'$value'";
					break;
				case 'int':
					if (!$value) break;
					list ($min,$max)=explode (":",$value);
					$min=intval($min); $max=intval($max);
					switch ($field->RepresentType) {
						case 'int.gt': if ($min!==false) $stext="$field->FieldName>$min"; break;
						case 'int.lt': if ($max!==false) $stext="$field->FieldName<$max"; break;
						case 'int.bt': 
							if ($max && $min) $stext="$field->FieldName BETWEEN $min AND $max"; 
							elseif ($max) $stext="$field->FieldName<$max"; 
							elseif ($min) $stext="$field->FieldName>$min"; 
							break;
						case 'int': $stext="$field->FieldName=$value"; break;
					}
					$result['Title'].=(($result['Title'])?",":"")."[$min..$max]";
					break;
			}
			if ($stext) $searchExpr.=(($searchExpr)?" AND ":"")."($stext)";
		}
		if (strlen($result['Title'])>100) $result['Title']=substr($result['Title'],0,97)."..";

		
		
		$DropTime=$time+$lifetime; 
		if (!$SearchID) {	$SearchID=DBGetID("doc.Search");$inserting=1;} else $inserting=0;
		$timestart=getmicrotime();
		$s="INSERT INTO doc_SearchTags (`SearchID`,`DocumentID`,`DocClassID`)
		  SELECT $SearchID,`$IDField`,$DocClassID
		  FROM $DocTable WHERE $searchExpr";
#				print $s;
		DBExec ($s);
		$result['TimeElapsed']=round(getmicrotime()-$timestart,4);
		$qc=DBQuery("SELECT COUNT(*) AS DocCount FROM doc_SearchTags WHERE SearchID=$SearchID");
		$result['DocCount']=$qc->Top->DocCount;

		if ($inserting)	{
			DBInsert (array(Table=>"doc_Searches",Values=>array(
				SearchID=>$SearchID,
				SearchFormID=>$FormID,
			  SessionKey=>$_SESSION->SessionKey,
			  UserID=>intval($_USER->UserID),
			  QueryTime=>$time,
			  DropTime=>$DropTime,
			  QueryParameters=>serialize($QueryParameters),
			  DocCount=>$result['DocCount'],
			  TimeElapsed=>$result['TimeElapsed'],
			  Title=>$title
			  ))); 
		} else {
			DBUpdate (array(Table=>"doc_Searches",
				Keys=>array(SearchID=>$SearchID),
				DocCount=>$result['DocCount'],
				Values=>array(
				  DropTime=>$DropTime,
				  DocCount=>$result['DocCount'],
				  Dropped=>0
			)));	
		}
		$result['SearchID']=$SearchID;
		
		return $result;
  
	}
	function AcceptSearchFormData(&$args) {
	  extract(param_extract(array(
	    FormID=>'*int', # Search requesting form
	    TargetURL=>'string', # open full list after preview
	    Text_ListIsEmpty=>'string',
	    SubmitType=>'string', #inframe/totargetpage
	    BooleanMode=>'int',
	  ),$args));
		global $_SESSION;
	  $_ =&$GLOBALS['_STRINGS']['doc'];
		
		$time=time();
		$prevtime=$_SESSION->doc_Search['Time'];
		if (($time-$prevtime)<15) {
			if ($SubmitType!='inframe') print sprintf($_['WARNING_TOO_FAST_RESEARCH'],$time-$prevtime);
			return;
		}
		
	  $result=$this->PrepareSearchResult($args);
	  $SearchID=$result['SearchID'];
	  if ($result['DocCount']) {
			$_SESSION->doc_Search['SearchID']=$SearchID;
			$_SESSION->doc_Search['Time']=$time;
			if ($SubmitType!='inframe') {
				if (!$TargetURL) $TargetURL=$_SERVER['HTTP_REFERER'];
				header("Location: $TargetURL"); exit;
#				print "Выполняется поиск<br><a href='$TargetURL'>Нажмите здесь</a>";
				exit;
			}
	    print "<table cellpadding='10'><tr><td><h1>Результаты поиска</h1><h5>$Caption</h5>
			<table>
		  <tr><td align='right'><b>Ключевые значения поиска:</b></td><td>$result[Title]</td></tr>
		  <tr><td align='right'><b>Время выполнения поиска:</b></td><td>$result[TimeElapsed] сек</td></tr>
		  <tr><td align='right'><b>Найдено документов:</b></td><td><h3>$result[DocCount]</h3></td></tr>
      <tr><td align='right'></td><td><a href='$TargetURL' target='_top'>Открыть результаты</a></td></tr></table>";
		
		  include_once ("inc.DocumentForm.php");
		  $tags=$this->LoadDocumentsBySearchResult(array(SearchID=>$SearchID,PageNo=>1,RowsPerPage=>10));
		  if (!$tags) {return array(Error=>"Поиск не успешен");}
		  $DocClassCount=count($tags);
		  foreach ($tags as $DocClassID=>$DocumentTags) {
		  	if ($DocClassCount>1) {
		  		print "Класс документа: $DocClassID<br>";
		  	}
		  	$form=new DocumentForm();
			  $r=$form->Load(array(DocClassID=>$DocClassID,FormType=>"list.preview"));
			  if ($r['Error']) return $r;
			  print "<hr><h3>".langstr_get($form->qform->Top->Caption)."</h3>";
			  $form->LoadDocumentList(array(WhereKeyIn=>implode(",",array_keys($DocumentTags))));
			  $form->DisplayList(array());
				if ($result['Error']) return $result;
		  }
		  
		 # $qsearchdata->Dump();
		  
		  
/*
		  $form=new DocumentForm();
		  $r=$form->Load(array(DocClassID=>$DocClassID,FormType=>"list.preview"));
		  if ($r['Error']) return $r;
		  print "<hr><h3>".langstr_get($form->qform->Top->Caption)."</h3>";
		  $r=$form->LoadDocumentsBySearchResult(array(SearchID=>$SearchID));
		  if ($r) $form->DisplayList(array());
			if ($result['Error']) return $result;
			*/
	  } else {
	  	print "<center>Поиск не дал результатов</center>";
	  }
	  
	  
/*	  
		global $_SESSION,$_USER;
		$qform=DBQuery("SELECT * FROM doc_Forms WHERE FormID=$FormID");
	  if (!$qform) return array (Error=>"Form not found",Details=>"FormID=$FormID");
	  
		extract(param_extract(array(
		  DocClassID=>'int',
		  FormType=>'string',
		  Caption=>'langstring',
		  ),$qform->Top));
	  $qdoc=DBQuery ("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
	  if (!$qdoc) {
	  	return array (Error=>"Document class not found for '$Caption'",Details=>"DocClassID=$DocClassID");
	  }
	  $DocTable=$qdoc->Top->DocTable;
	  $IDField=$qdoc->Top->IDField;
	  
		$qf=DBQuery ("SELECT ff.FormFieldID, ff.RepresentType, df.FieldName,
		df.FieldType, df.Size, df.Decimals, df.Required, df.IsProperty, df.TargetDocClass
	  FROM doc_FormFields AS ff LEFT JOIN doc_Fields AS df ON ff.DocFieldID = df.DocFieldID
		WHERE FormID=$FormID ORDER BY ff.Seq","FormFieldID");
		
		$searchExpr="";
		$QueryParameters=false;
    $title="";
		foreach ($qf->Rows as $FormFieldID=>$field) {
			$value=$args["Field$FormFieldID"];
			if (!$value) continue;
			if ($field->IsProperty) {
				print "Я еще не умею работать c поиском по свойствам";
				continue;
			}
			$QueryParameters[$FormFieldID]=$value;
			
			# пропускаем вспомогательные поля
			if (!$field->FieldName) continue;
			$stext="";
			switch ($field->FieldType) {
				case 'enum':
					switch ($field->RepresentType) {
						case 'enum.checks': $stext="$field->FieldName IN ($value)"; break;
					}
					break;
				case 'string': case 'text':
					$value=strip_tags($value);
					$words=explode (" ",$value);
					$words=implode ("* ",$words)."*";
					$stext="MATCH ($field->FieldName) AGAINST ('$words'".(($BooleanMode)?" IN BOOLEAN MODE":"").")";
					$title.=(($title)?",":"")."'$value'";
					break;
				case 'int':
					if (!$value) break;
					list ($min,$max)=explode (":",$value);
					$min=intval($min); $max=intval($max);
					switch ($field->RepresentType) {
						case 'int.gt': if ($min!==false) $stext="$field->FieldName>$min"; break;
						case 'int.lt': if ($max!==false) $stext="$field->FieldName<$max"; break;
						case 'int.bt': 
							if ($max && $min) $stext="$field->FieldName BETWEEN $min AND $max"; 
							elseif ($max) $stext="$field->FieldName<$max"; 
							elseif ($min) $stext="$field->FieldName>$min"; 
							break;
						case 'int': $stext="$field->FieldName=$value"; break;
					}
					$title.=(($title)?",":"")."[$min..$max]";
					break;
			}
			if ($stext) $searchExpr.=(($searchExpr)?" AND ":"")."($stext)";
		}
		if (strlen($title)>100) $title=substr($title,0,97)."..";
		if ($searchExpr) {
			$time=time();
			$killtime=$time+60*60*3; # 3 hours to live
			$SearchID=DBGetID("doc.Search");

			$timestart=getmicrotime();
			DBExec ("DELETE FROM doc_SearchTags WHERE `KillTime`<$time");
			$s="INSERT INTO doc_SearchTags (`SearchID`,`DocumentID`,`KillTime`,`DocClassID`)
			  SELECT $SearchID,`$IDField`,$killtime,$DocClassID
			  FROM $DocTable WHERE $searchExpr";
#				print $s;
			DBExec ($s);
			$timelen=round(getmicrotime()-$timestart,4);
			
			$qc=DBQuery("SELECT COUNT(*) AS DocCount FROM doc_SearchTags WHERE SearchID=$SearchID");
			$DocCount=$qc->Top->DocCount;
			if ($DocCount) {
				DBInsert (array(Table=>"doc_Searches",Values=>array(
					SearchID=>$SearchID,
				  SessionKey=>$_SESSION->SessionKey,
				  UserID=>intval($_USER->UserID),
				  QueryTime=>$time,
				  KillTime=>$killtime, # 15 minutes
				  QueryParameters=>serialize($QueryParameters),
				  DocCount=>$DocCount,
				  Title=>$title
				  ))); 
				  
				global $_SESSION;
				$_SESSION->doc_Search['SearchID']=$SearchID;
				if ($SubmitType!='inframe') {
					if (!$TargetURL) $TargetURL=$_SERVER['HTTP_REFERER'];
					header("Location: $TargetURL"); exit;
					print "Выполняется поиск<br><a href='$TargetURL'>Нажмите здесь</a>";
					exit;
				}
		    print "<table cellpadding='10'><tr><td><h1>Результаты поиска</h1><h5>$Caption</h5>";
				print "<table>
				
			  <tr><td align='right'><b>Ключевые значения поиска:</b></td><td>$title</td></tr>
			  <tr><td align='right'><b>Время выполнения поиска:</b></td><td>$timelen сек</td></tr>
			  <tr><td align='right'><b>Найдено документов:</b></td><td><h3>$DocCount</h3></td></tr>
        <tr><td align='right'></td><td><a href='$TargetURL' target='_top'>Открыть результаты</a></td></tr></table>";
			
			  include_once ("inc.DocumentForm.php");
			  $form=new DocumentForm();
			  $r=$form->Load(array(DocClassID=>$DocClassID,FormType=>"list.preview"));
			  if ($r['Error']) return $r;
			  print "<hr><h3>".langstr_get($form->qform->Top->Caption)."</h3>";
			  $form->LoadDocumentsBySearchResult(array(SearchID=>$SearchID));
			  $form->DisplayList(array());
				if ($result['Error']) return $result;
			} else {
				print "<h3>Поиск не дал результатов</h3>";
			}
			
		} else {
			print "Вы ничего не указали для поиска";
		}
  
	*/


	}
	
	function ShowWait($args) {
		print "<table width='100%' height='100%'><tr><td align='center'><h3>Пожалуйста подождите</h3>Идет поиск...</td></tr></table>";
	}
	
	function DisplayItem($args) {
		
	}
}
?>