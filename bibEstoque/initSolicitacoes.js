jQuery(document).ready( function () {
	
	initTabelaEstoque_solicitacoes();
	initTabelaSolicitacoes_solicitacoes();
	getEstoque(atualizaTabEstoque_solicitacoes);
	
	solicitacao_formGetLocal();
	solicitacao_formGetItem();
	
	getSolicitacoes();
	
	
	jQuery("#btnSolicitar_cancela").on("click", function(e){
		window.scrollTo({ top: 0, behavior: 'smooth' });
		resetMsgTopo();
		jQuery("#formAlteraEstoque").hide();
		jQuery("#formAlteraEstoque").trigger("reset");

	});

} );
