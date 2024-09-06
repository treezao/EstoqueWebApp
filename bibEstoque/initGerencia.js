jQuery(document).ready( function () {
	
	initTabelaSolicitacoes_solicitacoes();
	
	getSolicitacoesTudo();
	
	
	jQuery("#btnFechar").on("click", function(e){
		window.scrollTo({ top: 0, behavior: 'smooth' });
		resetMsgTopo();
		
		jQuery("#formAlteraSolicitacao").hide();
		jQuery("#formAlteraSolicitacao").trigger("reset");

	});

} );
