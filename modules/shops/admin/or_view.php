<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2017 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 04/18/2017 09:47
 */

if (! defined('NV_IS_FILE_ADMIN')) {
    die('Stop!!!');
}

$page_title = $lang_module['order_title'];
$table_name = $db_config['prefix'] . '_' . $module_data . '_orders';

$order_id = $nv_Request->get_int('order_id', 'post,get', 0);
$db->query('UPDATE ' . $table_name . ' SET order_view = 1 WHERE order_id=' . $order_id);

$save = $nv_Request->get_string('save', 'post', '');

$result = $db->query('SELECT * FROM ' . $table_name . ' WHERE order_id=' . $order_id);
$data_content = $result->fetch();

if (empty($data_content)) {
    Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=order');
}

if ($save == 1 and intval($data_content['transaction_status']) == - 1) {
    $order_id = $nv_Request->get_int('order_id', 'post', 0);
    $transaction_status = 0;
    $payment_id = 0;
    $payment_amount = 0;
    $payment_data = '';
    $payment = '';
    $userid = $admin_info['userid'];

    $transaction_id = $db->insert_id("INSERT INTO " . $db_config['prefix'] . "_" . $module_data . "_transaction (transaction_id, transaction_time, transaction_status, order_id, userid, payment, payment_id, payment_time, payment_amount, payment_data) VALUES (NULL, " . NV_CURRENTTIME . ", '" . $transaction_status . "', '" . $order_id . "', '" . $userid . "', '" . $payment . "', '" . $payment_id . "', " . NV_CURRENTTIME . ", '" . $payment_amount . "', '" . $payment_data . "')");

    if ($transaction_id > 0) {
        $db->query("UPDATE " . $db_config['prefix'] . "_" . $module_data . "_orders SET transaction_status=" . $transaction_status . " , transaction_id = " . $transaction_id . " WHERE order_id=" . $order_id);

        nv_insert_logs(NV_LANG_DATA, $module_name, 'log_process_product', "order_id " . $order_id, $admin_info['userid']);
    }

    $nv_Cache->delMod($module_name);
    Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=order');
}

$link = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=';

// Thong tin chi tiet mat hang trong don hang
$listid = $listnum = $listprice = $listgroup = $slistgroup = array();
$result = $db->query('SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_orders_id WHERE order_id=' . $order_id);
while ($row = $result->fetch()) {
    $listid[] = $row['proid'];
    $listnum[] = $row['num'];
    $listprice[] = $row['price'];

    $result_group = $db->query('SELECT group_id FROM ' . $db_config['prefix'] . '_' . $module_data . '_orders_id_group WHERE order_i=' . $row['id']);
    $group = array();
    while (list($group_id) = $result_group->fetch(3)) {
        $group[] = $group_id;
    }
    $listgroup[] = $group;
	$slistgroup[] = implode( ",", $group );
}

$data_pro = array();
$i = 0;
$slistid= implode( ',', $listid );

foreach ($slistgroup as $list) {
    $sql = 'SELECT t1.id, t1.listcatid, t1.product_code, t1.publtime, t1.' . NV_LANG_DATA . '_title, t1.' . NV_LANG_DATA . '_alias, t1.product_price,t2.' . NV_LANG_DATA . '_title FROM ' . $db_config['prefix'] . '_' . $module_data . '_units AS t2, ' . $db_config['prefix'] . '_' . $module_data . '_rows AS t1, ' . $db_config['prefix'] . '_' . $module_data . '_orders_id AS t3  WHERE t1.product_unit = t2.id AND t1.id = t3.proid AND t1.id IN (' . $slistid . ') AND listgroupid=' . $db->quote( $list ) . ' AND t3.order_id=' . $order_id.' AND t1.status =1 AND t1.publtime < ' . NV_CURRENTTIME . ' AND (t1.exptime=0 OR t1.exptime>' . NV_CURRENTTIME . ')';
    $result = $db->query($sql);
    if ($result->rowCount()) {
        list($id, $_catid, $product_code, $publtime, $title, $alias, $product_price, $unit) = $result->fetch(3);
     	 $data_pro[] = array(
            'id' => $id,
            'publtime' => $publtime,
            'title' => $title,
            'alias' => $alias,
            'product_price' => $listprice[$i],
            'product_price_total' => $listprice[$i] * $listnum[$i],
            'product_code' => $product_code,
            'product_unit' => $unit,
            'link_pro' => $link . $global_array_shops_cat[$_catid]['alias'] . '/' . $alias . $global_config['rewrite_exturl'],
            'product_number' => $listnum[$i],
            'product_group' => isset($listgroup[$i]) ? $listgroup[$i] : ''
        );
        ++$i;
    }
}

// Thong tin van chuyen
$result = $db->query('SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_orders_shipping WHERE order_id = ' . $order_id);
$data_shipping = $result->fetch();

if ($data_content['transaction_status'] == '4') {
    $lang_module['order_submit_pay_comfix'] = $lang_module['order_submit_unpay_comfix'];
}

$xtpl = new XTemplate('or_view.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('dateup', date('d-m-Y', $data_content['order_time']));
$xtpl->assign('moment', date('H:i', $data_content['order_time']));
$xtpl->assign('DATA', $data_content);
$xtpl->assign('order_id', $data_content['order_id']);

$array_group_main = array( );
if (!empty($global_array_group)) {
    foreach ($global_array_group as $array_group) {
        if ($array_group['indetail'] and $array_group['lev'] == 0) {
            $array_group_main[] = $array_group['groupid'];
            $xtpl->assign('MAIN_GROUP', $array_group);
            $xtpl->parse('main.main_group');
        }
    }
}

$j = 0;
foreach ($data_pro as $pdata) {
    $xtpl->assign('product_code', $pdata['product_code']);
    $xtpl->assign('product_name', $pdata['title']);
    $xtpl->assign('product_number', $pdata['product_number']);
    $xtpl->assign('product_price', nv_number_format($pdata['product_price'], nv_get_decimals($pro_config['money_unit'])));
    $xtpl->assign('product_price_total', nv_number_format($pdata['product_price_total'], nv_get_decimals($pro_config['money_unit'])));
    $xtpl->assign('product_unit', $pdata['product_unit']);
    $xtpl->assign('link_pro', $pdata['link_pro']);
    $xtpl->assign('pro_no', $j + 1);

    // Nhóm hiển thị cùng sản phẩm
    foreach ($array_group_main as $group_main_id) {
        $array_sub_group = GetGroupID($pdata['id']);
        for ($i = 0; $i < count($array_group_main); $i++) {
            $data = array( 'title' => '', 'link' => '' );
            foreach ($array_sub_group as $sub_group_id) {
                $item = $global_array_group[$sub_group_id];
                if ($item['parentid'] == $group_main_id) {
                    $data = array(
                        'title' => $item['title'],
                        'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=group/' . $item['alias']
                    );
                    $xtpl->assign('SUB_GROUP', $data);
                    $xtpl->parse('main.loop.sub_group.loop');
                }
            }
        }
        $xtpl->parse('main.loop.sub_group');
    }

    // Nhóm thuộc tính sản phẩm khách hàng chọn
    if (! empty($pdata['product_group'])) {
        foreach ($pdata['product_group'] as $groupid) {
            $items = $global_array_group[$groupid];
            $items['parent_title'] = $global_array_group[$items['parentid']]['title'];
            $xtpl->assign('group', $items);
            $xtpl->parse('main.loop.display_group.item');
        }
        $xtpl->parse('main.loop.display_group');
    }

    $xtpl->parse('main.loop');
    ++$j;
}

// Thong tin van chuyen
if ($pro_config['use_shipping']) {
    if ($data_shipping) {
        $data_shipping['ship_price'] = nv_number_format($data_shipping['ship_price'], nv_get_decimals($data_shipping['ship_price_unit']));
        $data_shipping['ship_location_title'] = $array_location[$data_shipping['ship_location_id']]['title'];
        while ($array_location[$data_shipping['ship_location_id']]['parentid'] > 0) {
            $items = $array_location[$array_location[$data_shipping['ship_location_id']]['parentid']];
            $data_shipping['ship_location_title'] .= ', ' . $items['title'];
            $array_location[$data_shipping['ship_location_id']]['parentid'] = $items['parentid'];
        }
        $data_shipping['ship_shops_title'] = $array_shops[$data_shipping['ship_shops_id']]['name'];
        $xtpl->assign('DATA_SHIPPING', $data_shipping);
        $xtpl->parse('main.data_shipping');
    }
} else {
    $xtpl->parse('main.order_address');
}

if (! empty($data_content['order_note'])) {
    $xtpl->parse('main.order_note');
}
$xtpl->assign('order_total', nv_number_format($data_content['order_total'], nv_get_decimals($pro_config['money_unit'])));
$xtpl->assign('unit', $money_config[$data_content['unit_total']]['symbol']);

// transaction_status: Trang thai giao dich:
// -1 - Giao dich cho duyet
// 0 - Giao dich moi tao
// 1 - Chua thanh toan;
// 2 - Da thanh toan, dang bi tam giu;
// 3 - Giao dich bi huy;
// 4 - Giao dich da hoan thanh thanh cong (truong hop thanh toan ngay hoac thanh toan tam giu nhung nguoi mua da phe chuan)

if ($data_content['transaction_status'] == 4) {
    $html_payment = $lang_module['history_payment_yes'];
} elseif ($data_content['transaction_status'] == 3) {
    $html_payment = $lang_module['history_payment_cancel'];
} elseif ($data_content['transaction_status'] == 2) {
    $html_payment = $lang_module['history_payment_check'];
} elseif ($data_content['transaction_status'] == 1) {
    $html_payment = $lang_module['history_payment_send'];
} elseif ($data_content['transaction_status'] == 0) {
    $html_payment = $lang_module['history_payment_no'];
} elseif ($data_content['transaction_status'] == - 1) {
    $html_payment = $lang_module['history_payment_wait'];
} else {
    $html_payment = 'ERROR';
}

$xtpl->assign('payment', $html_payment);

if ($data_content['transaction_status'] == - 1) {
    $xtpl->parse('main.onsubmit');
}

$action_pay = '';
if ($data_content['transaction_status'] != '4') {
    $action_pay = '&action=pay';
    $xtpl->parse('main.onpay');
} else {
    $lang_module['order_submit_pay_comfix'] = $lang_module['order_submit_unpay_comfix'];
    $xtpl->parse('main.unpay');
    $action_pay = '&action=unpay';
}

$xtpl->assign('LINK_PRINT', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=print&order_id=' . $data_content['order_id'] . '&checkss=' . md5($data_content['order_id'] . $global_config['sitekey'] . session_id()));
$xtpl->assign('URL_ACTIVE_PAY', NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=active_pay&order_id=' . $order_id . $action_pay);
$xtpl->assign('URL_BACK', NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=or_view&order_id=' . $order_id);

$array_data_payment = array();
$sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_payment ORDER BY weight ASC';
$result = $db->query($sql);

while ($row = $result->fetch()) {
    $payment = $row['payment'];
    $array_data_payment[$payment] = array(
        'config' => array(),
        'orders_id' => array(),
        'data' => array()
    );

    $array_data_payment[$payment]['domain'] = $row['domain'];
    $array_data_payment[$payment]['paymentname'] = $row['paymentname'];

    if (file_exists(NV_ROOTDIR . '/modules/' . $module_file . '/payment/' . $payment . '.config.ini')) {
        $array_data_payment[$payment]['config'] = unserialize(nv_base64_decode($row['config']));
    }
}

// Check lai cac don hang
$checkpayment = $nv_Request->get_string('checkpayment', 'post,get', '');
if (! empty($checkpayment) and $checkpayment == md5($order_id . session_id() . $global_config['sitekey'])) {
    $order_code = $data_content['order_code'];
    require_once NV_ROOTDIR . '/modules/' . $module_file . '/payment/nganluong.class.php';
    $payment_config = $array_data_payment['nganluong']['config'];
    $nl = new NL_Checkout($payment_config['checkout_url'], $payment_config['merchant_site'], $payment_config['secure_pass']);
    $transaction_i = $nl->checkOrder($payment_config['public_api_url'], $order_code, 0);
    if ($transaction_i !== false) {
        print_r($transaction_i);
        die();
    }
}

$a = 1;
$array_transaction = array();
$result = $db->query('SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_transaction WHERE order_id=' . $order_id . ' ORDER BY transaction_id ASC');

if ($result->rowCount()) {
    $array_payment = array();
    while ($row = $result->fetch()) {
        $row['a'] = $a++;
        $row['transaction_time'] = nv_date('H:i:s d/m/y', $row['transaction_time']);
        $row['order_id'] = (! empty($row['order_id'])) ? $row['order_id'] : '';
        $row['payment_time'] = (! empty($row['payment_time'])) ? nv_date('H:i:s d/m/y', $row['payment_time']) : '';
        $row['payment_id'] = (! empty($row['payment_id'])) ? $row['payment_id'] : '';

        if (! empty($row['payment_id'])) {
            $array_payment[] = $row['payment_id'];
        }

        $row['payment_amount'] = nv_number_format($row['payment_amount'], nv_get_decimals($pro_config['money_unit']));

        if ($row['transaction_status'] == 4) {
            $row['transaction'] = $lang_module['history_payment_yes'];
        } elseif ($row['transaction_status'] == 3) {
            $row['transaction'] = $lang_module['history_payment_cancel'];
        } elseif ($row['transaction_status'] == 2) {
            $row['transaction'] = $lang_module['history_payment_check'];
        } elseif ($row['transaction_status'] == 1) {
            $row['transaction'] = $lang_module['history_payment_send'];
        } elseif ($row['transaction_status'] == 0) {
            $row['transaction'] = $lang_module['history_payment_no'];
        } elseif ($row['transaction_status'] == - 1) {
            $row['transaction'] = $lang_module['history_payment_wait'];
        } else {
            $row['transaction'] = 'ERROR';
        }
        if ($row['userid'] > 0) {
            $username = $db->query('SELECT username FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . $row['userid'])->fetchColumn();
            $row['payment'] = $username;
            $row['link_user'] = NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=users&' . NV_OP_VARIABLE . '=edit&userid=' . $row['userid'];
        } elseif (isset($array_data_payment[$row['payment']])) {
            $row['link_user'] = $array_data_payment[$row['payment']]['domain'];
            $row['payment'] = $array_data_payment[$row['payment']]['paymentname'];
        } else {
            $row['link_user'] = '#';
        }

        $xtpl->assign('DATA_TRANS', $row);
        $xtpl->parse('main.transaction.looptrans');
    }

    if (! empty($array_payment)) {
        $xtpl->assign('LINK_CHECK_PAYMENT', NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op . '&order_id=' . $order_id . '&checkpayment=' . md5($order_id . session_id() . $global_config['sitekey']));
        $xtpl->parse('main.transaction.checkpayment');
    }

    $xtpl->parse('main.transaction');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$set_active_op = 'order';

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
