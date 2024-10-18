<?php

// cabeçalho para servidor localhost
require $_SERVER['DOCUMENT_ROOT'] . '/private/serverdata.php';

global $path_debug_file;
$path_debug_file = $_SERVER['DOCUMENT_ROOT'] . "/WPEstoque/bibEstoque/logs.txt";

// cabeçalho para servidor UFSC
/*

require $_SERVER['DOCUMENT_ROOT'] . '/../private/serverdata.php';

global $path_debug_file;
$path_debug_file = $_SERVER['DOCUMENT_ROOT'] . "../private/logs.txt";

*/


global $cf_data, $cf_conn, $cf_timezone;

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
	
	global $servername, $username, $password, $dbname, $port;
	
	
	$cf_conn = new mysqli($servername, $username, $password,$dbname,$port);
	
	//printf("Current character set: %s\n", $cf_conn->character_set_name());
	$cf_conn -> set_charset("utf8");
	
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
	
	//$sql = "SELECT * FROM item ORDER BY item.nome ASC";
	$sql = "SELECT i.*, e.total, e.totalEmpr from item as i " . 
				"INNER JOIN (SELECT iditem, sum(qt) as total, sum(qtEmprestada) as totalEmpr from estoque group by iditem) e ON i.id = e.iditem;";
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
										$row["link_imagem"], // link imagem 
										$row["total"], // qt total do item em estoque 
										$row["totalEmpr"] // qt total emprestada do item
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
	$cf_data["msg"] = "Chegou onde não devia, operação indicada errada: " . $_POST["op"] . "/função alteraEstoque...";
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
			$cf_data["msg"] = "O estoque a ser adicionado já existe e está multiplicado...";
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
		$sql2 = "INSERT INTO movimentacoes (idUsuario,idItem,idLocalDestino,qt,tipo_movimentacao,obs) " .
				"VALUES (" . get_current_user_id() . "," .
							$post["idItem"] . "," .
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
		// Primeiro verifica se patrimônio já está registrado
		$sql = "SELECT * FROM estoque WHERE patrimonio='" . $post["addPatr"] . "';";
		$result = $cf_conn->query($sql);
		
		if(!$result){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// se existe este patrimônio no estoque, verifica se a qt é zero
		if($result->num_rows >= 1 ) {
			
			$qtTotal = 0;
			while($row = $result->fetch_assoc()) {
				
				$id = $row["id"];
				$iditem = $row["iditem"];
				$idlocal = $row["idLocal"];
				$qt_est = $row["qt"];
				
				// se já tem estoque registrado com este patrimônio, não pode adicionar duplicado.
				if($qt_est > 0){
					$cf_data["msg"] = "Este patrimônio já está registrado com estoque não nulo...";
					$cf_data["error"] = true;
					finaliza();
				}
				
				//combinação local/item/patrimonio já tem registro, mas com estoque 0, então só atualiza estoque
				if($iditem == $post["idItem"] && $idlocal == $post["idLocal"]){
					
					$sql1 = "UPDATE estoque SET qt = 1 WHERE id =" . $id . ";";
					
				}
			}
			
			
		}else{ // se não existe estoque registrado com este patrimônio
			
			$sql1 = "INSERT INTO estoque (iditem,idLocal,qt,patrimonio) ".
							" VALUES (" . $post["idItem"] . "," .
										 $post["idLocal"] . "," .
										 "1," .
										 $post["addPatr"] . ");";
		
		}
		
		// sql para histórico de movimentações
		$sql2 = "INSERT INTO movimentacoes (idUsuario,idItem,idLocalDestino,patrimonio,qt,tipo_movimentacao,obs) " .
				"VALUES (" . get_current_user_id() . "," .
							$post["idItem"] . "," .
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
		
		// Se existe ou não estoque no destino, é necessário SQLs diferentes
		// se tem múltiplas entradas, BD com problema!
		if($result2->num_rows > 1) {
			$cf_data["msg"] = "O estoque de destino está multiplicado...";
			$cf_data["msg2"]= "SQL: " . $sql2 . " <br> Linhas encontradas: " . $result2->num_rows;
			$cf_data["error"] = true;
			finaliza();
		}
		
		
		
		
		// Prepara SQL para atualização do estoque na origem
		$sql1 = "UPDATE estoque SET qt = qt - " . $post["movQt"] . 
					" WHERE iditem=" . $post["idItem"] .
							" AND idLocal=" . $post["idLocal"] . ";";
		

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
		// Buscando estoque com o patrimonio e estoque 1
		$sql = "SELECT * FROM estoque WHERE patrimonio=" . $post["movPatr"] . " AND qt = 1;";
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
		
		// verifica se o item está emprestado, se sim, não pode mover.
		$row = $result->fetch_assoc();
		if($row["qtEmprestada"]>0){
			$cf_data["msg"] = "Este patrimônio está emprestado, precisa ser devolvido primeiro...";
			$cf_data["error"] = true;
			finaliza();
		}
		
		
		// sql para remover patrimonio do estoque atual
		$sql1 = "UPDATE estoque SET qt = 0 WHERE id=" . $row["id"] . ";"; 
		
		
		// verifica se o estoque de destino existe 
		$sql = "SELECT * FROM estoque WHERE patrimonio=" . $post["movPatr"] . " AND idLocal = " . $post["movLocalId"] . ";";
		$result2 = $cf_conn->query($sql);
		
		if(!$result2){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// existem múltiplos estoques de destinos com o mesmo patrimônio, algo de errado no BD
		if($result2->num_rows > 1 ) {
			$cf_data["msg"] = "O estoque de destino está multiplicado...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Linhas encontradas: " . $result2->num_rows;
			$cf_data["error"] = true;
			finaliza();
		}
		
		
		// estoque de destino já existe
		if($result2->num_rows == 1){
			$row = $result2->fetch_assoc();
			$sql2 = "UPDATE estoque SET qt = 1 WHERE id=" . $row["id"] . ";";

		}else{// estoque de destino não existe
			// cria novo estoque 
			$sql2 = "INSERT INTO estoque (iditem,idLocal,qt,patrimonio) ".
						" VALUES (" . $post["idItem"] . "," .
									 $post["movLocalId"] . "," .
									 "1," .
									 $post["movPatr"] . ");";
									 
		}
			
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
		
		

		// Prepara SQL para atualização do estoque (estoque com valor 0 nunca é removido para manter histórico)
		$sql1 = "UPDATE estoque SET qt = qt - " . $post["remQt"] . 
					" WHERE iditem=" . $post["idItem"] .
										" AND idLocal=" . $post["idLocal"] . ";";
		

		// sql para histórico de movimentações
		$sql2 = "INSERT INTO movimentacoes (idUsuario,idItem,idLocalOrigem,qt,tipo_movimentacao,obs) " .
				"VALUES (" . get_current_user_id() . "," .
							$post["idItem"] . "," .
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
		// Buscando estoque com o patrimônio em que a qt é 1
		$sql = "SELECT * FROM estoque WHERE patrimonio='" . $post["remPatr"] . "' AND qt = 1;";
		$result = $cf_conn->query($sql);
		
		if(!$result){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// não existe este estoque registrado então não pode ser removido! (ou existem múltiplos estoque com qt = 1 e tem algo errado no BD
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
		
		// Prepara SQL para atualização de estoque
		$sql1 = "DELETE FROM estoque WHERE id=" . $row["id"] . ";"; 
		
		$sql1 = "UPDATE estoque SET qt = qt - 1 " . 
								" WHERE id = " . $row["id"] . ";";

		// sql para histórico de movimentações
		$sql2 = "INSERT INTO movimentacoes (idUsuario,idItem,idLocalOrigem,patrimonio,qt,tipo_movimentacao,obs) " .
				"VALUES (" . get_current_user_id() . "," .
							$post["idItem"] . "," .
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


/*
	Funções relacionadas a solicitações
*/
add_action('wp_ajax_addSolicitacao','addSolicitacao');
function addSolicitacao($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_addSolicitacao') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Adicionando solicitação...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	
	
	if($_POST["tipo"] === "Consumo"){
		
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
		
		// se existirem múltiplas entradas , tem erro no BD ou não existe o estoque pedido
		if($result->num_rows != 1) {
			$cf_data["msg"] = "O estoque consultado não existe ou está multiplicado...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Linhas encontradas: " . $result->num_rows;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// busca linha do estoque 
		$row = $result->fetch_assoc();
		$estoque_id = $row["id"];
		
		// verifique se quantidade pedida está adequada
		if($_POST["qt"] < 1){
			$cf_data["msg"] = "A quantidade pedida é menor ou igual a 0. Favor ajustar o quantitativo.";
			$cf_data["error"] = true;
			finaliza();
		}
		
		// sql para inserir solicitação
		$sql1 = "INSERT INTO solicitacao (idUsuario,idEstoque,qtPedida,status) " .
				"VALUES (" . get_current_user_id() . "," .
							$estoque_id . "," .
							$_POST["qt"] . "," .
							"'solicitado');";
		
		$result1 = $cf_conn->query($sql1);
		
		if(!$result1){
			$cf_data["msg"] = "Problema na adição da solicitação...";
			$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL );
			
			finaliza();
		}
		
		finaliza();
		
	}elseif($_POST["tipo"] === "Permanente"){
		// Buscando estoque
		$sql = "SELECT * FROM estoque WHERE patrimonio=" . $_POST["patr"] . 
											" AND iditem = " . $_POST["idItem"] . 
											" AND idLocal = " . $_POST["idLocal"] . 
											";";
		$result = $cf_conn->query($sql);
		
		if(!$result){
			$cf_data["msg"] = "Problema com banco de dados...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// se existirem múltiplas entradas , tem erro no BD ou não existe o estoque pedido
		if($result->num_rows != 1) {
			$cf_data["msg"] = "O estoque consultado não existe ou está multiplicado...";
			$cf_data["msg2"]= "SQL: " . $sql . " <br> Linhas encontradas: " . $result->num_rows;
			$cf_data["error"] = true;
			finaliza();
		}
		
		// busca linha do estoque 
		$row = $result->fetch_assoc();
		$estoque_id = $row["id"];
		
		// verifique se quantidade pedida está adequada
		if($_POST["qt"] < 1){
			$cf_data["msg"] = "A quantidade pedida é menor ou igual a 0. Favor ajustar o quantitativo.";
			$cf_data["error"] = true;
			finaliza();
		}
		
		// sql para inserir solicitação
		$sql1 = "INSERT INTO solicitacao (idUsuario,idEstoque,qtPedida,profResponsavel,status) " .
				"VALUES (" . get_current_user_id() . "," .
							$estoque_id . "," .
							$_POST["qt"] . "," .
							"'" . $_POST["prof"] . "'," . 
							"'solicitado');";
		
		$result1 = $cf_conn->query($sql1);
		
		if(!$result1){
			$cf_data["msg"] = "Problema na adição da solicitação...";
			$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
			$cf_data["error"] = true;
			
			escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL );
			
			finaliza();
		}
		
		finaliza();
		
		
	}
	
	// não deveria chegar aqui
	$cf_data["msg"] = "Chegou onde não devia, função addSolicitacao...";
	$cf_data["error"] = true;
	finaliza();
	
}


// busca solicitações do próprio usuário
add_action('wp_ajax_getSolicitacao','getSolicitacao');
function getSolicitacao($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_getSolicitacao') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Buscando solicitações...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	
	
	$sql = "SELECT s.*, e.patrimonio, i.itemNome,i.itemTipo,l.localNome FROM solicitacao s " .
			"INNER JOIN (SELECT * from estoque) e ON s.idEstoque = e.id " .
			"INNER JOIN (SELECT id, nome as itemNome, tipo as itemTipo FROM item) i ON e.iditem=i.id " .
			"INNER JOIN (SELECT id, nome as localNome FROM localizacao ) l ON e.idLocal=l.id ". 
			"WHERE s.idUsuario=". get_current_user_id() . ";";
	$result = $cf_conn->query($sql);
	
	
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
											$row["id"], // id
											$row["dataSolicitacao"],
											$row["dataAtendimento"],
											$row["dataDevolucao"],
											$row["qtPedida"],
											$row["qtAtendida"], 
											$row["qtDevolvida"], 
											$row["status"],
											$row["obs"],
											$row["itemNome"],
											$row["itemTipo"],
											$row["localNome"],
											$row["patrimonio"],
											$row["profResponsavel"],
										];
		}
		
		$cf_data["msg"] = "Solicitações encontradas: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "Nenhuma solicitação foi encontrada...";
		$cf_data["error"] = false;
	}
	
	finaliza();
	
}

add_action('wp_ajax_cancelaSolicitacao','cancelaSolicitacao');
function cancelaSolicitacao($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_cancelaSolicitacao') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Cancelando solicitação...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	
	$sql = "SELECT * FROM solicitacao WHERE id=" . $_POST["id"] . ";";
	$result = $cf_conn->query($sql);
	
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	
	if($result->num_rows == 0 ) {
		$cf_data["msg"] = "Nenhuma solicitação com o id indicado foi encontrada... Atualize a página ou contacte o administrador.";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	$row = $result->fetch_assoc();
	
	$estadoAtual = $row["status"];
	
	if($estadoAtual !== 'solicitado'){
		$cf_data["msg"] = "Solicitação não pode ser cancelada. Apenas solicitações não atendidas podem ser canceladas.<br> Estado: " + $estadoAtual;
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	// sql de atualização
	$sql1 = "UPDATE solicitacao SET status='cancelado'" . 
				" WHERE id=" . $_POST["id"] . ";";
	
	$obs = "Usuário cancelou solicitação.";
	
	// sql para histórico de movimentações
	$sql2 = "INSERT INTO movimentacoes (idUsuario,idSolicitacao,tipo_movimentacao,obs) " .
			" VALUES (" . get_current_user_id() . "," .
						$_POST["id"] . "," .
						"'cancela', " . 
						"'" . $obs . "');";
	
	$result1 = $cf_conn->query($sql1);
	
	if(!$result1){
		$cf_data["msg"] = "Problema no cancelamento da solicitação...";
		$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
		
		finaliza();
	}

	$result2 = $cf_conn->query($sql2);
	
	if(!$result2){
		$cf_data["msg"] = "Problema na adição do cancelamento da solicitação na movimentação...";
		$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
		
		finaliza();
	}
	
	finaliza();
	
}


/*
	Funções relacionadas gerência de solicitações
*/
add_action('wp_ajax_getSolicitacaoTudo','getSolicitacaoTudo');
function getSolicitacaoTudo($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_getSolicitacaoTudo') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Buscando solicitações...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	
	$sql = "SELECT s.*, e.patrimonio, i.itemNome,i.itemTipo,l.localNome, u.user_nicename from solicitacao s ".
		"INNER JOIN (SELECT * from estoque) e ON s.idEstoque = e.id " .
		"INNER JOIN (SELECT id, nome as itemNome, tipo as itemTipo FROM item) i ON e.iditem=i.id " .
		"INNER JOIN (SELECT id, nome as localNome FROM localizacao ) l ON e.idLocal=l.id ". 
		"INNER JOIN (SELECT id, user_nicename from wp_users) u ON s.idUsuario=u.id; ";
		
	$result = $cf_conn->query($sql);
	
	
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
											$row["id"], // id
											$row["dataSolicitacao"],
											$row["dataAtendimento"],
											$row["dataDevolucao"],
											$row["qtPedida"],
											$row["qtAtendida"], 
											$row["qtDevolvida"], 
											$row["status"],
											$row["obs"],
											$row["itemNome"],
											$row["itemTipo"],
											$row["localNome"],
											$row["patrimonio"],
											$row["profResponsavel"],
											$row["user_nicename"]
										];
		}
		
		$cf_data["msg"] = "Solicitações encontradas: " . $result->num_rows;
		$cf_data["error"] = false;
		
	}else {
		$cf_data["msg"] = "Nenhuma solicitação foi encontrada...";
		$cf_data["error"] = false;
	}
	
	finaliza();
	
}


add_action('wp_ajax_get1Solicitacao','get1Solicitacao');
function get1Solicitacao($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_get1Solicitacao') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Buscando solicitação...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	$cf_data["encontrado"] = false;
	
	
	$sql = "SELECT s.*, e.patrimonio, i.itemNome,i.itemTipo,l.localNome, u.user_nicename from solicitacao s ".
		"INNER JOIN (SELECT * from estoque) e ON s.idEstoque = e.id " .
		"INNER JOIN (SELECT id, nome as itemNome, tipo as itemTipo from item) i ON e.iditem=i.id " . 
		"INNER JOIN (SELECT id, nome as localNome from localizacao ) l ON e.idLocal=l.id " .
		"INNER JOIN (SELECT id, user_nicename from wp_users) u ON s.idUsuario=u.id " .
		" WHERE s.id=". $_POST["idSolicitacao"] .";";
	
	$result = $cf_conn->query($sql);
	
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	if($result->num_rows != 1 ) 
	{
		$cf_data["msg"] = "Nenhum Item com este ID foi encontrado ou multiplicado...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	$cf_data["encontrado"] = true;
	$row = $result->fetch_assoc();
		
	$cf_data["consultas"] = [
								$row["id"], // id
								$row["dataSolicitacao"],
								$row["dataAtendimento"],
								$row["dataDevolucao"],
								$row["qtPedida"],
								$row["qtAtendida"], 
								$row["qtDevolvida"], 
								$row["status"],
								$row["obs"],
								$row["itemNome"],
								$row["itemTipo"],
								$row["localNome"],
								$row["patrimonio"],
								$row["profResponsavel"],
								$row["user_nicename"]
							];
	
	$cf_data["msg"] = "Solicitações encontradas: " . $result->num_rows;
	
	
	finaliza();
	
}


add_action('wp_ajax_cancelaSolicitacaoGerencia','cancelaSolicitacaoGerencia');
function cancelaSolicitacaoGerencia($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_cancelaSolicitacaoGerencia') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Cancelando solicitação...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	
	$sql = "SELECT * FROM solicitacao WHERE id=" . $_POST["id"] . ";";
	$result = $cf_conn->query($sql);
	
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	
	if($result->num_rows == 0 ) {
		$cf_data["msg"] = "Nenhuma solicitação com o id indicado foi encontrada... Atualize a página ou contacte o administrador.";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	$row = $result->fetch_assoc();
	
	$estadoAtual = $row["status"];
	
	if($estadoAtual !== 'solicitado'){
		$cf_data["msg"] = "Solicitação não pode ser cancelada. Apenas solicitações não atendidas podem ser canceladas.<br> Estado: " + $estadoAtual;
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	// sql de atualização
	$sql1 = "UPDATE solicitacao SET status='cancelado', obs = '" . $_POST["obs"] . "'" .
				" WHERE id=" . $_POST["id"] . ";";
	
	
	// sql para histórico de movimentações
	$obs = "Gerência cancelou solicitação.";
	$sql2 = "INSERT INTO movimentacoes (idUsuario,idSolicitacao,tipo_movimentacao,obs) " .
			" VALUES (" . get_current_user_id() . "," .
						$_POST["id"] . "," .
						"'cancela', " . 
						"'" . $obs . "');";
	
	$result1 = $cf_conn->query($sql1);
	
	if(!$result1){
		$cf_data["msg"] = "Problema no cancelamento da solicitação...";
		$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
		
		finaliza();
	}

	$result2 = $cf_conn->query($sql2);
	
	if(!$result2){
		$cf_data["msg"] = "Problema na adição do cancelamento da solicitação na movimentação...";
		$cf_data["msg2"]= "SQL: " . $sql2  . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL);
		
		finaliza();
	}
	
	finaliza();
	
}


add_action('wp_ajax_atendeSolicitacao','atendeSolicitacao');
function atendeSolicitacao($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_atendeSolicitacao') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Atendendo solicitação...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	
	
	// busca solicitação e valida
	$sql = "SELECT * FROM solicitacao WHERE id=" . $_POST["id"] . ";";
	$result = $cf_conn->query($sql);
	
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	
	if($result->num_rows == 0 ) {
		$cf_data["msg"] = "Nenhuma solicitação com o id indicado foi encontrada... Atualize a página ou contacte o administrador.";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	$row = $result->fetch_assoc();
	
	$estadoAtual = $row["status"];
	$qtPedida = $row["qtPedida"];
	$idEstoque = $row["idEstoque"];
	
	if($estadoAtual !== 'solicitado'){
		$cf_data["msg"] = "Solicitação não pode ser atendida. Apenas solicitações com estado 'solicitado' podem ser atendidas.<br> Estado: " + $estadoAtual;
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	if($_POST["qt"] > $qtPedida) {
		$cf_data["msg"] = "Quantidade atendida não pode ser maior que quantidade solicitada.<br> Qt. solicitada: " . $qtPedida . ", qt. atendida: " . $_POST["qt"] . "<br>";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	
	$sql = "SELECT * from estoque WHERE id =" . $idEstoque . ";";
	
	$result = $cf_conn->query($sql);
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	if($result->num_rows == 0 ) {
		$cf_data["msg"] = "Nenhum estoque condizente com a solicitação foi encontrado... Atualize a página ou contacte o administrador.";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	$row = $result->fetch_assoc();

	$estoqueQt = $row["qt"];
	$estoqueQtEmpr = $row["qtEmprestada"];
	$estoqueSaldo = $estoqueQt - $estoqueQtEmpr;
	
	
	if($_POST["qt"] > $estoqueSaldo) {
		$cf_data["msg"] = "Quantidade a ser atendida é maior que o saldo atual!<br> Qt. a ser atendida: " . $_POST["qt"] . ", saldo atual do estoque: " . $estoqueSaldo . "<br>";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	
	// Se chegou aqui, é possível realizar empréstimo
	
	// sql de atualização da solictação 
	$sql1 = "UPDATE solicitacao SET status='atendido', " .
									" obs = '" . $_POST["obs"] . "', " . 
									" dataAtendimento = CURRENT_TIMESTAMP(), " . 
									" qtAtendida = " . $_POST["qt"] . 
								" WHERE id=" . $_POST["id"] . ";";
	
	
	// sql para atualizar estoque
	$sql2 = "UPDATE estoque SET qtEmprestada= qtEmprestada + " . $_POST["qt"] .
								" WHERE id=" . $idEstoque . ";";
	
	// sql para histórico de movimentações
	$obs = "Atendimento de solicitação.";
	$sql3 = "INSERT INTO movimentacoes (idUsuario,idSolicitacao,tipo_movimentacao,obs) " .
			" VALUES (" . get_current_user_id() . "," .
						$_POST["id"] . "," .
						"'emprestimo', " . 
						"'" . $obs . "');";
	
	$result1 = $cf_conn->query($sql1);
		
	if(!$result1){
		$cf_data["msg"] = "Problema na atualização da solicitação...";
		$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL . "NÃO FEZ 3:" . $sql3 . PHP_EOL);
		
		finaliza();
	}

	$result2 = $cf_conn->query($sql2);
	
	if(!$result2){
		$cf_data["msg"] = "Problema na atualização do estoque...";
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
	
}


add_action('wp_ajax_devolveSolicitacao','devolveSolicitacao');
function devolveSolicitacao($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_devolveSolicitacao') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Devolvendo solicitação...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	
	
	// busca solicitação e valida
	$sql = "SELECT * FROM solicitacao WHERE id=" . $_POST["id"] . ";";
	$result = $cf_conn->query($sql);
	
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	
	if($result->num_rows == 0 ) {
		$cf_data["msg"] = "Nenhuma solicitação com o id indicado foi encontrada... Atualize a página ou contacte o administrador.";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	$row = $result->fetch_assoc();
	
	$estadoAtual = $row["status"];
	$qtAtendida = $row["qtAtendida"];
	$idEstoque = $row["idEstoque"];
	
	if($estadoAtual !== 'atendido'){
		$cf_data["msg"] = "Solicitação não pode ser devolvida. Apenas solicitações com estado 'atendido' podem ser devolvidas.<br> Estado: " + $estadoAtual;
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	if($_POST["qt"] > $qtAtendida) {
		$cf_data["msg"] = "Quantidade devolvida não pode ser maior que quantidade atendida.<br> Qt. a devolver: " . $_POST["qt"] . ", qt. atendida: " . $qtAtendida . "<br>";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	// verifica se é devolução com baixa
	$devComBaixa = false;
	if($_POST["qt"] < $qtAtendida){
		$devComBaixa = true;
		$qtBaixa = $qtAtendida - $_POST["qt"];
	}
	
	
	// busca estoque e valida
	$sql = "SELECT * from estoque WHERE id =" . $idEstoque . ";";
	
	$result = $cf_conn->query($sql);
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	if($result->num_rows == 0 ) {
		$cf_data["msg"] = "Nenhum estoque condizente com a solicitação foi encontrado... Atualize a página ou contacte o administrador.";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	$row = $result->fetch_assoc();

	$estoqueQt = $row["qt"];
	$estoqueQtEmpr = $row["qtEmprestada"];

	if($_POST["qt"] > $estoqueQtEmpr) {
		$cf_data["msg"] = "Quantidade a ser devolvida é maior que a qt emprestada no estoque!<br> Qt. a ser devolvida: " . $_POST["qt"] . ", qt emprestada: " . $estoqueQtEmpr . "<br>";
		$cf_data["error"] = true;
		
		finaliza();
	}
	
	
	
	
	// Se chegou aqui, é possível realizar devolução
	
	// sql de atualização da solicitação 	
	$sql1 = "UPDATE solicitacao SET status='devolvido', " .
									" obs = '" . $_POST["obs"] . "', " . 
									" dataDevolucao = CURRENT_TIMESTAMP(), " . 
									" qtDevolvida = " . $_POST["qt"] . 
								" WHERE id=" . $_POST["id"] . ";";
	
	
	// sql para atualizar estoque
	if($devComBaixa){// se teve baixa, tem que atualizar a qt do estoque
		$sql2 = "UPDATE estoque SET qtEmprestada= qtEmprestada - " . $qtAtendida . ", " .
									" qt = qt - " . $qtBaixa .
								" WHERE id=" . $idEstoque . ";";
	}else{
		$sql2 = "UPDATE estoque SET qtEmprestada= qtEmprestada - " . $_POST["qt"] .
								" WHERE id=" . $idEstoque . ";";
	}
	
	// sql para histórico de movimentações
	if($devComBaixa){
		$obs = "Devolução de solicitação com baixa.";
		$sql3 = "INSERT INTO movimentacoes (idUsuario,idSolicitacao,tipo_movimentacao,qt,obs) " .
				" VALUES (" . get_current_user_id() . "," .
							$_POST["id"] . "," .
							"'devolucaoEBaixa', " .
							$qtBaixa . ", " . 
							"'" . $obs . "');";
	}else{
		$obs = "Devolução de solicitação.";
		$sql3 = "INSERT INTO movimentacoes (idUsuario,idSolicitacao,tipo_movimentacao,obs) " .
				" VALUES (" . get_current_user_id() . "," .
							$_POST["id"] . "," .
							"'devolucao', " . 
							"'" . $obs . "');";
							
	}
	
	
	
	$result1 = $cf_conn->query($sql1);
		
	if(!$result1){
		$cf_data["msg"] = "Problema na atualização da solicitação...";
		$cf_data["msg2"]= "SQL: " . $sql1  . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		
		escreveDebug("NÃO FEZ 1 - " . $sql1 . PHP_EOL . "NÃO FEZ 2:" . $sql2 . PHP_EOL . "NÃO FEZ 3:" . $sql3 . PHP_EOL);
		
		finaliza();
	}

	$result2 = $cf_conn->query($sql2);
	
	if(!$result2){
		$cf_data["msg"] = "Problema na atualização do estoque...";
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
	
}


/*
	Funções relacionadas a relatórios
*/
add_action('wp_ajax_getRelatorioEstoque','getRelatorioEstoque');
function getRelatorioEstoque($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_getRelatorioEstoque') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Gerando relatório estoque...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	$cd_data["consultas"] = [];
	
	
	$sql = "SELECT e.id, i.nome as item, i.tipo, l.nome as local, e.patrimonio, e.qt, e.qtEmprestada FROM estoque e " . 
		" INNER JOIN (SELECT * FROM item) i ON e.iditem = i.id " . 
		" INNER JOIN (SELECT * FROM localizacao) l ON e.idLocal = l.id;";
	
	$result = $cf_conn->query($sql);
	
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		
		$cf_data["consultas"][] = ['id', 'item', 'tipo', 'local', 'patrimonio', 'qt', 'qtEmprestada'];
		
		while($row = $result->fetch_assoc()) {
			
				$cf_data["consultas"][] = [ 
											$row["id"], // id
											$row["item"],
											$row["tipo"],
											$row["local"],
											$row["patrimonio"],
											$row["qt"], 
											$row["qtEmprestada"]
										];

		}

		
		$cf_data["msg"] = "Estoques encontrados: " . $result->num_rows . "<br>Download iniciado.";
		$cf_data["error"] = false;
		$cf_data["sql"] = $sql;
		
	}else {
		$cf_data["msg"] = "Nenhum estoque foi encontrado...";
		$cf_data["error"] = false;
	}
	
	
	finaliza();
	
}


add_action('wp_ajax_getRelatorioSolicitacao','getRelatorioSolicitacao');
function getRelatorioSolicitacao($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_getRelatorioSolicitacao') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Gerando relatório estoque...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	$cd_data["consultas"] = [];
	
	
	$sql = "SELECT s.*, u.user_nicename, u.user_email, e.iditem, i.nome as nomeItem, i.tipo, e.patrimonio, e.idLocal, l.nome as nomeLocal ".
		" FROM solicitacao s " .
		" INNER JOIN (SELECT ID, user_nicename, user_email FROM wp_users) u ON s.idUsuario = u.ID " .
		" INNER JOIN (SELECT id, iditem, idLocal, patrimonio FROM estoque) e ON s.idEstoque = e.id " .
		" INNER JOIN (SELECT id, nome, tipo FROM item) i ON e.iditem = i.id " .
		" INNER JOIN (SELECT id, nome FROM localizacao) l ON e.idLocal = l.id;";
	
	$result = $cf_conn->query($sql);
	
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		
		$cf_data["consultas"][] = ['id', 'idUsuario', 'idEstoque', 'dataSolicitacao', 'dataAtendimento', 'dataDevolucao', 'qtPedida' ,'qtAtendida' ,'qtDevolvida' ,'profResponsavel', 'status', 'obs' , 'user_nicename' , 'user_email' , 'iditem' ,'nomeItem', 'tipo' ,'patrimonio', 'idLocal','nomeLocal'];
		
		while($row = $result->fetch_assoc()) {
			
				$cf_data["consultas"][] = [ 
											$row["id"],
											$row["idUsuario"],
											$row["idEstoque"],
											$row["dataSolicitacao"],
											$row["dataAtendimento"],
											$row["dataDevolucao"],
											$row["qtPedida"],
											$row["qtAtendida"],
											$row["qtDevolvida"],
											$row["profResponsavel"],
											$row["status"],
											'"' . str_replace('"','""',$row["obs"]) . '"',
											$row["user_nicename"],
											$row["user_email"],
											$row["iditem"],
											$row["nomeItem"],
											$row["tipo"],
											$row["patrimonio"],
											$row["idLocal"],
											$row["nomeLocal"],
										];
		}

		
		$cf_data["msg"] = "Solicitações encontradas: " . $result->num_rows . "<br>Download iniciado.";
		$cf_data["error"] = false;
		//$cf_data["sql"] = $sql;
		
	}else {
		$cf_data["msg"] = "Nenhuma solicitação foi encontrada...";
		$cf_data["error"] = false;
	}
	
	
	finaliza();
	
}


add_action('wp_ajax_getRelatorioMovimentacao','getRelatorioMovimentacao');
function getRelatorioMovimentacao($post){
	global $cf_conn, $cf_data;
	
	
	if(!validaPOST() || !validaNonce('nonce_getRelatorioMovimentacao') || !validaUsuario() || !conecta()){
		finaliza(); // termina o programa aqui;
	}

	$cf_data["msg"] = "Gerando relatório de movimentação...";
	$cf_data["msg2"] = "";
	$cf_data["error"] = false;
	$cd_data["consultas"] = [];
	
	
	$sql = "SELECT m.*, u.user_nicename, u.user_email, i.nome as nomeItem, i.tipo, l.nome as localOrigem, l2.nome as localDestino FROM movimentacoes m " . 
		" INNER JOIN (SELECT ID, user_nicename, user_email FROM wp_users) u ON m.idUsuario = u.ID " .
		" LEFT JOIN (SELECT id, nome, tipo FROM item) i ON m.idItem = i.id " .
		" LEFT JOIN (SELECT id, nome FROM localizacao) l ON m.idLocalOrigem = l.id " .
		" LEFT JOIN (SELECT id, nome FROM localizacao) l2 ON m.idLocalDestino = l2.id;";
	
	$result = $cf_conn->query($sql);
	
	
	if(!$result){
		$cf_data["msg"] = "Problema com banco de dados...";
		$cf_data["msg2"]= "SQL: " . $sql . " <br> Erro: " . $cf_conn->error;
		$cf_data["error"] = true;
		finaliza();
	}
	
	
	if($result->num_rows > 0 ) {
		// output data of each row
		
		$cf_data["consultas"][] = ['id','idUsuario','user_email','user_nicename','idItem','nomeItem','tipo','patrimonio','idLocalOrigem','localOrigem','idLocalDestino','localDestino','idSolicitacao','data','qt','tipo_movimentacao','obs'];
		
		while($row = $result->fetch_assoc()) {
			
				$cf_data["consultas"][] = [ 
											$row["id"],
											$row["idUsuario"],
											$row["user_email"],
											$row["user_nicename"],
											$row["idItem"],
											$row["nomeItem"],
											$row["tipo"],
											$row["patrimonio"],
											$row["idLocalOrigem"],
											$row["localOrigem"],
											$row["idLocalDestino"],
											$row["localDestino"],
											$row["idSolicitacao"],
											$row["data"],
											$row["qt"],
											$row["tipo_movimentacao"],
											'"' . str_replace('"','""',$row["obs"]) . '"'
										];
		}

		
		$cf_data["msg"] = "Movimentações encontrados: " . $result->num_rows . "<br>Download iniciado.";
		$cf_data["error"] = false;
		//$cf_data["sql"] = $sql;
		
	}else {
		$cf_data["msg"] = "Nenhuma movimentação foi encontrada...";
		$cf_data["error"] = false;
	}
	
	
	finaliza();
	
}

?>