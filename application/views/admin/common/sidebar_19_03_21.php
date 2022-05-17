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
                <p><?php //echo $user;      ?></p>
                <h4><a href="#"><i class="fa fa-circle text-success"></i> <?php echo ucwords($user); ?></a></h4>
            </div>
        </div>

        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree" id="sub-headermenu">
            <li class="header" style="background: #4a617f">MAIN NAVIGATION</li>

            <?php
            if ($user != 'Admin') {
                ?>		
                <li class="treeview11 <?php
                if ($pagename1 == 'myprofile') {
                    echo 'active';
                }
                ?>">
                    <a href="<?= base_url(); ?>admin/myprofile">
                        <i class="fa fa-list-alt"></i> <span>My Account</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                </li>
                <?php
            }
            ?>	

            <li class="treeview <?php
            if ($pagename1 == 'registeredUser' || $pagename1 == 'unregisteredUser') {
                echo 'active';
            }
            ?>">
                <a href="#">
                    <i class="fa fa-users"></i> <span>Manage User List</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="active"><a href="<?= base_url(); ?>admin/registeredUser"><i class="fa fa-circle-o"></i> Registered User</a></li>
                    <li class="active"><a href="<?= base_url(); ?>admin/unregisteredUser"><i class="fa fa-circle-o"></i> UnRegistered User</a></li>

                </ul>
            </li>

            <li class="treeview <?php
            if ($pagename1 == 'registeredServiceProvider' || $pagename1 == 'unregisteredServiceProvider') {
                echo 'active';
            }
            ?>">
                <a href="#">
                    <i class="fa fa-handshake-o" aria-hidden="true"></i> <span>Manage Service Provider</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="active"><a href="<?= base_url(); ?>admin/registeredServiceProvider"><i class="fa fa-circle-o"></i> Registered Service Provider</a></li>
                    <li class="active"><a href="<?= base_url(); ?>admin/unregisteredServiceProvider"><i class="fa fa-circle-o"></i> UnRegistered Service Provider</a></li>

                </ul>
            </li>


            <li class="treeview11 <?php
            if ($pagename1 == 'industry') {
                echo 'active';
            }
            ?>" >
                <a href="<?= base_url(); ?>admin/industry">
                    <i class="fa fa-industry" aria-hidden="true"></i> <span>Manage Industry</span>                    
                </a>
            </li>

            <li class="treeview <?php
            if ($this->uri->segment(2) == 'question' || $this->uri->segment(2) == 'questions') {
                echo 'active';
            }
            ?>">
                <a href="#">
                    <i class="fa fa-list-alt"></i> <span>Manage Question</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">                    
                    <li class="active"><a href="<?= base_url(); ?>admin/questions"><i class="fa fa-circle-o"></i> Provider Questions</a></li>
                    <li class="active"><a href="<?= base_url(); ?>admin/question"><i class="fa fa-circle-o"></i> Customer Questions</a></li>


                </ul>
            </li>

            <?php $master = ['experience', 'skill', 'factors', 'communication', 'language', 'package', 'location', 'assesment']; ?>
            <li class="treeview <?php echo (in_array($this->uri->segment(2), $master)) ? 'active' : '' ?>">
                <a href="#">
                    <i class="fa fa-database" aria-hidden="true"></i><span>Masters</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">                    
                    <li class="<?php echo ($segment == 'skill') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/skill"><i class="fa fa-circle-o"></i> Manage Skills</a></li>
                    <li class="<?php echo ($segment == 'experience') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/experience"><i class="fa fa-circle-o"></i> Manage Experience</a></li>
                    <li class="<?php echo ($segment == 'factors') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/factors"><i class="fa fa-circle-o"></i> Manage Factors</a></li>
                    <li class="<?php echo ($segment == 'communication') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/communication"><i class="fa fa-circle-o"></i> Manage Communication</a></li>
                    <li class="<?php echo ($segment == 'language') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/language"><i class="fa fa-circle-o"></i> Manage Language</a></li>
                    <li class="<?php echo ($segment == 'package') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/package"><i class="fa fa-circle-o"></i> Manage Package</a></li>
                    <li class="<?php echo ($segment == 'location') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/location"><i class="fa fa-circle-o"></i> Manage Location</a></li>
                    <li class="<?php echo ($segment == 'assesment') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/assesment"><i class="fa fa-circle-o"></i> Manage Assessment</a></li>
                </ul>
            </li>
            
            <?php $master = ['matchingsurvey','interviewsurvey']; ?>
            <li class="treeview <?php echo (in_array($this->uri->segment(2), $master)) ? 'active' : '' ?>">
                <a href="#">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i> <span>Survey Management</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">                    
                    <li class="<?php echo ($segment == 'matchingsurvey') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/matchingsurvey"><i class="fa fa-circle-o"></i> Matching Survey Question</a></li>
                    <li class="<?php echo ($segment == 'interviewsurvey') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/interviewsurvey"><i class="fa fa-circle-o"></i> Interview Survey Question</a></li>
                </ul>
            </li>
            
            <?php $master = ['unsubscribequestion','unsubscribe']; ?>
            <li class="treeview <?php echo (in_array($this->uri->segment(2), $master)) ? 'active' : '' ?>">
                <a href="#">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i> <span>Un subscribe Management</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">                    
                    <li class="<?php echo ($segment == 'unsubscribequestion') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/unsubscribequestion"><i class="fa fa-circle-o"></i> Un Subscribe Question</a></li>
                    <li class="<?php echo ($segment == 'unsubscribe') ? 'active' : '' ?>"><a href="<?= base_url(); ?>admin/unsubscribe"><i class="fa fa-circle-o"></i> Un Subscribe Users</a></li>
                </ul>
            </li>

            <li class="treeview <?php
            if ($pagename1 == 'registrationPayments' || $pagename1 == 'servicePayments') {
                echo 'active';
            }
            ?>">
                <a href="#">
                    <i class="fa fa-credit-card-alt" aria-hidden="true"></i> <span>Manage Payments</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="active"><a href="<?= base_url(); ?>admin/registrationPayments"><i class="fa fa-circle-o"></i> Registration Payments</a></li>
                    <li class="active"><a href="<?= base_url(); ?>admin/servicePayments"><i class="fa fa-circle-o"></i> Service Payments</a></li>

                </ul>
            </li>





            <li class="treeview11 <?php
            if ($pagename1 == 'termscondition') {
                echo 'active';
            }
            ?>">
                <a href="<?= base_url(); ?>admin/termscondition">
                    <i class="fa fa-file-text-o"></i> <span>Manage In-App Content</span>                    
                </a>
            </li>

            <li class="treeview11 <?php
            if ($pagename1 == 'doctor') {
                echo 'active';
            }
            ?>">
                <a href="<?php echo base_url(); ?>admin/doctor">
                    <i class="fa fa-plus-square" aria-hidden="true"></i> <span>Doctors Management</span>                    
                </a>
            </li>

            <li class="treeview11 <?php
            if ($pagename1 == 'appfees') {
                echo 'active';
            }
            ?>">
                <a href="<?php echo base_url(); ?>admin/appfees">
                    <i class="fa fa-arrows" aria-hidden="true"></i> <span>App fees</span>                    
                </a>
            </li>

            <li class="treeview11 <?php
            if ($pagename1 == 'blog') {
                echo 'active';
            }
            ?>">
                <a href="<?php echo base_url(); ?>admin/blog">
                    <i class="fa fa-rss" aria-hidden="true"></i> <span>Blog Management</span>                    
                </a>
            </li>

            <li class="treeview11 <?php
            if ($pagename1 == 'tasktime') {
                echo 'active';
            }
            ?>">
                <a href="<?php echo base_url(); ?>admin/tasktime">
                    <i class="fa fa-tasks"></i> <span>Task Time</span>                    
                </a>
            </li>
            <li class="treeview11 <?php
            if ($pagename1 == 'changepassword') {
                echo 'active';
            }
            ?>">
                <a href="<?= base_url(); ?>admin/changepassword">
                    <i class="fa fa-key"></i> <span>Change Password</span>                    
                </a>
            </li>

            <li class="treeview11">
                <a href="<?php echo base_url(); ?>admin/dashboard/logout">
                    <i class="fa fa-sign-out"></i> <span>Sign Out</span>                    
                </a>
            </li>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>