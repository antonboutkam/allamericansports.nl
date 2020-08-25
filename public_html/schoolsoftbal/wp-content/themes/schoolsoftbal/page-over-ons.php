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
			<div class="content">
				<?php the_content();?>
			</div>
		</div>
		<div class="clear"></div>
	</div> 
	
	<div class="clear"></div>

	<a href="<?php echo get_page_link(10);?>">	
		<div class="block one-block groot-assortiment" style="margin-left: 16%; min-height:20em;">
			<div style="z-index:100;" class="block-header">Bekijk ons bedrijf</div>
			<iframe width="100%" height="100%" src="https://www.youtube.com/embed/_Azy8pZZ2Zk?rel=0&amp;controls=1&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>
		</div> 
	</a>
	
	<div class="over-ons-content">

	<?php 
		echo wpautop(get_post_meta($post->ID, "_cmb_text", true));
	?>

		
	</div>
	
	
	
	
	<div class="clear"></div>
	
</div>
<?php endwhile; // end of the loop. ?>
<?php wp_reset_query(); ?> 


<?php get_footer(); ?>    



</body>
</html>