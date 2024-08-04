<?php
include('function_estoque.php');

global $lib_jquery, $lib_jquery_datatables, $lib_jquery_datatables_css;

global $path_pagina;


$lib_jquery = 'https://code.jquery.com/jquery-3.7.1.js';
$lib_jquery_datatables = 'https://cdn.datatables.net/2.1.3/js/dataTables.js';
$lib_jquery_datatables_css = 'https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.css';
$lib_bootstrap_css = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css';


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



add_shortcode('estoque_locais', 'pagina_locais');
function pagina_locais(){
	global $path_pagina;
	
	enqueue_datatables();
	enqueue_bootstrap();

	wp_enqueue_script('funcoes_estoque', '/bibEstoque/funcoes_estoque.js', '', '', true);
	wp_enqueue_script('initTabLocais', '/bibEstoque/initTabLocais.js', '', '', true);
	
	wp_localize_script( 'funcoes_estoque', 'ajax_url', [admin_url( 'admin-ajax.php' )]);
	wp_localize_script( 'funcoes_estoque', 'nonce_get_Locais', 
							wp_create_nonce('nonce_get_Locais'));
							
	wp_localize_script( 'funcoes_estoque', 'nonce_add_Local', 
							wp_create_nonce('nonce_add_Local'));
	
	//$x = getConsultaLocais();
	
	
	$path = $path_pagina . "tabConsultaLocais.html";
	
	return file_get_contents($path);
	
}


/*

add_shortcode('tabelaHistorico', 'tabelaHistorico');
function tabelaHistorico(){
	global $dataTablecss, $dataTablejs;
	
	wp_enqueue_style( 'jQueryDataTableStyle', $dataTablecss );
	wp_enqueue_script('jQueryDataTable', $dataTablejs , ['jquery']);
	
	wp_enqueue_script('funcoesConsulta', '/js/funcoesConsulta.js', '', '', true);
	wp_enqueue_script('initHistorico', '/js/initHistorico.js', '', '', true);

	wp_localize_script( 'funcoesConsulta', 'ajax_url', [admin_url( 'admin-ajax.php' )]);
	//wp_localize_script( 'scriptsHistorico', 'fnonce', wp_create_nonce('funcaoConsultas-nonce'));
	
	wp_localize_script( 'funcoesConsulta', 'fnonceConsultaHistorico', wp_create_nonce('funcoesConsultaHistorico-nonce'));
	
	//localhost
	$path = $_SERVER['DOCUMENT_ROOT'] . "/wordpress2/js/tabelaHistorico.html";

	//pantheon
	//$path = $_SERVER['DOCUMENT_ROOT'] . "/js/tabelaHistorico.html";



	$file = '<meta http-equiv="Refresh" content="300">';
	//$file = "<p> ID usuário: " . get_current_user_id() . "</p>";
	//$file .= "<p> Admin url: " . admin_url('admin-post.php') . "</p>";
	//$file .= "<script> window.idUsuario = " . get_current_user_id() . "; </script>";

	$file = $file .  file_get_contents($path);
	
	return $file;
	
	
}

add_shortcode('formConsulta', 'formConsulta');
function formConsulta(){
	global $dataTablecss, $dataTablejs;
	
	wp_enqueue_style( 'jQueryDataTableStyle', $dataTablecss );
	wp_enqueue_script('jQueryDataTable', $dataTablejs , ['jquery']);
	
	wp_enqueue_script('funcoesConsulta', '/js/funcoesConsulta.js', '', '', true);
	wp_enqueue_script('initFormConsulta', '/js/initFormConsulta.js', '', '', true);

	wp_localize_script( 'funcoesConsulta', 'ajax_url', [admin_url( 'admin-ajax.php' )]);
	wp_localize_script( 'funcoesConsulta', 'fnonceConsultasAtivas', wp_create_nonce('funcoesConsultasAtivas-nonce'));
	wp_localize_script( 'funcoesConsulta', 'fnonceGetBairros', wp_create_nonce('fnonceGetBairros-nonce'));
	wp_localize_script( 'funcoesConsulta', 'fnonceGetQuadras', wp_create_nonce('fnonceGetQuadras-nonce'));
	wp_localize_script( 'funcoesConsulta', 'fnonceGetLotes', wp_create_nonce('funcoesGetLotes-nonce'));
	wp_localize_script( 'funcoesConsulta', 'fnonceGetLocal', wp_create_nonce('funcoesGetLocal-nonce'));
	
	
	//localhost
	$path = $_SERVER['DOCUMENT_ROOT'] . "/wordpress2/js/formConsulta.html";
	$path2 = $_SERVER['DOCUMENT_ROOT'] . "/wordpress2/js/tabelaHistorico.html";

	//pantheon
	//$path = $_SERVER['DOCUMENT_ROOT'] . "/js/formConsulta.html";
	//$path2 = $_SERVER['DOCUMENT_ROOT'] . "/js/tabelaHistorico.html";



	$file = '<meta http-equiv="Refresh" content="300">';
	$file = $file .  file_get_contents($path);
	$file .= '<p>OBS: Lista estará vazia se não houver consultas ativas!</p>';
	$file .= file_get_contents($path2);
	
	return $file;
	
}

add_shortcode('pagePerfilMembro', 'pagePerfilMembro');
function pagePerfilMembro(){
	$paginaPerfil = '';
	
	$paginaPerfil .="<p> Tipo de Assinatura: ";
	
	if(current_user_is("s2member_level0")){
		$paginaPerfil .= " Gratuita -> permite 1 consulta por hora </p>";
		
	}elseif(current_user_is("s2member_level1")){
		$paginaPerfil .= " Tipo 1 -> permite 2 consultas por hora </p>";

	}elseif(current_user_is("s2member_level2")){
		$paginaPerfil .= " Tipo 2 -> permite 3 consultas por hora </p>";

	}elseif(current_user_is("s2member_level3")){
		$paginaPerfil .= " Tipo 3 -> permite 4 consultas por hora </p>";

	}elseif(current_user_is("s2member_level4")){
		$paginaPerfil .= " Tipo 4 -> permite 5 consultas por hora </p>";

	}else{
		$paginaPerfil .= " Administrador -> permite 50 consulta por hora </p>";
	}
	
	date_default_timezone_set($cf_timezone);
	
	$paginaPerfil .= "<p> Data de cadastro: " . date('Y-m-d H:i:s', s2member_registration_time()) . "</p>";



	
	return $paginaPerfil;
	
}
*/

?>

