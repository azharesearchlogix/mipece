<!--BANNER START-->
<div class="banner">

<div id="demo" class="carousel slide" data-ride="carousel">
  <ul class="carousel-indicators">
    <li data-target="#demo" data-slide-to="0" class="active"></li>
    <li data-target="#demo" data-slide-to="1"></li>
  </ul>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="<?php echo base_url(); ?>front-design/images/slider1.jpg" alt="Slider" width="1100" height="500">
     
    </div>
    <div class="carousel-item">
      <img src="<?php echo base_url(); ?>front-design/images/slider2.png" alt="Slider" width="1100" height="500">
      
    </div>
    
  </div>
  <a class="carousel-control-prev" href="#demo" data-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </a>
  <a class="carousel-control-next" href="#demo" data-slide="next">
    <span class="carousel-control-next-icon"></span>
  </a>
</div>

</div>
<!--BANNER END-->




<!--WHAT WE DO SECTION START-->

<div class="home-what-we-do">
<div class="container">
<div class="row">



	<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="200ms">
		<div class="home-about-heading">
			<h2>Category List</h2>
			<div class="heading-seprate"><img src="<?php echo base_url(); ?>front-design/images/headding-seprate.png"></div>
		</div>
	</div>



<?php
foreach($data as $post){
?>

<div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 wow fadeInLeft" data-wow-duration="1000ms" data-wow-delay="400ms">
<label for="category<?php echo $post->id; ?>">
<a onclick="getSummary(<?php echo $post->id; ?>)">

	<div class="home-what-we-do-box">
	 <h3><input type="radio" value="<?php echo $post->id; ?>" name="catid" id="category<?php echo $post->id; ?>"><?php echo $post->catname; ?></h3>
	 
	</div>

</a>
</label>
</div>
<?php
}
?>



</div>
</div>
</div>
<!--WHAT WE DO SECTION END-->
<div id="subcate" style="display:none">
	<div class="container">
		<div class="row">
		 <div class="material-menu">
		<div class="material-list">
		  
			<div class="home-about-heading1">
				<h4>Sub Category</h4>
				<div class="heading-seprate1"><img src="<?php echo base_url(); ?>front-design/images/headding-seprate.png"></div>
			</div>
			<ul id="sub_category">

				

			</ul>
			</div>
		</div>
		</div>
	</div>
</div>


<div id="childcate" style="display:none">
	<div class="container">
		<div class="row">
		 <div class="material-menu">
		<div class="material-list">
		  
			<div class="home-about-heading1">
				<h4>Child Category</h4>
				<div class="heading-seprate1"><img src="<?php echo base_url(); ?>front-design/images/headding-seprate.png"></div>
			</div>
			<ul id="child_category">

				

			</ul>
			</div>
		</div>
		</div>
	</div>
</div>



<div id="material" style="display:none">
	<div class="container">
		<div class="row">
		 <div class="material-menu">
		<div class="material-list">
		  
			<div class="home-about-heading1">
				<h4>Material List</h4>
				<div class="heading-seprate1"><img src="<?php echo base_url(); ?>front-design/images/headding-seprate.png"></div>
			</div>
			<ul id="pdt_material">

				

			</ul>
			</div>
		</div>
		</div>
	</div>
</div>

<div id="pdtsize" style="display:none">
<div class="container">
	<div class="row">
	 <div class="material-menu">
	<div class="material-list">
	  
	    <div class="home-about-heading1">
			<h4>Size</h4>
			<div class="heading-seprate1"><img src="<?php echo base_url(); ?>front-design/images/headding-seprate.png"></div>
		</div>
		<ul id="pdt_size">
          
		   <?php 
			   $sql ="SELECT * FROM variant";
			   $query = $this->db->query($sql);
			   if ($query->num_rows() > 0) {
				  foreach ($query->result() as $row) {
					  ?>
					  <li><input type="checkbox" name="size" onclick="getproductlist()" value="<?php echo $row->size; ?>"> <?php echo $row->size; ?></li>
					 <?php
					 
				  }
			   }
			  ?>

		</ul>
		</div>
	</div>
	</div>
</div>
</div>


<div id="pdtcolor" style="display:none">
<div class="container">
	<div class="row">
	 <div class="material-menu">
	<div class="material-list">
	  
	    <div class="home-about-heading1">
			<h4>Color</h4>
			<div class="heading-seprate1"><img src="<?php echo base_url(); ?>front-design/images/headding-seprate.png"></div>
		</div>
		<ul id="pdt_color">
         
		      <?php 
			   $sqlc ="SELECT * FROM variant";
			   $queryc = $this->db->query($sqlc);
			   if ($queryc->num_rows() > 0) {
				  foreach ($queryc->result() as $rowc) {
					  ?>
					  <li><input type="checkbox" name="color" onclick="getproductlistbycolor()" value="<?php echo $rowc->id; ?>"> <?php echo $rowc->color; ?></li>
					 <?php
					 
				  }
			   }
			  ?>

		</ul>
		</div>
	</div>
	</div>
</div>
</div>

<!--
<div id="submitbtn" style="display:none">
 <input type="submit" name="submit" value="Search Product">
</div> -->



<!--WHY CHOOSE US SECTION START-->
<div id="pdtlist" style="display:none">
<div class="home-why-choose-wrapper wow fadeInLeftBig" >
    <div class="container">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                <h3>Product List</h3>

                <div class="home-why-choose-list">
                    <ul id="pdt_list">
					
                        
                        
                        
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>
</div>
<!--WHY CHOOSE US SECTION END-->

