<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the HRSALE License
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.hrsale.com/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to hrsalesoft@gmail.com so we can send you a copy immediately.
 *
 * @author   HRSALE
 * @author-email  hrsalesoft@gmail.com
 * @copyright  Copyright © hrsale.com. All Rights Reserved
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Project extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        //load the model
        $this->load->model("Project_model");
        $this->load->model("Xin_model");
        $this->load->model("Company_model");
        $this->load->model("Department_model");
        $this->load->model("Designation_model");
        $this->load->model("Timesheet_model");
        $this->load->model("Clients_model");
        $this->load->library('email');
    }

    /*Function to set JSON output*/
    public function output($Return = array())
    {
        /*Set response header*/
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        /*Final JSON response*/
        exit(json_encode($Return));
    }

    public function index()
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $system = $this->Xin_model->read_setting_info(1);
        if ($system[0]->module_projects_tasks != 'true') {
            redirect('admin/dashboard');
        }
        $data['project_data'] = $this->db->get('xin_projects')->result();
        $data['title'] = $this->lang->line('xin_projects') . ' | ' . $this->Xin_model->site_title();
        $data['breadcrumbs'] = $this->lang->line('xin_projects');
        $data['path_url'] = 'project';
        $role_resources_ids = $this->Xin_model->user_role_resource();
        if (in_array('44', $role_resources_ids)) {
            if (!empty($session)) {
                $data['subview'] = $this->load->view("admin/project/project", $data, true);
                $this->load->view('admin/layout/layout_main', $data); //page load
            } else {
                redirect('admin/');
            }
        } else {
            redirect('admin/dashboard');
        }
    }

    public function add_project_form()
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $data['title'] = $this->lang->line('xin_projects') . ' | ' . $this->Xin_model->site_title();
        $data['all_employees'] = $this->db->select('user_id,first_name,last_name')->where('user_role_id',3)->where('status',1)->get('xin_employees')->result();
        $data['all_clients'] = $this->Clients_model->get_all_clients();
        $data['breadcrumbs'] = $this->lang->line('xin_projects');
        $role_resources_ids = $this->Xin_model->user_role_resource();
        $data['subview'] = $this->load->view("admin/project/project_form", $data, true);
        $this->load->view('admin/layout/layout_main', $data); //page load
    }
    public function Payment_details($project_id)
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $this->db->select('xin_projects.*, xin_clients.name as client_name');
        $this->db->from('xin_projects');
        $this->db->join('xin_clients', 'xin_projects.client_id = xin_clients.client_id');
        $this->db->where('project_id', $project_id);
        $this->db->order_by('project_id desc');
        $data['project_data'] = $this->db->get()->row();
        $this->db->select('xin_project_account.*');
        $this->db->from('xin_project_account');
        $this->db->where('project_id', $project_id);
        $data['project_payment'] = $this->db->get()->row();
        $data['title'] = $this->lang->line('xin_projects') . ' | ' . $this->Xin_model->site_title();
        $data['breadcrumbs'] = 'Payment Details';
        $role_resources_ids = $this->Xin_model->user_role_resource();

        $data['subview'] = $this->load->view("admin/project/Payment_details", $data, true);
        $this->load->view('admin/layout/layout_main', $data); //page load
    }
    public function get_payment_page()
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        // Establish a database connection

        $this->db->select('xin_projects.*');
        $this->db->from('xin_projects');
        $this->db->join('xin_project_account', 'xin_project_account.project_id = xin_projects.project_id');
        $this->db->where('xin_project_account.if_notify', 1);
        $this->db->where('xin_project_account.notify_date_start <=', date('Y-m-d'));
        $this->db->order_by('xin_project_account.project_id desc');
        $query = $this->db->get();

        if ($query) {
            // Get the query results
            $data['soft_payment_data'] = $query->result();
        } else {
            // Handle query error
            $data['soft_payment_data'] = array();
        }
        $this->db->select('xin_project_service_payment.*');
        $this->db->from('xin_project_service_payment');
        $this->db->where('xin_project_service_payment.nitify_date <=', date('Y-m-d'));
        $this->db->where('xin_project_service_payment.status',0);
        $data['service_payment_data'] = $this->db->get()->result();
        // dd($data['service_payment_data']);
        $this->db->select('xin_project_invoice.*,xin_clients.name as client_name,xin_projects.title');
        $this->db->from('xin_project_invoice');
        $this->db->join('xin_clients', 'xin_project_invoice.clint_id = xin_clients.client_id');
        $this->db->join('xin_projects', 'xin_project_invoice.project_id = xin_projects.project_id');
        $this->db->order_by('xin_project_invoice.project_id desc');
        $data['invoice_data'] = $this->db->get()->result();
        $data['title'] = $this->lang->line('xin_projects') . ' | ' . $this->Xin_model->site_title();
        $data['breadcrumbs'] = 'Payment In';
        $data['subview'] = $this->load->view("admin/project/get_payment_page", $data, true);
        $this->load->view('admin/layout/layout_main', $data);
    }

    public function get_invoice_n()
    {
        $this->db->select('xin_project_invoice.*,xin_clients.name as client_name,xin_projects.title');
        $this->db->from('xin_project_invoice');
        $this->db->join('xin_clients', 'xin_project_invoice.clint_id = xin_clients.client_id');
        $this->db->join('xin_projects', 'xin_project_invoice.project_id = xin_projects.project_id');
        $this->db->where('xin_project_invoice.id', $_POST['id']);
        $data['invoice_data'] = $this->db->get()->row();
        $page = $this->load->view("admin/project/get_invoice_n", $data, true);
        echo $page;
    }
    public function get_software_payment()
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $this->db->select('pa.project_id, p.title, pa.notify_date_start, pa.next_installment_date, c.name as client_name');
        $this->db->from('xin_project_account as pa');
        $this->db->join('xin_clients as c', 'pa.clint_id = c.client_id');
        $this->db->join('xin_projects as p', 'pa.project_id = p.project_id');
        $this->db->where('pa.if_notify', 1);
        $this->db->where('pa.notify_date_start <=', date('Y-m-d'));
        $this->db->order_by('pa.next_installment_date DESC');

        $data['soft_payment_data'] = $this->db->get()->result();

        $data['title'] = $this->lang->line('xin_projects') . ' | ' . $this->Xin_model->site_title();
        $data['breadcrumbs'] = 'Software Payment';
        $data['subview'] = $this->load->view("admin/project/get_software_payment", $data, true);
        $this->load->view('admin/layout/layout_main', $data); //page load
    }
    public function get_service_payment()
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $this->db->select('s.project_id, s.service_id, s.status, p.title, s.nitify_date, s.payment_date, c.name as client_name');
        $this->db->from('xin_project_service_payment as s');
        $this->db->join('xin_clients as c', 's.client_id = c.client_id');
        $this->db->join('xin_projects as p', 's.project_id = p.project_id');
        $this->db->where('s.nitify_date <=', date('Y-m-d'));
        $this->db->where('s.status', 0);
        $this->db->order_by('s.payment_date DESC');
        
        $data['service_payment_data'] = $this->db->get()->result();
        
        $data['title'] = $this->lang->line('xin_projects') . ' | ' . $this->Xin_model->site_title();
        $data['breadcrumbs'] = 'Service Payment';
        $data['subview'] = $this->load->view("admin/project/get_service_payment", $data, true);
        $this->load->view('admin/layout/layout_main', $data); //page load
    }
    public function get_instalment_data()
    {
        $project_id = $this->input->post('project_id');
        $number = $this->input->post('number');
        $this->db->select('xin_project_account.*,xin_projects.title,xin_clients.name as client_name');
        $this->db->from('xin_project_account');
        $this->db->where('xin_project_account.project_id', $project_id);
        $this->db->join('xin_projects', 'xin_project_account.project_id = xin_projects.project_id');
        $this->db->join('xin_clients', 'xin_project_account.clint_id = xin_clients.client_id');
        $soft_payment_data = $this->db->get()->row();
        $data['number']=$soft_payment_data->soft_intmnt_takes;
        $data['project_id'] = $project_id;
        $data['soft_payment_data'] = $soft_payment_data;
        echo json_encode($data);
    }
    public function get_service_data()
    {
        $service_id = $this->input->post('service_id');
        
        $service_payment_data = $this->db
            ->select('p.project_id, p.id, p.amount, p.status, pr.title, p.nitify_date, p.payment_date, c.name as client_name,c.client_id')
            ->from('xin_project_service_payment AS p')
            ->where([
                'p.service_id' => $service_id,
                'p.status' => 0
            ])
            ->join('xin_clients AS c', 'p.client_id = c.client_id')
            ->join('xin_projects AS pr', 'p.project_id = pr.project_id')
            ->get()
            ->result();
            
        $data = [
            'service_id' => $service_id,
            'service_payment_data' => $service_payment_data
        ];
        
        echo json_encode($data);
    }
    
    public function getFromClient()
    {
        $type = $this->input->post("type");
        if ($type == 1) {
            $data = $this->load->view('admin/project/govfrom', '', true);
        } elseif ($type == 2) {
            $data = $this->load->view('admin/project/nongovfrom', '', true);
        }
        echo $data;
    }
    public function payment_in_form()
    {
        $session = $this->session->userdata('username');
        if (empty($session) && $session['role_id'] == 3) {
            return false;
        }
        $data = array(
            'project_id' => $_POST['project_id'],
            'clint_id' => $_POST['client_id'],
            'payment_for' => 1, 
            'payment_type' => 1, 
            'date' => $_POST['installment_date'],
            'payment_way' => $_POST['payment_way'],
            'pyment_amount' => $_POST['today_payment'],
        );
        $r = $this->db->insert('xin_project_invoice', $data);
        if ($r) {
            $invoice_ids[] = $this->db->insert_id();
            $project_acount = $this->db->where('project_id', $_POST['project_id'])->get('xin_project_account')->row();
            $soft_intmnt_dates = json_decode($project_acount->soft_intmnt_dates);
            $soft_intmnt_prements = json_decode($project_acount->soft_intmnt_prements);
            $soft_intmnt_status = json_decode($project_acount->soft_intmnt_status);
            $number = $_POST['number'];
            $soft_intmnt_dates[$number] = $_POST['installment_date'];
            $soft_intmnt_prements[$number] = $_POST['today_payment'];
            $soft_intmnt_status[$number] = 1;

            $soft_intmnt_dates_json = json_encode($soft_intmnt_dates);
            $soft_intmnt_prements_json = json_encode($soft_intmnt_prements);
            $soft_intmnt_status_json = json_encode($soft_intmnt_status);

            $soft_intmnt_takes = $number + 1;

            if ($_POST['latest_remaining_payment']<=0) {
                $dtt = array(
                    'status' => 1,
                );
                $this->db->where('project_id', $_POST['project_id']);
                $ra = $this->db->update('xin_projects', $dtt);
                $soft_prement_status = 1;
                $if_notify = 0;
            } else {
                $soft_prement_status = 0;
                $if_notify = 1;
            }
            $next_installment_date = $_POST['next_installment_date'];
            $next_payment_amount = $_POST['latest_remaining_payment'];
            $installment_deu = 0;
            $Payment_Received = $_POST['payment_received']+$_POST['today_payment'];
            $Remaining_Payment = $_POST['latest_remaining_payment'];
            $Payment_Received_percent = (($Payment_Received * 100) / $project_acount->software_budget);
            $Remaining_Payment_percent = (($Remaining_Payment * 100) / $project_acount->software_budget);
            $notify_date_start = date('Y-m-d', strtotime('-2 day', strtotime($next_installment_date)));
            $update_at = date('Y-m-d');
            $dat = array(
                'soft_intmnt_dates' => $soft_intmnt_dates_json,
                'soft_intmnt_prements' => $soft_intmnt_prements_json,
                'soft_intmnt_status' => $soft_intmnt_status_json,
                'invoice_ids' => json_encode($invoice_ids),
                'soft_intmnt_takes' => $soft_intmnt_takes,
                'soft_prement_status' => $soft_prement_status,
                'if_notify' => $if_notify,
                'next_installment_date' => $next_installment_date,
                'next_payment_amount' => $next_payment_amount,
                'installment_deu' => $installment_deu,
                'Payment_Received' => $Payment_Received,
                'Remaining_Payment' => $Remaining_Payment,
                'Payment_Received_percent' => $Payment_Received_percent,
                'Remaining_Payment_percent' => $Remaining_Payment_percent,
                'notify_date_start' => $notify_date_start,
                'update_at' => $update_at,
            );
            $this->db->where('project_id', $_POST['project_id']);
            $ra = $this->db->update('xin_project_account', $dat);
            if ($ra) {
                $result = 'Success';
            } else {
                $result = 'Error';
            }
            echo $result;
        } else {
            $result = 'Error';
            echo $result;
        }
    }
    public function payment_in_form_service()
    {
        $session = $this->session->userdata('username');
        if (empty($session) && $session['role_id'] == 3) {
            return false;
        }
        $data = array(
            'status' =>$_POST['status'] ,
        );
        $this->db->where('id', $_POST['service_id']);
        $ra = $this->db->update('xin_project_service_payment', $data);
        if($ra && $_POST['status']==1){
        $data = array(
            'project_id' => $_POST['project_id'],
            'clint_id' => $_POST['client_id'],
            'payment_for' => 2,
            'payment_type' => 3,
            'date' => date('Y-m-d'),
            'payment_way' => $_POST['payment_way'],
            'pyment_amount' => $_POST['amount'],
        );
        $this->db->insert('xin_project_invoice', $data);
        }
    }
    public function timelogs()
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $system = $this->Xin_model->read_setting_info(1);
        if ($system[0]->module_projects_tasks != 'true') {
            redirect('admin/dashboard');
        }
        $data['title'] = $this->lang->line('xin_project_timelogs') . ' | ' . $this->Xin_model->site_title();
        $data['all_employees'] = $this->Xin_model->all_employees();
        $data['all_companies'] = $this->Xin_model->get_companies();
        $data['all_projects'] = $this->Project_model->get_all_projects();
        $data['all_clients'] = $this->Clients_model->get_all_clients();
        $data['breadcrumbs'] = $this->lang->line('xin_project_timelogs');
        $data['path_url'] = 'project_timelogs';
        $role_resources_ids = $this->Xin_model->user_role_resource();
            if (!empty($session)) {
                $data['subview'] = $this->load->view("admin/project/project_timelogs_list", $data, true);
                $this->load->view('admin/layout/layout_main', $data); //page load
            } else {
                redirect('admin/');
            }
        
    }
    public function reject_timelogs($id){
        $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
        $Return['csrf_hash'] = $this->security->get_csrf_hash();
        $this->db->where('timelogs_id', $id);
        if($this->db->update('xin_projects_timelogs', array('status' => 2))){
            $Return['result'] = 'Timelogs Rejected';
            redirect('admin/project/emp_timelogs');
        }else{
            $Return['error'] = 'Error Rejected';
            redirect('admin/project/emp_timelogs');
        }
    }
    public function approve_timelogs($id){
        $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
        $Return['csrf_hash'] = $this->security->get_csrf_hash();
        $this->db->where('timelogs_id', $id);
        if($this->db->update('xin_projects_timelogs', array('status' => 1))){
            $Return['result'] = 'Timelogs Approved';
            redirect('admin/project/emp_timelogs');
        }else{
            $Return['error'] = 'Error Approved';
            redirect('admin/project/emp_timelogs');
        }
    }
    public function emp_timelogs()
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $system = $this->Xin_model->read_setting_info(1);
        if ($system[0]->module_projects_tasks != 'true') {
            redirect('admin/dashboard');
        }
        $data['title'] = $this->lang->line('xin_project_timelogs') . ' | ' . $this->Xin_model->site_title();
        $data['all_employees'] = $this->Xin_model->all_employees();
        $data['all_companies'] = $this->Xin_model->get_companies();
        $data['all_projects'] = $this->Project_model->get_all_projects();
        $data['all_clients'] = $this->Clients_model->get_all_clients();
        $data['breadcrumbs'] = $this->lang->line('xin_project_timelogs');
        $data['path_url'] = 'project_timelogs';
        $role_resources_ids = $this->Xin_model->user_role_resource();
            if (!empty($session)) {
                $data['subview'] = $this->load->view("admin/project/emp_project_timelogs_list", $data, true);
                $this->load->view('admin/layout/layout_main', $data); //page load
            } else {
                redirect('admin/');
            }
        
    }

    //projects calendar
    public function projects_calendar()
    {

        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $data['title'] = $this->lang->line('xin_hr_projects_calendar');
        $data['breadcrumbs'] = $this->lang->line('xin_hr_projects_calendar');
        $data['all_tasks'] = $this->Timesheet_model->get_tasks();
        $data['all_projects'] = $this->Project_model->get_projects();
        $data['path_url'] = 'projects_calendar';
        $role_resources_ids = $this->Xin_model->user_role_resource();
        if (in_array('44', $role_resources_ids)) {
            $data['subview'] = $this->load->view("admin/project/projects_calendar", $data, true);
            $this->load->view('admin/layout/layout_main', $data); //page load
        } else {
            redirect('admin/dashboard');
        }
    }
    //tasks calendar
    public function tasks_calendar()
    {

        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $data['title'] = $this->lang->line('xin_tasks_calendar');
        $data['breadcrumbs'] = $this->lang->line('xin_tasks_calendar');
        $data['all_tasks'] = $this->Timesheet_model->get_tasks();
        $data['all_projects'] = $this->Project_model->get_projects();
        $data['path_url'] = 'projects_calendar';
        $role_resources_ids = $this->Xin_model->user_role_resource();
        if (in_array('45', $role_resources_ids)) {
            $data['subview'] = $this->load->view("admin/project/tasks_calendar", $data, true);
            $this->load->view('admin/layout/layout_main', $data); //page load
        } else {
            redirect('admin/dashboard');
        }
    }
    //tasks scrum board
    public function tasks_scrum_board()
    {

        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $data['title'] = $this->lang->line('xin_tasks_sboard');
        $data['breadcrumbs'] = $this->lang->line('xin_tasks_sboard');
        $data['all_tasks'] = $this->Timesheet_model->get_tasks();
        $data['all_projects'] = $this->Project_model->get_projects();
        $data['path_url'] = 'tasks_scrum_board';
        $role_resources_ids = $this->Xin_model->user_role_resource();
        if (in_array('45', $role_resources_ids)) {
            $data['subview'] = $this->load->view("admin/project/task_scrum_baord", $data, true);
            $this->load->view('admin/layout/layout_main', $data); //page load
        } else {
            redirect('admin/dashboard');
        }
    }
    //projects scrum board
    public function projects_scrum_board()
    {

        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $data['title'] = $this->lang->line('xin_projects_sboard');
        $data['breadcrumbs'] = $this->lang->line('xin_projects_sboard');
        $data['all_tasks'] = $this->Timesheet_model->get_tasks();
        $data['all_projects'] = $this->Project_model->get_projects();
        $data['path_url'] = 'projects_scrum_board';
        $role_resources_ids = $this->Xin_model->user_role_resource();
        if (in_array('44', $role_resources_ids)) {
            $data['subview'] = $this->load->view("admin/project/project_scrum_baord", $data, true);
            $this->load->view('admin/layout/layout_main', $data); //page load
        } else {
            redirect('admin/dashboard');
        }
    }
    // get company > employees
    public function get_employees()
    {

        $data['title'] = $this->Xin_model->site_title();
        $id = $this->uri->segment(4);

        $data = array(
            'company_id' => $id,
        );
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view("admin/project/get_employees", $data);
        } else {
            redirect('admin/');
        }
        // Datatables Variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
    }
    // get company > project employees
    public function get_project_employees()
    {

        $data['title'] = $this->Xin_model->site_title();
        $id = $this->uri->segment(4);
        $result = $this->Project_model->read_project_information($id);
        if (is_null($result)) {
            redirect('admin/project/timelogs');
        }
        $data = array(
            'project_id' => $id,
            'assigned_to' => $result[0]->assigned_to,
            'company_id' => $result[0]->company_id,
        );
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view("admin/project/get_project_employees", $data);
        } else {
            redirect('admin/');
        }
        // Datatables Variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
    }
    // update task status
    public function update_task_scrum_board_status()
    {

        $data['title'] = $this->Xin_model->site_title();
        /* Define return | here result is used to return user data and error for error message */
        $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
        $Return['csrf_hash'] = $this->security->get_csrf_hash();
        $task_id = $this->uri->segment(4);
        $task_status = $this->uri->segment(5);

        $data = array(
            'task_status' => $task_status,
        );
        $result = $this->Timesheet_model->update_task_record($data, $task_id);
        if ($result == true) {
            $Return['result'] = $this->lang->line('xin_success_task_status');
        } else {
            $Return['error'] = $this->lang->line('xin_error_msg');
        }
        $this->output($Return);
        exit;
    }
    // update project status
    public function update_project_scrum_board_status()
    {

        $data['title'] = $this->Xin_model->site_title();
        /* Define return | here result is used to return user data and error for error message */
        $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
        $Return['csrf_hash'] = $this->security->get_csrf_hash();
        $project_id = $this->uri->segment(4);
        $project_status = $this->uri->segment(5);

        $data = array(
            'status' => $project_status,
        );
        $result = $this->Project_model->update_record($data, $project_id);
        if ($result == true) {
            $Return['result'] = $this->lang->line('xin_success_task_status');
        } else {
            $Return['error'] = $this->lang->line('xin_error_msg');
        }
        $this->output($Return);
        exit;
    }

    public function invoices()
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $system = $this->Xin_model->read_setting_info(1);
        if ($system[0]->module_projects_tasks != 'true') {
            redirect('admin/dashboard');
        }
        $data['title'] = $this->Xin_model->site_title();
        $data['all_employees'] = $this->Xin_model->all_employees();
        $data['all_companies'] = $this->Xin_model->get_companies();
        $data['all_clients'] = $this->Clients_model->get_all_clients();
        $data['breadcrumbs'] = $this->lang->line('xin_projects');
        $data['path_url'] = 'project';
        $role_resources_ids = $this->Xin_model->user_role_resource();
        if (in_array('44', $role_resources_ids)) {
            if (!empty($session)) {
                $data['subview'] = $this->load->view("admin/project/project_list", $data, true);
                $this->load->view('admin/layout/layout_main', $data); //page load
            } else {
                redirect('admin/');
            }
        } else {
            redirect('admin/dashboard');
        }
    }

    public function detail()
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $system = $this->Xin_model->read_setting_info(1);
        if ($system[0]->module_projects_tasks != 'true') {
            redirect('admin/dashboard');
        }
        /*$role_resources_ids = $this->Xin_model->user_role_resource();
        if(in_array('318',$role_resources_ids)) { //view
        redirect('admin/project');
        }*/
        $data['title'] = $this->Xin_model->site_title();
        //$data['all_employees'] = $this->Xin_model->all_employees();
        //$data['all_companies'] = $this->Xin_model->get_companies();
        //$data['breadcrumbs'] = $this->lang->line('xin_project_detail');
        $id = $this->uri->segment(4);
        $result = $this->Project_model->read_project_information($id);
        if (is_null($result)) {
            redirect('admin/project');
        }
        $edata = array(
            'is_notify' => 0,
        );
        $this->Project_model->update_record($edata, $id);
        // get user > added by
        $user = $this->Xin_model->read_user_info($result[0]->added_by);
        // user full name
        if (!is_null($user)) {
            $full_name = $user[0]->first_name . ' ' . $user[0]->last_name;
        } else {
            $full_name = '--';
        }
        $result2 = $this->Clients_model->read_client_info($result[0]->client_id);
        if (!is_null($result2)) {
            $client_name = $result2[0]->name;
        } else {
            $client_name = '--';
        }

        $data = array(
            'breadcrumbs' => $this->lang->line('xin_project_detail'),
            'project_id' => $result[0]->project_id,
            'title' => $result[0]->title,
            'project_note' => $result[0]->project_note,
            'summary' => $result[0]->summary,
            'client_id' => $result[0]->client_id,
            'client_name' => $client_name,
            'start_date' => $result[0]->start_date,
            'end_date' => $result[0]->end_date,
            'company_id' => $result[0]->company_id,
            'assigned_to' => $result[0]->assigned_to,
            'created_at' => $result[0]->created_at,
            'priority' => $result[0]->priority,
            'added_by' => $full_name,
            'description' => $result[0]->description,
            'progress' => $result[0]->project_progress,
            'project_no' => $result[0]->project_no,
            'budget_hours' => $result[0]->budget_hours,
            'status' => $result[0]->status,
            'path_url' => 'project_detail',
            'all_clients' => $this->Clients_model->get_all_clients(),
            'all_employees' => $this->Xin_model->all_employees(),
            'all_companies' => $this->Xin_model->get_companies(),
        );

        //$role_resources_ids = $this->Xin_model->user_role_resource();
        //if(in_array('7',$role_resources_ids)) {
        if (!empty($session)) {
            $data['subview'] = $this->load->view("admin/project/project_details", $data, true);
            $this->load->view('admin/layout/layout_main', $data); //page load
        } else {
            redirect('admin/');
        }
    }

    public function project_list()
    {

        $data['title'] = $this->Xin_model->site_title();
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view("admin/project/project_list", $data);
        } else {
            redirect('admin/');
        }
        // Datatables Variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $role_resources_ids = $this->Xin_model->user_role_resource();
        $user_info = $this->Xin_model->read_user_info($session['user_id']);
        if ($user_info[0]->user_role_id == 1) {
            $project = $this->Project_model->get_projects();
        } else {
            if (in_array('318', $role_resources_ids)) {
                $project = $this->Project_model->get_company_projects($user_info[0]->company_id);
            } else {
                $project = $this->Project_model->get_employee_projects($session['user_id']);
            }
        }
        $data = array();

        foreach ($project->result() as $r) {
            $aim = explode(',', $r->assigned_to);
            // get user > added by
            $user = $this->Xin_model->read_user_info($r->added_by);
            // user full name
            if (!is_null($user)) {
                $full_name = $user[0]->first_name . ' ' . $user[0]->last_name;
            } else {
                $full_name = '--';
            }
            // get date
            $psdate = $this->Xin_model->set_date_format($r->start_date);
            $pedate = $this->Xin_model->set_date_format($r->end_date);

            //project_progress
            if ($r->project_progress <= 20) {
                $progress_class = 'progress-bar-danger';
            } elseif ($r->project_progress > 20 && $r->project_progress <= 50) {
                $progress_class = 'progress-bar-warning';
            } elseif ($r->project_progress > 50 && $r->project_progress <= 75) {
                $progress_class = 'progress-bar-info';
            } else {
                $progress_class = 'progress-bar-success';
            }

            // progress
            $pbar = '<p class="m-b-0-5">' . $this->lang->line('xin_completed') . ' <span class="pull-xs-right">' . $r->project_progress . '%</span>
	<div class="progress progress-xs"><div class="progress-bar ' . $progress_class . ' progress-bar-striped" role="progressbar" aria-valuenow="' . $r->project_progress . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $r->project_progress . '%"></div></div></p>';

            //status
            if ($r->status == 0) {
                $status = '<span class="label label-warning">' . $this->lang->line('xin_not_started') . '</span>';
            } elseif ($r->status == 1) {
                $status = '<span class="label label-primary">' . $this->lang->line('xin_in_progress') . '</span>';
            } elseif ($r->status == 2) {
                $status = '<span class="label label-success">' . $this->lang->line('xin_completed') . '</span>';
            } elseif ($r->status == 3) {
                $status = '<span class="label label-danger">' . $this->lang->line('xin_project_cancelled') . '</span>';
            } else {
                $status = '<span class="label label-danger">' . $this->lang->line('xin_project_hold') . '</span>';
            }

            // priority
            if ($r->priority == 1) {
                $priority = '<span class="label label-danger">' . $this->lang->line('xin_highest') . '</span>';
            } elseif ($r->priority == 2) {
                $priority = '<span class="label label-danger">' . $this->lang->line('xin_high') . '</span>';
            } elseif ($r->priority == 3) {
                $priority = '<span class="label label-primary">' . $this->lang->line('xin_normal') . '</span>';
            } else {
                $priority = '<span class="label label-success">' . $this->lang->line('xin_low') . '</span>';
            }

            //assigned user
            if ($r->assigned_to == '') {
                $ol = $this->lang->line('xin_not_assigned');
            } else {
                $ol = '';
                foreach (explode(',', $r->assigned_to) as $desig_id) {
                    $assigned_to = $this->Xin_model->read_user_info($desig_id);
                    if (!is_null($assigned_to)) {

                        $assigned_name = $assigned_to[0]->first_name . ' ' . $assigned_to[0]->last_name;
                        if ($assigned_to[0]->profile_picture != '' && $assigned_to[0]->profile_picture != 'no file') {
                            $ol .= '<a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="' . $assigned_name . '"><span class="avatar box-32"><img src="' . base_url() . 'uploads/profile/' . $assigned_to[0]->profile_picture . '" class="user-image-hr" alt=""></span></a>';
                        } else {
                            if ($assigned_to[0]->gender == 'Male') {
                                $de_file = base_url() . 'uploads/profile/default_male.jpg';
                            } else {
                                $de_file = base_url() . 'uploads/profile/default_female.jpg';
                            }
                            $ol .= '<a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="' . $assigned_name . '"><span class="avatar box-32"><img src="' . $de_file . '" class="user-image-hr" alt=""></span></a>';
                        }
                    } else {
                        $ol .= '';
                    }
                }
                $ol .= '';
            }
            if (in_array('316', $role_resources_ids)) { //edit
                $edit = '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_edit') . '"><button type="button" class="btn icon-btn btn-xs btn-default waves-effect waves-light"  data-toggle="modal" data-target=".edit-modal-data"  data-project_id="' . $r->project_id . '"><span class="fa fa-pencil"></span></button></span>';
                $add_users = '<span type="button" data-toggle="modal" data-target=".edit-modal-data"  data-project_id="' . $r->project_id . '"><span class="fa fa-plus"></span></span>';
            } else {
                $edit = '';
                $add_users = '';
            }
            if (in_array('317', $role_resources_ids)) { // delete
                $delete = '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_delete') . '"><button type="button" class="btn icon-btn btn-xs btn-danger waves-effect waves-light delete" data-toggle="modal" data-target=".delete-modal" data-record-id="' . $r->project_id . '"><span class="fa fa-trash"></span></button></span>';
            } else {
                $delete = '';
            }
            $client_id = $this->Clients_model->read_client_info($r->client_id);
            if (!is_null($client_id)) {
                $client_name = $client_id[0]->name;
            } else {
                $client_name = '--';
            }

            $new_time = $this->Xin_model->actual_hours_timelog($r->project_id);
            $project_summary = '<a href="' . site_url() . 'admin/project/detail/' . $r->project_id . '">' . $r->title . '</a><br><small>' . $this->lang->line('xin_project_client') . ': ' . $client_name . '</small><br><small>' . $this->lang->line('xin_project_budget_hrs') . ': ' . $r->budget_hours . '</small><br><small>' . $this->lang->line('xin_project_actual_hrs') . ': ' . $new_time . '</small>';

            $project_date = $this->lang->line('xin_start_date') . ': ' . $psdate . '<br>' . $this->lang->line('xin_end_date') . ': ' . $pedate;
            // progress
            $project_progress = $pbar . $status;
            $project_no = '<a href="' . site_url() . 'admin/project/detail/' . $r->project_id . '">' . $r->project_no . '</a>';
            $combhr = $edit . $delete;
            $data[] = array(
                $combhr,
                $project_no,
                $project_summary,
                $priority,
                $ol . $add_users,
                $project_date,
                $project_progress,

            );
            // } //}
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $project->num_rows(),
            "recordsFiltered" => $project->num_rows(),
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function read()
    {
        $data['title'] = $this->Xin_model->site_title();
        $id = $this->input->get('project_id');
        $result = $this->Project_model->read_project_information($id);
        $result2 = $this->Clients_model->read_client_info($result[0]->client_id);
        if (!is_null($result2)) {
            $client_name = $result2[0]->name;
        } else {
            $client_name = '--';
        }
        $data = array(
            'project_id' => $result[0]->project_id,
            'title' => $result[0]->title,
            'client_id' => $result[0]->client_id,
            'client_name' => $client_name,
            'start_date' => $result[0]->start_date,
            'end_date' => $result[0]->end_date,
            'company_id' => $result[0]->company_id,
            'priority' => $result[0]->priority,
            'summary' => $result[0]->summary,
            'project_no' => $result[0]->project_no,
            'budget_hours' => $result[0]->budget_hours,
            'assigned_to' => $result[0]->assigned_to,
            'description' => $result[0]->description,
            'project_progress' => $result[0]->project_progress,
            'status' => $result[0]->status,
            'all_clients' => $this->Clients_model->get_all_clients(),
            'all_employees' => $this->Xin_model->all_employees(),
            'all_companies' => $this->Xin_model->get_companies(),
        );
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view('admin/project/dialog_project', $data);
        } else {
            redirect('admin/');
        }
    }
    public function get_scrumboard_task()
    {
        $data['title'] = $this->Xin_model->site_title();
        $task_status = $this->input->get('task_status');
        //$result = $this->Project_model->read_project_information($id);
        $data = array(
            'task_status' => $task_status,
            'all_projects' => $this->Project_model->get_all_projects(),
            'all_employees' => $this->Xin_model->all_employees(),
            'all_companies' => $this->Xin_model->get_companies(),
        );
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view('admin/project/dialog_scrumboard_task', $data);
        } else {
            redirect('admin/');
        }
    }
    public function get_scrumboard_project()
    {
        $data['title'] = $this->Xin_model->site_title();
        $project_status = $this->input->get('project_status');
        $data = array(
            'project_status' => $project_status,
            'all_employees' => $this->Xin_model->all_employees(),
            'all_companies' => $this->Xin_model->get_companies(),
            'all_clients' => $this->Clients_model->get_all_clients(),
        );
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view('admin/project/dialog_scrumboard_project', $data);
        } else {
            redirect('admin/');
        }
    }
    // Validate and add info in database
    public function add_scrum_board_task()
    {

        if ($this->input->post('add_type') == 'task') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
            $description = $this->input->post('description');

            $st_date = strtotime($start_date);
            $ed_date = strtotime($end_date);
            $qt_description = htmlspecialchars(addslashes($description), ENT_QUOTES);

            /* Server side PHP input validation */
            if ($this->input->post('company_id') === '') {
                $Return['error'] = $this->lang->line('error_company_field');
            } elseif ($this->input->post('task_name') === '') {
                $Return['error'] = $this->lang->line('xin_error_task_name');
            } elseif ($this->input->post('start_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_start_date');
            } elseif ($this->input->post('end_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_end_date');
            } elseif ($st_date > $ed_date) {
                $Return['error'] = $this->lang->line('xin_error_start_end_date');
            } elseif ($this->input->post('task_hour') === '') {
                $Return['error'] = $this->lang->line('xin_error_task_hour');
            } elseif ($this->input->post('project_id') === '') {
                $Return['error'] = $this->lang->line('xin_error_project_field');
            } elseif ($this->input->post('assigned_to') === '') {
                $Return['error'] = $this->lang->line('xin_error_task_assigned_user');
            }

            if ($Return['error'] != '') {
                $this->output($Return);
            }

            $assigned_ids = implode(',', $this->input->post('assigned_to'));
            // get company name by project id
            $co_info = $this->Project_model->read_project_information($this->input->post('project_id'));

            $data = array(
                'project_id' => $this->input->post('project_id'),
                'company_id' => $this->input->post('company_id'),
                'created_by' => $this->input->post('user_id'),
                'task_name' => $this->input->post('task_name'),
                'assigned_to' => $assigned_ids,
                'start_date' => $this->input->post('start_date'),
                'end_date' => $this->input->post('end_date'),
                'task_hour' => $this->input->post('task_hour'),
                'task_progress' => '0',
                'task_status' => $this->input->post('task_status'),
                'is_notify' => '1',
                'description' => $qt_description,
                'created_at' => date('Y-m-d h:i:s'),
            );
            $result = $this->Timesheet_model->add_task_record($data);

            if ($result == true) {
                $row = $this->db->select("*")->limit(1)->order_by('task_id', "DESC")->get("xin_tasks")->row();
                $Return['result'] = $this->lang->line('xin_success_task_added');
                $Return['re_last_id'] = $row->task_id;
                //get setting info
                $setting = $this->Xin_model->read_setting_info(1);
                if ($setting[0]->enable_email_notification == 'yes') {

                    $this->email->set_mailtype("html");
                    $to_email = array();
                    foreach ($this->input->post('assigned_to') as $p_employee) {

                        // assigned by
                        $user_info = $this->Xin_model->read_user_info($this->input->post('user_id'));
                        $full_name = $user_info[0]->first_name . ' ' . $user_info[0]->last_name;

                        // assigned to
                        $user_to = $this->Xin_model->read_user_info($p_employee);
                        //get company info
                        $cinfo = $this->Xin_model->read_company_setting_info(1);
                        //get email template
                        $template = $this->Xin_model->read_email_template(14);

                        $subject = $template[0]->subject . ' - ' . $cinfo[0]->company_name;
                        $logo = base_url() . 'uploads/logo/signin/' . $cinfo[0]->sign_in_logo;

                        $message = '
			<div style="background:#f6f6f6;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:12px;margin:0;padding:0;padding: 20px;">
			<img src="' . $logo . '" title="' . $cinfo[0]->company_name . '"><br>' . str_replace(array("{var site_name}", "{var site_url}", "{var task_name}", "{var task_assigned_by}"), array($cinfo[0]->company_name, site_url(), $this->input->post('task_name'), $full_name), htmlspecialchars_decode(stripslashes($template[0]->message))) . '</div>';

                        hrsale_mail($cinfo[0]->email, $cinfo[0]->company_name, $user_info[0]->email, $subject, $message);
                    }
                }
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }
    // Validate and add info in database
    public function add_project()
    {

        if ($this->input->post('add_type') == 'project') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            /* Server side PHP input validation */
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
            $description = $this->input->post('description');
            $st_date = strtotime($start_date);
            $ed_date = strtotime($end_date);
            $qt_description = htmlspecialchars(addslashes($description), ENT_QUOTES);
            $assigned_to = $this->input->post('assigned_to');

            if ($this->input->post('title') === '') {
                $Return['error'] = $this->lang->line('xin_error_title');
            } elseif ($this->input->post('project_no') === '') {
                $Return['error'] = $this->lang->line('xin_project_projectno_field_error');
            } elseif ($this->input->post('client_id') === '') {
                $Return['error'] = $this->lang->line('xin_error_client_name');
            } elseif ($this->input->post('company_id') === '') {
                $Return['error'] = $this->lang->line('xin_error_company');
            } elseif ($this->input->post('start_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_start_date');
            } elseif ($this->input->post('end_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_end_date');
            } elseif ($st_date > $ed_date) {
                $Return['error'] = $this->lang->line('xin_error_start_end_date');
            } elseif ($this->input->post('budget_hours') === '') {
                $Return['error'] = $this->lang->line('xin_project_budget_hrs_field_error');
            } elseif (empty($assigned_to)) {
                $Return['error'] = $this->lang->line('xin_error_project_manager');
            } elseif ($this->input->post('summary') === '') {
                $Return['error'] = $this->lang->line('xin_error_summary');
            }

            if ($Return['error'] != '') {
                $this->output($Return);
            }

            $assigned_ids = implode(',', $this->input->post('assigned_to'));
            $employee_ids = $assigned_ids;

            $data = array(
                'title' => $this->input->post('title'),
                'project_no' => $this->input->post('project_no'),
                'client_id' => $this->input->post('client_id'),
                'company_id' => $this->input->post('company_id'),
                'start_date' => $this->input->post('start_date'),
                'end_date' => $this->input->post('end_date'),
                'summary' => $this->input->post('summary'),
                'budget_hours' => $this->input->post('budget_hours'),
                'priority' => $this->input->post('priority'),
                'assigned_to' => $employee_ids,
                'description' => $qt_description,
                'project_progress' => '0',
                'status' => '0',
                'is_notify' => '1',
                'added_by' => $this->input->post('user_id'),
                'created_at' => date('d-m-Y'),

            );
            $result = $this->Project_model->add($data);
            if ($result == true) {

                $row = $this->db->select("*")->limit(1)->order_by('project_id', "DESC")->get("xin_projects")->row();
                $Return['result'] = $this->lang->line('xin_success_add_project');
                $Return['re_last_id'] = $row->project_id;
                //get setting info
                $setting = $this->Xin_model->read_setting_info(1);
                if ($setting[0]->enable_email_notification == 'yes') {

                    $this->email->set_mailtype("html");

                    $to_email = array();
                    foreach ($this->input->post('assigned_to') as $p_employee) {

                        $user_info = $this->Xin_model->read_user_info($p_employee);
                        //get company info
                        $cinfo = $this->Xin_model->read_company_setting_info(1);
                        //get email template
                        $template = $this->Xin_model->read_email_template(3);

                        $subject = $template[0]->subject . ' - ' . $cinfo[0]->company_name;
                        $logo = base_url() . 'uploads/logo/signin/' . $cinfo[0]->sign_in_logo;

                        $p_date = $this->Xin_model->set_date_format($start_date);

                        $message = '
				<div style="background:#f6f6f6;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:12px;margin:0;padding:0;padding: 20px;">
				<img src="' . $logo . '" title="' . $cinfo[0]->company_name . '"><br>' . str_replace(array("{var site_name}", "{var name}", "{var project_name}", "{var project_start_date}"), array($cinfo[0]->company_name, 'User', $this->input->post('title'), $p_date), html_entity_decode(stripslashes($template[0]->message))) . '</div>';

                        hrsale_mail($cinfo[0]->email, $cinfo[0]->company_name, $user_info[0]->email, $subject, $message);
                    }
                }
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }
    public function add_project_n()
    {
            $projecttype=$this->input->post('projecttype');
            $title = $this->input->post('title');
            $project_no = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 5)), 0, 5);
            $client_id = $this->input->post('client_id');
            $company_id = 1;
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
            $total_days = floor((strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24));
            $summary = '';
            $budget_hours = $total_days*8;
            $priority = $this->input->post('priority');
            $assigned_to = $this->input->post('assigned_to');
            $description = $this->input->post('description');
            $project_progress = '0';
            $status = 0;
            $is_notify = 1;
            $added_by = 1;
            $created_at = date('d-m-Y');
            $software_Budget = $this->input->post('software_Budget');
            $instalment = $this->input->post('instalment');
            $hardware_Budget = $this->input->post('hardware_Budget');
            $hardware_Summary = $this->input->post('hardware_Summary');
            $serviceEnabled = $this->input->post('serviceEnabled');
            
            $service_status = ($serviceEnabled == 'on') ? 1 : 0;
            
            $Service_type = $Service_amount = $Service_Increment_Date = '';
            if ($service_status) {
                $Service_type = $this->input->post('Service_type');
                $Service_amount = $this->input->post('Service_amount');
                $Service_Increment_Date = $this->input->post('Service_Increment_Date');
            }
            
            $data = array(
                'title' => $title,
                'project_id' => $project_no,
                'project_no' => $project_no,
                'client_id' => $client_id,
                'company_id' => $company_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'summary' => $summary,
                'budget_hours' => $budget_hours,
                'priority' => $priority,
                'assigned_to' => $assigned_to,
                'description' => $description,
                'project_progress' => $project_progress,
                'status' => $status,
                'is_notify' => $is_notify,
                'added_by' => $added_by,
                'created_at' => $created_at,
                'software_Budget' => $software_Budget,
                'instalment' => $instalment,
                'hardware_Budget' => $hardware_Budget,
                'hardware_Summary' => $hardware_Summary,
                'service_status' => $service_status,
                'Service_type' => $Service_type,
                'Service_amount' => $Service_amount,
                'project_note' =>'',
                'Service_Increment_Date' => $Service_Increment_Date
            );
            $r =$this->db->insert('xin_projects', $data);
            if ($r) {
                $project_id = $this->db->insert_id();
                $soft_intmnt_dates=json_encode($this->input->post('soft_intmnt_dates'));
                $soft_intmnt_prements=json_encode($this->input->post('soft_intmnt_prements'));
                $soft_intmnt_status=json_encode($this->input->post('soft_intmnt_status'));
                $soft_intmnt_takes=0;
                $soft_prement_status=0;
                $hardware_prement_status=0;
                $if_notify=1;
                $intmnt_dates=$this->input->post('soft_intmnt_dates');
                $notify_date_start = date('Y-m-d', strtotime('-3 day', strtotime($intmnt_dates[0])));
                $update_at=date('Y-m-d');
                $data = array(
                    'project_id' => $project_id,
                    'clint_id' => $client_id,
                    'software_budget' => $software_Budget,
                    'hardware_budget' => $hardware_Budget,
                    'total_budget' => $hardware_Budget+$software_Budget,
                    'soft_total_installment' => $instalment,
                    'soft_intmnt_dates' => $soft_intmnt_dates,
                    'soft_intmnt_prements' => $soft_intmnt_prements,
                    'soft_intmnt_status' => $soft_intmnt_status,
                    'soft_intmnt_takes' => $soft_intmnt_takes,
                    'soft_prement_status' => $soft_prement_status,
                    'hardware_prement_status' => $hardware_prement_status,
                    'if_notify' => $if_notify,
                    'next_installment_date' => $intmnt_dates[0],
                    'installment_deu' => 0,
                    'notify_date_start' => $notify_date_start,
                    'Payment_Received' => 0,
                    'Remaining_Payment' => $software_Budget+$hardware_Budget,
                    'Payment_Received_percent' => 0,
                    'Remaining_Payment_percent' =>100,
                    'update_at' => $update_at
                );
                $res =$this->db->insert('project_account', $data);
                if ($res) {
                    $result=true;
                }else {
                    $result=false;
                }   
            } else {
                $result=false;
            }          
            echo json_encode($result);
    }

    // Validate and add info in database
    public function add_scrum_board_project()
    {
        if ($this->input->post('add_type') == 'project') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();
            /* Server side PHP input validation */
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
            $description = $this->input->post('description');
            $st_date = strtotime($start_date);
            $ed_date = strtotime($end_date);
            $qt_description = htmlspecialchars(addslashes($description), ENT_QUOTES);
            $assigned_to = $this->input->post('assigned_to');

            if ($this->input->post('title') === '') {
                $Return['error'] = $this->lang->line('xin_error_title');
            } elseif ($this->input->post('project_no') === '') {
                $Return['error'] = $this->lang->line('xin_project_projectno_field_error');
            } elseif ($this->input->post('client_id') === '') {
                $Return['error'] = $this->lang->line('xin_error_client_name');
            } elseif ($this->input->post('company_id') === '') {
                $Return['error'] = $this->lang->line('xin_error_company');
            } elseif ($this->input->post('start_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_start_date');
            } elseif ($this->input->post('end_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_end_date');
            } elseif ($st_date > $ed_date) {
                $Return['error'] = $this->lang->line('xin_error_start_end_date');
            } elseif ($this->input->post('budget_hours') === '') {
                $Return['error'] = $this->lang->line('xin_project_budget_hrs_field_error');
            } elseif (empty($assigned_to)) {
                $Return['error'] = $this->lang->line('xin_error_project_manager');
            } elseif ($this->input->post('summary') === '') {
                $Return['error'] = $this->lang->line('xin_error_summary');
            }

            if ($Return['error'] != '') {
                $this->output($Return);
            }

            $assigned_ids = implode(',', $this->input->post('assigned_to'));
            $employee_ids = $assigned_ids;

            $data = array(
                'title' => $this->input->post('title'),
                'project_no' => $this->input->post('project_no'),
                'client_id' => $this->input->post('client_id'),
                'company_id' => $this->input->post('company_id'),
                'start_date' => $this->input->post('start_date'),
                'end_date' => $this->input->post('end_date'),
                'summary' => $this->input->post('summary'),
                'budget_hours' => $this->input->post('budget_hours'),
                'priority' => $this->input->post('priority'),
                'assigned_to' => $employee_ids,
                'description' => $qt_description,
                'project_progress' => '0',
                'status' => $this->input->post('project_status'),
                'is_notify' => '1',
                'added_by' => $this->input->post('user_id'),
                'created_at' => date('d-m-Y'),

            );
            $result = $this->Project_model->add($data);
            if ($result == true) {

                $row = $this->db->select("*")->limit(1)->order_by('project_id', "DESC")->get("xin_projects")->row();
                $Return['result'] = $this->lang->line('xin_success_add_project');
                $Return['re_last_id'] = $row->project_id;
                //get setting info
                $setting = $this->Xin_model->read_setting_info(1);
                if ($setting[0]->enable_email_notification == 'yes') {

                    $this->email->set_mailtype("html");

                    $to_email = array();
                    foreach ($this->input->post('assigned_to') as $p_employee) {

                        $user_info = $this->Xin_model->read_user_info($p_employee);
                        //get company info
                        $cinfo = $this->Xin_model->read_company_setting_info(1);
                        //get email template
                        $template = $this->Xin_model->read_email_template(3);

                        $subject = $template[0]->subject . ' - ' . $cinfo[0]->company_name;
                        $logo = base_url() . 'uploads/logo/signin/' . $cinfo[0]->sign_in_logo;

                        $p_date = $this->Xin_model->set_date_format($start_date);

                        $message = '
				<div style="background:#f6f6f6;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:12px;margin:0;padding:0;padding: 20px;">
				<img src="' . $logo . '" title="' . $cinfo[0]->company_name . '"><br>' . str_replace(array("{var site_name}", "{var name}", "{var project_name}", "{var project_start_date}"), array($cinfo[0]->company_name, 'User', $this->input->post('title'), $p_date), html_entity_decode(stripslashes($template[0]->message))) . '</div>';

                        hrsale_mail($cinfo[0]->email, $cinfo[0]->company_name, $user_info[0]->email, $subject, $message);
                    }
                }
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }

    // Validate and update info in database
    public function update()
    {

        if ($this->input->post('edit_type') == 'project') {

            $id = $this->uri->segment(4);

            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            /* Server side PHP input validation */
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
            $description = $this->input->post('description');
            $st_date = strtotime($start_date);
            $ed_date = strtotime($end_date);
            $qt_description = htmlspecialchars(addslashes($description), ENT_QUOTES);
            $assigned_to = $this->input->post('assigned_to');

            if ($this->input->post('title') === '') {
                $Return['error'] = $this->lang->line('xin_error_title');
            } elseif ($this->input->post('project_no') === '') {
                $Return['error'] = $this->lang->line('xin_project_projectno_field_error');
            } elseif ($this->input->post('start_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_start_date');
            } elseif ($this->input->post('end_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_end_date');
            } elseif ($st_date >= $ed_date) {
                $Return['error'] = $this->lang->line('xin_error_start_end_date');
            } elseif ($this->input->post('budget_hours') === '') {
                $Return['error'] = $this->lang->line('xin_project_budget_hrs_field_error');
            } elseif (empty($assigned_to)) {
                $Return['error'] = $this->lang->line('xin_error_project_manager');
            } elseif ($this->input->post('summary') === '') {
                $Return['error'] = $this->lang->line('xin_error_summary');
            }

            if ($Return['error'] != '') {
                $this->output($Return);
            }

            if (null != $this->input->post('assigned_to')) {
                $assigned_ids = implode(',', $this->input->post('assigned_to'));
                $employee_ids = $assigned_ids;
            } else {
                $employee_ids = 'all-employees';
            }

            $data = array(
                'title' => $this->input->post('title'),
                'project_no' => $this->input->post('project_no'),
                'start_date' => $this->input->post('start_date'),
                'end_date' => $this->input->post('end_date'),
                'summary' => $this->input->post('summary'),
                'priority' => $this->input->post('priority'),
                'budget_hours' => $this->input->post('budget_hours'),
                'assigned_to' => $employee_ids,
                'description' => $qt_description,
                'project_progress' => $this->input->post('progres_val'),
                'status' => $this->input->post('status'),
            );

            $result = $this->Project_model->update_record($data, $id);

            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_success_update_project');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }

    // Validate and update info in database
    public function update_status()
    {

        if ($this->input->post('type') == 'update_status') {

            $id = $this->input->post('project_id');

            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            /* Server side PHP input validation */
            $data = array(
                'priority' => $this->input->post('priority'),
                'project_progress' => $this->input->post('progres_val'),
                'status' => $this->input->post('status'),
            );

            $result = $this->Project_model->update_record($data, $id);

            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_success_update_project');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }

    // Validate and update info in database // assign_ticket
    public function assign_project()
    {

        if ($this->input->post('type') == 'project_user') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            if (null != $this->input->post('assigned_to')) {
                $assigned_ids = implode(',', $this->input->post('assigned_to'));
                $employee_ids = $assigned_ids;
            } else {
                $employee_ids = '';
            }

            $data = array(
                'assigned_to' => $employee_ids,
            );
            $id = $this->input->post('project_id');
            $result = $this->Project_model->update_record($data, $id);

            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_project_employees_updated');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }

    // update task user > task details
    public function project_users()
    {

        $data['title'] = $this->Xin_model->site_title();
        $id = $this->uri->segment(3);

        $data = array(
            'project_id' => $id,
            'all_employees' => $this->Xin_model->all_employees(),
        );
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view("project/get_project_users", $data);
        } else {
            redirect('admin/');
        }
        // Datatables Variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
    }

    public function discussion_list()
    {

        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }

        $data['title'] = $this->Xin_model->site_title();
        //$id = $this->input->get('ticket_id');
        $id = $this->uri->segment(4);

        $ses_user = $this->Xin_model->read_user_info($session['user_id']);
        $this->load->view("admin/project/project_details", $data);
        // Datatables Variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $discussion = $this->Project_model->get_discussion($id);

        $data = array();

        foreach ($discussion->result() as $r) {

            // get user > employee_
            $employee = $this->Xin_model->read_user_info($r->user_id);
            // employee full name
            if (!is_null($employee)) {
                $employee_name = $employee[0]->first_name . ' ' . $employee[0]->last_name;
                // get designation
                $_designation = $this->Designation_model->read_designation_information($employee[0]->designation_id);
                if (!is_null($_designation)) {
                    $designation_name = $_designation[0]->designation_name;
                } else {
                    $designation_name = '--';
                }

                // profile picture
                if ($employee[0]->profile_picture != '' && $employee[0]->profile_picture != 'no file') {
                    $u_file = base_url() . 'uploads/profile/' . $employee[0]->profile_picture;
                } else {
                    if ($employee[0]->gender == 'Male') {
                        $u_file = base_url() . 'uploads/profile/default_male.jpg';
                    } else {
                        $u_file = base_url() . 'uploads/profile/default_female.jpg';
                    }
                }
            } else {
                $employee_name = '--';
                $designation_name = '--';
                $u_file = $u_file;
            }
            // created at
            $created_at = date('h:i A', strtotime($r->created_at));
            $_date = explode(' ', $r->created_at);
            $date = $this->Xin_model->set_date_format($_date[0]);
            //
            if ($ses_user[0]->user_role_id == 1) {
                $link = '<a class="c-user text-black" href="' . site_url() . 'admin/employees/detail/' . $r->user_id . '"><span class="underline">' . $employee_name . ' (' . $designation_name . ')</span></a>';
            } else {
                $link = '<span class="underline">' . $employee_name . ' (' . $designation_name . ')</span>';
            }

            if ($r->attachment_file != '' && $r->attachment_file != 'no_file') {
                $at_file = '<a data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_download') . '" href="' . site_url() . 'admin/download?type=project/discussion&filename=' . $r->attachment_file . '"> <i class="fa fa-download"></i> </a>';
            } else {
                $at_file = '';
            }

            $function = '<div class="c-item">
					<div class="media">
						<div class="media-left">
							<div class="avatar box-48">
							<img class="user-image-hr-prj d-block ui-w-30 rounded-circle" src="' . $u_file . '">
							</div>
						</div>
						<div class="media-body">
							<div class="mb-0-5">
								' . $link . '
								<span class="font-90 text-muted">' . $date . ' ' . $created_at . '</span>
							</div>
							<div class="c-text">' . $r->message . '<br> ' . $at_file . '</div>
						</div>
					</div>
				</div>';

            $data[] = array(
                $function,
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $discussion->num_rows(),
            "recordsFiltered" => $discussion->num_rows(),
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    // Validate and add info in database
    public function set_discussion()
    {

        if ($this->input->post('add_type') == 'set_discussion') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            /* Server side PHP input validation */
            if ($this->input->post('xin_message') === '') {
                $Return['error'] = $this->lang->line('xin_project_message');
            }
            $xin_message = $this->input->post('xin_message');
            $qt_xin_message = htmlspecialchars(addslashes($xin_message), ENT_QUOTES);

            if ($_FILES['attachment_discussion']['size'] == 0) {
                $fname = 'no_file';
            } else {
                // is file upload
                if (is_uploaded_file($_FILES['attachment_discussion']['tmp_name'])) {
                    //checking image type
                    $allowed = array('png', 'jpg', 'gif', 'jpeg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'gzip', 'ppt');
                    $filename = $_FILES['attachment_discussion']['name'];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);

                    if (in_array($ext, $allowed)) {
                        $tmp_name = $_FILES["attachment_discussion"]["tmp_name"];
                        $attachment_file = "uploads/project/discussion/";
                        // basename() may prevent filesystem traversal attacks;
                        // further validation/sanitation of the filename may be appropriate
                        $name = basename($_FILES["attachment_discussion"]["name"]);
                        $newfilename = 'discussion_' . round(microtime(true)) . '.' . $ext;
                        move_uploaded_file($tmp_name, $attachment_file . $newfilename);
                        $fname = $newfilename;
                    } else {
                        $Return['error'] = $this->lang->line('xin_error_project_file');
                    }
                }
            }

            if ($Return['error'] != '') {
                $this->output($Return);
            }

            $data = array(
                'message' => $qt_xin_message,
                'attachment_file' => $fname,
                'project_id' => $this->input->post('discussion_project_id'),
                'user_id' => $this->input->post('user_id'),
                'created_at' => date('d-m-Y h:i:s'),
            );
            $result = $this->Project_model->add_discussion($data);
            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_success_project_message_added');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }

    public function bug_list()
    {

        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }

        $data['title'] = $this->Xin_model->site_title();
        $id = $this->uri->segment(4);

        $ses_user = $this->Xin_model->read_user_info($session['user_id']);
        $this->load->view("admin/project/project_details", $data);
        // Datatables Variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $bug = $this->Project_model->get_bug($id);

        $data = array();

        foreach ($bug->result() as $r) {

            // get user > employee_
            $employee = $this->Xin_model->read_user_info($r->user_id);
            // employee full name
            if (!is_null($employee)) {
                $employee_name = $employee[0]->first_name . ' ' . $employee[0]->last_name;
                // get designation
                $_designation = $this->Designation_model->read_designation_information($employee[0]->designation_id);
                if (!is_null($_designation)) {
                    $designation_name = $_designation[0]->designation_name;
                } else {
                    $designation_name = '--';
                }

                // profile picture
                if ($employee[0]->profile_picture != '' && $employee[0]->profile_picture != 'no file') {
                    $u_file = base_url() . 'uploads/profile/' . $employee[0]->profile_picture;
                } else {
                    if ($employee[0]->gender == 'Male') {
                        $u_file = base_url() . 'uploads/profile/default_male.jpg';
                    } else {
                        $u_file = base_url() . 'uploads/profile/default_female.jpg';
                    }
                }
            } else {
                $employee_name = '--';
                $designation_name = '--';
                $u_file = '--';
            }
            // created at
            $created_at = date('h:i A', strtotime($r->created_at));
            $_date = explode(' ', $r->created_at);
            $date = $this->Xin_model->set_date_format($_date[0]);
            //
            if ($ses_user[0]->user_role_id == 1) {
                $link = '<a class="c-user text-black" href="' . site_url() . 'admin/employees/detail/' . $r->user_id . '"><span class="underline">' . $employee_name . ' (' . $designation_name . ')</span></a>';
            } else {
                $link = '<span class="underline">' . $employee_name . ' (' . $designation_name . ')</span>';
            }

            if ($r->attachment_file != '' && $r->attachment_file != 'no_file') {
                $at_file = '<a data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_download') . '" href="' . site_url() . 'admin/download?type=project/bug&filename=' . $r->attachment_file . '"> <i class="fa fa-download"></i> </a>';
            } else {
                $at_file = '';
            }

            $dlink = '<div class="media-right">
							<div class="c-rating">
							<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_update_status') . '"><button type="button" class="btn icon-btn btn-xs btn-default waves-effect waves-light"  data-toggle="modal" data-target=".view-modal-data"  data-bug_id="' . $r->bug_id . '"><i class="fa fa-pencil"></i></button></span>
							<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_delete') . '">
								<a class="btn icon-btn btn-xs btn-danger delete" href="#" data-toggle="modal" data-target=".delete-modal" data-record-id="' . $r->bug_id . '">
			  <span class="fa fa-trash m-r-0-5"></span></a></span>
							</div>
						</div>';

            if ($r->status == 0) {
                $status = '<select name="status" id="status" class="bug_status" data-bug-id="' . $r->bug_id . '">
							<option value="0" selected="selected">' . $this->lang->line('xin_pending') . '</option>
							<option value="1">' . $this->lang->line('xin_project_status_solved') . '</option>
							</select>';
                $st_tag = '<span class="badge badge-warning">' . $this->lang->line('xin_pending') . '</span>';
            } else {
                $status = '<select name="status" id="status" class="bug_status" data-bug-id="' . $r->bug_id . '">
							<option value="0">' . $this->lang->line('xin_pending') . '</option>
							<option value="1" selected="selected">' . $this->lang->line('xin_project_status_solved') . '</option>
							</select>';
                $st_tag = '<span class="badge badge-success">' . $this->lang->line('xin_project_status_solved') . '</span>';
            }
            $function = '<div class="c-item">
					<div class="media">
						<div class="media-left">
							<div class="avatar box-48">
							<img class="user-image-hr-prj d-block ui-w-30 rounded-circle" src="' . $u_file . '">
							</div>
						</div>
						<div class="media-body">
							<div class="mb-0-5">
								' . $link . '
								<span class="font-90 text-muted">' . $date . ' ' . $created_at . ' &nbsp; ' . $st_tag . '
							</div>
							<div class="c-text">' . $r->title . '<br> ' . $at_file . '</div>
						</div>
						' . $dlink . '
					</div>
				</div>
				';

            $data[] = array(
                $function,
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $bug->num_rows(),
            "recordsFiltered" => $bug->num_rows(),
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    // Validate and add info in database
    public function set_bug()
    {

        if ($this->input->post('add_type') == 'set_bug') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            /* Server side PHP input validation */
            if ($this->input->post('title') === '') {
                $Return['error'] = $this->lang->line('xin_error_project_bug_title');
            }
            $title = $this->input->post('title');
            $qt_title = htmlspecialchars(addslashes($title), ENT_QUOTES);

            if ($_FILES['attachment']['size'] == 0) {
                $fname = 'no_file';
            } else {
                // is file upload
                if (is_uploaded_file($_FILES['attachment']['tmp_name'])) {
                    //checking image type
                    $allowed = array('png', 'jpg', 'gif', 'jpeg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'gzip', 'ppt');
                    $filename = $_FILES['attachment']['name'];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);

                    if (in_array($ext, $allowed)) {
                        $tmp_name = $_FILES["attachment"]["tmp_name"];
                        $attachment_file = "uploads/project/bug/";
                        // basename() may prevent filesystem traversal attacks;
                        // further validation/sanitation of the filename may be appropriate
                        $name = basename($_FILES["attachment"]["name"]);
                        $newfilename = 'bug_' . round(microtime(true)) . '.' . $ext;
                        move_uploaded_file($tmp_name, $attachment_file . $newfilename);
                        $fname = $newfilename;
                    } else {
                        $Return['error'] = $this->lang->line('xin_error_project_file');
                    }
                }
            }

            if ($Return['error'] != '') {
                $this->output($Return);
            }

            $data = array(
                'title' => $qt_title,
                'attachment_file' => $fname,
                'project_id' => $this->input->post('bug_project_id'),
                'user_id' => $this->input->post('user_id'),
                'created_at' => date('d-m-Y h:i:s'),
            );
            $result = $this->Project_model->add_bug($data);
            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_success_project_bug_added');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }

    // Validate and add info in database
    public function add_attachment()
    {

        if ($this->input->post('add_type') == 'dfile_attachment') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            /* Server side PHP input validation */
            if ($this->input->post('file_name') === '') {
                $Return['error'] = $this->lang->line('xin_error_project_file_title');
            } elseif ($_FILES['attachment_file']['size'] == 0) {
                $Return['error'] = $this->lang->line('xin_error_task_file');
            } elseif ($this->input->post('file_description') === '') {
                $Return['error'] = $this->lang->line('xin_error_task_file_description');
            }
            $description = $this->input->post('file_description');
            $file_description = htmlspecialchars(addslashes($description), ENT_QUOTES);

            if ($Return['error'] != '') {
                $this->output($Return);
            }

            // is file upload
            if (is_uploaded_file($_FILES['attachment_file']['tmp_name'])) {
                //checking image type
                $allowed = array('png', 'jpg', 'gif', 'jpeg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'gzip', 'ppt');
                $filename = $_FILES['attachment_file']['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);

                if (in_array($ext, $allowed)) {
                    $tmp_name = $_FILES["attachment_file"]["tmp_name"];
                    $attachment_file = "uploads/project/files/";
                    // basename() may prevent filesystem traversal attacks;
                    // further validation/sanitation of the filename may be appropriate
                    $name = basename($_FILES["attachment_file"]["name"]);
                    $newfilename = 'project_' . round(microtime(true)) . '.' . $ext;
                    move_uploaded_file($tmp_name, $attachment_file . $newfilename);
                    $fname = $newfilename;
                } else {
                    $Return['error'] = $this->lang->line('xin_error_project_file');
                }
            }
            if ($Return['error'] != '') {
                $this->output($Return);
            }

            $data = array(
                'project_id' => $this->input->post('project_id'),
                'upload_by' => $this->input->post('user_id'),
                'file_title' => $this->input->post('file_name'),
                'file_description' => $file_description,
                'attachment_file' => $fname,
                'created_at' => date('d-m-Y h:i:s'),
            );
            $result = $this->Project_model->add_new_attachment($data);
            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_success_project_file_added');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }

    // attachment list
    public function attachment_list()
    {

        $data['title'] = $this->Xin_model->site_title();
        //$id = $this->input->get('ticket_id');
        $id = $this->uri->segment(4);
        $session = $this->session->userdata('username');
        // Datatables Variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $attachments = $this->Project_model->get_attachments($id);

        $data = array();

        foreach ($attachments->result() as $r) {

            $data[] = array(
                '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_download') . '"><a href="' . site_url() . 'admin/download?type=project/files&filename=' . $r->attachment_file . '"><button type="button" class="btn icon-btn btn-xs btn-default waves-effect waves-light"><span class="fa fa-download"></span></button></a></span><span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_delete') . '"><button type="button" class="btn icon-btn btn-xs btn-danger waves-effect waves-light fidelete" data-toggle="modal" data-target=".delete-modal-file" data-record-id="' . $r->project_attachment_id . '"><span class="fa fa-trash"></span></button></span>',
                $r->file_title,
                $r->file_description,
                $r->created_at,
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $attachments->num_rows(),
            "recordsFiltered" => $attachments->num_rows(),
            "data" => $data,
        );

        echo json_encode($output);
        exit();
    }

    // delete attachment
    public function attachment_delete()
    {
        if ($this->input->post('is_ajax') == '8') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $id = $this->uri->segment(4);
            $Return['csrf_hash'] = $this->security->get_csrf_hash();
            $result = $this->Project_model->delete_attachment_record($id);
            if (isset($id)) {
                $Return['result'] = $this->lang->line('xin_success_project_file_deleted');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
        }
    }

    // Validate and update info in database // add_note
    public function add_note()
    {

        if ($this->input->post('type') == 'add_note') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            $data = array(
                'project_note' => $this->input->post('project_note'),
            );
            $id = $this->input->post('note_project_id');
            $result = $this->Project_model->update_record($data, $id);
            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_success_project_note_updated');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }

    public function task_categories()
    {
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $system = $this->Xin_model->read_setting_info(1);

        $data['title'] = $this->lang->line('xin_task_categories') . ' | ' . $this->Xin_model->site_title();
        $data['breadcrumbs'] = $this->lang->line('xin_task_categories');
        $data['path_url'] = 'task_categories';
        $data['all_companies'] = $this->Xin_model->get_companies();
        $role_resources_ids = $this->Xin_model->user_role_resource();
        if (in_array('45', $role_resources_ids)) {
            if (!empty($session)) {
                $data['subview'] = $this->load->view("admin/project/task_categories", $data, true);
                $this->load->view('admin/layout/layout_main', $data); //page load
            } else {
                redirect('admin/');
            }
        } else {
            redirect('admin/dashboard');
        }
    }
    public function task_categories_list()
    {

        $data['title'] = $this->Xin_model->site_title();
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view("admin/project/task_categories", $data);
        } else {
            redirect('admin/');
        }
        // Datatables Variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $task_categories = $this->Project_model->get_task_categories();
        $role_resources_ids = $this->Xin_model->user_role_resource();
        $data = array();

        foreach ($task_categories->result() as $r) {

            if (in_array('346', $role_resources_ids)) { //edit
                $edit = '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_edit') . '"><button type="button" class="btn icon-btn btn-xs btn-default waves-effect waves-light"  data-toggle="modal" data-target=".edit-modal-data"  data-task_category_id="' . $r->task_category_id . '"><span class="fa fa-pencil"></span></button></span>';
            } else {
                $edit = '';
            }
            if (in_array('347', $role_resources_ids)) { // delete
                $delete = '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_delete') . '"><button type="button" class="btn icon-btn btn-xs btn-danger waves-effect waves-light delete" data-toggle="modal" data-target=".delete-modal" data-record-id="' . $r->task_category_id . '"><span class="fa fa-trash"></span></button></span>';
            } else {
                $delete = '';
            }

            $combhr = $edit . $delete;

            $data[] = array(
                $combhr,
                $r->category_name,
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $task_categories->num_rows(),
            "recordsFiltered" => $task_categories->num_rows(),
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function timelogs_list()
    {
        $data['title'] = $this->Xin_model->site_title();
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view("admin/project/project_timelogs_list", $data);
        } else {
            redirect('admin/');
        }
        // Datatables Variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $user_info = $this->Xin_model->read_user_info($session['user_id']);
        if ($user_info[0]->user_role_id == '1') {
            $timelogs = $this->Project_model->get_all_project_timelogs();
        } else {
            $timelogs = $this->Project_model->get_all_project_employee_timelogs($session['user_id']);
        }
        $role_resources_ids = $this->Xin_model->user_role_resource();
        $data = array();

        foreach ($timelogs->result() as $r) {

            // get user > added by
            $user = $this->Xin_model->read_user_info($r->employee_id);
            // user full name
            if (!is_null($user)) {
                $full_name = $user[0]->first_name . ' ' . $user[0]->last_name;
            } else {
                $full_name = '--';
            }
            $project = $this->Project_model->read_project_information($r->project_id);
            if (!is_null($project)) {
                $project_name = '<a target="_blank" href="' . site_url('admin/project/detail/') . $r->project_id . '">' . $project[0]->title . '</a>';
            } else {
                $project_name = '--';
            }
            $start_date = $this->Xin_model->set_date_format($r->start_date);
            $movement = $r->movement;
            if ($r->status == 0) {
                $status = '<span class="label label-warning">Pending</span>';
            } else if ($r->status == 1) {
                $status = '<span class="label label-success">Approved</span>';
            } else if ($r->status == 2) {
                $status = '<span class="label label-danger">Rejected</span>';
            }else{
                $status = '<span class="label label-danger">error</span>';
            }
            



            $end_date = $this->Xin_model->set_date_format($r->end_date);
            $edit = '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_edit') . '"><button type="button" class="btn icon-btn btn-xs btn-default waves-effect waves-light"  data-toggle="modal" data-target=".edit-modal-timelog-data"  data-timelogs_id="' . $r->timelogs_id . '"><span class="fa fa-pencil"></span></button></span>';
            $delete = '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_delete') . '"><button type="button" class="btn icon-btn btn-xs btn-danger waves-effect waves-light delete" data-toggle="modal" data-target=".delete-modal" data-record-id="' . $r->timelogs_id . '"><span class="fa fa-trash"></span></button></span>';
            if ($user_info[0]->user_role_id == '1') {
                $combhr = $edit . $delete;
            } else {
                $combhr = $edit;
            }

            $data[] = array(
                $project_name,
                $full_name,
                $start_date,
                $r->total_hours,
                $movement,
                $r->timelogs_memo,
                $status,
                $combhr,
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $timelogs->num_rows(),
            "recordsFiltered" => $timelogs->num_rows(),
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function timelogs_report(){
        $data['title'] = 'Timelogs Report'.' | '.$this->Xin_model->site_title();
        $data['breadcrumbs'] = 'Timelogs Report';
        $data['path_url'] = '';
        // $data['all_office_shifts'] = $this->Location_model->all_office_locations();
        $data['subview'] = $this->load->view("admin/project/project_timelogs_report", $data, true);
        $this->load->view('admin/layout/layout_main', $data); //page load

    }
    public function logreport(){
        $data['first_date']=$this->input->post('first_date');
        $data['second_date']=$this->input->post('second_date');
        $sql=$this->input->post('sql');
        $data['emp_id']= explode(',', trim($sql));
        $data['type']=$this->input->post('type');
        $data['status']=$this->input->post('status');
        echo $this->load->view("admin/project/logreport", $data, true);
    }

    public function project_timelogs_list()
    {

        $data['title'] = $this->Xin_model->site_title();
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view("admin/project/project_details", $data);
        } else {
            redirect('admin/');
        }
        // Datatables Variables
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));

        $id = $this->uri->segment(4);
        $timelogs = $this->Project_model->get_project_timelogs($id);
        $user_info = $this->Xin_model->read_user_info($session['user_id']);
        $role_resources_ids = $this->Xin_model->user_role_resource();
        $data = array();

        foreach ($timelogs->result() as $r) {

            // get user > added by
            $user = $this->Xin_model->read_user_info($r->employee_id);
            // user full name
            if (!is_null($user)) {
                $full_name = $user[0]->first_name . ' ' . $user[0]->last_name;
            } else {
                $full_name = '--';
            }
            $start_date = $this->Xin_model->set_date_format($r->start_date);
            $end_date = $this->Xin_model->set_date_format($r->end_date);
            //if(in_array('346',$role_resources_ids)) { //edit
            $edit = '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_edit') . '"><button type="button" class="btn icon-btn btn-xs btn-default waves-effect waves-light"  data-toggle="modal" data-target=".edit-modal-timelog-data"  data-timelogs_id="' . $r->timelogs_id . '"><span class="fa fa-pencil"></span></button></span>';
            //} else {
            //    $edit = '';
            //}
            //if(in_array('347',$role_resources_ids)) { // delete
            $delete = '<span data-toggle="tooltip" data-placement="top" title="' . $this->lang->line('xin_delete') . '"><button type="button" class="btn icon-btn btn-xs btn-danger waves-effect waves-light delete-timelog" data-toggle="modal" data-target=".delete-modal-timelogs" data-record-id="' . $r->timelogs_id . '"><span class="fa fa-trash"></span></button></span>';
            //    } else {
            //        $delete = '';
            //    }
            if ($user_info[0]->user_role_id == '1') {
                $combhr = $edit . $delete;
            } else {
                $combhr = $edit;
            }

            $data[] = array(
                $combhr,
                $full_name,
                $start_date,
                $end_date,
                $r->total_hours,
                $r->timelogs_memo,
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $timelogs->num_rows(),
            "recordsFiltered" => $timelogs->num_rows(),
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    // Validate and add info in database
    public function add_task_category()
    {

        if ($this->input->post('add_type') == 'task_category') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            /* Server side PHP input validation */
            if ($this->input->post('category_name') === '') {
                $Return['error'] = $this->lang->line('xin_task_category_field_error');
            }

            if ($Return['error'] != '') {
                $this->output($Return);
            }

            $data = array(
                'category_name' => $this->input->post('category_name'),
                'created_at' => date('d-m-Y h:i:s'),
            );
            $result = $this->Project_model->add_task_categories($data);
            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_task_category_field_added_success');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }
    // Validate and update info in database
    public function task_category_update()
    {

        if ($this->input->post('edit_type') == 'task_category') {

            $id = $this->uri->segment(4);

            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            /* Server side PHP input validation */
            if ($this->input->post('category_name') === '') {
                $Return['error'] = $this->lang->line('xin_task_category_field_error');
            }

            if ($Return['error'] != '') {
                $this->output($Return);
            }

            $data = array(
                'category_name' => $this->input->post('category_name'),
            );

            $result = $this->Project_model->update_task_category_record($data, $id);

            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_task_category_field_updated_success');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }
    // Validate and add info in database
    public function add_project_timelog()
    {

        if ($this->input->post('add_type') == 'timelog') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();

            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');

            $st_date = strtotime($start_date);
            $ed_date = strtotime($end_date);

            /* Server side PHP input validation */
            if ($this->input->post('project_id') === '') {
                $Return['error'] = $this->lang->line('xin_error_project_field');
            } elseif ($this->input->post('employee_id') === '') {
                $Return['error'] = $this->lang->line('xin_error_employee_id');
            } elseif ($this->input->post('start_time') === '') {
                $Return['error'] = $this->lang->line('xin_project_time_start_field_error');
            } elseif ($this->input->post('end_time') === '') {
                $Return['error'] = $this->lang->line('xin_project_time_end_field_error');
            } elseif ($this->input->post('start_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_start_date');
            } elseif ($this->input->post('end_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_end_date');
            } elseif ($st_date > $ed_date) {
                $Return['error'] = $this->lang->line('xin_error_start_end_date');
            } elseif ($this->input->post('timelogs_memo') === '') {
                $Return['error'] = $this->lang->line('xin_project_memo_field_error');
            }

            if ($Return['error'] != '') {
                $this->output($Return);
            }
            $project = $this->Project_model->read_project_information($this->input->post('project_id'));
            if (!is_null($project)) {
                $cid = $project[0]->company_id;
            } else {
                $cid = 0;
            }
            $data = array(
                'project_id' => $this->input->post('project_id'),
                'company_id' => $cid,
                'employee_id' => $this->input->post('employee_id'),
                'movement'=>$this->input->post('movement'),
                'start_time' => $this->input->post('start_time'),
                'end_time' => $this->input->post('end_time'),
                'start_date' => $this->input->post('start_date'),
                'end_date' => $this->input->post('end_date'),
                'total_hours' => $this->input->post('total_hours'),
                'timelogs_memo' => $this->input->post('timelogs_memo'),
                'created_at' => date('Y-m-d h:i:s'),
            );
            $result = $this->Project_model->add_project_timelog($data);

            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_project_timelogs_added_success');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }
    // Validate and update info in database
    public function update_project_timelog()
    {

        if ($this->input->post('edit_type') == 'timelog_record') {

            $id = $this->uri->segment(4);

            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $Return['csrf_hash'] = $this->security->get_csrf_hash();
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');

            $st_date = strtotime($start_date);
            $ed_date = strtotime($end_date);
            /* Server side PHP input validation */
            if ($this->input->post('start_time') === '') {
                $Return['error'] = $this->lang->line('xin_project_time_start_field_error');
            } elseif ($this->input->post('end_time') === '') {
                $Return['error'] = $this->lang->line('xin_project_time_end_field_error');
            } elseif ($this->input->post('start_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_start_date');
            } elseif ($this->input->post('end_date') === '') {
                $Return['error'] = $this->lang->line('xin_error_end_date');
            } elseif ($st_date > $ed_date) {
                $Return['error'] = $this->lang->line('xin_error_start_end_date');
            } elseif ($this->input->post('timelogs_memo') === '') {
                $Return['error'] = $this->lang->line('xin_project_memo_field_error');
            }

            if ($Return['error'] != '') {
                $this->output($Return);
            }

            $data = array(
                'start_time' => $this->input->post('start_time'),
                'end_time' => $this->input->post('end_time'),
                'start_date' => $this->input->post('start_date'),
                'end_date' => $this->input->post('end_date'),
                'total_hours' => $this->input->post('total_hours'),
                'timelogs_memo' => $this->input->post('timelogs_memo'),
            );

            $result = $this->Project_model->update_project_timelog_record($data, $id);

            if ($result == true) {
                $Return['result'] = $this->lang->line('xin_project_timelogs_updated_success');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
            exit;
        }
    }
    public function read_timelog_record()
    {
        $data['title'] = $this->Xin_model->site_title();
        $id = $this->input->get('timelogs_id');
        $result = $this->Project_model->read_timelog_info($id);
        $data = array(
            'timelogs_id' => $result[0]->timelogs_id,
            'project_id' => $result[0]->project_id,
            'company_id' => $result[0]->company_id,
            'employee_id' => $result[0]->employee_id,
            'start_time' => $result[0]->start_time,
            'end_time' => $result[0]->end_time,
            'start_date' => $result[0]->start_date,
            'end_date' => $result[0]->end_date,
            'total_hours' => $result[0]->total_hours,
            'timelogs_memo' => $result[0]->timelogs_memo,
        );
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view('admin/project/dialog_project_timelogs', $data);
        } else {
            redirect('admin/');
        }
    }
    public function read_project_timelog_record()
    {
        $data['title'] = $this->Xin_model->site_title();
        $id = $this->input->get('timelogs_id');
        $result = $this->Project_model->read_timelog_info($id);
        $data = array(
            'timelogs_id' => $result[0]->timelogs_id,
            'project_id' => $result[0]->project_id,
            'company_id' => $result[0]->company_id,
            'employee_id' => $result[0]->employee_id,
            'start_time' => $result[0]->start_time,
            'end_time' => $result[0]->end_time,
            'start_date' => $result[0]->start_date,
            'end_date' => $result[0]->end_date,
            'total_hours' => $result[0]->total_hours,
            'timelogs_memo' => $result[0]->timelogs_memo,
        );
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view('admin/project/dialog_project_timelogs_record', $data);
        } else {
            redirect('admin/');
        }
    }
    public function task_category_read()
    {
        $data['title'] = $this->Xin_model->site_title();
        $id = $this->input->get('task_category_id');
        $result = $this->Project_model->read_task_category_information($id);
        $data = array(
            'task_category_id' => $result[0]->task_category_id,
            'category_name' => $result[0]->category_name,
        );
        $session = $this->session->userdata('username');
        if (!empty($session)) {
            $this->load->view('admin/project/dialog_task_categories', $data);
        } else {
            redirect('admin/');
        }
    }
    public function delete_task_category()
    {
        /* Define return | here result is used to return user data and error for error message */
        $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
        $id = $this->uri->segment(4);
        $Return['csrf_hash'] = $this->security->get_csrf_hash();
        $result = $this->Project_model->delete_task_category_record($id);
        if (isset($id)) {
            $Return['result'] = $this->lang->line('xin_task_category_field_deleted_success');
        } else {
            $Return['error'] = $this->lang->line('xin_error_msg');
        }
        $this->output($Return);
    }
    public function bug_read()
    {

        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $data['title'] = $this->Xin_model->site_title();
        $id = $this->input->get('bug_id');
        $result = $this->Project_model->read_bug_information($id);
        $data = array(
            'bug_id' => $result[0]->bug_id,
            'project_id' => $result[0]->project_id,
            'status' => $result[0]->status,
        );
        $this->load->view('admin/project/dialog_project_bug', $data);
    }

    public function change_bug_status()
    {
        if ($this->input->post('data') == 'change_status') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $id = $this->uri->segment(4);
            $Return['csrf_hash'] = $this->security->get_csrf_hash();
            $data = array(
                'status' => $this->input->post('status'),
            );
            $result = $this->Project_model->update_bug($data, $id);
            if (isset($id)) {
                $Return['result'] = $this->lang->line('xin_success_project_bug_status_updated');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
        }
    }

    public function bug_delete()
    {
        if ($this->input->post('data') == 'bug') {
            /* Define return | here result is used to return user data and error for error message */
            $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
            $id = $this->uri->segment(4);
            $Return['csrf_hash'] = $this->security->get_csrf_hash();
            $result = $this->Project_model->delete_bug_record($id);
            if (isset($id)) {
                $Return['result'] = $this->lang->line('xin_success_project_bug_deleted');
            } else {
                $Return['error'] = $this->lang->line('xin_error_msg');
            }
            $this->output($Return);
        }
    }

    public function delete()
    {
        /* Define return | here result is used to return user data and error for error message */
        $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
        $id = $this->uri->segment(4);
        $Return['csrf_hash'] = $this->security->get_csrf_hash();
        $result = $this->Project_model->delete_record($id);
        if (isset($id)) {
            $Return['result'] = $this->lang->line('xin_success_delete_project');
        } else {
            $Return['error'] = $this->lang->line('xin_error_msg');
        }
        $this->output($Return);
    }
    public function delete_timelog()
    {
        /* Define return | here result is used to return user data and error for error message */
        $Return = array('result' => '', 'error' => '', 'csrf_hash' => '');
        $id = $this->uri->segment(4);
        $Return['csrf_hash'] = $this->security->get_csrf_hash();
        $result = $this->Project_model->delete_timelog_record($id);
        if (isset($id)) {
            $Return['result'] = $this->lang->line('xin_project_timelogs_deleted_success');
        } else {
            $Return['error'] = $this->lang->line('xin_error_msg');
        }
        $this->output($Return);
    }
    public function EOI_notice(){
        $session = $this->session->userdata('username');
        if (empty($session)) {
            redirect('admin/');
        }
        $data['title'] = 'EOI Notice';
        $data['breadcrumbs'] = 'EOI Notice Information' ;
        $data['subview'] = $this->load->view("admin/project/EOI_notice", $data, true);
        $this->load->view('admin/layout/layout_main', $data); 
    }
}
