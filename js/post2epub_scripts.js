/**
 * 
 */
function p2epub_obten_datos() {
	var seleccionados     = new Array;
	var seleccionados_tag = new Array;
	var contador          = 0;
	var entradas          = new Array;
	jQuery('.p2epub_cont_listado').addClass('ui-widget-shadow');
	
	jQuery( '.ui-selected', '#p2epub_combo_cat' ).each(function() {
		seleccionados[contador]=jQuery(this).attr('id');
		contador++;
	});
	
	contador = 0; // Se reinicia el contador para la parte de etiquetas
	
	jQuery( ".ui-selected", '#p2epub_combo_tag' ).each(function() {
		seleccionados_tag[contador]=jQuery(this).attr('id');
		contador++;
	});
	
	contador = 0;
	jQuery('#posts_add_p2epub li','.p2epub_listado_agregar' ).each(function() {
		entradas[contador]=jQuery(this).attr('id');
		contador++;
	});
	
	var datos = 
	{
		action: 'obten_registro_post2epub_client',
		filtro_cat: seleccionados,
		filtro_tag: seleccionados_tag,
		filtro_ent: entradas
	};

	jQuery.post(ajaxurl, datos, function(response)
	{	
		jQuery("#posts_p2epub").html(response);
		
		jQuery( "#posts_p2epub, #posts_add_p2epub" ).sortable({
			connectWith: ".posts_p2epub",
			dropOnEmpty: true
		}).disableSelection();
		
		jQuery('.p2epub_cont_listado').removeClass('ui-widget-shadow');
	});
	
}

jQuery(document).ready( function() {	
	
	var formfield;

	jQuery('#btn_carga_portada').click(function() {
		formfield = jQuery('#carga_portada').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});

	window.send_to_editor = function(html) {
		
		imgurl = jQuery('img',html).attr('src');
		jQuery('#carga_portada').val(imgurl);
		tb_remove();
	}

	jQuery('.btn_eliminar').click(function() {
		
		var indice = jQuery(this).attr('fila');
		var datos = 
		{
			action: 'elimina_elemento_post2epub_client',
			archivo: jQuery(this).attr('elemento')
		};

		jQuery.post(ajaxurl, datos, function(respuesta)
		{
			jQuery('#'+indice).remove();
			jQuery( "#p2epub_mensaje" ).html(respuesta);
			jQuery( "#p2epub_mensaje" ).dialog(
			{
				modal: true,
				buttons:
				{
					Ok: function()
					{
						jQuery( this ).dialog( "close" );						
					}
				}
			});

		});
	});
			

	jQuery('#p2epub_combo_cat').selectable({stop: function() {
			p2epub_obten_datos();					
		}
	});
			
	jQuery('#p2epub_combo_tag').selectable({stop: function() {
			p2epub_obten_datos();					
		}
	});
			
	jQuery('.btn_genera_edicion').click(function() {
		
		var p2epub_posts = new Array();
		var contador     = 0;
		
		jQuery('#posts_add_p2epub li','.p2epub_listado_agregar' ).each(function() {
			p2epub_posts[contador]=jQuery(this).attr('id');
			contador++;
		});
		
		
		if(jQuery("#nombre_edicion").val().length > 0)
		{
			var datos =
			{
				action:            'genera_elemento_post2epub_client',
				nombre_edicion:    jQuery('#nombre_edicion').attr('value'),
				portada_edicion:   jQuery('#carga_portada').attr('value'),
				elementos_edicion: p2epub_posts,
				identificador:     jQuery('#p2epub_identificador').attr('value')
			};
					
			jQuery.post(ajaxurl, datos, function(response)
			{	
				
				jQuery( "#p2epub_mensaje" ).html(response);
				jQuery( "#p2epub_mensaje" ).dialog({
					modal: true,
					buttons:
					{
						Ok: function()
						{
							jQuery( this ).dialog( "close" );
						}
					}
				});
			});
		}
		
		else
		{
			jQuery('#etiqueta_nombre_edicion').css({ color: "#FF0000"})
		}
	});
	
});

 

