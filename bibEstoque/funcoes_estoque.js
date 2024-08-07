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
			{width : '5%', targets : 3}
			
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

				jQuery("#btnAddLocalToogle").show();
				jQuery("#formAlteraLocal").trigger("reset");
				jQuery("#formAlteraLocal").hide();

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
	jQuery("#btnAddLocalToogle").show();
	jQuery("#formAlteraLocal").trigger("reset");
	jQuery("#formAlteraLocal").hide();
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
			{width : '5%', targets : 4},
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

function adicionaItem() {

	jQuery("#msgTopoHistorico").html("");
	
	var str_nome = jQuery("#item_nome").val();
	var str_desc = jQuery("#item_descricao").val();
	var str_tipo = jQuery("#item_tipo option:selected").val();
	var str_datasheet = jQuery("#item_datasheet").val();
	var str_img = jQuery("#item_imagem").val();
	
	
	if(!str_nome || !str_desc || (str_tipo !== "Consumo" && str_tipo !== "Permanente")) {
		jQuery("#msgTopoHistorico").html("Um dos campos está vazio...<br>");
		return;
	}
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'addItem',
			"nonce":    nonce_addItem,
			"nome": str_nome,
			"descricao": str_desc,
			"tipo": str_tipo,
			"datasheet": str_datasheet,
			"img": str_img
		},
		dataType: "JSON",
		success: function(data){
			if(!data.error){
		
				jQuery("#formAddItem").trigger("reset");
				jQuery("#formAddItem").toggle();
				
				getItens();
				
			}else{
				jQuery("#msgResultado").html('');
				jQuery("#msgErro").html("Erro ao adicionar o novo item, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
				
				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}
			}
		}
	});
	
}

function get1Item(id){
	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'get1Item',
			"nonce":    nonce_get1Item,
			"idItem": id
		},
		success: function(data){
			
			window.scrollTo({ top: 0, behavior: 'smooth' });
			
			if(!data.error){

				jQuery("#btnAddItemToogle").hide();
				jQuery("#formAddItem").hide();
				
				jQuery("#formAlteraItem").show();
				
				jQuery("#altera_item_id").val(data.consultas[0]);
				jQuery("#altera_item_nome").val(data.consultas[1]);
				jQuery("#altera_item_descricao").val(data.consultas[2]);
				
				if(data.consultas[3] === "Consumo"){
					jQuery("#altera_item_tipo").val("0");
				}else{
					jQuery("#altera_item_tipo").val("1");
				}

				
				
				jQuery("#altera_item_datasheet").val(data.consultas[4]);
				jQuery("#altera_item_imagem").val(data.consultas[5]);

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

function alteraItem() {

	jQuery("#msgTopoHistorico").html("");
	
	var id = Number(jQuery("#altera_item_id").val());
	var str_nome = jQuery("#altera_item_nome").val();
	var str_desc = jQuery("#altera_item_descricao").val();
	var str_tipo = jQuery("#altera_item_tipo option:selected").text();
	var str_datasheet = jQuery("#altera_item_datasheet").val();
	var str_img = jQuery("#altera_item_imagem").val();
	
	
	if(!str_nome || !str_desc || (str_tipo !== "Consumo" && str_tipo !== "Permanente")) {
		jQuery("#msgTopoHistorico").html("Um dos campos está vazio ou incorreto...<br>");
		return;
	}
	if(!Number.isInteger(id)){
		jQuery("#msgTopoHistorico").html("ID não é numérico ou inteiro...<br>");
		return;
	}
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'alteraItem',
			"nonce":    nonce_alteraItem,
			"idItem": id,
			"nome": str_nome,
			"descricao": str_desc,
			"tipo": str_tipo,
			"datasheet": str_datasheet,
			"img": str_img
		},
		dataType: "JSON",
		success: function(data){
			if(!data.error){

				jQuery("#btnAddItemToogle").show();
				jQuery("#formAlteraItem").trigger("reset");
				jQuery("#formAlteraItem").hide();

			}else{
				jQuery("#msgResultado").html('');
				jQuery("#msgErro").html("Erro ao alterar o item, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");

				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}
			}
			
			getItens();
		}
	});
	
}

function alteraItemCancelar(){
	jQuery("#btnAddItemToogle").show();
	jQuery("#formAlteraItem").trigger("reset");
	jQuery("#formAlteraItem").hide();
}

/*
	funções da página Estoque
*/

function initTabelaEstoque(){
	new DataTable('#tab_consulta_estoque',{
		"language": {
			url: 'https://cdn.datatables.net/plug-ins/2.1.3/i18n/pt-BR.json'
		},
		"columnDefs": [
			{width : '20%', targets : 0},
			//{width : '20%', targets : 1},
			{width : '7%', targets : 2},
			{width : '7%', targets : 3},
			{width : '12%', targets : 4},
			{width : '5%', targets : 5},
		]
	});

}


function getEstoque(){
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'getEstoque',
			"nonce":    nonce_getEstoque
		},
		success: atualizaTabEstoque
	});
	
}

function atualizaTabEstoque(data){
	
	var t = jQuery("#tab_consulta_estoque").DataTable();
	
	if(!data.error){
		
		t.clear();
	
		for(x of data.consultas){
			
			t.row.add([
					x[7],
					x[5],
					x[2],
					x[3],
					x[4],
					'<i class="far fa-edit editEstoque" onclick="xxxx(' + x[0] +')"></i> '
					]);
		}
		
		
		t.draw();
		
	
	}else{
		jQuery("#msgTopoHistorico").html("Erro ao buscar histórico de locais, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
	}
	
}


function estoque_formGetLocal(){
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'getLocais',
			"nonce":    nonce_get_Locais
		},
		success: function(data){
			var t = jQuery("#estoque_local");
			var t2 = jQuery("#estoque_local_mov");
			
			if(!data.error){
				
				t.empty();
				t.append(new Option("",-1));
				t2.append(new Option("",-1));
			
				for(x of data.consultas){
					
					t.append(new Option(x[1],x[0]));
					t2.append(new Option(x[1],x[0]));
				}
				
			
			}else{
				jQuery("#msgTopoHistorico").html("Erro ao buscar histórico de locais, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
				
				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}
			}	
		}
	});
	
	
}


function estoque_formGetItem(){
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'getItens',
			"nonce":    nonce_getItens
		},
		success: function(data){
			var t = jQuery("#estoque_item");
			
			
			if(!data.error){
				
				t.empty();
				t.append(new Option("",-1));
				
			
				for(x of data.consultas){
					
					t.append(new Option(x[1],x[0]));
					
				}
				
			
			}else{
				jQuery("#msgTopoHistorico").html("Erro ao buscar histórico de itens, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
				
				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}
			}	
		}
	});
	
}



