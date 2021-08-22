P$.$.regModule('sys.button',{

onusing:function(params,ldr,p){
	P$.trace('Вызван sys.button.onUsing()','Источник: '+ldr.name);
	},
// вызывается после того как все модули, от которых зависит данный модуль, загружены
initModule:function(){
	P$.trace('Вызван sys.button.initModule()');

},
put:function(e,t){
	var c=t.document.createElement('div');
	c.innerHTML=e[1].caption;
	t.appendChild(c);
	
},
	
},'sys.templates');
