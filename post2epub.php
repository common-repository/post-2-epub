<?php

/*
Plugin Name: Post2epub
Plugin URI: http://www.rinconalterno.com
Description: Convierte tu sitio en un  epub
Version: 0.0.1
Author: Eduardo Morales
Author URI: http://www.rinconalterno.com
License: 
	Copyright 2011  Abel Eduardo Morales López  (email : meduardo01@gmail.com)
	
	    This program is free software; you can redistribute it and/or modify
	    it under the terms of the GNU General Public License, version 2, as 
	    published by the Free Software Foundation.
	
	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.
	
	    You should have received a copy of the GNU General Public License
	    along with this program; if not, write to the Free Software
	    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

define('POST2EPUB_VERSION', '0.0.1');
define('POST2EPUB_DIR', dirname( plugin_basename( __FILE__ ) ));
define('POST2EPUB_URL',plugin_dir_url( __FILE__ ));
define('POST2EPUB_BLOGNAME',get_bloginfo('name'));
define('POST2EPUB_HOME',str_replace('/','\/',get_bloginfo('home')));

add_action('admin_menu', 'menu_post2epub');
add_action('admin_enqueue_scripts','carga_post2epub_scripts');
add_action('wp_ajax_obten_registro_post2epub_client','obten_registros_post2epub');
add_action('wp_ajax_elimina_elemento_post2epub_client','elimina_publicacion_post2epub');
add_action('wp_ajax_genera_elemento_post2epub_client','genera_publicacion_post2epub');
add_action('wp_ajax_guarda_opc_post2epub_client','guarda_opc_post2epub');

load_plugin_textdomain('post2epub',false, POST2EPUB_DIR.'/languages/' );


function menu_post2epub()
{	
	add_menu_page(  __( 'Post 2 epub','post2epub'),'Post2epub', 'manage_options', 'post2epub', 'forma_menu_post2epub' );
	add_submenu_page( 'post2epub', __( 'Crear','post2epub'),__( 'Crear','post2epub') , 'manage_options', 'post2epub_add', 'forma_agrega_post2epub' );
	add_submenu_page( 'post2epub', __( 'Opciones','post2epub'),__( 'Opciones','post2epub') , 'manage_options', 'post2epub_opc', 'forma_opciones_post2epub' );
}

function carga_post2epub_scripts($elemento)
{
	if($elemento == "toplevel_page_post2epub" or $elemento == "post2epub_page_post2epub_add")
	{
    	wp_enqueue_script  ( 'jquery' );
    	wp_register_style  ( 'post2epub-general-css' , POST2EPUB_URL.'css/post2epub_general_css.css');
    	wp_enqueue_style   ( 'post2epub-general-css' );
    	wp_register_style  ( 'post2epub-jquery-css' , POST2EPUB_URL.'css/jquery-ui-1.8.11.custom.css');
    	wp_enqueue_style   ( 'post2epub-jquery-css' );
    	wp_enqueue_script  ( 'media-upload' );
		wp_enqueue_script  ( 'thickbox' );
		wp_register_script ( 'post2epub_scripts' , POST2EPUB_URL.'js/post2epub_scripts.js', array('jquery','jquery-ui-core','jquery-ui-selectable','jquery-ui-dialog','jquery-ui-sortable','media-upload','thickbox'), '1.0');
		wp_enqueue_script  ( 'post2epub_scripts' );
		wp_enqueue_style   ( 'thickbox' );
	}
	elseif ($elemento == "post2epub_page_post2epub_opc")
	{
		wp_register_style  ( 'post2epub-general-css' , POST2EPUB_URL.'css/post2epub_general_css.css');
    	wp_enqueue_style   ( 'post2epub-general-css' );
		wp_register_style  ( 'post2epub-jquery-css' , POST2EPUB_URL.'css/jquery-ui-1.8.11.custom.css');
    	wp_enqueue_style   ( 'post2epub-jquery-css' );
    	wp_register_script ( 'post2epub_script_opc' , POST2EPUB_URL.'js/post2epub_script_opc.js', array('jquery','jquery-ui-core','jquery-ui-selectable','jquery-ui-dialog','jquery-ui-sortable','media-upload','thickbox'), '1.0');
		wp_enqueue_script  ( 'post2epub_script_opc' );
	}
}

function forma_menu_post2epub()
{
	if(current_user_can('manage_options')===true)
	{
		$carpeta = (!get_option('post2epub_campo_directorio'))? 'post2epub' : get_option('post2epub_campo_directorio');
		$ruta    = WP_CONTENT_DIR.'/uploads/'.$carpeta;
		if(!file_exists($ruta))
		{
			mkdir($ruta);
		}
		
		$dir  = dir($ruta);
		$html = '<div class="p2epub_div" id="p2epub_div">
			<h2>'. __( 'Post 2 epub','post2epub').'</h2>
			<table class="ui-widget-content ui-corner-all p2epub_listado">
  				<thead><tr>
			    	<th scope="col">'.__( 'Nombre de la publicaci&oacute;n','post2epub').'</th>
			    	<th scope="col">'.__('Fecha de creaci&oacute;n','post2epub').'</th>
			    	<th scope="col">'.__('Ruta de acceso','post2epub').'</th>
			    	<th scope="col"> '.__('Opciones','post2epub').'</th>
  				</tr></thead>';
  		
		$contador = 1;		
		
		while ($elemento = $dir->read())
		{
		   if($elemento !='.' and $elemento != '..')
		   {
		   		$html .= '
		   			<tr id="indice_'.$contador.'">
		   				<td>'.$elemento.'</td>
		   				<td>'.date("F-d-Y.", filectime($ruta.'/'.$elemento)).'</td>
		   				<td>'.WP_CONTENT_URL.'/uploads/'.$carpeta.'/'.$elemento.'</td>
		   				<td><input type="button" value='.__('Eliminar','post2epub').' elemento="'.$elemento.'"  fila="indice_'.$contador.'" class="btn_eliminar button"></td>
		   			</tr>';
		   		
		   		$contador ++; 
		   }
		}
		
		$dir->close();
		
		$html .= '</table></div><div id="p2epub_mensaje"></div>';
		echo $html;
	}
	
	else 
	{
		echo __('No tienes privilegios suficientes','post2epub');
	}
}

function elimina_publicacion_post2epub()
{
	if(current_user_can('manage_options')===true)
	{
		$carpeta        = (!get_option('post2epub_campo_directorio'))? 'post2epub' : get_option('post2epub_campo_directorio');
		$ruta           = WP_CONTENT_DIR.'/uploads/'.$carpeta;
		$nombre_archivo = $ruta.'/'.$_POST['archivo'];
		$estado_archivo	= unlink($nombre_archivo);
		
		if($estado_archivo == true)
		{
			echo __('El archivo se borro correctamente','post2epub');
			exit;
		}
		else
		{
			echo __('Error: El archivo especificado no se pudo borrar','post2epub');
		}
	}
	
	else 
	{
		echo __('No tienes privilegios suficientes','post2epub');
	}
}

function forma_agrega_post2epub()
{
	if(current_user_can('manage_options')===true)
	{
		$args = array(
		    'type'       => 'post',
		    'orderby'    => 'name',
		    'order'      => 'ASC',
		    'hide_empty' => 1,
		    'taxonomy'   => 'category',
		    'pad_counts' => false );
		
		$categorias    = get_categories($args);
		$list_opciones = '';
		
		if($categorias)
		{
			foreach ($categorias as $categoria)
			{
				$list_opciones .= '<li class="seleccionable ui-corner-all" id="'.$categoria->slug.'">'.$categoria->cat_name.'</li>';
			}
			$list_opciones ='
				<ul id="p2epub_combo_cat">'
					.$list_opciones.'</ul>';
		}
		
		$opt_categorias = '
			<div class="p2epub_cont_cat ui-state-default ui-corner-all p2epub_cont_listado">
				<div class="p2epub_leyenda ui-corner-all" title="'.__('Selecciona una categor&iacute;a, manten tecla Ctrl presionada para seleccionar varias categor&iacute;as','post2epub').'">
    				'.__('Categor&iacute;a','post2epub').'
    			</div><br>
				'.$list_opciones.'
			</div>';
		
		$tags = get_tags();
		
		if ($tags)
		{
  			foreach($tags as $tag)
  			{
    			$opt_tags .= '<li class="seleccionable ui-corner-all" id="'.$tag->slug.'">'.$tag->name.'</li>';
    		}
    		
    		$opt_tags =
    			'<ul id="p2epub_combo_tag">
    				'.$opt_tags.'
    			</ul>';
    	}
    	
    	$opt_tags = '
    		<div class="p2epub_cont_tag ui-state-default ui-corner-all p2epub_cont_listado">
    			<div class="p2epub_leyenda ui-corner-all" title="'.__('Selecciona una etiqueta, manten tecla Ctrl presionada para seleccionar varias etiquetas','post2epub').'">
    				'.__('Etiqueta','post2epub').'
    			</div><br>
    			'.$opt_tags.'
    		</div>';
		
		$html = '
			<div class="p2epub_contenedor" id="post2epub_contenedor">
				<h2>'.__('Crear Archivo','post2epub').'</h2>
				<div class="p2epub_seccion1 ui-widget-content ui-corner-all">
					<div class="ui-widget-header ui-corner-all p2epub_leyenda" style="clear:both; width:580px">
					'.__('Filtro de entradas','post2epub').'</div>
					'.$opt_categorias.'
					'.$opt_tags.'
				<br><div class="ui-widget-header ui-corner-all p2epub_leyenda" style="clear:both; width:580px">
					'.__('Listado de entradas','post2epub').'</div>
			<div id="cont_posts_p2epub" class="p2epub_cont_posts">
				<div class="p2epub_listado ui-state-default ui-corner-all p2epub_cont_listado">
					<div class="p2epub_leyenda ui-corner-all" title="'.__('Aqu&iacute; se muestra las entradas filtradas por categor&iacute;(s) y etiqueta(s)','post2epub').'">
	    				'.__('Entradas','post2epub').'
	    			</div><br>
					<ul class="posts_p2epub ui-corner-all" id="posts_p2epub"></ul>
				</div>
				<div class="p2epub_listado_agregar ui-state-default ui-corner-all p2epub_cont_listado" title="'.__('Listado de entradas que conformar&aacute;n el archivo','post2epub').'">
					<div class="p2epub_leyenda ui-corner-all">
    					'.__('Entradas Post 2 epub','post2epub').'
    				</div><br>	
					<ul class="posts_p2epub ui-corner-all" id="posts_add_p2epub"></ul>
				</div>
			</div>
			</div>';
		
		$html .= '
			<div class="p2epub_seccion2 ui-widget-content ui-corner-all" id="p2epub_seccion2">
				<div class="ui-widget-header ui-corner-all p2epub_leyenda" style="clear:both; width:330px">
					'.__('Datos de la edici&oacute;n','post2epub').'
				</div>
				<form id="p2epu_forma_agrega" class="p2epub_forma_agrega">
					<table>
						<tr>
						
							<td>*<label title="'.__('Campo obligatorio','post2epub').'" for="nombre_edicion" id="etiqueta_nombre_edicion">'.__('Nombre del archivo','post2epub').'</label></td>
							<td colspan="2" ><input type="text" class="p2epub_input_nombre" id="nombre_edicion"></td>
						</tr>
						<tr>
							<td><label for="carga_portada">'.__('Portada','post2epub').'</label></td>
							<td><input id="carga_portada" type="text" name="carga_portada" value="" /></td>
							<td><p class="seleccionable ui-state-default ui-corner-all" id="btn_carga_portada"><span class="ui-icon ui-icon-image" alt="'.__('Agregar imagen','post2epub').'"></span></p></td>
						</tr>
						<tr>
							<td><label for="identificador" title="'.__('Parametro opcional (por ejemplo: ISBN)','post2epub').'">'.__('Identificador &uacute;nico','post2epub').'</label></td>
							<td colspan="2"><input type="text" value="" id="p2epub_identificador"></td>
						</tr>
						<tr>
							<td colspan="3"><input type="button" class="btn_genera_edicion button" value="'.__('Generar edici&oacute;n','post2epub').'"></td>
						</tr>
					</table>
				</form> 
			</div>
		</div><div id="p2epub_mensaje" title="'.__('Mensaje','post2epub').'"></div>';
		echo $html;
	}
	
	else 
	{
		echo __('No tienes privilegios suficientes','post2epub');
	}
}

function obten_registros_post2epub()
{
	if(current_user_can('manage_options')===true)
	{
		$entradas   = array();
		$categorias = '';
		$tags       = '';
		
		if(!empty($_POST['filtro_cat']))
		{
			$total_categorias = count($_POST['filtro_cat'])-1;
	
			for ($indice = 0;$indice<=$total_categorias;$indice++)
			{
				$categorias .= ($indice==$total_categorias)? $_POST['filtro_cat'][$indice] : $_POST['filtro_cat'][$indice].',';
			}
		}
		
		if(!empty($_POST['filtro_tag']))
		{
			$total_tags = count($_POST['filtro_tag'])-1;
			
			for ($indice = 0;$indice<=$total_tags;$indice++)
			{
				$tags .= ($indice == $total_tags)? $_POST['filtro_tag'][$indice] : $_POST['filtro_tag'][$indice].',';
			}
		}
		
		if(!empty($_POST['filtro_ent']))
		{
			$total_ent = count($_POST['filtro_ent'])-1;
			
			for ($indice = 0;$indice<=$total_ent;$indice++)
			{
				$entradas[]= $_POST['filtro_ent'][$indice];
			}
		}
		
		$args = array(
			'post__not_in' => $entradas,
			'category_name' => $categorias,
			'tag' => $tags
		);
		
		$posts = query_posts($args);
		$html  = '';
		
		wp_reset_query();
		
		foreach ($posts as $post)
		{
			$html .= '<li class="seleccionable ui-corner-all ui-state-highlight" id="'.$post->ID.'">'.$post->post_title.'</li>';
		}
		
		echo $html;
		exit;
	}
	
	else 
	{
		echo __('No tienes privilegios suficientes','post2epub');
		exit;
	}
}

function genera_publicacion_post2epub()
{
	if(current_user_can('manage_options')===true)
	{
		
		$filtro_elementos   = array();
		if(!empty($_POST['elementos_edicion']))
		{
			$total_elementos  = count($_POST['elementos_edicion'])-1;
			$cont_nav         = 2;
			$orden_entrada = array();
			for ($indice=0;$indice<=$total_elementos;$indice++)
			{
				$filtro_elementos[] = ($indice==$total_elementos)?$_POST['elementos_edicion'][$indice]:$_POST['elementos_edicion'][$indice].',';
				$orden_entrada[$_POST['elementos_edicion'][$indice]]  = $cont_nav;
				$cont_nav++;
			}
			
			$args = array(
				'post__in' => $filtro_elementos
			);
			
			$posts = query_posts($args);
	
			if(count($posts > 0))
			{
				$carpeta  = (!get_option('post2epub_campo_directorio'))? 'post2epub' : get_option('post2epub_campo_directorio');// Anotacion hacer la variable ruta global
				$ruta     = WP_CONTENT_DIR.'/uploads/'.$carpeta.'/'.$_POST['nombre_edicion'];
				if(!file_exists(WP_CONTENT_DIR.'/uploads/'.$carpeta))
				{
					mkdir(WP_CONTENT_DIR.'/uploads/'.$carpeta);
				}
				$zip = new ZipArchive;
				$res = $zip->open($ruta.'.zip', ZipArchive::CREATE);
				if ($res == true)
				{
					$portada_nombre = '';
					$tipo_portada   = '';
					
					if (!empty($_POST['portada_edicion']))
					{
						$portada_url    = explode (WP_CONTENT_URL,$_POST['portada_edicion']);
						$formato_img    = explode ('.',$portada_url[1]);
						$zip->addFile(WP_CONTENT_DIR.$portada_url[1],'OEBPS/cover.'.$formato_img[1]);
						
						$portada_nombre = 'cover.'.$formato_img[1];
						$tipo_portada   = $formato_img[1];
					}
					
					else 
					{
						$zip->addFile(WP_CONTENT_DIR.'/plugins/post-2-epub/plantilla/cover.jpg','OEBPS/cover.jpg');
						
						$portada_nombre = 'cover.jpg';
						$tipo_portada   = 'jpg';
					}
					
					$identificador = (!empty($_POST['identificador']))?'urn:uuid:'.$_POST['identificador']:'urn:uuid:'.POST2EPUB_BLOGNAME.date('Ymd');		
					
					$zip->addFromString('mimetype',$contenido_mimetype);
					
					$container = '<?xml version="1.0"?>
							<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
			  					<rootfiles>
			    					<rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/>
			  					</rootfiles>
							</container>';
					$zip->addFromString('META-INF/container.xml',$container);
					
					$contador_entrada    = 1;
					$campo_personalizado = get_option('post2epub_campo_video');
					
					if(empty($campo_personalizado))
					{
						$campo_personalizado = 'p2e_video';
					}
					
					foreach ($posts as $post)
					{
						$contenido_html =
							'<?xml version="1.0" encoding="utf-8"?>
							<html xmlns="http://www.w3.org/1999/xhtml">
		  					<head>
								<title>'.$post->post_title.'</title>
								<meta name="generator" content="pdftohtml 0.36"/>
		    					<meta content="http://www.w3.org/1999/xhtml; charset=utf-8" http-equiv="Content-Type"/>
							</head>
							<body>
								<h1>'.$post->post_title.'</h1>
								<p style="text-align: justify">
									'.obten_formato_post2epub($post->post_content,$_POST['nombre_edicion'],$contador_entrada,$zip).
								'</p>';
						
						$videos = get_post_meta($post->ID,$campo_personalizado);
						
						if(count($videos>0))
						{
							$contador_video = 0;
							foreach ($videos as $video)
							{
								$ruta_archivo = explode(WP_CONTENT_URL,$video);
								$formato      = explode('.',$ruta_archivo[1]);
								
								$zip->addFile(WP_CONTENT_DIR.'/'.$ruta_archivo[1],'OEBPS/video_'.$contador_video.'.'.$formato[1]);			
								
								$contenido_html .= '
									<video src="video_'.$contador_video.'.'.$formato[1].'" controls="true" width="320" height="240" autoplay="false"> 
					  					'. __( 'Tu lector no puede reproducir video','post2epub').'
				    				</video>';
								$contador_video++;
							}
						}
						
						$contenido_html .=
								'</body>
							</html>';
						
						$nav_points =
							'<navPoint id="'.$post->post_name.'" playOrder="'.$orden_entrada[$post->ID].'">
					     		<navLabel>
						        	<text>'.$post->post_title.'</text>
						     	</navLabel>
					      		<content src="'.$post->post_name.'.xhtml"/>
				    		</navPoint>';
						$orden_entrada[$post->ID]=$nav_points;
							
						$items    .= '<item id="'.$post->post_name.'" href="'.$post->post_name.'.xhtml" media-type="application/xhtml+xml" />';
						$item_ref .= '<itemref idref="'.$post->post_name.'" />';
						$zip->addFromString('OEBPS/'.$post->post_name.'.xhtml',$contenido_html);
								
						$contador_entrada++;
					}		
					
					$toc =
						'<?xml version="1.0" encoding="UTF-8"?>
						<!DOCTYPE ncx PUBLIC "-//NISO//DTD ncx 2005-1//EN"  "http://www.daisy.org/z3986/2005/ncx-2005-1.dtd">
						<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1">
					  		<head>
							    <meta name="dtb:uid" content="'.$identificador.'"/>
							    <meta name="dtb:depth" content="1"/>
							    <meta name="dtb:totalPageCount" content="0"/>
							    <meta name="dtb:maxPageNumber" content="0"/>
					 	 	</head>
						  	<docTitle>
						    	<text>'.$_POST['nombre_edicion'].'</text>
						  	</docTitle>
						  	<navMap>
						  		<navPoint id="navPoint-1" playOrder="1">
						            <navLabel>
						                <text>'.__('Portada','post2epub').'</text>
						            </navLabel>
					            	<content src="cover.xhtml"/>
					        	</navPoint>';
					foreach ($orden_entrada as $contenido_entrada)
					{
						$toc .= $contenido_entrada;
					}
					$toc.='</navMap>
						</ncx>';
					
					$zip->addFromString('OEBPS/toc.ncx',$toc);
					$nombre = (!get_option('post2epub_publisher'))?POST2EPUB_BLOGNAME:get_option('post2epub_publisher');
		
					$contenido_opf =
						'<?xml version="1.0" encoding="UTF-8"?>
						<package xmlns="http://www.idpf.org/2007/opf" unique-identifier="BookId" version="2.0">
							<metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:opf="http://www.idpf.org/2007/opf">
							 	<dc:title>'.$_POST['nombre_edicion'].'</dc:title> 
								<dc:creator opf:role="aut">'.$nombre.'</dc:creator>
								<dc:language>'.get_bloginfo('language').'</dc:language> 
								<dc:rights>Public Domain</dc:rights> 
								<dc:publisher>'.$nombre.'</dc:publisher>
								<meta name="cover" content="'.$portada_nombre.'"/>
							</metadata>
							<manifest>
								<item id="cover.xhtml" href="cover.xhtml" media-type="application/xhtml+xml"></item>
								<item id="ncx" href="toc.ncx" media-type="application/x-dtbncx+xml"/>
								<item id="'.$portada_nombre.'" href="'.$portada_nombre.'" media-type="image/'.$tipo_portada.'"/>
								'.$items.'
							</manifest>
							<spine toc="ncx">
								<itemref idref="cover.xhtml"/>
								'.$item_ref.'
							</spine>
							<guide>
					        	<reference type="cover" title="Cover" href="cover.xhtml"/>
					    	</guide>
						</package>';
					
					$zip->addFromString('OEBPS/content.opf',$contenido_opf);
					$zip->addFile(WP_CONTENT_DIR.'/plugins/post-2-epub/plantilla/cover.xhtml','OEBPS/cover.xhtml');
					$zip->close();
					rename($ruta.'.zip',$ruta.'.epub');
		    		echo __('El archivo se ha generado correctamente','post2epub');
		    		exit;
				}
				else 
				{
					echo __('Error: El archivo no pudo ser creado asegurate de tener permisos de lectura y escritura en la carpeta ','post2epub').'<strong>'.$carpeta.'</strong>';
					exit;
				}
			}
	
			else 
			{
				echo __('Error: No hay registros correspondientes con los datos seleccionados','post2epub');
				exit;
			}
		}
		else 
		{
			echo __('Error: La secci&oacute;n de entradas no contiene registros','post2epub');
			exit;
		}
	}
	
	else 
	{
		echo __('No tienes privilegios suficientes','post2epub');
		exit;
	}
}

function obten_formato_post2epub($contenido,$nombre_edicion,$contador_entrada,$zip)
{
	if(current_user_can('manage_options')===true)
	{
		preg_match_all('/src="'.POST2EPUB_HOME.'\/wp-content(.*?)"/is', $contenido, $coincidencias, PREG_SET_ORDER);
		
		$carpeta = (!get_option('post2epub_campo_directorio'))? 'post2epub' : get_option('post2epub_campo_directorio');// Anotacion hacer la variable ruta global
		$ruta = WP_CONTENT_DIR.'/uploads/'.$carpeta.'/'.$nombre_edicion;
		$contador_imagen_entrada=0;
		foreach($coincidencias as $coincidencia)
		{
			$nombre_imagen = 'imagen_'.$contador_entrada.'_'.$contador_imagen_entrada.'.jpg';
			$zip->addFile(WP_CONTENT_DIR.'/'.$coincidencia[1],'OEBPS/'.$nombre_imagen);		
			
			$contenido = strip_tags(str_replace($coincidencia[0],'src="'.$nombre_imagen.'"',$contenido),'<a><img><ul><li><strong>');
			$contador_imagen_entrada ++;
		}
		return $contenido;
	}
	
	else 
	{
		echo __('No tienes privilegios suficientes','post2epub');
		exit;
	}
}

function forma_opciones_post2epub()
{
	if(current_user_can('manage_options')===true)
	{
		$directorio = get_option('post2epub_campo_directorio');
		$campo = get_option('post2epub_campo_video');
		$html = 
		'<h2>'.__('Opciones','post2epub').'</h2>
		<div class="p2epub_seccion1 ui-widget-content ui-corner-all">
			<table>
				<tr>
					<td>
						<span title="'.__('Nombre del directorio en donde se guardaran las ediciones creadas','post2epub').'">
							 '.__('Directorio de ediciones','post2epub').'
						</span>
					</td>
					<td> <input type="text" class="p2epub_directorio" value="'.$directorio.'"> </td>
				</tr>
				<tr>
					<td>
						<span title="'.__('Compatibilidad de videos solo en iBooks','post2epub').'">
							'.__('Campo personalizado para videos','post2epub').'
						</span>
					</td>
					<td> <input type="text" class="p2epub_campo" value="'.$campo.'"> </td>
				</tr>
				<tr>
					<td colspan="2"><input type="button" id="btn_guardar_opc" value="'.__('Guardar','post2epub').'"></td>
			</table>
		</div><div id="p2epub_mensaje"></div>';
		
		echo $html;
	}
	
	else 
	{
		echo __('No tienes privilegios suficientes','post2epub');
	}
}

function guarda_opc_post2epub()
{
	if(current_user_can('manage_options')===true)
	{
		if ( get_option('post2epub_campo_directorio'))
	 	{
	    	update_option('post2epub_campo_directorio', $_POST['directorio']);
		} 
	
		else 
		{
			add_option('post2epub_campo_directorio', $_POST['directorio']);
		}
		
		if ( get_option('post2epub_campo_video'))
	 	{
	    	update_option('post2epub_campo_video', $_POST['campo']);
		} 
	
		else 
		{
			add_option('post2epub_campo_video', $_POST['campo']);
		}
		
		echo __('Los cambios se han guardado correctamente','post2epub');
		exit;
	}
	
	else 
	{
		echo __('No tienes privilegios suficientes','post2epub');
		exit;
	}
}
?>