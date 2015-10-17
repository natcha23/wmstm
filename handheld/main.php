<?php
if(isset($_GET['page'])&& $_GET['page']!=''){
    $page = $_GET['page'];
    if($page == 'show_po'){
        include('show_po.php');
    }else if($page == 'show_item'){
        include('show_item.php');
    }else if($page == 'choice_item'){
        include('choice_item.php');
    }else if($page == 'show_rt'){
        include('show_rt.php');
    }else if($page == 'rt_items'){
        include('rt_items.php');
    }else if($page == 'rt_operate'){
        include('rt_operate.php');
    }else if($page == 'choice_car'){
        include('choice_car.php');
    }else if($page == 'rt_process'){
        include('rt_process.php');
    }else if($page == 'show_check'){
        include('show_check.php');
    }else if($page == 'check_items'){
        include('check_items.php');
    }else if($page == 'check_operate'){
        include('check_operate.php');
    }else if($page == 'check_process'){
        include('check_process.php');
    }else if($page == 'confirm_to_branch'){
        include('confirm_to_branch.php');
    }else if($page == 'confirm_detail'){
        include('confirm_detail.php');
    }else if($page == 'arrive_branch'){
        include('arrive_branch.php');
    }else if($page == 'rt_delete'){
    	include('rt_delete.php');
    }else if($page == 'update_process'){
    	include('update_process.php');
    }else if($page == 'move_product_location'){
        include('move_product_location.php');
    }else if($page == 'put_pallet_to_location'){
        include('put_pallet_to_location.php');
    }

    else if($page == 'show_picking'){
    	include('show_picking.php');
    }else if($page == 'pick_process'){
    	include('pick_process.php');
    }

    else if($page == 'show_branch_check'){
        include('show_branch_check.php');
    }else if($page == 'branch_check_items'){
        include('branch_check_items.php');
    }else if($page == 'branch_check_operate'){
        include('branch_check_operate.php');
    }
    
}else{
    include 'home.php';
}

?>