<?

class forms_IFormEditor {
	var $x;
	
	
	function TestWindow(&$args){
		global $cfg;
    print "\n\n<script src='$cfg[PublicURL]/sys/debugger.js'></script>\n\n";
		?>
		<div style='background-color:#80f080'><br><br>TOPWND</div>
		<script>window.status=''; 
		P$.on('load',function(){ 
			P$.$.systemInfoMenuTo
			P$.$.putSystemInfoMenuTo(P$.find('systemInfoPanel'));
			P$.$.watchSystemInfoTo(P$.find('processesWatch'));

		},window);</script>
		<table border=1 width='100%'><tr valign='top'>
		  <td width='80%'>
		<iframe src='<? print ActionURL("forms.IFormEditor.TestWindowMainFrameset.n"); ?>' width='100%' height='700'></iframe>
		</td><td>
		  <style>
				
				pre{font-family:arial,sans;font-size:9px;margin:0;padding:0}
				plaintext{font-family:arial,sans;font-size:11px;margin:0;}
				table.props{border:solid 1px;}
				table.props tr td.warn{background-color:#ffffe0;}
				table.props tr td.err{background-color:#ffe0e0;}
				table.props tr td {background-color:#ffffff;font-family:arial, sans;font-size:11px;padding:0}
				table.props tr td.level1 {background-color:#f8f8f8;}
				table.props tr td.level2 {background-color:#f0f0f0;}
				table.props tr td.level3 {background-color:#e8e8e8;}
				table.props tr td.level4 {background-color:#e0e0e0;}
				.hov{cursor:pointer;color:#ff0000;background-color:#fff0e8;}
				.hov:hover{color:#ffffff;background-color:#ff6633;}
				span.m {color:#ff0000;background-color:#fff0f0; cursor:pointer;} 
				span.m:hover{background-color:#ff6633;color:#ffffff;}
		  </style>
		  <div id='systemInfoPanel'></div>
		  <div id='processesWatch' style='background-color:#f0f0f0;width:300px;height:700px;overflow:auto;'>Here will be processes watch</div>
		</td></tr></table>
		<?
	}
	function TestWindowMainFrameset(&$args){
		print "
		<frameset cols='*,200'>
		<frameset rows='*,200'>
		  <frame src='".ActionURL("forms.IFormEditor.TestWindowFrameMain.f")."'>
		  <frame src='".ActionURL("forms.IFormEditor.TestWindowFrameBottom.f")."'>
		  </frameset>
		  <frame src='".ActionURL("forms.IFormEditor.TestWindowFrameSide.f")."'>
		</frameset>
		  ";
	}
	
	function TestWindowFrameSide(&$args){
		?>
		<div id='XMLView'>XMLView</div>	
		<script>P$.on('load',function(){P$.find('XMLView');},window);</script>
		<?
	}
	
	function TestWindowFrameBottom(&$args){
		?>
		<div id='TraceView' style='overflow:auto;width:100%; height:180px'></div>
		<script>P$.on('load',function(){P$.$.putTraceView(P$.find('TraceView'));},window);</script>
		<?
	}
	function TestWindowFrameMain(&$args){
#		$_ENV->InitWindows();
		?><script>
		
		function modalReceiver(r){
			alert(P$.dump(r));
		}
		
		function createformndata(){
			P$.run('sys.XML.create',{DOMId:'forms',layout:'<pf:form><pf:table ></pf:table></pf:form>'});
		}
		function openw(no,e){
			var obj,a={url:"<? print ActionURL("forms.IFormEditor.TestWindowFrameMain.f"); ?>",
			  //x:Math.random()*600,
			  //y:Math.random()*500,
			  w:400, h:400, start:0,
			  fadeIn:1,fadeOut:1
			  //skinName:undefined,
			};
			switch(no){
				case 1: a.isModal=1; a.callOnModalResult=modalReceiver;break;
				case 2: a.isModal=1;a.inside=1;break;
				case 4: a.inside=1;break;
				case 5: a.isTop=1;break;
				case 6: a.fadeIn=1; a.fadeOut=1;delete a.url;a.text='Hello !!! <input type="text">';break;
				case 7: a.skinName='popWindow'; a.closeOnBlur=1;a.fadeIn=0; a.isTop=1; obj=P$.eventObj(e); a.posAtElement=1; a.text='<hr>Context menu data<br/>';break;
				case 8: /* popup */ a.skinName='popWindow'; a.closeOnLeave=1;a.fadeIn=0;a.fadeOut=0; a.isTop=1; obj=P$.eventObj(e); a.posAtElement=1; a.text='Context menu data<br/>';break;
			}
			P$.run('sys.win.openWin',a,obj);
		}
		</script>
		<table><tr><td align='center'>
		<input type='button' onClick='createformndata()' value='Создать форму и данные'>
		<input type='button' onClick='openw(1)' value='Открыть модальное'>
		<input type='button' onClick='openw(2)' value='Открыть модальное внутри'>
		<input type='button' onClick='openw(3)' value='Открыть окно'>
		<input type='button' onClick='openw(4)' value='Открыть окно внутри'>
		<input type='button' onClick='openw(5)' value='Открыть окно сверху'>
		<input type='button' onClick='openw(6)' value='Текстовый'>
		<input type='button' onClick='openw(7,event)' value='Контекстное меню'>
		<input type='button' onClick='debugger' value='Debug'>
		<!-- <input type='button' onClick="P$.$.sys.debug.updateTraceView()" value='Трассировка'>-->
<input type='button' onClick="P$.updateTrace(P$.find('tracedata',document))" value='Трассировка'>

		<input type='button' onClick='P$.run("sys.win.modalResult","ok")' value='Закрыть'>
		<input type='button' onClick='P$.run("sys.win.setWinParams",{w:200,h:200,title:"abcdef"})' value='Изменить размер'>
		<a href='zzzz' onMouseOver='openw(8,event)'>Попап меню</a>
		<div id='tracedata'></div></td></tr></table>
		<table width='400'><tr><td bgcolor='red'>z</td></tr></table>
		<div id='forms''>Here will be forms</div>
		<?

	}
	
	function Browse(&$args) {
	  $iform=&$_ENV->LoadInterface("forms.PsbForm");
	  
	  /*$iform->PutDataset(array(
	  	Name=>"DocClasses",
	  	Recordset=>&$q,
#	  	Columns=>array(array(Field=>"ClassName",Width=>'20%'),array(Field=>"Caption",Caption=>"Описание"))
		));
		
	  $iform->PutDataset(array(Name=>"SomeValues",Values=>array(1=>"Пример 1",2=>"Пример 2",3=>"Пример 3")));*/

	  $iform->Init();
	  
#		 <instance id='filter1'><instance id='filter1'></instance><criteria>Some text</criteria><criteria><b>Some</b> text2</criteria><checker>Checked</checker></instance>
#	   <instance id='rss1' src='http://localhost/rss_feed.php' loadOnStart='yes' paging='yes' partSize='10' reqNodeset='filter1:' />
	  
	  print '<style>.pfds{font-size:9px;}
	  .pfds-input{font-family:verdana,arial; size:12px;color:#000000;padding:2;margin:0;border:0; }
	  .pfds-input-readonly{font-family:verdana,arial; size:12px;color:#202020;padding:2;margin:0;border:0;}
	  .pfds-thead {background-color:#e0e0e0; font-family:verdana,arial; size:12px; color:#202020;}
	  
	  </style>';
	  $iform->SetModel("model1","
	  <model>
	   <submission action='".ActionURL("doc.IDocClasses.CreateNewClass")."'></submission>

	   
	   <instance id='filter1'>
	   <someparam>hello</someparam>
	   </instance>
	   
	   <instance id='rss1' src='http://localhost/rss_feed.php' loadOnStart='yes' paging='yes' partSize='10' reqNodeset='filter1:' schema='rss_feeder'/>
	   
	   <instance id='instance1' schema='schema1'>
	   <fio>Иванов А.И.</fio>
	   <weight>123.0</weight>
		 <basket>
		   <row id='1'><name>The \"apple</name><price>12.4</price><count>3</count>
		     <options>
		       <item id='1'><attr>color</attr><value>green</value></item>
		       <item id='2'><attr>size</attr><value>xl</value></item>
		       <item id='3'><attr>color</attr><value>green</value></item>
		       <item id='4'><attr>size</attr><value>xl</value></item>
		      </options></row>
		   <row id='2'><name>The banana2</name><count>10</count><price>5.7</price></row>
		   <row id='3'><name>The banana3</name><count>11</count><price>5.8</price>
		     <options>
		       <item id='17'><attr>color</attr><value>green</value></item>
		       <item id='28'><attr>size</attr><value>xl</value></item>
		       <item id='31'><attr>color</attr><value>green</value></item>
		      </options>		   </row>
		   <row id='4'><name>The banana4</name><count>12</count><price>5.9</price></row>
		   <row id='5'><name>The banana5</name><count>13</count><price>5.6</price></row>
		   <row id='6'><name>The banana6</name><count>14</count><price>5.5</price></row>
		 </basket>
		 <total></total>
		 </instance>
		 
		 
		 <schema id='rss_feeder'>
		 <instance>
		 <rowset>
		   <row><name/><price/></row>
		   </rowset>
		 </instance>
		 </schema>
		 
		 <schema id='schema1'>
		 <instance>
	     <sure type='boolean'><init>1</init></sure>
	     <fio required='yes'><init>TheFio</init></fio>
	     <weight type='number'><init>12</init></weight>
	     <basket required='yes' array='row'>
	       <row sequence='yes'>
	         <name required='yes'/> 
	         <price type='float' init='0.0'><format>fff</format>
	           <warning if='{price} &lt; 10'>Вы уверены, что цена может быть меньше 10?</warning>
	           <error if='{price} &lt; 0'>Цена не может быть меньше нуля</error></price>
	         <count init='1'></count>
	         <cost type='float' calculate='{price} + {count}'/>
	         <options array='item'>
	           <item sequence='yes'>
	             <attr init='color'/><value/>
	           </item>
	         </options>
	       </row>
	     </basket>
		 </instance>
		 </schema>

		 </model>
		 ");
	  	
	  $iform->SetLayout("formLayout1","
		<forms:form ref='instance1:/instance'>
		<table width='100%' border='1'><tr><td bgcolor='#f0e0e0'> Fio:</td><td> <forms:text ref='fio'/></td></tr></table>
	    <forms:table nodeset='basket/row' offset='0' limit='2' width='700' height='350'>
				<forms:tr>
					<forms:td caption='Название продукта' width='100' ref='name'><forms:text /></forms:td>
					<forms:td caption='Кол-во' width='100' ref='count'><forms:text />шт.</forms:td>
					<forms:td caption='Цена' width='100' ref='price'><forms:text size='3' /></forms:td>
					<forms:td caption='Стоимость' width='50' ref='cost'><forms:text size='3' /></forms:td>
					<forms:td caption='Опции' width='250' >
						<forms:table nodeset='options/item' width='230' limit='3' height='100'>
							<forms:tr >
								<forms:td caption='Атрибут' width='30' ref='attr'><forms:text size='8' /></forms:td>
								<forms:td caption='Значение' width='50' ref='value'><forms:text size='3'/></forms:td>
							</forms:tr>
						</forms:table>
					</forms:td>
				</forms:tr>
	    </forms:table>
			
	    
		  <forms:table nodeset='rss1:/rowset/row' offset='0' limit='10' width='800' height='400' mode='1'>
    		<forms:thead>
					<forms:tr height='10'>
	    			<forms:th colspan='2' width='200'>Название продукта</forms:th>
	    			<forms:th rowspan='2' width='150' height='100'>Цена</forms:th>
					</forms:tr>
					<forms:tr>
	    			<forms:th width='40'>SKU</forms:th>
	    			<forms:th width='160'>Имя</forms:th>
					</forms:tr>
    		</forms:thead>

				<forms:tr>
					<forms:td ref='name'><forms:text/></forms:td>
					<forms:td ref='price'><forms:text/></forms:td>
					<forms:td>KuKu</forms:td>
				</forms:tr>
		  </forms:table>
		</forms:form>");
	  
	  
	  print "<body>";
	  $iform->RenderForm("form1","formLayout1","model1",false); 
	  //$iform->RenderForm("form2","formLayout2","model1",false); 
	  $iform->AfterRender();
	  
	  /*
	  	     <!--
	  Weight: <forms:text ref='/weight'/>
	    <forms:repeat nodeset='/basket/row'>
	     <table><tr><td>
	     Название продукта:<forms:text ref='name'/></td><td>
	     Кол-во<forms:text ref='count'><label>Кол-во</label></forms:text>
	     <forms:text ref='price'/>
	     <forms:text size='3' ref='cost'/>
	     </td>
	     <td>
	       <forms:repeat nodeset='options/item'>
	         <forms:text size='3' ref='attr'/>
	         <forms:text size='4' ref='value'/>
	       </forms:repeat>
	     </td>
	     </tr></table>
	    </forms:repeat>
	    Are you sure? <forms:text ref='sure'/><br/>
	       -->

	  */
	  
	}
};
?>