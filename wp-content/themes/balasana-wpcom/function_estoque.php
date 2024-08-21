<?php

global $cf_data, $cf_conn, $cf_timezone;
global $path_debug_file;
$path_debug_file = $_SERVER['DOCUMENT_ROOT'] . "/WPEstoque/bibEstoque/logs.txt";

$cf_data["msg"] = "Falhou em algo";
$cf_data["msg2"] = "";
$cf_data["error"] = true;
$cf_data["consultas"] = [];

$cf_timezone = "America/Sao_Paulo";



/*
	funções de debug
*/

function escreveDebug($msg){
	global $path_debug_file;
	
	$myfile = fopen($path_debug_file, "a") or wp_die("Unable to open file!");
	
	fwrite($myfile, date("Y-m-d H:i:s") . ": " . $msg );
	
	fclose($myfile);
	
}


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
	
	$sql = "SELECT * FROM localizacao ORDER BY localizacao.nome ASC";
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
	
	// verifica se nome já existe 
	$sql = "SELECT * from localizacao WHERE nome='" . $_POST["nome"] . "';";
	
	$result = $cf_conn->query($sql);
	
	if(!$result){
		$cf_data["msg"] = "Não foi possível alterar o local. SQL:" . $sql . "/erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	if($result->num_rows >0){// encontrou local com mesmo nome
		$row = $result->fetch_assoc();
	
		// se o local encontrado for diferente do local a ser modificado...
		if($_POST["idLocal"] != $row["id"]){
			$cf_data["msg"] = "Nome do local já existe! Favor escolher um nome distinto.";
			$cf_data["error"] = true;
			finaliza();
		}
	}
	
	
	// Se não tem o nome duplicado, faz modificação
	$sql1 = "UPDATE localizacao SET nome='". $_POST["nome"] . "'," .
			" descricao='" . $_POST["descricao"] . "' " .
			" WHERE id=" . $_POST["idLocal"];

	
	// sql para histórico de movimentações
	$obs  = "Alteração de local: " . $_POST["nome"] . PHP_EOL . "Descricao: " . $_POST["descricao"] . PHP_EOL;
	
	$sql2 = "INSERT INTO movimentacoes (idUsuario,idLocalOrigem,tipo_movimentacao,obs) " .
			"VALUES (" . get_current_user_id() . "," .
						$_POST["idLocal"] . "," .
						"'altLocal', " .
						"'" . $obs . "');";
	
	
	
	$result1 = $cf_conn->query($sql1);
	
	if(!$result1){
		$cf_data["msg"] = "Problema na atualização do local. <br>";
		$cf_data["msg2"] = $cf_conn->error . "<br>" . $sql1 . "<br>";
		$cf_data["error"] = true;
		
		escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
		
		finaliza();
	}
	
	$result2 = $cf_conn->query($sql2);
		
	if(!$result2){
		$cf_data["msg"] = "Problema no registro de movimentação da atualização do local...";
		$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
		
		finaliza();
	}
	
	
	$cf_data["msg"] = "Local atualizado.";
	$cf_data["error"] = false;
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
	
	
	// verifica se nome já existe 
	$sql = "SELECT * from localizacao WHERE nome='" . $_POST["nome"] . "';";
	
	$result = $cf_conn->query($sql);
	
	if(!$result){
		$cf_data["msg"] = "Não foi possível adicionar o novo local. SQL:" . $sql . "/erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	if($result->num_rows >0 ){
		$cf_data["msg"] = "Nome do local já existe! Favor escolher um nome distinto.";
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	// se nome ainda não existe faz inserção
	$sql1 = "INSERT INTO localizacao (id, nome, descricao) VALUES (NULL, '" . $_POST["nome"] . "', ' ". $_POST["descricao"] . "');";
	
	
	$result1 = $cf_conn->query($sql1);
	
	$novoID = $cf_conn->insert_id;
	$obs = "Novo local: ". $_POST["nome"] . PHP_EOL .  "Descricao: " . $_POST["descricao"] . PHP_EOL;
	
	// sql para histórico de movimentações
	$sql2 = "INSERT INTO movimentacoes (idUsuario,idLocalOrigem,tipo_movimentacao,obs) " .
			"VALUES (" . get_current_user_id() . "," .
						$novoID . "," .
						"'addLocal', " .
						"'" . $obs . "');";
	
	
	if(!$result1){
		$cf_data["msg"] = "Não foi possível adicionar o novo local. SQL:" . $sql . "/erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
	}
	
	
	$result2 = $cf_conn->query($sql2);
		
	if(!$result2){
		$cf_data["msg"] = "Problema no registro de movimentação na adição do local...";
		$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
		
		finaliza();
	}
	
	
	// se tudo deu certo
	$cf_data["msg"] = "Local adicionado!";
	$cf_data["error"] = false;
	
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
	
	$sql = "SELECT * FROM localizacao ORDER BY localizacao.nome ASC";
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
	
	$sql = "SELECT * FROM item ORDER BY item.nome ASC";
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


	$cf_data["msg"] = "Adicionando item...";
	
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
	
	
	// verifica se existe um item com o mesmo nome
	$sql = "SELECT * from item WHERE nome='" . $_POST["nome"] . "';";
	
	$result = $cf_conn->query($sql);
	
	if(!$result){
		$cf_data["msg"] = "Não foi possível adicionar o novo item. SQL:" . $sql . "/erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	if($result->num_rows >0 ){
		$cf_data["msg"] = "Nome do item já existe! Favor escolher um nome distinto.";
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	// se o nome não existe, fazer inserção
	$sql1 = "INSERT INTO item (id, nome, tipo, descricao, link_manual, link_imagem ) ". 
			"VALUES (NULL, '" . $_POST["nome"] . "', ".
						"'". $_POST["tipo"] . "', " .
						"'". $_POST["descricao"] . "', ".
						"'". $_POST["datasheet"] . "', ".
						"'". $_POST["img"] . "');";
	
	$result1 = $cf_conn->query($sql1);
	
	$novoID = $cf_conn->insert_id;
	$obs = "Novo item: ". $_POST["nome"] . PHP_EOL .  
			"Descricao: " . $_POST["descricao"] . PHP_EOL . 
			"img: " . $_POST["img"] . PHP_EOL . 
			"datasheet: " . $_POST["datasheet"] . PHP_EOL;
	
	// sql para histórico de movimentações
	$sql2 = "INSERT INTO movimentacoes (idUsuario,idItem,tipo_movimentacao,obs) " .
			"VALUES (" . get_current_user_id() . "," .
						$novoID . "," .
						"'addItem', " .
						"'" . $obs . "');";

	if(!$result1){
		$cf_data["msg"] = "Não foi possível adicionar o novo item. SQL:" . $sql . "/erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
	}
	
	
	$result2 = $cf_conn->query($sql2);
		
	if(!$result2){
		$cf_data["msg"] = "Problema no registro de movimentação na adição do item...";
		$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
		
		finaliza();
	}
	
	
	// se tudo deu certo
	$cf_data["msg"] = "Item adicionado!";
	$cf_data["error"] = false;
	
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

	$cf_data["msg"] = "Atualizando item...";
	
	// verifica se nome já existe 
	$sql = "SELECT * from item WHERE nome='" . $_POST["nome"] . "';";
	
	$result = $cf_conn->query($sql);
	
	if(!$result){
		$cf_data["msg"] = "Não foi possível alterar o item. SQL:" . $sql . "/erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	if($result->num_rows >0){// encontrou item com mesmo nome
		$row = $result->fetch_assoc();
	
		// se o item encontrado for diferente do item a ser modificado...
		if($_POST["idItem"] != $row["id"]){
			$cf_data["msg"] = "Nome do item já existe! Favor escolher um nome distinto.";
			$cf_data["error"] = true;
			finaliza();
		}
	}
	
	// Se não tem o nome duplicado, faz modificação
	$sql1 = "UPDATE item SET ". 
				"nome='". $_POST["nome"] . "', ".
				"tipo='". $_POST["tipo"] . "', " .
				"descricao='". $_POST["descricao"] . "', ".
				"link_manual='". $_POST["datasheet"] . "', ".
				"link_imagem='". $_POST["img"] . "' ".
				" WHERE id=" . $_POST["idItem"] . ";";
	
	
	// sql para histórico de movimentações
	$obs = "Altera item: ". $_POST["nome"] . PHP_EOL .  
			"Descricao: " . $_POST["descricao"] . PHP_EOL . 
			"img: " . $_POST["img"] . PHP_EOL . 
			"datasheet: " . $_POST["datasheet"] . PHP_EOL;
	
	$sql2 = "INSERT INTO movimentacoes (idUsuario,idItem,tipo_movimentacao,obs) " .
			"VALUES (" . get_current_user_id() . "," .
						$_POST["idItem"] . "," .
						"'altItem', " .
						"'" . $obs . "');";
	
	
	
	$result1 = $cf_conn->query($sql1);
	
	if(!$result1){
		$cf_data["msg"] = "Problema na atualização do item. <br>";
		$cf_data["msg2"] = $cf_conn->error . "<br>" . $sql1 . "<br>";
		$cf_data["error"] = true;
		
		escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
		
		finaliza();
	}
	
	$result2 = $cf_conn->query($sql2);
		
	if(!$result2){
		$cf_data["msg"] = "Problema no registro de movimentação da atualização do item...";
		$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
		
		finaliza();
	}
	
	
	$cf_data["msg"] = "Item atualizado.";
	$cf_data["error"] = false;
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
	
	$sql = "SELECT e.*, i.itemNome,i.itemTipo,l.localNome, i.itemDescricao, i.link_manual,i.link_imagem from estoque e ".
			"INNER JOIN (SELECT id, nome as itemNome, tipo as itemTipo, descricao as itemDescricao, link_manual, link_imagem  from item) i ON e.iditem=i.id ".
			"INNER JOIN (SELECT id, nome as localNome from localizacao ) l ON e.idLocal=l.id;";
	$result = $cf_conn->query($sql);
	
	//print($sql);
	
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
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
										$row["itemDescricao"],
										$row["link_manual"],
										$row["link_imagem"],
										$row["id"]
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

add_action('wp_ajax_get1Estoque','get1Estoque');
function get1Estoque(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_get1Estoque') || !validaUsuario() || !conecta()){ 
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Recuperando 1 estoque...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	$cf_data["encontrado"] = false;
	
	
	// Buscando o tipo do item selecionado
	$sql = "SELECT * FROM item WHERE id=" . $_POST["idItem"] .";";
	
	$result = $cf_conn->query($sql);
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	if($result->num_rows == 1 ) {
		
		$row = $result->fetch_assoc();
		
		$tipoItem = $row["tipo"];
		$cf_data["tipo"] = $row["tipo"];

	}else {
		$cf_data["msg"] = "Nenhum Item com este ID foi encontrado ou multiplicado...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	// Buscando estoque
	$sql = "SELECT * FROM estoque WHERE iditem=" . $_POST["idItem"] .
										" AND idLocal=" . $_POST["idLocal"] . ";";
	$result = $cf_conn->query($sql);
	
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	if($tipoItem === "Consumo"){
		
		if($result->num_rows > 0 ) {
			$cf_data["encontrado"] = true;
			
			$row = $result->fetch_assoc();
			$cf_data["consultas"] = [
										$row["iditem"],
										$row["idLocal"], 
										$row["qt"], 
										$row["qtEmprestada"], 
										$row["patrimonio"]
									];
			
		}
		
		finaliza();
	
	}elseif($tipoItem === "Permanente"){
		
		if($result->num_rows > 0 ) {
			$cf_data["encontrado"] = true;
			
			while($row = $result->fetch_assoc()){
				$cf_data["consultas"][] = [
											$row["iditem"],
											$row["idLocal"], 
											$row["qt"], 
											$row["qtEmprestada"], 
											$row["patrimonio"]
										];
			}
		}
		
		finaliza();
		
	}else{
		$cf_data["msg"] = "Problema no tipo do item...";
		$cf_data["msg2"]= "tipo: " . $tipoItem;
		$cf_data["error"] = true;
		finaliza();
	}

	
	// não deveria chegar aqui
	$cf_data["msg"] = "Chegou onde não devia, função alteraEstoque...";
	$cf_data["error"] = true;
	finaliza();
}

add_action('wp_ajax_alteraEstoque','alteraEstoque');
function alteraEstoque(){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_alteraEstoque') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Alterando 1 estoque...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	
	// verifica se o item existe
	$sql = "SELECT * FROM item WHERE id=" . $_POST["idItem"] .";";
	
	$result = $cf_conn->query($sql);
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	if($result->num_rows == 1 ) {
		
		$row = $result->fetch_assoc();
		
		$tipoItem = $row["tipo"];
		
		if($tipoItem !== $_POST["tipo"]){
			$cf_data["msg"] = "O tipo de item enviado não é igual ao tipo de item no banco de dados...";
			
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		

	}else {
		$cf_data["msg"] = "Nenhum Item com este ID foi encontrado...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	// verifica se o local existe
	$sql = "SELECT * FROM localizacao WHERE id=" . $_POST["idLocal"] .";";
	
	$result = $cf_conn->query($sql);
	
	if(!$result || $result->num_rows != 1 ) {
		$cf_data["msg"] = "Nenhum Local com este ID foi encontrado...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	// Chama a função adequada de acordo com a operação
	// as funções chamadas vão finalizar a execução
	if($_POST["op"] === "Adicionar"){
		addEstoque($_POST);
	}
	
	
	if($_POST["op"] === "Movimentar"){
		movEstoque($_POST);
	}
	
	if($_POST["op"] === "Remover"){
		remEstoque($_POST);
		
	}
	
	
	// não deveria chegar aqui
	$cf_data["msg"] = "Chegou onde não devia, função alteraEstoque...";
	$cf_data["error"] = true;
	finaliza();
}


function addEstoque($post){
	global $cf_conn, $cf_data;


	if($post["tipo"] === "Consumo"){
		
		// Buscando estoque
		$sql = "SELECT * FROM estoque WHERE iditem=" . $post["idItem"] .
											" AND idLocal=" . $post["idLocal"] . ";";
		$result = $cf_conn->query($sql);
		
		if(!$result){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// se existirem múltiplas entradas, tem erro no BD
		if($result->num_rows > 1) {
			$cf_data["msg"] = "O estoque a ser adiciona já existe e está multiplicado...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Linhas encontradas: " . $result->num_rows;
			$cf_data["error"] = true;
			finaliza();
		}
		
		
		// não existe este estoque registrado
		if($result->num_rows == 0 ) {
			
			$sql1 = "INSERT INTO estoque (iditem,idLocal,qt) ".
					" VALUES (" . $post["idItem"] . "," .
								 $post["idLocal"] . "," .
								 $post["addQt"] . ");";
			
		}else{ // existe estoque registrado
			$sql1 = "UPDATE estoque SET qt = qt + " . $post["addQt"] . 
					" WHERE iditem=" . $post["idItem"] .
										" AND idLocal=" . $post["idLocal"] . ";";
		}
		
		
		// sql para histórico de movimentações
		$sql2 = "INSERT INTO movimentacoes (idUsuario,idItem,idLocalOrigem,idLocalDestino,qt,tipo_movimentacao,obs) " .
				"VALUES (" . get_current_user_id() . "," .
							$post["idItem"] . "," .
							$post["idLocal"] . "," .
							$post["idLocal"] . "," .
							$post["addQt"]. "," .
							"'adicao', " .
							"'" . $post["obs"] . "');";
		
		$result1 = $cf_conn->query($sql1);
		
		if(!$result1){
			$cf_data["msg"] = "Problema na adição de estoque...";
			$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
			
			finaliza();
		}

		$result2 = $cf_conn->query($sql2);
		
		if(!$result2){
			$cf_data["msg"] = "Problema na adição da movimentação...";
			$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
			
			finaliza();
		}
		
		finaliza();
		
	}elseif($post["tipo"] === "Permanente"){
		// Buscando estoque
		$sql = "SELECT * FROM estoque WHERE patrimonio=" . $post["addPatr"] . ";";
		$result = $cf_conn->query($sql);
		
		if(!$result){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
			
		// não existe este patrimônio no estoque
		if($result->num_rows == 0 ) {
			
			$sql1 = "INSERT INTO estoque (iditem,idLocal,qt,patrimonio) ".
					" VALUES (" . $post["idItem"] . "," .
								 $post["idLocal"] . "," .
								 "1," .
								 $post["addPatr"] . ");";
			
		}else{ // existe estoque registrado com este patrimônio, finaliza!
			$cf_data["msg"] = "Este patrimônio já está registrado...";
			$cf_data["error"] = true;
			finaliza();
		}
		
		// sql para histórico de movimentações
		$sql2 = "INSERT INTO movimentacoes (idUsuario,idItem,idLocalOrigem,idLocalDestino,patrimonio,qt,tipo_movimentacao,obs) " .
				"VALUES (" . get_current_user_id() . "," .
							$post["idItem"] . "," .
							$post["idLocal"] . "," .
							$post["idLocal"] . "," .
							$post["addPatr"] . "," .
							"1," .
							"'adicao', " .
							"'" . $post["obs"] . "');";
		
		$result1 = $cf_conn->query($sql1);
		
		if(!$result1){
			$cf_data["msg"] = "Problema na adição de estoque...";
			$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
			
			finaliza();
		}

		$result2 = $cf_conn->query($sql2);
		
		if(!$result2){
			$cf_data["msg"] = "Problema na adição da movimentação...";
			$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
			
			finaliza();
		}
		
		finaliza();
		
	}
	
	// não deveria chegar aqui
	$cf_data["msg"] = "Chegou onde não devia, função addEstoque...";
	$cf_data["error"] = true;
	finaliza();
	
}


function movEstoque($post){
	global $cf_conn, $cf_data;

	if($post["tipo"] === "Consumo"){
		
		// Buscando estoque
		$sql1 = "SELECT * FROM estoque WHERE iditem=" . $post["idItem"] .
											" AND idLocal=" . $post["idLocal"] . ";";
		$result1 = $cf_conn->query($sql1);
		
		if(!$result1){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql1 . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// não existe este estoque registrado
		if($result1->num_rows == 0 ) {
			$cf_data["msg"] = "Problema na movimentação de estoque...<br>Estoque de origem sem registro...";
			$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			finaliza();
		}
		
		// estoque na origem multiplicado, problema no BD
		if($result1->num_rows > 1) {
			$cf_data["msg"] = "O estoque de origem está multiplicado...";
			$cf_data["msg2"]= "SQL: " . $sql1 . " <br> Linhas encontradas: " . $result1->num_rows;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// qt que sobrar não pode ser menor que o estoque emprestado
		$row = $result1->fetch_assoc();
		$est_result = $row["qt"]-$row["qtEmprestada"]-$post["movQt"];
		if($est_result < 0){
			$cf_data["msg"] = "Problema no quantitativo a mover...";
			$cf_data["msg2"]= "Estoque resultante será negativo: ". $est_result;
			$cf_data["error"] = true;
			finaliza();
		}
		
		
		// verifica estoque no destino 
		$sql2 = "SELECT * FROM estoque WHERE iditem=" . $post["idItem"] .
											" AND idLocal=" . $post["movLocalId"] . ";";
		$result2 = $cf_conn->query($sql2);
		
		
		if(!$result2){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql2 . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		
		
		// Prepara SQL para remoção de estoque
		// se existe estoque na origem, prepara sql.
		// ao executar movimentação, se não tem nada emprestado, estoque vai a zero e podemos eliminar a linha
		if($row["qt"]-$post["movQt"] == 0 && $row["qtEmprestada"] == 0){
			$sql1 = "DELETE from estoque WHERE id=" . $row["id"] . ";";
		}else{ // caso contrário, só atualiza row do BD
			$sql1 = "UPDATE estoque SET qt = qt - " . $post["movQt"] . 
					" WHERE iditem=" . $post["idItem"] .
							" AND idLocal=" . $post["idLocal"] . ";";
		}
		
		// Se existe ou não estoque no destino, é necessário SQLs diferentes
		// se tem múltiplas entradas, BD com problema!
		if($result2->num_rows > 1) {
			$cf_data["msg"] = "O estoque de destino está multiplicado...";
			$cf_data["msg2"]= "SQL: " . $sql2 . " <br> Linhas encontradas: " . $result2->num_rows;
			$cf_data["error"] = true;
			finaliza();
		}
		// estoque no destino não existe
		if($result2->num_rows == 0 ) {
			$sql2 = "INSERT INTO estoque (iditem,idLocal,qt) ".
						" VALUES (" . $post["idItem"] . "," .
									 $post["movLocalId"] . "," .
									 $post["movQt"] . ");";
		}else{// estoque já existe, então atualiza
			$sql2 = "UPDATE estoque SET qt = qt + " . $post["movQt"] . 
				" WHERE iditem=" . $post["idItem"] .
									" AND idLocal=" . $post["movLocalId"] . ";";
		}
		
		// sql para histórico de movimentações
		$sql3 = "INSERT INTO movimentacoes (idUsuario,idItem,idLocalOrigem,idLocalDestino,qt,tipo_movimentacao,obs) " .
				"VALUES (" . get_current_user_id() . "," .
							$post["idItem"] . "," .
							$post["idLocal"] . "," .
							$post["movLocalId"] . "," .
							$post["movQt"]. "," .
							"'movimentacao', " .
							"'" . $post["obs"] . "');";
		
		$result1 = $cf_conn->query($sql1);
		
		if(!$result1){
			$cf_data["msg"] = "Problema na movimentação de estoque na origem...";
			$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL . "NÃO FEZ 3:" . $sql3 . PHP_EOL);
			
			finaliza();
		}

		$result2 = $cf_conn->query($sql2);
		
		if(!$result2){
			$cf_data["msg"] = "Problema na movimentação de estoque no destino...";
			$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL . "NÃO FEZ 3:" . $sql3 . PHP_EOL);
			
			finaliza();
		}
		
		$result3 = $cf_conn->query($sql3);
		
		if(!$result3){
			$cf_data["msg"] = "Problema na adição da movimentação...";
			$cf_data["msg2"]= "SQL: " . $sql3  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "FEZ 2:" . $sql2 . PHP_EOL . "NÃO FEZ 3:" . $sql3 . PHP_EOL);
			
			finaliza();
		}
		
		finaliza();
	
	}elseif($post["tipo"] === "Permanente"){
		// Buscando estoque
		$sql = "SELECT * FROM estoque WHERE patrimonio=" . $post["movPatr"] . ";";
		$result = $cf_conn->query($sql);
		
		if(!$result){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// não existe este estoque registrado então não pode ser removido! (ou existem múltiplos estoque e tem algo errado no BD
		if($result->num_rows != 1 ) {
			$cf_data["msg"] = "O estoque a ser movido não existe ou está multiplicado...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Linhas encontradas: " . $result->num_rows;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// sql para remover patrimonio do estoque atual
		$row = $result->fetch_assoc();
		if($row["qtEmprestada"]>0){
			$cf_data["msg"] = "Este patrimônio está emprestado, precisa ser devolvido primeiro...";
			$cf_data["error"] = true;
			finaliza();
		}
		
		// remove estoque atual
		$sql1 = "DELETE FROM estoque WHERE id=" . $row["id"] . ";"; 
		
		// cria novo estoque 
		$sql2 = "INSERT INTO estoque (iditem,idLocal,qt,patrimonio) ".
					" VALUES (" . $post["idItem"] . "," .
								 $post["movLocalId"] . "," .
								 "1," .
								 $post["movPatr"] . ");";
		
		// sql para histórico de movimentações
		$sql3 = "INSERT INTO movimentacoes (idUsuario,idItem,idLocalOrigem,idLocalDestino,patrimonio,qt,tipo_movimentacao,obs) " .
				"VALUES (" . get_current_user_id() . "," .
							$post["idItem"] . "," .
							$post["idLocal"] . "," .
							$post["movLocalId"] . "," .
							$post["movPatr"] . "," .
							"1," .
							"'movimentacao', " .
							"'" . $post["obs"] . "');";
		
		$result1 = $cf_conn->query($sql1);
		
		if(!$result1){
			$cf_data["msg"] = "Problema na remoção de estoque durante movimentação...";
			$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL . "NÃO FEZ 3:" . $sql3 . PHP_EOL);
			
			finaliza();
		}
		
		$result2 = $cf_conn->query($sql2);
		
		if(!$result2){
			$cf_data["msg"] = "Problema na adição de estoque durante a movimentação...";
			$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL . "NÃO FEZ 3:" . $sql3 . PHP_EOL);
			
			finaliza();
		}
		
		$result3 = $cf_conn->query($sql3);
		
		if(!$result3){
			$cf_data["msg"] = "Problema na adição do registro de movimentação durante a movimentação...";
			$cf_data["msg2"]= "SQL: " . $sql3  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "FEZ 2:" . $sql2 . PHP_EOL . "NÃO FEZ 3:" . $sql3 . PHP_EOL);
			
			finaliza();
		}
		
		finaliza();
	}
	
	// não deveria chegar aqui
	$cf_data["msg"] = "Chegou onde não devia, função movEstoque...";
	$cf_data["error"] = true;
	finaliza();
	
}

function remEstoque($post){
	global $cf_conn, $cf_data;


	if($post["tipo"] === "Consumo"){
		
		// Buscando estoque
		$sql = "SELECT * FROM estoque WHERE iditem=" . $post["idItem"] .
											" AND idLocal=" . $post["idLocal"] . ";";
		$result = $cf_conn->query($sql);
		
		if(!$result){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// não existe este estoque registrado então não pode ser removido! (ou existem múltiplos estoque e tem algo errado no BD
		if($result->num_rows != 1 ) {
			$cf_data["msg"] = "O estoque a ser removido não existe ou está multiplicado...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Linhas encontradas: " . $result->num_rows;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// verifica se é possível remover a qt desejada
		$row = $result->fetch_assoc();
		$est_result = $row["qt"]-$row["qtEmprestada"]-$post["remQt"];
		if($est_result < 0){
			$cf_data["msg"] = "Problema no quantitativo a remover...";
			$cf_data["msg2"]= "Estoque resultante será negativo: ". $est_result;
			$cf_data["error"] = true;
			finaliza();
		}
		
		
		// ao executar remoção, como não tem nada emprestado, estoque vai a zero e podemos eliminar a linha
		// Prepara SQL para remoção de estoque
		if($row["qt"]-$post["remQt"] == 0 && $row["qtEmprestada"] == 0){
			$sql1 = "DELETE from estoque WHERE id=" . $row["id"] . ";";
		}else{ // caso contrário, só atualiza row do DB
			$sql1 = "UPDATE estoque SET qt = qt - " . $post["remQt"] . 
					" WHERE iditem=" . $post["idItem"] .
										" AND idLocal=" . $post["idLocal"] . ";";
		}

		// sql para histórico de movimentações
		$sql2 = "INSERT INTO movimentacoes (idUsuario,idItem,idLocalOrigem,idLocalDestino,qt,tipo_movimentacao,obs) " .
				"VALUES (" . get_current_user_id() . "," .
							$post["idItem"] . "," .
							$post["idLocal"] . "," .
							$post["idLocal"] . "," .
							$post["remQt"]. "," .
							"'baixa', " .
							"'" . $post["obs"] . "');";
		
		$result1 = $cf_conn->query($sql1);
		
		if(!$result1){
			$cf_data["msg"] = "Problema na remoção de estoque...";
			$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
			
			finaliza();
		}

		$result2 = $cf_conn->query($sql2);
		
		if(!$result2){
			$cf_data["msg"] = "Problema na adição da movimentação...";
			$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
			
			finaliza();
		}
		
		finaliza();
		
		
	}elseif($post["tipo"] === "Permanente"){
		// Buscando estoque
		$sql = "SELECT * FROM estoque WHERE patrimonio='" . $post["remPatr"] . "';";
		$result = $cf_conn->query($sql);
		
		if(!$result){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// não existe este estoque registrado então não pode ser removido! (ou existem múltiplos estoque e tem algo errado no BD
		if($result->num_rows != 1 ) {
			$cf_data["msg"] = "O estoque a ser removido não existe ou está multiplicado...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Linhas encontradas: " . $result->num_rows;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// verifica se é possível remover a qt desejada
		$row = $result->fetch_assoc();
		$est_result = $row["qt"]-$row["qtEmprestada"]-$post["remQt"];
		if($est_result < 0){
			$cf_data["msg"] = "Problema no quantitativo a remover...";
			$cf_data["msg2"]= "Estoque resultante será negativo: ". $est_result;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// Prepara SQL para remoção de estoque
		$sql1 = "DELETE FROM estoque WHERE id=" . $row["id"] . ";"; 
		

		// sql para histórico de movimentações
		$sql2 = "INSERT INTO movimentacoes (idUsuario,idItem,idLocalOrigem,idLocalDestino,patrimonio,qt,tipo_movimentacao,obs) " .
				"VALUES (" . get_current_user_id() . "," .
							$post["idItem"] . "," .
							$post["idLocal"] . "," .
							$post["idLocal"] . "," .
							$post["remPatr"] . "," .
							$post["remQt"]. "," .
							"'baixa', " .
							"'" . $post["obs"] . "');";
		
		$result1 = $cf_conn->query($sql1);
		
		if(!$result1){
			$cf_data["msg"] = "Problema na remoção de estoque...";
			$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
			
			finaliza();
		}

		$result2 = $cf_conn->query($sql2);
		
		if(!$result2){
			$cf_data["msg"] = "Problema na adição da movimentação...";
			$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
			
			finaliza();
		}
		
		finaliza();

	}
	
	
	// não deveria chegar aqui
	$cf_data["msg"] = "Chegou onde não devia, função remEstoque...";
	$cf_data["error"] = true;
	finaliza();
	
}



?>