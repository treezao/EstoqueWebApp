jQuery(document).ready( function () {
	initTabelaEstoque();
	getEstoque();
	
	
	
	// Event listeners
	// toggle do form de adição de item
	jQuery("#btnAlteraEstoqueToogle").on("click", function(e){
		jQuery("#formAlteraEstoque").toggle();
	});
	
} );
