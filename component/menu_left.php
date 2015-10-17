<?php //_print($_GET['page']);?>

<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
            <!-- pic logo -->
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <span>
                    <img alt="image" class="" src="<?php echo _BASE_URL_; ?>lib/img/logo.jpg" />
                    </span>
                </div>
                <div class="logo-element">
                    <img alt="image" class="" src="<?php echo _BASE_URL_; ?>lib/img/logo.jpg" style="width:50px" />
                </div>
            </li>

            <!-- ---------- Booking Menu ------------------>
            <li>
                <a href="#"><i class="fa fa-th-large"></i> <span class="nav-label">Booking Order</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level" id="nav-booking">
                <!-- จองวันส่งสินค้า -->
                    <li rel="<?php echo _BASE_URL_; ?>?page=booking_inbound" id="booking_inbound"><a href="?page=booking_inbound">จองวันส่งสินค้า</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=booking_confirm" id="booking_confirm"><a href="?page=booking_confirm">ยืนยันการส่งสินค้า</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=booking_list" id="booking_list"><a href="?page=booking_list">แผนการรับสินค้า</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=report_supplier_perform" id="report_supplier_perform"><a href="?page=report_supplier_perform">รายงานเวลา supplier ส่งสินค้า</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=report_booking_confirm" id="report_booking_confirm"><a href="?page=report_booking_confirm">รายงานสถานะยืนยันการส่งสินค้า</a></li>
                </ul>
            </li>

            <li>     <!------------------------------------------------>
                <a href="#"><i class="fa fa-th-large"></i> <span class="nav-label">Inbound</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level" id="nav-inbound">
                    <!--<li rel="<?php echo _BASE_URL_; ?>?page=inbound_items2"><a href="?page=inbound_items2">ดึงข้อมูลจากระบบ b-plus</a></li>-->
                    <li rel="<?php echo _BASE_URL_; ?>?page=inbound_po" id="inbound_po"><a href="?page=inbound_po">รายการขาเข้าทั้งหมด</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=inbound_start" id="inbound_start"><a href="?page=inbound_start">รายการที่ดำเนินการ</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=inbound_get_product" id="inbound_get_product"><a href="?page=inbound_get_product">จัดสินค้าวาง พาเลท</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=pallet_manage" id="pallet_manage"><a href="?page=pallet_manage">จัดการพาเลท</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=inbound_success" id="inbound_success"><a href="?page=inbound_success">รายการที่ดำเนินการเสร็จสมบูรณ์</a></li>
                </ul>
            </li>
            <li >     <!------------------------------------------------>
                <a href="#"><i class="fa fa-th-large"></i> <span class="nav-label">รายการสินค้า</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level" id="nav-product">
                    <li rel="<?php echo _BASE_URL_; ?>?page=product" id="product"><a href="?page=product">สินค้า</a></li>
                </ul>
            </li>
            <li>     <!------------------------------------------------>
                <a href="#"><i class="fa fa-th-large"></i> <span class="nav-label">Outbound</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level" id="nav-outbound">
                    <li rel="<?php echo _BASE_URL_; ?>?page=outbound_list" id="outbound_list"><a href="?page=outbound_list">สินค้าขาออก</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=outbound_process" id="outbound_process"><a href="?page=outbound_process">กำลังดำเนินการ</a></li>
                    <!-- <li rel="<?php echo _BASE_URL_; ?>?page=outbound_rtcheck" id="outbound_rtcheck"><a href="?page=outbound_rtcheck">ตรวจนับสินค้า</a></li> -->
					<li rel="<?php echo _BASE_URL_; ?>?page=confirm_car_out" id="confirm_car_out"><a href="?page=confirm_car_out">ยืนยันออกรถ</a></li>
                </ul>
            </li>

            <!-- ---------- Report Menu ------------------>
            <li>
                <a href="#"><i class="fa fa-th-large"></i> <span class="nav-label">รายงาน</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level" id="nav-report">
                	<li rel="<?php echo _BASE_URL_; ?>?page=report_location" id="report_location"><a href="?page=report_location">สินค้าตามที่เก็บ</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=report_supplier" id="report_supplier"><a href="?page=report_supplier">สินค้าตามผู้ผลิต</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=report_inbound" id="report_inbound"><a href="?page=report_inbound">สินค้าขาเข้า</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=report_outbound" id="report_outbound"><a href="?page=report_outbound">สินค้าขาออก</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=report_transport" id="report_transport"><a href="?page=report_transport">การขนส่ง</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=report_product_receive" id="report_productreceive"><a href="?page=report_product_receive">สินค้ารับตามจริง</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=report_aging" id="report_aging"><a href="?page=report_aging">อายุการเก็บสินค้า</a></li>
                    <!-- <li rel="<?php echo _BASE_URL_; ?>?page=report_supplier_perform" id="report_supplier_perform"><a href="?page=report_supplier_perform">การรับสินค้า</a></li> -->
                    <li rel="<?php echo _BASE_URL_; ?>?page=report_status_product" id="report_status_product"><a href="?page=report_status_product">การติดตามสถานะสินค้า</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=report_product_trans" id="report_product_trans"><a href="?page=report_product_trans">ติดตามสถานะสินค้าตามบาร์โค้ด</a></li>
                </ul>
            </li>

            <!-- ---------- Import Menu ------------------>
            <li>
                <a href="#"><i class="fa fa-th-large"></i> <span class="nav-label">นำเข้าข้อมูล</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level" id="nav-import">
                	<li rel="<?php echo _BASE_URL_; ?>?page=inbound_import" id="inbound_import"><a href="?page=inbound_import">นำเข้าข้อมูล PO ตามวัน</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=inbound_import_bypo" id="inbound_import_bypo"><a href="?page=inbound_import_bypo">นำเข้าข้อมูลตามใบ PO</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=ibp_import_in_date" id="ibp_import_in_date"><a href="?page=ibp_import_in_date">นำเข้า IBP ตามวัน</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=outbound_import" id="outbound_import"><a href="?page=outbound_import">นำเข้าข้อมูล RT ตามวัน</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=outbound_import_byrt" id="outbound_import_byrt"><a href="?page=outbound_import_byrt">นำเข้าข้อมูลตามใบ RT</a></li>
                </ul>
            </li>

            <?php if($_SESSION['level']==0){ ?>
            <li>     <!------------------------------------------------>
                <a href="#"><i class="fa fa-cog"></i> <span class="nav-label">ตั้งค่า</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level" id="nav-setting">
                    <li rel="<?php echo _BASE_URL_; ?>?page=user" id="user"><a href="?page=user">ผู้ใช้งาน</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=setting_product" id="setting_product"><a href="?page=setting_product">ตั้งค่าสินค้า</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=product_import" id="product_import"><a href="?page=product_import">ดึงข้อมูล Master Product</a></li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=supplier_import" id="supplier_import"><a href="?page=supplier_import">ดึงข้อมูล Master Supplier</a></li>

                    <li class="" id="li-category">
                        <a href="#">คลังสินค้า<span class="fa arrow"></span></a>
                        <ul class="nav nav-third-level collapse" aria-expanded="false" style="height: 0px;">
                            <li rel="<?php echo _BASE_URL_; ?>?page=catagory" id="catagory"><a href="?page=catagory">ประเภทสินค้า</a></li>
                            <li rel="<?php echo _BASE_URL_; ?>?page=address" id="address"><a href="?page=address">สถานที่เก็บ</a></li>
                            <li rel="<?php echo _BASE_URL_; ?>?page=setting_zone" id="setting_zone"><a href="?page=setting_zone">ตั้งค่าโซน</a></li>
                        </ul>
                    </li>
                    <li rel="<?php echo _BASE_URL_; ?>?page=car"><a href="<?php echo _BASE_URL_; ?>?page=car">รถ</a></li>
                </ul>
            </li>
            <?php } ?>
<!--             <li class="landing_link"> -->
            <li>
                <a href="logout.php"><i class="fa fa-sign-out"></i> <span class="nav-label">Log out</span></a>
            </li>
        </ul>

    </div>
</nav>