<?
class backend_IBackupDbase
{
var $CopyrightText="(c)2007 PHP Systems builder. Backend";
var $CopyrightURL="http://www.phpsb.com/backend";
var $RoleAccess=array(BackupManager=>"Backup,DoBackup,ViewBackups,ViewBackupContent,Restore,Remove");
var $unzipsize;


# t=4 - about
# t=3 - SQL table

function Restore($args)
  {
  return;
  extract(param_extract(array(
    file=>'string',
    action=>'string',
    check=>'array',
    ),$args));

  $_ =&$GLOBALS['_STRINGS']['backend'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;


  print "<br/><input type='button' onClick='location.href=\"".ActionURL("backend.IBackupDbase.ViewBackups.bm")."\"' class='button' value='$__[CAPTION_BACK]'>";
  $file=$cfg['BackupsPath'].'/'.basename ($file);
  if (!$file)
    {
    return array(Error=>"Backup file not found",IntruderAlert=>100,Details=>$file);
    }
  $index=$this->read_index($file);
  $description="";
  if (!$index)
    {
    return array(Error=>"The backup file has no index",IntruderAlert=>100,Details=>$file);
    }


  if ($action=='restore') {$s="Restore database backup disabled. Use setup.php"; return;}
  if ($action=='preview') $s="Preview backup [$file]";
  print "<h2>$s</h2>";
  $gz=gzopen($file,'r');
  if (!$gz)
    {
    return array(Error=>'Cannot open the gzip file',Details=>$file);
    }

  foreach ($index as $i=>$d)
    {
    print "<table width='100%'>";
    switch ($d['t'])
      {
      case 4: # description
        $description=$d['text'];
        $backupdate=$d['date'];
        break;
      case 3: # sql query
        if (!$check[$d['n']]) {  continue;  }

        gzseek($gz, $d['o']);
        $data=explode ("\x00",gzread($gz, $d['s']));
        $createtable=$data[0];
        if ($action=='restore')
          {
          print $createtable;
          $createtable="";
          }

        $rowCount=$d[rowcount];

        print "<tr><td><h4>$d[n]</h4></td></tr><tr><td><pre>$createtable</pre><table border='1' cellspacing='0'>";

        $col=0; $row=0; $s=""; $ecount=count($data); $exe="";
        for ($i=1; $i<$ecount; $i++)
          {
          $e=$data[$i];
          if (!$e) {
            if ($action=='preview')
              {
              if (($row<3)||($row>$rowCount-5)) print "<tr>$s</tr>";
                elseif ($row==3) print "<tr><td colspan='20' align='center'>...</td></tr>";
              }
            if ($action=='restore')
              {
              $se="INSERT INTO $d[n] VALUES ($exe)";
              }
            $s=""; $exe="";
            $col=0;
            $row++;
            continue;
            }

          if ($action=='preview')
            {
            if (($row<3)||($row>($rowCount-5))) {
              if (strlen($e)>200)
                {$pe=htmlspecialchars(substr($e,1,190))."/.../";}
              else
                {$pe=htmlspecialchars(substr($e,1));}
              if ($e=='Z') $pe='<i>NULL</i>';
              $s.="<td style='font-size:9px; font-family:Arial,sans'>$pe</td>";
              }
            }

          if ($action=='restore')
            {
            $pe=substr($e,1);
            switch (substr($e,0,1))
              {
              case 'Z': $pe='NULL'; break;
              case 'S': $pe=DBEscape($pe); $pe="'$pe'"; break;
              }
            if ($exe) $exe.=",";
            $exe.=$pe;
            }
          }
        print "</table><b>$_[BACKUP_ROWCOUNT]:</b> $d[rowcount]<br><br></td></tr>";
        break;
      }
    print "</table>";
    }
  }

function Backup ()
  {
  $_=&$GLOBALS['_STRINGS']['backend'];
  global $cfg;
  $dir=$cfg['BackupsPath'];
  if (!is_dir($dir))
    {
    print_warning("Backup directory does not exists",$dir);
    exit;
    }
  else
    {
    print "<a href='".ActionURL("backend.IBackupDbase.ViewBackups.bm")."'>$_[BACKUP_EXISTING_BACKUPS]</a>";
    }

  ?><script>
    function viewSelectedTables()
      {
      var d=(document.getElementById('selected_tables').style.display == 'block')? 'none':'block';
      document.getElementById('selected_tables').style.display=d;
        document.getElementById('a_selected_tables').style.display='none';
      }
    function checkerClickAll(checker)
    { for (var el in tablesForm.elements){if (el.substr(0,6)=='check['){
        tablesForm.elements(el).checked=!checker.checked;
        tablesForm.elements(el).click();}}}</script>
  <?
  $qt=DBQuery("SHOW TABLES");
  if ($qt)
    {
    $maxrow=floor($qt->RowCount/3);
    $f0=$qt->Fields[0];
    $fname=$this->generate_name();
    print "<form name='tablesForm' method='post' action='".ActionURL("backend.IBackupDbase.DoBackup.bm")."'>
      $_[BACKUP_NEW_FILENAME]<br/>
      <input type='text' name='fname' value='$fname' class='inputarea' size='35'>.jsbz<br/>
      $_[BACKUP_DESCRIPTION]<br/>
      <textarea class='inputarea' cols='40' name='description'></textarea><br/><br/>";
      $_ENV->PutButton(array(Action=>'submit',Caption=>"Make backup"));
      print "<table border='0' bgcolor='#f8f8f8'><tr><td>
    <b>$_[BACKUP_CHOOSE_TABLES]</b><br><input onClick='checkerClickAll(this)' type='checkbox' id='ch' name='ch' value='1' checked><label for='ch'>$_[BACKUP_SELECT_ALL_TABLES]</label><br>
    <div id='a_selected_tables'><a href='javascript:viewSelectedTables()'>$_[BACKUP_VIEW_SELECTED_TABLES]</a></div>
    <div id='selected_tables' style='display:none'>
      <table cellpadding='1' cellspacing='5'><tr valign='top'>
    ";

    $rowno=0;
    foreach ($qt->Rows as $i=>$row)
      {
      if (!$rowno) {print "<td background='#d0d0d0'>";}
      $table=$row->$f0;
      print "<input type='checkbox' name='check[$table]' id='check_$table' value='1' align='middle' checked/>
      <label for='check_$table'>$table</label></br>";
      $rowno++;
      if ($rowno>$maxrow)
        {
        print "</td>";
        $rowno=0;
        }
      }
    $dh=opendir($cfg[RootPath]);
    $sother="";
    while (($name = readdir($dh)) !== false)
      {
      if (is_dir($cfg['RootPath'].'/'.$name) && ($name!='.') && ($name!='..'))
        {
        $found=false;
        foreach ($cfg['Resources'] as $rtype=>$v)
          {
          if ($cfg[RootPath].'/'.$name == $v[0])
            {
            $found=true;
            break;
            }
          }
        if (!$found)
          {
          $resource_type="other:$name";
          $sother.="<input type='checkbox' name='resources[$resource_type]' value='1'>".$cfg[RootPath].'/'.$name."<br>";
          }
        }
      }
    closedir($dh);


    include_once($cfg['PHPSB_PATH'].'/inc.adminconfig.php');
    global $admincfg;

    print "</tr></table></div></td></tr>
      <tr><td><br><b>$_[BACKUPRESOURCE_SELECT]</b><br>
        <table cellpadding='3'>
        <tr bgcolor='#f0f0f0' valign='top'><td><input type='checkbox' id='r1' name='resources[files]' value='1' checked></td>
          <td><label for='r1'>$_[BACKUPRESOURCE_FILES]<br>$cfg[FilesPath]</label></td></tr>

        <tr  bgcolor='#f0f0f0' valign='top'><td><input type='checkbox' id='r4' name='resources[themes]' value='1'></td>
          <td><label for='r4'>$_[BACKUPRESOURCE_THEMES_SKINS]<br>
          $cfg[ThemesPath]<br>
          $cfg[SkinsPath]</label></td></tr>

        <tr  bgcolor='#f0f0f0' valign='top'><td><input type='checkbox' id='r6' name='resources[data]' value='1'></td>
          <td><label for='r6'>$_[BACKUPRESOURCE_DATA]<br>
          $cfg[DataPath]</label></td></tr>

        <tr bgcolor='#f0f0f0' valign='top'><td></td>
          <td>$_[BACKUPRESOURCE_OTHER]<br>
          $sother</td></tr>

        <tr><td colspan='2'><br><b>$_[BACKUPRESOURCE_ADVANCED_SELECT]</b></td></tr>
        <tr  bgcolor='#f0f0f0' valign='top'><td><input type='checkbox' id='r5' name='resources[root]' value='1'></td>
          <td><label for='r5'>$_[BACKUPRESOURCE_ROOT_CONFIG]<br>
          $cfg[RootPath]</label></td></tr>

          ";

        if ($admincfg['AllowAdminsToBackupPHPSB']) print "<tr  bgcolor='#f0f0f0' valign='top'><td><input type='checkbox' id='r2' name='resources[phpsb]' value='1'></td>
          <td><label for='r2'>$_[BACKUPRESOURCE_PHPSBSCRIPTS]<br>
          $cfg[PHPSBScriptsPath]</label></td></tr>";

        print "<tr  bgcolor='#f0f0f0' valign='top'><td><input type='checkbox' id='r2' name='resources[scripts]' value='1'></td>
          <td><label for='r2'>$_[BACKUPRESOURCE_SCRIPTS]<br>
          $cfg[ScriptsPath]</label></td></tr>";

         print "</table><br>
         <b>$_[BACKUPRESOURCE_EXCLUDEEXT]</b><br><input size='40' class='inputarea' type='text' name='excludeext' value='fla,exe,cdr,psd,db,swd'>
         ";


    print "</table></form>";
    }

  }

function Remove ($args)
  {
  $_=&$GLOBALS[_STRINGS][backend];
  $__=&$GLOBALS[_STRINGS][_];
  extract(param_extract(array(
    check=>'array',
    ),$args));

  global $cfg;
  if (!$check)
    {
    print "<h2>$_[BACKUP_NO_SELECTION]</h2>";
    }
  else
    {
    print "<h2>$_[BACKUP_REMOVING_TITLE]</h2><table border='1' cellspacing='0' cellpadding='5'>";
    foreach ($check as $file=>$x)
      {
      print "<tr><td>";
      $file=$cfg['BackupsPath']."/".basename($file);
      if (file_exists($file))
        {
        print "$file</td><td>";
        if (unlink($file)) print $_[BACKUP_FILEREMOVED]; else print $_[BACKUP_REMOVE_PROTECTED];
        }
      else {print "$file</td><td>$_[BACKUP_FILENOTFOUND]";}
      print "</td></tr>";
      }
    print "</table>";
    }
  print "<br/><input type='button' onClick='location.href=\"".ActionURL("backend.IBackupDbase.ViewBackups.bm")."\"' class='button' value='$__[CAPTION_BACK]'>";
  }

function ViewBackups ($args)
  {
  $_=&$GLOBALS[_STRINGS][backend];
  $__=&$GLOBALS[_STRINGS][_];
  extract(param_extract(array(
    pageNo=>'int=1'
    ),$args));

  global $cfg;
  $dir=$cfg['BackupsPath'];
  if (!is_dir($dir))
    {
    return array(Error=>"Backup directory not found",Details=>$dir);
    }

  $dh=opendir($dir);
  print "<h2>$_[BACKUP_EXISTING_BACKUPS]</h2>";
  $backuplist=false;
  $zipbytes=0;
  while (($file = readdir($dh)) !== false)
    {
    if ((filetype($dir . '/'. $file)=='file') && (substr($file,-5)=='.jsbz'))
      {
      $zipsize=filesize($dir . '/'. $file);
      $ziptotalsize+=$zipsize;
      $index=$this->read_index($dir . '/'. $file);
      $description="";
      if ($index)
        {
        $d=$index[0];
        if (($d)&&($d['t']==4))
          {
          $description=$d['text'];
          $backupdate=$d['date'];
          }

        $tablecount=0;
        $totalrows=0;
        $totalsize=0;
        for ($i=0;$i<count($index);$i++)
          {
          $d=$index[$i];
          if ($d['t']==3)
            {
            $tablecount++;
            $totalrows+=$d['rowcount'];
            $totalsize+=$d['s'];
            }
          }
        }

      if ($backupdate)
        {
        $backuplist[$backupdate]=
        array(file=>$file,
          description=>$description,
          rows=>$totalrows,
          size=>$totalsize,
          zipsize=>$zipsize,
          tablecount=>$tablecount);
        }
      }
    }
  closedir($dh);

  if ($backuplist)
    {
    krsort($backuplist);
    $rowCount=count($backuplist);
    $rowsPerPage=10;
    $pageCount=ceil($rowCount / $rowsPerPage);
    if ($pageCount>1)
      {
      $s=$__[CAPTION_PAGES];
      for ($i=1;$i<=$pageCount;$i++)
        {
        if ($i==$pageNo) $s.="$i "; else $s.="<a href='".ActionURL("backend.IBackupDbase.ViewBackups.bm",array(pageNo=>$i))."'>$i</a> ";
        }
      print "<table width='100%'><tr><td align='right'>$s</td></tr></table>";
      }
    print "
    <form method='post' action='".ActionURL("backend.IBackupDbase.Remove.b")."'>
      <table cellpadding='4' cellspacing='1'><tr><td></td>
      <th>$_[BACKUP_DATE]</th>
      <th>$_[BACKUP_DESCRIPTION]</th>
      <th>$_[BACKUP_TABLECOUNT]</th>
      <th>$_[BACKUP_ROWCOUNT]</th>
      <th>$_[BACKUP_PACKUNPACK_SIZE]</th></tr>";

    $rowNo=0;
    $pass=$rowsPerPage*($pageNo-1);
    foreach ($backuplist as $backupdate=>$data)
      {
      if ($pass>0) {$pass--; continue;}
      if ($rowNo>$rowsPerPage) break;
      $c=" class='".(($rowNo & 1 )?"ce":"co")."'";
      $backupdate=format_date("<b>day mon year</b><br>hh:mm",$backupdate);
      print "<tr valign='top'>
        <td><input type='checkbox' name='check[".$data[file]."]' value='1'></td>
        <td align='center' $c>$backupdate</td>
        <td $c><a href='".ActionURL("backend.IBackupDbase.ViewBackupContent.bm",array(file=>$data[file]))."'>$data[file]</a><br>$data[description]</td>
        <td $c>$data[tablecount]</td>
        <td $c>$data[rows]</td>
        <td $c><b>".ceil($data['zipsize']/1024)."</b>&nbsp;kB&nbsp;/&nbsp;".ceil($data['size']/1024)."&nbsp;kB</td>
        </tr>";
      $rowNo++;
      }
    print "<tr><td colspan='2'><input type='submit' class='button' value='$__[CAPTION_DELETE]'></td></tr></table>$_[BACKUP_ZIP_TOTAL]:".((ceil($ziptotalsize/1024/102.4))/10)." Mb";
    print "</table></form>";
    }
  }

function generate_name ($fname=false)
  {
  global $cfg;

  if (substr($fname,-5)=='.jsbz')
    {
    $fname=substr($fname,0,-5);
    }
  if (!$fname)
    {
    $d=getdate (time());
    $fname=$cfg['SiteName'];
    if (!$fname) $fname="backup";
    $fname=strftime ($fname."_%Y_%m_%d",time());
    }

  $targetfilename=$cfg['BackupsPath']."/".$fname.'.jsbz';
  if (file_exists($targetfilename))
    {
    for ($i=2;$i<1000;$i++)
      {
      $targetfilename=$cfg['BackupsPath']."/".$fname."($i).jsbz";
      if (!file_exists($targetfilename))
        {
        $fname.="($i)";
        break;
        }
      }
    }
  return $fname;
  }

function read_index($hbzname)
  {
  $f=@fopen ($hbzname,"rb");
  if (!$f)
    {
    print "File could not be opened '$hbzname'";
    return false;
    }
  flush();
  fseek ($f,-100,SEEK_END);
  $s=fread ($f,100);
  $p=false;
  for ($i=100;$i>0;$i--)
    {
    $c=substr($s,$i,1);
    if ($c=='|') {$p=$i;break;}
    }
  if ($p==false)
    {
    print "Bad package file format";
    return false;
    }
  $footer=substr($s,$p+1);
  list($indexstart,$indexsize,$passkey)=explode (":",$footer);

  fseek ($f,$indexstart,SEEK_SET);
  $sindex=gzuncompress (fread ($f,$indexsize));
  fclose ($f);

  $index=unserialize($sindex);
  if (!$index)
    {
    print "Cannot extract hbzip index<br>";
    return false;
    }
  return $index;
  }

function ViewBackupContent($args)
  {
  extract(param_extract(array(
    file=>'string'
    ),$args));
  $_ =&$GLOBALS['_STRINGS']['backend'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;
  ?><script>function checkerClickAll(checker)
    { for (var el in tablesForm.elements){if (el.substr(0,6)=='check['){
        tablesForm.elements(el).checked=!checker.checked;
        tablesForm.elements(el).click();}}}</script>
  <?
  $file=$cfg['BackupsPath'].'/'.basename ($file);
  if (!$file)
    {
    return array(Error=>"Backup file not found",IntruderAlert=>100,Details=>$file);
    }
  $index=$this->read_index($file);
  $description="";
  if (!$index)
    {
    return array(Error=>"The backup file has no index",IntruderAlert=>100,Details=>$file);
    }
  $d=$index[0];
  if (($d)&&($d['t']==4))
    {
    $description=$d['text'];
    $backupdate=$d['date'];
    }
  $backupdate=format_date("day mon year hh:mm",$backupdate);
  print "<a href='".ActionURL("backend.IBackupDbase.ViewBackups.bm")."'>$_[BACKUP_EXISTING_BACKUPS]</a><br><br>
  <h2>$_[BACKUP_FILE]</h2>
  <font color='red'>$file</font>
   $_[BACKUP_CHOOSE_TABLES_TO_RESTORE]
    <table>
      <tr><td align='right'><b>$_[BACKUP_DATE]:</b></td><td>$backupdate</td></tr>
      <tr valign='top'><td align='right'><b>$_[BACKUP_DESCRIPTION]:</b></td><td>$description</td></tr>
    </table>
    <form method='post' name='tablesForm' action='".ActionURL("backend.IBackupDbase.Restore.bm")."'>
    <input type='hidden' name='file' value='$file'>
    <input onClick='checkerClickAll(this)' type='checkbox' id='ch' name='ch' value='1' checked>
      <label for='ch'>$_[BACKUP_SELECT_ALL]</label>
    <table cellpadding='10' cellspacing='2'><tr valign='top'>";
  $rowno=0;
  $maxrow=ceil(count($index)/3);
  for ($i=0;$i<count($index);$i++)
    {
    if (!$rowno) print "<td>";
    $d=$index[$i];
    $name=$d['n'];
    if ($d['t']==3)
      {
      print "<input type='checkbox' name='check[$name]' id='check_$name' value='1' align='middle' checked/>
      <label for='check_$name'>$name</label></br>";
      $rowno++;
      if ($rowno>$maxrow)
        {
        print "</td>";
        $rowno=0;
        }
      }
    }
  print "</tr></table>
    <input type='button' class='button' value='$_[BACKUP_CAPTION_PREVIEW]' onClick='tablesForm.action.value=\"preview\"; tablesForm.submit();'>
    <input type='button' class='button' value='$_[BACKUP_CAPTION_RESTORE]' onClick='tablesForm.action.value=\"restore\"; tablesForm.submit();'>
    <input type='button' onClick='location.href=\"".ActionURL("backend.IBackupDbase.ViewBackups.bm")."\"' class='button' value='$__[CAPTION_CANCEL]'>
    <input type='hidden' name='action'>
    </form><span class='warning'>$_[BACKUP_RESTORE_WARNING]</span>";
  }


function recursive_compress ($rootdirname,$localdir,&$index,&$gz,$resource_type,$drill_subdirs=true,&$excludeext,$level=0)
  {
  if ($level>20)
    {
    print "Dir drilling Overflow. Maximal depth is 20 folders";
    return false;
    }

  $dname=$rootdirname;
  if ($localdir) $dname.='/'.$localdir;
  print "[$resource_type] - $dname<br>";
  if (is_dir($dname))
    {
    if ($dh = opendir($dname))
      {
      $dirs=false;
      while (($file = readdir($dh)) !== false)
        {
        $realname=$dname.'/'.$file;
        if (($file=='.')||($file=='..')) {continue;}
        if (is_dir($realname))
          {
          $dirs[]=$file;
          continue;
          }
        else
          {
          $pathinfo=pathinfo($file);
          $fileext=$pathinfo[extension];
          if ((array_search($fileext,$excludeext)!==false)||($file=='setup.php'))
            {
            continue;
            }

          $src=fopen($realname,"rb");
          $gzbegin=gztell($gz);
          while (!feof($src))
            {
            gzwrite ($gz,fread($src,1024*512));
            }
          fclose ($src);

          $gzsize=gztell($gz)-$gzbegin;
          $this->unzipsize+=$gzsize;
          $index[]=array(t=>2,n=>$file,s=>$gzsize,o=>$gzbegin);
          }
        }

      if (($dirs)&&($drill_subdirs))
        {
        foreach($dirs as $dirfilename)
          {
          $dirin=$localdir;
          if ($dirin) $dirin.='/';
          $dirin.=$dirfilename;
          $index[]=array(t=>1,n=>$dirin,p=>$resource_type);
          $this->recursive_compress ($rootdirname,$dirin,$index,$gz,$resource_type,$drill_subdirs,$excludeext,$level+1);
          }
        }
      closedir($dh);
      }
    }
  }

function DoBackup($args)
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  $_ =&$GLOBALS['_STRINGS']['backend'];
  global $cfg;
  extract(param_extract(array(
    description=>'string',
    check=>'array',
    fname=>'string',
    resources=>'array',
    excludeext=>'string',
    ),$args));


  if (!is_dir($cfg['BackupsPath']))
    {
    return array(Error=>"Backup directory does not exists",$cfg['BackupsPath']);
    }
  $STARTTIME=time();
  $excludeext=explode (",",$excludeext);
  $tablescount=0;
  $tablerows=0;
  $this->unzipsize=0;
  if (array_search('jsbz',$excludeext)===false) {$excludeext[]='jsbz';}


  $targetfilename=$cfg['BackupsPath']."/".$this->generate_name($fname).".jsbz";
  $gz=gzopen ($targetfilename,"w");
  if (!$gz)
    {
    return array(Error=>"Cannot open backup file for writing","$targetfilename");
    }
  $index[0]=array(t=>4,description=>$description,date=>time());

  global $Database,$cfg;
  $t=DBQuery("SHOW TABLES");
  $f0=$t->Fields[0];
  foreach ($t->Rows as $i=>$row)
    {
    $TableName=$row->$f0;
    if (!$check[$TableName]) continue;
    $tablescount++;
    
    
    $CreateData=DBQuery("SHOW CREATE TABLE $TableName");
    $TableData=DBQuery("SELECT * FROM $TableName");

    $fn=$CreateData->Fields[1];
    $buf=$CreateData->Top->$fn."\x00";
    /*
    $ColumnInfo=DBQuery ("SHOW COLUMNS FROM $TableName","Field");
    $IndexInfo=DBQuery ("SHOW INDEX FROM $TableName",array("Key_name","Column_name"));

    $PrimaryKeys="";

    $buf="";
    $buf.="CREATE TABLE $TableName (\n";

    $j=0;
    foreach ($ColumnInfo->Rows as $fname=>$fdata)
     {
     if ($j>0) {$buf.=",\n";}
     $ftype=$fdata->Type;
     $isnull=($fdata->Null=="YES");
     $buf.=" $fname $ftype ";
     if ($fdata->Default!='') {$buf.=" DEFAULT '$fdata->Default'";}
     if ($fdata->Null!="YES") {$buf.=" not null";} else {$buf.=" null";}
     $j++;
     }

    if ($IndexInfo)
      {
      foreach ($IndexInfo->Rows as $key_name=>$key_elements)
        {
        $s="";
        foreach ($key_elements as $fname=>$key_data)
          {
          if ($s) {$s.=",";}
          $s.=$fname;
          if ($key_data->Sub_part)
            {
            $s.=" (".$key_data->Sub_part.")";
            }
          }
        if ($key_name=="PRIMARY")
          {
          $s="PRIMARY KEY ($s)";
          }
        else
          {
          if (!$key_data->Non_unique)
            {
            $s="UNIQUE $key_name ($s)";
            }
          else
            {
            $s="KEY $key_name ($s)";
            }
          }
        $buf.=",\n$s";
        }
        
      }*/

#    $buf.=") ENGINE=MyISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci\x00";


    $rowno=0;
    if ($TableData) foreach ($TableData->Rows as $rowno=>$row)
      {
      $s2="";
      for ($j=0;$j<$TableData->FieldCount;$j++)
        {
        $fname =$TableData->Fields[$j];
        $ftype =$TableData->FieldType[$fname];
        $fflags=$TableData->FieldFlags[$fname];
        $val=$row->$fname;
        $numfield=(strpos("int,real",$ftype)!==false);

        if ($val==NULL)
          {
          if (strpos($fflags,"not_null")!==false)
            {
            $s2.=($numfield)?"N0":"S";
            }
          else
            {
            $s2.="Z";
            }
          }
        else
          {
          $s2.=($numfield)?"N$val":"S$val";
          }
        $s2.="\x00";
        }
      $buf.=$s2."\x00";
      $rowno++;
      $tablerows++;
      }
    $gzbegin=gztell($gz);
    gzwrite ($gz,$buf);
    $gzsize=gztell($gz)-$gzbegin;
    $this->unzipsize+=$gzsize;
    $index[]=array(t=>3,n=>$TableName,s=>$gzsize,o=>$gzbegin,rowcount=>$rowno);
    gzwrite($gz,$buf);
    }
  # END OF TABLES BACKUP

  # BACKUP FILES
  if (is_array($resources))
    {
    if ($resources['themes']) $resources['skins']=1;
    foreach ($resources as $resource_type=>$x)
      {
      list ($rtype,$folder)=explode (":",$resource_type,2);
      print "<table width='500'><tr><td bgcolor='#f8f8f8'><b>Compressing resource '$resource_type'</b><br>";
      $drill_subdirs=true;
      if ($resource_type=='root') {$drill_subdirs=false;}
      $rootdir=$cfg['Resources'][$rtype];
      if ($rootdir) $rootdir=$rootdir[0];
      if (!$rootdir) $rootdir=$cfg[RootPath];
      $index[]=array(t=>1,n=>$folder,p=>$resource_type);
      $this->recursive_compress ($rootdir,$folder,$index,$gz,$resource_type,$drill_subdirs,$excludeext,0);
      print "</td></tr></table>";
      }
    }
  gzclose($gz);

  $index[0]+=array(
    tablescount=>$tablescount,
    tablerows=>$tablerows,
    unzipsize=>$this->unzipsize
    );

  $hz=fopen ($targetfilename,"ab+");
  fseek($hz,0,SEEK_END);
  $indexstart=ftell($hz);
  $s=gzcompress (serialize($index));
  $indexsize=strlen($s);
  fputs ($hz,$s);
  fputs ($hz,"|$indexstart:$indexsize");
  fclose ($hz);
  print $_['BACKUP_COMPLETE'];
  print "<br>".(time()-$STARTTIME).' seconds<br>';
  print "<br/><input type='button' onClick='location.href=\"".ActionURL("backend.IBackupDbase.ViewBackups.bm")."\"' class='button' value='$__[CAPTION_OK]'>";
  }
}
?>
