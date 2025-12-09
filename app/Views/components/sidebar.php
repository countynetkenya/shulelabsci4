<nav id="sidebar">
    <div class="sidebar-header" style="padding: 20px; background: #34495e; text-align: center;">
        <h3>ShuleLabs</h3>
        <p style="margin: 0; font-size: 12px;">School Management</p>
    </div>

    <ul class="list-unstyled components" style="padding: 20px 0;">
        <li style="padding: 10px 20px;">
            <a href="<?= base_url('dashboard') ?>" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-home"></i> Dashboard
            </a>
        </li>
        
        <?php
        $usertypeID = session()->get('usertypeID');
            $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);
            ?>
        
        <?php if ($isAdmin) : ?>
        <li style="padding: 10px 20px;">
            <a href="<?= base_url('admin') ?>" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-shield"></i> Admin Panel
            </a>
        </li>
        <?php endif; ?>
        
        <li style="padding: 10px 20px;">
            <a href="<?= base_url('students') ?>" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-user-graduate"></i> Students
            </a>
        </li>
        
        <li style="padding: 10px 20px;">
            <a href="<?= base_url('teachers') ?>" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-chalkboard-teacher"></i> Teachers
            </a>
        </li>
        
        <li style="padding: 10px 20px;">
            <a href="<?= base_url('learning/courses') ?>" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-book-reader"></i> Learning
            </a>
        </li>
        
        <li style="padding: 10px 20px;">
            <a href="#" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-book"></i> Classes
            </a>
        </li>
        
        <li style="padding: 10px 20px;">
            <a href="#" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-calendar"></i> Attendance
            </a>
        </li>

        <li style="padding: 10px 20px;">
            <a href="<?= base_url('inventory') ?>" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-cubes"></i> Inventory
            </a>
        </li>

        <li style="padding: 10px 20px;">
            <a href="<?= base_url('library') ?>" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-book"></i> Library
            </a>
        </li>

        <li style="padding: 10px 20px;">
            <a href="<?= base_url('pos') ?>" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-shopping-cart"></i> POS
            </a>
        </li>

        <li style="padding: 10px 20px;">
            <a href="<?= base_url('scheduler') ?>" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-clock-o"></i> Scheduler
            </a>
        </li>
        
        <li style="padding: 10px 20px;">
            <a href="#" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-money"></i> Fees
            </a>
        </li>
        
        <li style="padding: 10px 20px;">
            <a href="#" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-file-text"></i> Reports
            </a>
        </li>
        
        <li style="padding: 10px 20px;">
            <a href="#" style="color: #fff; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-cog"></i> Settings
            </a>
        </li>
        
        <li style="padding: 10px 20px; margin-top: 20px; border-top: 1px solid #34495e;">
            <a href="<?= base_url('auth/signout') ?>" style="color: #e74c3c; text-decoration: none; display: block; padding: 10px; border-radius: 5px;">
                <i class="fa fa-sign-out"></i> Sign Out
            </a>
        </li>
    </ul>
</nav>
