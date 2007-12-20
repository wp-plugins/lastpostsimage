<?php
//-----------------------------------------------------------------------------
/*
Plugin Name: Last Posts Image
Version: 0.1
Plugin URI: http://www.rene-ade.de/inhalte/wordpress-plugin-lastpostsimage.html
Description: This WordPress plugin provides a image always showing the last posts of your blog to use it as signature in communities for example. Go to LastPostsImage in the manage-menu of your adminpanel to customize the picture and get your public link. 
Author: Ren&eacute; Ade
Author URI: http://www.rene-ade.de
*/
//-----------------------------------------------------------------------------
?>
<?php

//-----------------------------------------------------------------------------

// replace entities
function lpi_replace_entities_entity( $entity ) {
  return chr( $entity[1] );
}
function lpi_replace_entities( $string ) {
  $string = html_entity_decode( $string );
  return preg_replace_callback( 
    '/&#([0-9]*);/', 
    'lpi_replace_entities_entity', 
    $string );  
}

//-----------------------------------------------------------------------------

// formatstring (replace placeholders)
function lpi_formatstring( $string, $post ) {
  $string = str_replace( '%title%',
    lpi_replace_entities(get_the_title($post->ID)), $string );  
  $string = str_replace( '%year%',
    date('Y',strtotime($post->post_date)), $string );
  $string = str_replace( '%monthnum%',
    date('m',strtotime($post->post_date)), $string );
  $string = str_replace( '%day%',
    date('d',strtotime($post->post_date)), $string );
  $string = str_replace( '%hour%',
    date('H',strtotime($post->post_date)), $string );
  $string = str_replace( '%minute%',
    date('i',strtotime($post->post_date)), $string );
  $string = str_replace( '%second%',
    date('s',strtotime($post->post_date)), $string );

  return $string;
}

//-----------------------------------------------------------------------------

// get color array by string
function lpi_getcolor( $string ) {
  if( strlen($string)!=6 )
    return( null );
  return array(
    'R' => hexdec(substr($string,0,2)),
    'G' => hexdec(substr($string,2,2)),
    'B' => hexdec(substr($string,4,2))
  );
}    

//-----------------------------------------------------------------------------

// create the image
function lpi_image() {
  
  // params
  $width = 468;
  if( isset($_GET['width']) )
    $width = (int) $_GET['width'];
  $height = 54;
  if( isset($_GET['height']) )
    $height = (int) $_GET['height'];
  $fontsize = 3;
  if( isset($_GET['fontsize']) )
    $fontsize = (int) $_GET['fontsize'];
  $numberposts = 4;
  if( isset($_GET['numberposts']) )
    $numberposts = (int) $_GET['numberposts'];
  $formatstring = '%title%';
  if( isset($_GET['formatstring']) )
    $formatstring = $_GET['formatstring'];
  $backgroundcolor = array( 'R'=>255, 'G'=>255, 'B'=>255 );
  if( isset($_GET['backgroundcolor']) && lpi_getcolor($_GET['backgroundcolor']) )
    $backgroundcolor = lpi_getcolor( $_GET['backgroundcolor'] );
  $textcolor = array( 'R'=>0, 'G'=>0, 'B'=>0 );
  if( isset($_GET['textcolor']) && lpi_getcolor($_GET['textcolor']) )
    $textcolor = lpi_getcolor( $_GET['textcolor'] );
    
  // get posts args
  $args = array(
    'numberposts' => $numberposts,
	);   
  // get posts
  $posts = get_posts( $args );
  
  // get text
  $text = array();
  foreach( $posts as $post ) {
    $text[] = lpi_formatstring( $formatstring, $post );
  }
  
  // create image
  $image = imagecreate( $width, $height );
  // get colors
  $backgroundcolor = imagecolorallocate( $image, 
    $backgroundcolor['R'], $backgroundcolor['G'], $backgroundcolor['B'] ); 
  $textcolor = imagecolorallocate( $image, 
    $textcolor['R'], $textcolor['G'], $textcolor['B'] );
  // get font size
  $fontwidth  = imagefontwidth ( $fontsize );
  $fontheight = imagefontheight( $fontsize );
  // write text
  $line = 0;
  foreach( $text as $textline ) {
    imagestring( $image, $fontsize, 0, $line*$fontheight, $textline, $textcolor );
    $line ++;
  }
  
  // output as jpeg
  header('content-type: image/jpeg');
  imagejpeg( $image );
}

//-----------------------------------------------------------------------------

// menu (configuration, get link)
function lpi_submenupage() {

  echo '<div class="wrap">';
	echo '<h2>LastPostImage</h2>';
  
  // configuration
  $path = '/wp-content/plugins/last-posts-image/last-posts-image.php'; ###TODO### get path dynamicly
  echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">';
  echo   '<lable>'._e('Path').' '.'<input type="text" name="lpi-path" value="'
         .(isset($_POST['lpi-submit'])?$_POST['lpi-path']:(get_option('home').$path)).'">'.'</lable>'.'<br>';
  echo   '<lable>'._e('Width').' '.'<input type="text" name="lpi-width" value="'
         .(isset($_POST['lpi-submit'])?$_POST['lpi-width']:'468').'">'.'</lable>'.'<br>';
  echo   '<lable>'._e('Height').' '.'<input type="text" name="lpi-height" value="'
         .(isset($_POST['lpi-submit'])?$_POST['lpi-height']:'54').'">'.'</lable>'.'<br>';  
  echo   '<lable>'._e('Fontsize').' '.'<input type="text" name="lpi-fontsize" value="'
         .(isset($_POST['lpi-submit'])?$_POST['lpi-fontsize']:'3').'">'.'</lable>'.'<br>';  
  echo   '<lable>'._e('Numberposts').' '.'<input type="text" name="lpi-numberposts" value="'
         .(isset($_POST['lpi-submit'])?$_POST['lpi-numberposts']:'4').'">'.'</lable>'.'<br>';  
  echo   '<lable>'._e('Backgroundcolor').' '.'<input type="text" name="lpi-backgroundcolor" value="'
         .(isset($_POST['lpi-submit'])?$_POST['lpi-backgroundcolor']:'FFFFFF').'">'.'</lable>'.'<br>';  
  echo   '<lable>'._e('Textcolor').' '.'<input type="text" name="lpi-textcolor" value="'
         .(isset($_POST['lpi-submit'])?$_POST['lpi-textcolor']:'000000').'">'.'</lable>'.'<br>';    
  echo   '<lable>'._e('Formatstring').' '.'<input type="text" name="lpi-formatstring" value="'
         .(isset($_POST['lpi-submit'])?$_POST['lpi-formatstring']:'- %title%').'">'.'</lable>'
         .' Placeholders: %title%, %year%, %monthnum%, %day%, %hour%, %minute%, %second%<br>';    
  $addurl = isset($_POST['lpi-submit']) ? isset($_POST['lpi-addurl']) : true;
  echo   '<lable>'._e('Add Link').' '.'<input type="checkbox" name="lpi-addurl" '.($addurl?'checked':'').'>'.'</lable>'.'<br>';
  echo   '<input type="submit" name="lpi-submit">'.'<br>';
  echo '</form>';

  // get link
  if( isset($_POST['lpi-submit']) ) {
  
    // create url
    $home = get_option('home');
    $image = $_POST['lpi-path']
             .'?lastpostsimage'
             .'&width='.(int)$_POST['lpi-width']
             .'&height='.(int)$_POST['lpi-height']
             .'&fontsize='.(int)$_POST['lpi-fontsize']
             .'&numberposts='.(int)$_POST['lpi-numberposts']
             .'&formatstring='.urlencode($_POST['lpi-formatstring'])
             .'&backgroundcolor='.$_POST['lpi-backgroundcolor']
             .'&textcolor='.$_POST['lpi-textcolor'];                     
  
    // display image  
    echo '<br>';
    echo _e('Image').'<br>'.'<img src="'.$image.'">'.'<br>';                     
    
    // image url
    echo '<br>';
    echo '<lable>'._e('Path').'<br>'.'<textarea rows="5" cols="50">'.$image.'</textarea>'.'</lable>'.'<br>';
    
    // html- and bb-code
    echo '<br>';
    $code = '<img src="'.$image.'" border="0" alt="'.$home.'">';
    if( $_POST['lpi-addurl'] )
      $code = '<a href="'.$home.'" target="_blank">'.$code.'</a>'; 
    echo '<lable>'._e('HTML').'<br>'.'<textarea rows="5" cols="50">'.$code.'</textarea>'.'</lable>'.'<br>';
    $code = '[IMG]'.$image.'[/IMG]';
    if( $_POST['lpi-addurl'] )
      $code = '[ULR='.$home.']'.$code.'[/URL]';
    echo '<lable>'._e('BB-Code').'<br>'.'<textarea rows="5" cols="50">'.$code.'</textarea>'.'</lable>'.'<br>';
  }
  
  echo '</div>';

  // advertisement
  echo '<div class="wrap">';
  echo 'Plugin Website: '
       .'<a href="http://www.rene-ade.de/inhalte/wordpress-plugin-lastpostsimage.html" target="_blank">'
       .'http://www.rene-ade.de/inhalte/wordpress-plugin-lastpostsimage.html'
       .'</a> '
       .'(Comments are welcome)'
       .'<br>';
  echo '<br>';       
  echo 'Donations: '
       .'<a href="http://www.rene-ade.de/stichwoerter/spenden" target="_blank">'
       .'http://www.rene-ade.de/stichwoerter/spenden'
       .'</a> '
       .'(Amazone-Wishlist, Paypal, ...)'
       .'<br>';
  echo '<br>';
  echo 'More dynamic generated pictures: '
       .'<a href="http://www.picgen.net/" target="_blank">'
       .'http://www.picgen.net'
       .'</a> '
       .'(Countdown-Pictures, Animated Text-Pictures, Visitors IP-Adress, Barcodes, ...)'
       .'<br>';       
  echo '</div>';    
}

// add menu
function lpi_adminmenu() {
	add_submenu_page( 'edit.php', 'LastPostImage', 'LastPostImage', 1, 'last-posts-image', 'lpi_submenupage' );
}

//-----------------------------------------------------------------------------

// activation
function lpi_activate() {
  // register option and set active
  add_option( 'lpi', array('active'=>true) );
}
// deactivation
function lpi_deactivate() {
  // unregister option
  delete_option('lpi'); 
}

//-----------------------------------------------------------------------------

// create image
if( isset($_GET['lastpostsimage']) ) {

  // load wordpress
  require_once( '../../../wp-config.php' ); 

  // check if plugin is active
  $options = get_option( 'lpi' ); 
  if( $options && $options['active'] ) {
    // create and output image
    lpi_image();
  }
}
// add actions
else {
  // activation and deactivation
  add_action( 'activate_'.plugin_basename(__FILE__),   'lpi_activate' );
  add_action( 'deactivate_'.plugin_basename(__FILE__), 'lpi_deactivate' );
  // admin menu
  add_action( 'admin_menu', 'lpi_adminmenu' );
}

//-----------------------------------------------------------------------------

?>