jQuery(document).ready( function () {
	initTabelaItens();
	getItens();
	
	
	
	// Event listeners
	jQuery("#btnAddItemToogle").on("click", function(e){
		jQuery("#formAddItem").toggle();
	});
	
	
	

	

} );
