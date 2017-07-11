<?php
if (!class_exists('Paylike\Client')) {
    require_once(DIR_SYSTEM . 'library/Paylike/Client.php');
}

class ControllerPaymentPaylike extends Controller {
    /**
     * Should we capture Credit cards
     *
     * @var bool
     */
    public $capture;

    /**
     * Show payment popup on the checkout action.
     *
     * @var bool
     */
    public $direct_checkout;

    /**
     * API access app key
     *
     * @var string
     */
    public $app_key;

    /**
     * Api access public key
     *
     * @var string
     */
    public $public_key;

    /**
     * Is test mode active?
     *
     * @var bool
     */
    public $testmode;

    /**
     * Logging enabled?
     *
     * @var bool
     */
    public $logging;

    public $logger;
		
    public function index() {
        header('Access-Control-Allow-Origin: *');
        $this->load->language('payment/paylike');

        $this->data['button_confirm'] = $this->language->get('button_confirm');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $products = $this->cart->getProducts();
        $products_array = array();
        $products_label = array();
        $p = 0;
        foreach ($products as $key => $product) {
            $products_array[$p] = array(
                'ID' => $product['product_id'],
                'name' => $product['name'],
                'quantity' => $product['quantity']
            );
            $products_label[$p] = $product['quantity'] . 'x ' . $product['name'];
            $p++;
        }
        $this->data['products'] = json_encode($products_array);

        $this->data['paylike_public_key'] = ($this->config->get('paylike_mode') == 'test') ? $this->config->get('paylike_test_key') : $this->config->get('paylike_live_key');
        $this->data['popup_title'] = (!empty($this->config->get('paylike_title'))) ? $this->config->get('paylike_title') : $this->config->get('config_title');
        if ($this->config->get('paylike_description_status') == 1) {
            $this->data['popup_description'] = ($this->config->get('paylike_description')) ? $this->config->get('paylike_description') : '';
        } else {
            $this->data['popup_description'] = implode(", & ", $products_label);
        }
        $this->data['order_id'] = $this->session->data['order_id'];
        $this->data['name'] = $order_info['payment_firstname'] . " " . $order_info['payment_lastname'];
        $this->data['email'] = $order_info['email'];
        $this->data['telephone'] = $order_info['telephone'];
        $this->data['address'] = $order_info['payment_address_1'] . ', ' . $order_info['payment_address_2'] . ', ' . $order_info['payment_city'] . ', ' . $order_info['payment_zone'] . ', ' . $order_info['payment_country'] . ' - ' . $order_info['payment_postcode'];
        $this->data['ip'] = $order_info['ip'];
        $this->data['amount'] = $this->currency->getPayLikeAmount($order_info['total'], $order_info['currency_code']);
        $this->data['currency_code'] = $this->session->data['currency'];      

        if (version_compare(VERSION, '1.5.6.5', '>=')) {
            $this->template = 'payment/paylike';
        } else {
            $this->template = 'default/template/payment/paylike.tpl';
        }
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function update() {
        $this->load->language('payment/paylike');

        if (isset($this->request->post['trans_ref']) && $this->request->post['trans_ref'] != '') {
            $message = "";
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            $message .= $this->request->post['trans_ref'];
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'), $message);

            $amount = round($this->currency->format($order_info['total'], $order_info['currency_code'], 1.00000, false));
            $pat_order_query = $this->db->query("SELECT order_id from " . DB_PREFIX . "paylike_admin where order_id = '" . $order_info['order_id'] . "'");
            if (!$pat_order_query->num_rows) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "paylike_admin SET `order_id` = '" . $order_info['order_id'] . "', trans_id = '" . $this->request->post['trans_ref'] . "', amount = " . $amount . "");
            } else {
                $this->db->query("UPDATE " . DB_PREFIX . "paylike_admin SET trans_id = '" . $this->request->post['trans_ref'] . "', amount = '" . $amount . "' WHERE `order_id` = '" . $order_info['order_id'] . "'");
            }

            $json['success'] = $this->language->get('text_order_updated');
            $json['redirect'] = $this->url->link('checkout/success', '', true);
        } else {
            $json['error'] = $this->language->get('text_no_transaction_found');
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Process the payment
     *
     * @param int $order_id Reference.
     *
     * @return array
     */
    public function processPayment() {
        $this->title = $this->config->get('paylike_title');
        $this->description = $this->config->get('paylike_description');
        $this->enabled = $this->config->get('paylike_status');
        $this->testmode = 'test' === $this->config->get('paylike_mode');
        $this->capture = '1' === $this->config->get('paylike_capture');
        $this->app_key = ($this->testmode) ? $this->config->get('paylike_test_app_key') : $this->config->get('paylike_live_app_key');
        $this->public_key = ($this->testmode) ? $this->config->get('paylike_test_public_key') : $this->config->get('paylike_live_public_key');
        $this->logging = 'yes' === 'yes';

        if ($this->app_key != '') {
            Paylike\Client::setKey($this->app_key);
        }

        $this->logger = new Log('paylike.log');
        $this->logger->write('Paylike Class Initialized');
        $this->load->language('payment/paylike');

        $json = array();

        if (isset($this->request->post['trans_ref']) && $this->request->post['trans_ref'] != '') {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            /*$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'), $this->request->post['trans_ref']);*/
            $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('config_order_status_id'));
            $transaction_id = $this->request->post['trans_ref'];

            $result = $this->handlePayment($transaction_id, $order_info);
            if (!empty($result) && isset($result['transaction'])) {
                $this->model_checkout_order->update($this->session->data['order_id'], $this->config->get('paylike_order_status_id'), $this->request->post['trans_ref'], false);

                //$json['success'] = $this->url->link('checkout/success');
                $json['success'] = $this->language->get('text_order_updated');
                $json['redirect'] = $this->url->link('checkout/success', '', true);
            } else {
                $message = $this->language->get('text_invalid_transaction');
                if (is_array($result) && !empty($result['error']) && $result['error'] == 1) {
                    $message = $result['message'];

                } else if (!empty($result[0]['message'])) {
                    $message = $result[0]['message'];
                }
                $json['error'] = $message;
                //$json['redirect'] = $this->url->link('checkout/checkout', '', true);
            }
        } else {
            $json['error'] = $this->language->get('text_no_transaction_found');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Handles API interaction for the order
     * by either only authorizing the payment
     * or making the capture directly
     *
     * @param $transaction_id
     * @param $order
     * @param $amount
     *
     * @return bool|int|mixed
     */
    protected function handlePayment($transaction_id, $order, $amount = false) {
        $amount = round($this->currency->format($order['total'], $order['currency_code'], 1.00000, false)) . '00';
        $this->logger->write("Info: Begin processing payment for order " . $order['order_id'] . " for the amount of {$amount}");
        $amount = false;
        if (false == $this->capture) {
            $result = Paylike\Transaction::fetch($transaction_id);
            $response = $this->handleAuthorizeResult($result, $order, $amount);
        } else {
            $data = array(
                'amount' => $this->currency->getPayLikeAmount($order['total'], $order['currency_code']),
                'currency' => $order['currency_code']
            );
            $result = Paylike\Transaction::capture($transaction_id, $data);
            $response = $this->handleCaptureResult($result, $order, $amount);
        }

        return $response;
    }

    /**
     * @param $order
     * @param $result // array result returned by the api wrapper
     * @param int $amount
     */
    public function handleAuthorizeResult($result, $order, $amount = 0) {
        $result = $this->parseApiTransactionResponse($result, $order, $amount);
        if (empty($result)) {
            $this->logger->write('Unable to capture transaction!');
            $response = $result;
        } else {
            $orderId = $order['order_id'];
            $status = $this->config->get('paylike_order_status_id');
            $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '{$status}' WHERE `order_id` = '{$orderId}'");
            $this->getTransactionAuthorizationDetails($result);
            $this->saveTransaction($result['transaction']['id'], $order);
            $response = $result;
        }
        return $response;
    }

    /**
     * Parses api transaction response to for errors
     *
     * @param $result
     * @param $order
     * @param bool $amount
     *
     * @return null
     */
    protected function parseApiTransactionResponse($result, $order = null, $amount = false) {
        if (!$result) {
            $this->logger->write("paylike_error: cURL request failed.");
            $result = null;
        }

        if (!$this->isTransactionSuccessful($result, $order, $amount)) {
            $error_message = $this->getResponseError($result);
            $result = null;
        }

        return $result;
    }

    /**
     * Checks if the transaction was successful and
     * the data was not tempered with
     *
     *
     * @param $result
     * @param $order
     * @param bool|false $amount used to overwrite the amount, when we don't pay the full order
     *
     * @return bool
     */
    protected function isTransactionSuccessful($result, $order = null, $amount = false) {
        // if we don't have the order, we only check the successful status
        if (!$order) {
            return 1 == $result['transaction']['successful'];
        }
        // we need to overwrite the amount in the case of a subscription
        if (!$amount) {
            $amount = $this->currency->getPayLikeAmount($order['total'], $order['currency_code']);
        }

        if (isset($result['transaction'])) {
            return 1 == $result['transaction']['successful']
                && $result['transaction']['currency'] == $order['currency_code']
                && (int)$result['transaction']['amount'] == (int)$amount;
        } else {
            return false;
        }
    }

    /**
     * Gets errors from a failed api request
     *
     * @param $result
     *
     * @return string
     */
    protected function getResponseError($result) {
        $error = array();
        foreach ($result as $field_error) {
            if (isset($field_error['field']))
                $error[] = @$field_error['field'] . ':' . @$field_error['message'];
        }
        $error_message = implode(" ", $error);

        return $error_message;
    }

    /**
     * @param $result
     *
     * @return string
     */
    protected function getTransactionAuthorizationDetails($result) {
        $this->logger->write("paylike_authorization: Paylike authorization completed at " . $result['transaction']['created'] . " for Transaction ID " . $result['transaction']['id']);

        return 'Paylike authorization complete.' . PHP_EOL .
            'Transaction ID: ' . $result['transaction']['id'] . PHP_EOL .
            'Payment Amount: ' . $result['transaction']['amount'] . PHP_EOL .
            'Transaction authorized at: ' . $result['transaction']['created'];
    }

    /**
     * @param $transaction_id
     * @param $order
     */
    protected function saveTransaction($transaction_id, $order, $captured = 'NO') {
        $amount = $this->currency->getPayLikeAmount($order['total'], $order['currency_code']);
        $pat_order_query = $this->db->query("SELECT order_id from " . DB_PREFIX . "paylike_admin where order_id = '" . $order['order_id'] . "'");
        if (!$pat_order_query->num_rows) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "paylike_admin SET `order_id` = '" . $order['order_id'] . "', trans_id = '" . $transaction_id . "', amount = " . $amount . ", captured = '" . $captured . "'");
        } else {
            $this->db->query("UPDATE " . DB_PREFIX . "paylike_admin SET trans_id = '" . $transaction_id . "', amount = '" . $amount . "' WHERE `order_id` = '" . $order['order_id'] . "' AND captured = '" . $captured . "'");
        }
    }

    /**
     * @param $order
     * @param $result // array result returned by the api wrapper
     * @param int $amount
     */
    public function handleCaptureResult($result, $order, $amount = 0) {
        $result = $this->parseApiTransactionResponse($result, $order, $amount);

        if (!isset($result['transaction'])) {
            $this->logger->write('Unable to capture transaction!');
            $response = $result;

        } else {
            $orderId = $order['order_id'];
            $status = $this->config->get('paylike_order_status_id');
            $this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '{$status}' WHERE `order_id` = '{$orderId}'");
            $this->getTransactionCaptureDetails($result);
            $this->saveTransaction($result['transaction']['id'], $order, 'YES');
            $response = $result;
        }
        return $response;
    }

    /**
     * @param $result
     *
     * @return string
     */
    protected function getTransactionCaptureDetails($result) {
        $this->logger->write("paylike_captured: Captured amount: " . $result['transaction']['capturedAmount'] . " at " . $result['transaction']['created'] . " Created for Transaction ID " . $result['transaction']['id']);

        return 'Transaction ID: ' . $result['transaction']['id'] . PHP_EOL .
            'Authorized amount: ' . $result['transaction']['amount'] . PHP_EOL .
            'Captured amount: ' . $result['transaction']['capturedAmount'] . PHP_EOL .
            'Charge authorized at: ' . $result['transaction']['created'];
    }
        
}