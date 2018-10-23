<?php
    $page=isset($_GET['page'])? $_GET['page']:"main";
?>
                <!-- BEGIN SIDEBAR -->
        <div id="sidebar" class="nav-collapse collapse">
            <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
            <div class="sidebar-toggler hidden-phone"></div>
            <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
            <!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
            <div class="navbar-inverse">
                <form class="navbar-search visible-phone">
                    <input type="text" class="search-query" placeholder="Search" />
                </form>
            </div>
            <!-- END RESPONSIVE QUICK SEARCH FORM -->
            <!-- BEGIN SIDEBAR MENU -->
            <ul class="sidebar-menu">
                <li><a class="" href="?page=main"><span class="icon-box"><i class="icon-home"></i></span> Dashboard</a></li>
				<li><a class="" href="?page=mytask"><span class="icon-box"><i class=" icon-pushpin"></i></span> My Task</a></li>  
                <li class="has-sub <?php SetMenuActive($page,array("qhse_platform","projects","staff_listing","workload_management")) ?>"><a href="javascript:;" class=""><span class="icon-box"><i class="icon-briefcase"></i></span>QHSE Platform<span class="arrow"></span></a>
                  <ul class="sub">
                        <li class="<?php SetMenuActive($page,array("projects")) ?>"><a class="" href="?page=project_listing">Project listing</a></li>
                        <li class="<?php SetMenuActive($page,array("staff_listing")) ?>"><a class="" href="?page=staff_listing">Staff Listing</a></li>
						<li class="<?php SetMenuActive($page,array("workload_management")) ?>"><a class="" href="?page=workload_management">Work Load Management</a></li>
                        <li class="<?php SetMenuActive($page,array("equipment_listing")) ?>"><a class="" href="?page=equipment_listing">Equipment Listing</a></li> 
                        <li class="<?php SetMenuActive($page,array("daily_information")) ?>"><a class="" href="?page=daily_information">Daily Information</a></li>
                        <li class="<?php SetMenuActive($page,array("staff_induction")) ?>"><a class="" href="?page=staff_induction">Staff Induction</a></li>
                        <li class="<?php SetMenuActive($page,array("tool_box_talk")) ?>"><a class="" href="?page=tool_box_talk">Tool-Box Talk TBT</a></li>
                        <li class="<?php SetMenuActive($page,array("near_miss")) ?>"><a class="" href="?page=near_miss">Near Miss</a></li>
                        <li class="<?php SetMenuActive($page,array("house_keeping")) ?>"><a class="" href="?page=house_keeping">House Keeping</a></li>
                        <li class="<?php SetMenuActive($page,array("fines_penalty")) ?>"><a class="" href="?page=fines_penalty">Fines/Penalty</a></li>
                        <li class="<?php SetMenuActive($page,array("serious_incident")) ?>"><a class="" href="?page=serious_incident">Serious Incident</a></li>
                        <li class="<?php SetMenuActive($page,array("non_serious_incident")) ?>"><a class="" href="?page=non_serious_incident">Non-Serious Incident</a></li>
                        <li class="<?php SetMenuActive($page,array("environment")) ?>"><a class="" href="?page=environment">Environment</a></li>
                        <li class="<?php SetMenuActive($page,array("work_permit")) ?>"><a class="" href="?page=work_permit">Work Permit</a></li>
                        <li class="<?php SetMenuActive($page,array("weekly_slogan")) ?>"><a class="" href="?page=weekly_slogan">Slogan of the week</a></li>
						
                  </ul>
                </li>
                <li class="has-sub <?php SetMenuActive($page,array("daily_report","monthly_report")) ?>"><a href="javascript:;" class=""><span class="icon-box"><i class=" icon-bar-chart"></i></span>Reports<span class="arrow"></span></a>
                  <ul class="sub">
                        <li class="<?php SetMenuActive($page,array("daily_report")) ?>"><a class="" href="?page=daily_report">Daily Reports</a></li>
                        <li class="<?php SetMenuActive($page,array("monthly_report")) ?>"><a class="" href="?page=monthly_report">Monthly Report</a></li>
                  </ul>
                </li>

                <li class="has-sub <?php SetMenuActive($page,array("admins","mail_log","database","config")) ?>"><a href="javascript:;" class=""><span class="icon-box"><i class="icon-cogs"></i></span>System Settings<span class="arrow"></span></a>
                  <ul class="sub">
                        <li class="<?php SetMenuActive($page,array("admins")) ?>"><a class="" href="?page=admins">Administrators accounts</a></li>
                        <li class="<?php SetMenuActive($page,array("mail_log")) ?>"><a class="" href="?page=mail_log">System Mail logs</a></li>
                        <li class="<?php SetMenuActive($page,array("database")) ?>"><a class="" href="?page=database">Database Utility</a></li>
                        <li class="<?php SetMenuActive($page,array("config")) ?>"><a class="" href="?page=config">System Configuration</a></li>
                  </ul>
                </li>
                <li><a class="" href="?page=logout"><span class="icon-box"><i class="icon-off"></i></span> Logout</a></li>

            </ul>
            <!-- END SIDEBAR MENU --> 
        </div>
        <!-- END SIDEBAR -->
<?php
function SetMenuActive($curr_page,$submenupage_arr){
    if(in_array($curr_page,$submenupage_arr)) echo "active";
}
?>


	<div id="sessionDialog" class="modal hide fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="myModalLabel3" aria-hidden="true">
		<div class="modal-header">
			<h3 id="myModalLabel4">Session Timeout</h3>
		</div>
		<div class="modal-body">
			<p><div id="hideMsg" style="font-size:14px">
				There was no user activity for the last 15 minutes, session will expire In <span>5</span> Seconds
				</div>
			</p>
		</div>
		<div class="modal-footer">
			<button class="btn" id="btn_resume" data-dismiss="modal" aria-hidden="true">Resume session</button>
			<a href="admin.php?page=logout" class="btn">Log out</a>
		</div>
	</div>




	<!-- idle timeout -->
<script language="javascript" type="text/javascript">
jQuery(document).ready(function() {


		// start the idle timer plugin
		// $.idleTimeout('#sessionDialog', '#btn_resume', {
			// idleAfter: 900,
			// pollingInterval: 10,
			// keepAliveURL: 'session.php',
			// serverResponseEquals: 'OK',
			// onTimeout: function(){
				// window.location.href = 'admin.php?page=logout';
			// },
			// onIdle: function(){
				// $("#sessionDialog").modal();
			// },
			// onCountdown: function(counter){
				// $("#hideMsg span").text(counter);
			// },
			// onResume: function(){
				// console.log("session resumed...");
			// }

		// });

	})
</script>
