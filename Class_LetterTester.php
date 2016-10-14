<?php
require_once 'Class_ScriptBase.php';

/*
 * Opencart Utility Class
 *
 * It helps to generate customer order notification email into a file
 * */
class LetterTester extends ScriptBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /* Generate order email in HTML format
     *
     * based on Opencart 1.5.6.4
     * */
    public function generateOrderEmail($order_id)
    {
        $notify = false;
        $comment = '';
        $html = '';
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $language = new Language($order_info['language_directory']);
        $language->load($order_info['language_filename']);
        $language->load('mail/order');
        $language->load('product/product');

        $subject = sprintf($language->get('text_new_subject'), $order_info['store_name'], $order_id);

        // HTML Mail
        $template = new Template();

        $template->data['title'] = sprintf($language->get('text_new_subject'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'), $order_id);

        $template->data['text_greeting'] = sprintf($language->get('text_new_greeting'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
        $template->data['text_link'] = $language->get('text_new_link');
        $template->data['text_download'] = $language->get('text_new_download');
        $template->data['text_order_detail'] = $language->get('text_new_order_detail');
        $template->data['text_instruction'] = $language->get('text_new_instruction');
        $template->data['text_order_id'] = $language->get('text_new_order_id');
        $template->data['text_date_added'] = $language->get('text_new_date_added');
        $template->data['text_payment_method'] = $language->get('text_new_payment_method');
        $template->data['text_shipping_method'] = $language->get('text_new_shipping_method');
        $template->data['text_shipping_info'] = $language->get('text_shipping_info');
        $template->data['text_email'] = $language->get('text_new_email');
        $template->data['text_telephone'] = $language->get('text_new_telephone');
        $template->data['text_ip'] = $language->get('text_new_ip');
        $template->data['text_payment_address'] = $language->get('text_new_payment_address');
        $template->data['text_shipping_address'] = $language->get('text_new_shipping_address');
        $template->data['text_product'] = $language->get('text_new_product');
        $template->data['text_model'] = $language->get('text_new_model');
        $template->data['text_quantity'] = $language->get('text_new_quantity');
        $template->data['text_price'] = $language->get('text_new_price');
        $template->data['text_total'] = $language->get('text_new_total');
        $template->data['text_footer'] = $language->get('text_new_footer');
        $template->data['text_powered'] = $language->get('text_new_powered');

        $template->data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
        $template->data['store_name'] = $order_info['store_name'];
        $template->data['store_url'] = $order_info['store_url'];
        $template->data['customer_id'] = $order_info['customer_id'];
        $template->data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id;

        $order_download_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");

        if ($order_download_query->num_rows) {
            $template->data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
        } else {
            $template->data['download'] = '';
        }

        $template->data['order_id'] = $order_id;
        $template->data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
        $template->data['payment_method'] = $order_info['payment_method'];
        $template->data['shipping_method'] = $order_info['shipping_method'];
        $template->data['email'] = $order_info['email'];
        $template->data['telephone'] = $order_info['telephone'];
        $template->data['ip'] = $order_info['ip'];

        if ($comment && $notify) {
            $template->data['comment'] = nl2br($comment);
        } else {
            $template->data['comment'] = '';
        }

        if ($order_info['payment_address_format']) {
            $format = $order_info['payment_address_format'];
        } else {
            $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
        }

        $find = array(
            '{firstname}',
            '{lastname}',
            '{company}',
            '{address_1}',
            '{address_2}',
            '{city}',
            '{postcode}',
            '{zone}',
            '{zone_code}',
            '{country}'
        );

        $replace = array(
            'firstname' => $order_info['payment_firstname'],
            'lastname'  => $order_info['payment_lastname'],
            'company'   => $order_info['payment_company'],
            'address_1' => $order_info['payment_address_1'],
            'address_2' => $order_info['payment_address_2'],
            'city'      => $order_info['payment_city'],
            'postcode'  => $order_info['payment_postcode'],
            'zone'      => $order_info['payment_zone'],
            'zone_code' => $order_info['payment_zone_code'],
            'country'   => $order_info['payment_country']
        );

        $template->data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

        if ($order_info['shipping_address_format']) {
            $format = $order_info['shipping_address_format'];
        } else {
            $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
        }

        $find = array(
            '{firstname}',
            '{lastname}',
            '{company}',
            '{address_1}',
            '{address_2}',
            '{city}',
            '{postcode}',
            '{zone}',
            '{zone_code}',
            '{country}'
        );

        $replace = array(
            'firstname' => $order_info['shipping_firstname'],
            'lastname'  => $order_info['shipping_lastname'],
            'company'   => $order_info['shipping_company'],
            'address_1' => $order_info['shipping_address_1'],
            'address_2' => $order_info['shipping_address_2'],
            'city'      => $order_info['shipping_city'],
            'postcode'  => $order_info['shipping_postcode'],
            'zone'      => $order_info['shipping_zone'],
            'zone_code' => $order_info['shipping_zone_code'],
            'country'   => $order_info['shipping_country']
        );

        $template->data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

        // Products
        $template->data['products'] = array();

        $order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

        foreach ($order_product_query->rows as $product) {
            $option_data = array();

            $order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

            foreach ($order_option_query->rows as $option) {
                if ($option['type'] != 'file') {
                    $value = $option['value'];
                } else {
                    $value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
                }

                $option_data[] = array(
                    'name'  => $option['name'],
                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                );
            }

            $template->data['products'][] = array(
                'name'     => $product['name'],
                'model'    => $product['model'],
                'option'   => $option_data,
                'quantity' => $product['quantity'],
                'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
            );
        }

        // Vouchers
        $template->data['vouchers'] = array();

        $this->load->model('checkout/voucher');

        $order_voucher_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

        foreach ($order_voucher_query->rows as $voucher) {
            $template->data['vouchers'][] = array(
                'description' => $voucher['description'],
                'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
            );
        }

        $order_total_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order ASC");

        $template->data['totals'] = $order_total_query->rows;

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/order.tpl')) {
            $html = $template->fetch($this->config->get('config_template') . '/template/mail/order.tpl');
        } else {
            $html = $template->fetch('default/template/mail/order.tpl');
        }

        file_put_contents('output/generated_email.html', $html);
    }
}
?>