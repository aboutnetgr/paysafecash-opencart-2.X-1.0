<?php
class ControllerPaymentPaysafecash extends Controller
{
    // current version
    private $version = '1.0.0';

    // load PaymentClass and connect
    private function get_connection()
    {
        require_once(DIR_SYSTEM."library/paysafe/PaymentClass.php");
        $test_mode = $this->config->get('paysafecash_test_mode');
        $paysafecash_api_key = $this->config->get('paysafecash_api_key');

        if ($test_mode) {
            $env = "TEST";
        } else {
            $env = "PRODUCTION";
        }

        $conn = new PaysafecardCashController($paysafecash_api_key, $env);
        return $conn;
    }

    public function index()
    {
        $this->load->language('payment/paysafecash');
        $this->load->model('checkout/order');

        $debug_mode = $this->config->get('paysafecash_debug_mode');

        $data['redirect_url'] =  $this->url->link('payment/paysafecash/redirect', '', true);
        $data['payment_title'] =  $this->language->get('payment_title');

        $data['button_proceed'] =  $this->language->get('button_proceed');

        $data['confirm_description'] =  html_entity_decode($this->config->get('paysafecash_confirm_description' . $this->config->get('config_language_id')), ENT_QUOTES, 'UTF-8');

        if ($debug_mode) {
            $this->log->write('FRONT (paysafe:cash): Start ');
        }

        if (version_compare(VERSION, '2.0.0.0', '>')) {
            if (version_compare(VERSION, '2.2.0.0', '<')) {
                if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paysafecash_redirect.tpl')) {
                    return $this->load->view($this->config->get('config_template') . '/template/payment/paysafecash_redirect.tpl', $data);
                } else {
                    return $this->load->view('default/template/payment/paysafecash_redirect.tpl', $data);
                }
            } else {
                return $this->load->view('payment/paysafecash_redirect', $data);
            }
        }
    }

    // redirect to paysafe checkout page
    public function redirect()
    {
        $this->load->language('payment/paysafecash');
        $this->load->model('checkout/order');
        $this->load->model('payment/paysafecash');

        $debug_mode = $this->config->get('paysafecash_debug_mode');
        $test_mode = $this->config->get('paysafecash_test_mode');
        $paysafecash_submerchant_id = $this->config->get('paysafecash_submerchant_id');
        $customer_send_data = $this->config->get('paysafecash_customer_data');
        $timeout_limit = $this->config->get('paysafecash_var_trans_timeout');

        if ($test_mode) {
            $env = "TEST";
        } else {
            $env = "PRODUCTION";
        }

        if ($debug_mode) {
            $this->log->write('FRONT (paysafe:cash => redirect): environment = '.$env);
        }

        // get order information
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        // if order does not exists redirect to checkout page
        if (!$order_info) {
            if ($debug_mode) {
                $this->log->write('FRONT (paysafe:cash => redirect): Order not found from session! ');
            }
            $this->session->data['error'] = "Error: Order not found!";
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

        // if test mode then order amount is 1
        if ($test_mode) {
            $order_amount = 1;
        } else {
            $order_amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        }

        $pscpayment = $this->get_connection();
        $success_url = $this->url->link('payment/paysafecash/success'.'&order_id=' . $order_info["order_id"]);
        $failure_url = $this->url->link('payment/paysafecash/failure'.'&order_id=' . $order_info["order_id"]);
        $webhook_url = $this->url->link('payment/paysafecash/webhook'.'&order_id=' . $order_info["order_id"]);
        $notification_url = $this->url->link('payment/paysafecash/notification'.'&order_id=' . $order_info["order_id"]);

        // if settings timeout is set then convert to minutes
        if ($timeout_limit > 0) {
            $timeout_limit = $timeout_limit*60*24;
        } else {
            $timeout_limit = 4320;
        }

        // set customer hash
        $customer_hash = "";
        if ($order_info['customer_id'] > 0) {
            $customer_hash = md5($order_info['customer_id']);
        } else {
            $customer_hash = md5($order_info['email']);
        }

        // send customer data if setting is enabled
        if ($customer_send_data) {
            $customer_data = [
                "first_name"   => $order_info['payment_firstname'],
                "last_name"    => $order_info['payment_lastname'],
                "address1"     => $order_info['payment_address_1'],
                "postcode"     => $order_info['payment_postcode'],
                "city"         => $order_info['payment_city'],
                "phone_number" => $order_info['telephone'],
                "email"        => $order_info['email']
            ];
        } else {
            $customer_data = [];
        }

        $data_payment = [
            'amount' => $order_amount,
            'currency_code' => $order_info['currency_code'],
            'customer_id' => $customer_hash,
            'REMOTE_ADDR' => $this->request->server['REMOTE_ADDR'],
            'success_url' => $success_url."&payment_id={payment_id}",
            'failure_url' => $failure_url."&payment_id={payment_id}",
            'notification_url' => $notification_url."&payment_id={payment_id}",
            'webhook_url' => $webhook_url,
            'customer_data' => $customer_data,
            'time_limit' => $timeout_limit,
            'correlation_id' => $order_info['order_id'].'_'.uniqid(),
            'country_restriction' => $this->config->get('paysafecash_countries'),
            'kyc_restriction' => '',
            'min_age' => '',
            'shop_id' => $order_info["store_name"]." ".VERSION." | ".$this->version,
            'env' => ($env == 'TEST' ? '' : $paysafecash_submerchant_id),
        ];

        if ($debug_mode) {
            $this->log->write('FRONT (paysafe:cash => redirect): Initiate Payment with data: '.print_r($data_payment, true));
        }

        // send data to paysafe and get a response
        $response = $pscpayment->initiatePayment(
            $data_payment['amount'],
            $data_payment['currency_code'],
            $data_payment['customer_id'],
            $data_payment['REMOTE_ADDR'],
            $data_payment['success_url'],
            $data_payment['failure_url'],
            $data_payment['notification_url'],
            $data_payment['webhook_url'],
            $data_payment['customer_data'],
            $data_payment['time_limit'],
            $data_payment['correlation_id'],
            $data_payment['country_restriction'],
            $data_payment['kyc_restriction'],
            $data_payment['min_age'],
            $data_payment['shop_id'],
            $data_payment['env']
        );

        // if response is ok redirect user to paysafe page
        if (isset($response["object"])) {
            if ($debug_mode) {
                $this->log->write('FRONT (paysafe:cash => redirect): Response initiate payment Success: ('.$this->request->server['REQUEST_URI'].') '.print_r($response, true));
            }

            $this->model_payment_paysafecash->validatePaymentOrder($order_info["order_id"], (int)$this->config->get('paysafecash_awaiting_order_status_id'), $response["id"]);
            $this->model_checkout_order->addOrderHistory($order_info["order_id"], (int)$this->config->get('paysafecash_awaiting_order_status_id'), '', true);
            $this->response->redirect($response["redirect"]['auth_url']);
        // if not thand redirect to checkout page with an error
        } else {
            if ($debug_mode) {
                $this->log->write('FRONT (paysafe:cash => redirect): Response initiate payment Error: ('.$this->request->server['REQUEST_URI'].') '.print_r($response, true));
            }
            $error = $pscpayment->getError();
            $this->session->data['error'] = $error["message"];
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }
    }

    // return success url from paysafe
    public function success()
    {
        $this->load->model('payment/paysafecash');
        $this->load->model('checkout/order');

        $debug_mode = $this->config->get('paysafecash_debug_mode');

        $order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : 0;
        $payment_id = isset($this->request->get['payment_id']) ? $this->request->get['payment_id'] : '';

        if ($debug_mode) {
            $this->log->write('FRONT (paysafe:cash => success): Start (order_id='.$order_id.' payment_id='.$payment_id.')');
        }

        if (!($order_id > 0)) {
            if ($debug_mode) {
                $this->log->write('FRONT (paysafe:cash => success): No order id! ');
            }
            return false;
        }

        $redirect = $this->url->link('checkout/checkout');

        // check if order id and payment id are ok
        if ($order_id && $payment_id) {
            $order_info = $this->model_checkout_order->getOrder($order_id);

            if (!count($order_info)) {
                if ($debug_mode) {
                    $this->log->write('FRONT (paysafe:cash => success): Order not found! ');
                }
                return false;
            }

            $payment_status = $this->config->get('paysafecash_order_status_id'); // Default value for a payment that succeed.
            $pscpayment = $this->get_connection();

            // retrieve payment from paysafe
            $response   = $pscpayment->retrievePayment($payment_id);

            // if there is not a response
            if ($response == false) {
                if ($debug_mode) {
                    if ($debug_mode) {
                        $this->log->write('FRONT (paysafe:cash => success): No response! ('.$this->request->server['REQUEST_URI'].') '.print_r($response, true));
                    }
                }
            } elseif (isset($response["object"])) {
                if ($response["status"] == "SUCCESS") {
                    if ($debug_mode) {
                        $this->log->write('FRONT (paysafe:cash => success): status='.$response["status"].'! ('.$this->request->server['REQUEST_URI'].') '.print_r($response, true));
                    }
                    if ($order_info['order_status_id'] == (int)$this->config->get('paysafecash_awaiting_order_status_id')) {
                        $this->model_payment_paysafecash->validatePaymentOrder($order_id, (int)$this->config->get('paysafecash_order_status_id'), $payment_id);
                        $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('paysafecash_order_status_id'), "Payment ID: ".$payment_id, true);
                        if ($debug_mode) {
                            $this->log->write('FRONT (paysafe:cash => success): status='.$response["status"].' add order history! Payment ID: '.$payment_id);
                            $this->log->write('FRONT (paysafe:cash => success): status='.$response["status"].' validate order database! Payment ID: '.$payment_id);
                        }
                    } else {
                        $this->model_payment_paysafecash->validatePaymentOrder($order_id, (int)$this->config->get('paysafecash_order_status_id'), $payment_id);
                        if ($debug_mode) {
                            $this->log->write('FRONT (paysafe:cash => success): status='.$response["status"].' validate order database (1)! Payment ID: '.$payment_id);
                        }
                    }
                    if ($this->session->data['order_id'] == $order_id) {
                        if (isset($this->session->data['order_id']) && $order_id == $this->session->data['order_id']) {
                            $this->cart->clear();
                            $reset = ['order_id', 'shipping_methods', 'shipping_method', 'payment_methods', 'payment_method', 'guest', 'comment', 'coupon', 'reward', 'voucher', 'vouchers', 'totals'];
                            foreach ($reset as $key) {
                                unset($this->session->data[$key]);
                            }
                        }
                        $redirect = $this->url->link('checkout/success');
                    } elseif ($this->session->data['order_id'] > 0) {
                    } else {
                        $this->session->data['order_id'] = $order_id;
                        $redirect = $this->url->link('checkout/success');
                    }
                } elseif ($response["status"] == "INITIATED" || $response["status"] == "REDIRECTED") {
                    if (isset($this->session->data['order_id']) && $order_id == $this->session->data['order_id']) {
                        $this->cart->clear();
                        $reset = ['order_id', 'shipping_methods', 'shipping_method', 'payment_methods', 'payment_method', 'guest', 'comment', 'coupon', 'reward', 'voucher', 'vouchers', 'totals'];
                        foreach ($reset as $key) {
                            unset($this->session->data[$key]);
                        }
                    }
                    if ($debug_mode) {
                        $this->log->write('FRONT (paysafe:cash => success): status='.$response["status"].' update order status! order_id='.$order_id.' Payment ID: '.$payment_id);
                        $this->log->write('FRONT (paysafe:cash => success): status='.$response["status"].' validate order database! order_id='.$order_id.' Payment ID: '.$payment_id);
                    }
                    $this->model_payment_paysafecash->validatePaymentOrder($order_id, (int)$this->config->get('paysafecash_awaiting_order_status_id'), $payment_id);
                    if ($this->customer->isLogged()) {
                        $redirect = $this->url->link('account/order/info', 'order_id=' . $order_id);
                    } else {
                        $redirect = $this->url->link('checkout/success');
                    }
                } elseif ($response["status"] == "EXPIRED") {
                } elseif ($response["status"] == "AUTHORIZED") {
                    $response_capture = $pscpayment->capturePayment($payment_id);
                    if ($debug_mode) {
                        $this->log->write('FRONT (paysafe:cash => success): status='.$response["status"].' ('.$this->request->server['REQUEST_URI'].') Capture payment response! '.print_r($response_capture, true));
                    }
                    if ($response_capture == true) {
                        if ($debug_mode) {
                            $this->log->write('FRONT (paysafe:cash => success): status='.$response["status"].' Success Transaction before! ');
                        }
                        if (isset($response_capture["object"])) {
                            if ($response_capture["status"] == "SUCCESS") {
                                $this->model_payment_paysafecash->validatePaymentOrder($order_id, (int)$this->config->get('paysafecash_order_status_id'), $payment_id);
                                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('paysafecash_order_status_id'), "Payment ID: ".$payment_id, true);
                                if ($debug_mode) {
                                    $this->log->write('FRONT (paysafe:cash => success): status='.$response["status"].' add order history! Payment ID: '.$payment_id);
                                    $this->log->write('FRONT (paysafe:cash => success): status='.$response["status"].' validate order database! Payment ID: '.$payment_id);
                                }

                                if ($this->session->data['order_id'] == $order_id) {
                                    if (isset($this->session->data['order_id']) && $order_id == $this->session->data['order_id']) {
                                        $this->cart->clear();
                                        $reset = ['order_id', 'shipping_methods', 'shipping_method', 'payment_methods', 'payment_method', 'guest', 'comment', 'coupon', 'reward', 'voucher', 'vouchers', 'totals'];
                                        foreach ($reset as $key) {
                                            unset($this->session->data[$key]);
                                        }
                                    }
                                    $redirect = $this->url->link('checkout/success');
                                } elseif ($this->session->data['order_id'] > 0) {
                                    $redirect = $this->url->link('checkout/success');
                                } else {
                                    $this->session->data['order_id'] = $order_id;
                                    $redirect = $this->url->link('checkout/success');
                                }
                            }
                        } else {
                            if (isset($this->session->data['order_id']) && $order_id == $this->session->data['order_id']) {
                                $this->cart->clear();
                                $reset = ['order_id', 'shipping_methods', 'shipping_method', 'payment_methods', 'payment_method', 'guest', 'comment', 'coupon', 'reward', 'voucher', 'vouchers', 'totals'];
                                foreach ($reset as $key) {
                                    unset($this->session->data[$key]);
                                }
                            }
                            $redirect = $this->url->link('checkout/success');
                        }
                    } else {
                        if (isset($this->session->data['order_id']) && $order_id == $this->session->data['order_id']) {
                            $this->cart->clear();
                            $reset = ['order_id', 'shipping_methods', 'shipping_method', 'payment_methods', 'payment_method', 'guest', 'comment', 'coupon', 'reward', 'voucher', 'vouchers', 'totals'];
                            foreach ($reset as $key) {
                                unset($this->session->data[$key]);
                            }
                        }
                        $redirect = $this->url->link('checkout/success');
                    }
                }
            }
        } else {
            if ($debug_mode) {
                $this->log->write('FRONT (paysafe:cash => success): Order or Payment not available! ');
            }
        }
        if ($debug_mode) {
            $this->log->write('FRONT (paysafe:cash => success): redirect to: '.$redirect);
        }
        $this->response->redirect($redirect);
    }

    // return failure url from paysafe
    public function failure()
    {
        $this->load->model('checkout/order');
        $this->load->model('payment/paysafecash');
        $this->log->write('FRONT (paysafe:cash => failure): SERVER: '.print_r($this->request, true));
        $debug_mode = $this->config->get('paysafecash_debug_mode');

        if ($debug_mode) {
            $debug_mode = $this->config->get('paysafecash_debug_mode');
        }
        $this->log->write('FRONT (paysafe:cash => failure): ('.$this->request->server['REQUEST_URI'].') Transaction aborted by the user: '.print_r($this->request->get, true));

        $order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : 0;
        $payment_id = isset($this->request->get['payment_id']) ? $this->request->get['payment_id'] : '';
        if ($order_id > 0) {
            $order_info = $this->model_checkout_order->getOrder($order_id);
            $this->model_payment_paysafecash->validatePaymentOrder($order_info["order_id"], (int)$this->config->get('paysafecash_declined_order_status_id'), $payment_id);
            $this->model_checkout_order->addOrderHistory($order_info["order_id"], $this->config->get('paysafecash_declined_order_status_id'), "Declined by client!", false);
        }
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

    // this is not enabled
    public function notification()
    {
        $this->load->model('checkout/order');
        $this->load->model('payment/paysafecash');

        $debug_mode = $this->config->get('paysafecash_debug_mode');
        if ($debug_mode) {
            $this->log->write('FRONT (paysafe:cash => notification): ('.$this->request->server['REQUEST_URI'].') '.print_r($this->request->get, true));
        }

        $order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : 0;
        $payment_id = isset($this->request->get['payment_id']) ? $this->request->get['payment_id'] : '';

        if ($debug_mode) {
            $this->log->write('FRONT (paysafe:cash => notification): Start (order_id='.$order_id.' payment_id='.$payment_id.')');
        }

        if (!($order_id > 0)) {
            if ($debug_mode) {
                $this->log->write('FRONT (paysafe:cash => notification): No order id! ');
            }
            return false;
        }

        if ($order_id && $payment_id) {
            $order_info = $this->model_checkout_order->getOrder($order_id);
            if (!count($order_info)) {
                if ($debug_mode) {
                    $this->log->write('FRONT (paysafe:cash => notification): Order not found! ');
                }
                return false;
            }

            $payment_status = $this->config->get('paysafecash_order_status_id');
            $pscpayment = $this->get_connection();
            $response = $pscpayment->retrievePayment($payment_id);

            if ($response == false) {
                if ($debug_mode) {
                    $this->log->write('FRONT (paysafe:cash => notification): No response! ('.$this->request->server['REQUEST_URI'].') '.print_r($response, true));
                }
            } elseif (isset($response["object"])) {
                if ($response["status"] == "SUCCESS") {
                    if ($debug_mode) {
                        $this->log->write('FRONT (paysafe:cash => notification): status=SUCCESS! ('.$this->request->server['REQUEST_URI'].') '.print_r($response, true));
                    }
                    if ($order_info['order_status_id'] == (int)$this->config->get('paysafecash_awaiting_order_status_id')) {
                        $this->model_payment_paysafecash->validatePaymentOrder($order_id, (int)$this->config->get('paysafecash_order_status_id'), $payment_id);
                        $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('paysafecash_order_status_id'), "Payment ID: ".$payment_id, true);

                        if ($debug_mode) {
                            $this->log->write('FRONT (paysafe:cash => notification): status='.$response["status"].' add order history! Payment ID: '.$payment_id);
                        }

                        if ($debug_mode) {
                            $this->log->write('FRONT (paysafe:cash => notification): status='.$response["status"].' validate order database! Payment ID: '.$payment_id);
                        }
                    }
                } elseif ($response["status"] == "INITIATED" || $response["status"] == "REDIRECTED") {
                } elseif ($response["status"] == "EXPIRED") {
                } elseif ($response["status"] == "AUTHORIZED") {
                    $response_capture = $pscpayment->capturePayment($payment_id);
                    if ($debug_mode) {
                        $this->log->write('FRONT (paysafe:cash => notification): status='.$response["status"].' ('.$this->request->server['REQUEST_URI'].') Capture payment response! '.print_r($response_capture, true));
                    }
                    if ($response_capture == true) {
                        if ($debug_mode) {
                            $this->log->write('FRONT (paysafe:cash => notification): status='.$response["status"].' Success Transaction before! ');
                        }
                        if (isset($response_capture["object"])) {
                            if ($response_capture["status"] == "SUCCESS") {
                                $this->model_payment_paysafecash->validatePaymentOrder($order_id, (int)$this->config->get('paysafecash_order_status_id'), $payment_id);
                                $this->model_checkout_order->addOrderHistory($order_id, (int)$this->config->get('paysafecash_order_status_id'), "Payment ID: ".$payment_id, true);

                                return true;
                            }
                        }
                    }
                }
            }
        } else {
            if ($debug_mode) {
                $this->log->write('FRONT (paysafe:cash => notification): Order or Payment not available! ');
            }
        }
    }

    // webhook function to finalize order
    public function webhook()
    {
        $this->load->model('checkout/order');
        $this->load->model('payment/paysafecash');
        $debug_mode = $this->config->get('paysafecash_debug_mode');

        $response = file_get_contents("php://input");

        if ($debug_mode) {
            $this->log->write('FRONT (paysafe:cash => webhook): php: '.print_r($response, true));
        }

        $rsa_key = $this->config->get('paysafecash_webhook_rsa_key');
        $signature = str_replace('"', '', str_replace('signature="', '', explode(",", $this->get_headers()["Authorization"])[2]));
        $publick_key = $this->getPublicKey($rsa_key);
        $status = 0;

        if ($publick_key !== false) {
            $status = openssl_verify($response, base64_decode($signature), $publick_key, OPENSSL_ALGO_SHA256);
            openssl_free_key($publick_key);
        } else {
            if ($debug_mode) {
                $this->log->write('FRONT (paysafe:cash => webhook): Public Key failed for Public Key '.$publick_key);
            }
        }
        if ($debug_mode) {
            $this->log->write('FRONT (paysafe:cash => webhook): Status: '.$status);
        }

        if ($status) {
            $json_response = json_decode($response);

            $order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : 0;
            $payment_id = isset($json_response->data->mtid) ? $json_response->data->mtid : '';

            if ($order_id > 0 && $payment_id) {
                $check_order_data = $this->model_payment_paysafecash->getPaymentAndOrder($order_id, $payment_id);
                if ($check_order_data && $check_order_data['order_id'] > 0) {
                    $order_info = $this->model_checkout_order->getOrder($check_order_data['order_id']);

                    $payment_status = $this->validatePaymentStatus($payment_id);
                    if ($payment_status['status']) {
                        $this->completeOrder($order_info, $payment_id, $payment_status['code']);
                    }
                }
            }
        }
    }

    // check payment status from paysafe
    private function validatePaymentStatus($payment_id)
    {
        $data = ['status' => false, 'code' => ''];
        $pscpayment = $this->get_connection();
        $response = $pscpayment->retrievePayment($payment_id);
        if ($response !== false && isset($response["status"])) {
            $data['code'] = $response["status"];
            if ($response["status"] == "SUCCESS") {
                $data['status'] = true;
            } elseif ($response["status"] == "AUTHORIZED") {
                $response = $pscpayment->capturePayment($payment_id);
                if ($response !== false && isset($response["status"]) && $response["status"] == "SUCCESS") {
                    $data['status'] = true;
                    $data['code'] = $response["status"];
                }
            }
        }

        return $data;
    }

    // Extract public key from certificate and prepare it for use
    private function getPublicKey($key)
    {
        if (strpos($key, 'RSA PUBLIC') !== false) {
            $key = str_replace(['-----BEGIN RSA PUBLIC KEY-----', '-----END RSA PUBLIC KEY-----'], '', $key);
            $key = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A' . trim($key);
            $key = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($key, 64) . '-----END PUBLIC KEY-----';
        }
        return openssl_pkey_get_public($key);
    }

    // Fetch all HTTP request headers
    private function get_headers()
    {
        if (function_exists('apache_request_headers')) {
            return apache_request_headers();
        }
        $headers = [];
        foreach ($this->request->server as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
            }
        }
        return $headers;
    }

    // complete order
    private function completeOrder($order, $payment_id, $code)
    {
        $this->load->model('checkout/order');
        $this->load->model('payment/paysafecash');
        $debug_mode = $this->config->get('paysafecash_debug_mode');

        if ($order['order_status_id'] == (int)$this->config->get('paysafecash_awaiting_order_status_id')) {
            $this->model_payment_paysafecash->validatePaymentOrder($order['order_id'], (int)$this->config->get('paysafecash_order_status_id'), $payment_id);
            $this->model_checkout_order->addOrderHistory($order['order_id'], $this->config->get('paysafecash_order_status_id'), "Payment ID: ".$payment_id, true);

            if ($debug_mode) {
                $this->log->write('FRONT (paysafe:cash => complete order): add order history! Payment ID: '.$payment_id);
            }

            if ($debug_mode) {
                $this->log->write('FRONT (paysafe:cash => complete order): validate order database! Payment ID: '.$payment_id);
            }
        }
    }
}
