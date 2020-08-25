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
	<div class="producten-container">
	
		<?php 
		
		$args = array('post_type' => 'producten', 'posts_per_page' => -1, 'order'=>'ASC');
		$loop = new WP_Query( $args );
		$prodArray = array();

		while($loop->have_posts()) : $loop->the_post();
		$title = get_the_title();
		$content = wpautop(get_the_content());
		$photo = get_post_meta($post->ID, "_cmb_image", true);
		$prodId = $post->ID; 
		array_push($prodArray, array('id'=> $prodId, 'title' => $title, 'content' => $content, 'photo' => $photo));
		
		$i++;
		
		?>
		
		<?php endwhile; // end of the loop. ?>
		<?php wp_reset_query(); ?> 
		
		<?php 
			$prepDescArray = array();
			$predImgArray = array();
			foreach ($prodArray as $product){ 
			
			$thisLogo = '<div onclick="showThis('.$product["id"].')" id="prod-click'.$product["id"].'" class="prod-image" >'.$product["title"].'<br/><img src="'.$product["photo"].'"/></div>';

			$i++;
			$thisDesc = '<div class="prod-description" id="prod-desc'.$product['id'].'">';
			if(!empty($product['content'])){ 
				$thisDesc .= '<div class="prod-content">'.$product['content'].'</div>'; 
			};

			$thisDesc .= '</div>';	
			
			array_push($prepDescArray, $thisDesc);
			array_push($predImgArray, $thisLogo);


			};
			
			$prepArray = array_chunk($prepDescArray, 3, true);
			$productArray = array_chunk($predImgArray, 3, true);
		
			$i = 0;
			while($i<count($productArray)) {
				foreach($productArray[$i] as $product) {
					echo $product;
				};
				echo '<div class="clear"></div>';
				foreach($prepArray[$i] as $desc) {
					echo $desc;
				};
				$i++;
			};
				
			
			
			
			?>		
	
	
	
		<div class="clear"></div>
	</div>
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