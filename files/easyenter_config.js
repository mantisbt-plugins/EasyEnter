
/**
 * Visually emphasize passed "elem" if empty
 * @param elem
 */
function trigger_gray_out_if_empty( elem ) {
    if( elem.val( ).replace( /\s/g, '' ) == '' ) {
        elem.css( 'background-color', '#cecece' );
    } else {
        elem.css( 'background-color', '#ffffff' );
    }
}


/**
 * Gray out field_value-fields without content, add event listener to gray
 * out/whiten the appropriate fields on entering a value
 */
var fvalinp = jQuery( '#field_values_fields' ).find( 'input' )
fvalinp.each( function( ) {
    trigger_gray_out_if_empty( jQuery( this ) );
});
fvalinp.on( 'blur, keyup', function( ) {
    trigger_gray_out_if_empty( jQuery( this ) );
});


/**
 * Event handler for project dropdown select, reload entire form with
 * project_id-GET-parameter
 */
jQuery( '#project_id' ).on( 'change', function( ) {
    window.location = window.location.protocol + '//'
        + window.location.host
        + window.location.pathname
        + '?page=EasyEnter/config.php&project_id=' + jQuery( this ).val( );
});
