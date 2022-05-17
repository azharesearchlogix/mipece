<?php
$pagename = $this->uri->segment(2);
$segment = $this->uri->segment(2);

if ($pagename == 'myprofile' || $pagename == 'doctor' || $pagename == 'user' || $pagename == 'registeredUser' || $pagename == 'unregisteredUser' || $pagename == 'question' || $pagename == 'registeredServiceProvider' || $pagename == 'unregisteredServiceProvider' || $pagename == 'teams' || $pagename == 'services' || $pagename == 'charges' || $pagename == 'category' || $pagename == 'subcategory' || $pagename == 'registrationPayments' || $pagename == 'servicePayments' || $pagename == 'changepassword' || $pagename == 'termscondition' || $pagename == 'aboutus' || $pagename == 'contactus') {

    $pagename1 = $this->uri->segment(2);
} else {
    $pagename1 = $this->uri->segment(3);
}
?>
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?php echo base_url(); ?>design/dist/img/admin.png" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p><?php //echo $user;       ?></p>
                <h4><a href="#"><i class="fa fa-circle text-success"></i> <?php echo ucwords($user); ?></a></h4>
            </div>
        </div>

        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree" id="sub-headermenu">
            <li class="header" style="background: #4a617f">MAIN NAVIGATION</li>
            <?php echo menu(); ?>

            
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>