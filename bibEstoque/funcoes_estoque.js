function initTabelaLocais(){
	new DataTable('#tab_consulta_locais',{
		"language": {
			url: 'https://cdn.datatables.net/plug-ins/2.1.3/i18n/pt-BR.json'
		}
	}
	);
}


function getLocais(){
	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'getLocais',
			"nonce":    nonce_get_Locais
		},
		success: atualizaTabLocais
	});
}

function atualizaTabLocais(data){
	var t = jQuery("#tab_consulta_locais").DataTable();
	
	if(!data.error){
		
		
		t.clear();
	
		for(x of data.consultas){
			
			t.row.add([
					x[0],
					x[1],
					x[2]
					]);
		}
		
		t.draw();
		
	
	}else{
		jQuery("#msgTopoHistorico").html("Erro ao buscar histórico de locais, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
	}
	
}

function adiciona_Local() {

	jQuery("#msgTopoHistorico").html("");
	
	var str_nome = document.getElementById("local_nome");
	var str_desc = document.getElementById("local_descricao");
	
	
	if(!str_nome.value || !str_desc.value) {
		jQuery("#msgTopoHistorico").html("Um dos campos está vazio...<br>");
		return;
	}
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'addLocal',
			"nonce":    nonce_add_Local,
			"nome": str_nome.value,
			"descricao": str_desc.value,
		},
		dataType: "JSON",
		success: resultadoAdicionaLocal
	});
	
}

function resultadoAdicionaLocal(data){
	if(!data.error){
		
		jQuery("#formAddLocal").trigger("reset");
		jQuery("#formAddLocal").toggle();
		
		getLocais();
		
	}else{
		jQuery("#msgResultado").html('');
		jQuery("#msgErro").html("Erro ao adicionar o novo local, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
		
		if(data.msg2 != ""){
			jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
		}

		
	}

	
}


