<?php

global $cf_data, $cf_conn, $cf_timezone;

$cf_data["msg"] = "Falhou em algo";
$cf_data["msg2"] = "";
$cf_data["error"] = true;
$cf_data["consultas"] = [];

$cf_timezone = "America/Sao_Paulo";

function validaPOST(){
	global $cf_data;
	
	if (!($_SERVER["REQUEST_METHOD"] == "POST")) {
		$cf_data["msg"] = "Método de requisição não foi POST...";
		$cf_data["error"] = true;
		
		return false;
		
	}else{
		
		return true;
		
	}
}

function validaNonce($nomeNonce){
	global $cf_data;
	
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $nomeNonce)) {
		$cf_data["msg"] = "Erro de validação!";
		$cf_data["error"] = true;
		
		return false;
	}else{
		return true;
	}
}

function validaUsuario(){
	global $cf_data;
	
	if(!get_current_user_id()){
		
		$cf_data["msg"] = "Usuário não definido!";
		$cf_data["error"] = true;
		
		return false;
		
	}else{
		return true;
	}
	
}

function conecta(){
	global $cf_data, $cf_conn;
	
	// $servername = "dbserver.dev.e5fdf7b7-89ab-420e-b66e-65eaa2e54805.drush.in";
	// $username = "pantheon";
	// $password = "80b37e330cc64e1aa0aed2f4b5bd80d1";
	// $port = 14581;
	// $dbname = "pantheon";
	
	
	// localhost
	$servername = "localhost";
	$username = "wp";
	$password = "wp";
	$dbname = "test";
	$port = 3306;
	
	$cf_conn = new mysqli($servername, $username, $password,$dbname,$port);
	
	if ($cf_conn->connect_error){
		$cf_data["msg"] = "Connection failed: " . $cf_conn->connect_error . "<br>";
		$cf_data["error"] = true;
		
		return false;
	}else{
		return true;
	}
	
}

function finaliza(){
	global $cf_data, $cf_conn;
	
	
	if($cf_conn){
		$cf_conn->close();
	}
	
	echo json_encode($cf_data);
	
	wp_die();
}



add_action('wp_ajax_getLocais','get_Locais');
function get_Locais(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_get_Locais') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Recuperando consultas...";
	
	$sql = "SELECT * FROM localizacao ORDER BY localizacao.nome DESC";
	$result = $cf_conn->query($sql);
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
				$cf_data["consultas"][] = [
										$row["id"], // id
										$row["nome"], // nome curto
										$row["descricao"], // descricao
										];
		}
		
		$cf_data["msg"] = "Consultas encontradas: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "Nenhum local foi cadastrado...";
		$cf_data["error"] = true;
	}
	
	finaliza();
}

add_action('wp_ajax_addLocal','add_Local');
function add_Local(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_add_Local') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}


	$cf_data["msg"] = "Adicionando local...";
	
	if(!isset($_POST["nome"])){
		$cf_data["msg"] = "Nome não definido!";
		$cf_data["error"] = true;
		
		finaliza();
	}elseif(!isset($_POST["descricao"])){
		$cf_data["msg"] = "Descrição em branco!";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	
	$sql = "INSERT INTO localizacao (id, nome, descricao) VALUES (NULL, '" . $_POST["nome"] . "', ' ". $_POST["descricao"] . "');";
	
	
	if($cf_conn->query($sql) === TRUE){
		$cf_data["msg"] = "Local adicionado!";
		$cf_data["error"] = false;
		
	}else{
		$cf_data["msg"] = "Não foi possível adicionar o novo local. SQL:" . $sql . "/erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
	}

	finaliza();
}



function getConsultaLocais(){
	global $cf_conn, $cf_data;
	
	/*
	if(!validaPOST() || !validaNonce('funcoesConsultaHistorico-nonce') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}
	*/
	
	if(!conecta()){
		//finaliza();
		return 'não conectou...';
	}
	
	$cf_data["msg"] = "Recuperando consultas...";
	
	$sql = "SELECT * FROM localizacao ORDER BY localizacao.nome DESC";
	$result = $cf_conn->query($sql);
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		while($row = $result->fetch_assoc()){
		
			print $row["nome"] . " - ". $row["descricao"] . "<br>";
			
		}
	}
	
	//var_dump($result);
	
	return '<br>xx<br>';
	
/*
	
	if($result->num_rows > 0 ) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
			if(verificaValidade($row["horario"])){
				$cf_data["consultas"][] = [
										$row["horario"], // horario
										$row["bairro_nome"], // nome bairro
										$row["quadra_nome"], // nome quadra
										$row["lote_nome"], // nome lote
										$cf_gmaps . $row["local"], // link
										];
			}else{
				$cf_data["consultas"][] = [
										$row["horario"], // horario
										$row["bairro_nome"], // nome bairro
										$row["quadra_nome"], // nome quadra
										$row["lote_nome"], // nome lote
										"", // link
										];
			}
		}
		
		$cf_data["msg"] = "Consultas encontradas: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "Este usuário não fez consultas anteriormente...";
		$cf_data["error"] = true;
	}
	
	finaliza();
	*/
	
}

/*
<?php

global $cf_data, $cf_conn, $cf_gmaps, $cf_timezone, $cf_maxConsultas;

$cf_data["msg"] = "Falhou em algo";
$cf_data["msg2"] = "";
$cf_data["error"] = true;
$cf_data["bairros"] = [];
$cf_data["quadra"] = [];
$cf_data["lotes"] = [];
$cf_data["consultas"] = [];

$cf_timezone = "America/Sao_Paulo";




function finaliza(){
	global $cf_data, $cf_conn;
	
	
	if($cf_conn){
		$cf_conn->close();
	}
	
	echo json_encode($cf_data);
	
	wp_die();
}


add_action('wp_ajax_funcoesConsultaHistorico','getConsultaHistorico');
function getConsultaHistorico(){
	global $cf_conn, $cf_data, $cf_gmaps;
	
	if(!validaPOST() || !validaNonce('funcoesConsultaHistorico-nonce') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Recuperando consultas...";
	
	$sql = "SELECT consultas.horario, bairros.bairro_nome,quadras.quadra_nome,lotes.lote_nome,lotes.local FROM consultas INNER JOIN (bairros,quadras,lotes) ON (consultas.bairro_id=bairros.id AND consultas.quadra_id=quadras.id AND consultas.lote_id = lotes.id)".
		"WHERE consultas.idUsuario=" . get_current_user_id() . " ORDER BY consultas.id DESC";
	$result = $cf_conn->query($sql);
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
			if(verificaValidade($row["horario"])){
				$cf_data["consultas"][] = [
										$row["horario"], // horario
										$row["bairro_nome"], // nome bairro
										$row["quadra_nome"], // nome quadra
										$row["lote_nome"], // nome lote
										$cf_gmaps . $row["local"], // link
										];
			}else{
				$cf_data["consultas"][] = [
										$row["horario"], // horario
										$row["bairro_nome"], // nome bairro
										$row["quadra_nome"], // nome quadra
										$row["lote_nome"], // nome lote
										"", // link
										];
			}
		}
		
		$cf_data["msg"] = "Consultas encontradas: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "Este usuário não fez consultas anteriormente...";
		$cf_data["error"] = true;
	}
	
	finaliza();
}


add_action('wp_ajax_funcoesConsultasAtivas','getConsultasAtivas');
function getConsultasAtivas(){
	global $cf_conn, $cf_data, $cf_gmaps;
	
	if(!validaPOST() || !validaNonce('funcoesConsultasAtivas-nonce') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Recuperando consultas ativas...";
	
	$sql = "SELECT consultas.horario, bairros.bairro_nome,quadras.quadra_nome,lotes.lote_nome,lotes.local FROM consultas INNER JOIN (bairros,quadras,lotes) ON (consultas.bairro_id=bairros.id AND consultas.quadra_id=quadras.id AND consultas.lote_id = lotes.id)".
		"WHERE consultas.idUsuario=" . get_current_user_id() . " ORDER BY consultas.id DESC";
	
	$result = $cf_conn->query($sql);
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
			if(verificaValidade($row["horario"])){
				$cf_data["consultas"][] = [
										$row["horario"], // horario
										$row["bairro_nome"], // nome bairro
										$row["quadra_nome"], // nome quadra
										$row["lote_nome"], // nome lote
										$cf_gmaps . $row["local"], // link
										];
			}
		}
		
		$cf_data["msg"] = "Consultas encontradas: " . count($cf_data["consultas"]);
		$cf_data["error"] = false;
		
	}
	
	finaliza();
}

add_action('wp_ajax_funcoesGetBairros','getBairros');
function getBairros(){
	
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('fnonceGetBairros-nonce') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}
	
	$sql = "SELECT id,bairro_nome FROM bairros ORDER BY bairro_nome ASC";
	$result = $cf_conn->query($sql);
	
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
			//$cf_data["bairros"][$row["id"]] = $row["bairro_nome"];
			$cf_data["bairros"][] = [$row["id"],$row["bairro_nome"]];

		}
		
		$cf_data["msg"] = "Bairros encontrados: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "sem bairros cadastrados...";
		$cf_data["error"] = true;
	}
	
	finaliza();
}


add_action('wp_ajax_funcoesGetQuadras','getQuadras');
function getQuadras(){
	
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('fnonceGetQuadras-nonce') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Bairro selecionado, obtendo quadras...";

	if(!isset($_POST["idBairro"])){
		$cf_data["msg"] = "Bairro não definido!";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	$idBairro = intval($_POST["idBairro"]);

	
	$sql = "SELECT id,quadra_nome FROM quadras WHERE bairro_id=" . $idBairro . " ORDER BY quadra_nome ASC";
	$result = $cf_conn->query($sql);
	
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
			$cf_data["quadras"][] = [$row["id"],$row["quadra_nome"]];
			
		}
		
		$cf_data["msg"] = "Quadras encontradas: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "sem quadras cadastradas para esse bairro...";
		$cf_data["error"] = true;
	}
	
	finaliza();
	
}


add_action('wp_ajax_funcoesGetLotes','getLotes');
function getLotes(){
	
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('funcoesGetLotes-nonce') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}
	
	$cf_data["msg"] = "Bairro e quadra selecionados, obtendo lotes...";
	
	if(!isset($_POST["idQuadra"])){
		$cf_data["msg"] = "Quadra não definida!";
		$cf_data["error"] = true;
		
		finaliza();
	}

	$idQuadra = intval($_POST["idQuadra"]);
	
	$sql = "SELECT id,lote_nome FROM lotes WHERE quadra_id=" . $idQuadra . " ORDER BY lote_nome ASC";
	$result = $cf_conn->query($sql);
	
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
			$cf_data["lotes"][] = [$row["id"],$row["lote_nome"]];
			
		}
		
		$cf_data["msg"] = "Lotes encontrados: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "sem lotes cadastrados para essa quadra...";
		$cf_data["error"] = true;
	}
	
	finaliza();
}

add_action('wp_ajax_funcoesGetLocal','getLocal');
function getLocal(){
	
	global $cf_conn, $cf_data, $cf_gmaps;
	
	if(!validaPOST() || !validaNonce('funcoesGetLocal-nonce') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}
	
	$cf_data["msg"] = "Bairro, quadra e lote selecionados, obtendo lotes...";
	
	if(!isset($_POST["idLote"])){
		$cf_data["msg"] = "Lote não definido!";
		$cf_data["error"] = true;
		
		finaliza();
	}elseif(!isset($_POST["idQuadra"])){
		$cf_data["msg"] = "Quadra não definida!";
		$cf_data["error"] = true;
		
		finaliza();
	}elseif(!isset($_POST["idBairro"])){
		$cf_data["msg"] = "Bairro não definido!";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	$idUsuario = get_current_user_id();
	$idLote = intval($_POST["idLote"]);
	$idQuadra = intval($_POST["idQuadra"]);
	$idBairro = intval($_POST["idBairro"]);
	
	if(verificaConsultaDuplicadaAtiva($idLote)){
		$cf_data["msg2"] = "Usuário já realizou a consulta na última hora! Busque a consulta na tabela abaixo!";
		$cf_data["error"] = false;
		$cf_data["linkMap"] = "";
		
		
		finaliza();
	}
	
	$sql = "SELECT local FROM lotes WHERE id=" . $idLote;
	$result = $cf_conn->query($sql);
	
	if ($result->num_rows > 1 ) {
	
		$cf_data["msg"] = "Erro: mais de um lote encontrado: " . $result->num_rows;
		$cf_data["error"] = true;
	
	}elseif($result->num_rows > 0 ) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
			$cf_data["linkMap"] = $cf_gmaps . $row["local"];
			
		}
		
		$cf_data["msg"] = "Lotes encontrados: " . $result->num_rows;
		
		// Verificando se usuário pode fazer mais consultas
		
		$resCon = verificaPermissaoConsultas();
		//$resCon =[true,0,5];

		if($resCon[0]){
		
			$sql = "INSERT INTO consultas (idUsuario,bairro_id,quadra_id,lote_id) VALUES (" . 
					$idUsuario 		. "," .
					$idBairro 		. "," .
					$idQuadra 		. "," .
					$idLote 		.
					")";
			
			
			
			if($cf_conn->query($sql) === TRUE){
				$cf_data["msg"] = "Consulta registrada!";
				$cf_data["error"] = false;
				
				$cf_data["msg2"] = montaMsgConsultas($resCon[1]+1,$resCon[2]);
				
				
			}else{
				$cf_data["msg"] = "Não foi possível registrar a consulta, tente novamente! sql:" . $sql . "/erro: " . $cf_conn->error;
				$cf_data["error"] = true;
				$cf_data["linkMap"] = "";
				
				$cf_data["msg2"] = montaMsgConsultas($resCon[1],$resCon[2]);
				
			}
			
		}else{
			$cf_data["msg"] = "Número máximo de consultas atingido! Aguarde até a consulta mais antiga expirar.";
			$cf_data["error"] = true;
			$cf_data["linkMap"] = "";
				
			$cf_data["msg2"] = montaMsgConsultas($resCon[1],$resCon[2]);
		}
		
	}else {
		$cf_data["msg"] = "Não foi encontrado o lote desejado...";
		$cf_data["error"] = true;
	}
	
	finaliza();
}

function montaMsgConsultas($n,$nMax){
	return "Usuário realizou <b>" . $n . "</b> de <b>" . $nMax . "</b> consultas permitidas na última hora.";
}



function verificaValidade($horario){
	global $cf_timezone;
	
	
	date_default_timezone_set($cf_timezone);
	
	$hAtual = date_create(date("Y-m-d H:i:s"));
	
	$time = strtotime($horario); 
	$time2 = date_create(date('Y-m-d H:i:s', $time));
	
	$diff = $hAtual->diff($time2);
	
	if($diff->y >0){
		return false;
	}elseif($diff->m > 0){
		return false;
	}elseif($diff->d > 0){
		return false;
	}elseif($diff->h > 0){
		return false;
	}else{
		return true;
	}
	
	
	
}

function verificaPermissaoConsultas(){
	global $cf_conn, $cf_data, $cf_timezone, $cf_maxConsultas;
	
	
	
	// obtem máximo de consultas de acordo com assinatura
	if(current_user_is("s2member_level0")){
		$maxPermitido = $cf_maxConsultas[0];

	}elseif(current_user_is("s2member_level1")){
		$maxPermitido = $cf_maxConsultas[1];

	}elseif(current_user_is("s2member_level2")){
		$maxPermitido = $cf_maxConsultas[2];

	}elseif(current_user_is("s2member_level3")){
		$maxPermitido = $cf_maxConsultas[3];

	}elseif(current_user_is("s2member_level4")){
		$maxPermitido = $cf_maxConsultas[4];

	}elseif(current_user_is("administrator")){
		$maxPermitido = 50;

	}else{
		$cf_data["msg"] = "<p>Usuário com o perfil incorreto, " . get_current_user_id() . "</p>";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	
	// busca horário atual e range da última hora
	date_default_timezone_set($cf_timezone);
	
	$hAtual = date_create(date("Y-m-d H:i:s"));
	$hAntes = clone $hAtual;

	date_sub($hAntes,date_interval_create_from_date_string("1 hours"));

	//echo "<br><br>" . $hAtual->format("Y-m-d H:i:s") . "<br>" . $hAntes->format("Y-m-d H:i:s") . "<br>";

	$cf_data["msg"] = "Recuperando consultas da última hora...";


	
	$sql = 	"SELECT COUNT(*) FROM consultas " .
			"WHERE idUsuario=" . get_current_user_id() .
				" AND horario BETWEEN '" . $hAntes->format("Y-m-d H:i:s") . "' AND '" . $hAtual->format("Y-m-d H:i:s") . "'";
	$result = $cf_conn->query($sql);
	
	$consultasFeitas = $result->fetch_array();

	if($consultasFeitas[0] < $maxPermitido){
		return [true,$consultasFeitas[0],$maxPermitido];

	}else{
		return [false,$consultasFeitas[0],$maxPermitido];

	}
	
}

function verificaConsultaDuplicadaAtiva($idLote){
	global $cf_conn, $cf_timezone;
	
	// busca horário atual e range da última hora
	date_default_timezone_set($cf_timezone);
	
	$hAtual = date_create(date("Y-m-d H:i:s"));
	$hAntes = clone $hAtual;

	date_sub($hAntes,date_interval_create_from_date_string("1 hours"));

	$sql = 	"SELECT COUNT(*) FROM consultas " .
			"WHERE idUsuario=" . get_current_user_id() .
				" AND lote_id=" . $idLote .
				" AND horario BETWEEN '" . $hAntes->format("Y-m-d H:i:s") . "' AND '" . $hAtual->format("Y-m-d H:i:s") . "'";
	$result = $cf_conn->query($sql);
	
	
	$consultasFeitas = $result->fetch_array();
	
	if($consultasFeitas[0] >0 ){
		return true;

	}else{
		return false;
		
	}
		
}


?>
*/

?>