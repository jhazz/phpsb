P$.$.regModule('sys.buttons',{

onUsing:function(params,ldr,p){
	P$.trace('Вызван sys.button.onUsing()','Источник: '+ldr.name);
	},
	// вызывается после того как все модули, от которых зависит данный модуль, загружены
initModule:function(){
	P$.trace('Вызван sys.button.initModule()');

}
},'sys.templates');
