<?php
if (!class_exists('Paylike\Client')) {
    require_once(DIR_SYSTEM . 'library/Paylike/Client.php');
}

class ControllerPaymentPaylike extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('payment/paylike');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('paylike', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');
            $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        } else {
            $this->data['error'] = @$this->error;
        }

        //Creating table if not exists already
        $sql = "
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paylike_admin` (
			`order_id` int(11) NOT NULL default '0',
			`trans_id` varchar(255) NOT NULL,
			`amount` int(11) NOT NULL default '0',
			`action` varchar(32) NOT NULL default 'NO',
			`captured` varchar(8) NOT NULL default 'NO',
			PRIMARY KEY  (`order_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        $this->db->query($sql);

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_edit'] = $this->language->get('text_edit');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_yes'] = $this->language->get('text_yes');
        $this->data['text_no'] = $this->language->get('text_no');
        $this->data['text_all_zones'] = $this->language->get('text_all_zones');
        $this->data['text_test'] = $this->language->get('text_test');
        $this->data['text_live'] = $this->language->get('text_live');
        $this->data['text_capture_instant'] = $this->language->get('text_capture_instant');
        $this->data['text_capture_delayed'] = $this->language->get('text_capture_delayed');

        $this->data['payment_method_title'] = $this->language->get('payment_method_title');
        $this->data['payment_method_description'] = $this->language->get('payment_method_description');
        $this->data['entry_title'] = $this->language->get('entry_title');
        $this->data['description_status'] = $this->language->get('description_status');
        $this->data['entry_description'] = $this->language->get('entry_description');
        $this->data['entry_mode'] = $this->language->get('entry_mode');
        $this->data['entry_test_key'] = $this->language->get('entry_test_key');
        $this->data['entry_test_app_key'] = $this->language->get('entry_test_app_key');
        $this->data['entry_live_key'] = $this->language->get('entry_live_key');
        $this->data['entry_live_app_key'] = $this->language->get('entry_live_app_key');

        $this->data['entry_total'] = $this->language->get('entry_total');
        $this->data['entry_order_status'] = $this->language->get('entry_order_status');
        $this->data['entry_capture'] = $this->language->get('entry_capture');
        $this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $this->data['default_payment_method_title'] = $this->config->get('default_payment_method_title');
        $this->data['default_payment_method_description'] = $this->language->get('default_payment_method_description');
        $this->data['default_entry_title'] = (!empty($this->config->get('config_name'))) ? $this->config->get('config_name') : 'Payment';
        //$this->data['default_entry_description'] = $this->language->get('default_entry_description');
        $this->data['default_entry_description'] = '';

        $this->data['help_paylike_payment_method_title'] = $this->language->get('help_paylike_payment_method_title');
        $this->data['help_paylike_title'] = $this->language->get('help_paylike_title');
        $this->data['help_paylike_payment_method_description'] = $this->language->get('help_paylike_payment_method_description');
        $this->data['help_paylike_show_on_popup'] = $this->language->get('help_paylike_show_on_popup');
        $this->data['help_paylike_description'] = $this->language->get('help_paylike_description');
        $this->data['help_key'] = $this->language->get('help_key');
        $this->data['help_app_key'] = $this->language->get('help_app_key');
        $this->data['help_total'] = $this->language->get('help_total');
        $this->data['help_capture'] = $this->language->get('help_capture');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['paylike_payment_method_title'])) {
            $this->data['error_payment_method_title'] = $this->error['paylike_payment_method_title'];
        } else {
            $this->data['error_payment_method_title'] = '';
        }

        if (isset($this->error['paylike_payment_method_description'])) {
            $this->data['error_payment_method_description'] = $this->error['paylike_payment_method_description'];
        } else {
            $this->data['error_payment_method_description'] = '';
        }

        if (isset($this->error['paylike_title'])) {
            $this->data['error_title'] = $this->error['paylike_title'];
        } else {
            $this->data['error_title'] = '';
        }

        /*if (isset($this->error['paylike_description'])) {
            $this->data['error_description'] = $this->error['paylike_description'];
        } else {
            $this->data['error_description'] = '';
        }*/

        $paylike_mode = (isset($this->request->post['paylike_mode'])) ? $this->request->post['paylike_mode'] : $this->config->get('paylike_mode');
        $this->data['error_test_key'] = '';
        $this->data['error_test_app_key'] = '';
        $this->data['error_live_key'] = '';
        $this->data['error_live_app_key'] = '';

        if ($paylike_mode == 'test') {
            if (isset($this->error['test_key'])) {
                $this->data['error_test_key'] = $this->error['test_key'];
            } else {
                $this->data['error_test_key'] = '';
            }

            if (isset($this->error['test_app_key'])) {
                $this->data['error_test_app_key'] = $this->error['test_app_key'];
            } else {
                $this->data['error_test_app_key'] = '';
            }
        }

        if ($paylike_mode == 'live') {
            if (isset($this->error['live_key'])) {
                $this->data['error_live_key'] = $this->error['live_key'];
            } else {
                $this->data['error_live_key'] = '';
            }

            if (isset($this->error['live_app_key'])) {
                $this->data['error_live_app_key'] = $this->error['live_app_key'];
            } else {
                $this->data['error_live_app_key'] = '';
            }
        }


        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/paylike', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        /*$this->data['action'] = $this->url->link('payment/paylike', 'token=' . $this->session->data['token'], true);
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], true);*/
        $this->data['action'] = $this->url->link('payment/paylike', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        //Admin Settings form fields

        //Payment Method Title
        if (isset($this->request->post['paylike_payment_method_title'])) {
            $this->data['paylike_payment_method_title'] = $this->request->post['paylike_payment_method_title'];
        } else {
            $this->data['paylike_payment_method_title'] = $this->config->get('paylike_payment_method_title');
        }

        //Payment Method Description
        if (isset($this->request->post['paylike_payment_method_description'])) {
            $this->data['paylike_payment_method_description'] = $this->request->post['paylike_payment_method_description'];
        } else {
            $this->data['paylike_payment_method_description'] = $this->config->get('paylike_payment_method_description');
        }

        //Title
        if (isset($this->request->post['paylike_title'])) {
            $this->data['paylike_title'] = $this->request->post['paylike_title'];
        } else {
            $this->data['paylike_title'] = $this->config->get('paylike_title');
        }

        //Description Status
        if (isset($this->request->post['paylike_description_status'])) {
            $this->data['paylike_description_status'] = $this->request->post['paylike_description_status'];
        } else {
            $this->data['paylike_description_status'] = $this->config->get('paylike_description_status');
        }

        //Description
        if (isset($this->request->post['paylike_description'])) {
            $this->data['paylike_description'] = trim($this->request->post['paylike_description']);
        } else {
            $this->data['paylike_description'] = $this->config->get('paylike_description');
        }

        //Mode(Test/Live)
        if (isset($this->request->post['paylike_mode'])) {
            $this->data['paylike_mode'] = $this->request->post['paylike_mode'];
        } else {
            $this->data['paylike_mode'] = $this->config->get('paylike_mode');
        }

        //Testmode Public Key
        if (isset($this->request->post['paylike_test_key'])) {
            $this->data['paylike_test_key'] = $this->request->post['paylike_test_key'];
        } else {
            $this->data['paylike_test_key'] = $this->config->get('paylike_test_key');
        }

        //Testmode App Key
        if (isset($this->request->post['paylike_test_app_key'])) {
            $this->data['paylike_test_app_key'] = $this->request->post['paylike_test_app_key'];
        } else {
            $this->data['paylike_test_app_key'] = $this->config->get('paylike_test_app_key');
        }

        //Livemode Public Key
        if (isset($this->request->post['paylike_live_key'])) {
            $this->data['paylike_live_key'] = $this->request->post['paylike_live_key'];
        } else {
            $this->data['paylike_live_key'] = $this->config->get('paylike_live_key');
        }

        //Livemode App Key
        if (isset($this->request->post['paylike_live_app_key'])) {
            $this->data['paylike_live_app_key'] = $this->request->post['paylike_live_app_key'];
        } else {
            $this->data['paylike_live_app_key'] = $this->config->get('paylike_live_app_key');
        }

        //Total
        if (isset($this->request->post['paylike_total'])) {
            $this->data['paylike_total'] = $this->request->post['paylike_total'];
        } else {
            $this->data['paylike_total'] = $this->config->get('paylike_total');
        }

        //Order Status
        if (isset($this->request->post['paylike_order_status_id'])) {
            $this->data['paylike_order_status_id'] = $this->request->post['paylike_order_status_id'];
        } else {
            $this->data['paylike_order_status_id'] = $this->config->get('paylike_order_status_id');
        }
        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        //Capture
        if (isset($this->request->post['paylike_capture'])) {
            $this->data['paylike_capture'] = $this->request->post['paylike_capture'];
        } else {
            $this->data['paylike_capture'] = $this->config->get('paylike_capture');
        }

        //Zone
        if (isset($this->request->post['paylike_geo_zone_id'])) {
            $this->data['paylike_geo_zone_id'] = $this->request->post['paylike_geo_zone_id'];
        } else {
            $this->data['paylike_geo_zone_id'] = $this->config->get('paylike_geo_zone_id');
        }
        $this->load->model('localisation/geo_zone');
        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        //Status
        if (isset($this->request->post['paylike_status'])) {
            $this->data['paylike_status'] = $this->request->post['paylike_status'];
        } else {
            $this->data['paylike_status'] = $this->config->get('paylike_status');
        }

        //Sort Order
        if (isset($this->request->post['paylike_sort_order'])) {
            $this->data['paylike_sort_order'] = $this->request->post['paylike_sort_order'];
        } else {
            $this->data['paylike_sort_order'] = $this->config->get('paylike_sort_order');
        }

        /*$this->data['header'] = $this->load->controller('common/header');
        $this->data['column_left'] = $this->load->controller('common/column_left');
        $this->data['footer'] = $this->load->controller('common/footer');

        if( version_compare(VERSION, '2.2.0.0', '>=') ) {
            $this->response->setOutput($this->load->view('payment/paylike', $data));
        }else{
            $this->response->setOutput($this->load->view('payment/paylike.tpl', $data));
        }*/

        $this->data['token'] = $this->session->data['token'];

        $this->template = 'payment/paylike.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());

    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/paylike')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $paylike_mode = (isset($this->request->post['paylike_mode'])) ? $this->request->post['paylike_mode'] : $this->config->get('paylike_mode');

        if ($paylike_mode == 'test') {
            if (!$this->request->post['paylike_test_key']) {
                $this->error['test_key'] = $this->language->get('error_test_key');
            }

            if (!$this->request->post['paylike_test_app_key']) {
                $this->error['test_app_key'] = $this->language->get('error_test_app_key');
            }
        }

        if ($paylike_mode == 'live') {
            if (!$this->request->post['paylike_live_key']) {
                $this->error['live_key'] = $this->language->get('error_live_key');
            }

            if (!$this->request->post['paylike_live_app_key']) {
                $this->error['live_app_key'] = $this->language->get('error_live_app_key');
            }
        }

        return !$this->error;
    }

    public function doaction()
    {
        $response = array();

        if (isset($this->request->post['trans_ref'])
            && !empty($this->request->post['trans_ref'])
            && isset($this->request->post['p_action'])
            && !empty($this->request->post['p_action'])
            //&& isset($this->request->post['p_amount'])
            //&& !empty($this->request->post['p_amount'])
        ) {
            //Set app key
            $app_key = ($this->config->get('paylike_mode') === 'test') ? $this->config->get('paylike_test_app_key') : $this->config->get('paylike_live_app_key');
            Paylike\Client::setKey($app_key);

            $this->logger = new Log('paylike.log');
            $this->logger->write('Paylike Class Initialized in Admin');

            $this->load->language('payment/paylike');

            $orderId = $this->request->post['p_order_id'];
            $transactionId = $this->request->post['trans_ref'];
            $action = $this->request->post['p_action'];
            if (isset($this->request->post['p_amount']) && !empty($this->request->post['p_amount']))
                $amount = $this->get_paylike_amount($this->request->post['p_amount']);
            else
                $amount = 0;
            $reason = $this->request->post['p_reason'];
            $captured = $this->request->post['p_captured'];

            switch ($action) {
                case "capture":
                    if ('YES' == $captured) {
                        $response['transaction']['errors'] = $this->language->get('error_order_already_captured');
                        $response['transaction']['error'] = 1;
                    } else {
                        $this->logger->write('Paylike Capture Action Initialized in Admin for Amount: ' . $amount);
                        $data = array(
                            'amount' => $amount,
                            'descriptor' => "Order #{$orderId}",
                            'currency' => $this->session->data['currency']
                        );
                        $trans_data = Paylike\Transaction::fetch($transactionId);
                        $data['amount'] = (int)$trans_data['transaction']['pendingAmount'];
                        $response = Paylike\Transaction::capture($transactionId, $data);
                        if (isset($response['transaction'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '5' WHERE `order_id` = '{$orderId}'");
                            $this->db->query("UPDATE " . DB_PREFIX . "paylike_admin SET captured = 'YES' WHERE `order_id` = '{$orderId}'");
                            $response['order_status_id'] = 5;
                            $response['success_message'] = $this->language->get('order_captured_success');
                        } else {
                            $response['transaction']['errors'] = $this->get_response_error($response);
                            $response['transaction']['error'] = 1;
                        }
                    }
                    break;
                case "refund":
                    $this->logger->write('Paylike Refund Action Initialized in Admin for Amount: ' . $amount);
                    $data['amount'] = $amount;
                    if ($reason) {
                        $data['descriptor'] = $reason;
                    }
                    if ('YES' == $captured) {
                        $response = Paylike\Transaction::refund($transactionId, $data);
                        if (isset($response['transaction'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '11' WHERE `order_id` = '{$orderId}'");
                            $response['order_status_id'] = 11;
                            $response['success_message'] = sprintf($this->language->get('order_refunded_success'), $this->session->data['currency'] . ' ' . number_format(($amount / 100), 2, '.', ''));
                        } else {
                            $response['transaction']['errors'] = $this->get_response_error($response);
                            $response['transaction']['error'] = 1;
                        }
                    } else {
                        $response['transaction']['errors'] = $this->language->get('refund_before_capture_error');
                        $response['transaction']['error'] = 1;
                    }
                    break;
                case "void":
                    if ('YES' == $captured) {
                        $response['transaction']['errors'] = $this->language->get('void_after_capture_error');
                        $response['transaction']['error'] = 1;
                    } else {
                        $this->logger->write('Paylike Void Action Initialized in Admin for Amount: ' . $amount);
                        $trans_data = Paylike\Transaction::fetch($transactionId);
                        $data['amount'] = (int)$trans_data['transaction']['amount'] - $trans_data['transaction']['refundedAmount'];

                        $response = Paylike\Transaction::void($transactionId, $data);
                        if (isset($response['transaction'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '16' WHERE `order_id` = '{$orderId}'");
                            $response['order_status_id'] = 16;
                            $response['success_message'] = $this->language->get('order_voided_success');
                        }

                        if (!isset($response['transaction'])) {
                            $response['transaction']['errors'] = $this->get_response_error($response);
                            $response['transaction']['error'] = 1;
                        }
                    }

                    /*$data['amount'] = $amount;
                    if ( $reason ) {
                        $data['descriptor'] = $reason;
                    }
                    if ( 'YES' == $captured ) {
                        $response = Paylike\Transaction::refund( $transactionId, $data );
                        if (isset($response['transaction'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '16' WHERE `order_id` = '{$orderId}'");
                            $response['order_status_id'] = 16;
                            $response['success_message'] = $this->language->get('order_voided_success');
                        }
                    } else {
                        $response = Paylike\Transaction::void( $transactionId, $data );
                        if (isset($response['transaction'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '16' WHERE `order_id` = '{$orderId}'");
                            $response['order_status_id'] = 16;
                        }
                    }*/
                    break;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($response));
    }

    /**
     * Get Paylike amount to pay
     *
     * @param float $total Amount due.
     * @param string $currency Accepted currency.
     *
     * @return float|int
     */
    public function get_paylike_amount($total, $currency = '')
    {
        $zero_decimal_currency = array(
            "CLP",
            "JPY",
            "VND"
        );
        $currency_code = $currency != '' ? $currency : $this->session->data['currency'];
        if (in_array($currency_code, $zero_decimal_currency)) {
            $total = number_format($total, 0, ".", "");
        } else {
            $total = $total * 100;
        }

        return ceil($total);
    }

    /**
     * Gets errors from a failed api request
     *
     * @param $result
     *
     * @return string
     */
    protected function get_response_error($result)
    {
        $error = array();
        foreach ($result as $field_error) {
            $error[] = ucwords($field_error['field']) . ': ' . $field_error['message'];
        }
        $error_message = implode(" ", $error);

        return $error_message;
    }
}
