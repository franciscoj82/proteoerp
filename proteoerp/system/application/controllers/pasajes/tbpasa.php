<?php
class Tbpasa extends Controller {
	var $mModulo = 'TBPASA';
	var $titp    = 'Modulo de pasajes';
	var $tits    = 'Modulo de pasajes';
	var $url     = 'pasajes/tbpasa/';

	function Tbpasa(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'TBPASA', $ventana=0 );
	}

	function index(){
		$this->datasis->creaintramenu(array('modulo'=>'162','titulo'=>'Reservaciones','mensaje'=>'Reservaciones','panel'=>'PASAJES','ejecutar'=>'pasajes/tbpasa','target'=>'popu','visible'=>'S','pertenece'=>'1','ancho'=>900,'alto'=>600));
		$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );


		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname']);

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"factura",   "img"=>"images/pdf_logo.gif",  "alt" => "Formato PDF", "label"=>"Facturar"));
		$grid->wbotonadd(array("id"=>"boletos",   "img"=>"images/pdf_logo.gif",  "alt" => "Formato PDF", "label"=>"Emitir Boletos"));
		$grid->wbotonadd(array("id"=>"boletos",   "img"=>"images/pdf_logo.gif",  "alt" => "Formato PDF", "label"=>"Cambiar Reserva"));
		$grid->wbotonadd(array("id"=>"boletos",   "img"=>"images/pdf_logo.gif",  "alt" => "Formato PDF", "label"=>"Eliminar Reserva"));

		$WestPanel = $grid->deploywestp();

		$adic = array(
			array('id'=>'fedita',  'title'=>'Agregar/Editar Registro'),
			array('id'=>'fshow' ,  'title'=>'Mostrar Registro'),
			array('id'=>'fborra',  'title'=>'Eliminar Registro')
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']   = $WestPanel;
		//$param['EastPanel'] = $EastPanel;
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('TBPASA', 'JQ');
		$param['otros']       = $this->datasis->otros('TBPASA', 'JQ');
		$param['temas']       = array('proteo','darkness','anexos1');
		$param['bodyscript']  = $bodyscript;
		$param['tabs']        = false;
		$param['encabeza']    = $this->titp;
		$param['tamano']      = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0 ){
		$bodyscript = '		<script type="text/javascript">';
		$ngrid      = "#newapi".$grid0;

		$bodyscript .= '
		function tbpasaadd(){
			$.post("'.site_url($this->url.'dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function tbpasaedit(){
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url($this->url.'dataedit/modify').'/"+id, function(data){
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				});
			} else {
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';

		$bodyscript .= '
		function tbpasashow(){
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
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
		function tbpasadel() {
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				if(confirm(" Seguro desea eliminar el registro?")){
					var ret    = $("#newapi'.$grid0.'").getRowData(id);
					mId = id;
					$.post("'.site_url($this->url.'dataedit/do_delete').'/"+id, function(data){
						try{
							var json = JSON.parse(data);
							if (json.status == "A"){
								apprise("Registro eliminado");
								jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
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
		$bodyscript .= $this->jqdatagrid->bswrapper($ngrid);
		$bodyscript .= $this->jqdatagrid->bsfedita( $ngrid, $height = "550", $width = "770" );
		$bodyscript .= $this->jqdatagrid->bsfshow( $height = "500", $width = "700" );
		$bodyscript .= $this->jqdatagrid->bsfborra( $height = "450", $width = "750" );

		$bodyscript .= '});'."\n";

		$bodyscript .= '</script>';
		return $bodyscript;
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = 'false';

		$grid  = new $this->jqdatagrid;

		$grid->addField('nropasa');
		$grid->label('Pasaje');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
		));


		$grid->addField('codppr');
		$grid->label('Codppr');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
		));


		$grid->addField('nacio');
		$grid->label('Nac.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 10 }',
		));


		$grid->addField('codcli');
		$grid->label('Cliente');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
		));


		$grid->addField('nomcli');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:150, maxlength: 150 }',
		));


		$grid->addField('codcarnet');
		$grid->label('Carnet');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
		));


		$grid->addField('dtn');
		$grid->label('Destino');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:100, maxlength: 100 }',
		));


		$grid->addField('fecven');
		$grid->label('Fec.Ven');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('tippas');
		$grid->label('Tippas');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('anula');
		$grid->label('Anulado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('prepas');
		$grid->label('Prepas');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('seguro');
		$grid->label('Seguro');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('mondes');
		$grid->label('Mondes');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('moncomi');
		$grid->label('Moncomi');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('codofi');
		$grid->label('Codofi');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('tipven');
		$grid->label('Tipven');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 10 }',
		));


		$grid->addField('horpas');
		$grid->label('Horpas');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:20, maxlength: 20 }',
		));


		$grid->addField('codptos');
		$grid->label('Codptos');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('coddes');
		$grid->label('Coddes');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:50, maxlength: 50 }',
		));


		$grid->addField('tippag');
		$grid->label('Tippag');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 10 }',
		));


		$grid->addField('tasa');
		$grid->label('Tasa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 140,
			'edittype'      => "'text'",
		));


		$grid->addField('codrut');
		$grid->label('Codrut');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 10 }',
		));


		$grid->addField('fecpas');
		$grid->label('Fecpas');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');



		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('TBPASA','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('TBPASA','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('TBPASA','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('TBPASA','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("addfunc: tbpasaadd, editfunc: tbpasaedit, delfunc: tbpasadel, viewfunc: tbpasashow");

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

	/**
	* Busca la data en el Servidor por json
	*/
	function getdata(){
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('tbpasa');

		$response   = $grid->getData('tbpasa', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$mcodp  = "??????";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM tbpasa WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('tbpasa', $data);
					echo "Registro Agregado";

					logusu('TBPASA',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM tbpasa WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM tbpasa WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE tbpasa SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("tbpasa", $data);
				logusu('TBPASA',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('tbpasa', $data);
				logusu('TBPASA',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM tbpasa WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM tbpasa WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM tbpasa WHERE id=$id ");
				logusu('TBPASA',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	function dataedit(){
		//$this->rapyd->load('dataobject','datadetails');
		$this->rapyd->load('dataedit');
		$script= '
		$(function() {
			$("#fecven").datepicker({dateFormat:"dd/mm/yy"});
		});';

		$edit = new DataEdit($this->tits, 'tbpasa');

		$edit->script($script,'modify');
		$edit->script($script,'create');
		$edit->on_save_redirect=false;

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process( 'insert','_pre_insert' );
		$edit->pre_process( 'update','_pre_update' );
		$edit->pre_process( 'delete','_pre_delete' );

		$edit->fecven = new dateField('Fecha','fecven');
		$edit->fecven->rule        = 'chfecha';
		$edit->fecven->size        = 12;
		$edit->fecven->maxlength   = 12;
		$edit->fecven->insertValue = date('Y-m-d');
		$edit->fecven->calendar = false;

		$edit->org = new dropdownField('Origen','org');
		$edit->org->rule = '';
		$edit->org->option('','Seleccionar');
		$edit->org->options('SELECT a.codofi, CONCAT(a.codofi," ", a.desofi) desofi FROM tbofici AS a ORDER BY a.codofi');
		$edit->org->style ='width:170px;';

		$edit->dtn = new dropdownField('Destino','dtn');
		$edit->dtn->rule='';
		$edit->dtn->option('','Seleccionar');
		$edit->dtn->options('SELECT a.codofi, CONCAT(a.codofi," ", a.desofi) desofi FROM tbofici AS a ORDER BY a.codofi');
		$edit->dtn->style='width:170px;';

		$edit->nropasa = new inputField('Nro. Pasaje','nropasa');
		$edit->nropasa->rule       = '';
		$edit->nropasa->size       = 10;
		$edit->nropasa->maxlength  =  8;

		$edit->codppr = new inputField('Codppr','codppr');
		$edit->codppr->rule       = '';
		$edit->codppr->size       = 22;
		$edit->codppr->maxlength  = 20;

		$edit->nacio = new dropdownField('Nacionalidad','nacio');
		$edit->nacio->rule      = '';
		$edit->nacio->option('V','Venezolano');
		$edit->nacio->option('E','Extranjero');
		$edit->nacio->option('P','Pasaporte');
		$edit->nacio->option('J','Juridico');
		$edit->nacio->option('G','Gobierno');
		$edit->nacio->style ='width:100px;';

		$edit->codcli = new inputField('C.I.','codcli');
		$edit->codcli->rule      = '';
		$edit->codcli->size      = 10;
		$edit->codcli->maxlength = 20;

		$edit->nomcli = new inputField('Nombre','nomcli');
		$edit->nomcli->rule      = '';
		$edit->nomcli->size      = 32;
		$edit->nomcli->maxlength = 150;

		$edit->codcarnet = new inputField('Codcarnet','codcarnet');
		$edit->codcarnet->rule      = '';
		$edit->codcarnet->size      = 22;
		$edit->codcarnet->maxlength = 20;

		$edit->codrut = new hiddenField('Ruta','codrut');

		$edit->tippas = new inputField('Tippas','tippas');
		$edit->tippas->rule='';
		$edit->tippas->size =7;
		$edit->tippas->maxlength =5;

		$edit->anula = new inputField('Anula','anula');
		$edit->anula->rule='';
		$edit->anula->size =3;
		$edit->anula->maxlength =1;

		$edit->prepas = new inputField('Prepas','prepas');
		$edit->prepas->rule='';
		$edit->prepas->size =10;
		$edit->prepas->maxlength =8;

		$edit->seguro = new inputField('Seguro','seguro');
		$edit->seguro->rule='';
		$edit->seguro->size =10;
		$edit->seguro->maxlength =8;

		$edit->mondes = new inputField('Mondes','mondes');
		$edit->mondes->rule='';
		$edit->mondes->size =10;
		$edit->mondes->maxlength =8;

		$edit->moncomi = new inputField('Moncomi','moncomi');
		$edit->moncomi->rule='';
		$edit->moncomi->size =10;
		$edit->moncomi->maxlength =8;

		$edit->codofi = new inputField('Codofi','codofi');
		$edit->codofi->rule='';
		$edit->codofi->size =7;
		$edit->codofi->maxlength =5;

		$edit->tipven = new inputField('Tipven','tipven');
		$edit->tipven->rule='';
		$edit->tipven->size =12;
		$edit->tipven->maxlength =10;

		$edit->horpas = new inputField('Horpas','horpas');
		$edit->horpas->rule='';
		$edit->horpas->size =22;
		$edit->horpas->maxlength =20;

		$edit->codptos = new inputField('Codptos','codptos');
		$edit->codptos->rule='';
		$edit->codptos->size =10;
		$edit->codptos->maxlength =8;

		$edit->coddes = new inputField('Coddes','coddes');
		$edit->coddes->rule='';
		$edit->coddes->size =17;
		$edit->coddes->maxlength =15;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->tippag = new inputField('Tippag','tippag');
		$edit->tippag->rule='';
		$edit->tippag->size =12;
		$edit->tippag->maxlength =10;

		$edit->tasa = new inputField('Tasa','tasa');
		$edit->tasa->rule='';
		$edit->tasa->size =10;
		$edit->tasa->maxlength =8;

		$edit->fecpas = new inputField('Fecpas','fecpas');
		$edit->fecpas->rule='';
		$edit->fecpas->size =17;
		$edit->fecpas->maxlength =15;

		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);
			echo json_encode($rt);
		}else{
			$conten['form']  =& $edit;
			$this->load->view('view_tbpasa', $conten);
		}
	}


	//******************************************************************
	// Get data para las rutas
	//
	function getbrutas(){
		$mid1  = $this->uri->segment(4);
		$mid2  = $this->uri->segment(5);
		$dia   = $this->uri->segment(6);
		$mes   = $this->uri->segment(7);
		$ano   = $this->uri->segment(8);
		
		$grid     = $this->jqdatagrid;

		$qmid1 = $this->db->escape($mid1);
		$qmid2 = $this->db->escape($mid2);

		$mSQL = "
			SELECT a.id, b.codrut, b.horsal, b.tipuni, b.origen, b.destino, a.orden, a.hora, IF(b.tipserv='01', prec_01, prec_02)+e.valsegu+e.vtasa precio  
			FROM tbdestinos a 
			JOIN tbrutas    b ON a.codrut = b.codrut 
			JOIN tbprecios  c ON c.codofiorg=a.codofiorg AND c.codofides=a.codofides
			LEFT JOIN tbbloqueo d ON a.codrut=d.codrut AND d.fecblo=${ano}${mes}${dia}   
			JOIN tbparam    e ON e.codofiori=a.codofiorg
			WHERE a.codofiorg = ${qmid1} AND a.codofides = ${qmid2} AND a.mostrar='S' AND d.codrut IS NULL
			ORDER BY b.horsal 
		";


		$response = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
		
	}

	//******************************************************************
	// Busca los puestos disponibles
	//
	function puestos(){
		$id    = $this->uri->segment(4);
		$dia   = $this->uri->segment(5);
		$mes   = $this->uri->segment(6);
		$ano   = $this->uri->segment(7);

		$reg  = $this->datasis->damereg('SELECT codrut,codofiorg,codofides FROM tbdestinos WHERE id='.$id);

		$codrut  = $reg['codrut'];
		$origen  = $reg['codofiorg'];
		$destino = $reg['codofides'];

		$mSQL = "SELECT orden FROM tbdestinos WHERE codrut = '${codrut}' AND codofiorg = '${origen}' AND codofides='${origen}' ";
		$inicio = $this->datasis->dameval($mSQL);

		$mSQL = "SELECT orden FROM tbdestinos WHERE codrut = '${codrut}' AND codofiorg = '${origen}' AND codofides='${destino}' ";
		$fin = $this->datasis->dameval($mSQL);

		$mSQL1 = "
		SELECT b.indice, b.valor, if(c.nroasi is null, 'L', c.tipven ) estatus, b.id
		FROM tbrutas a JOIN tbtipbus b ON a.tipuni=b.tipbus 
			LEFT JOIN tbpuestos c ON a.codrut=c.codrut 
			AND b.valor=c.nroasi 
			AND c.fecpas=${ano}${mes}${dia} 
			AND c.inicio<${fin} AND c.fin>${inicio} 
		WHERE a.codrut='${codrut}' AND ";

		$rs = "No hay Disponibilidad";
		$bl = "\t\t<td>&nbsp;<td>\n";
		
		$rs  = "<table style='border-collapse:collapse;'>";
		//$rs .= "<tr><td colspan='3' align='center'>Ruta: ".$codrut." Fecha: ".$dia."/".$mes."/".$ano."</td></tr>";
	
		$rs .= "<tr><td>PLANTA ALTA/UNICA</td><td>&nbsp;&nbsp;</td><td>PLANTA BAJA</td></tr>";
		$rs .= "<tr><td><table style='border-collapse:collapse;'>\n\t<tr>\n";

		$mSQL = $mSQL1." b.indice < 12 ORDER BY b.indice ";
		$rs .= $this->busfila($mSQL, 0);

		$mSQL = $mSQL1."b.indice > 11 AND b.indice < 24 ORDER BY b.indice ";
		$rs .= $this->busfila($mSQL, 12);

		$mSQL = $mSQL1." b.indice > 23 AND b.indice < 36 ORDER BY b.indice ";
		$rs .= $this->busfila($mSQL, 24);

		$mSQL = $mSQL1." b.indice > 35 AND b.indice < 48 ORDER BY b.indice ";
		$rs .= $this->busfila($mSQL, 36);

		$rs .= "</table>\n</td>\n<td>&nbsp;</td>";

		// SEGUNDO PISO
		$rs .= "<td>\n<table style='border-collapse:collapse;'>\n\t<tr>\n";

		$mSQL = $mSQL1." b.indice >= 100 AND b.indice < 112 ORDER BY b.indice ";
		$rs .= $this->busfila($mSQL, 100);

		$mSQL = $mSQL1." b.indice > 111 AND b.indice < 124 ORDER BY b.indice ";
		$rs .= $this->busfila($mSQL, 112);

		$mSQL = $mSQL1." b.indice > 123 AND b.indice < 136 ORDER BY b.indice ";
		$rs .= $this->busfila($mSQL, 124);

		$mSQL = $mSQL1." b.indice > 135 AND b.indice < 148 ORDER BY b.indice ";
		$rs .= $this->busfila($mSQL, 136);

		$rs .= "</table>\n</td></tr></table>";

		echo $rs;
		
	}

	//******************************************************************
	//
	//
	function busfila($mSQL, $i) {
		$libre   = "style='background:#0EF72D;'";
		$ocupado = "style='background:#FC0532;color:white;font-weight:bold;font-size:12px;margin-left:0.5em;margin-right:0.5em;height:20px;'"; //color:#FFFFFF; font-weight:bold;'";
		$reserva = "style='background:#026837;color:white;font-weight:bold;font-size:12px;margin-left:0.5em;margin-right:0.5em;height:20px;'"; //color:#FFFFFF; margin-left:10em; font-weight:bold;'";
		$manual  = "style='background:#F2A2F2;color:black;font-weight:bold;font-size:12px;margin-left:0.5em;margin-right:0.5em;height:20px;'"; //color:#FFFFFF; margin-left:10em; font-weight:bold;'";
		$mi = $i;
		$query = $this->db->query($mSQL);

		$bl = "\t\t<td>&nbsp;<td>\n";
		$rs = "";
		$f = $i+11;
		if ($query->num_rows() > 0){
			$rs  = "\t<tr>\n";
			$rs1 = '';
			foreach( $query->result_array() as  $row ){
				$color = $libre;
				if ($row['estatus'] == 'L')	$color = $libre;
				if ($row['estatus'] == 'R')	$color = $reserva;
				if ($row['estatus'] == 'V')	$color = $ocupado;
				if ($row['estatus'] == 'P')	$color = $manual;
				while ( $i != $row['indice'] ){ 
					$rs1 = $bl.$rs1; 
					$i++;	
					if ( $i > $mi+12  ) break;
				}
				if ( $i == $row['indice'] ){
					if ($row['estatus'] == 'L')
						$rs1 = "\t\t<td ".$color." ><input type='checkbox' id='asiento".$row['valor']."' name='asiento".$row['valor']."' onclick='resepu(\"".$row['valor']."\",\"".utf8_encode($row['valor'])."\")' ><label for='asiento".$row['valor']."'>".utf8_encode($row['valor'])."</label><td>\n".$rs1;
					else
						$rs1 = "\t\t<td ".$color."><label ".$color." for='asiento".$row['indice']."'>".utf8_encode($row['valor'])."</label><td>\n".$rs1;
				}
				$i ++;
			}
			while ( $i <= $f ){ $rs1 = "\t\t<td>&nbsp;<td>\n".$rs1;$i ++;}
			$rs .= $rs1."\t</tr>\n";
		}
		return $rs;
	}


	//******************************************************************
	//
	//
	function drutas(){
		session_write_close();
		$mid1  = $this->input->post('q1');
		$mid2  = $this->input->post('q2');
		$feven = $this->input->post('fecven');

		$data = '<h1>No se encontraron resultados</h1>';
		if($mid1 !== false && $mid2 !== false){
			$qmid1 = $this->db->escape($mid1);
			$qmid2 = $this->db->escape($mid2);

			$qlite = $this->db->escape($mid1.'%'.$mid2);

			$retArray = $retorno = array();
			$mSQL="SELECT aa.codrut,CONCAT_WS('-',b.origen,b.destino,hora) as label FROM (
				SELECT a.codrut,
				GROUP_CONCAT(DISTINCT codofides ORDER BY orden) AS toques, GROUP_CONCAT(DISTINCT a.hora ORDER BY orden) hora
				FROM tbdestinos AS a
				WHERE a.codofides IN (${qmid1},${qmid2})
				GROUP BY a.codrut) AS aa
				JOIN tbrutas AS b ON aa.codrut=b.codrut
				WHERE aa.toques LIKE ${qlite}";

			$mSQL = "
				SELECT a.id, b.codrut, b.horsal, b.tipuni, b.origen, b.destino, a.orden  
				FROM tbdestinos a JOIN tbrutas b ON a.codrut=b.codrut
				WHERE a.codofiorg = ${qmid1} AND a.codofides = ${qmid2}
			";

			//echo $mSQL;

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$data  = "<table>\n";
				$data .= "\t<tr>";
				$data .= "\t\t<td>Ruta<td>";
				$data .= "<td>Salida<td>";
				$data .= "<td>Unidad<td>";
				$data .= "<td>Origen<td>";
				$data .= "<td>Destino<td>";
				$data .= "<td>Orden<td>";
				$data .= "\t</tr>\n";

				foreach( $query->result_array() as  $row ) {
					$data .= "\t<tr>";
					$data .= "\t\t<td>".utf8_encode($row['codrut'])."<td>";
					$data .= "<td>".utf8_encode($row['horsal'])."<td>";
					$data .= "<td>".utf8_encode($row['tipuni'])."<td>";
					$data .= "<td>".utf8_encode($row['origen'])."<td>";
					$data .= "<td>".utf8_encode($row['destino'])."<td>";
					$data .= "<td>".utf8_encode($row['orden'])."<td>";
					$data .= "\t</tr>\n";
				}
				$data .= "</table>\n";
			}
		}
		echo $data;
		return true;
	}

	function getruta(){
		session_write_close();
		$mid1 = $this->input->post('q1');
		$mid2 = $this->input->post('q2');

		$data = '[ ]';
		if($mid1 !== false && $mid2 !== false){
			$qmid1 = $this->db->escape($mid1);
			$qmid2 = $this->db->escape($mid2);

			$qlite = $this->db->escape($mid1.'%'.$mid2);

			$retArray = $retorno = array();
			$mSQL="SELECT aa.codrut,CONCAT_WS('-',b.origen,b.destino,hora) as label FROM (
				SELECT a.codrut,
				GROUP_CONCAT(DISTINCT codofides ORDER BY orden) AS toques, GROUP_CONCAT(DISTINCT a.hora ORDER BY orden) hora
				FROM tbdestinos AS a
				WHERE a.codofides IN (${qmid1},${qmid2})
				GROUP BY a.codrut) AS aa
				JOIN tbrutas AS b ON aa.codrut=b.codrut
				WHERE aa.toques LIKE ${qlite}";
			//echo $mSQL;
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']    = utf8_encode($row['codrut']);
					$retArray['label']    = utf8_encode('('.$row['codrut'].') '.$row['label']);
					array_push($retorno, $retArray);
				}
			}
			if(count($data)>0)
				$data = json_encode($retorno);
		}
		echo $data;
		return true;
	}

	function getpuestos(){
		session_write_close();
		$mid1 = $this->input->post('fecha');
		$mid2 = $this->input->post('ruta');

		$data = '[ ]';
		if($mid1 !== false && $mid2 !== false){
			$qmid1 = $this->db->escape($mid1);
			$qmid2 = $this->db->escape($mid2);

			$unidad= $this->datasis->dameval('SELECT tipuni FROM tbrutas WHERE codrut='.$qmid2);
			if(empty($unidad)){
				echo $data;
				return true;
			}
			$dbunidad= $this->db->escape($unidad);

			$qlite = $this->db->escape($mid1.'%'.$mid2);

			$retArray = $retorno = array();
			$mSQL="SELECT indice,valor FROM tbtipbus WHERE tipbus=${dbunidad}";
			//echo $mSQL;
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']    = utf8_encode($row['valor']);
					$retArray['label']    = utf8_encode($row['indice']);
					array_push($retorno, $retArray);
				}
			}
			if(count($data)>0)
				$data = json_encode($retorno);
		}
		echo $data;
		return true;
	}

	function _pre_insert($do){
		$codrut  = $_POST['codrut'];
		$origen  = $_POST['org'];
		$destino = $_POST['dtn'];
		$fecven  = $_POST['fecven'];
		$reto = false;
		$msj = "";

		$fecven = substr($fecven,6,4).substr($fecven,3,2).substr($fecven,0,2);

		$inicio = $this->datasis->dameval("SELECT orden FROM tbdestinos WHERE codrut='$codrut' AND codofiorg='$origen' AND codofides='$origen'");
		$fin    = $this->datasis->dameval("SELECT orden FROM tbdestinos WHERE codrut='$codrut' AND codofiorg='$origen' AND codofides='$destino'");

		$puestos = array();
		$localiza = $this->datasis->prox_numero('nlocaliza');

		foreach( $_POST as $id=>$nombre ){
			if (substr( $id,0,7) == 'asiento') {
				$nroasi = str_replace('asiento','',$id);
				// Guarda los Puestos 
				$data = array(
					"codrut"   => $codrut,
					"fecpas"   => $fecven,
					"tipven"   => "R",
					"nroasi"   => $nroasi,  
					"inicio"   => $inicio,
					"fin"      => $fin,
					"localiza" => $localiza );
			
				$istring = $this->db->insert_string('tbpuestos',$data);
				$istring = str_replace("INSERT INTO","INSERT IGNORE INTO",$istring);
				$this->db->query($istring);
				
				$puestos = $this->db->insert_id();
				$reto = true;

				if  ($puestos == 0 ) {
					// No funciono deshace todo
					$this->db->query("delete from tbpuestos where localiza=$localiza"); 
					$reto = false;
					$msj = "Asientos Vendidos";
					break;
				}
			}
		}
		
		$do->set('nacio','TTT');
		$do->set('codcli','-TEMP');
		$do->set('nomcli','*TEMPORAL SE ELIMINARA');
		$do->set('usuario', $this->session->userdata('usuario'));
		$do->set('tipven','R');
		$do->set('localiza',$localiza);
		
		$do->error_message_ar['pre_ins']=$msj;
		return $reto;
	}

	function _pre_update($do){
		$do->error_message_ar['pre_upd']='';
		return true;
	}

	function _pre_delete($do){
		$do->error_message_ar['pre_del']='';
		return false;
	}

	function _post_insert($do){
	
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}

	function instalar(){
		if (!$this->db->table_exists('tbpasa')) {
			$mSQL="CREATE TABLE `tbpasa` (
			  `nropasa` double NOT NULL AUTO_INCREMENT,
			  `codppr` varchar(20) DEFAULT NULL,
			  `nacio` varchar(10) DEFAULT NULL,
			  `codcli` varchar(20) DEFAULT '',
			  `nomcli` varchar(150) DEFAULT '',
			  `codcarnet` varchar(20) DEFAULT '',
			  `dtn` varchar(100) DEFAULT '',
			  `fecven` varchar(15) DEFAULT '',
			  `tippas` varchar(5) DEFAULT '',
			  `anula` char(1) DEFAULT '',
			  `prepas` double DEFAULT '0',
			  `seguro` double DEFAULT '0',
			  `mondes` double DEFAULT '0',
			  `moncomi` double DEFAULT '0',
			  `codofi` varchar(5) DEFAULT '',
			  `tipven` varchar(10) DEFAULT '',
			  `horpas` varchar(20) DEFAULT '',
			  `codptos` double DEFAULT '0',
			  `coddes` varchar(15) DEFAULT '',
			  `usuario` varchar(50) DEFAULT '',
			  `tippag` varchar(10) DEFAULT NULL,
			  `tasa` double DEFAULT '0',
			  `codrut` varchar(10) DEFAULT NULL,
			  `fecpas` varchar(15) DEFAULT NULL,
			  UNIQUE KEY `nropasa` (`nropasa`),
			  KEY `codptos` (`codptos`),
			  KEY `codofi` (`codofi`),
			  KEY `fecven` (`fecven`),
			  KEY `tipven` (`tipven`),
			  KEY `codcli` (`codcli`),
			  KEY `codppr` (`codppr`),
			  KEY `usuario` (`usuario`)
			) ENGINE=MyISAM AUTO_INCREMENT=6863833 DEFAULT CHARSET=latin1";
			$this->db->simple_query($mSQL);
		}
	}
}
