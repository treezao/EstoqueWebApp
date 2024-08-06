/*
	funções da página Locais
*/


function initTabelaLocais(){
	new DataTable('#tab_consulta_locais',{
		"language": {
			url: 'https://cdn.datatables.net/plug-ins/2.1.3/i18n/pt-BR.json'
		},
		"columnDefs": [
			{
				targets: 0,
				visible: false
			},
			{width : '25%', targets : 1},
			{width : '10%', targets : 3}
			
		]
	});

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
					x[2],
					'<i class="far fa-edit editLocal" onclick="get1Local(' + x[0] +')"></i> '
					
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


function get1Local(id){
	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'get1Local',
			"nonce":    nonce_get_1_Local,
			"idLocal": id
		},
		success: function(data){
			
			window.scrollTo({ top: 0, behavior: 'smooth' });
			
			if(!data.error){

				jQuery("#btnAddLocalToogle").hide();
				jQuery("#formAddLocal").hide();
				
				jQuery("#formAlteraLocal").show();
				jQuery("#altera_local_id").val(data.consultas[0]);
				jQuery("#altera_local_nome").val(data.consultas[1]);
				jQuery("#altera_local_descricao").val(data.consultas[2]);

			}else{
				jQuery("#msgResultado").html('');
				jQuery("#msgErro").html("Erro ao buscar o local, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");

				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}


			}
		}
	});
	
}

function alteraLocal() {

	jQuery("#msgTopoHistorico").html("");
	
	var str_id = document.getElementById("altera_local_id");
	var str_nome = document.getElementById("altera_local_nome");
	var str_desc = document.getElementById("altera_local_descricao");
	
	
	if(!str_id.value || !str_nome.value || !str_desc.value) {
		jQuery("#msgTopoHistorico").html("Um dos campos está vazio...<br>");
		return;
	}
	if(!Number.isInteger(Number(str_id.value))){
		jQuery("#msgTopoHistorico").html("ID não é numérico ou inteiro...<br>");
		return;
	}
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'alteraLocal',
			"nonce":    nonce_alteraLocal,
			"idLocal": Number(str_id.value),
			"nome": str_nome.value,
			"descricao": str_desc.value,
		},
		dataType: "JSON",
		success: function(data){
			if(!data.error){

				jQuery("#btnAddLocalToogle").toggle();
				jQuery("#formAlteraLocal").trigger("reset");
				jQuery("#formAlteraLocal").toggle();

			}else{
				jQuery("#msgResultado").html('');
				jQuery("#msgErro").html("Erro ao alterar o local, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");

				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}
			}
			
			getLocais();
		}
	});
	
}


function altera_Local_cancelar(){
	jQuery("#btnAddLocalToogle").toggle();
	jQuery("#formAlteraLocal").trigger("reset");
	jQuery("#formAlteraLocal").toggle();
}




/*
	funções da página Itens
*/

function initTabelaItens(){
	new DataTable('#tab_consulta_itens',{
		"language": {
			url: 'https://cdn.datatables.net/plug-ins/2.1.3/i18n/pt-BR.json'
		},
		"columnDefs": [
			{
				targets: 0,
				visible: false
			},
			{width : '25%', targets : 1},
			{width : '7%', targets : 3},
			{width : '7%', targets : 4},
		]
	});

}


function getItens(){
	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'getItens',
			"nonce":    nonce_getItens
		},
		success: atualizaTabItens
	});
}

function atualizaTabItens(data){
	var t = jQuery("#tab_consulta_itens").DataTable();
	
	if(!data.error){
		
		t.clear();
	
		for(x of data.consultas){
			
			t.row.add([
					x[0],
					x[1],
					x[2],
					x[3],
					'<i class="far fa-edit editItem" onclick="get1Item(' + x[0] +')"></i> '
					]);
		}
		
		
		t.draw();
		
	
	}else{
		jQuery("#msgTopoHistorico").html("Erro ao buscar histórico de locais, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
	}
}

