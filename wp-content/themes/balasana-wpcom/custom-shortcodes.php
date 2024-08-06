<?php
include('function_estoque.php');

global $lib_jquery, $lib_jquery_datatables, $lib_jquery_datatables_css;

global $path_pagina;


$lib_jquery = 'https://code.jquery.com/jquery-3.7.1.js';
$lib_jquery_datatables = 'https://cdn.datatables.net/2.1.3/js/dataTables.js';
$lib_jquery_datatables_css = 'https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.css';
$lib_bootstrap_css = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css';
$lib_fontawesome_css = 'https://use.fontawesome.com/releases/v5.3.1/css/all.css';

$path_pagina = $_SERVER['DOCUMENT_ROOT'] . "/WPEstoque/bibEstoque/";


function enqueue_datatables(){
	global $lib_jquery, $lib_jquery_datatables, $lib_jquery_datatables_css;
	
	wp_enqueue_style( 'jQueryDataTableStyle', $lib_jquery_datatables_css );
	wp_enqueue_script('jQueryDataTable', $lib_jquery_datatables , ['jquery']);
	
}

function enqueue_bootstrap(){
	global $lib_bootstrap_css;
	
	wp_enqueue_style( 'bootstrapStyle', $lib_bootstrap_css );
	
}

function enqueue_fontawesome(){
	global $lib_fontawesome_css;
	
	wp_enqueue_style( 'fontAwesomeStyle', $lib_fontawesome_css );
	
}



add_shortcode('estoque_locais', 'pagina_locais');
function pagina_locais(){
	global $path_pagina;
	
	enqueue_datatables();
	enqueue_bootstrap();
	enqueue_fontawesome();

	wp_enqueue_script('funcoes_estoque', '/bibEstoque/funcoes_estoque.js', '', '', true);
	wp_enqueue_script('initTabLocais', '/bibEstoque/initTabLocais.js', '', '', true);
	
	wp_localize_script( 'funcoes_estoque', 'ajax_url', [admin_url( 'admin-ajax.php' )]);
	wp_localize_script( 'funcoes_estoque', 'nonce_get_Locais', 
							wp_create_nonce('nonce_get_Locais'));
							
	wp_localize_script( 'funcoes_estoque', 'nonce_add_Local', 
							wp_create_nonce('nonce_add_Local'));
							
	wp_localize_script( 'funcoes_estoque', 'nonce_get_1_Local', 
							wp_create_nonce('nonce_get_1_Local'));
							
	wp_localize_script( 'funcoes_estoque', 'nonce_alteraLocal', 
							wp_create_nonce('nonce_alteraLocal'));
	
	$path = $path_pagina . "tabConsultaLocais.html";
	
	return file_get_contents($path);
	
}


add_shortcode('estoque_itens', 'pagina_itens');
function pagina_itens(){
	global $path_pagina;
	
	enqueue_datatables();
	enqueue_bootstrap();
	enqueue_fontawesome();

	wp_enqueue_script('funcoes_estoque', '/bibEstoque/funcoes_estoque.js', '', '', true);
	wp_enqueue_script('initTabLocais', '/bibEstoque/initTabItens.js', '', '', true);
	
	
	
	wp_localize_script( 'funcoes_estoque', 'ajax_url', [admin_url( 'admin-ajax.php' )]);
	wp_localize_script( 'funcoes_estoque', 'nonce_getItens', 
							wp_create_nonce('nonce_getItens'));
	
	/*						
	wp_localize_script( 'funcoes_estoque', 'nonce_add_Local', 
							wp_create_nonce('nonce_add_Local'));
							
	wp_localize_script( 'funcoes_estoque', 'nonce_get_1_Local', 
							wp_create_nonce('nonce_get_1_Local'));
							
	wp_localize_script( 'funcoes_estoque', 'nonce_alteraLocal', 
							wp_create_nonce('nonce_alteraLocal'));
	
	//$x = getConsultaLocais();
	
	*/
	
	
	$path = $path_pagina . "tabConsultaItens.html";
	
	return file_get_contents($path);
	
}

?>

