        <div class="sideBarNav">
            <div class="company-details">
                <div class="companyName">
                    <i class="fa-solid fa-book-open-reader"></i>Story
                </div>
            </div>
            <ul class="sideNavList">
                <li>
                    <a href="timesheet.php">
                        <i class="fa-sharp fa-solid fa-calendar-days"></i>
                        <span class="linksName">Time Sheet</span>
                    </a>
                </li>
                <li>
                    <a href="scheduleReport.php">
                    <i class="fa-solid fa-table-columns"></i>
                        <span class="linksName">Schedule Report</span>
                    </a>
                </li>
                <li class="profile">
                    <div class="profileInfo">
                        <!--<img src="profile.jpg" alt="profileImg">-->
                        <div class="name_position">
                        <div class="name"><?php echo $user; ?></div>
                            <div class="position"><?php echo $role; ?></div>
                        </div>
                        <a href="logout.php"
                            ><i
                                class="fa-sharp fa-solid fa-arrow-right-from-bracket"
                                id="log_out"></i
                        ></a>
                    </div>
                </li>
            </ul>
        </div>
        
