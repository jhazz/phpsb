P$.$.regModule('sys.button',{
using:function(params,ldr,p){P$.trace('Вызван sys.button.using()','Источник: '+ldr.name);},
initModule:function(){ 
	P$.$.registerClass
	P$.trace('Вызван sys.button.initModule()');
},

put:function(a,owner,htmlNode){
	a.className='sys.Button';
	var W=P$.$.sys.win,P=P$.$,c=P.createObject('control',a,[owner,htmlNode]);
	if(a.onclick)P$.on('click',a.onclick,c.le.i);
},
changeState:function(b){
	if(b.onchangestate)b.onchangestate();
}

},'sys.baseSkins');
