<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Index extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('user_agent');
        $this->odb = $this->load->database("odb", true);

        $all_groups = $this->db->query("SELECT option_value FROM wp_options WHERE option_name = 'AFFWP_AG_groups'")->row_array();
        $this->all_groups = unserialize($all_groups['option_value']);

        $all_price = $this->db->query("SELECT post_id, meta_value FROM `wp_postmeta` WHERE meta_key = '_price'")->result_array();
        $this->all_price = array_combine(array_column($all_price, "post_id"), array_column($all_price, "meta_value"));

        if ($this->input->post("dates") != null) {
            $dates = explode('-', $this->input->post("dates"));
            $this->date_from = date("Y-m-d", strtotime($dates["0"])) . " 00:00:00";
            $this->date_to = date("Y-m-d", strtotime($dates["1"])) . " 23:59:59";
        }
    }

    public function index()
    {
        $this->load->view('index/index');
    }

    public function login()
    {
        $user_login = $this->input->get("username");
        $user = $this->db->query("SELECT * FROM wp_users u
            LEFT JOIN wp_affiliate_wp_affiliates a ON u.ID = a.user_id
            WHERE user_login = '" . $user_login . "'")->row_array();
        if ($user == null) {
            $this->session->set_flashdata('message', 'Please input username correctly.');
            redirect(base_url('/'));
        }

        $user_detail = $this->db->query("SELECT meta_key, meta_value FROM wp_affiliate_wp_affiliatemeta
            WHERE meta_value = 1 AND affiliate_id = '" . $user["affiliate_id"] . "'")->result_array();
        $user_detail = array_combine(array_column($user_detail, "meta_key"), array_column($user_detail, "meta_value"));

        if ($user_login == 'fundraisermadeeasy') {
            redirect('index.php/index/boss_dashboard/' . $user_login, 'refresh');
        } else if (isset($user_detail["is_owner"])) {
            redirect('index.php/index/owner_dashboard/' . $user_login, 'refresh');
        } else if (isset($user_detail["is_sales_rep"])) {
            redirect('index.php/index/rep_dashboard/' . $user_login, 'refresh');
        } else if (isset($user_detail["is_group_admin"])) {
            redirect('index.php/index/group_dashboard/' . $user_login, 'refresh');
        } else {
            redirect('index.php/index/participant_dashboard/' . $user_login, 'refresh');
        }
    }

    public function boss_dashboard($user_login)
    {
        ini_set('max_execution_time', '600');

        if ($this->date_from == null) {
            $this->date_from = date("Y-m-1") . " 00:00:00";
        }

        if ($this->date_to == null) {
            $this->date_to = date("Y-m-d") . " 23:59:59";
        }

        $user = $this->db->query("SELECT ID, user_login, display_name
            FROM wp_users
            WHERE user_login = '" . $user_login . "'")->row_array();
        $data = array(
            "user" => $user,
            "owner_count" => 0,
            "sheets_count" => 0,
            "pillows_count" => 0,
            "amount" => 0,
            "group_list" => array(),
            "date_filter" => date("m/d/Y", strtotime($this->date_from)) . " - " . date("m/d/Y", strtotime($this->date_to)),
        );

        $owner_count = $this->db->query("SELECT count(*) as count FROM wp_affiliate_wp_affiliatemeta
            WHERE meta_key = 'is_owner' AND meta_value = 1")->row_array();
        $data["owner_count"] = is_array($owner_count) ? $owner_count["count"] : 0;

        $group_list = $this->db->query("SELECT a.affiliate_id, wp_users.user_login, wp_users.display_name FROM wp_users
            LEFT JOIN wp_affiliate_wp_affiliates a ON wp_users.ID = a.user_id
            LEFT JOIN wp_affiliate_wp_affiliatemeta m ON a.affiliate_id = m.affiliate_id
            WHERE m.meta_key = 'is_group_admin' AND m.meta_value = 1
            GROUP BY wp_users.ID")->result_array();

        foreach ($group_list as $key => $group) {
            $group_data = $this->group_dashboard($group["user_login"], true);

            $parent_owner = "";
            $parent_rep = $this->db->query("SELECT wp_users.ID, wp_users.user_login, wp_users.display_name FROM wp_affiliate_wp_affiliatemeta
                LEFT JOIN wp_users ON wp_users.user_login = wp_affiliate_wp_affiliatemeta.meta_value
                WHERE meta_key = 'hibernate_affiliate_parent' AND affiliate_id = '" . $group["affiliate_id"] . "'")->row_array();

            if (isset($parent_rep["ID"])) {
                $parent_rep_detail = $this->db->query("SELECT a.affiliate_id, m.meta_value, m.meta_key FROM wp_users
                    LEFT JOIN wp_affiliate_wp_affiliates a ON wp_users.ID = a.user_id
                    LEFT JOIN wp_affiliate_wp_affiliatemeta m ON m.affiliate_id = a.affiliate_id
                    WHERE wp_users.ID = '" . $parent_rep["ID"] . "'")->result_array();

                $parenT_rep_affiliate_id = $parent_rep_detail[0]["affiliate_id"];

                $parent_rep_detail = array_combine(array_column($parent_rep_detail, "meta_key"), array_column($parent_rep_detail, "meta_value"));

                if ($parent_rep_detail["is_owner"] == 1) {
                    $parent_owner = $parent_rep["display_name"];
                } else {
                    $parent_owner = $this->db->query("SELECT wp_users.ID, wp_users.user_login, wp_users.display_name FROM wp_affiliate_wp_affiliatemeta
                        LEFT JOIN wp_users ON wp_users.user_login = wp_affiliate_wp_affiliatemeta.meta_value
                        WHERE meta_key = 'hibernate_affiliate_parent' AND affiliate_id = '" . $parenT_rep_affiliate_id . "'")->row_array();

                    $parent_owner = isset($parent_owner["display_name"]) ? $parent_owner["display_name"] : "";
                }

            }

            $data["group_list"][] = array(
                "affiliate_id" => $group["affiliate_id"],
                "user_login" => $group["user_login"],
                "group_name" => $group_data["group_name"],
                "rep_name" => isset($parent_rep["display_name"]) ? $parent_rep["display_name"] : "",
                "owner_name" => $parent_owner,
                "sheets_count" => $group_data["sheets_count"],
                "pillows_count" => $group_data["pillows_count"],
                "amount" => $group_data["amount"],
            );

            $data["sheets_count"] += $group_data["sheets_count"];
            $data["pillows_count"] += $group_data["pillows_count"];
            $data["amount"] += $group_data["amount"];
        }

        if ($data["group_list"] != null) {
            $col_amount = array_column($data["group_list"], "amount");
            array_multisort($col_amount, SORT_DESC, $data["group_list"]);
        }

        $hdata = array(
            'logo_img' => 'https://hibernatefund.com/wp-content/uploads/2020/08/hibernate-bed-sheets-450x450-min-1.jpg',
            'group_name' => 'Master',
        );

        $this->load->view('layouts/header', $hdata);
        $this->load->view('index/boss', $data);
        $this->load->view('layouts/footer');
    }

    public function owner_dashboard($user_login, $flag = false)
    {
        $user = $this->db->query("SELECT ID, user_login, display_name
            FROM wp_users
            WHERE user_login = '" . $user_login . "'")->row_array();

        $data = array(
            "user" => $user,
            "backUrl" => $this->getBackUrl(),
            "sheets_count" => 0,
            "pillows_count" => 0,
            "amount" => 0,
            "rep_list" => array(),
            "group_list" => array(),
            "contact_list" => array(),
        );

        $group_data = $this->rep_dashboard($user_login, true);
        $data["sheets_count"] += $group_data["sheets_count"];
        $data["pillows_count"] += $group_data["pillows_count"];
        $data["amount"] += $group_data["amount"];
        $data["group_list"] = array_merge($data["group_list"], $group_data["group_list"]);
        $data["contact_list"] = array_merge($data["contact_list"], $group_data["contact_list"]);

        $rep_list = $this->db->query("SELECT wp_users.user_login, wp_users.display_name FROM wp_users
            LEFT JOIN wp_affiliate_wp_affiliates a ON wp_users.ID = a.user_id
            LEFT JOIN wp_affiliate_wp_affiliatemeta m ON m.affiliate_id = a.affiliate_id
            LEFT JOIN wp_affiliate_wp_affiliatemeta m1 ON m1.affiliate_id = m.affiliate_id
            WHERE m1.meta_key = 'is_sales_rep' AND m1.meta_value = 1
            	AND m.meta_key = 'hibernate_affiliate_parent' AND m.meta_value = '" . $user_login . "'
            GROUP BY wp_users.ID")->result_array();

        foreach ($rep_list as $key => $rep) {
            $rep_data = $this->rep_dashboard($rep["user_login"], true);

            $data["rep_list"][] = array(
                "user_login" => $rep["user_login"],
                "rep_name" => $rep["display_name"],
                "sheets_count" => $rep_data["sheets_count"],
                "pillows_count" => $rep_data["pillows_count"],
                "count_contact_list" => count($rep_data["contact_list"]),
                "amount" => $rep_data["amount"],
            );

            $data["sheets_count"] += $rep_data["sheets_count"];
            $data["pillows_count"] += $rep_data["pillows_count"];
            $data["amount"] += $rep_data["amount"];
            $data["group_list"] = array_merge($data["group_list"], $rep_data["group_list"]);
            $data["contact_list"] = array_merge($data["contact_list"], $rep_data["contact_list"]);
        }

        if ($flag == true) {
            return $data;
        }

        if ($data["rep_list"] != null) {
            $col_amount = array_column($data["rep_list"], "amount");
            array_multisort($col_amount, SORT_DESC, $data["rep_list"]);
        }

        if ($data["group_list"] != null) {
            $col_amount = array_column($data["group_list"], "amount");
            array_multisort($col_amount, SORT_DESC, $data["group_list"]);
        }

        $this->load->view('layouts/header', $this->get_group_detail($user["ID"]));
        $this->load->view('index/owner', $data);
        $this->load->view('layouts/footer');
    }

    public function owner_groups($user_login, $flag = false)
    {
        $user = $this->db->query("SELECT ID, display_name
            FROM wp_users
            WHERE user_login = '" . $user_login . "'")->row_array();

        $data = array(
            "user" => $user,
            "backUrl" => $this->getBackUrl(),
            "sheets_count" => 0,
            "pillows_count" => 0,
            "amount" => 0,
            "group_list" => array(),
            "participant_list" => array(),
        );

        $group_list = $this->db->query("SELECT wp_users.user_login, wp_users.display_name FROM wp_users
            LEFT JOIN wp_affiliate_wp_affiliates a ON wp_users.ID = a.user_id
            LEFT JOIN wp_affiliate_wp_affiliatemeta m ON m.affiliate_id = a.affiliate_id
            LEFT JOIN wp_affiliate_wp_affiliatemeta m1 ON m1.affiliate_id = m.affiliate_id
            WHERE m1.meta_key = 'is_group_admin' AND m1.meta_value = 1
            AND m.meta_key = 'hibernate_affiliate_parent' AND m.meta_value = '" . $user_login . "'
            GROUP BY wp_users.ID")->result_array();

        foreach ($group_list as $key => $group) {
            $group_data = $this->group_dashboard($group["user_login"], true);

            $data["group_list"][] = array(
                "user_login" => $group["user_login"],
                "group_name" => $group_data["group_name"],
                "sheets_count" => $group_data["sheets_count"],
                "pillows_count" => $group_data["pillows_count"],
                "amount" => $group_data["amount"],
            );

            $data["sheets_count"] += $group_data["sheets_count"];
            $data["pillows_count"] += $group_data["pillows_count"];
            $data["amount"] += $group_data["amount"];
            $data["participant_list"] = array_merge($data["participant_list"], $group_data["participant_list"]);
        }

        if ($flag == true) {
            return $data;
        }

        if ($data["group_list"] != null) {
            $col_amount = array_column($data["group_list"], "amount");
            array_multisort($col_amount, SORT_DESC, $data["group_list"]);
        }

        if ($data["participant_list"] != null) {
            $col_amount = array_column($data["participant_list"], "amount");
            array_multisort($col_amount, SORT_DESC, $data["participant_list"]);
        }

        $hdata = $this->get_group_detail($user["ID"]);
        $hdata["group_name"] = "Owner's Groups";

        $this->load->view('layouts/header', $hdata);
        $this->load->view('index/ogroups', $data);
        $this->load->view('layouts/footer');
    }

    public function rep_dashboard($user_login, $flag = false)
    {
        $user = $this->db->query("SELECT ID, display_name
            FROM wp_users
            WHERE user_login = '" . $user_login . "'")->row_array();

        $data = array(
            "user" => $user,
            "backUrl" => $this->getBackUrl(),
            "sheets_count" => 0,
            "pillows_count" => 0,
            "amount" => 0,
            "group_list" => array(),
            "participant_list" => array(),
            "contact_list" => array(),
        );

        $group_list = $this->db->query("SELECT wp_users.user_login, wp_users.display_name FROM wp_users
            LEFT JOIN wp_affiliate_wp_affiliates a ON wp_users.ID = a.user_id
            LEFT JOIN wp_affiliate_wp_affiliatemeta m ON m.affiliate_id = a.affiliate_id
            LEFT JOIN wp_affiliate_wp_affiliatemeta m1 ON m1.affiliate_id = m.affiliate_id
            WHERE m1.meta_key = 'is_group_admin' AND m1.meta_value = 1
            AND m.meta_key = 'hibernate_affiliate_parent' AND m.meta_value = '" . $user_login . "'
            GROUP BY wp_users.ID")->result_array();

        foreach ($group_list as $key => $group) {
            $group_data = $this->group_dashboard($group["user_login"], true);

            $data["group_list"][] = array(
                "user_login" => $group["user_login"],
                "group_name" => $group_data["group_name"],
                "sheets_count" => $group_data["sheets_count"],
                "pillows_count" => $group_data["pillows_count"],
                "count_contact_list" => count($group_data["contact_list"]),
                "amount" => $group_data["amount"],
            );

            $data["sheets_count"] += $group_data["sheets_count"];
            $data["pillows_count"] += $group_data["pillows_count"];
            $data["amount"] += $group_data["amount"];
            $data["participant_list"] = array_merge($data["participant_list"], $group_data["participant_list"]);
            $data["contact_list"] = array_merge($data["contact_list"], $group_data["contact_list"]);
        }

        if ($flag == true) {
            return $data;
        }

        if ($data["group_list"] != null) {
            $col_amount = array_column($data["group_list"], "amount");
            array_multisort($col_amount, SORT_DESC, $data["group_list"]);
        }

        if ($data["participant_list"] != null) {
            $col_amount = array_column($data["participant_list"], "amount");
            array_multisort($col_amount, SORT_DESC, $data["participant_list"]);
        }

        $this->load->view('layouts/header', $this->get_group_detail($user["ID"]));
        $this->load->view('index/sales', $data);
        $this->load->view('layouts/footer');
    }

    public function group_dashboard($user_login, $flag = false)
    {
        $user = $this->db->query("SELECT ID, user_login, display_name
            FROM wp_users
            WHERE user_login = '" . $user_login . "'")->row_array();

        $group_attrs = $this->db->query("SELECT meta_key, meta_value FROM `wp_affiliate_wp_affiliatemeta` m LEFT JOIN wp_affiliate_wp_affiliates a ON a.affiliate_id = m.affiliate_id LEFT JOIN wp_users u ON u.ID = a.user_id WHERE u.user_login = '" . $user_login . "'")->result_array();

        $group_attrs = array_combine(array_column($group_attrs, "meta_key"), array_column($group_attrs, "meta_value"));

        $data = array(
            "user" => $user,
            "backUrl" => $this->getBackUrl(),
            "sheets_count" => 0,
            "pillows_count" => 0,
            "amount" => 0,
            "order_list" => array(),
            "contact_list" => array(),
            "participant_list" => array(),
            "group_attrs" => $group_attrs,
        );

        $participant_list = $this->db->query("SELECT wp_users.user_login, wp_users.display_name FROM wp_users
            LEFT JOIN wp_affiliate_wp_affiliates a ON wp_users.ID = a.user_id
            LEFT JOIN wp_affiliate_wp_affiliatemeta m ON m.affiliate_id = a.affiliate_id
            WHERE m.meta_key = 'hibernate_affiliate_parent' AND m.meta_value = '" . $user_login . "'
            GROUP BY wp_users.ID")->result_array();

        $participant_list = array_merge($participant_list, array($user));

        foreach ($participant_list as $key => $participant) {
            $participant_data = $this->participant_dashboard($participant["user_login"], true);

            $participant["sheets_count"] = $participant_data["sheets_count"];
            $participant["pillows_count"] = $participant_data["pillows_count"];
            $participant["amount"] = $participant_data["amount"];
            $participant["count_contact_list"] = count($participant_data["contact_list"]);
            $participant_list[$key] = $participant;

            $data["sheets_count"] += $participant_data["sheets_count"];
            $data["pillows_count"] += $participant_data["pillows_count"];
            $data["amount"] += $participant_data["amount"];
            $data["order_list"] = array_merge($data["order_list"], $participant_data["order_list"]);
            $data["contact_list"] = array_merge($data["contact_list"], $participant_data["contact_list"]);
        }

        if ($participant_list != null) {
            $col_amount = array_column($participant_list, "amount");
            array_multisort($col_amount, SORT_DESC, $participant_list);
        }

        if ($data["order_list"] != null) {
            $col_date = array_column($data["order_list"], "date");
            array_multisort($col_date, SORT_DESC, $data["order_list"]);
        }

        $hdata = $this->get_group_detail($user["ID"]);

        $data["participant_list"] = $participant_list;
        $data["group_name"] = $hdata["group_name"];

        if ($flag == true) {
            return $data;
        }

        $this->load->view('layouts/header', $hdata);
        $this->load->view('index/group', $data);
        $this->load->view('layouts/footer');
    }

    public function update_group_data($user_login)
    {
        $user = $this->db->query("SELECT * FROM wp_users
            LEFT JOIN wp_affiliate_wp_affiliates m ON m.user_id = wp_users.ID
            WHERE user_login = '" . $user_login . "'")->row_array();

        if ($user == null) {
            return json_encode(array('status' => "failed"));
        }

        $group_name = $this->input->get("group_name");
        $group_goal = $this->input->get("group_goal");
        $end_date = $this->input->get("end_date");
        $commission = $this->input->get("commission");
        $hide_leaderboard = $this->input->get("hibernate_hide_leaderboard");

        $goal = $this->db->query("SELECT * FROM wp_affiliate_wp_affiliatemeta
            WHERE meta_key = 'fundraising_goal' AND affiliate_id = '" . $user['affiliate_id'] . "'")->row_array();

        if ($goal == null) {
            $this->db->insert('wp_affiliate_wp_affiliatemeta', array('affiliate_id' => $user['affiliate_id'], 'meta_key' => 'fundraising_goal', 'meta_value' => $group_goal));
        } else {
            $this->db->update('wp_affiliate_wp_affiliatemeta', array('meta_value' => $group_goal), array('meta_id' => $goal['meta_id']));
        }

        $date = $this->db->query("SELECT * FROM wp_affiliate_wp_affiliatemeta
            WHERE meta_key = 'end_date' AND affiliate_id = '" . $user['affiliate_id'] . "'")->row_array();

        if ($date == null) {
            $this->db->insert('wp_affiliate_wp_affiliatemeta', array('affiliate_id' => $user['affiliate_id'], 'meta_key' => 'end_date', 'meta_value' => $end_date));
        } else {
            $this->db->update('wp_affiliate_wp_affiliatemeta', array('meta_value' => $end_date), array('meta_id' => $date['meta_id']));
        }

        $sheet_commission = $this->db->query("SELECT * FROM wp_affiliate_wp_affiliatemeta
            WHERE meta_key = 'sheet_commission' AND affiliate_id = '" . $user['affiliate_id'] . "'")->row_array();

        if ($sheet_commission == null) {
            $this->db->insert('wp_affiliate_wp_affiliatemeta', array('affiliate_id' => $user['affiliate_id'], 'meta_key' => 'sheet_commission', 'meta_value' => $commission));
        } else {
            $this->db->update('wp_affiliate_wp_affiliatemeta', array('meta_value' => $commission), array('meta_id' => $sheet_commission['meta_id']));
        }

        $hibernate_hide_leaderboard = $this->db->query("SELECT * FROM wp_affiliate_wp_affiliatemeta
            WHERE meta_key = 'hibernate_hide_leaderboard' AND affiliate_id = '" . $user['affiliate_id'] . "'")->row_array();

        if ($hibernate_hide_leaderboard == null) {
            $this->db->insert('wp_affiliate_wp_affiliatemeta', array('affiliate_id' => $user['affiliate_id'], 'meta_key' => 'hibernate_hide_leaderboard', 'meta_value' => $hide_leaderboard));
        } else {
            $this->db->update('wp_affiliate_wp_affiliatemeta', array('meta_value' => $hide_leaderboard), array('meta_id' => $hibernate_hide_leaderboard['meta_id']));
        }

        $group = $this->db->query("SELECT * FROM wp_affiliate_wp_affiliatemeta
            WHERE meta_key = 'affiliate_groups' AND affiliate_id = '" . $user['affiliate_id'] . "'")->row_array();

        $group_id = unserialize($group["meta_value"]);
        $this->all_groups[$group_id[0]]["name"] = $group_name;

        $this->db->update("wp_options", array("option_value" => serialize($this->all_groups)), array("option_name" => "AFFWP_AG_groups"));

        echo json_encode(array('status' => 'success'));
    }

    public function group_hiberblast($user_login)
    {
        $user = $this->db->query("SELECT ID, user_login, display_name
            FROM wp_users
            WHERE user_login = '" . $user_login . "'")->row_array();

        $fundraising_goal = $this->db->query("SELECT IFNULL(meta_value, 0) as fundraising FROM `wp_affiliate_wp_affiliatemeta` m LEFT JOIN wp_affiliate_wp_affiliates a ON a.affiliate_id = m.affiliate_id LEFT JOIN wp_users u ON u.ID = a.user_id WHERE u.user_login = '" . $user_login . "' AND meta_key = 'fundraising_goal'")->row_array();

        $hdata = $this->get_group_detail($user["ID"]);

        $data = array(
            "user" => $user,
            "group_name" => $hdata["group_name"],
            "backUrl" => $this->getBackUrl(),
            "sheets_count" => 0,
            "pillows_count" => 0,
            "amount" => 0,
            "order_list" => array(),
            "participant_list" => array(),
            "sdate" => date("Y-m-d H:i:s"),
            "fdate" => date("d/m/Y"),
            "fundraising_goal" => $fundraising_goal["fundraising"],
        );

        $this->load->view('layouts/header', $hdata);
        $this->load->view('index/hiberblast', $data);
        $this->load->view('layouts/footer');
    }

    public function group_hiberblast_data($user_login)
    {
        $overral_data = $this->group_dashboard($user_login, true);

        $this->date_from = $this->input->get("date_from");
        $hiberblast_data = $this->group_dashboard($user_login, true);

        echo json_encode(array("odata" => $overral_data, "hdata" => $hiberblast_data));
    }

    public function participant_dashboard($user_login, $flag = false)
    {
        $user = $this->db->query("SELECT ID, display_name, user_login
            FROM wp_users
            WHERE user_login = '" . $user_login . "'")->row_array();

        $ndata = $this->get_statistics($this->db, $user_login);
        $odata = $this->get_statistics($this->odb, $user_login);
        $cdata = $this->db->query("SELECT *
            FROM wp_contacts
            WHERE participant = '" . $user_login . "'")->result_array();

        $data = array(
            "user" => $user,
            "backUrl" => $this->getBackUrl(),
            "sheets_count" => $ndata["sheets_count"] + $odata["sheets_count"],
            "pillows_count" => $ndata["pillows_count"] + $odata["pillows_count"],
            "amount" => $ndata["amount"] + $odata["amount"],
            "order_list" => array_merge($ndata["order_list"], $odata["order_list"]),
            "contact_list" => $cdata);

        if ($flag == true) {
            return $data;
        }

        if ($data["order_list"] != null) {
            $col_date = array_column($data["order_list"], "date");
            array_multisort($col_date, SORT_DESC, $data["order_list"]);
        }

        $this->load->view('layouts/header', $this->get_group_detail($user["ID"]));
        $this->load->view('index/affiliate', $data);
        $this->load->view('layouts/footer');
    }

    public function add_contact($user_login)
    {
        $user = $this->db->query("SELECT ID, fm.meta_value as first_name, lm.meta_value as last_name FROM wp_users
            LEFT JOIN (SELECT * from wp_usermeta WHERE meta_key = 'first_name') as fm ON fm.user_id = wp_users.ID
            LEFT JOIN (SELECT * from wp_usermeta WHERE meta_key = 'last_name') as lm ON lm.user_id = wp_users.ID
            WHERE user_login = '" . $user_login . "'")->row_array();

        if ($user == null) {
            echo json_encode(array('status' => "failed"));
            return;
        }

        $email_list = json_decode($this->input->get("emails"), true);

        foreach ($email_list as $email) {
            if ($this->db->query("SELECT * FROM wp_contacts WHERE email = '" . $email . "' AND participant = '" . $user_login . "'")->row() == null) {
                $group_detail = $this->get_group_detail($user["ID"]);

                $group_admin = $this->db->query("SELECT pa.user_id, pu.user_login, pm.meta_value as goal FROM wp_affiliate_wp_affiliates pa
                    LEFT JOIN wp_affiliate_wp_affiliatemeta pm ON pm.affiliate_id = pa.affiliate_id
                    LEFT JOIN (SELECt * FROM wp_users u
                        LEFT JOIN (SELECT m.meta_value as admin_id FROM wp_affiliate_wp_affiliates a
                        LEFT JOIN wp_affiliate_wp_affiliatemeta m ON m.affiliate_id = a.affiliate_id
                        WHERE a.user_id = '" . $user["ID"] . "' AND m.meta_key = 'hibernate_affiliate_parent') as p ON p.admin_id = u.user_login
                        WHERE p.admin_id = u.user_login) as pu ON pu.ID = pa.user_id
                    WHERE pm.meta_key = 'fundraising_goal' AND pu.ID = pa.user_id")->row_array();

                $goal = 250;
                if ($group_admin != null && $group_admin["goal"] != null) {
                    $members = $this->db->query("SELECT * FROM wp_affiliate_wp_affiliatemeta
                        WHERE meta_key = 'hibernate_affiliate_parent' AND meta_value = '" . $group_admin["user_login"] . "'
                        GROUP BY affiliate_id")->result_array();

                    $goal = $group_admin["goal"] / (count($members) == 0 ? 1 : count($members));
                }

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/contacts",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode(array(
                        'list_ids' => array($this->config->item("sendgrid_list_id")),
                        'contacts' => array(
                            array(
                                'alternate_emails' => array($email),
                                'email' => $email,
                                'custom_fields' => array(
                                    'e1_T' => $user["first_name"] . " " . $user["last_name"],
                                    'e2_T' => $group_detail['group_name'],
                                    'e3_T' => 'https://hibernatefund.com/shop-sheets/now/' . $user_login,
                                    'e4_T' => $group_detail['logo_img'],
                                    'e5_T' => number_format($goal, 2),
                                ),
                            ),
                        ),
                    )),
                    CURLOPT_HTTPHEADER => array(
                        "authorization: Bearer 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",
                        "content-type: application/json",
                    ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);

                $response = json_decode($response, true);
                if (!isset($response["errors"])) {
                    $this->db->insert('wp_contacts', array(
                        "participant" => $user_login,
                        "email" => $email,
                        "created_date" => date("Y-m-d h:i:s"))
                    );
                }
            }
        }

        echo json_encode(array('status' => 'success'));
    }

    public function get_statistics($db, $user_login)
    {
        $result = array("sheets_count" => 0, "pillows_count" => 0, "amount" => 0, "order_list" => array());

        $user = $db->query("SELECT *
            FROM wp_users u
            LEFT JOIN `wp_affiliate_wp_affiliates` a ON u.ID = a.user_id
            WHERE user_login = '" . $user_login . "'")->row_array();

        if ($user == null) {
            return $result;
        }

        $user_affiliate = $db->query("SELECT *
            FROM `wp_affiliate_wp_affiliates`
            WHERE user_id = '" . $user["ID"] . "'")->row_array();

        $real_order_list = array();

        $query = "SELECT r.referral_id, r.customer_id, r.amount, r.products, r.date, CONCAT(u.first_name, ' ', u.last_name) as customer_name, r.reference
            FROM wp_affiliate_wp_referrals r
            LEFT JOIN wp_affiliate_wp_customers u ON u.customer_id = r.customer_id
            WHERE r.reference not like '%level%' AND r.affiliate_id = '" . $user_affiliate["affiliate_id"] . "' AND status = 'unpaid'";

        if ($this->date_from != null) {
            $query .= " AND date >= '" . $this->date_from . "'";
        }

        if ($this->date_to != null) {
            $query .= " AND date <= '" . $this->date_to . "'";
        }

        $order_list = $db->query($query)->result_array();

        foreach ($order_list as $key => $order) {
            $order["participant_name"] = $user["display_name"];
            $order["pillows_count"] = 0;
            $order["sheets_count"] = 0;

            $order_id = (int) filter_var($order["reference"], FILTER_SANITIZE_NUMBER_INT);

            $customer_name = $db->query("SELECT m1.meta_value as firstname, m2.meta_value as lastname FROM wp_postmeta m1, wp_postmeta m2 WHERE m1.post_id = m2.post_id ANd m1.meta_key='_shipping_first_name' AND m2.meta_key='_shipping_last_name' AND m1.post_id = '" . $order_id . "'")->row_array();
            $order["customer_name"] = $customer_name["firstname"] . " " . $customer_name["lastname"];

            $products = $db->query("SELECT * FROM wp_woocommerce_order_items
                LEFT JOIN wp_woocommerce_order_itemmeta ON wp_woocommerce_order_itemmeta.order_item_id = wp_woocommerce_order_items.order_item_id
                WHERE meta_key = '_qty' AND order_item_type = 'line_item' AND order_id = '" . $order_id . "'")->result_array();

            foreach ($products as $product) {
                if (strstr($product['order_item_name'], 'Pillow')) {
                    $order["pillows_count"] += isset($product['meta_value']) ? $product['meta_value'] : 1;
                } else {
                    $order["sheets_count"] += isset($product['meta_value']) ? $product['meta_value'] : 1;
                }
            }

            if (count($products) > 0) {
                $products = $this->db->query("SELECT m1.meta_value, order_item_name FROM wp_woocommerce_order_itemmeta m
                    LEFT JOIN wp_woocommerce_order_items o ON o.order_item_id = m.order_item_id
                    LEFT JOIN wp_woocommerce_order_itemmeta m1 ON o.order_item_id = m1.order_item_id
                    WHERE m.meta_key = '_refunded_item_id' AND order_item_type = 'line_item' AND m.meta_value IN (" . implode(',', array_column($products, 'order_item_id')) . ") AND m1.meta_key = '_qty'")->result_array();

                foreach ($products as $product) {
                    if (strstr($product['order_item_name'], 'Pillow')) {
                        $order["pillows_count"] += isset($product['meta_value']) ? $product['meta_value'] : 1;
                    } else {
                        $order["sheets_count"] += isset($product['meta_value']) ? $product['meta_value'] : 1;
                    }
                }
            }

            $result["sheets_count"] += $order["sheets_count"];
            $result["pillows_count"] += $order["pillows_count"];
            $result["amount"] += $order["amount"];

            $real_order_list[$key] = $order;
        }

        $result["order_list"] = $real_order_list;

        return $result;
    }

    public function get_group_detail($user_id)
    {
        $user = $this->db->query("SELECT *
            FROM wp_affiliate_wp_affiliates a
            LEFT JOIN wp_affiliate_wp_affiliatemeta m ON a.affiliate_id = m.affiliate_id
            WHERE meta_key = 'affiliate_groups'
            AND a.user_id = '" . $user_id . "'")->row_array();

        $group_id = unserialize($user["meta_value"]);
        $group_name = $this->all_groups[$group_id[0]]["name"];

        $group_attr = $this->db->query('SELECT p2.meta_key, p2.meta_value
            FROM wp_postmeta p1, wp_postmeta p2
            WHERE p1.post_id = p2.post_id
            AND  p1.meta_key = "agl_group_name"
            AND p1.meta_value="' . $group_name . '"')->result_array();

        $group_attr = array_combine(array_column($group_attr, "meta_key"), array_column($group_attr, "meta_value"));

        $logo = "https://hibernatefund.com/wp-content/uploads/2020/08/hibernate-bed-sheets-450x450-min-1.jpg";
        if (isset($group_attr["_wp_attached_file"])) {
            if (!strstr($group_attr["_wp_attached_file"], "https://hibernatefund.com/wp-content/uploads/")) {
                $logo = "https://hibernatefund.com/wp-content/uploads/" . $group_attr["_wp_attached_file"];
            } else {
                $logo = $group_attr["_wp_attached_file"];
            }

        }

        $result = array(
            "logo_img" => $logo,
            "group_name" => $group_name,
        );
        return $result;
    }

    public function getBackUrl()
    {
        $url = $this->agent->referrer();

        if (strpos($url, base_url()) === false) {
            return null;
        }

        if (strpos($url, base_url('index.php/index/login')) !== false) {
            return null;
        }

        return $url;
    }

    public function add_groups()
    {
        $participant_list = $this->db->query("SELECT * from wp_affiliate_wp_affiliates a
            LEFT JOIN (SELECT affiliate_id, meta_value as affiliate_groups FROM wp_affiliate_wp_affiliatemeta WHERE meta_key = 'affiliate_groups') m ON a.affiliate_id = m.affiliate_id
            LEFT JOIN (SELECT affiliate_id, meta_value as is_group_admin FROM wp_affiliate_wp_affiliatemeta WHERE meta_key = 'is_group_admin') m1 ON a.affiliate_id = m1.affiliate_id
            LEFT JOIN (SELECT affiliate_id, meta_value as hibernate_affiliate_parent FROM wp_affiliate_wp_affiliatemeta WHERE meta_key = 'hibernate_affiliate_parent') m2 ON a.affiliate_id = m2.affiliate_id
            WHERE is_group_admin IS NULL AND affiliate_groups IS NULL")->result_array();

        foreach ($participant_list as $participant) {
            $user = $this->db->query("SELECT u.ID, a.affiliate_id, m.meta_value FROM `wp_users`  u
                LEFT JOIN wp_affiliate_wp_affiliates a ON u.ID = a.user_id
                LEFT JOIN (SELECT * FROM wp_affiliate_wp_affiliatemeta WHERE meta_key = 'affiliate_groups') m ON a.affiliate_id = m.affiliate_id
                WHERE user_login = '" . $participant["hibernate_affiliate_parent"] . "'")->row_array();

            if ($user != null) {
                $groups = $this->db->query("SELECT * FROM wp_affiliate_wp_affiliatemeta
                    WHERE meta_key = 'affiliate_groups' AND affiliate_id = '" . $participant['affiliate_id'] . "'")->row_array();

                if ($groups == null) {
                    $this->db->insert('wp_affiliate_wp_affiliatemeta', array('affiliate_id' => $participant['affiliate_id'], 'meta_key' => 'affiliate_groups', 'meta_value' => $user["meta_value"]));
                } else {
                    $this->db->update('wp_affiliate_wp_affiliatemeta', array('meta_value' => $user["meta_value"]), array('meta_id' => $groups['meta_id']));
                }

            }
        }
    }

    public function recalculate_amount()
    {
        $query = "SELECT r.referral_id, r.affiliate_id, r.reference FROM wp_affiliate_wp_referrals r
            WHERE r.reference not like '%level%' AND status = 'unpaid'";

        $order_list = $this->db->query($query)->result_array();

        foreach ($order_list as $key => $order) {
            $pillows_count = 0;
            $sheets_count = 0;

            $order_id = (int) filter_var($order["reference"], FILTER_SANITIZE_NUMBER_INT);

            $products = $this->db->query("SELECT * FROM wp_woocommerce_order_items
                LEFT JOIN wp_woocommerce_order_itemmeta ON wp_woocommerce_order_itemmeta.order_item_id = wp_woocommerce_order_items.order_item_id
                WHERE meta_key = '_qty' AND order_item_type = 'line_item' AND order_id = '" . $order_id . "'")->result_array();

            foreach ($products as $product) {
                if (strstr($product['order_item_name'], 'Pillow')) {
                    $pillows_count += isset($product['meta_value']) ? $product['meta_value'] : 1;
                } else {
                    $sheets_count += isset($product['meta_value']) ? $product['meta_value'] : 1;
                }
            }

            if (count($products) > 0) {
                $products = $this->db->query("SELECT m1.meta_value, order_item_name FROM wp_woocommerce_order_itemmeta m
                    LEFT JOIN wp_woocommerce_order_items o ON o.order_item_id = m.order_item_id
                    LEFT JOIN wp_woocommerce_order_itemmeta m1 ON o.order_item_id = m1.order_item_id
                    WHERE m.meta_key = '_refunded_item_id' AND order_item_type = 'line_item' AND m.meta_value IN (" . implode(',', array_column($products, 'order_item_id')) . ") AND m1.meta_key = '_qty'")->result_array();

                foreach ($products as $product) {
                    if (strstr($product['order_item_name'], 'Pillow')) {
                        $pillows_count += isset($product['meta_value']) ? $product['meta_value'] : 1;
                    } else {
                        $sheets_count += isset($product['meta_value']) ? $product['meta_value'] : 1;
                    }
                }
            }

            $tmp_affiliate_id = $order["affiliate_id"];
            if ($this->db->query("SELECT * FROM wp_affiliate_wp_affiliatemeta WHERE meta_key='is_group_admin' AND meta_key = 1 AND affiliate_id = '" . $tmp_affiliate_id . "'")->row() == null) {
                $parent_rep = $this->db->query("SELECT a2.affiliate_id FROM wp_affiliate_wp_affiliatemeta a1
                    LEFT JOIN wp_users ON wp_users.user_login = a1.meta_value
                    LEFT JOIN wp_affiliate_wp_affiliates a2 ON a2.user_id = wp_users.ID
                    WHERE a1.meta_key = 'hibernate_affiliate_parent' AND a1.affiliate_id = '" . $tmp_affiliate_id . "'")->row();

                if ($parent_rep != null) {
                    $tmp_affiliate_id = $parent_rep->affiliate_id;
                }
            }

            $sheet_commission = $this->db->query("SELECT * FROM wp_affiliate_wp_affiliatemeta
                WHERE meta_key = 'sheet_commission' AND affiliate_id = '" . $tmp_affiliate_id . "'")->row();

            if ($sheet_commission == null) {
                $sheet_commission = 12.5;
            } else {
                $sheet_commission = $sheet_commission->meta_value;
            }

            $amount = $pillows_count * 4.5 + $sheets_count * $sheet_commission;

            $this->db->query("UPDATE wp_affiliate_wp_referrals SET amount = '" . $amount . "' WHERE referral_id = " . $order["referral_id"]);
        }

        echo "success";
    }
}
