<?php //echo '<pre>'. print_r($_SESSION, 1).'</pre>'; 
$profile = $_SESSION;
?>
<div class="row border-bottom">
    <nav class="navbar navbar-static-top white-bg" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
        </div>
        <div style="margin:14px 5px 5px 20px">Thaimart
        
	        <ul class="nav navbar-top-links navbar-right">
				<li>
			    	<span class="m-r-sm text-muted welcome-message"><i class="fa fa-user"></i><a href="?page=user_edit&id=<?php echo ($profile['userID'])?$profile['userID']:1;?>"><?php echo $profile['fname'] . ' ' . $profile['lname']; ?></a></span>
				</li>
			</ul>
		
		</div>
    </nav>
</div>

