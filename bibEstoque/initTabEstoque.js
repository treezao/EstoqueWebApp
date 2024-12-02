jQuery(document).ready( function () {
	initTabelaEstoque();
	getEstoque(atualizaTabEstoque);
	
	estoque_formGetLocal();
	estoque_formGetItem();
	
	// aplica Select2 (biblioteca)
	jQuery("#estoque_local").select2({
		width: '80%'
	});
	
	jQuery("#estoque_item").select2({
		width: '80%'
	});
	
	jQuery("#estoque_local_mov").select2({
		width: '80%'
	});
	
	jQuery("#estoque_patr_mov").select2({
		width: '80%'
	});
	
	
	
	// Event listeners
	// toggle do form de adição de item
	jQuery("#btnAlteraEstoqueToogle").on("click", function(e){
		jQuery("#formAlteraEstoque").toggle();
	});
	
	
	jQuery("#estoque_op").on("change", function(e){
		var sel = jQuery("#estoque_op option:selected").text();
		
		if(sel === "Adicionar"){
			jQuery("#formAdicionarEstoque").show();
			jQuery("#formRemoverEstoque").hide();
			jQuery("#formMoverEstoque").hide();
			
			return;
		}
		
		if(sel === "Movimentar"){
			jQuery("#formAdicionarEstoque").hide();
			jQuery("#formRemoverEstoque").hide();
			jQuery("#formMoverEstoque").show();
			
			return;
		}
		
		if(sel === "Remover"){
			jQuery("#formAdicionarEstoque").hide();
			jQuery("#formRemoverEstoque").show();
			jQuery("#formMoverEstoque").hide();
			
			return;
		}
		
		jQuery("#formAdicionarEstoque").hide();
		jQuery("#formRemoverEstoque").hide();
		jQuery("#formMoverEstoque").hide();
		
		return;
		
	});
} );
