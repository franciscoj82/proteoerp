<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Apan extends Controller {
	var $mModulo='APAN';
	var $titp='Aplicacion de Anticipos';
	var $tits='Aplicacion de Anticipos';
	var $url ='finanzas/apan/';

	function Apan(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_id('NNN',1);
	}

	function index(){
		if ( !$this->datasis->iscampo('apan','id') ) {
			$this->db->simple_query('ALTER TABLE apan DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE apan ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE apan ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		$grid1   = $this->defgridit();
		$param['grids'][] = $grid1->deploy();


		$readyLayout = '
	$(\'body\').layout({
		minSize: 30,
		north__size: 60,
		resizerClass: \'ui-state-default\',
		west__size: 212,
		west__onresize: function (pane, $Pane){jQuery("#west-grid").jqGrid(\'setGridWidth\',$Pane.innerWidth()-2);},
	});
	
	$(\'div.ui-layout-center\').layout({
		minSize: 30,
		resizerClass: "ui-state-default",
		center__paneSelector: ".centro-centro",
		south__paneSelector:  ".centro-sur",
		south__size: 150,
		center__onresize: function (pane, $Pane) {
			jQuery("#newapi'.$param['grids'][0]['gridname'].'").jqGrid(\'setGridWidth\',$Pane.innerWidth()-6);
			jQuery("#newapi'.$param['grids'][0]['gridname'].'").jqGrid(\'setGridHeight\',$Pane.innerHeight()-110);
			jQuery("#newapi'.$param['grids'][1]['gridname'].'").jqGrid(\'setGridWidth\',$Pane.innerWidth()-6);
		}
	});
	';


		$bodyscript = '
<script type="text/javascript">
$(function() {
	$( "input:submit, a, button", ".boton1" ).button();
});

jQuery("#boton1").click( function(){
	var id = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getGridParam\',\'selrow\');
	if (id)	{
		var ret = jQuery("#newapi'. $param['grids'][0]['gridname'].'").jqGrid(\'getRowData\',id);
		window.open(\'/proteoerp/formatos/ver/APAN/\'+id, \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-400), screeny=((screen.availWidth/2)-300)\');
	} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
});
</script>
';

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		$WestPanel = '
<div id="LeftPane" class="ui-layout-west ui-widget ui-widget-content">

<div class="anexos">
<table id="west-grid" align="center">
	<tr>
		<td><div class="tema1"><table id="listados"></table></div></td>
	</tr><tr>
		<td><div class="tema1"><table id="otros"></table></div></td>
	</tr>
</table>
</div>
<table id="west-grid" align="center">
	<tr>
		<td><div class="tema1 boton1"><a style="width:190px" href="#" id="boton1">Reimprimir Documento '.img(array('src' => 'images/pdf_logo.gif', 'alt' => 'Formato PDF',  'title' => 'Formato PDF', 'border'=>'0')).'</a></div></td>
	</tr>
</table>

'.

'</div> <!-- #LeftPane -->
';


		$centerpanel = '
<div id="RightPane" class="ui-layout-center">
	<div class="centro-centro">
		<table id="newapi'.$param['grids'][0]['gridname'].'"></table>
		<div id="pnewapi'.$param['grids'][0]['gridname'].'"></div>
	</div>
	<div class="centro-sur" id="adicional" style="overflow:auto;">

		<table id="newapi'.$param['grids'][1]['gridname'].'"></table>
	</div>
</div> <!-- #RightPane -->
';


		$funciones = '';

		$SouthPanel = '
<div id="BottomPane" class="ui-layout-south ui-widget ui-widget-content">
<p>'.$this->datasis->traevalor('TITULO1').'</p>
</div> <!-- #BottomPanel -->
';

		$param['WestPanel']    = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('APAN', 'JQ');
		$param['otros']        = $this->datasis->otros('APAN', 'JQ');
		
		$param['centerpanel']  = $centerpanel;
		//$param['funciones']    = $funciones;

		$param['temas']        = array('proteo','darkness','anexos1');

		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;

		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('tipo');
		$grid->label('Tipo');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));


		$grid->addField('clipro');
		$grid->label('Cli/Prv');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('monto');
		$grid->label('Monto');
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


		$grid->addField('reinte');
		$grid->label('Reintegro');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('observa1');
		$grid->label('Observa1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 50 }',
		));

/*
		$grid->addField('observa2');
		$grid->label('Observa2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 50 }',
		));
*/

		$grid->addField('transac');
		$grid->label('Transac');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('estampa');
		$grid->label('Estampa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));

		$grid->addField('modificado');
		$grid->label('Modificado');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Modificado" }'
		));

		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('230');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){
				if (id){
					var ret = $("#titulos").getRowData(id);
					jQuery(gridId2).jqGrid(\'setGridParam\',{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
					jQuery(gridId2).trigger("reloadGrid");
				}
			}
		');

		$grid->setFormOptionsE('-');
		$grid->setFormOptionsA('-');
		$grid->setAfterSubmit("-");
		$grid->setOndblClickRow('');

		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(false);
		$grid->setDelete(false);
		$grid->setSearch(false);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

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
	function getdata()
	{
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('apan');

		$response   = $grid->getData('apan', array(array()), array(), false, $mWHERE, 'id', 'desc' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData()
	{
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$this->db->insert('apan', $data);
				echo "Registro Agregado";

				logusu('APAN',"Registro ????? INCLUIDO");
			} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			//unset($data['ubica']);
			$this->db->where('id', $id);
			$this->db->update('apan', $data);
			logusu('APAN',"Registro ????? MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM apan WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM apan WHERE id=$id ");
				logusu('APAN',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}

	//**********************************
	//Definicion del Grid del Item
	//**********************************
	function defgridit( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('origen');
		$grid->label('Origen');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));

		$grid->addField('anticipo');
		$grid->label('Anticipo');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));

		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 90,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('monto');
		$grid->label('Monto');
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


		$grid->addField('abono');
		$grid->label('Abono');
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


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight(100);
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(false);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('-');
		$grid->setFormOptionsA('-');
		$grid->setAfterSubmit("-");
		$grid->setOndblClickRow('');


		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(false);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(100);
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

	/*
	* Busca la data en el Servidor por json
	*/
	function getdatait()
	{
		$id = $this->uri->segment(4);
		if ($id){
			$transac = $this->datasis->dameval("SELECT transac FROM apan WHERE id=$id");
			$grid       = $this->jqdatagrid;
			$mSQL = "
				SELECT 'Cliente' origen, cod_cli, fecha, CONCAT(tipoccli,numccli) anticipo, CONCAT(tipo_doc, numero) numero, monto, abono, ppago, reten, reteiva, id
				FROM itccli WHERE transac='$transac' 
				UNION ALL
				SELECT 'Prveed' origen, cod_prv, fecha, CONCAT(tipoppro,numppro) anticipo, CONCAT(tipo_doc, numero) numero, monto, abono, ppago, reten, reteiva, id
				FROM itppro WHERE transac='$transac'
			";

			$response   = $grid->getDataSimple($mSQL);
			$rs = $grid->jsonresult( $response);
		} else
			$rs ='';
		echo $rs;
	}





/*
class apan extends validaciones {

	function apan(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		if ( !$this->datasis->iscampo('apan','id') ) {
			$this->db->simple_query('ALTER TABLE apan DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE apan ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE apan ADD UNIQUE INDEX numero (numero)');
			echo "Indice ID Creado";
		}
		$this->datasis->modulo_id(505,1);
		$this->apanextjs();
		//redirect("finanzas/apan/filteredgrid");
	}

*/
	function dataedit($tipo)	{
		$this->rapyd->load('dataobject','datadetails');
		$do = new DataObject("apan");
		$title="";
		if($tipo=='P'){
			$do->rel_one_to_many('itppro', 'itppro', array('transac'=>'transac'));
			$title='itppro';
		}
		else {
			$do->rel_one_to_many('itccli', 'itccli', array('transac'=>'transac'));
			$title='itccli';
		}


		$edit = new DataDetails('Aplicaci&oacute;n de Anticipos', $do);
		$edit->back_url = site_url('finanzas/apan/filteredgrid');
		$edit->set_rel_title($title,'Anticipo <#o#>');

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->mode="autohide";
		$edit->numero->size =12;
		$edit->numero->rule="trim|required";
		$edit->numero->maxlength=8;

		$edit->fecha = new DateonlyField("Fecha", "fecha");
		$edit->fecha->size = 12;
		$edit->fecha->rule="required|chfecha";
		$edit->fecha->insertValue = date("Y-m-d");

		$edit->tipo = new dropdownField("Tipo", "tipo");
		$edit->tipo->option("C","Cliente");
		$edit->tipo->option("P","Proveedor");
		$edit->tipo->style="width:100px";
			
		$edit->clipro =new inputField("Codigo", "clipro");
		$edit->clipro->rule='trim|required';
		$edit->clipro->size =12;
		$edit->clipro->readonly=true;

		$edit->nombre =   new inputField("Nombre", "nombre");
		$edit->nombre->size =30;
		$edit->nombre->rule = "trim|strtoupper";
		$edit->nombre->readonly=true;

		$edit->monto =    new inputField("Monto", "monto");
		$edit->monto->size = 12;
		$edit->monto->css_class='inputnum';
		$edit->monto->rule='trim|numeric';
		$edit->monto->maxlengxlength=0;
		$edit->monto->rule='positive';

		$edit->reinte =   new inputField("Convertido", "reinte");
		$edit->reinte->rule='trim|required';
		$edit->reinte->size =12;
		$edit->reinte->readonly=true;

		$edit->nombreintes=new inputField("Nombre","nombreintes");
		$edit->nombreintes->size=30;
		$edit->nombreintes->readonly=true;

		$edit->observa1 = new inputField("Observaciones", "observa1");
		$edit->observa1->rule='trim';
		$edit->observa1->size =50;
		$edit->observa1->maxlength=50;

		$edit->observa2 = new inputField("", "observa2");
		$edit->observa2->rule='trim';
		$edit->observa2->size =50;
		$edit->observa2->maxlength=50;

		//Detalles itppro
		if($tipo=='P'){
			$edit->tipoppro = new inputField("Tipo <#o#>","tipoppro_<#i#>");
			$edit->tipoppro->db_name = "tipoppro";
			$edit->tipoppro->rel_id  = 'itppro';
			$edit->tipoppro->rule='trim|required';
			$edit->tipoppro->size =10;
			$edit->tipoppro->readonly=true;

			$edit->tipo_doc = new inputField("Tipo Documento <#o#>","tipo_doc_<#i#>");
			$edit->tipo_doc->db_name = "tipo_doc";
			$edit->tipo_doc->rel_id  = 'itppro';
			$edit->tipo_doc->rule='trim|required';
			$edit->tipo_doc->size =10;
			$edit->tipo_doc->readonly=true;

			$edit->itnumero = new inputField("N&uacute;mero <#o#>","itnumero_<#i#>");
			$edit->itnumero->db_name = "numero";
			$edit->itnumero->rel_id  = 'itppro';
			$edit->itnumero->rule='trim|required';
			$edit->itnumero->size =10;
			$edit->itnumero->readonly=true;

			$edit->itnumppro = new inputField("N&uacute;mero <#o#>","itnumppro_<#i#>");
			$edit->itnumppro->db_name = "numppro";
			$edit->itnumppro->rel_id  = 'itppro';
			$edit->itnumppro->rule='trim|required';
			$edit->itnumppro->size =10;
			$edit->itnumppro->readonly=true;

			$edit->itfechap = new DateonlyField("Fecha", "itfechap_<#i#>");
			$edit->itfechap->db_name = "fecha";
			$edit->itfechap->rel_id  = 'itppro';
			$edit->itfechap->size = 12;
			$edit->itfechap->rule="required|chfecha";
			$edit->itfechap->insertValue = date("Y-m-d");

			$edit->itmontop = new inputField("Monto <#o#>", "itmontop_<#i#>");
			$edit->itmontop->db_name='monto';
			$edit->itmontop->css_class='inputnum';
			$edit->itmontop->rel_id   ='itppro';
			$edit->itmontop->size=3;
			$edit->itmontop->rule='positive';

			$edit->itabonop = new inputField("Abono <#o#>", "itabonop_<#i#>");
			$edit->itabonop->db_name='abono';
			$edit->itabonop->css_class='inputnum';
			$edit->itabonop->rel_id   ='itppro';
			$edit->itabonop->size=3;
			$edit->itabonop->rule='positive';
		}
		//Detalles itccli
		if($tipo=='C'){
			$edit->tipoccli = new inputField("Tipo <#o#>","tipoccli_<#i#>");
			$edit->tipoccli->db_name = "tipoccli";
			$edit->tipoccli->rel_id  = 'itccli';
			$edit->tipoccli->rule='trim|required';
			$edit->tipoccli->size =10;
			$edit->tipoccli->readonly=true;

			$edit->tipo_doc_c = new inputField("Tipo Documento <#o#>","tipo_doc_C<#i#>");
			$edit->tipo_doc_c->db_name = "tipo_doc";
			$edit->tipo_doc_c->rel_id  = 'itccli';
			$edit->tipo_doc_c->rule='trim|required';
			$edit->tipo_doc_c->size =10;
			$edit->tipo_doc_c->readonly=true;

			$edit->itnumero_c = new inputField("N&uacute;mero <#o#>","itnumero_c_<#i#>");
			$edit->itnumero_c->db_name = "numero";
			$edit->itnumero_c->rel_id  = 'itccli';
			$edit->itnumero_c->rule='trim|required';
			$edit->itnumero_c->size =10;
			$edit->itnumero_c->readonly=true;

			$edit->numccli = new inputField("N&uacute;mero <#o#>","numccli_<#i#>");
			$edit->numccli->db_name = "numccli";
			$edit->numccli->rel_id  = 'itccli';
			$edit->numccli->rule='trim|required';
			$edit->numccli->size =10;
			$edit->numccli->readonly=true;

			$edit->itfechac = new DateonlyField("Fecha", "itfechac_<#i#>");
			$edit->itfechac->db_name = "fecha";
			$edit->itfechac->rel_id  = 'itccli';
			$edit->itfechac->size = 12;
			$edit->itfechac->rule="required|chfecha";
			$edit->itfechac->insertValue = date("Y-m-d");

			$edit->itmontoc = new inputField("Monto <#o#>", "itmontoc_<#i#>");
			$edit->itmontoc->db_name='monto';
			$edit->itmontoc->css_class='inputnum';
			$edit->itmontoc->rel_id   ='itccli';
			$edit->itmontoc->size=3;
			$edit->itmontoc->rule='positive';

			$edit->itabonoc = new inputField("Abono <#o#>", "itabonoc_<#i#>");
			$edit->itabonoc->db_name='abono';
			$edit->itabonoc->css_class='inputnum';
			$edit->itabonoc->rel_id   ='itccli';
			$edit->itabonoc->size=3;
			$edit->itabonoc->rule='positive';
		}
		///fin de detalles
		$edit->buttons("modify", "save", "undo", "delete", "back");
		$edit->build();

		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_apan', $conten,true);
		$data['title']   = "<h1>Aplicaci&oacute;n de Anticipos</h1>";
		$data["script"]  = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js");
		$data["head"]    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function instalar(){
		//$sql="ALTER TABLE `apan`  DROP PRIMARY KEY";
		//$this->db->query($sql);
		$sql="ALTER TABLE `apan`  ADD COLUMN `id` INT(10) NULL AUTO_INCREMENT AFTER `usuario`,  ADD PRIMARY KEY (`id`)";
		$this->db->query($sql);
	}


/*

	function griditapan(){
		$numero   = isset($_REQUEST['numero'])  ? $_REQUEST['numero']   :  '';
		if ($numero == '' ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM apan ")  ;
		} else
			$id = $this->datasis->dameval("SELECT id FROM apan WHERE numero='$numero' ")  ;

		$transac  =  $this->datasis->dameval("SELECT transac FROM apan WHERE id=$id ")  ;
		
	
		$mSQL = "
SELECT
'1' origen, cod_cli, fecha, tipo_doc, numero, monto, abono, ppago, reten, reteiva
FROM itccli WHERE transac='$transac' 
UNION ALL
SELECT
'2' origen, cod_prv, fecha, tipo_doc, numero, monto, abono, ppago, reten, reteiva
FROM itppro WHERE transac='$transac'
";
	}

		$funciones = "
function renderScli(value, p, record) {
	var mreto='';
	if ( record.data.cod_cli == '' ){
		mreto = '{0}';
	} else {
		mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlAjax+'sclibu/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	}
	return Ext.String.format(mreto,	value, record.data.numero );
}

function renderSinv(value, p, record) {
	var mreto='';
	mreto = '<a href=\'javascript:void(0);\' onclick=\"window.open(\''+urlApp+'inventario/sinv/dataedit/show/{1}\', \'_blank\', \'width=800,height=600,scrollbars=yes,status=yes,resizable=yes,screenx='+mxs+',screeny='+mys+'\');\" heigth=\"600\">{0}</a>';
	return Ext.String.format(mreto,	value, record.data.codid );
}
	";


*/
}

?>