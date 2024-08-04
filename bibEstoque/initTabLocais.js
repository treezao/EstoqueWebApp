jQuery(document).ready( function () {
	initTabelaLocais();
	getLocais();
	
	
	
	// Event listeners
	jQuery("#btnAddLocalToogle").on("click", function(e){
		jQuery("#formAddLocal").toggle();
	});

	

} );
