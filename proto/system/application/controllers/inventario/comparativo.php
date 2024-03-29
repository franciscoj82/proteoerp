<?php
class Comparativo extends Controller {

	function Comparativo(){
		parent::Controller();
		$this->load->library("rapyd");
	}

	function index(){
		//$this->datasis->modulo_id(309,1);
		redirect("inventario/comparativo/filteredgrid");
	}

	function filteredgrid(){
		$this->rapyd->load("datafilter2","datagrid");
		$mSPRV=array(
				'tabla'   =>'sprv',
				'columnas'=>array(
				'proveed' =>'C&oacute;odigo',
				'nombre'=>'Nombre',
				'contacto'=>'Contacto'),
				'filtro'  =>array('proveed'=>'C&oacute;digo','nombre'=>'Nombre'),
				'retornar'=>array('proveed'=>'proveed'),
				'titulo'  =>'Buscar Proveedor');

		$bSPRV=$this->datasis->modbus($mSPRV);

		$link2=site_url('inventario/common/get_linea');
		$link3=site_url('inventario/common/get_grupo');

		$script='
		$(document).ready(function(){

			$("#depto").change(function(){
				depto();
				$.post("'.$link2.'",{ depto:$(this).val() },function(data){$("#linea").html(data);})
				$.post("'.$link3.'",{ linea:"" },function(data){$("#grupo").html(data);})
			});
			$("#linea").change(function(){
				linea();
				$.post("'.$link3.'",{ linea:$(this).val() },function(data){$("#grupo").html(data);})
			});

			$("#grupo").change(function(){
				grupo();
			});
			depto();
			linea();
			grupo();
		});

		function depto(){
			if($("#depto").val()!=""){
				$("#nom_depto").attr("disabled","disabled");
			}
			else{
				$("#nom_depto").attr("disabled","");
			}
		}

		function linea(){
			if($("#linea").val()!=""){
				$("#nom_linea").attr("disabled","disabled");
			}
			else{
				$("#nom_linea").attr("disabled","");
			}
		}

		function grupo(){
			if($("#grupo").val()!=""){
				$("#nom_grupo").attr("disabled","disabled");
			}
			else{
				$("#nom_grupo").attr("disabled","");
			}
		}
		';

		//filter
		$filter = new DataFilter2("Filtro por Producto");
		$filter->script($script);

		$filter->db->select('a.codigo');
		$filter->db->select('a.descrip');
		$filter->db->select('s.exmin');
		$filter->db->select('s.id');
		$filter->db->from('eventas as a');
		$filter->db->join("grup as b" ,"a.grupo=b.grupo ",'LEFT');
		$filter->db->join("sinv as s" ,"a.codigo=s.codigo ",'LEFT');
		$filter->db->groupby('a.codigo');

		$filter->fechad = new dateonlyField("Desde", "fechad",'m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'m/Y');
		$filter->fechad->dbformat='Y-m-';
		$filter->fechah->dbformat='Y-m-';
		$filter->fechah->rule = "required";
		$filter->fechad->rule = "required";
		$filter->fechad->clause  =$filter->fechah->clause='';
		$filter->fechad->insertValue = date("Y-m-d",mktime(0,0,0,date('m')-12,date('j'),date('Y')));
		$filter->fechah->insertValue = date("Y-m-d");

		$filter->codigo = new inputField("C&oacute;digo", "codigo");
		$filter->codigo -> size=25;

		/*$filter->descrip = new inputField("Descripci&oacute;n", "descrip");
		$filter->descrip->db_name='CONCAT_WS(" ",a.descrip,a.descrip2)';
		$filter->descrip -> size=25;

		$filter->tipo = new dropdownField("Tipo", "tipo");
		$filter->tipo->db_name=("a.tipo");
		$filter->tipo->option("","Todos");
		$filter->tipo->option("Articulo","Art&iacute;culo");
		$filter->tipo->option("Servicio","Servicio");
		$filter->tipo->option("Descartar","Descartar");
		$filter->tipo->option("Consumo","Consumo");
		$filter->tipo->option("Fraccion","Fracci&oacute;n");
		$filter->tipo ->style='width:220px;';

		$filter->clave = new inputField("Clave", "clave");
		$filter->clave -> size=25;

		$filter->activo = new dropdownField("Activo", "activo");
		$filter->activo->option("","");
		$filter->activo->option("S","Si");
		$filter->activo->option("N","No");
		$filter->activo ->style='width:220px;';

		$filter->proveed = new inputField("Proveedor", "proveed");
		$filter->proveed->append($bSPRV);
		$filter->proveed->clause ="in";
		$filter->proveed->db_name='( a.prov1, a.prov2, a.prov3 )';
		$filter->proveed -> size=25;

		$filter->depto2 = new inputField("Departamento", "nom_depto");
		$filter->depto2->db_name="d.descrip";
		$filter->depto2 -> size=10;

		$filter->depto = new dropdownField("Departamento","depto");
		$filter->depto->db_name="d.depto";
		$filter->depto->option("","Seleccione un Departamento");
		$filter->depto->options("SELECT depto, descrip FROM dpto WHERE tipo='I' ORDER BY depto");
		$filter->depto->in="depto2";

		$filter->linea = new inputField("Linea", "nom_linea");
		$filter->linea->db_name="c.descrip";
		$filter->linea -> size=10;

		$filter->linea2 = new dropdownField("L&iacute;nea","linea");
		$filter->linea2->db_name="c.linea";
		$filter->linea2->option("","Seleccione un Departamento primero");
		$filter->linea2->in="linea";
		$depto=$filter->getval('depto');
		if($depto!==FALSE){
			$filter->linea2->options("SELECT linea, descrip FROM line WHERE depto='$depto' ORDER BY descrip");
		}else{
			$filter->linea2->option("","Seleccione un Departamento primero");
		}

		$filter->grupo2 = new inputField("Grupo", "nom_grupo");
		$filter->grupo2->db_name="b.nom_grup";
		$filter->grupo2 -> size=10;

		$filter->grupo = new dropdownField("Grupo", "grupo");
		$filter->grupo->db_name="b.grupo";
		$filter->grupo->option("","Seleccione una L&iacute;nea primero");
		$filter->grupo->in="grupo2";
		$linea=$filter->getval('linea2');
		if($linea!==FALSE){
			$filter->grupo->options("SELECT grupo, nom_grup FROM grup WHERE linea='$linea' ORDER BY nom_grup");
		}else{
			$filter->grupo->option("","Seleccione un Departamento primero");
		}

		$filter->marca = new dropdownField("Marca", "marca");
		$filter->marca->option("","");
		$filter->marca->options("SELECT TRIM(marca) AS clave, TRIM(marca) AS valor FROM marc ORDER BY marca");
		$filter->marca -> style='width:220px;';
		*/

		$filter->buttons("reset","search");
		$filter->build();

		$uri = "inventario/sinv/dataedit/show/<#codigo#>";

		function minimos($param){
			$data= func_get_args();
			$valor=array_sum($data)-min($data)-max($data);
			return ceil($valor/(count($data)-2));
		}

		$tabla='';
		if ($filter->is_valid()){
			$udia=days_in_month(substr($filter->fechah->newValue,4),substr($filter->fechah->newValue,0,4));
			$fechad=$filter->fechad->newValue.'01';
			$fechah=$filter->fechah->newValue.$udia;
			$filter->db->where('a.fecha >=',$fechad);
			$filter->db->where('a.fecha <=',$fechah);

			$datetime1 = new DateTime($fechad);
			$datetime2 = new DateTime($fechah);
			$interval = $datetime1->diff($datetime2);

			$ffechad=explode('-',$fechad);

			$grid = new DataGrid("Lista de Art&iacute;culos");
			$grid->order_by("codigo","asc");
			$grid->use_function('minimos');
			$grid->per_page = 15;

			$grid->column("C&oacute;digo",'codigo');
			$grid->column("Descripci&oacute;n","descrip");

			$columncal=array();
			for($i=0;$i<=$interval->m+1;$i++){
				$mk=mktime(0,0,0,$ffechad[1]+$i,1,$ffechad[0]);
				$udia=days_in_month(date('m',$mk),date('Y',$mk));
				$sqdesde=date("Y-m-d",$mk);
				$sqhasta=date("Y-m-",$mk).$udia;
				$etiq=date("m/Y",$mk);

				$select="SUM(cana*(fecha BETWEEN '$sqdesde' AND '$sqhasta')) AS '$etiq'";
				$filter->db->select($select);
				$grid->column($etiq,"<nformat><#$etiq#></nformat>",'align=right');
				$columncal[]="<#$etiq#>";
			}
			$grid->column('Promedio','<nformat><minimos>'.implode('|',$columncal).'</minimos></nformat>','align=right');
			$grid->column('Minimo','<nformat><#exmin#></nformat>','align=right');
			$grid->column('&nbsp;','<a href="javascript:actumin(\'<#id#>\',\'<minimos>'.implode('|',$columncal).'</minimos>\')" >Actualizar</a>','align=right');

			$grid->build();
			$tabla=$grid->output;
		}

		$url=site_url('inventario/comparativo/actumin/').'/';
		$data['script']  ='<script language="javascript" type="text/javascript">
		function actumin(id,val){
			$.get("'.$url.'"+id+"/"+val, function(data) {
				alert(data);
			});
		}
		</script>';
		$data['content'] = $filter->output.$tabla;
		$data['title']   = "<h1>Comparativo de M&iacute;nimos de Inventario</h1>";
		$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").script("sinvmaes2.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

		function actumin($id,$exmin){
			//echo "$id,$exmin";
			$data['exmin']=$exmin;
			$mSQL = $this->db->update_string('sinv', $data, 'id='.$this->db->escape($id));
			if($this->db->simple_query($mSQL)==FALSE){
				echo 'Error actualzando';
			}
			echo 'Listo!!';
		}

	function instalar(){
		/*$mSQL='ALTER TABLE `sinv` DROP PRIMARY KEY';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE `sinv` ADD UNIQUE `codigo` (`codigo`)';
		$this->db->simple_query($mSQL);
		$mSQL='ALTER TABLE sinv ADD id INT AUTO_INCREMENT PRIMARY KEY';
		$this->db->simple_query($mSQL);*/
	}
}