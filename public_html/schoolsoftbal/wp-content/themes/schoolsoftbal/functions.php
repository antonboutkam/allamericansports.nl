<?php
/*
update_option('siteurl','http://schoolsoftbal.nl');
update_option('home','http://schoolsoftbal.nl');
*/

add_theme_support( 'post-thumbnails' ); 

function theme_name_scripts() {	
	wp_enqueue_style( 'stylesheet', get_stylesheet_uri() );
	wp_enqueue_style( 'wpcore', get_template_directory_uri() . '/wpcore.css');
	wp_enqueue_style( 'mediaqueries', get_template_directory_uri() . '/mediaqueries.css');
	wp_enqueue_script( 'backstretch', get_template_directory_uri() . '/js/jquery.backstretch.min.js', array( 'jquery' ), '1.0.0', false);
	wp_enqueue_script( 'custom', get_template_directory_uri() . '/js/custom.js', array( 'jquery' ), '1.0.0', false);
}

add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );

register_nav_menus( array(
	'hoofdmenu' => 'Hoofdmenu',
) );


function nieuws() {
    $args = array( 'public' => true, 'label' => 'Blog', 'supports' => array('title', 'editor') );
    register_post_type( 'nieuws', $args );
}
add_action( 'init', 'nieuws' );

function producten() {
    $args = array( 'public' => true, 'label' => 'Producten', 'supports' => array('title', 'editor') );
    register_post_type( 'producten', $args );
}
add_action( 'init', 'producten' );



if ( file_exists( dirname( __FILE__ ) . '/cmb/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB/init.php';
}


add_action( 'cmb2_admin_init', 'default_metaboxes' );
 
function default_metaboxes() {
	$prefix = '_cmb_';

	$cmb_demo = new_cmb2_box( array(
		'id'            => $prefix . 'meta',
		'title' => 'Velden',
		'object_types'  => array( 'page'), // Post type
	) );
	
	$cmb_demo->add_field( array(
		'name' => 'Groot assortiment',
	    'id' => $prefix . 'assortiment',
	    'type' => 'textarea',
	) );
	$cmb_demo->add_field( array(
		'name' => 'Prijs/kwaliteit',
	    'id' => $prefix . 'prijskwaliteit',
	    'type' => 'textarea',
	) );
	$cmb_demo->add_field( array(
		'name' => 'Aanbieding op maat',
	    'id' => $prefix . 'aanbiedingopmaat',
	    'type' => 'textarea',
	) );
	
	$cmb_demo->add_field( array(
		'name' => 'Onze merken',
	    'id' => $prefix . 'brands',
	    'type' => 'file',
	) );

	$cmb_demo->add_field( array(
		'name' => 'Aanbieding',
	    'id' => $prefix . 'aanbiedingimg',
	    'type' => 'file',
	) );
	$cmb_demo->add_field( array(
		'name' => 'Aanbieding text',
	    'id' => $prefix . 'aanbiedingtext',
	    'type' => 'textarea',
	) );
	$cmb_demo->add_field( array(
		'name' => 'Aanbieding prijs',
	    'id' => $prefix . 'aanbiedingprice',
	    'type' => 'text',
	) );

}


add_action( 'cmb2_admin_init', 'producten_metaboxes' );
 
function producten_metaboxes() {
	$prefix = '_cmb_';

	$cmb_demo = new_cmb2_box( array(
		'id'            => $prefix . 'producten',
		'title' => 'Velden',
		'object_types'  => array( 'producten'), // Post type
	) );
	
	$cmb_demo->add_field( array(
		'name' => 'Afbeelding',
	    'id' => $prefix . 'image',
	    'type' => 'file',
	) );

} 

add_action( 'cmb2_admin_init', 'overons_metaboxes' );

function overons_metaboxes() {
	$prefix = '_cmb_';

	$cmb_demo = new_cmb2_box( array(
		'id'            => $prefix . 'over-ons',
		'title' => 'Text',
		'object_types'  => array( 'page'), // Post type
	) );
	
	$cmb_demo->add_field( array(
		'name' => 'Text',
	    'id' => $prefix . 'text',
	    'type' => 'wysiwyg',
	) );

}


function slugify($text)
{ 
  // replace non letter or digits by -
  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

  // trim
  $text = trim($text, '-');

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // lowercase
  $text = strtolower($text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  if (empty($text))
  {
    return 'n-a';
  }

  return $text;
};
 

?>