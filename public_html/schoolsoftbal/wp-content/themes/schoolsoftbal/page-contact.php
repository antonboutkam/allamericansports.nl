<?php get_header(); ?>   
	   
<?php 
    $catslug = slugify(get_the_title());
?>


<body class="<?php echo $catslug;?>">		
		  
<?php include_once('top.php'); ?>



<?php 
while ( have_posts() ) : the_post(); 
?>

<div class="container">
<div class="marge-calc"></div>	
	<div class="block block-three">
		<div class="split backstretch">
		</div>
		<div class="split gradiant">
			<div class="content" style="padding:1em 5em;">
				<?php the_content();?>
			</div>
		</div>
	</div> 
	
	<div class="clear"></div>

	
	<div class="block-three maps">
		<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2474.2484929327256!2d5.0770674161662726!3d51.67359440635843!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47c693ec03bf4e7b%3A0xea3bd91da932e683!2sAntony+van+Dijckstraat+15%2C+5143+JB+Waalwijk%2C+Nederland!5e0!3m2!1snl!2sus!4v1468570915529" width="100%" height="100%" frameborder="0" style="border:0" allowfullscreen></iframe>		
	</div>
	
	<br/>
	
	
	<div class="clear"></div>
	
</div>
<?php endwhile; // end of the loop. ?>
<?php wp_reset_query(); ?> 


<?php get_footer(); ?>    


</body>
</html>