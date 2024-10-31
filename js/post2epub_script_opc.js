jQuery(document).ready( function() {	
	
	jQuery('#btn_guardar_opc').click(function() {
		var datos = 
		{
			action: 'guarda_opc_post2epub_client',
			directorio: jQuery('.p2epub_directorio').attr('value'),
			campo: jQuery('.p2epub_campo').attr('value')
		};

		jQuery.post(ajaxurl, datos, function(respuesta)
		{
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
});

 

