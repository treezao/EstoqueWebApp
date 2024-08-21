jQuery(document).ready( function () {
	initTabelaItens();
	getItens();
	
	
	
	// Event listeners
	jQuery("#btnAddItemToogle").on("click", function(e){
		resetMsgTopo();
		jQuery("#formAddItem").toggle();
	});
	
	
	

	

} );
