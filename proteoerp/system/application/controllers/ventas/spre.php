<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
class Spre extends Controller {
	var $mModulo = 'SPRE';
	var $titp    = 'Presupuestos';
	var $tits    = 'Presupuestos';
	var $url     = 'ventas/spre/';

	function Spre(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'SPRE', $ventana=0 );
	}

	function index(){
		$this->instalar();
		$this->datasis->modintramenu( 900, 650, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//******************************************************************
	// Layout en la Ventana
	//
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$grid1   = $this->defgridit();
		$param['grids'][] = $grid1->deploy();

		// Configura los Paneles
		$readyLayout = $grid->readyLayout2( 212, 220, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		//Botones Panel Izq
		$grid->wbotonadd(array('id'=>'boton1',  'img'=>'assets/default/images/print.png',   'alt' => 'Reimprimir',      'label'=>'Reimprimir'));

		if($this->datasis->sidapuede('SFAC','INCLUIR%' ))
			$grid->wbotonadd(array('id'=>'bffact',  'img'=>'images/RedoGreen.png', 'alt' => 'Facturar',        'label'=>'Facturar'));

		if($this->datasis->sidapuede('SNTE','INCLUIR%' ))
			$grid->wbotonadd(array('id'=>'bsnte',   'img'=>'images/RedoBlue.png', 'alt' => 'Nota de Entrega', 'label'=>'N.Entrega'));

		$grid->wbotonadd(array('id'=>'bcorreo', 'img'=>'assets/default/images/mail_btn.png','alt' => 'Notificacion',  'label'=>'Notificar por email'));
		$grid->wbotonadd(array('id'=>'bfavori', 'img'=>'images/star.png','alt'     => 'Marcar Favorito',      'label'=>'Marcar Favorito'));
		$grid->wbotonadd(array('id'=>'bgrfavo', 'img'=>'images/star.png','alt'     => 'Grupo de Favorito',    'label'=>'Grupo de Favorito'));

		$WestPanel = $grid->deploywestp();

		//Panel Central
		$centerpanel = $grid->centerpanel( $id = 'radicional', $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		$adic = array(
			array('id'=>'fedita' , 'title'=>'Agregar/Editar Registro'),
			array('id'=>'ffact'  , 'title'=>'Convertir en factura'),
			array('id'=>'scliexp', 'title'=>'Ficha de Cliente' ),
			array('id'=>'fshow'  , 'title'=>'Mostrar Registro' ),
			array('id'=>'fborra' , 'title'=>'Eliminar Registro')
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']    = $WestPanel;
		$param['script']       = '';
		//$param['EastPanel']  = $EastPanel;
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('SPRE', 'JQ');
		$param['otros']        = $this->datasis->otros('SPRE', 'JQ');
		$param['centerpanel']  = $centerpanel;
		//$param['funciones']    = $funciones;
		$param['temas']        = array('proteo','darkness','anexos1');
		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;
		$param['tamano']       = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);
	}

	//******************************************************************
	// Funciones de los Botones
	//
	function bodyscript( $grid0, $grid1 ){

		$btguardar ='
			"Guardar": function() {
				if($("#scliexp").dialog( "isOpen" )===true) {
					$("#scliexp").dialog("close");
				}
				var murl = $("#df1").attr("action");
				//allFields.removeClass( "ui-state-error" );
				$.ajax({
					type: "POST", dataType: "html", async: false,
					url: murl,
					data: $("#df1").serialize(),
					success: function(r,s,x){
						try{
							var json = JSON.parse(r);
							if (json.status == "A"){
								$( "#fedita" ).dialog( "close" );
								grid.trigger("reloadGrid");
								'.$this->datasis->jwinopen(site_url('formatos/ver/PRESUP').'/\'+json.pk.id+\'/id\'').';
								return true;
							} else {
								apprise(json.mensaje);
							}
						} catch(e) { $("#fedita").html(r);}
					}
				})
			},
		';

		$btduplicar = '
			"Duplicar": function() {
				if($("#scliexp").dialog( "isOpen" )===true) {
					$("#scliexp").dialog("close");
				}
				var bValid = true;
				var murl = $("#df1").attr("action");
				var m = 0;
				murl = murl.replace("update","insert");
				m = murl.indexOf("insert")+6;
				if ( m > 6){murl = murl.substring(0,m);}
				$.ajax({
					type: "POST", dataType: "html", async: false,
					url: murl,
					data: $("#df1").serialize(),
					success: function(r,s,x){
						try{
							var json = JSON.parse(r);
							if (json.status == "A"){
								apprise("Registro Guardado");
								$( "#fedita" ).dialog( "close" );
								grid.trigger("reloadGrid");
								'.$this->datasis->jwinopen(site_url('formatos/ver/PRESUP').'/\'+json.pk.id+\'/id\'').';
								return true;
							} else {
								apprise(json.mensaje);
							}
						}catch(e){
							$("#fedita").html(r);
						}
					}
				})
			},
		';

		$btcancelar = '
		"Cancelar": function() {
				$("#fedita").html("");
				$( this ).dialog( "close" );
			}
		';

		$btcerrar = '
			close: function() {
				if($("#scliexp").dialog( "isOpen" )===true) {
					$("#scliexp").dialog("close");
				}
				$("#fedita").html("");
				//allFields.val("").removeClass( "ui-state-error" );
			}
		';

		$bodyscript = '		<script type="text/javascript">';

		$bodyscript .= '
		function spreadd() {
			var id  = $("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			var favo = "N";
			var ruta = "'.site_url('ventas/spre/dataedit/create').'";
			if(id){
				var ret = $("#newapi'.$grid0.'").getRowData(id);
				favo = ret.favorito;
				ruta = "'.site_url('ventas/spre/dataedit/modify/').'/"+id;
			}

			$.post(ruta,
			function(data){
				if (favo != "S"){  // Guardar y Cancelar
					$("#fedita").dialog({
						buttons: {
							'.$btguardar.'
							'.$btcancelar.'
						},
						'.$btcerrar.'
					});
				} else { // Solo Duplicar
					$("#fedita").dialog({
						buttons: {
							'.$btduplicar.'
							'.$btcancelar.'
						},
						'.$btcerrar.'
					});
				};
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function spreedit(){
			var id     = $("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret  = $("#newapi'.$grid0.'").getRowData(id);
				var favo = ret.favorito;
				mId = id;
				$.post("'.site_url($this->url.'dataedit/modify').'/"+id,
				function(data){
					if (favo != "S"){  // Guardar y Cancelar
						$("#fedita").dialog({
							buttons: {
								'.$btguardar.'
								'.$btcancelar.'
							},
							'.$btcerrar.'
						});
					} else { // Solo Duplicar
						$("#fedita").dialog({
							buttons: {
								'.$btduplicar.'
								'.$btcancelar.'
							},
							'.$btcerrar.'
						});
					};
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				})
			} else {
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';

		$bodyscript .= '
		function spreshow(){
			var id     = $("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url($this->url.'dataedit/show').'/"+id, function(data){
					$("#fshow").html(data);
					$("#fshow").dialog( "open" );
				});
			} else {
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';

		$bodyscript .= '
		function spredel() {
			var id = $("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				if(confirm(" Seguro desea eliminar el registro?")){
					var ret    = $("#newapi'.$grid0.'").getRowData(id);
					mId = id;
					$.post("'.site_url($this->url.'dataedit/do_delete').'/"+id, function(data){
						try{
							var json = JSON.parse(data);
							if (json.status == "A"){
								apprise("Registro eliminado");
								$("#newapi'.$grid0.'").trigger("reloadGrid");
							}else{
								apprise("Registro no se puede eliminado");
							}
						}catch(e){
							$("#fborra").html(data);
							$("#fborra").dialog( "open" );
						}
					});
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';

		//Wraper de javascript
		$bodyscript .= '
		$(function() {
			$("#dialog:ui-dialog").dialog( "destroy" );
			var mId = 0;
			var montotal = 0;
			var ffecha = $("#ffecha");
			var grid = $("#newapi'.$grid0.'");
			var s;
			var allFields = $( [] ).add( ffecha );
			var tips = $( ".validateTips" );
			s = grid.getGridParam(\'selarrrow\');
			';

		$bodyscript .= '
		$("#boton1").click(function(){
			var id = $("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = $("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				'.$this->datasis->jwinopen(site_url('formatos/ver/PRESUP').'/\'+id+\'/id\'').';
			} else { $.prompt("<h1>Por favor Seleccione un Presupuesto</h1>");}
		});';


		$bodyscript .= '
		$("#bgrfavo").click(function(){
			$.post("'.site_url('ventas/spre/grform').'",
			function(data){
				$("#fshow").html(data);
				$("#fshow").dialog( { title:"GRUPOS", width: 370, height: 400, modal: true } );
				$("#fshow").dialog( "open" );
			});
		});';


		$bodyscript .= '
		$("#bffact").click(function(){
			var id = $("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				$.post("'.site_url('ventas/sfac/creafromspre/N').'/"+ret.numero+"/create",
				function(data){
					$("#ffact").html(data);
					$("#ffact").dialog( "open" );
				});
			} else { $.prompt("<h1>Por favor Seleccione un Presupuesto</h1>");}
		});';

		$bodyscript .= '
		$("#bsnte").click(function(){
			var id = $("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				$.post("'.site_url('ventas/snte/creafromspre/N').'/"+ret.numero+"/create",
				function(data){
					$("#ffact").html(data);
					$("#ffact").dialog( "open" );
				});
			} else { $.prompt("<h1>Por favor Seleccione un Presupuesto</h1>");}
		});';

		$bodyscript .= '
		$("#bfavori").click(function(){
			var id = $("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				$.post("'.site_url('ventas/spre/favorito').'/"+id,
				function(data){
					$.prompt(data);
				});
			} else { $.prompt("<h1>Por favor Seleccione un Presupuesto</h1>");}
		});';

		$bodyscript .= '
		$("#bcorreo").click(function(){
			var id = $("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var mgene = {
				state0: {
					html:"<h1>Enviar notificacion por email?</h1>",
					buttons: { Cancelar: false, Aceptar: true },
					focus: 1,
					submit:function(e,v,m,f){
						if(v){
							e.preventDefault();
							$.post("'.site_url('ventas/spre/notifica').'/"+id,
								function(data){
									$.prompt.goToState("state1");
							});
							return false;}}
				},
				state1: {
					html:"<h2>Envio efectuado!</h2> ",
					buttons: { Salir: 0 },
					focus: 1,
					submit:function(e,v,m,f){
						e.preventDefault();
						$.prompt.close();
						grid.trigger("reloadGrid");
					}
				}};
				$.prompt(mgene);
			} else { $.prompt("<h1>Por favor Seleccione un Presupuesto</h1>");}
		});';


		$bodyscript .= '
		$("#fedita").dialog({ autoOpen: false, height: 550, width: 800, modal: true });';

		$bodyscript .= '
		function cierra(dlg){
				if($("#scliexp").dialog( "isOpen" )===true) {
					$("#scliexp").dialog("close");
				}
				$("#"+dlg).html("");
				//allFields.val("").removeClass( "ui-state-error" );
		};';


		//Convertir Factura
		$bodyscript .= '
			$("#ffact").dialog({
				autoOpen: false, height: 550, width: 870, modal: true,
				buttons: {
					"Guardar": function() {
						var bValid = true;
						var murl = $("#df1").attr("action");
						$.ajax({
							type: "POST",
							dataType: "html",
							async: false,
							url: murl,
							data: $("#df1").serialize(),
							success: function(r,s,x){
								try{
									var json = JSON.parse(r);
									if ( json.status == "A" ) {
										if ( json.manual == "N" ) {
											$( "#ffact" ).dialog( "close" );
											$("#newapi'.$grid0.'").trigger("reloadGrid");
											window.open(\''.site_url('ventas/sfac/dataprint/modify').'/\'+json.pk.id, \'_blank\', \'width=400,height=420,scrollbars=yes,status=yes,resizable=yes\');
											return true;
										} else {
											$.post("'.site_url($this->url.'dataedit/S/create').'",
											function(data){
												$("#ffact").html(data);
											})
											window.open(\''.site_url('ventas/sfac/dataprint/modify').'/\'+json.pk.id, \'_blank\', \'width=400,height=420,scrollbars=yes,status=yes,resizable=yes\');
											return true;
										}

									} else {
										apprise(json.mensaje);
									}
								}catch(e){
									$("#ffact").html(r);
								}
							}
						})
					},
					"Cancelar": function() {
						$("#ffact").html("");
						$( this ).dialog( "close" );
						$("#newapi'.$grid0.'").trigger("reloadGrid");
					}
				},
				close: function() {
					$("#ffact").html("");
				}
			});';

		$bodyscript .= '
		$("#fshow").dialog({
			autoOpen: false, height: 500, width: 700, modal: true,
			buttons: {
				"Aceptar": function() {
					$("#fshow").html("");
					$( this ).dialog( "close" );
				},
			},
			close: function() {
				$("#fshow").html("");
			}
		});';

		$bodyscript .= '
		$("#fborra").dialog({
			autoOpen: false, height: 300, width: 400, modal: true,
			buttons: {
				"Aceptar": function() {
					$("#fborra").html("");
					$("#newapi'.$grid0.'").trigger("reloadGrid");
					$( this ).dialog( "close" );
				},
			},
			close: function() {
				$("#newapi'.$grid0.'").trigger("reloadGrid");
				$("#fborra").html("");
			}
		});';

		$bodyscript .= '
		$("#scliexp").dialog({
			autoOpen:false, modal:true, width:500, height:350,
			buttons: {
				"Guardar": function(){
					var murl = $("#sclidialog").attr("action");
					$.ajax({
						type: "POST", dataType: "json", async: false,
						url: murl,
						data: $("#sclidialog").serialize(),
						success: function(r,s,x){
							if(r.status=="B"){
								$("#sclidialog").find(".alert").html(r.mensaje);
							}else{
								$("#scliexp").dialog( "close" );
								$("#cod_cli").val(r.data.cliente);
								$("#nombre").val(r.data.nombre);
								$("#rifci").val(r.data.rifci);
								$("#sclitipo").val(r.data.tipo);
								$("#direc").val(r.data.direc);
								return true;
							}
						}
					});
				},
				"Cancelar": function(){
					$("#scliexp").html("");
					$(this).dialog("close");
				}
			},
			close: function(){
				$("#scliexp").html("");
			}
		});';

		$bodyscript .= '});';
		$bodyscript .= '</script>';
		return $bodyscript;
	}


	//******************************************************************
	// Grupos de favorito
	//
	function grform(){
		$grid  = new $this->jqdatagrid;

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false',
			'hidden'        => 'true'
		));

		$grid->addField('grupo');
		$grid->label('Grupo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'align'         => "'left'",
			'edittype'      => "'text'",
			'width'         => 80,
		));

		$grid->showpager(true);
		$grid->setViewRecords(false);
		$grid->setWidth('350');
		$grid->setHeight('220');

		$grid->setUrlget(site_url('ventas/spre/grgd/'));
		$grid->setUrlput(site_url('ventas/spre/grsd/'));

		$mgrid = $grid->deploy();

		$msalida  = '<script type="text/javascript">'."\n";
		$msalida .= '
		$("#newapi'.$mgrid['gridname'].'").jqGrid({
			ajaxGridOptions : {type:"POST"}
			,jsonReader : { root:"data", repeatitems: false }
			'.$mgrid['table'].'
			,scroll: true
			,pgtext: null, pgbuttons: false, rowList:[]
		})
		$("#newapi'.$mgrid['gridname'].'").jqGrid(\'navGrid\',  "#pnewapi'.$mgrid['gridname'].'",{edit:false, add:false, del:true, search: false});
		$("#newapi'.$mgrid['gridname'].'").jqGrid(\'inlineNav\',"#pnewapi'.$mgrid['gridname'].'");
		$("#newapi'.$mgrid['gridname'].'").jqGrid(\'filterToolbar\');
		';

		$msalida .= "\n</script>\n";
		$msalida .= '<id class="anexos"><table id="newapi'.$mgrid['gridname'].'"></table>';
		$msalida .= '<div   id="pnewapi'.$mgrid['gridname'].'"></div></div>';

		echo $msalida;

	}

	//******************************************************************
	// Busca la data en el Servidor por json
	//
	function grgd(){
		$grid       = $this->jqdatagrid;
		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('spregr');
		$response   = $grid->getData('spregr', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	//******************************************************************
	// Guarda la Informacion del Grid o Tabla
	//
	function grsd(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = 0; //$this->datasis->dameval("SELECT count(*) FROM medesp WHERE especialidad=".$this->db->escape($data['especialidad']));
				if ( $check == 0 ){
					$this->db->insert('spregr', $data);
					echo "Registro Agregado";
					logusu('SPREGR',"Registro  INCLUIDO");
				} else
					echo "Ya existe un registro con ese nombre";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$this->db->where("id", $id);
			$this->db->update('spregr', $data);
			logusu('SPREGR',"Grupos de presupuesto  ".$data['grupo']." MODIFICADO");
			echo "$mcodp Modificado";

		} elseif($oper == 'del') {
			$check = $this->datasis->dameval("SELECT count(*) FROM spre WHERE grupo=$id");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene presupuestos asignados ";
			} else {
				$this->db->query("DELETE FROM spregr WHERE id=$id ");
				logusu('SPREGR',"Registro $id ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}


	//******************************************************************
	// Notificar por Correo
	function favorito( $id = 0 ){
		if ( $id == 0 )
			$id = $this->uri->segment($this->uri->total_segments());
		$id = intval($id);
		$mSQL = "UPDATE spre SET favorito=IF(favorito='S','N','S') WHERE id=${id}";
		$this->db->query($mSQL);
		echo '<h1>Favorito cambiado</h1>';
	}



	//******************************************************************
	// Notificar por Correo
	function notifica( $id = 0 ){
		if ( $id == 0 )
			$id = $this->uri->segment($this->uri->total_segments());
		$id = intval($id);
		$query = $this->db->query("SELECT * FROM spre WHERE id=".$id);
		$msj = 'No hay correo para enviar';
		if ( $query->num_rows() > 0 ){
			$msj = 'Correo enviado';
			$row = $query->row();
			$notifica = "
Muchas gracias por su compra. Su número de orden es: ".$row->numero."

Estos son los pasos para concretar su compra.
1) Puede depositar o transferir a las siguientes cuentas:

COEX TRADE C.A. J-40386086-6
BICENTENARIO Cta. 0175-0011-2300-7305-1179
VENEZUELA    Cta. 0102-0441-1000-0023-3563
BNC          Cta. 0191-0093-6721-9303-0443
MERCANTIL    Cta. 0105-0065-6410-6538-5552
PROVINCIAL   Cta. 0108-0067-6401-0028-3544

FACUNDO CALELLO E-84.571.125
BANESCO Cta. 0134-0030-0103-0102-9938

El monto a depositar es de Bs: ".$row->totalg."

2) Ingresar a la página: www.tecbloom.com y registre todos los datos
solicitados, su número de orden está al comienzo de este correo. Los datos
ingresados son los mismos que se procesarán, por favor llenar los campos
indicados cuidadosamente. Si se encuentra en la ciudad de Mérida o desea retirar
personalmente en nuestra tienda, la dirección es la siguiente: Av. 2 Lora, esquina
calle 18, C.C. Las Pirámides, planta baja, local 14, pasos arriba de Corredor
Hermanos. Mérida, estado Mérida, de Lunes a Sábado en horario comprendido
de 9:00 am a 1:00 pm y de 2:00 pm a 6:00 pm.

No responder a este correo, debe obligatoriamente llenar sus datos en la página
web indicada en el paso 2 con el número de orden correspondiente. No se
procesará ninguna información que sea enviada a este correo, ni se tomarán
datos vía telefónica.";

		$this->datasis->correo( $row->email, 'Instrucciones: Orden No. '.$row->numero, utf8_decode($notifica) );

		$this->db->where('id',$id);
		$this->db->update('spre',array('notifica'=>'S'));
		}
		echo $msj;
	}


	//******************************************************************
	// Definicion del Grid y la Forma
	//
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = 'false';

		$grid  = new $this->jqdatagrid;

		$grid->addField('favorito');
		$grid->label('*');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));

		$grid->addField('numero');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));

		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('notifica');
		$grid->label('Notif.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));

		$grid->addField('cod_cli');
		$grid->label('Cliente');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:40, maxlength: 40 }',
		));

		$grid->addField('totalg');
		$grid->label('Total');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('totals');
		$grid->label('Base');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('iva');
		$grid->label('IVA');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$hay = $this->datasis->dameval( "SELECT count(*) FROM spregr ORDER BY grupo ");

		if ( $hay > 0 ){
			$mSQL = "SELECT id, grupo FROM spregr ORDER BY grupo ";
			$agrupo  = $this->datasis->llenajqselect($mSQL, false );

			$grid->addField('grupo');
			$grid->label('Grupo');
			$grid->params(array(
				'search'        => 'true',
				'editable'      => 'true',
				'align'         => "'center'",
				'edittype'      => "'text'",
				'width'         => 50,
				'edittype'      => "'select'",
				'editrules'     => '{ required:false}',
				'editoptions'   => '{value: '.$agrupo.', style:"width:150px" }',
			));
		}

		$grid->addField('vd');
		$grid->label('Vendedor');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));

		$grid->addField('rifci');
		$grid->label('RIF/CI');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 90,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:13, maxlength: 13 }',
		));


		$grid->addField('inicial');
		$grid->label('Inicial');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('condi1');
		$grid->label('Condiciones 1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:25, maxlength: 25 }',
		));


		$grid->addField('condi2');
		$grid->label('Condiciones 2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:25, maxlength: 25 }',
		));


		$grid->addField('peso');
		$grid->label('Peso');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));


		$grid->addField('modificado');
		$grid->label('Modificado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('id');
		$grid->label('ID');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('200');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){
				if (id){
					$(gridId2).jqGrid(\'setGridParam\',{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
					$(gridId2).trigger("reloadGrid");
					$.ajax({
						url: "'.site_url('ventas/scli/respres/').'/"+id,
						success: function(msg){
							$("#ladicional").html(msg);
						}
					});
				}
			}'
		);


		$iruta = site_url('images/star.png');
		$grid->setAfterInsertRow('
			function( rid, aData, rowe){
				if(aData.favorito !== undefined){
					if(aData.favorito == "S"){
						$(this).jqGrid( "setCell", rid, "favorito","", {"color":"#000CFF", "background-image":"url(\''.$iruta.'\')" });

					}else {
						$(this).jqGrid( "setCell", rid, "favorito","", {color:"#FFFFFF", background:"none" });
					}
				}
			}
		');

//						$(this).jqGrid( "setCell", rid, "favorito","", {color:"#000CFF", background-image:"url(\''.$iruta.'\')" });
//						$("#"+rid,this).css({ "background-image":"url(\''.$iruta.'\')" });
//						$(this).jqGrid( "setCell", rid, "favorito","", {color:"#000CFF", background:"YELLOW" });


		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('SPRE','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('SPRE','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('SPRE','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('SPRE','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions('addfunc: spreadd, editfunc: spreedit, delfunc: spredel, viewfunc: spreshow');

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/*******************************************************************
	* Busca la data en el Servidor por json
	*/
	function getdata(){
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('spre');

		$response   = $grid->getData('spre', array(array()), array(), false, $mWHERE, 'id', 'desc' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/*******************************************************************
	* Guarda la Informacion
	*/
	function setData(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$mcodp  = 'numero';
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
/*
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM spre WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('spre', $data);
					echo "Registro Agregado";

					logusu('SPRE',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";
*/
		} elseif($oper == 'edit') {
			//$nuevo  = $data[$mcodp];
			//$anterior = $this->datasis->dameval("SELECT $mcodp FROM spre WHERE id=$id");

			//if ( $nuevo <> $anterior ){
			//	//si no son iguales borra el que existe y cambia
			//	$this->db->query("DELETE FROM spre WHERE $mcodp=?", array($mcodp));
			//	$this->db->query("UPDATE spre SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
			//	$this->db->where("id", $id);
			//	$this->db->update("spre", $data);
			//	logusu('SPRE',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
			//	echo "Grupo Cambiado/Fusionado en clientes";
			//} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('spre', $data);
				logusu('SPRE',"Grupo de Cliente  ".$id." MODIFICADO");
				echo "Presupuesto Modificado";
			//}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM spre WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM spre WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM spre WHERE id=$id ");
				logusu('SPRE',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};

	}

	//******************************************************************
	// Definicion del Grid y la Forma
	//
	function defgridit( $deployed = false ){
		$i      = 1;
		$editar = 'false';

		$grid  = new $this->jqdatagrid;

		$grid->addField('codigo');
		$grid->label('C&oacute;digo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));

		$grid->addField('desca');
		$grid->label('Descripci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 180,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:40, maxlength: 40 }',
		));

		$grid->addField('activo');
		$grid->label('Actv');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 30,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('cana');
		$grid->label('Cant.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 50,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('saldo');
		$grid->label('Saldo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('preca');
		$grid->label('Precio');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('importe');
		$grid->label('Importe');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('iva');
		$grid->label('IVA');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 50,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$mSQL = "SELECT codigo, CONCAT(codigo, ' ', nombre) nombre FROM medrec ORDER BY codigo";
		$recu = $this->datasis->llenajqselect($mSQL, true );

		$grid->addField('recurso');
		$grid->label('Recurso');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'select'",
			'editoptions'   => '{ value: '.$recu.',  style:"width:210px"}',
			'stype'         => "'text'",
		));

		$grid->addField('combo');
		$grid->label('Combo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('descuento');
		$grid->label('Descuento');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'hidden'        => 'true',
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->setAfterInsertRow('
			function( rid, aData, rowe){
				if(aData.activo !== undefined){
					if(aData.activo == "N"){
						$(this).jqGrid( "setCell", rid, "activo","", {color:"#FFFFFF", background:"#C90623" });
					}else {
						$(this).jqGrid( "setCell", rid, "activo","", {color:"#FFFFFF", background:"#0A9810" });
					}

					if(aData.saldo < 0 ){
						$(this).jqGrid( "setCell", rid, "saldo","", {color:"#FFFFFF", background:"#C90623" });
					}else if(aData.saldo == 0 ) {
						$(this).jqGrid( "setCell", rid, "saldo","", {color:"#000000", background:"#FFA500" });
					}else {
						$(this).jqGrid( "setCell", rid, "saldo","", {color:"#FFFFFF", background:"#0A9810" });
					}
				}
			}
		');

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('190');
		$grid->setfilterToolbar(false);
		$grid->setToolbar('false', '"top"');

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('SPRE','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('SPRE','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('SPRE','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('SPRE','BUSQUEDA%'));
		$grid->setRowNum(180);
		$grid->setShrinkToFit('false');


		#Set url
		$grid->setUrlput(site_url($this->url.'setdatait/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdatait/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/*******************************************************************
	* Busca la data en el Servidor por json
	*/
	function getdatait( $id = 0 ){
		if($id == 0){
			$id = $this->datasis->dameval('SELECT MAX(id) AS id FROM spre');
		}
		$dbid = intval($id);
		if(empty($id)) return '';
		$numero   = $this->datasis->dameval("SELECT numero FROM spre WHERE id=${dbid}");
		$dbnumero = $this->db->escape($numero);

		$orderby= '';
		$sidx=$this->input->post('sidx');
		if($sidx){
			$campos = $this->db->list_fields('itspre');
			if(in_array($sidx,$campos)){
				$sidx = trim($sidx);
				$sord   = $this->input->post('sord');
				$orderby="ORDER BY `${sidx}` ".(($sord=='asc')? 'ASC':'DESC');
			}
		}

		$grid    = $this->jqdatagrid;
		$mSQL    = "SELECT a.*, b.existen, b.activo, if(MID(b.tipo,1,1)='S',1,b.existen-a.cana) saldo ";
		$mSQL   .= "FROM itspre a JOIN sinv b ON a.codigo=b.codigo WHERE numero=${dbnumero} ${orderby}";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/*******************************************************************
	* Guarda la Informacion
	*/
	function setdatait(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;

		if(empty($id)) return false;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			echo 'Deshabilitado';
		}elseif($oper == 'edit'){
			$posibles=array('recurso');
			foreach($data as $ind=>$val){
				if(!in_array($ind,$posibles)){
					echo 'Campo no permitido ('.$ind.')';
					return false;
				}
			}
			$tipo = $this->datasis->dameval("SELECT MID(b.tipo,1,1) tipo FROM itspre a JOIN sinv b ON a.codigoa=b.codigo WHERE a.id=${id} ");
			if ( $tipo != 'S' )
				$this->db->where('id',      $id);
				$this->db->update('itspre', $data);
				echo 'Guardado ';
				return true;
			} else {
				echo 'Item no es servicio ';
				return true;
			}
		} elseif($oper == 'del') {
			echo 'Deshabilitado';
		};
	}


	//******************************************************************
	// Dataedit
	//
	function dataedit( $status='',$id='' ){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigo_<#i#>',
				'descrip'=>'desca_<#i#>',
				'base1'  =>'precio1_<#i#>',
				'base2'  =>'precio2_<#i#>',
				'base3'  =>'precio3_<#i#>',
				'base4'  =>'precio4_<#i#>',
				'iva'    =>'itiva_<#i#>',
				'tipo'   =>'sinvtipo_<#i#>',
				'peso'   =>'sinvpeso_<#i#>',
				'pond'   =>'pond_<#i#>',
				'ultimo' =>'ultimo_<#i#>'
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
			'script'  => array('post_modbus_sinv(<#i#>)')
		);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre',
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n',
			'tipo'=>'Tipo'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','rifci'=>'rifci',
						  'dire11'=>'direc','tipo'=>'sclitipo'),
		'titulo'  =>'Buscar Cliente',
		'script'  => array('post_modbus_scli()'));
		$boton =$this->datasis->modbus($mSCLId);

		$do = new DataObject('spre');
		$do->rel_one_to_many('itspre', 'itspre', 'numero');
		$do->pointer('scli' ,'scli.cliente=spre.cod_cli','scli.tipo AS sclitipo','left');
		$do->rel_pointer('itspre','sinv','itspre.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		if($status=='create' && !empty($id)){
			$do->load($id);
		}

		$edit = new DataDetails('Presupuestos', $do);
		$edit->back_url = site_url('ventas/spre/filteredgrid');
		$edit->set_rel_title('itspre','Producto <#o#>');
		$edit->on_save_redirect=false;

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->size = 10;
		$edit->fecha->calendar=false;

		$vend=$this->secu->getvendedor();
		$edit->vd = new  dropdownField ('Vendedor', 'vd');
		$edit->vd->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vd->style='width:170px;';
		$edit->vd->insertValue=$vend;
		$edit->vd->size = 5;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;
		$edit->peso->type      = 'inputhidden';

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		//$edit->cliente->maxlength=5;
		//$edit->cliente->append($boton);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 45;
		$edit->nombre->maxlength=45;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';

		$edit->rifci   = new inputField('RIF/CI','rifci');
		$edit->rifci->autocomplete=false;
		$edit->rifci->size = 12;

		$edit->direc = new inputField('Direcci&oacute;n','direc');
		$edit->direc->size = 40;

		$edit->dire1 = new inputField('','dire1');
		$edit->dire1->size = 40;

		//Para saber que precio se le va a dar al cliente
		$edit->sclitipo = new hiddenField('', 'sclitipo');
		$edit->sclitipo->db_name     = 'sclitipo';
		$edit->sclitipo->pointer     = true;
		$edit->sclitipo->insertValue = 1;


		$edit->email = new inputField('Email','email');
		$edit->email->rule='';
		$edit->email->size =25;
		$edit->email->maxlength =100;

		$edit->telefono = new inputField('Telefono','telefono');
		$edit->telefono->rule='';
		$edit->telefono->size =25;
		$edit->telefono->maxlength =30;

		$edit->ciudad = new inputField('Ciudad','ciudad');
		$edit->ciudad->rule='';
		$edit->ciudad->size =42;
		$edit->ciudad->maxlength =40;

		$edit->estado = new inputField('Estado','estado');
		$edit->estado->rule='integer';
		$edit->estado->css_class='inputonlynum';
		$edit->estado->size =13;
		$edit->estado->maxlength =11;

		$edit->mercalib = new inputField('Merc.Libre','mercalib');
		$edit->mercalib->rule='';
		$edit->mercalib->size =25;
		$edit->mercalib->maxlength =50;

		$edit->codbanc = new inputField('Codbanc','codbanc');
		$edit->codbanc->rule='';
		$edit->codbanc->size =4;
		$edit->codbanc->maxlength =2;

		$edit->tipo_op = new inputField('Tipo_op','tipo_op');
		$edit->tipo_op->rule='';
		$edit->tipo_op->size =4;
		$edit->tipo_op->maxlength =2;

		$edit->num_ref = new inputField('Num_ref','num_ref');
		$edit->num_ref->rule='';
		$edit->num_ref->size =22;
		$edit->num_ref->maxlength =20;


		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->rel_id   = 'itspre';
		$edit->codigo->rule     = 'required';
		$edit->codigo->style    = 'width:80%;';
		$edit->codigo->autocomplete=false;
		//$edit->codigo->append($btn);

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size=40;
		$edit->desca->db_name='desca';
		$edit->desca->maxlength=40;
		$edit->desca->readonly  = true;
		$edit->desca->rel_id='itspre';
		$edit->desca->style    = 'width:99%';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name  = 'cana';
		$edit->cana->css_class= 'inputnum';
		$edit->cana->rel_id   = 'itspre';
		$edit->cana->maxlength= 10;
		$edit->cana->size     = 6;
		$edit->cana->rule     = 'required|positive';
		$edit->cana->autocomplete=false;
		$edit->cana->onkeyup  ='importe(<#i#>)';
		$edit->cana->style    = 'width:98%';

		$edit->preca = new inputField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name   = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id    = 'itspre';
		$edit->preca->size      = 10;
		$edit->preca->rule      = 'required|positive|callback_chpreca[<#i#>]';
		$edit->preca->readonly  = true;
		//$edit->preca->style    = 'width:98%';

		$edit->importe = new inputField('Importe <#o#>', 'importe_<#i#>');
		$edit->importe->db_name  = 'importe';
		$edit->importe->size     = 10;
		$edit->importe->css_class= 'inputnum';
		$edit->importe->rel_id   = 'itspre';
		$edit->importe->style    = 'width:98%';
		$edit->importe->type     = 'inputhidden';

		for($i=1;$i<4;$i++){
			$obj='precio'.$i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj.'_<#i#>');
			$edit->$obj->db_name   = 'sinv'.$obj;
			$edit->$obj->rel_id    = 'itspre';
			$edit->$obj->pointer   = true;
		}

		$edit->precio4 = new hiddenField('', 'precio4_<#i#>');
		$edit->precio4->db_name   = 'precio4';
		$edit->precio4->rel_id    = 'itspre';

		$edit->detalle = new hiddenField('', 'detalle_<#i#>');
		$edit->detalle->db_name  = 'detalle';
		$edit->detalle->rel_id   = 'itspre';

		$edit->itiva = new hiddenField('', 'itiva_<#i#>');
		$edit->itiva->db_name  = 'iva';
		$edit->itiva->rel_id   = 'itspre';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name   = 'sinvpeso';
		$edit->sinvpeso->rel_id    = 'itspre';
		$edit->sinvpeso->pointer   = true;

		$edit->sinvtipo = new hiddenField('', 'sinvtipo_<#i#>');
		$edit->sinvtipo->db_name   = 'sinvtipo';
		$edit->sinvtipo->rel_id    = 'itspre';
		$edit->sinvtipo->pointer   = true;

		$edit->ultimo = new hiddenField('', 'ultimo_<#i#>');
		$edit->ultimo->db_name   = 'ultimo';
		$edit->ultimo->rel_id    = 'itspre';

		$edit->pond = new hiddenField('', "pond_<#i#>");
		$edit->pond->db_name='pond';
		$edit->pond->rel_id ='itspre';
		//**************************
		//fin de campos para detalle
		//**************************

		$edit->ivat = new hiddenField('Impuesto', 'iva');
		$edit->ivat->css_class ='inputnum';
		$edit->ivat->readonly  =true;
		$edit->ivat->size      = 10;

		$edit->totals = new hiddenField('Sub-Total', 'totals');
		$edit->totals->css_class ='inputnum';
		$edit->totals->readonly  =true;
		$edit->totals->size      = 10;

		$edit->totalg = new hiddenField('Total', 'totalg');
		$edit->totalg->css_class ='inputnum';
		$edit->totalg->readonly  =true;
		$edit->totalg->size      = 10;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->condi1 = new inputField('Condiciones', 'condi1');
		$edit->condi1->size = 40;
		$edit->condi1->maxlength=25;
		$edit->condi1->autocomplete=false;

		$edit->condi2 = new inputField('Condiciones', 'condi2');
		$edit->condi2->size = 40;
		$edit->condi2->maxlength=25;
		$edit->condi2->autocomplete=false;

		$edit->observa = new textareaField('Observacion', 'observa');
		$edit->observa->rule = 'trim';
		$edit->observa->cols = 40;
		$edit->observa->rows =  2;
		$edit->observa->maxlength =200;

		$edit->codbanc = new dropdownField('Banco','codbanc');
		$edit->codbanc->option('','Seleccionar');
		$edit->codbanc->options('SELECT codbanc, CONCAT(banco,\' \',numcuent) banco FROM banc WHERE activo="S" AND tipocta="C" ORDER BY banco');
		$edit->codbanc->style='width:140px;';
		$edit->codbanc->size = 2;

		$edit->tipo_op = new dropdownField('Tipo','tipo_op');
		$edit->tipo_op->option('','Seleccionar');
		$edit->tipo_op->options(array('NC'=> 'Transferencia','DE'=>'Deposito'));
		$edit->tipo_op->style='width:90px';

		$edit->fechadep = new DateonlyField('Fecha', 'fechadep','d/m/Y');
		$edit->fechadep->insertValue = date('Y-m-d');
		$edit->fechadep->updateValue = date('Y-m-d');
		//$edit->fechadep->rule = 'required';
		$edit->fechadep->size = 10;
		$edit->fechadep->calendar=false;

		$edit->num_ref = new inputField('Numero','num_ref');
		$edit->num_ref->rule='';
		$edit->num_ref->size =20;
		$edit->num_ref->maxlength =20;

		//$edit->_details_fields();
		//sniff and build fields
		//$edit->_sniff_fields();



		$edit->buttons('add_rel');
		$edit->build();


		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			$conten['form']  =&  $edit;
			$data['content'] = $this->load->view('view_spre', $conten);
		}
	}

	function _pre_insert($do){
		$numero=$this->datasis->fprox_numero('nspre');
		$do->set('numero',$numero);
		$fecha =$do->get('fecha');
		$vd    =$do->get('vd');

		$iva=$totals=0;
		$cana=$do->count_rel('itspre');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itspre','cana',$i);
			$itpreca   = $do->get_rel('itspre','preca',$i);
			$itiva     = $do->get_rel('itspre','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('itspre','importe' ,$itimporte,$i);
			$do->set_rel('itspre','totaorg' ,$itimporte*(1+($itiva/100)),$i);
			$do->set_rel('itspre','fecha'   ,$fecha  ,$i);
			$do->set_rel('itspre','vendedor',$vd     ,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;
			//$do->set_rel('itspre','mostrado',$iva+$totals,$i);
		}
		$totalg = $totals+$iva;

		$do->set('inicial',0 );
		$do->set('totals' ,round($totals ,2));
		$do->set('totalg' ,round($totalg ,2));
		$do->set('iva'    ,round($iva    ,2));
		//print_r($do->get_all()); return false;

		return true;
	}

	function _pre_update($do){
		$fecha =$do->get('fecha');
		$vd    =$do->get('vd');

		$iva=$totals=0;
		$cana=$do->count_rel('itspre');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itspre','cana',$i);
			$itpreca   = $do->get_rel('itspre','preca',$i);
			$itiva     = $do->get_rel('itspre','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('itspre','importe' ,$itimporte,$i);
			$do->set_rel('itspre','totaorg' ,$itimporte,$i);
			$do->set_rel('itspre','fecha'   ,$fecha  ,$i);
			$do->set_rel('itspre','vendedor',$vd     ,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;
			//$do->set_rel('itspre','mostrado',$iva+$totals,$i);
		}
		$totalg = $totals+$iva;

		$do->set('inicial',0 );
		$do->set('totals' ,round($totals ,2));
		$do->set('totalg' ,round($totalg ,2));
		$do->set('iva'    ,round($iva    ,2));

		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('spre',"PRESUPUESTO ${codigo} CREADO");
	}

	function chpreca($preca,$ind){
		$codigo  = $this->input->post('codigo_'.$ind);
		$precio4 = $this->datasis->dameval('SELECT base4 FROM sinv WHERE codigo='.$this->db->escape($codigo));
		$preca   = round($preca,2);
		if($precio4<0) $precio4=0;

		if($preca<$precio4){
			$this->validation->set_message('chpreca', 'El art&iacute;culo '.$codigo.' debe contener un precio de al menos '.nformat($precio4));
			return false;
		}else{
			return true;
		}
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('spre',"PRESUPUESTO ${codigo} MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('spre',"PRESUPUESTO ${codigo} ELIMINADO");
	}

	function tabla() {
		$id       = isset($_REQUEST['id'])  ? $_REQUEST['id']   :  0;
		$dbid     = $this->db->escape($id);
		$cliente  = $this->datasis->dameval("SELECT cod_cli FROM spre WHERE id=${dbid}");
		$dbcliente= $this->db->escape($cliente);
		$mSQL     = "SELECT cod_cli, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM smov WHERE cod_cli=${dbcliente} AND abonos<>monto AND tipo_doc<>'AB' ORDER BY fecha";
		$query    = $this->db->query($mSQL);
		$salida   = '';
		$saldo    = 0;
		if($query->num_rows() > 0){
			$salida  = '<br><table width=\'100%\' border=\'1\'>';
			$salida .= '<tr bgcolor=\'#e7e3e7\'><td colspan=\'3\'>Movimiento en Cuentas X Cobrar</td></tr>';
			$salida .= '<tr bgcolor=\'#e7e3e7\'><td>Tp</td><td align=\'center\'>N&uacute;mero</td><td align=\'center\'>Monto</td></tr>';

			foreach ($query->result_array() as $row){
				$salida .= '<tr>';
				$salida .= '<td>'.$row['tipo_doc'].'</td>';
				$salida .= '<td>'.$row['numero'].'</td>';
				$salida .= '<td align=\'right\'>'.nformat($row['monto']-$row['abonos']).'</td>';
				$salida .= '</tr>';
				if ( $row['tipo_doc'] == 'FC' or $row['tipo_doc'] == 'ND' or $row['tipo_doc'] == 'GI' )
					$saldo += $row['monto']-$row['abonos'];
				else
					$saldo -= $row['monto']-$row['abonos'];
			}
			$salida .= '<tr bgcolor=\'#d7c3c7\'><td colspan=\'4\' align=\'center\'>Saldo : '.nformat($saldo).'</td></tr>';
			$salida .= '</table>';
		}
		$query->free_result();
		echo $salida;
	}


	//******************************************************************
	// Edicion

	function dataeditc(){
		$this->rapyd->load('dataedit');
		$script= '
		$(function() {
			$("#fecha").datepicker({dateFormat:"dd/mm/yy"});
			$("#fechadep").datepicker({dateFormat:"dd/mm/yy"});
			$(".inputnum").numeric(".");
		});
		';

		$edit = new DataEdit('', 'spre');

		$edit->script($script,'modify');
		$edit->script($script,'create');
		$edit->on_save_redirect=false;

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insertc');
		$edit->post_process('update','_post_updatec');
		$edit->post_process('delete','_post_deletec');
		$edit->pre_process('insert', '_pre_insertc' );
		$edit->pre_process('update', '_pre_updatec' );
		$edit->pre_process('delete', '_pre_deletec' );

		$edit->numero = new inputField('Numero','numero');
		$edit->numero->rule='';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;

		$edit->fecha = new dateonlyField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->calendar=false;
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->rifci = new inputField('Cedula','rifci');
		$edit->rifci->rule='';
		$edit->rifci->size =15;
		$edit->rifci->maxlength =13;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =40;

		$edit->direc = new inputField('Direccion','direc');
		$edit->direc->rule='';
		$edit->direc->size =42;
		$edit->direc->maxlength =40;

		$edit->dire1 = new inputField('Dire1','dire1');
		$edit->dire1->rule='';
		$edit->dire1->size =42;
		$edit->dire1->maxlength =40;

		$edit->email = new inputField('Email','email');
		$edit->email->rule='';
		$edit->email->size =102;
		$edit->email->maxlength =100;

		$edit->telefono = new inputField('Telefono','telefono');
		$edit->telefono->rule='';
		$edit->telefono->size =32;
		$edit->telefono->maxlength =30;

		$edit->ciudad = new inputField('Ciudad','ciudad');
		$edit->ciudad->rule='';
		$edit->ciudad->size =42;
		$edit->ciudad->maxlength =40;

		$edit->estado = new inputField('Estado','estado');
		$edit->estado->rule='integer';
		$edit->estado->css_class='inputonlynum';
		$edit->estado->size =13;
		$edit->estado->maxlength =11;

		$edit->mercalib = new inputField('Mercalib','mercalib');
		$edit->mercalib->rule='';
		$edit->mercalib->size =52;
		$edit->mercalib->maxlength =50;

		$edit->codbanc = new inputField('Codbanc','codbanc');
		$edit->codbanc->rule='';
		$edit->codbanc->size =4;
		$edit->codbanc->maxlength =2;

		$edit->tipo_op = new inputField('Tipo','tipo_op');
		$edit->tipo_op->rule='';
		$edit->tipo_op->size =4;
		$edit->tipo_op->maxlength =2;

		$edit->num_ref = new inputField('Num_ref','num_ref');
		$edit->num_ref->rule='';
		$edit->num_ref->size =22;
		$edit->num_ref->maxlength =20;

		$edit->iva = new inputField('Iva','iva');
		$edit->iva->rule='numeric';
		$edit->iva->css_class='inputnum';
		$edit->iva->size =14;
		$edit->iva->maxlength =12;

		$edit->totals = new inputField('Totals','totals');
		$edit->totals->rule='numeric';
		$edit->totals->css_class='inputnum';
		$edit->totals->size =14;
		$edit->totals->maxlength =12;

		$edit->totalg = new inputField('Totalg','totalg');
		$edit->totalg->rule='numeric';
		$edit->totalg->css_class='inputnum';
		$edit->totalg->size =14;
		$edit->totalg->maxlength =12;

		$edit->observa = new textareaField('Observa','observa');
		$edit->observa->rule='';
		$edit->observa->cols = 70;
		$edit->observa->rows = 4;

		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			echo $edit->output;
		}
	}

	//******************************************************************
	// Revisa si se puede facturar
	//
	function puedefac($manual,$numero,$status=null){

		$sel=array('a.cod_cli','b.nombre','b.tipo','b.rifci','b.dire11 AS direc'
		,'a.totals','a.iva','a.totalg');
		$this->db->select($sel);
		$this->db->from('spre AS a');
		$this->db->join('scli AS b','a.cod_cli=b.cliente','left');
		$this->db->where('a.numero',$numero);
		$query = $this->db->get();

		if ($query->num_rows() > 0 && $status=='create'){
			$row = $query->row();

			$_POST=array(
				'btn_submit' => 'Guardar',
				'fecha'      => inputDateFromTimestamp(mktime(0,0,0)),
				'cajero'     => $this->secu->getcajero(),
				'vd'         => $this->secu->getvendedor(),
				'almacen'    => $this->secu->getalmacen(),
				'tipo_doc'   => 'F',
				'factura'    => '',
				'cod_cli'    => $row->cod_cli,
				'sclitipo'   => $row->tipo,
				'nombre'     => rtrim($row->nombre),
				'rifci'      => $row->rifci,
				'direc'      => rtrim($row->direc),
				'totals'     => $row->totals,
				'iva'        => $row->iva,
				'totalg'     => $row->totalg,
				'pfac'       => $numero,
				'bultos'     => 0,
			);

			$itsel=array('a.codigo', 'b.descrip desca', 'a.cana', 'b.existen', 'b.existen-a.cana saldo', 'b.activo');
			$this->db->select($itsel);
			$this->db->from('itspre AS a');
			$this->db->join('sinv AS b','b.codigo=a.codigo');
			$this->db->where('a.numero',$numero);
			//$this->db->where('b.activo','S');
			//$this->db->where('a.cana >','0');
			$qquery = $this->db->get();
			$i=0;
			$tabla1 = '<table>';
			$tabla1 .= '<tr>';
			$tabla  .= '<td>Activo</td>';
			$tabla  .= '<td>Codigo</td>';
			$tabla  .= '<td>Descripcion</td>';
			$tabla  .= '<td>Presupuestado</td>';
			$tabla  .= '<td>Existencia</td>';
			$tabla  .= '<td>Saldo</td>';
			$tabla1 .= '</tr>';

			foreach ($qquery->result() as $itrow){
				$tabla1 .= '<tr>';
				$tabla  .= '<td>'.$itrow->activo.'</td>';
				$tabla  .= '<td>'.rtrim($itrow->codigo).'</td>';
				$tabla  .= '<td>'.rtrim($itrow->desca).'</td>';
				$tabla  .= '<td>'.$itrow->cana.'</td>';
				$tabla  .= '<td>'.$itrow->existen.'</td>';
				$tabla  .= '<td>'.$itrow->saldo.'</td>';
				$tabla1 .= '</tr>';
				$i++;
			}
			$tabla1 = '</table>';

		}else{
			echo 'Presupuesto no existe';
		}
	}

	function _pre_insertc($do){
		$do->error_message_ar['pre_ins']='';
		return false;
	}

	function _pre_updatec($do){
		$do->error_message_ar['pre_upd']='';
		return true;
	}

	function _pre_deletec($do){
		$do->error_message_ar['pre_del']='';
		return false;
	}

	function _post_insertc($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_updatec($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_deletec($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}

	//******************************************************************
	// Duplica un Presupuesto
	//
	function duplica( $id = 0 ){
		$data = '[ ]';
		if($id !== false){
			$dbid = $this->db->escape($id);
			$retArray = $retorno = array();

			$mSQL="SELECT b.codigo,b.descrip,b.tipo,b.ultimo,b.pond,
			a.precio, b.peso, b.precio1, b.iva, a.cantidad
			FROM itspre AS a
			JOIN spre   AS b ON a.numero = b.numero
			JOIN sinv   AS c ON a.codigo = c.codigo
			WHERE a.combo=";


			$mSQL="
			SELECT TRIM(a.descrip) AS descrip, TRIM(a.codigo) AS codigo, a.unidad,
			a.precio1,a.precio2,a.precio3,a.precio4, a.iva, a.existen, a.tipo, a.peso, a.ultimo,
			a.pond, a.barras, 0 AS descufijo, 0 AS dgrupo,0 AS promo, a.existen,
			a.marca, a.ubica,a.id, c.cana
			FROM itspre AS c
			JOIN spre   AS b ON c.numero = b.numero
			JOIN sinv   AS a ON a.codigo=c.codigo
			WHERE b.id = ${dbid}
			ORDER BY a.descrip ";

			$query = $this->db->query($mSQL);
			if($query->num_rows() > 0){
				foreach( $query->result_array() as $id=>$row ){
					$retArray['codigo']  = $this->en_utf8($row['codigo']);
					$retArray['cana']    = $row['cana'];
					$retArray['tipo']    = $row['tipo'];
					$retArray['peso']    = $row['peso'];
					$retArray['ultimo']  = $row['ultimo'];
					$retArray['pond']    = $row['pond'];
					$retArray['base1']   = round($row['precio1']*100/(100+$row['iva']),2);
					$retArray['base2']   = round($row['precio2']*100/(100+$row['iva']),2);
					$retArray['base3']   = round($row['precio3']*100/(100+$row['iva']),2);
					$retArray['base4']   = round($row['precio4']*100/(100+$row['iva']),2);
					$retArray['descrip'] = $this->en_utf8($row['descrip']);
					$retArray['barras']  = $row['barras'];
					$retArray['iva']     = $row['iva'];
					$retArray['existen'] = (empty($row['existen']))? 0 : round($row['existen'],2);
					$retArray['marca']   = $row['marca'];
					$retArray['ubica']   = $row['ubica'];
					$retArray['unidad']  = $row['unidad'];
					$retArray['id']      = intval($row['id']);
					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
	        }
		}
		echo $data;
	}


	//******************************************************************
	// Instalar
	//
	function instalar(){
		$campos=$this->db->list_fields('spre');
		if(!in_array('id',$campos)){
			$this->db->simple_query('ALTER TABLE spre DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE spre ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE spre ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		}
		if(!in_array('email',    $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN email    VARCHAR(100) NULL DEFAULT NULL AFTER dire1'   );
		if(!in_array('telefono', $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN telefono VARCHAR(30)  NULL DEFAULT NULL AFTER email'   );
		if(!in_array('ciudad',   $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN ciudad   VARCHAR(40)  NULL DEFAULT NULL AFTER telefono');
		if(!in_array('estado',   $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN estado   INT          NULL DEFAULT NULL AFTER ciudad'  );
		if(!in_array('mercalib', $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN mercalib VARCHAR(50)  NULL DEFAULT NULL AFTER estado'  );
		if(!in_array('codbanc',  $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN codbanc  CHAR(2)      NULL DEFAULT NULL AFTER mercalib');
		if(!in_array('tipo_op',  $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN tipo_op  CHAR(2)      NULL DEFAULT NULL AFTER codbanc' );
		if(!in_array('num_ref',  $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN num_ref  VARCHAR(20)  NULL DEFAULT NULL AFTER tipo_op' );
		if(!in_array('observa',  $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN observa  TEXT         NULL DEFAULT NULL AFTER condi2'  );
		if(!in_array('fechadep', $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN fechadep DATE         NULL DEFAULT NULL AFTER tipo_op' );
		if(!in_array('notifica', $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN notifica CHAR(1)      NULL DEFAULT NULL AFTER fechadep');
		if(!in_array('favorito', $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN favorito CHAR(1)      NULL DEFAULT NULL AFTER notifica');
		if(!in_array('grupo',    $campos)) $this->db->query('ALTER TABLE spre ADD COLUMN grupo    INT          NULL DEFAULT "0"  AFTER favorito');

		$campos=$this->db->list_fields('itspre');
		if(!in_array('recurso', $campos)) $this->db->query('ALTER TABLE itspre ADD COLUMN recurso VARCHAR(10) NULL DEFAULT NULL');

		$campos=$this->db->list_fields('sitems');
		if(!in_array('recurso', $campos)) $this->db->query('ALTER TABLE sitems ADD COLUMN recurso VARCHAR(10) NULL DEFAULT NULL');

		if(!$this->db->table_exists('spregr')){
			$mSQL="
			CREATE TABLE spregr (
				grupo VARCHAR(60) NOT NULL DEFAULT '',
				id INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY (id),
			UNIQUE INDEX grupo (grupo)
			)
			COMMENT='Grupos de presupuestos'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM
			ROW_FORMAT=DYNAMIC;";
			$this->db->simple_query($mSQL);
		}


	}

	function en_utf8($str){
		if($this->config->item('charset')=='UTF-8' && $this->db->char_set=='latin1'){
			return utf8_encode($str);
		}else{
			return $str;
		}
	}


}
