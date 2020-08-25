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
	<div class="block block-large">
		<div class="content readfix">
			<?php the_content();?>
		</div>
	</div> 
	
	<div class="block one-block groot-assortiment">
		<div class="block-header">Groot assortiment</div>
		<div class="content readfix"><?php echo get_post_meta( $post->ID,  '_cmb_assortiment', 1 ); ?></div> 
	</div> 
	
	<div class="block one-block prijs-kwaliteit">
		<div class="content">
		<b>Beste prijs/kwaliteit</b><br/><br/>
		<?php echo get_post_meta( $post->ID,  '_cmb_prijskwaliteit', 1 ); ?> 
<a href="<?php echo get_page_link('13');?>">Neem gerust vrijblijvend contact op.</a>
		</div>
	</div> 
	
	<div class="clear"></div>
	
	
	<?php if(function_exists('fetch_feed')) {

		include_once(ABSPATH . WPINC . '/feed.php');  // include the required file
		$feed = fetch_feed('http://allamericansports.blogspot.com/feeds/posts/default'); // specify the source feed
		$limit = $feed->get_item_quantity(7); // specify number of items
		$feeditems = $feed->get_items(0, 4); // limit at 4 posts
	} ?>
	
	
	<div class="nieuwscontainer block">
		<div class="content">
			<div class="blocktitel">Blog</div>
			 <a  target="_blank" href="https://allamericansports.nl/blog"><img style="width: 100%; height: auto;"  src="<?php echo get_template_directory_uri();?>/images/blogger.png"/></a>
		</div>
	</div> 
	
	<div class="homeimg">
	     <br /> <p /> <br /> <p /> <br /> <p />
	     <br /> <p /> <br /> <p /> <br /> <p />
	     <br /> <p /> <br /> <p /> <br /> <p />
	</div>
	
		
	<div class="block one-block aanbiedingopmaat">
		<div class="block-header">Aanbieding op maat?</div>
		<div class="content"><?php echo get_post_meta( $post->ID,  '_cmb_aanbiedingopmaat', 1 ); ?><br/><br/>
			<a href="<?php echo get_page_link(13);?>"><div class="aanbieding-button"><img class="aanbieding-more" src="<?php echo get_template_directory_uri();?>/images/more.png"/> Contact&nbsp;</div></a>
		</div> 
	</div> 

	
	<div class="block one-block onze-merken">
		<div class="content">
			<div class="blocktitel">Onze merken</div>
			<img src="<?php echo get_post_meta( $post->ID,  '_cmb_brands', 1 ); ?>" />
		</div>
	</div> 
	
	<div class="block block-large-right aanbieding">
			<div class="content">
				<div class="aanbieding-content">
					<div class="aanbieding-titel">Aanbieding</div>
						<?php echo wpautop(get_post_meta( $post->ID,  '_cmb_aanbiedingtext', 1 )); ?>
					<div class="aanbieding-prijs"><?php echo get_post_meta( $post->ID,  '_cmb_aanbiedingprice', 1 ); ?></div>
					<a href="<?php echo get_page_link(13);?>"><div class="aanbieding-button"><img class="aanbieding-more" src="<?php echo get_template_directory_uri();?>/images/more.png"/> Bestellen&nbsp;</div></a>
				</div>
				<div class="aanbieding-image">
					<img src="<?php echo get_post_meta( $post->ID,  '_cmb_aanbiedingimg', 1 ); ?>" />
				</div>
				<div class="clear"></div>
			</div>
		
	</div> 
	
	<div class="clear"></div>
	
</div>
<?php endwhile; // end of the loop. ?>
<?php wp_reset_query(); ?> 


<?php get_footer(); ?>    


</body>
</html>