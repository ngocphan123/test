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

$id = $nv_Request->get_int('id', 'post,get', 0);
$save = $nv_Request->get_string('save', 'post', '');

if ($save == 1) {
    $order_id = $nv_Request->get_int('order_id', 'post', 0);

    $db->query('UPDATE ' . $table_name . ' SET status = 1 WHERE order_id=' . $order_id);
    Header('Location: ' . NV_BASE_ADMINURL . 'index.php?' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=order');
}

$data = $db->query('SELECT * FROM ' . $table_name . ' WHERE order_id=' . $id)->fetch();

$xtpl = new XTemplate('print.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('dateup', date('d-m-Y', $data['order_time']));
$xtpl->assign('moment', date('H:i', $data['order_time']));
$xtpl->assign('DATA', $data);
$xtpl->assign('order_id', $data['order_id']);

// Thong tin chi tiet mat hang trong don hang
$listid = $listnum = array();
$result = $db->query('SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_orders_id WHERE order_id=' . $data['order_id']);
while ($row = $result->fetch()) {
    $listid[] = $row['proid'];
    $listnum[] = $row['num'];
}

$i = 0;
foreach ($listid as $id) {
    $sql = 'SELECT id, ' . NV_LANG_DATA . '_title, product_price,money_unit FROM ' . $db_config['prefix'] . '_' . $module_data . '_rows WHERE id = ' . $id . ' AND status =1 AND publtime < ' . NV_CURRENTTIME . ' AND (exptime=0 OR exptime>' . NV_CURRENTTIME . ')';
    $result = $db->query($sql);
    list($id, $title, $product_price, $money_unit) = $result->fetch(3);

    $xtpl->assign('product_name', $title);
    $xtpl->assign('product_number', $listnum[$i]);
    $xtpl->assign('product_price', nv_number_format($product_price, nv_get_decimals($pro_config['money_unit'])));
    $xtpl->assign('product_unit', $money_unit);
    $xtpl->assign('tt', $i + 1);

    $xtpl->parse('main.loop');
    ++$i;
}

$xtpl->assign('order_total', nv_number_format($data['order_total'], nv_get_decimals($pro_config['money_unit'])));
$xtpl->assign('unit', $data['unit_total']);

$payment = ($data['order_total'] == '1') ? $lang_module['order_payment'] : $lang_module['order_no_payment'];

$xtpl->assign('payment', $payment);

if ($data['status'] != '1') {
    $xtpl->parse('main.onsubmit');
}

$xtpl->parse('main');

$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo $contents;
include NV_ROOTDIR . '/includes/footer.php';
