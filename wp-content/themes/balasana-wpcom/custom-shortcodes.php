<?php
include('function_estoque.php');

global $lib_jquery, $lib_jquery_datatables, $lib_jquery_datatables_css, $lib_bootstrap_css, $lib_fontawesome_css;

$lib_jquery = 'https://code.jquery.com/jquery-3.7.1.js';
$lib_jquery_datatables = 'https://cdn.datatables.net/2.1.3/js/dataTables.js';
$lib_jquery_datatables_css = 'https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.css';
$lib_bootstrap_css = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';
$lib_bootsrap_bundle = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js
';
$lib_fontawesome_css = 'https://use.fontawesome.com/releases/v5.3.1/css/all.css';


global $path_pagina;
$path_pagina = $_SERVER['DOCUMENT_ROOT'] . "/WPEstoque/bibEstoque/";


function enqueue_datatables(){
	global $lib_jquery, $lib_jquery_datatables, $lib_jquery_datatables_css;
	
	wp_enqueue_style( 'jQueryDataTableStyle', $lib_jquery_datatables_css );
	wp_enqueue_script('jQueryDataTable', $lib_jquery_datatables , ['jquery']);
	
}

function enqueue_bootstrap(){
	global $lib_bootstrap_css, $lib_bootsrap_bundle;
	
	wp_enqueue_style( 'bootstrapStyle', $lib_bootstrap_css );
	
	wp_enqueue_script( 'bootstrap-scripts', $lib_bootsrap_bundle ,array( 'jquery' ), '', true );
	
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
	
							
	wp_localize_script( 'funcoes_estoque', 'nonce_addItem', 
							wp_create_nonce('nonce_addItem'));
							
	wp_localize_script( 'funcoes_estoque', 'nonce_get1Item', 
							wp_create_nonce('nonce_get1Item'));
							
	wp_localize_script( 'funcoes_estoque', 'nonce_alteraItem', 
							wp_create_nonce('nonce_alteraItem'));
	
	
	
	$path = $path_pagina . "tabConsultaItens.html";
	
	return file_get_contents($path);
	
}

add_shortcode('estoque_estoque', 'pagina_estoque');
function pagina_estoque(){
	global $path_pagina;
	
	enqueue_datatables();
	enqueue_bootstrap();
	enqueue_fontawesome();

	
	wp_enqueue_script('funcoes_estoque', '/bibEstoque/funcoes_estoque.js', '', '', true);
	wp_enqueue_script('initTabLocais', '/bibEstoque/initTabEstoque.js', '', '', true);
	
	
	
	
	wp_localize_script( 'funcoes_estoque', 'ajax_url', [admin_url( 'admin-ajax.php' )]);
	wp_localize_script( 'funcoes_estoque', 'nonce_getEstoque', 
							wp_create_nonce('nonce_getEstoque'));
							
	wp_localize_script( 'funcoes_estoque', 'nonce_getItens', 
							wp_create_nonce('nonce_getItens'));
	
	wp_localize_script( 'funcoes_estoque', 'nonce_get_Locais', 
							wp_create_nonce('nonce_get_Locais'));
							
	wp_localize_script( 'funcoes_estoque', 'nonce_get1Estoque', 
							wp_create_nonce('nonce_get1Estoque'));
	
							
	wp_localize_script( 'funcoes_estoque', 'nonce_alteraEstoque', 
							wp_create_nonce('nonce_alteraEstoque'));
	
	/*
	wp_localize_script( 'funcoes_estoque', 'nonce_alteraItem', 
							wp_create_nonce('nonce_alteraItem'));
	
	
	*/
	
	$path = $path_pagina . "tabConsultaEstoque.html";
	
	return file_get_contents($path);
	
}
?>

