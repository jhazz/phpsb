// Progress bar

var PB={};
function PutProgress(id,info,w)
  {
  document.write ("<table cellspacing=0 cellpadding=0><tr valign='top'><td><img style='visibility:hidden' id='progra"+id+"' src='"+SkinURL+"/wait.gif'></td><td>"
   +"<table height='30' width='"+w+"' border='1' cellspacing='0'><tr><td bgcolor='white'>"
   +"<table height='30' id='progrl"+id+"' width='1'><tr><td bgcolor='#000080'></td></tr></table></td></tr></table>"
   +"<div id='progri"+id+"' style='width:"+w+";font-size:10px; font-weight:bold; font-family:verdana,arial,sans'>"+info+"</div>"
   +"<div id='progrt"+id+"' style='font-size:10px; font-weight:bold; font-family:verdana,arial,sans; color:#808080'>0 sec</td></tr></table>");

  PB[id]={w:w};
  }
/*function gettime()
  {
  var d=new Date();
  return d.getSeconds()+
  }
  */
function Progress_Start(id)
  {
  var d=new Date();
  PB[id].prevlap=0;
  PB[id].paused=true;
  Progress_Continue(id);
  }
function Progress_Pause(id)
  {
  var d=new Date();
  var p=PB[id];
  document.getElementById("progra"+id).style.visibility='hidden';
  p.prevlap+=d.getTime()-p.starttime;
  p.paused=true;
  }
function Progress_Continue(id)
  {
  var p=PB[id];
  if (!p.paused) return;
  p.paused=false;
  var d=new Date();
  p.starttime=d.getTime();
  document.getElementById("progra"+id).style.visibility='visible';
  window.setTimeout("Progress_Tick('"+id+"')",1000);
  }
function Progress_Tick(id)
  {
  var d=new Date();
  var p=PB[id];
  var t=(p.paused)?p.prevlap:d.getTime()-p.starttime+p.prevlap;

  t=Math.floor(t/1000);
  var min=Math.floor(t/60);
  var sec=t-min*60;
  var s=(min==0)?sec+" sec":min+":"+sec;
  document.getElementById ("progrt"+id).innerHTML=s;
  if (!p.paused) window.setTimeout("Progress_Tick('"+id+"')",1000);
  }
function Progress_NewPos(id,pos,info) // 0-100
  {
  var p=PB[id];
  var s=Math.round(p.w*(pos/100));
  document.getElementById("progri"+id).innerHTML=info;
  document.getElementById("progrl"+id).style.width=s;
  }