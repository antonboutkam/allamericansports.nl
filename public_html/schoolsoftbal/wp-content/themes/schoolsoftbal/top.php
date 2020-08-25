<div class="top">
		<a href="<?php echo home_url();?>"><div class="logo"><img src="<?php echo get_template_directory_uri();?>/images/schoolsoftbal-text.png" /><img class="bal" src="<?php echo get_template_directory_uri();?>/images/schoolsoftbal-bal.png" /></div></a>
		<span class="payoff">Het meest complete assortiment softbal materialen voor scholen</span>

		<a href="https://www.facebook.com/AA.Sports/" class="facebook" target="_blank"><img src="<?php echo get_template_directory_uri();?>/images/facebook.png" /><span>Volg ons</span></a>
		<div class="menu">		<?php wp_nav_menu(array('link_after' => '</a></li><li class="sep">|</li>')); ?> 		</div>
</div>

<div class="cta-container">
	<img alt="Informatie aanvragen over een Schoolsoftbal" onclick="toggleCta();" class="cta" src="<?php echo get_stylesheet_directory_uri();?>/images/cta-img.png"/>
	<div class="cta-content">
		<span class="cta-title">Stel een vraag</span>
		<span class="cta-close" onclick="toggleCta();">Sluiten<img alt="Sluiten" src="<?php echo get_stylesheet_directory_uri();?>/images/close.png"/></span>
 <?php
$action=$_REQUEST['action'];
    ?>
<form action="" method="POST" enctype="multipart/form-data">
<input type="hidden"  name="action" value="submit">
<input type="text"  id="email" class="email" name="email" placeholder="E-mail">
<input type="text"  id="telefoon" class="telefoon" name="telefoon" placeholder="Telefoon">
<div style="margin-top: .5em;">Bij voorkeur contact via:  
<input type="radio" checked="true" name="contact" id="contactemail" value="email"/>Email
<input type="radio" name="contact" id="contacttelefoon" value="telefoon"/>Telefoon
</div>

<textarea rows="7" class="vraag" id="vraag" name="vraag" placeholder="Stel hier uw vraag."></textarea> 

<img alt="Schoolsoftbal" class="cta-logo" src="<?php echo get_stylesheet_directory_uri();?>/images/schoolsoftbal-diap.png"/>
<input type="submit" value="Verzenden"/>
    <?php
if ($action!="")    /* display the contact form */
    {
    $mail=$_REQUEST['email'];
    $tel=$_REQUEST['telefoon'];
    $contact = $_POST["contact"];
    $vraag=$_REQUEST['vraag'];
    if ($mail==""|| $tel=="" || $vraag=="" || !filter_var($mail, FILTER_VALIDATE_EMAIL))
        {
	       ?>
	      <script>$('.cta-container').addClass('cta-active');</script>
	      <?php
		  if ($mail=="" || !filter_var($mail, FILTER_VALIDATE_EMAIL)){
	       ?>
	      <script>$('.email').addClass('cta-invalid');</script>
	      <?php
		  };
		  if ($tel==""){
	       ?>
	      <script>$('.telefoon').addClass('cta-invalid');</script>
	      <?php
		  };
		  if ($vraag==""){
	       ?>
	      <script>$('.vraag').addClass('cta-invalid');</script>
	      <?php
		  };
		  
		  if($contact=='email'){
		  ?>
		  <script>document.getElementById("contactemail").checked = true;</script>
	      <?php
		  };
		  
		  if($contact=='telefoon'){
		  ?>
		  <script>document.getElementById("contacttelefoon").checked = true;</script>
	      <?php
		  };

		  ?>
		  <script>document.getElementById('email').value="<?php echo $mail;?>"</script>
		  <script>document.getElementById('telefoon').value="<?php echo $tel;?>"</script>
		  <script>document.getElementById('vraag').value="<?php echo $vraag;?>"</script>
		  <?php		  
        }
    else{  
	    
	    
	    if($_POST)
		{
		
		    $recipient_email = 'info@schoolsoftbal.nl'; //recipient email
		    $subject = 'Vraag via Schoolsoftbal.nl'; //subject of email
		    $message = "Email: " . $mail . "\r\nTelefoon: " . $tel . "\r\nVoorkeur contact via: " . $contact . "\r\nVraag: " . $vraag . "\r\nVerzonden vanaf: ".get_the_permalink(); //message body
 		        $boundary = md5("sanwebe");
		        //header
		        $headers = "MIME-Version: 1.0\r\n";
		        $headers .= "Reply-To: ".$mail."" . "\r\n";
		        $headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n";
		       
		        //plain text
		        $body = "--$boundary\r\n";
		        $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
		        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
		        $body .= chunk_split(base64_encode($message));
		       
		   
		    $sentMail = @mail($recipient_email, $subject, $body, $headers);
		    if($sentMail) //output success or failure messages
		    {     
			    ?>
				<script>$('.cta-container').addClass('cta-active');</script>
				<script>$('.cta-content').html('<span class="cta-title">Bedankt voor uw vraag.</span><span class="cta-close" onclick="toggleCta();">Sluiten<img alt="Sluiten" src="https://allamericansports.nl/schoolsoftbal/wp-content/themes/schoolsoftbal/images/close.png"></span></br>Wij doen ons best om binnen 1 werkdag te antwoorden.');</script>
			    <?php

		    }else{
			    ?>
				<script>$('.cta-container').addClass('cta-active');</script>
				<script>$('.cta-content').html('<span class="cta-title">Er is helaas iets misgegaan</span><span class="cta-close" onclick="toggleCta();">Sluiten<img alt="Sluiten" src="https://allamericansports.nl/schoolsoftbal/wp-content/themes/schoolsoftbal/images/close.png"></span></br>Stel uw vraag door hem direct te mailen aan<a href="mailto:info@schoolsoftbal.nl">info@schoolsoftbal.nl</a>.');</script>
			    <?php
		    }
		
		}
	    
        }
    }  
?> 


</form>


	</div>
</div>
