<?php

global $cf_data, $cf_conn, $cf_timezone;

$cf_data["msg"] = "Falhou em algo";
$cf_data["msg2"] = "";
$cf_data["error"] = true;
$cf_data["consultas"] = [];

$cf_timezone = "America/Sao_Paulo";

/*
	funções de validação 
*/

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
	
	// esta função encerra a chamada para o servidor PHP independente de onde estiver no código
	wp_die();
}



/*
	funções relacionadas ao local 
*/


add_action('wp_ajax_getLocais','getLocais');
function getLocais(){
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

add_action('wp_ajax_get1Local','get1Local');
function get1Local(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_get_1_Local') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Recuperando consulta...";
	
	$sql = "SELECT * FROM localizacao WHERE id=" . $_POST["idLocal"];
	$result = $cf_conn->query($sql);
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
				$cf_data["consultas"] = [
										$row["id"], // id
										$row["nome"], // nome curto
										$row["descricao"], // descricao
										];
		}
		
		$cf_data["msg"] = "Local encontrado: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "Nenhum local com este ID foi encontrado...";
		$cf_data["error"] = true;
	}
	
	finaliza();
}

add_action('wp_ajax_alteraLocal','alteraLocal');
function alteraLocal(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_alteraLocal') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Atualizando local...";
	
	$sql = "UPDATE localizacao SET nome='". $_POST["nome"] . "'," .
			" descricao='" . $_POST["descricao"] . "' " .
			" WHERE id=" . $_POST["idLocal"];
	
	
	if($cf_conn->query($sql) === TRUE){
		
		$cf_data["msg"] = "Local atualizado.";
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "Problema na atualização do local. <br>";
		$cf_data["msg2"] = $cf_conn->error . "<br>" . $sql . "<br>";
		$cf_data["error"] = true;
	}
	
	finaliza();
}

add_action('wp_ajax_addLocal','addLocal');
function addLocal(){
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
	funções relacionadas ao item 
*/

add_action('wp_ajax_getItens','getItens');
function getItens(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_getItens') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Recuperando consultas...";
	
	$sql = "SELECT * FROM item ORDER BY item.nome DESC";
	$result = $cf_conn->query($sql);
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
				$cf_data["consultas"][] = [
										$row["id"], // id
										$row["nome"], // nome curto
										$row["descricao"], // descricao
										$row["tipo"], // tipo
										$row["link_manual"], // link manual
										$row["link_imagem"] // link imagem 
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


add_action('wp_ajax_addItem','addItem');
function addItem(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_addItem') || !validaUsuario() || !conecta()){ 
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
	}elseif(!isset($_POST["tipo"])){
		$cf_data["msg"] = "Tipo de item não definido!";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	
	$sql = "INSERT INTO item (id, nome, tipo, descricao, link_manual, link_imagem ) ". 
			"VALUES (NULL, '" . $_POST["nome"] . "', ".
						"'". $_POST["tipo"] . "', " .
						"'". $_POST["descricao"] . "', ".
						"'". $_POST["datasheet"] . "', ".
						"'". $_POST["img"] . "');";
	 
	
	if($cf_conn->query($sql) === TRUE){
		$cf_data["msg"] = "Item adicionado!";
		$cf_data["error"] = false;
		
	}else{
		$cf_data["msg"] = "Não foi possível adicionar o novo item. SQL:" . $sql . "/erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
	}

	finaliza();
}

add_action('wp_ajax_get1Item','get1Item');
function get1Item(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_get1Item') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Recuperando consulta...";
	
	$sql = "SELECT * FROM item WHERE id=" . $_POST["idItem"];
	$result = $cf_conn->query($sql);
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
				$cf_data["consultas"] = [
										$row["id"], // id
										$row["nome"], // nome curto
										$row["descricao"], // descricao
										$row["tipo"], // tipo
										$row["link_manual"], // link manual
										$row["link_imagem"] // link imagem
										];
		}
		
		$cf_data["msg"] = "Item encontrado: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "Nenhum Item com este ID foi encontrado...";
		$cf_data["msg2"]= $cf_conn->error;
		$cf_data["error"] = true;
	}
	
	finaliza();
}

add_action('wp_ajax_alteraItem','alteraItem');
function alteraItem(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_alteraItem') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Atualizando local...";
	
	$sql = "UPDATE item SET ". 
				"nome='". $_POST["nome"] . "', ".
				"tipo='". $_POST["tipo"] . "', " .
				"descricao='". $_POST["descricao"] . "', ".
				"link_manual='". $_POST["datasheet"] . "', ".
				"link_imagem='". $_POST["img"] . "' ".
				" WHERE id=" . $_POST["idItem"] . ";";
	
	if($cf_conn->query($sql) === TRUE){
		
		$cf_data["msg"] = "Item atualizado.";
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "Problema na atualização do item. <br>";
		$cf_data["msg2"] = $cf_conn->error . "<br>" . $sql . "<br>";
		$cf_data["error"] = true;
	}
	
	finaliza();
}

/*
	funções relacionadas ao estoque 
*/

add_action('wp_ajax_getEstoque','getEstoque');
function getEstoque(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_getEstoque') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Recuperando consultas...";
	
	$sql = "SELECT e.*, i.itemNome,i.itemTipo,l.localNome from estoque e ".
			"INNER JOIN (SELECT id, nome as itemNome, tipo as itemTipo from item) i ON e.iditem=i.id ".
			"INNER JOIN (SELECT id, nome as localNome from localizacao ) l ON e.idLocal=l.id;";
	$result = $cf_conn->query($sql);
	
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
				$cf_data["consultas"][] = [
										$row["iditem"], // id
										$row["idLocal"], // nome curto
										$row["qt"], // descricao
										$row["qtEmprestada"], // tipo
										$row["patrimonio"], // link manual
										$row["itemNome"], 
										$row["itemTipo"], 
										$row["localNome"], 
										];
		}
		
		$cf_data["msg"] = "Consultas encontradas: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "Nenhum estoque foi cadastrado...";
		$cf_data["error"] = true;
	}
	
	
	finaliza();
}


?>