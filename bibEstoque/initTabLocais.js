jQuery(document).ready( function () {
	initTabelaLocais();
	getLocais();
	
	
	
	// Event listeners
	// toggle do form de adição de item
	jQuery("#btnAddLocalToogle").on("click", function(e){
		jQuery("#formAddLocal").toggle();
	});

} );
