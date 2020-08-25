<?php get_header(); ?>   
	   
<?php 
    $catslug = get_post_type_object( $post_type )->rewrite['slug'];
    if (empty($catslug)){
	    $catslug = 'home';
    };
?>


<body class="<?php echo $catslug;?>">		
		  
<?php include_once('top.php'); ?>



<?php 
while ( have_posts() ) : the_post(); 
?>

<div class="container">
<div class="marge-calc"></div>	
	
	<div class="clear"></div>
	
</div>
<?php endwhile; // end of the loop. ?>
<?php wp_reset_query(); ?> 


<?php get_footer(); ?>    


<script>
	 $('.homeimg').backstretch("<?php echo get_template_directory_uri();?>/images/schoolsoftbal.jpg");
	 $('.groot-assortiment').backstretch("<?php echo get_template_directory_uri();?>/images/groot-assortiment.jpg");
	 $('.block-large').backstretch("<?php echo get_template_directory_uri();?>/images/austin-fasting.jpg");
</script>

</body>
</html>