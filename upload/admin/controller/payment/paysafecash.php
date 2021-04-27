<?php
class ControllerPaymentPaysafecash extends Controller
{
    private $error = [];

    // current version
    private $version = '1.0.0';

    // url to check for updates
    private $version_url = 'https://raw.githubusercontent.com/Paysafecard-DEV/Version-Checker/master/version.json';

    public function index()
    {
        $this->load->language('payment/paysafecash');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('paysafecash', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data = [];

        // set language variables
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_paysafecash_test_mode'] = $this->language->get('text_paysafecash_test_mode');
        $data['text_paysafecash_api_key'] = $this->language->get('text_paysafecash_api_key');
        $data['text_paysafecash_webhook_rsa_key'] = $this->language->get('text_paysafecash_webhook_rsa_key');
        $data['text_paysafecash_submerchant_id'] = $this->language->get('text_paysafecash_submerchant_id');
        $data['text_paysafecash_customer_data'] = $this->language->get('text_paysafecash_customer_data');
        $data['text_paysafecash_var_trans_timeout'] = $this->language->get('text_paysafecash_var_trans_timeout');
        $data['text_paysafecash_countries'] = $this->language->get('text_paysafecash_countries');
        $data['text_paysafecash_debug_mode'] = $this->language->get('text_paysafecash_debug_mode');
        $data['text_paysafecash_enable_debug_mode'] = $this->language->get('text_paysafecash_enable_debug_mode');
        $data['entry_paysafecash_test_mode'] = $this->language->get('entry_paysafecash_test_mode');
        $data['entry_paysafecash_api_key'] = $this->language->get('entry_paysafecash_api_key');
        $data['entry_paysafecash_webhook_rsa_key'] = $this->language->get('entry_paysafecash_webhook_rsa_key');
        $data['entry_paysafecash_submerchant_id'] = $this->language->get('entry_paysafecash_submerchant_id');
        $data['entry_paysafecash_customer_data'] = $this->language->get('entry_paysafecash_customer_data');
        $data['entry_paysafecash_var_trans_timeout'] = $this->language->get('entry_paysafecash_var_trans_timeout');
        $data['entry_paysafecash_countries'] = $this->language->get('entry_paysafecash_countries');
        $data['entry_paysafecash_debug_mode'] = $this->language->get('entry_paysafecash_debug_mode');

        $data['vrs_latest_version'] = $this->language->get('vrs_latest_version');
        $data['vrs_last_update'] = $this->language->get('vrs_last_update');
        $data['vrs_changelog'] = $this->language->get('vrs_changelog');
        $data['vrs_please_check_version'] = $this->language->get('vrs_please_check_version');

        $data['tab_settings'] = $this->language->get('tab_settings');
        $data['tab_about'] = $this->language->get('tab_about');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_check'] = $this->language->get('button_check');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_declined_order_status'] = $this->language->get('entry_declined_order_status');
        $data['entry_awaiting_order_status'] = $this->language->get('entry_awaiting_order_status');
        $data['entry_refund_order_status'] = $this->language->get('entry_refund_order_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_payment_description'] = $this->language->get('entry_payment_description');
        $data['entry_confirm_description'] = $this->language->get('entry_confirm_description');
        $data['text_current_version_nr'] = $this->version;
        $data['text_current_version'] = $this->language->get('text_current_version');

        $data['link_check_version'] = $this->url->link('payment/paysafecash/checkversion'.'&token=' . $this->session->data['token'], '', 'SSL');

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/paysafecash', 'token=' . $this->session->data['token'], 'SSL')
        ];

        $data['action'] = $this->url->link('payment/paysafecash', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['paysafecash_test_mode'])) {
            $data['paysafecash_test_mode'] = trim($this->request->post['paysafecash_test_mode']);
        } else {
            $data['paysafecash_test_mode'] = $this->config->get('paysafecash_test_mode');
        }

        if (isset($this->request->post['paysafecash_api_key'])) {
            $data['paysafecash_api_key'] = trim($this->request->post['paysafecash_api_key']);
        } else {
            $data['paysafecash_api_key'] = $this->config->get('paysafecash_api_key');
        }

        if (isset($this->request->post['paysafecash_webhook_rsa_key'])) {
            $data['paysafecash_webhook_rsa_key'] = trim($this->request->post['paysafecash_webhook_rsa_key']);
        } else {
            $data['paysafecash_webhook_rsa_key'] = $this->config->get('paysafecash_webhook_rsa_key');
        }

        if (isset($this->request->post['paysafecash_submerchant_id'])) {
            $data['paysafecash_submerchant_id'] = trim($this->request->post['paysafecash_submerchant_id']);
        } else {
            $data['paysafecash_submerchant_id'] = $this->config->get('paysafecash_submerchant_id');
        }

        if (isset($this->request->post['paysafecash_customer_data'])) {
            $data['paysafecash_customer_data'] = trim($this->request->post['paysafecash_customer_data']);
        } else {
            $data['paysafecash_customer_data'] = $this->config->get('paysafecash_customer_data');
        }

        if (isset($this->request->post['paysafecash_var_trans_timeout'])) {
            $data['paysafecash_var_trans_timeout'] = trim($this->request->post['paysafecash_var_trans_timeout']);
        } else {
            $data['paysafecash_var_trans_timeout'] = $this->config->get('paysafecash_var_trans_timeout');
        }

        if (isset($this->request->post['paysafecash_countries'])) {
            $data['paysafecash_countries'] = unserialize(serialize($this->request->post['paysafecash_countries']));
        } else {
            $data['paysafecash_countries'] = $this->config->get('paysafecash_countries');
        }

        $this->load->model('localisation/country');
        $data['countries'] = $this->model_localisation_country->getCountries();

        if (isset($this->request->post['paysafecash_debug_mode'])) {
            $data['paysafecash_debug_mode'] = trim($this->request->post['paysafecash_debug_mode']);
        } else {
            $data['paysafecash_debug_mode'] = $this->config->get('paysafecash_debug_mode');
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['paysafecash_order_status_id'])) {
            $data['paysafecash_order_status_id'] = $this->request->post['paysafecash_order_status_id'];
        } else {
            $data['paysafecash_order_status_id'] = $this->config->get('paysafecash_order_status_id');
        }

        if (isset($this->request->post['paysafecash_declined_order_status_id'])) {
            $data['paysafecash_declined_order_status_id'] = $this->request->post['paysafecash_declined_order_status_id'];
        } else {
            $data['paysafecash_declined_order_status_id'] = $this->config->get('paysafecash_declined_order_status_id');
        }

        if (isset($this->request->post['paysafecash_awaiting_order_status_id'])) {
            $data['paysafecash_awaiting_order_status_id'] = $this->request->post['paysafecash_awaiting_order_status_id'];
        } else {
            $data['paysafecash_awaiting_order_status_id'] = $this->config->get('paysafecash_awaiting_order_status_id');
        }

        if (isset($this->request->post['paysafecash_refund_order_status_id'])) {
            $data['paysafecash_refund_order_status_id'] = $this->request->post['paysafecash_refund_order_status_id'];
        } else {
            $data['paysafecash_refund_order_status_id'] = $this->config->get('paysafecash_refund_order_status_id');
        }

        if (isset($this->request->post['paysafecash_status'])) {
            $data['paysafecash_status'] = $this->request->post['paysafecash_status'];
        } else {
            $data['paysafecash_status'] = $this->config->get('paysafecash_status');
        }

        if (isset($this->request->post['paysafecash_sort_order'])) {
            $data['paysafecash_sort_order'] = $this->request->post['paysafecash_sort_order'];
        } else {
            $data['paysafecash_sort_order'] = $this->config->get('paysafecash_sort_order');
        }

        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();
        $data['languages'] = $languages;

        if (version_compare(VERSION, '2.0.0.0', '>')) {
            if (version_compare(VERSION, '2.2.0.0', '<')) {
                foreach ($data['languages'] as $language_key => $language_val) {
                    $data['languages'][$language_key]['image'] = 'view/image/flags/'.$language_val['image'];
                }
            } else {
                foreach ($data['languages'] as $language_key => $language_val) {
                    $data['languages'][$language_key]['image'] = 'language/'.$language_val['code'].'/'.$language_val['code'].'.png';
                }
            }
        }

        foreach ($languages as $language) {
            if (isset($this->request->post['paysafecash_payment_description' . $language['language_id']])) {
                $data['paysafecash_payment_description' . $language['language_id']] = $this->request->post['paysafecash_payment_description' . $language['language_id']];
            } else {
                $data['paysafecash_payment_description' . $language['language_id']] = $this->config->get('paysafecash_payment_description' . $language['language_id']);
            }

            if (isset($this->request->post['paysafecash_confirm_description' . $language['language_id']])) {
                $data['paysafecash_confirm_description' . $language['language_id']] = $this->request->post['paysafecash_confirm_description' . $language['language_id']];
            } else {
                $data['paysafecash_confirm_description' . $language['language_id']] = $this->config->get('paysafecash_confirm_description' . $language['language_id']);
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/paysafecash.tpl', $data));
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/paysafecash')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    public function listorders()
    {
        $this->load->language('payment/paysafecash');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('payment/paysafecash');

        $data['heading_title'] = $this->language->get('heading_title');

        $url = '';

        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/paysafecash/listorders', 'token=' . $this->session->data['token'] . $url, 'SSL')
        ];

        $data['button_refund'] = $this->language->get('button_refund');

        // check if the extension is installed
        $check_installation = $this->model_payment_paysafecash->checkInstall();
        if (!$check_installation) {
            $data['error_install'] = $this->language->get('error_install');
        } else {
            $data['error_install'] = '';
            $data['text_no_results'] = $this->language->get('text_no_results');
            $data['text_list'] = $this->language->get('text_list');
            $data['text_missing'] = $this->language->get('text_missing');
            $data['text_yes'] = $this->language->get('text_yes');

            //filter entry
            $data['entry_order_id'] = $this->language->get('entry_order_id');
            $data['entry_customer'] = $this->language->get('entry_customer');
            $data['entry_paymentid'] = $this->language->get('entry_paymentid');
            $data['entry_orderstatus'] = $this->language->get('entry_orderstatus');
            $data['entry_date_added'] = $this->language->get('entry_date_added');
            $data['entry_date_modified'] = $this->language->get('entry_date_modified');

            $data['button_filter'] = $this->language->get('button_filter');

            $data['button_view'] = $this->language->get('button_view');
            $data['text_confirm'] = $this->language->get('text_confirm');

            //table
            $data['col_order'] = $this->language->get('col_order');
            $data['col_paymentid'] = $this->language->get('col_paymentid');
            $data['col_customer'] = $this->language->get('col_customer');
            $data['col_status'] = $this->language->get('col_status');
            $data['col_total'] = $this->language->get('col_total');
            $data['col_date_added'] = $this->language->get('col_date_added');
            $data['col_date_modified'] = $this->language->get('col_date_modified');
            $data['col_refunded'] = $this->language->get('col_refunded');
            $data['col_refunddate'] = $this->language->get('col_refunddate');
            $data['col_view'] = $this->language->get('col_view');

            if (isset($this->request->get['filter_order_id'])) {
                $filter_order_id = $this->request->get['filter_order_id'];
            } else {
                $filter_order_id = null;
            }

            if (isset($this->request->get['filter_customer'])) {
                $filter_customer = $this->request->get['filter_customer'];
            } else {
                $filter_customer = null;
            }

            if (isset($this->request->get['filter_paymentid'])) {
                $filter_paymentid = $this->request->get['filter_paymentid'];
            } else {
                $filter_paymentid = null;
            }

            if (isset($this->request->get['filter_order_status'])) {
                $filter_order_status = $this->request->get['filter_order_status'];
            } else {
                $filter_order_status = null;
            }

            if (isset($this->request->get['filter_date_added'])) {
                $filter_date_added = $this->request->get['filter_date_added'];
            } else {
                $filter_date_added = null;
            }

            if (isset($this->request->get['filter_date_modified'])) {
                $filter_date_modified = $this->request->get['filter_date_modified'];
            } else {
                $filter_date_modified = null;
            }

            if (isset($this->request->get['sort'])) {
                $sort = $this->request->get['sort'];
            } else {
                $sort = 'order_id';
            }

            if (isset($this->request->get['order'])) {
                $order = $this->request->get['order'];
            } else {
                $order = 'DESC';
            }

            if (isset($this->request->get['page'])) {
                $page = $this->request->get['page'];
            } else {
                $page = 1;
            }

            if (isset($this->request->get['filter_order_id'])) {
                $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
            }

            if (isset($this->request->get['filter_customer'])) {
                $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_paymentid'])) {
                $url .= '&filter_paymentid=' . urlencode(html_entity_decode($this->request->get['filter_paymentid'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_order_status'])) {
                $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['filter_date_modified'])) {
                $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $data['refund'] = $this->url->link('payment/paysafecash/refund', 'token=' . $this->session->data['token'] . $url, 'SSL');

            $data['psorders'] = [];

            $filter_data = [
                'filter_order_id'     => $filter_order_id,
                'filter_customer'     => $filter_customer,
                'filter_paymentid'    => $filter_paymentid,
                'filter_order_status' => $filter_order_status,
                'filter_date_added'   => $filter_date_added,
                'filter_date_modified'    => $filter_date_modified,
                'sort'            => $sort,
                'order'           => $order,
                'start'           => ($page - 1),
                'limit'           => 20
            ];
            $this->load->model('payment/paysafecash');
            $results = $this->model_payment_paysafecash->listorders($filter_data);
            $totalorders = $this->model_payment_paysafecash->countorders($filter_data);

            foreach ($results as $result) {
                $data['psorders'][] = [
                    'order_id' => $result['order_id'],
                    'payment_id' => $result['payment_id'],
                    'customer' => $result['customer'],
                    'status' => $result['status'],
                    'psc_status' => $result['psc_status'],
                    'total' => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
                    'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
                    'currency_code' => $result['currency_code'],
                    'currency_value' => $result['currency_value'],
                    'ref_id' => $result['ref_id'],
                    'refunded' => $result['refunded'],
                    'refunded_date' => date($this->language->get('date_format_short'), strtotime($result['refunded_date'])),
                    'payment_id' => $result['payment_id'],
                    'view' => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id='.$result['order_id'], 'SSL')
                ];
            }

            $data['token'] = $this->session->data['token'];

            if (isset($this->session->data['success'])) {
                $data['success'] = $this->session->data['success'];

                unset($this->session->data['success']);
            } else {
                $data['success'] = '';
            }

            if (isset($this->session->data['refund_messages'])) {
                $data['refund_messages'] = $this->session->data['refund_messages'];

                unset($this->session->data['refund_messages']);
            } else {
                $data['refund_messages'] = '';
            }

            if (isset($this->request->post['selected_orders'])) {
                $data['selected_orders'] = (array)$this->request->post['selected_orders'];
            } else {
                $data['selected_orders'] = [];
            }

            $url = '';

            if (isset($this->request->get['filter_order_id'])) {
                $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
            }

            if (isset($this->request->get['filter_customer'])) {
                $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_paymentid'])) {
                $url .= '&filter_paymentid=' . urlencode(html_entity_decode($this->request->get['filter_paymentid'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_order_status'])) {
                $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['filter_date_modified'])) {
                $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
            }

            if ($order == 'ASC') {
                $url .= '&order=DESC';
            } else {
                $url .= '&order=ASC';
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $data['sort_order'] = $this->url->link('payment/paysafecash/listorders', 'token=' . $this->session->data['token'] . '&sort=order_id' . $url, 'SSL');
            $data['sort_status'] = $this->url->link('payment/paysafecash/listorders', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');

            $url = '';

            if (isset($this->request->get['filter_order_id'])) {
                $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
            }

            if (isset($this->request->get['filter_customer'])) {
                $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_paymentid'])) {
                $url .= '&filter_paymentid=' . urlencode(html_entity_decode($this->request->get['filter_paymentid'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_order_status'])) {
                $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['filter_date_modified'])) {
                $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            //pagination
            $pagination = new Pagination();
            $pagination->total = $totalorders;
            $pagination->page = $page;
            $pagination->limit = $this->config->get('config_limit_admin');
            $pagination->url = $this->url->link('payment/paysafecash/listorders', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

            $data['pagination'] = $pagination->render();

            $data['results'] = sprintf($this->language->get('text_pagination'), ($totalorders) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($totalorders - $this->config->get('config_limit_admin'))) ? $totalorders : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $totalorders, ceil($totalorders / $this->config->get('config_limit_admin')));

            $data['filter_order_id'] = $filter_order_id;
            $data['filter_customer'] = $filter_customer;
            $data['filter_paymentid'] = $filter_paymentid;
            $data['filter_order_status'] = $filter_order_status;
            $data['filter_date_added'] = $filter_date_added;
            $data['filter_date_modified'] = $filter_date_modified;

            $this->load->model('localisation/order_status');

            $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

            $data['sort'] = $sort;
            $data['order'] = $order;
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('payment/paysafecash_orders.tpl', $data));
    }

    // refund the selected orders
    public function refund()
    {
        $this->load->model('payment/paysafecash');

        $debug_mode = $this->config->get('paysafecash_debug_mode');
        $test_mode = $this->config->get('paysafecash_test_mode');
        $paysafecash_submerchant_id = $this->config->get('paysafecash_submerchant_id');

        if ($test_mode) {
            $env = "TEST";
        } else {
            $env = "PRODUCTION";
        }

        $messages = [];

        foreach ($this->request->post['selected_orders'] as $order) {
            if ($order > 0) {
                $order_info = $this->model_payment_paysafecash->listorders(['filter_order_id' => $order]);

                if (count($order_info)) {
                    $customer_hash = "";
                    if ($order_info[0]['customer_id'] > 0) {
                        $customer_hash = md5($order_info[0]['customer_id']);
                    } else {
                        $customer_hash = md5($order_info[0]['email']);
                    }

                    if ($test_mode) {
                        $order_amount = 1;
                    } else {
                        $order_amount = $this->currency->format($order_info[0]['total'], $order_info[0]['currency_code'], $order_info[0]['currency_value'], false);
                    }

                    $data_payment = [
                        'payment_id' => $order_info[0]['payment_id'],
                        'amount' => $order_amount,
                        'currency' => $order_info[0]['currency_code'],
                        'merchantclientid' => $customer_hash,
                        'customer_mail' => $order_info[0]['email'],
                        'correlation_id' => '',
                        'submerchant_id' => '',
                        'shop_id' => $order_info[0]["store_name"]." ".VERSION." | ".$this->version,
                    ];

                    if ($debug_mode) {
                        $this->log->write('Back (paysafe:cash => refund): data sent: '.print_r($data_payment, true));
                    }

                    $pscpayment = $this->get_connection();
                    $response = $pscpayment->captureRefund($data_payment['payment_id'], $data_payment['amount'], $data_payment['currency'], $data_payment['merchantclientid'], $data_payment['customer_mail'], $data_payment['correlation_id'], $data_payment['submerchant_id'], $data_payment['shop_id']);

                    if ($debug_mode) {
                        $this->log->write('Back (paysafe:cash => refund): response: '.print_r($response, true));
                    }

                    if ($response == false || isset($response['number'])) {
                        $messages[] = ['status' => 0, 'number' => $response['number'], 'message' => $response['message'], 'order_id' => $order, 'payment_id' => $order_info[0]['payment_id']];
                    } elseif (isset($response["object"])) {
                        if ($response["status"] == "SUCCESS") {
                            $this->model_payment_paysafecash->doRefund($order, $order_info[0]['payment_id'], ['ref_id' => $response["id"], 'status' => (int)$this->config->get('paysafecash_refund_order_status_id')]);
                            $messages[] = ['status' => 1, 'message' => 'Refunded', 'order_id' => $order, 'payment_id' => $order_info[0]['payment_id'], 'ref_id' => $response["id"]];
                        } else {
                            $messages[] = ['status' => 0, 'number' => $response['number'], 'message' => $response['message'], 'order_id' => $order, 'payment_id' => $order_info[0]['payment_id']];
                        }
                    }
                }
            }
        }

        $this->session->data['refund_messages'] = $messages;
        $this->listorders();
    }

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

    // install extension
    public function install()
    {
        $this->load->model('payment/paysafecash');
        $this->model_payment_paysafecash->install();
    }

    // uninstall extension
    public function uninstall()
    {
        $this->load->model('payment/paysafecash');
        $this->model_payment_paysafecash->uninstall();
    }

    // check version and display data
    public function checkversion()
    {
        $json = file_get_contents($this->version_url.'?'.time());
        header('Content-Type: application/json');
        $data = json_decode($json, true);
        if ($data) {
            echo json_encode([
                'status' => 1,
                'data' => [
                    'version' => $this->version,
                    'latest_version' => $data['oc2']['current_version'],
                    'changelog' => $data['oc2']['changelog'],
                    'lastupdate' => $data['oc2']['last_update'],
                ],
            ]);
        } else {
            echo json_encode(['status' => 0, 'msg' => 'no data ('.$this->version_url.')']);
        }
    }
}
