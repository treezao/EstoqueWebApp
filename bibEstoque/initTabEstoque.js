jQuery(document).ready( function () {
	initTabelaEstoque();
	getEstoque();
	
	estoque_formGetLocal();
	estoque_formGetItem();
	
	
	
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
	});
} );
