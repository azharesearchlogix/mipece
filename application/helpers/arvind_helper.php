<?php

defined('BASEPATH') OR exit('No direct script access allowed');


if (!function_exists('menu')) {

    function menu() {
        $CI = & get_instance();
        $m = $CI->db->get('tbl_admin_menu')->row();
        $menu = (json_decode($m->data));
        $html = '';
        foreach ($menu as $k => $v) {
            $chield = [];
            $chielddata = [];
            if (isset($v->children)) {
                $chielddata = $v->children;
                foreach ($chielddata as $key => $ch) {
                    $chield[] = strtolower($ch->href);
                }
                $mh = strtolower($CI->router->fetch_method()) == 'index' ? $CI->router->fetch_class() : strtolower($CI->router->fetch_method());

                if (!empty($chield)) {
                    $c = in_array($mh, $chield) ? 'active treeview menu-open' : 'treeview';
                    $html .= '<li class="' . $c . '">
                    <a href="#">
                    <i class="' . $v->icon . '"></i>
                    <span>&nbsp;' . $v->text . '</span>
                    <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                    </span>
                    </a>
                    <ul class="treeview-menu">';
                    foreach ($chielddata as $key => $val) {
                        // print_r($val); die;
                        $mc = $CI->uri->segment(2) == $key ? 'active' : '';
                        $html .= '<li class=' . $mc . '><a href=' . base_url('admin/' . $val->href . '') . '><i class="fa fa-circle-o"></i>&nbsp;' . $val->text . '</a></li>';
                    }
                    $html .= '</ul></li>';
                }
            } else {

                $lc = $CI->uri->segment(2) == strtolower($v->href) ? 'active' : '';
                $html .= '<li class=' . $lc . '><a href=' . base_url('admin/' . strtolower($v->href) . '') . '><i class="' . $v->icon . '"></i> <span>&nbsp;' . $v->text . '</span></a></li>';
            }
        }

        return $html;
    }

}

function encrypt($string = NULL) {
    $result = '';
    for ($i = 0, $k = strlen($string); $i < $k; $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr(E_KEY, ($i % strlen(E_KEY)) - 1, 1);
        $char = chr(ord($char) + ord($keychar));
        $result .= $char;
    }
    return rtrim(base64_encode($result), "==");
}

function decrypt($string = NULL) {
    $result = '';
    $string = base64_decode($string);
    for ($i = 0, $k = strlen($string); $i < $k; $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr(E_KEY, ($i % strlen(E_KEY)) - 1, 1);
        $char = chr(ord($char) - ord($keychar));
        $result .= $char;
    }
    return $result;
}

function Gerandomstring($n = NULL) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}

function custom_exploade($id = NULL) {
    if ($id) {
        $id = explode('&', $id);
        return $id[0];
    } else {
        return FALSE;
    }
}
