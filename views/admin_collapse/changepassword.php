<?php

require_once '../../includes/global.inc.php';

//check to see if they're logged in
if(!isset($_SESSION['logged_in'])) {
    header("Location: banklogin.php");
}

try {

    NoCSRF::check( 'csrf_token', $_GET, true, 60*10, false );

    //get the user object from the session
    $user = unserialize(Validation::xss_clean($_SESSION['user']));

    $emailId = Validation::xss_clean(DB::makeSafe($_SESSION["emailId"]));

    if (filter_var($emailId, FILTER_VALIDATE_EMAIL) != true) {
        header ("Location: error.php?message=Email Validation Failed");
    }

    $row = mysql_fetch_object(mysql_query("SELECT * FROM USERS WHERE emailId = '$emailId' AND isActive = 1"));

    if (!empty($_POST)) {

        $currentpassword = Validation::xss_clean(DB::makeSafe($_POST["currentpassword"]));
        $newpassword = Validation::xss_clean(DB::makeSafe($_POST["newpassword"]));
        $confirmnewpassword = Validation::xss_clean(DB::makeSafe($_POST["confirmnewpassword"]));

        // Check if current password is correct
        $userTools = new UserTools();

        if (!$userTools->login($emailId, $currentpassword)) { 
            header ("Location: error.php?message=Current Password Wrong");
            return;
        }

        if ($newpassword != $confirmnewpassword) {
            header ("Location: error.php?message=Confirm Password Wrong");
            return;
        }

        $updateDate = array(
                "password" => "'". $newpassword ."'"
            );

        if ($db->update ($updateDate, "ACCOUNTS", "userId = '".$emailId."'")) {
            ?>
            <script>
                alert ("Password updated");
            </script>
            <?php
        }
    }
}
catch (Exception $e) {
    header ("Location: error.php");
}

$token = NoCSRF::generate( 'csrf_token' );

?>
<!DOCTYPE html>
<!--[if lt IE 7]> <html class="ie lt-ie9 lt-ie8 lt-ie7 sidebar sidebar-collapse"> <![endif]-->
<!--[if IE 7]>    <html class="ie lt-ie9 lt-ie8 sidebar sidebar-collapse"> <![endif]-->
<!--[if IE 8]>    <html class="ie lt-ie9 sidebar sidebar-collapse"> <![endif]-->
<!--[if gt IE 8]> <html class="ie sidebar sidebar-collapse"> <![endif]-->
<!--[if !IE]><!-->
<html class="sidebar sidebar-collapse">
<!-- <![endif]-->
<head>
    <title>Change Password</title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
    <!-- 
	**********************************************************
	In development, use the LESS files and the less.js compiler
	instead of the minified CSS loaded by default.
	**********************************************************
	<link rel="stylesheet/less" href="../assets/less/admin/module.admin.stylesheet-complete.sidebar_type.collapse.less" />
	-->
    <!--[if lt IE 9]><link rel="stylesheet" href="../assets/components/library/bootstrap/css/bootstrap.min.css" /><![endif]-->
    <link rel="stylesheet" href="../assets/css/admin/module.admin.stylesheet-complete.sidebar_type.collapse.min.css"
    />
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <script src="../assets/components/library/jquery/jquery.min.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
    <script src="../assets/components/library/rollups/sha512.js"></script>
    <script src="../assets/components/library/jquery/jquery-migrate.min.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
    <script src="../assets/components/library/modernizr/modernizr.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
    <script src="../assets/components/plugins/less-js/less.min.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
    <script src="../assets/components/modules/admin/charts/flot/assets/lib/excanvas.js?v=v1.0.3-rc2"></script>
    <script src="../assets/components/plugins/browser/ie/ie.prototype.polyfill.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
	<script src="../assets/components/library/js/changepassword.validation.js"></script>
    <script>
    if ( /*@cc_on!@*/ false && document.documentMode === 10)
    {
        document.documentElement.className += ' ie ie10';
    }
    </script>
</head>
<body class="">
    <!-- Main Container Fluid -->
    <div class="container-fluid menu-hidden">
        <!-- Sidebar Menu -->
        <div id="menu" class="hidden-print hidden-xs  sidebar-white">
            <div id="sidebar-collapse-wrapper">
                <div id="brandWrapper">
                    <a href="accountoverview.php?lang=en" class="display-block-inline pull-left logo">
                        <img src="../assets/images/logo/logo.jpg" alt="">
                    </a>
                    <a href="accountoverview.php?lang=en">
                        <span class="text">Bank of M&uuml;nchen</span>
                    </a>
                </div>
                
                <ul class="menu list-unstyled hide" id="navigation_components">
                </ul>
                <ul class="menu list-unstyled hide" id="navigation_modules">
                </ul>
                <ul class="menu list-unstyled hide" id="navigation_modules_front">
                </ul>
                <ul class="menu list-unstyled" id="navigation_current_page">
                    <li><a href="accountoverview.php?csrf_token=<?php echo $token; ?>" class="glyphicons home"><i></i><span>Account Overview</span></a>
                    </li>
					<li class="hasSubmenu ">
                        <a href="#sidebar-collapse-overview" data-toggle="collapse" class="glyphicons money ">
                            <span class="badge pull-right badge-primary hidden-md">2</span><i></i>
                            <span>Transactions</span>
                        </a>
                        <ul id="sidebar-collapse-overview" class="collapse  ">
                            <li><a href="detailedstatement.php?lang=en&csrf_token=<?php echo $token; ?>"><i class="fa fa-list-alt"></i> Detailed Statement</a>
                            </li>
                            <li><a href="initiatetransactions.php?lang=en&csrf_token=<?php echo $token; ?>"><i class="fa fa-euro"></i> Initiate Transactions</a>
                            </li>
                        </ul>
                    </li>
                    <?php if ($row->isAdmin == 1) { ?>
					<li><a href="admin.php?lang=en&csrf_token=<?php echo $token; ?>" class="glyphicons cogwheels"><i></i><span>Administration</span></a>
                        <?php } ?>
                    </li>
					<?php if ($row->isAdmin == 1) { ?>
					<li><a href="users.php?lang=en&csrf_token=<?php echo $token; ?>" class="glyphicons parents"><i></i><span>Registered Users</span></a>
                        <?php } ?>
                    </li>
                    <li><a href="#" class="glyphicons notes_2"><i></i><span>Batch Logs</span></a>
                    </li>
                    <?php if ($row->isAdmin == 1) { ?>
					<li><a href="usersPrivChange.php?lang=en&csrf_token=<?php echo $token; ?>" class="glyphicons parents"><i></i><span>Privilege Change</span></a>
                        <?php } ?>
                    </li>
                </ul>
            </div>
        </div>
        <!-- // Sidebar Menu END -->
        <!-- Content -->
        <div id="content">
            <nav class="navbar hidden-print main " role="navigation">
                <div class="navbar-header pull-left">
                    <div class="user-action user-action-btn-navbar pull-left border-right">
                        <button class="btn btn-sm btn-navbar btn-inverse btn-stroke"><i class="fa fa-bars fa-2x"></i>
                        </button>
                    </div>
                </div>
                <ul class="main pull-right ">
                    
                    <li class="dropdown username">
                        <a href="" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="../assets/images/people/35/2.jpg" class="img-circle"
                            width="30" /><?=Validation::xss_clean($_SESSION["emailId"])?>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu pull-right">
                            
                            <li><a href="banklogout.php?csrf_token=<?php echo $token; ?>" class="glyphicons lock no-ajaxify"><i></i>Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="navbar-collapse collapse">
                    
                </div>
            </nav>
            <!-- // END navbar -->
            <div id="pdfTarget">
                <div class="innerAll shop-client-products cart invoice">
                   
                    <div class="box-generic">
                        <form role="form" action="changepassword.php?csrf_token=<?php echo $token;?>" method="POST" id="changePasswordForm">
                        <table class="table table-invoice">
                            <tbody>
								<tr>
									<td><b>All fields marked <span style="color:red;">*</span> are mandatory</b></td>
								</tr>
                                <tr>
                                    <td>
                                        Current Password<span style="color:red;">*</span> :- 
                                    </td>
                                    <td>
										<input type="password" class="form-control" id="currentpassword" placeholder="Current Password" name="currentpassword" style="width: 30%;">
									</td>                                    
                                </tr>
								<tr>
                                    <td>
                                        New Password<span style="color:red;">*</span> :- 
                                    </td>
                                    <td>
										<input type="password" class="form-control" id="newpassword" placeholder="New Password" name="newpassword" style="width: 30%;">
									</td>  
                                </tr>
								<tr>
                                    <td>
                                        Confirm New Password<span style="color:red;">*</span> :- 
                                    </td>
                                    <td>
										<input type="password" class="form-control" id="confirmnewpassword" placeholder="New Password" name="confirmnewpassword" style="width: 30%;">
									</td>  
                                </tr>
								<tr>
                                    <td>
                                        <button type="submit" class="btn btn-primary btn-stroke" id="changepasswordsubmit">Submit</button>
                                    </td>
                                    <td>
										<button type="reset" class="btn btn-primary btn-stroke" id="changepasswordrest">Reset</button>
									</td>  
                                </tr>
                            </tbody>
                        </table>
                    </form>
                    </div>
                    
                    <div class="separator bottom hidden-print"></div>
                    
                </div>
            </div>
        </div>
        <!-- // Content END -->
        <div class="clearfix"></div>
        <!-- // Sidebar menu & content wrapper END -->
        <div id="footer" class="hidden-print">
            <!--  Copyright Line -->
            <div class="copy">&copy; 2014 - Bank of München 
            </div>
            <!--  End Copyright Line -->
        </div>
        <!-- // Footer END -->
    </div>
    <!-- // Main Container Fluid END -->
    <!-- Global -->
    <script data-id="App.Config">
    var App = {};
    var basePath = '',
        commonPath = '../assets/',
        rootPath = '../',
        DEV = false,
        componentsPath = '../assets/components/';
    var primaryColor = '#3695d5',
        dangerColor = '#b55151',
        successColor = '#609450',
        infoColor = '#4a8bc2',
        warningColor = '#ab7a4b',
        inverseColor = '#45484d';
    var themerPrimaryColor = primaryColor;
    </script>
    <script src="../assets/components/library/bootstrap/js/bootstrap.min.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
    <script src="../assets/components/plugins/nicescroll/jquery.nicescroll.min.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
    <script src="../assets/components/plugins/breakpoints/breakpoints.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
    <script src="../assets/components/plugins/preload/pace/pace.min.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
    <script src="../assets/components/plugins/preload/pace/preload.pace.init.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
    <script src="../assets/components/core/js/animations.init.js?v=v1.0.3-rc2"></script>
    <script src="../assets/components/modules/admin/invoice/assets/js/invoice.init.js?v=v1.0.3-rc2&sv=v0.0.1.1"></script>
    <script src="../assets/components/core/js/sidebar.main.init.js?v=v1.0.3-rc2"></script>
    <script src="../assets/components/core/js/sidebar.collapse.init.js?v=v1.0.3-rc2"></script>
    <script src="../assets/components/common/forms/elements/bootstrap-select/assets/lib/js/bootstrap-select.js?v=v1.0.3-rc2"></script>
    <script src="../assets/components/common/forms/elements/bootstrap-select/assets/custom/js/bootstrap-select.init.js?v=v1.0.3-rc2"></script>
    <script src="../assets/components/core/js/core.init.js?v=v1.0.3-rc2"></script>
</body>
</html>
