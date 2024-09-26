/* 
	Funções Gerais
*/

function resetMsgTopo(){
	jQuery("#msgTopoHistorico").html("");
	jQuery("#msgDebug").html("");
	jQuery("#msgResultado").html("");
	jQuery("#msgAviso").html("");
	jQuery("#msgErro").html("");
	
}


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
				visible: true
			},
			{width : '5%', targets: 0},
			{width : '25%', targets : 1},
			{width : '5%', targets : 3},
		],
		lengthMenu: [
			[25, 50, 100, -1],
			[25, 50, 100, 'Tudo']
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
					'<i class="far fa-edit editLocal" title="Alterar" onclick="get1Local(' + x[0] +')"></i> '
					
					]);
		}
		
		
		t.draw();
		
	
	}else{
		jQuery("#msgTopoHistorico").html("Erro ao buscar histórico de locais, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
	}
	
}

function adiciona_Local() {

	resetMsgTopo();
	
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
	window.scrollTo({ top: 0, behavior: 'smooth' });
	
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

	resetMsgTopo();
	
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
				visible: true
			},
			{width : '5%', targets: 0},
			{width : '25%', targets : 1},
			{width : '7%', targets : 3},
			{width : '5%', targets : 4},
		],
		lengthMenu: [
			[25, 50, 100, -1],
			[25, 50, 100, 'Tudo']
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
					//x[2],
					accordionItem(x),
					x[3],
					'<i class="far fa-edit editItem" title="Alterar" onclick="get1Item(' + x[0] +')"></i> '
					]);
		}
		
		
		t.draw();
		
	}else{
		jQuery("#msgTopoHistorico").html("Erro ao buscar histórico de locais, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
	}
}

function accordionItem(data){
	var nameAcc = 'accordionExample' + data[0];
	var nameItem = 'collapseOne' + data[0];
	
	var linkDatasheet;
	var linkImagem;
	
	if(data[4] === ""){
		linkDatasheet = '<div class="col"><p class="col">Datasheet Indisponível </p></div>'
	}else{
		linkDatasheet = '<div class="col"><a href="'+ data[4] + '" target="blank">Datasheet</a></div>';
	}
	
	if(data[5] === ""){
		linkImagem = '<div class="col"><p class "col">Imagem indisponível</p></div>'
	}else{
		linkImagem = '<div class="col"><img src="'+ data[5] + '" class="img-thumbnail col" alt="imag_componente"></div>';
	}
	
	
	
	
	
	var base = '<div class="accordion accordion-flush" id="' + nameAcc + '">' +
					'<div class="accordion-item">' + 
						'<h2 class="accordion-header">' + 
							'<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' + nameItem + '" aria-expanded="true" aria-controls="' + nameItem +'">' +
							data[2] + 
							'</button>' +
						'</h2>' +
						'<div id="' + nameItem + '" class="accordion-collapse collapse" data-bs-parent="' + nameAcc + '">' +
							'<div class="accordion-body">'+
								'<div class="row mb-2 align-items-center">' + 
								linkImagem +
								linkDatasheet + 
								'</div>' + 
							'</div>' +
						'</div>'+
					'</div>'+
				'</div>';
				
	
				
	return base;
}


function adicionaItem() {

	resetMsgTopo();
	
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
				jQuery("#msgResultado").html('Adição realizada!');
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
	resetMsgTopo();
	
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

	resetMsgTopo();
	
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
				jQuery("#msgResultado").html('Alteração realizada!');
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
	resetMsgTopo();
	
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
			{width : '47%', targets : 1},
			{width : '7%', targets : 2},
			{width : '7%', targets : 3},
			{width : '12%', targets : 4},
			{width : '7%', targets : 5},
		],
		lengthMenu: [
			[25, 50, 100, -1],
			[25, 50, 100, 'Tudo']
		]
	});

}


function getEstoque(funcao){
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'getEstoque',
			"nonce":    nonce_getEstoque
		},
		success: funcao
	});
	
}

function atualizaTabEstoque(data){
	
	var t = jQuery("#tab_consulta_estoque").DataTable();
	
	if(!data.error){
		
		t.clear();
	
		for(x of data.consultas){
			
			t.row.add([
					x[7],
					//x[5],
					accordionItemEstoque(x),
					x[2],
					x[3],
					x[4],
					'<i class="fas fa-reply" title="Adicionar" onclick="btnAdicionarEstoque(' + x[0] +','+ x[1] + ',' + x[4] + ')"></i> ' +
					'<i class="	fas fa-sync-alt" title="Movimentar" onclick="btnMovimentarEstoque(' + x[0] +','+ x[1] + ',' + x[4] + ')"></i> ' +
					'<i class="	far fa-trash-alt" title="Remover" onclick="btnRemoverEstoque(' + x[0] +','+ x[1] + ',' + x[4] + ')"></i> '
					]);
		}
		
		
		t.draw();
		
	
	}else{
		jQuery("#msgTopoHistorico").html("Erro ao buscar estoque, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
	}
	
}

function btnAdicionarEstoque(idItem,idLocal,patrimonio){
	jQuery("#estoque_op").val(0);
	jQuery("#estoque_op").trigger("change");
	
	jQuery("#estoque_local").val(idLocal);
	jQuery("#estoque_item").val(idItem);

	jQuery("#estoque_local").trigger("change");
	
	
	jQuery("#formAlteraEstoque").show();
	
	window.scrollTo({ top: 0, behavior: 'smooth' });


}

function btnMovimentarEstoque(idItem,idLocal,patrimonio){
	jQuery("#estoque_op").val(1);
	jQuery("#estoque_op").trigger("change");
	
	jQuery("#estoque_local").val(idLocal);
	jQuery("#estoque_item").val(idItem);

	jQuery("#estoque_local").trigger("change");
	
	/* não funciona pois as funções estão sendo executadas de forma assíncrona e esta termina antes do trigger ser chamado (ajax mais lento, tem callback na linha anterior) 
	if(patrimonio !== null){
		jQuery("#msgTopoHistorico").html("entrou aqui:" + patrimonio);
		jQuery("#estoque_patr_mov").val(patrimonio);
		jQuery("#estoque_patr_mov").trigger("change");
	}
	*/
	
	
	jQuery("#formAlteraEstoque").show();
	
	window.scrollTo({ top: 0, behavior: 'smooth' });


}

function btnRemoverEstoque(idItem,idLocal,patrimonio){
	jQuery("#estoque_op").val(2);
	jQuery("#estoque_op").trigger("change");
	
	jQuery("#estoque_local").val(idLocal);
	jQuery("#estoque_item").val(idItem);

	jQuery("#estoque_local").trigger("change");
	
	/* não funciona pois as funções estão sendo executadas de forma assíncrona e esta termina antes do trigger ser chamado (ajax mais lento, tem callback na linha anterior) 
	if(patrimonio !== null){
		jQuery("#estoque_patr_rem").val(patrimonio);
		jQuery("#estoque_patr_rem").trigger("change");
	}
	*/
	
	jQuery("#formAlteraEstoque").show();
	
	window.scrollTo({ top: 0, behavior: 'smooth' });


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
					
					//t.append(new Option(x[1],x[0]));
					var opt = '<option value="' + x[0] + '" tipo="' + x[3] + '">' + x[1] + '</option>';
					t.append(opt);
					
					
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


function get1Estoque(){
	resetMsgTopo();
	
	var sel_local = jQuery("#estoque_local option:selected");
	var sel_item = jQuery("#estoque_item option:selected");
	
	jQuery("#estoque_qt_atual").val("");
	jQuery("#estoque_qt_emprestada").val("");

	jQuery("#estoque_qt_add").prop("disabled", true);
	jQuery("#estoque_patr_add").prop("disabled", true);
	
	jQuery("#estoque_qt_rem").prop("disabled", true);
	jQuery("#estoque_patr_rem").prop("disabled", true);
	
	jQuery("#estoque_qt_mov").prop("disabled", true);
	jQuery("#estoque_patr_mov").prop("disabled", true);
	jQuery("#estoque_local_mov").prop("disabled", true);
	
	// desabilita botão de alterar estoque, habilitação ocorre abaixo apenas em certos casos
	jQuery("#btnAlteraEstoque").prop("disabled", true); 
	
	
	if(sel_local.val() >0 && sel_item.val() >0){
		//jQuery("#msgAviso").html('<br><p> local:' + sel_local.val() + ', item:' + sel_item.val() +'</p>');
		
		jQuery.post({
			url: ajax_url,
			type: "POST",
			dataType: "JSON",
			data: {
				"action": 'get1Estoque',
				"nonce":    nonce_get1Estoque,
				"idLocal": sel_local.val(),
				"idItem": sel_item.val()
			},
			success: function(data){
				if(!data.error){
					
					var sel_op = jQuery("#estoque_op option:selected").text(); // operação selecionada
					
					if(sel_op !== "Adicionar" && sel_op !== "Movimentar" && sel_op !== "Remover"){
						jQuery("#msgTopoHistorico").html("Operação escolhida inválida: "+ sel_op + "<br>");
						return;
					}
					
					if(data.tipo === "Consumo"){
						jQuery("#estoque_qt_add").prop("disabled", false);
						jQuery("#estoque_patr_add").val("");
						
						jQuery("#estoque_patr_mov").empty();
						
						jQuery("#estoque_patr_rem").empty();
						
						
						// no caso de adição, pode habilitar botão independente se houver estoque ou não
						if(sel_op === "Adicionar"){
							jQuery("#btnAlteraEstoque").prop("disabled", false); // habilita botão de alterar estoque
						}
						
						
						if(data.encontrado){
							jQuery("#btnAlteraEstoque").prop("disabled", false); // habilita botão de alterar estoque
							
							
							jQuery("#estoque_qt_atual").val(data.consultas[2]);
							jQuery("#estoque_qt_emprestada").val(data.consultas[3]);
							
							
							jQuery("#estoque_qt_mov").prop("disabled", false);
							jQuery("#estoque_local_mov").prop("disabled", false);
							
							jQuery("#estoque_qt_rem").prop("disabled", false);
							
							
						}else{
							jQuery("#estoque_qt_atual").val(0);
							jQuery("#estoque_qt_emprestada").val(0);
						
						}
					}else if(data.tipo === "Permanente"){
						// no caso de adição, pode habilitar botão independente se houver estoque ou não
						if(sel_op === "Adicionar"){
							jQuery("#btnAlteraEstoque").prop("disabled", false); // habilita botão de alterar estoque
						}
						
						
						jQuery("#estoque_qt_add").val(1);
						jQuery("#estoque_patr_add").prop("disabled", false);

						
						jQuery("#estoque_patr_mov").empty();
						jQuery("#estoque_qt_mov").val(1);
						
						jQuery("#estoque_patr_rem").empty();
						jQuery("#estoque_qt_rem").val(1);
						
						
						// considerar que patrimônio não foi inserido ainda. Validação de n. patrimônio ocorre mais tarde.
						jQuery("#estoque_qt_atual").val("");
						jQuery("#estoque_qt_emprestada").val("");
						
						if(data.encontrado){
							jQuery("#btnAlteraEstoque").prop("disabled", false); // habilita botão de alterar estoque
							
							jQuery("#estoque_patr_mov").prop("disabled", false);
							jQuery("#estoque_local_mov").prop("disabled", false);
							
							jQuery("#estoque_patr_rem").prop("disabled", false);
							
							
							var t = jQuery("#estoque_patr_mov");
							t.append(new Option("",-1));
							
							var t2 = jQuery("#estoque_patr_rem");
							t2.append(new Option("",-1));
							
							for(x of data.consultas){
								
								//t.append(new Option(x[4],x[0]));
								var opt = '<option value="' + x[4] + '" qtAtual="' + x[2] + '" qtEmpr="' + x[3] + '">' + x[4] + '</option>';
								t.append(opt);
								t2.append(opt);
							
							}
						}
						

					}else{
						jQuery("#msgTopoHistorico").html("Erro ao buscar estoque, tipo de item errado: " + data.tipo + "<br>");
					}
					
					return;
					
					
				}else{
					jQuery("#btnAlteraEstoque").prop("disabled", true);
					
					jQuery("#msgTopoHistorico").html("Erro ao buscar estoque, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
					
					if(data.msg2 != ""){
						jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
					}
				}
			}
		});
		
		
	}else{
		// deixa botão desabilitado se não há um item ou local não selecionado
		
		jQuery("#btnAlteraEstoque").prop("disabled", true);
		
		
	}
	
	
	
}


function alteraEstoque(){
	window.scrollTo({ top: 0, behavior: 'smooth' });
	resetMsgTopo();
	
	var sel_op = jQuery("#estoque_op option:selected").text(); // operação selecionada
	var idLocal = jQuery("#estoque_local option:selected").val();
	var idItem = jQuery("#estoque_item option:selected").val();

	var tipoItem = 	jQuery("#estoque_item option:selected").attr("tipo");

	var qtAtual = jQuery("#estoque_qt_atual").val();
	var qtEmpr = jQuery("#estoque_qt_emprestada").val();
	
	var addQt = jQuery("#estoque_qt_add").val();
	var addPatr = jQuery("#estoque_patr_add").val();
	
	var remQt = jQuery("#estoque_qt_rem").val();
	var remPatr = jQuery("#estoque_patr_rem option:selected").val();
	
	var movQt = jQuery("#estoque_qt_mov").val();
	var movPatr = jQuery("#estoque_patr_mov option:selected").val();
	var movLocalId = jQuery("#estoque_local_mov option:selected").val();
	
	var obs = jQuery("#estoque_obs").val();
	
	
	// validações
	if(idLocal <= 0){ 
		jQuery("#msgErro").html('<p>ID do local incorreto.</p>');
		return;
	}
	if(idItem <= 0){
		jQuery("#msgErro").html('<p>ID do item incorreto.</p>');
		return;
	}
	if(tipoItem !== "Consumo" && tipoItem !== "Permanente"){
		jQuery("#msgErro").html('<p>Tipo do item incorreto.</p>');
		return;
	}
	
	
	if(sel_op === "Adicionar"){
		
		if(tipoItem === "Consumo"){
			if(addQt <=0){
				jQuery("#msgErro").html('<p>Quantidade a adicionar precisar ser um inteiro positivo.</p>');
				return;
			}
			
			jQuery.post({
				url: ajax_url,
				type: "POST",
				dataType: "JSON",
				data: {
					"action": 'alteraEstoque',
					"nonce":    nonce_alteraEstoque,
					"op": sel_op,
					"idLocal": idLocal,
					"idItem": idItem,
					"tipo": tipoItem,
					"addQt": addQt,
					"obs": obs
				},
				success: alteraEstoque_retorno
			});
			
			return;
			
		}else if(tipoItem === "Permanente"){
			
			if(addPatr <=0){
				jQuery("#msgErro").html('<p>Número do patrimônio precisa ser um inteiro positivo.</p>');
				return;
			}
			
			jQuery.post({
				url: ajax_url,
				type: "POST",
				dataType: "JSON",
				data: {
					"action": 'alteraEstoque',
					"nonce":    nonce_alteraEstoque,
					"op": sel_op,
					"idLocal": idLocal,
					"idItem": idItem,
					"tipo": tipoItem,
					"addPatr": addPatr,
					"obs": obs
				},
				success: alteraEstoque_retorno
			});
			
			return;
			
		}
	
		return;
	}
	
	if(sel_op === "Movimentar"){
		
		if(movLocalId <= 0 ){
			jQuery("#msgErro").html('<p>É necessário escolher um local de destino válido.</p>');
			return;
		}
		
		if(movLocalId == idLocal ){
			jQuery("#msgErro").html('<p>É necessário escolher um local de destino diferente do de origem.</p>');
			return;
		}
		
		if(movQt > (qtAtual-qtEmpr)){
			jQuery("#msgErro").html('<p>Quantidade a mover precisa ser menor do que o estoque que não está emprestado. Estoque disponível para movimentação: ' + (qtAtual-qtEmpr) + '.</p>');
			return;
		}
		
		
		if(tipoItem === "Consumo"){
			if(movQt <=0){
				jQuery("#msgErro").html('<p>Quantidade a mover precisar ser um inteiro positivo.</p>');
				return;
			}
			

			
			jQuery.post({
				url: ajax_url,
				type: "POST",
				dataType: "JSON",
				data: {
					"action": 'alteraEstoque',
					"nonce":    nonce_alteraEstoque,
					"op": sel_op,
					"idLocal": idLocal,
					"idItem": idItem,
					"tipo": tipoItem,
					"movQt": movQt,
					"movPatr": movPatr,
					"movLocalId": movLocalId,
					"obs": obs
				},
				success: alteraEstoque_retorno
			});
			
			return;
			
		}else if(tipoItem === "Permanente"){
			
			if(movPatr <=0){
				jQuery("#msgErro").html('<p>Deve ser selecionado um patrimônio válido a ser movimentado.</p>');
				return;
			}
			
			jQuery.post({
				url: ajax_url,
				type: "POST",
				dataType: "JSON",
				data: {
					"action": 'alteraEstoque',
					"nonce":    nonce_alteraEstoque,
					"op": sel_op,
					"idLocal": idLocal,
					"idItem": idItem,
					"tipo": tipoItem,
					"movQt": movQt,
					"movPatr": movPatr,
					"movLocalId": movLocalId,
					"obs": obs
				},
				success: alteraEstoque_retorno
			});
			
			return;
			
		}
	
		return;
	}
	
	if(sel_op === "Remover"){
		
		if(tipoItem === "Consumo"){
			if(remQt <=0){
				jQuery("#msgErro").html('<p>Quantidade a remover precisar ser um inteiro positivo.</p>');
				return;
			}
			
			if(remQt > (qtAtual-qtEmpr)){
				jQuery("#msgErro").html('<p>Quantidade a remover precisa ser menor do que o estoque que não está emprestado. Estoque disponível para movimentação: ' + (qtAtual-qtEmpr) + '.</p>');
				return;
			}
			
			jQuery.post({
				url: ajax_url,
				type: "POST",
				dataType: "JSON",
				data: {
					"action": 'alteraEstoque',
					"nonce":    nonce_alteraEstoque,
					"op": sel_op,
					"idLocal": idLocal,
					"idItem": idItem,
					"tipo": tipoItem,
					"remQt": remQt,
					"remPatr": remPatr,
					"obs": obs
				},
				success: alteraEstoque_retorno
			});
			
			return;
			
		}else if(tipoItem === "Permanente"){
			
			if(remPatr <=0){
				jQuery("#msgErro").html('<p>Deve ser selecionado um patrimônio válido a ser removido.</p>');
				return;
			}
			if(remQt > (qtAtual-qtEmpr)){
				jQuery("#msgErro").html('<p>Quantidade a remover precisa ser menor do que o estoque que não está emprestado. Estoque disponível para movimentação: ' + (qtAtual-qtEmpr) + '.</p>');
				return;
			}
			
			jQuery.post({
				url: ajax_url,
				type: "POST",
				dataType: "JSON",
				data: {
					"action": 'alteraEstoque',
					"nonce":    nonce_alteraEstoque,
					"op": sel_op,
					"idLocal": idLocal,
					"idItem": idItem,
					"tipo": tipoItem,
					"remQt": remQt,
					"remPatr": remPatr,
					"obs": obs
				},
				success: alteraEstoque_retorno
			});
			
			return;
			
		}
		return;
	}
}

function alteraEstoque_retorno(data){
	
	if(!data.error){
		jQuery("#formAlteraEstoque").trigger("reset");
		jQuery("#formAlteraEstoque").hide();
		jQuery("#btnAlteraEstoque").prop("disabled", true);
		
		jQuery("#formAdicionarEstoque").show();
		jQuery("#formRemoverEstoque").hide();
		jQuery("#formMoverEstoque").hide();
		
		
		// reinicia campos do formulario
		jQuery("#estoque_qt_atual").val("");
		jQuery("#estoque_qt_emprestada").val("");

		jQuery("#estoque_qt_add").prop("disabled", true);
		jQuery("#estoque_patr_add").prop("disabled", true);
		
		jQuery("#estoque_qt_rem").prop("disabled", true);
		jQuery("#estoque_patr_rem").prop("disabled", true);
		jQuery("#estoque_patr_rem").empty();
		
		jQuery("#estoque_qt_mov").prop("disabled", true);
		jQuery("#estoque_patr_mov").prop("disabled", true);
		jQuery("#estoque_patr_mov").empty();
		jQuery("#estoque_local_mov").prop("disabled", true);
		
		getEstoque();
		
		jQuery("#msgResultado").html("Alteração feita com sucesso.<br>");
	}else{
		
		jQuery("#msgResultado").html("<p>Erro ao alterar estoque, recarregue a página ou contacte o administrador!</p><p>Erro: " + data.msg + "</p>");
		
		if(data.msg2 != ""){
			jQuery("#msgErro").html('<p>' + data.msg2 + '</p>');
		}
	}
	
}

function alteraQtMovRem(tipo){
	var sel;
	
	if(tipo === "mover"){
		sel = "#estoque_patr_mov";
	}else if(tipo === "remover"){
		sel = "#estoque_patr_rem";
	}else{
		jQuery("#msgErro").html("<p>Alguma coisa deu errado ao selecionar patrimônio para mover/remover.</p>");
		return;
	}
	
	var qtAtual = jQuery(sel + " option:selected").attr("qtAtual");
	var qtEmpr = jQuery(sel + " option:selected").attr("qtEmpr");
	
	jQuery("#estoque_qt_atual").val(qtAtual);
	jQuery("#estoque_qt_emprestada").val(qtEmpr);
	
}

function accordionItemEstoque(data){
	var nameAcc = 'accordionExample' + data[11];
	var nameItem = 'collapseOne' + data[11];
	var itemDescricao = data[8];
	
	var linkDatasheet;
	var linkImagem;
	
	if(data[9] === ""){
		linkDatasheet = '<div class="col"><p class="col">Datasheet Indisponível </p></div>'
	}else{
		linkDatasheet = '<div class="col"><a href="'+ data[9] + '" target="blank">Datasheet</a></div>';
	}
	
	if(data[10] === ""){
		linkImagem = '<div class="col"><p class "col">Imagem indisponível</p></div>'
	}else{
		linkImagem = '<div class="col"><img src="'+ data[10] + '" class="img-thumbnail col" alt="imag_componente"></div>';
	}
	
	
	var base = '<div class="accordion accordion-flush" id="' + nameAcc + '">' +
					'<div class="accordion-item">' + 
						'<h2 class="accordion-header">' + 
							'<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' + nameItem + '" aria-expanded="true" aria-controls="' + nameItem +'">' +
							data[5] + 
							'</button>' +
						'</h2>' +
						'<div id="' + nameItem + '" class="accordion-collapse collapse" data-bs-parent="' + nameAcc + '">' +
							'<div class="accordion-body">'+
								'<div class="row mb-2 align-items-center">' + 
									'<p>' + itemDescricao + '</p>' +
								'</div>'+
								'<div class="row mb-2 align-items-center">' + 
									linkImagem +
									linkDatasheet + 
								'</div>' + 
							'</div>' +
						'</div>'+
					'</div>'+
				'</div>';
				
	
				
	return base;
}


/*
	funções da página Solicitações
*/

function initTabelaEstoque_solicitacoes(){
	new DataTable('#tab_consulta_estoque',{
		"language": {
			url: 'https://cdn.datatables.net/plug-ins/2.1.3/i18n/pt-BR.json'
		},
		"columnDefs": [
			{width : '22%', targets : 0},
			{width : '47%', targets : 1},
			{width : '7%', targets : 2},
			{width : '7%', targets : 3},
			{width : '12%', targets : 4},
			{width : '5%', targets : 5},
		],
		lengthMenu: [
			[10, 20, 30, -1],
			[10, 20, 30, 'Tudo']
		]
	});

}

function initTabelaSolicitacoes_solicitacoes(){
	new DataTable('#tab_consulta_solicitacoes',{
		"language": {
			url: 'https://cdn.datatables.net/plug-ins/2.1.3/i18n/pt-BR.json'
		},
		"columnDefs": [
			{width : '5%', targets : 0},
			{width : '18%', targets : 1},
			{width : '7%', targets : 3},
			{width : '5%', targets : 4},
		],
		lengthMenu: [
			[10, 20, 30, -1],
			[10, 20, 30, 'Tudo']
		],
		order: {
			idx: 0,
			dir: 'desc'
		}
	});

}


function atualizaTabEstoque_solicitacoes(data){
	
	var t = jQuery("#tab_consulta_estoque").DataTable();
	
	if(!data.error){
		
		t.clear();
	
		for(x of data.consultas){
			
			t.row.add([
					x[7],
					//x[5],
					accordionItemEstoque(x),
					x[2],
					x[3],
					x[4],
					'<i class="fas fa-cart-arrow-down" title="Solicitar" onclick="get1Estoque_solicitacao(' + x[0] +','+ x[1] + ',' + x[4] + ')"></i> '
					]);
		}
		
		
		t.draw();
		
	
	}else{
		jQuery("#msgTopoHistorico").html("Erro ao buscar estoque, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
	}
	
}


function solicitacao_formGetLocal(){
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'getLocais',
			"nonce":    nonce_get_Locais
		},
		success: function(data){
			var t = jQuery("#solicitacao_local");
			
			
			if(data.error){
				jQuery("#msgTopoHistorico").html("Erro ao buscar histórico de locais, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
				
				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}
			}


			t.empty();
			t.append(new Option("",-1));
			
			for(x of data.consultas){
				t.append(new Option(x[1],x[0]));
			}
			
		}
	});
	
}


function solicitacao_formGetItem(){
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'getItens',
			"nonce":    nonce_getItens
		},
		success: function(data){
			var t = jQuery("#solicitacao_item");
			
			
			if(data.error){
				jQuery("#msgTopoHistorico").html("Erro ao buscar histórico de locais, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
				
				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}
			}


			t.empty();
			t.append(new Option("",-1));
			
			for(x of data.consultas){
				//t.append(new Option(x[1],x[0]));
				var opt = '<option value="' + x[0] + '" tipo="' + x[3] + '">' + x[1] + '</option>';
				t.append(opt);
			}
			
		}
	});
	
}


function get1Estoque_solicitacao(idItem,idLocal,patrimonio){
	window.scrollTo({ top: 0, behavior: 'smooth' });
	
	resetMsgTopo();
	
	jQuery("#solicitacao_local").val(idLocal);
	jQuery("#solicitacao_item").val(idItem);

	jQuery("#solicitacao_qt").val("");
	jQuery("#solicitacao_qt").prop("disabled", true); 
	
	jQuery("#solicitacao_patr").val(patrimonio);
	jQuery("#solicitacao_patr").prop("disabled", true); 
	
	jQuery("#solicitacao_prof").val("");
	jQuery("#solicitacao_prof").prop("disabled", true); 
	
	
	jQuery("#formPatrimonio").hide();
	jQuery("#formProfessor").hide();
	
	// desabilita botões do formulário
	jQuery("#btnSolicitar").prop("disabled", true); 
	jQuery("#btnSolicitar_cancela").prop("disabled", true); 
	

	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'get1Estoque',
			"nonce":    nonce_get1Estoque,
			"idLocal": idLocal,
			"idItem": idItem
		},
		success: function(data){
			if(data.error){
				jQuery("#msgTopoHistorico").html("Erro ao buscar estoque, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
				
				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}
				
				return;
			}
			
			
			if(!data.encontrado){
				jQuery("#msgTopoHistorico").html("Não foi encontrado o estoque buscado, atualize a página. Se o problema persistir contacte o administrador! <br>");
				
				return;
			}
			
			
			if(data.tipo === "Consumo"){
				jQuery("#solicitacao_qt").prop("disabled", false); 
				
			}else if(data.tipo === "Permanente"){
				jQuery("#solicitacao_qt").val(1);
				
				jQuery("#formPatrimonio").show();
				
				jQuery("#formProfessor").show();
				jQuery("#solicitacao_prof").prop("disabled", false);
				
			}else{
				jQuery("#msgTopoHistorico").html("Erro ao buscar estoque, tipo de item errado: " + data.tipo + "<br>");
				return;
			}
			
			jQuery("#formAlteraEstoque").show();
			
			jQuery("#btnSolicitar").prop("disabled", false); 
			jQuery("#btnSolicitar_cancela").prop("disabled", false); 
			
			return;
			
			
		}
	});
	
}


function adicionaSolicitacao() {
	window.scrollTo({ top: 0, behavior: 'smooth' });
	
	resetMsgTopo();
	
	var idLocal = jQuery("#solicitacao_local option:selected").val();
	var idItem = jQuery("#solicitacao_item option:selected").val();
	var qt = jQuery("#solicitacao_qt").val();
	var patr = jQuery("#solicitacao_patr").val();
	var prof = jQuery("#solicitacao_prof").val();
	var tipoItem = 	jQuery("#solicitacao_item option:selected").attr("tipo");
	
	// validações
	if(qt <=0){
		jQuery("#msgErro").html("A quantidade pedida deve ser maior do que 0. Favor ajustar o quantitativo.");
		return;
	}
	
	if(tipoItem === "Permanente" && !prof){
		jQuery("#msgErro").html("Favor inserir um professor responsável.");
		return;
	}
	
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'addSolicitacao',
			"nonce":    nonce_addSolicitacao,
			"idLocal": idLocal,
			"idItem": idItem,
			"qt": qt,
			"patr": patr,
			"prof": prof,
			"tipo": tipoItem
		},
		dataType: "JSON",
		success: function(data){
			if(data.error){
				jQuery("#msgResultado").html('');
				jQuery("#msgErro").html("Erro ao adicionar o novo item, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
				
				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}
				return;
			}
			
			jQuery("#formAlteraEstoque").hide();
			jQuery("#formAlteraEstoque").trigger("reset");
			
			jQuery("#msgResultado").html('Solicitação adicionada com sucesso.');
			
			
			getSolicitacoes();
			
		}
	});
	
}


function getSolicitacoes(){
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'getSolicitacao',
			"nonce":    nonce_getSolicitacao
		},
		dataType: "JSON",
		success: atualizaTabSolicitacoes_solicitacoes
	});	
}


function atualizaTabSolicitacoes_solicitacoes(data){
	
	if(data.error){
		jQuery("#msgTopoHistorico").html("Erro ao buscar solicitações, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
		return;
	}
	
	
	var t = jQuery("#tab_consulta_solicitacoes").DataTable();
	t.clear();

	for(x of data.consultas){
		var htmlBtn;
		
		if(x[7] !== "solicitado"){
			htmlBtn = '';
		}else{
			htmlBtn = '<i class="fas fa-trash-alt" title="Cancelar" onclick="cancelaSolicitacao(' + x[0] + ')"></i> ';
		}
		
		
		t.row.add([
				x[0],
				x[1].replace(" ", "<br>"),
				accordionSolicitacao(x),
				x[7],
				htmlBtn
				]);
	}
	
	
	t.draw();
	
}


function accordionSolicitacao(data){
	var nameAcc = 'accordionExample' + data[0];
	var nameItem = 'collapseOne' + data[0];
	
	var dataSolicitacao = data[1];
	var dataAtendimento = (data[2]===null)? '--' : data[2];
	var dataDevolucao = (data[3]===null)? '--' : data[3];
	var qtPedida = data[4];
	var qtAtendida = (data[5]===null)? '--' : data[5];
	var qtDevolvida = (data[6]===null)? '--' : data[6];
	var obs = data[8];
	var itemNome = data[9];
	var itemTipo = data[10];
	var localNome = data[11];
	var patrimonio = (data[12]===null)? '--' : data[12];
	var prof = (data[13]===null)? '--' : data[13];
	
	
	
	var base = '<div class="accordion accordion-flush" id="' + nameAcc + '">' +
					'<div class="accordion-item">' + 
						'<h2 class="accordion-header">' + 
							'<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' + nameItem + '" aria-expanded="true" aria-controls="' + nameItem +'">' +
							"Item: " + itemNome + "<br>" +
							"Local: " + localNome +
							'</button>' +
						'</h2>' +
						'<div id="' + nameItem + '" class="accordion-collapse collapse" data-bs-parent="' + nameAcc + '">' +
							'<div class="accordion-body">'+
								addRowAccordion("Data atendimento:", dataAtendimento) + "<hr>"+
								addRowAccordion("Data devolução:", dataDevolucao) + "<hr>"+
								addRowAccordion("Qt. pedida:", qtPedida) +"<hr>"+
								addRowAccordion("Qt. atendida:", qtAtendida) + "<hr>"+
								addRowAccordion("Qt. devolvida:", qtDevolvida) +"<hr>"+
								addRowAccordion("Tipo item:", itemTipo) +"<hr>"+
								addRowAccordion("Patrimônio:", patrimonio) +"<hr>"+
								addRowAccordion("Prof. responsável:", prof) +"<hr>"+
								addRowAccordion("Obs:", obs)+
							'</div>' +
						'</div>'+
					'</div>'+
				'</div>';
				
	
				
	return base;
}


function addRowAccordion(i1, i2){
	return '<div class="row mb-2 align-items-center">' + 
				'<div class="col">' + i1 + '</div>' + 
				'<div class="col">' + i2 + '</div>' +
			'</div>';
}

function cancelaSolicitacao(idSolicitacao){
	window.scrollTo({ top: 0, behavior: 'smooth' });
	
	resetMsgTopo();
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'cancelaSolicitacao',
			"nonce":	nonce_cancelaSolicitacao,
			"id": idSolicitacao
		},
		dataType: "JSON",
		success: function(data){
				if(data.error){
					jQuery("#msgTopoHistorico").html("Erro ao cancelar solicitação, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
					
					if(data.msg2 != ""){
						jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
					}
					
					return;
				}
				
				jQuery("#msgTopoHistorico").html("Solicitação cancelada com sucesso.");
				
				getSolicitacoes();
				
				return;
			
		}
	});
}


/*
	funções da página gerência
*/

function getSolicitacoesTudo(){
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'getSolicitacaoTudo',
			"nonce":    nonce_getSolicitacaoTudo
		},
		dataType: "JSON",
		success: atualizaTabSolicitacoes_gerencia
	});	
}

function atualizaTabSolicitacoes_gerencia(data){
	
	if(data.error){
		jQuery("#msgTopoHistorico").html("Erro ao buscar solicitações, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
		return;
	}
	
	
	var t = jQuery("#tab_consulta_solicitacoes").DataTable();
	t.clear();

	for(x of data.consultas){
		var htmlBtn = '';
		
		
		if(x[7] === "cancelado" || x[7] === "devolvido"){
			htmlBtn = '';
		}else{
			htmlBtn = '<i class="far fa-edit" title="Alterar" onclick="get1Solicitacao_gerencia(' + x[0] + ')"></i> ';
		}
		
		
		t.row.add([
				x[0],
				x[1].replace(" ", "<br>"),
				accordionSolicitacao_gerencia(x),
				x[7],
				htmlBtn
				]);
	}
	
	
	t.draw();
	
}

function accordionSolicitacao_gerencia(data){
	var nameAcc = 'accordionExample' + data[0];
	var nameItem = 'collapseOne' + data[0];
	
	var dataSolicitacao = data[1];
	var dataAtendimento = (data[2]===null)? '--' : data[2];
	var dataDevolucao = (data[3]===null)? '--' : data[3];
	var qtPedida = data[4];
	var qtAtendida = (data[5]===null)? '--' : data[5];
	var qtDevolvida = (data[6]===null)? '--' : data[6];
	var obs = data[8];
	var itemNome = data[9];
	var itemTipo = data[10];
	var localNome = data[11];
	var patrimonio = (data[12]===null)? '--' : data[12];
	var prof = (data[13]===null)? '--' : data[13];
	var nomeUsuario = data[14];
	
	
	var base = '<div class="accordion accordion-flush" id="' + nameAcc + '">' +
					'<div class="accordion-item">' + 
						'<h2 class="accordion-header">' + 
							'<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' + nameItem + '" aria-expanded="true" aria-controls="' + nameItem +'">' +
							"Item: " + itemNome + "<br>" +
							"Local: " + localNome + "<br>" + 
							"Solicitante: " + nomeUsuario +
							'</button>' +
						'</h2>' +
						'<div id="' + nameItem + '" class="accordion-collapse collapse" data-bs-parent="' + nameAcc + '">' +
							'<div class="accordion-body">'+
								addRowAccordion("Data atendimento:", dataAtendimento) + "<hr>"+
								addRowAccordion("Data devolução:", dataDevolucao) + "<hr>"+
								addRowAccordion("Qt. pedida:", qtPedida) +"<hr>"+
								addRowAccordion("Qt. atendida:", qtAtendida) + "<hr>"+
								addRowAccordion("Qt. devolvida:", qtDevolvida) +"<hr>"+
								addRowAccordion("Tipo item:", itemTipo) +"<hr>"+
								addRowAccordion("Patrimônio:", patrimonio) +"<hr>"+
								addRowAccordion("Prof. responsável:", prof) +"<hr>"+
								addRowAccordion("Obs:", obs)+
							'</div>' +
						'</div>'+
					'</div>'+
				'</div>';
				
	
				
	return base;
}


function get1Solicitacao_gerencia(idSolicitacao){
	window.scrollTo({ top: 0, behavior: 'smooth' });
	
	resetMsgTopo();
	
	jQuery("#formAlteraSolicitacao").trigger("reset");
	
	// desabilita botões e campos do formulário
	jQuery("#btnAtender").prop("disabled", true); 
	jQuery("#btnDevolver").prop("disabled", true); 
	jQuery("#btnCancelar").prop("disabled", true); 
	
	jQuery("#btnAtender").hide(); 
	jQuery("#btnDevolver").hide(); 
	jQuery("#btnCancelar").hide(); 
	
	jQuery("#gerencia_qt_at").prop("disabled", true); 
	jQuery("#gerencia_qt_dev").prop("disabled", true); 
	
	jQuery("#gerencia_prof").prop("disabled", true); 
	

	jQuery.post({
		url: ajax_url,
		type: "POST",
		dataType: "JSON",
		data: {
			"action": 'get1Solicitacao',
			"nonce":    nonce_get1Solicitacao,
			"idSolicitacao": idSolicitacao
		},
		success: function(data){
			if(data.error){
				jQuery("#msgTopoHistorico").html("Erro ao buscar solicitação, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
				
				if(data.msg2 != ""){
					jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
				}
				
				return;
			}
			
			
			if(!data.encontrado){
				jQuery("#msgTopoHistorico").html("Não foi encontrado a solicitação buscada, atualize a página. Se o problema persistir contacte o administrador! <br>");
				
				return;
			}
			
			
			jQuery("#gerencia_id").val(data.consultas[0]);
			jQuery("#gerencia_solicitante").val(data.consultas[14]);
			jQuery("#gerencia_item").val(data.consultas[9]);
			jQuery("#gerencia_local").val(data.consultas[11]);
			jQuery("#gerencia_data").val(data.consultas[1]);
			jQuery("#gerencia_qt").val(data.consultas[4]);
			jQuery("#gerencia_qt_at").val(data.consultas[5]);
			jQuery("#gerencia_qt_dev").val(data.consultas[6]);
			jQuery("#gerencia_patr").val(data.consultas[12]);
			jQuery("#gerencia_prof").val(data.consultas[13]);
			jQuery("#gerencia_obs").val(data.consultas[8]);
			
			var estado = 0;
			switch(data.consultas[7]){
				case "solicitado":
					estado = 0;
					jQuery("#btnAtender").prop("disabled", false);
					jQuery("#btnAtender").show();
					
					jQuery("#btnCancelar").prop("disabled", false);
					jQuery("#btnCancelar").show();
					
					jQuery("#gerencia_qt_at").prop("disabled", false); 
					
					break;
				
				case "atendido":
					estado = 1;
					
					jQuery("#btnDevolver").prop("disabled", false);
					jQuery("#btnDevolver").show();
					
					jQuery("#gerencia_qt_dev").prop("disabled", false); 
					
					break;
				
				case "devolvido":
					estado = 2;
					break;
				
				case "cancelado":
					estado = 3;
					break;
				
				default:
					estado = 0;
					break;
			}
			
			jQuery("#gerencia_estado").val(estado);
			
			jQuery("#formAlteraSolicitacao").show();
			
			//jQuery("#btnSolicitar").prop("disabled", false); 
			//jQuery("#btnSolicitar_cancela").prop("disabled", false); 
			
			return;
			
			
		}
	});
	
}

function cancelaSolicitacaoGerencia(){
	window.scrollTo({ top: 0, behavior: 'smooth' });
	
	resetMsgTopo();
	
	
	var idSolicitacao = jQuery("#gerencia_id").val();
	var obs = jQuery("#gerencia_obs").val();
	
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'cancelaSolicitacaoGerencia',
			"nonce":	nonce_cancelaSolicitacaoGerencia,
			"id": idSolicitacao,
			"obs": obs
		},
		dataType: "JSON",
		success: function(data){
				if(data.error){
					jQuery("#msgTopoHistorico").html("Erro ao cancelar solicitação, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
					
					if(data.msg2 != ""){
						jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
					}
					
					return;
				}
				
				jQuery("#msgTopoHistorico").html("Solicitação cancelada com sucesso.");
				
				jQuery("#formAlteraSolicitacao").hide();
				jQuery("#formAlteraSolicitacao").trigger("reset");
				
				getSolicitacoesTudo();
				
				return;
			
		}
	});
}

function atendeSolicitacao(){
	window.scrollTo({ top: 0, behavior: 'smooth' });
	
	resetMsgTopo();
	
	var idSolicitacao = jQuery("#gerencia_id").val();
	var qtPedida = Number.parseInt(jQuery("#gerencia_qt").val());
	var qtAtendida = Number.parseInt(jQuery("#gerencia_qt_at").val());
	
	var obs = jQuery("#gerencia_obs").val();
	
	// validações 
	if(qtAtendida > qtPedida){
		jQuery("#msgAviso").html('<p>Quantidade atendida não pode ser maior que a pedida... ' + qtPedida + "/" + qtAtendida + '</p>');
		return;
	}
	
	if(qtAtendida == 0){
		jQuery("#msgAviso").html('<p>Quantidade atendida deve ser maior que zero... </p>');
		return;
	}
	
	
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'atendeSolicitacao',
			"nonce":	nonce_atendeSolicitacao,
			"id": idSolicitacao,
			"qt": qtAtendida,
			"obs": obs
		},
		dataType: "JSON",
		success: function(data){
				if(data.error){
					jQuery("#msgTopoHistorico").html("Erro ao atender solicitação, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
					
					if(data.msg2 != ""){
						jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
					}
					
					return;
				}
				
				jQuery("#msgTopoHistorico").html("Solicitação atendida com sucesso.");
				
				jQuery("#formAlteraSolicitacao").hide();
				jQuery("#formAlteraSolicitacao").trigger("reset");
				
				getSolicitacoesTudo();
				
				return;
			
		}
	});
}

function devolveSolicitacao(){
	window.scrollTo({ top: 0, behavior: 'smooth' });
	
	resetMsgTopo();
	
	var idSolicitacao = jQuery("#gerencia_id").val();
	var qtPedida = Number.parseInt(jQuery("#gerencia_qt").val());
	var qtAtendida = Number.parseInt(jQuery("#gerencia_qt_at").val());
	var qtDevolvida = Number.parseInt(jQuery("#gerencia_qt_dev").val());
	
	var obs = jQuery("#gerencia_obs").val();
	
	// validações 
	if(qtDevolvida > qtAtendida ){
		jQuery("#msgAviso").html('<p>Quantidade devolvida não pode ser maior que a atendida... ' + qtDevolvida + "/" + qtAtendida + '</p>');
		return;
	}
	
	
	jQuery.post({
		url: ajax_url,
		type: "POST",
		data: {
			"action": 'devolveSolicitacao',
			"nonce":	nonce_devolveSolicitacao,
			"id": idSolicitacao,
			"qt": qtDevolvida,
			"obs": obs
		},
		dataType: "JSON",
		success: function(data){
				if(data.error){
					jQuery("#msgTopoHistorico").html("Erro ao devolver solicitação, recarregue a página ou contacte o administrador! <br> Erro: " + data.msg + "<br>");
					
					if(data.msg2 != ""){
						jQuery("#msgAviso").html('<br><p>' + data.msg2 + '</p>');
					}
					
					return;
				}
				
				jQuery("#msgTopoHistorico").html("Solicitação devolvida com sucesso.");
				
				jQuery("#formAlteraSolicitacao").hide();
				jQuery("#formAlteraSolicitacao").trigger("reset");
				
				getSolicitacoesTudo();
				
				return;
			
		}
	});
}

