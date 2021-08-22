<?
class doc_IUpdating
{
	var $CopyrightText="(c)2007 PHP Systems builder. Documents";
	var $CopyrightURL="http://www.phpsb.com/doc";
	var $ComponentVersion="1.0";
	var $RoleAccess=array(DocumentUpdater=>"Panel");

	
	var $logfile;
	var $errfile;
	var $qf;
	var $CSVFieldNames;
	
	function Panel($args) {
	  extract(param_extract(array(
	    PageNo=>'int=1',RowsPerPage=>'int=20',
	  ),$args));
	    		
		
	  global $cfg,$_THEME;
   	$ts=$_THEME['TableStyles'][0];
   	list($ts['te'],$ts['ce'])=get_css_pair($ts['Even'],"td");
   	list($ts['to'],$ts['co'])=get_css_pair($ts['Odd'],"td");
   	list($ts['th'],$ts['ch'])=get_css_pair($ts['Top'],"th");
   	list($ts['tw'],$ts['cw'])=get_css_pair($ts['Warning'],"td.bgup");

   	$TargetPath=$cfg['FilesPath'].'/doc/updates';
   	$TargetURL=$cfg['FilesURL'].'/doc/updates';
		$qcfgs=DBQuery ("SELECT * FROM doc_UpdateConfigs ORDER BY OrderNo","UpdateCfgID");
		if (!$qcfgs) {
			return array(Message=>"Не сконфигурирован ни один метод обновления документов");
		}
		foreach ($qcfgs->Rows as $UpdateCfgID=>$c) {
			if (file_exists("$TargetPath/$c->FileToUpload")) {
				$qcfgs->Rows[$UpdateCfgID]->Info=format_date("shortdate hh:mm",filemtime("$TargetPath/$c->FileToUpload"));
				$qcfgs->Rows[$UpdateCfgID]->Size=format_bytes(filesize("$TargetPath/$c->FileToUpload"));
				$qcfgs->Rows[$UpdateCfgID]->Actions="<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("doc.IUpdating.InitUpdater.b",array(UpdateCfgID=>$UpdateCfgID))."\",w:750,h:500,reloadOnOk:1});'>Проверить</a>";
				$qcfgs->Rows[$UpdateCfgID]->Delete="<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("doc.IUpdating.Clear.b",array(UpdateCfgID=>$UpdateCfgID))."\",reloadOnOk:1});'>Удалить</a>";
			} else $qcfgs->Rows[$UpdateCfgID]->Info="<span class='notice'>файл отсутствует</span>";
			$qcfgs->Rows[$UpdateCfgID]->ErrInfo="";
			if (file_exists("$TargetPath/$c->FileToUpload.errors.html")) {
				$qcfgs->Rows[$UpdateCfgID]->ErrInfo="<a href='javascript:;' onClick='W.openModal({url:\"$TargetURL/$c->FileToUpload.errors.html?r=".rand()."\",w:750,h:500});'>[Ошибки]</a>";
				
			} 
			if (file_exists("$TargetPath/$c->FileToUpload.log.html")) {
				if ($qcfgs->Rows[$UpdateCfgID]->ErrInfo) $qcfgs->Rows[$UpdateCfgID]->ErrInfo.="&nbsp;&nbsp;";
				$qcfgs->Rows[$UpdateCfgID]->ErrInfo.="<a href='#' onClick='W.openModal({url:\"$TargetURL/$c->FileToUpload.log.html?r=".rand()."\",w:500,h:500});'>[Обновления]</a>";
				
			}
		}

		$q=DBQuery("SELECT FondID,COUNT(ID) FROM gtrf_Data GROUP BY FondID","FondID");
		if ($q) $q->Dump();
		
	  $_ENV->PrintTable($qcfgs,array(
	    Action=>ActionURL("doc.IUpdating.InitUpdater.bm"),
	    Fields=>array(
	      Caption=>"Название конфига",
	      LastUpdate=>"Дата обновления базы",
	      Info=>"Дата файла",
	      Size=>"Размер",
	      Actions=>"Действия",
	      ErrInfo=>"Результат",
	      Delete=>"Удалить файл"
	      ),
	    ShowCheckers=>1,
	    Buttons=>array(array(FormAction=>'check',Caption=>'Проверить выбранные'),array(FormAction=>'update',Caption=>'Обновить игнорир ошибки')),
	    ColAligns=>array(Update=>'center',Size=>'center',Actions=>'center'),
	    HideSubmit=>1,
	    FieldHooks=>array(Caption=>"tab_Caption",Update=>"tab_Update"),
	    FieldTypes=>array(LastUpdate=>'datetime'),
	    ThisObject=>&$this));
	    
		$_ENV->OpenForm(array(
			Modal=>1,
			Name=>'uploadform',
		  Enctype=>"multipart/form-data",
		  Width=>'400',
		  Action=>ActionURL("doc.IUpdating.UploadFile.b")
		));
		print "<h2>Обновление вручную</h2><p>Укажите источник откуда следует скачать файл обновления
		 или укажите файл на своем компьютере (кнопка Обзор), а затем нажмите Загрузить<p><p>Максимальный размер файла:".get_post_max_size()."</p>";
		
		if (function_exists("zip_open")) {
			print "Вы можете использовать .zip-упаковку для уменьшения размера файлов. В одном zip-файле может содержаться несколько файлов обновлений";
		} else {print "<font color='red'>На хостинге не установлен или не включен модуль расширения для поддержки ZIP-файлов в PHP</font>";}
		$_ENV->PutFormField(array(Type=>'file',Name=>'Upfile',Caption=>"Ваш файл обновления",Required=>1));
		$_ENV->CloseForm();
		
		$dh=opendir($TargetPath);
		while (($file = readdir($dh)) !== false) {
			if (preg_match('/\.zip$/',$file)) {
				$s.="<a href='".ActionURL("doc.IUpdating.ExtractZip.bm",array(file=>$file))."'>Распаковать '".$file."'</a><br/>";
				
			}
    }
    closedir($dh);
    if ($s) print "<table><tr><td>$s</td></tr></table>";
    $_ENV->PutButton(array(Kind=>'delete',Href=>ActionURL("doc.IUpdating.CleanupDatabase.bm"),Caption=>'Очистить базу'));
	}

	function tab_Update(&$ConfigID,&$cfg,$fname,$args) {
		$v=$cfg->Update;
		if (!$v) print "-";
	}
	function tab_Caption(&$ConfigID,&$cfg,$fname,$args) {
		print "<b>".langstr_get($cfg->Caption)."</b>".(($cfg->FileToUpload)?"<br>$cfg->FileToUpload":"");
	}

	function tab_Fields(&$ConfigID,&$row,$fname,$args)
	{
	print $row->$fname;	
	}
	
	function UploadFile($args) {
		global $cfg;
	  $dirmode=$cfg['Resources']['files'][1]; if (!$dirmode) $dirmode=0777;
	  $filemode=$cfg['Resources']['files'][2];if (!$filemode)$filemode=0777;
		
		$Upfile_name=$_FILES['Upfile']['name'];
		$errcode=$_FILES['Upfile']['error'];
		if ($errcode)
		  {
		  if (($errcode==1)||($errcode==2))
		    {
		    return array(Error=>"Слишком большой размер файла. Используйте загрузку через FTP",Details=>"Filename: $Upfile_name, Errcode: $errcode");
		    }
		  }
		
		$Upfile=$_FILES['Upfile']['tmp_name'];  if ($Img_file=='none') $Img_file=false;
		$Upfile_size=$_FILES['Upfile']['size'];
		$Upfile_mimetype=$_FILES['Upfile']['type'];
		$TargetPath=$cfg['FilesPath'].'/doc/updates';
		if (!is_dir ($TargetPath)) mkdir_recursive($TargetPath,$dirmode);
		chmod($Upfile,$filemode);
		$Errors=false;
		if ($Upfile_mimetype=="application/x-zip-compressed") {
			$r=$this->_extract_zip($Upfile);
			return $r;
		} elseif ($Upfile_name) {
			$qcfg=DBQuery("SELECT FileToUpload FROM doc_UpdateConfigs WHERE FileToUpload='$Upfile_name'");
			if ($qcfg) {
				move_uploaded_file($Upfile,$TargetPath.'/'.$Upfile_name);
				chmod($TargetPath.'/'.$Upfile_name,$filemode);
				return array(ModalResult=>true);
			} else {
				return array(ButtonClose=>1,Message=>"Файл не является зарегистрированным именем файла обновления. Пожалуйста проверьте название файла или выполните конфигурацию для него.",Details=>$Upfile_name." ($Upfile_mimetype)");
			}
		} else {
			return array(ButtonClose=>1,Message=>"Файл не загружен. Код ошибки: $errcode");
		}
	}
	function ExtractZip($args) {
	  extract(param_extract(array(
	    file=>'*string',
	  ),$args));
	  global $cfg;
		$TargetPath=$cfg['FilesPath'].'/doc/updates';
		$TargetFile=$TargetPath."/".basename($file);
		if (!is_file($TargetFile)) {
			return array (Error=>"Файл '$TargetFile' не является файлом");
		}
		$r=$this->_extract_zip($TargetFile);
		return $r;
	}
	
	function _extract_zip ($Upfile) {
		global $cfg;
		$TargetPath=$cfg['FilesPath'].'/doc/updates';
		$Errors="";
		$zip=zip_open($Upfile);
		if ($zip) {
			$qcfg=DBQuery("SELECT FileToUpload FROM doc_UpdateConfigs WHERE FileToUpload IS NOT NULL","FileToUpload");
	    while ($zip_entry = zip_read($zip)) {
					$zipfilename=zip_entry_name($zip_entry);
					if ($qcfg->Rows[$zipfilename]) {
		        if (zip_entry_open($zip, $zip_entry, "r")) {
	            $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
	            zip_entry_close($zip_entry);
	            $fp=fopen($TargetPath.'/'.$zipfilename,"wb");
	            fputs($fp,$buf);
	            fclose ($fp);
		        } else {
							$Errors.="Ошибка распаковке [$zipfilename]<br/>";
		        }
					} else {
						$Errors.="Файл не является зарегистрированным именем обновления [$zipfilename]<br/>";
					}
        }
		    zip_close($zip);
	    }
	    
	  if ($Errors) {
	  	return array(Error=>"Обнаружена ошибка при распаковке",Details=>$Errors);
	  } else {
	  	return array(Message=>"Файл распакован",ButtonOk=>1);
	  }
		
	}

	function InitUpdater($args) {
	  extract(param_extract(array(
	    UpdateCfgID=>'int',
	    action=>'string=check',
	    check=>'int_checkboxes',
	  ),$args));
		
	  global $cfg;
	  
		$TargetPath=$cfg['FilesPath'].'/doc/updates';
	  if (file_exists("$TargetPath/UpdateProgress.txt")) {
	  	print "<h1>Внимание!</h1><font color='red'>В данный момент производится обновление.<br/>
	  	Если вы уверены в том, что процесс обновления следует прекратить, нажмите<br/> 
	  	<a href='".ActionURL("doc.IUpdating.StopUpdater.bm")."'>Удалить процесс обновления</a></font>";
	  	return;
	  }
	  print "<center>";
	  if ($action=='check') print "<h1>Анализ правильности CSV файла</h1>";
	  elseif  ($action=='update') print "<h1>Производится обновление</h1>";
	  
	  if ($UpdateCfgID) $ids=array($UpdateCfgID); else $ids=array_keys($check);
	  $progressdata=array(
	  	UpdatingCfgIDs=>$ids,
	  	Mode=>$action,
	  	CurrentIndex=>0,
	  	CurrentFileOffset=>0,
	  	RowNo=>0,
	  	errcount=>0,
	  );
	  
	  $FileName="$TargetPath/UpdateProgress.txt";
	  $fp=fopen ($FileName,"wb");
	  fputs($fp,serialize($progressdata));
	  fclose ($fp);
	  
	  $_ENV->PutProgress("p1","Процесс обновления");
    print "<br>
      <div id='framecontainer' style='displ ay:none; text-align:center; width:100%'>
      <iframe id='f1' width='450' height='250' src='".ActionURL("doc.IUpdating.Iteration.b")."'></iframe></div>
      <script>Progress_Start('p1');</script>";
	
		
	}
	function StopUpdater($args) {
	  global $cfg;
		$TargetPath=$cfg['FilesPath'].'/doc/updates';
	  if (file_exists("$TargetPath/UpdateProgress.txt")) {
	  	unlink("$TargetPath/UpdateProgress.txt");
	  	print "Процесс остановлен";
	  } else print "Файл процесса отсутствует";
	  print "<br/><a href='".ActionURL("doc.IUpdating.Panel.bm")."'>Перейти к списку файлов обновления</a>";
		
	}
	
	function Iteration($args) {
/*	  extract(param_extract(array(
	    UpdateCfgID=>'int',
	    DoUpdate=>'int'
	  ),$args));*/
	  
		global $cfg,$_USER;
	  $timelen=ini_get('max_execution_time')-3;
	  if ($timelen>5) $timelen=5;
	  $END_TIME=time()+$timelen;

		
		$TargetPath=$cfg['FilesPath'].'/doc/updates';
		$ProgressFileName="$TargetPath/UpdateProgress.txt";
	  if (file_exists($ProgressFileName)) {
	  	$fprogress=fopen($ProgressFileName,"rb");
	  	$s=fread($fprogress,4000);
	  	fclose ($fprogress);
			$progressdata=unserialize($s);
	  	$fprogress=fopen($ProgressFileName,"wb");
	  } else {
	  	return array(Warning=>"Процесс обновления не инициализирован. Запустите его сначала");
	  }
		$UpdateCfgID=$progressdata['UpdatingCfgIDs'][$progressdata['CurrentIndex']];
		print "<script>function go() 
  	  {location.href='".ActionURL("doc.IUpdating.Iteration.b")."';
  	  }
//  	  window.setTimeout('go()',2000);
	    Progress_Start('p1');
	    W.setErrorHandler(onError);
	  </script>";
		
	  $filemode=$cfg['Resources']['files'][2];if (!$filemode)$filemode=0777;

	  $qcfg=DBQuery("SELECT * FROM doc_UpdateConfigs WHERE UpdateCfgID=$UpdateCfgID");
	  if (!$qcfg) {
	  	return array(Error=>"Конфигурация не найдена",Details=>$UpdateCfgID);
	  }
	  extract(param_extract(array(
	  	FileToUpload=>'string',
	  	DocClassID=>'string',
	  	CSVFieldNames=>'string',
	  	UpdatingSegment=>'string',
	  	Encoding=>'string',
	  	KeyFieldName=>'string',
	  	Separator=>'string=,',
	  	Enclosure=>'string="',
	  	),$qcfg->Top));

	  $CSVFieldNames=explode(",",$CSVFieldNames);
	  $this->CSVFieldNames=&$CSVFieldNames; # for logError
	  
	  $qc=DBQuery("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
	  if (!$qc) {
	  	return array(Error=>"Ошибка в таблице конфигурации обновления",
	  	  Details=>"Ошибка конфигурации $FileToUpload. Для конфигурации назначен неверный класс [$DocClassID]");
	  }
	  extract(param_extract(array(
	  	DocTable=>'string',
	  	UpdateTimeField=>'string',
	  	UserIDField=>'string',
	  	ClassName=>'string',
	  	IDField=>'string',
	  	AutoIncMethod=>'int'
	  	),$qc->Top));
	  
	  $this->qf=DBQuery("SELECT Caption,FieldType,FieldName FROM doc_Fields WHERE DocClassID=$DocClassID","FieldName");
	  if (!$this->qf) {
	  	return array(Error=>"Ошибка в таблице конфигурации обновления",
	  	  Details=>"Ошибка конфигурации $FileToUpload. Для класса [$DocClassID] не найдено ни одного поля");
	  }

  	$SegmentWhereStr="";
  	$UpdatingSegmentValues=false;
	  if ($UpdatingSegment) {
	  	$tmp1=explode(",",$UpdatingSegment);
	  	$first=true;
	  	foreach($tmp1 as $tmp2){
	  		$p=explode ("=",$tmp2,2);
	  		$UpdatingSegmentValues[$p[0]]=$p[1];
	  		$v="$p[0]='".DBEscape($p[1])."'";
	  	  $SegmentWhereStr.=(($first)?"":" AND ").$v;
	  	  $first=false;
	  	} if ($SegmentWhereStr) $SegmentWhereStr=" ($SegmentWhereStr) ";
	  }
	  
		if (!$KeyFieldName) {
			return array(Error=>"Ошибка в описании класса $ClassName",
			  Details=>"Отсутствует ключевое поле, по которому будут идентифицироваться записи из обновления");
		}

		$TargetURL=$cfg['FilesURL'].'/doc/updates';		
		
		$this->errfile="$TargetPath/$FileToUpload.errors.html";
		$this->logfile="$TargetPath/$FileToUpload.log.html";
		if ($progressdata['CurrentFileOffset']==0) {
			if (file_exists($this->errfile)) unlink($this->errfile);
			if (file_exists($this->logfile)) unlink($this->logfile);
		}
		$this->stderr=false;

		$infile="$TargetPath/$FileToUpload";
		if (!is_readable($infile)) {
			return array(Error=>"Файл недоступен для чтения",Details=>$infile);
		}
	  
	  $s="SELECT $KeyFieldName,$IDField FROM $DocTable";
	  if ($SegmentWhereStr) $s.=" WHERE $SegmentWhereStr";
	  $qids=&DBQuery($s,$KeyFieldName);
	  if (!$qids) {
	  	global $Database;
	  	if ($Database->Error) return array(Error=>"Обновление невозможно из-за ошибки чтения существующих записей",Details=>$Database->Error);
	  }

		$dberrcount=0;
		$filesize=filesize($infile);
		$fcsv=fopen($infile,"rb");
		if ($progressdata['CurrentFileOffset']) {
			fseek ($fcsv,$progressdata['CurrentFileOffset'],SEEK_SET);
		}
		$UpdateCount=$InsertCount=0;
		$KeysDetected=false;
		$UpdateTimeValue=time();

		if ($progressdata['Mode']=='update') {
			$ID=0;
			switch ($AutoIncMethod) {
#case 0: case 2: # $ID=DBGetID($ClassName,"","",count($Inserts)); break;				
				case 1: $qm=DBQuery("SELECT MAX($IDField) AS MaxID FROM $DocTable"); $ID=intval($qm->Top->MaxID); break;
				case 3: return array(Error=>"Prototype autoinc method is under development");
			}
			
		}
		
		$ValueReplaceFieldsList=$ValueInsertFieldsList="";
		$Updates=$Inserts="";
		fseek($fcsv,$progressdata['CurrentFileOffset']);
		
		$Interrupted=false;
		while (($data = fgetcsv($fcsv, 3000, $Separator,$Enclosure)) !== FALSE) {
			$progressdata['RowNo']++; 
			$PassRow=false;
			foreach ($data as $i=>$v) {
				$v=str_replace("\x0b","\n",$v);
				if ($Encoding) $v=mb_convert_encoding($v,"UTF-8",$Encoding);
				$data[$i]=$v;
			}

			if (count($data)!=count($CSVFieldNames)) {
				$this->_logError("Количество полей:".count($data).", а должно быть: ".count($CSVFieldNames),$progressdata['RowNo'],$ColumnData,$ColumnNo);
				$progressdata['errcount']++; 
				$PassRow=true;
			}
			$ReplaceData=false;
			$s="";
			$KeyFieldColumnNo=0;
			foreach ($CSVFieldNames as $ColumnNo=>$DocFieldName) {
				$DocField=$this->qf->Rows[$DocFieldName];
				if (!$DocField) {
					return array(Error=>"Ошибка в таблице конфигурации обновления",Details=>"Ошибка конфигурации $FileToUpload. В классе ".langstr_get($qc->Top->Caption)." должно быть поле с идентификатором [$DocFieldName], но в определении класса документа его нет. Возможно оно был удалено.");
				}
				$ColumnData=$data[$ColumnNo];
				if (($DocField->Required)&&($ColumnData=="")) {
					$this->_logError("Пустое значение в требуемом поле ".langstr_get($DocField->Caption),$progressdata['RowNo'],$data,$ColumnNo);
				  $progressdata['errcount']++;
				}
				switch($DocField->Type) {
					case 'int': 
						$d=intval($ColumnData);
						if ($d!=$ColumnData) {
						$this->_logError("Поле должно содержать целочисленное значение ".langstr_get($DocField->Caption),$progressdata['RowNo'],$data,$ColumnNo);
					  $progressdata['errcount']++; $PassRow=true;
					  break;
						}
					$ColumnData=$d;
					break;
				default: $ColumnData="$ColumnData";
				}
				$ReplaceData[$DocField->FieldName]=$ColumnData;
				if ($DocFieldName==$KeyFieldName) {$KeyFieldValue=$ColumnData; $KeyFieldColumnNo=$ColumnNo;}
			} #foreach
			
			if (!$KeyFieldValue) {
				$this->_logError("Отсутствует значение в ключевом поле $KeyFieldName",$progressdata['RowNo'],$data,$KeyFieldColumnNo);
			  $progressdata['errcount']++; $PassRow=true;
			} else {
				if (($KeysDetected)&&(isset($KeysDetected[$KeyFieldValue]))) {
					$this->_logError("Повтор значения ключевого поля $KeyFieldName=$KeyFieldValue, которое уже было в строке <b>".$KeysDetected[$KeyFieldValue]."</b>",$progressdata['RowNo'],$data,$KeyFieldColumnNo);
				  $progressdata['errcount']++; $PassRow=true;
				}
				$KeysDetected[$KeyFieldValue]=$progressdata['RowNo'];
			}
			
			if ($progressdata['errcount']>100) {
				$this->_logError("Слишком много ошибок. Чтение файла остановлено");
				print "<h3>Обработка файла прервана из-за большого количества ошибок</h3>";
#				$progressdata['CurrentIndex']++;
				break;	
			}

			if (!$PassRow) {
				$isExists=isset($qids->Rows[$KeyFieldValue]);
				if ($isExists) $UpdateCount++; else $InsertCount++;
				if ($progressdata['Mode']=='update') {
					if ($UpdateTimeField) $ReplaceData[$UpdateTimeField]=$UpdateTimeValue;
					if ($UserIDField) $ReplaceData[$UserIDField]=$_USER->UserID;
					if ($isExists) {
							$s=""; foreach ($ReplaceData as $k=>$v) {
								if ($k!=$KeyFieldName) $s.=(empty($s)?"":",")."$k='".DBEscape($v)."'";
							}
							$s="UPDATE LOW_PRIORITY $DocTable SET $s WHERE $KeyFieldName=$KeyFieldValue";
							foreach ($UpdatingSegmentValues as $k=>$v) {$s.=" AND $k='$v'";}
							if (!DBExec ($s)) {
								$dberrcount++; if ($dberrcount>20) return array(Error=>"Слишком много ошибок в базе данных");
							}
					} else {
						if (is_array($UpdatingSegmentValues)) {$ReplaceData+=$UpdatingSegmentValues;}
						if ($AutoIncMethod==1) {$ReplaceData[$IDField]=$ID; $ID++;}
						if ((!$ValueInsertFieldsList) && (!$isExists)) $ValueInsertFieldsList=implode (",",array_keys($ReplaceData));
						$s=""; foreach ($ReplaceData as $k=>$v) {$s.=(empty($s)?"":",")."'".DBEscape($v)."'";}
						$Inserts.=(empty($Inserts)?"":",")."($s)";
						if (strlen($Inserts)>8000) {
							$s="INSERT DELAYED INTO $DocTable ($ValueInsertFieldsList) VALUES $Inserts";
							if (!DBExec ($s)) {
								$dberrcount++; if ($dberrcount>20) return array(Error=>"Слишком много ошибок в базе данных");
							}
							$Inserts="";
						}
					}
				}
			}
			if (time()>$END_TIME) {$Interrupted=true; break; } # BREAK THE LOOP IF OVERTIME
		}
		$progressdata['CurrentFileOffset']=ftell($fcsv);
		fclose ($fcsv);

		
		if ($progressdata['Mode']=='update') {
			if (!empty($Inserts)) {
				if (!DBExec ("INSERT INTO $DocTable ($ValueInsertFieldsList) VALUES $Inserts")) {
					$dberrcount++; if ($dberrcount>20) return array(Error=>"Слишком много ошибок в базе данных");
				}
			}
		}
	
		if ($progressdata['Mode']=='update') {
			print "<center><h2>Результат обновления $FileToUpload</h2>";
			DBUpdate(array(Table=>'doc_UpdateConfigs',
				Keys=>array(UpdateCfgID=>$UpdateCfgID),
				Values=>array(LastUpdate=>time())));
		}
		if ($progressdata['Mode']=='check') {print "<center><h1>Статистика анализа файла $FileToUpload</h1>";}
		
		$result="<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
			<style>td{font-family:arial,verdana,sans; font-size:11px;}</style>
			<table>
		  <tr><td align='right'><b>Количество обновляемых записей:</b></td><td>$UpdateCount</td></tr>
			<tr><td align='right'><b>Количество новых записей:</b></td><td>$InsertCount</td></tr>
			<tr><td align='right'><b>Строка:</b></td><td>$progressdata[RowNo]</td></tr>";
		if ($progressdata['errcount']) {
			$result.="<tr><td align='right'><b><font color='red'>Количество ошибок:</font></b></td><td><font color='red'>".intval($progressdata['errcount'])."</font></td></tr>";
		}
		if ($Interrupted) {
			$result.="<tr><td align='right'><b>Текущая позиция процесса:</b></td><td>$progressdata[CurrentFileOffset]/$filesize</td></tr>
			</table>";
			$position=($progressdata['CurrentFileOffset']/$filesize)*100;
		} else {
			$position=100;
			#$result.="<tr><td align='right'><b>Данный процесс закончен</b><br>";
			$progressdata['CurrentIndex']++;
			if ($progressdata['CurrentIndex']>=count($progressdata['UpdatingCfgIDs'])) {
				print "<h2>Больше файлов для анализа нет</h2>";
				$progressdata=false;
			} else {
				print "<h2>Анализ продолжается</h2>Следующий код конфигурации обновления: "
				.$progressdata['UpdatingCfgIDs'][$progressdata['CurrentIndex']];
			  $progressdata['errcount']=0;
			  $progressdata['CurrentFileOffset']=0;
			  $progressdata['RowNo']=0;
			}
			
			$result.="</td></tr></table>";
			$fp=fopen ($this->logfile,"wb");
			fputs($fp,$result);
			fclose ($fp);
		}
		
		print $result;
		if ($progressdata) {
		  fputs($fprogress,serialize($progressdata));
		  fclose ($fprogress);
		  $chref=ActionURL('doc.IUpdating.Iteration.b');
			print "<br><a href='$chref'>[...]</a>";
      print "<script>window.parent.Progress_NewPos('p1',".$position.",'".$FileToUpload."'); 
      window.setTimeout('go();',1000); 
      function go() {location.href='$chref';}</script>";
		} else {
      print "<script>window.parent.Progress_NewPos('p1',".$position.",'".$FileToUpload."'); window.parent.Progress_Pause('p1');</script>";
			fclose ($fprogress);
			unlink ($ProgressFileName);
		}
		
	}
	
	function _logError($ErrorText,$RowNo=false,$data=false,$ErrorColumnNo=false) {
		if (!$this->stderr) {
			$justcreated=false;
			if (!file_exists($this->errfile)) {$justcreated=true;}
			$this->stderr=fopen ($this->errfile,"a+");
			if ($justcreated) {
				foreach ($this->CSVFieldNames as $ColumnNo=>$DocFieldName) {
					$DocField=$this->qf->Rows[$DocFieldName];
					$s.="<td>$DocField->FieldName</td>";
				}
				fputs($this->stderr,"<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
				<style>td{font-family:arial,verdana,sans; font-size:11px;}</style>
				<h2>Ошибки '$this->errfile'</h2><table border='1' cellspacing='0'><tr><td>Строка</td>$s</tr>");	
			}
		}
		$s="<tr>".(($RowNo)?"<td rowspan='2'><b>$RowNo</b>":"<td>")."</td><td colspan='20'><font color='red'>$ErrorText</font></td></tr>";
		if (is_array($data)) {
			foreach ($data as $i=>$v) {
				if (!$v) $v="<font color='#808080'>[пусто]</font>";
				if (mb_strlen($v,"UTF-8")>50) $v=mb_substr($v,0,47,"UTF-8")."...";
				if (($ErrorColumnNo!==false)&&($i==$ErrorColumnNo)) $s2.="<td bgcolor='#ff8080'>$v</td>"; else $s2.="<td>$v</td>";
			}
			if ($s2) $s.="<tr>$s2</tr>\n";
		}
		fputs ($this->stderr,$s);
	}
	
	function ShowErrors($args) {
	  extract(param_extract(array(
	    UpdateCfgID=>'int',
	  ),$args));
	  global $cfg;
	  $qcfg=DBQuery("SELECT * FROM doc_UpdateConfigs WHERE UpdateCfgID=$UpdateCfgID");
	  if (!$qcfg) {
	  	return array(Error=>"Конфигурация не найдена",Details=>$UpdateCfgID);
	  }
	  $FileToUpload=$qcfg->Top->FileToUpload;
		$TargetPath=$cfg['FilesPath'].'/doc/updates';
		$errfile="$TargetPath/$FileToUpload.errors.html";
		print "<h1>Содержимое '$FileToUpload.errors.html'</h1><table><tr><td>";
		if (file_exists($errfile)) {
			print implode ("\n",file($errfile));
		} else {
			print "Файл '$errfile' не найден";
		}
  $_ENV->PutButton(array(Action=>'ok'));
	
	}
	
	function ShowLog($args) {
	  extract(param_extract(array(
	    UpdateCfgID=>'int',
	  ),$args));
	  global $cfg;
	  $qcfg=DBQuery("SELECT * FROM doc_UpdateConfigs WHERE UpdateCfgID=$UpdateCfgID");
	  if (!$qcfg) {
	  	return array(Error=>"Конфигурация не найдена",Details=>$UpdateCfgID);
	  }
	  $FileToUpload=$qcfg->Top->FileToUpload;
		$TargetPath=$cfg['FilesPath'].'/doc/updates';
		$file="$TargetPath/$FileToUpload.log.html";
		print "<h1>Содержимое '$FileToUpload.log.html'</h1><table><tr><td>";
		if (file_exists($file)) {
			print implode ("\n",file($file));
		} else {
			print "Файл '$file' не найден";
		}
  $_ENV->PutButton(array(Action=>'ok'));
	}
	

	function Clear($args) {
	  extract(param_extract(array(
	    UpdateCfgID=>'*int',
	  ),$args));
	  global $cfg;
	  $qcfg=DBQuery("SELECT * FROM doc_UpdateConfigs WHERE UpdateCfgID=$UpdateCfgID");
	  if (!$qcfg) {
	  	return array(Error=>"Конфигурация не найдена",Details=>$UpdateCfgID);
	  }
	  $FileToUpload=$qcfg->Top->FileToUpload;
		$TargetPath=$cfg['FilesPath'].'/doc/updates';
		$file="$TargetPath/$FileToUpload.errors.html";
		if (file_exists($file)) unlink($file);
		$file="$TargetPath/$FileToUpload";
		if (file_exists($file)) unlink($file);
		$file="$TargetPath/$FileToUpload.log.html";
		if (file_exists($file)) unlink($file);
		return array(ModalResult=>true);
		
	}
	
	function CleanupDatabase($args) {
		DBExec ("DELETE FROM gtrf_Data");
	}
}
?>